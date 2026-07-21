<?php

namespace Tests\Feature;

use App\Filament\Resources\CharacterResource;
use App\Models\City;
use App\Models\Kingdom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingKingdomFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_onboarding_to_first_post_flow(): void
    {
        $user = User::factory()->create();

        // Registration flow creates the character + stats atomically — replicate that here.
        $character = $user->character()->create([
            'name'   => 'Test Wanderer',
            'status' => 'pending',
        ]);
        $character->stats()->create([
            'level' => 0, 'exp' => 0, 'exp_to_next' => 0,
            'hp' => 100, 'mana' => 50, 'str' => 10, 'agi' => 10, 'int' => 10,
        ]);

        $this->actingAs($user);

        // Stage 1–3 essays promote the character to level 1, but status stays "pending" —
        // level-up alone must not unlock kingdom choice without admin approval.
        foreach ([1, 2, 3] as $stage) {
            $response = $this->post('/onboarding/stage', [
                'stage'   => $stage,
                'content' => str_repeat('lore ', 10),
            ]);
            $response->assertRedirect(route('onboarding'));
        }

        $character->refresh();
        $this->assertSame(1, $character->stats->level);
        $this->assertTrue($character->stats->stage_1_completed);
        $this->assertTrue($character->stats->stage_2_completed);
        $this->assertTrue($character->stats->stage_3_completed);
        $this->assertSame('pending', $character->status);

        // Still pending admin approval — /onboarding must NOT bounce to /choose-kingdom yet.
        $this->get('/onboarding')->assertOk();
        $this->post('/choose-kingdom', ['kingdom_id' => 1])->assertForbidden();

        // Admin approves (CharacterResource::approveCharacter is the only place that flips status).
        CharacterResource::approveCharacter($character);
        $character->refresh();
        $this->assertSame('active', $character->status);

        // actingAs() reuses one User object across every simulated request in this test, so its
        // already-cached `character` relation (loaded on the first request above) is stale after
        // the out-of-band admin approval — refresh() reloads currently-loaded relations too.
        // (Not an issue in production: each real HTTP request re-resolves the user from session.)
        $user->refresh();

        // Now /onboarding redirects to kingdom choice.
        $this->get('/onboarding')->assertRedirect(route('choose-kingdom'));

        $kingdom = Kingdom::create([
            'name' => 'Silvaria', 'description' => 'Forest kingdom', 'is_active' => true,
        ]);
        $city = City::create([
            'kingdom_id' => $kingdom->id, 'name' => 'Mokagi',
            'write_min_level' => 0, 'require_approval' => false,
        ]);

        $this->get('/choose-kingdom')->assertOk();

        $this->post('/choose-kingdom', ['kingdom_id' => $kingdom->id])
            ->assertRedirect(route('home'));

        $character->refresh();
        $this->assertSame($kingdom->id, $character->kingdom_id);
        $this->assertSame($kingdom->id, $character->current_kingdom_id);
        $user->refresh();

        // Kingdom is permanent — a second choice must be rejected server-side.
        $otherKingdom = Kingdom::create(['name' => 'Aurantia', 'is_active' => true]);
        $this->post('/choose-kingdom', ['kingdom_id' => $otherKingdom->id])->assertForbidden();
        $this->assertSame($kingdom->id, $character->fresh()->kingdom_id);

        // With a kingdom set, posting in a home-kingdom city with no approval gate goes live immediately.
        $response = $this->post("/cities/{$city->id}/threads", [
            'title'   => 'My First Tale',
            'content' => 'Once upon a time...',
            'action'  => 'submit',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('threads', ['city_id' => $city->id, 'title' => 'My First Tale', 'status' => 'approved']);
        $this->assertDatabaseHas('posts', ['character_id' => $character->id, 'status' => 'approved']);
    }
}
