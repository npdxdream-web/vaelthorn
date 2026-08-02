<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\RewardResource\Pages\CreateReward;
use App\Models\Character;
use App\Models\City;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Kingdom;
use App\Models\Post;
use App\Models\Reward;
use App\Models\RewardLog;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the Event <-> Thread wiring added on top of the pre-existing (and
 * untouched) LevelingService::distributeEventRewards()/resolveExpAmount()
 * logic: linking a thread to an Event via the create-thread form, the
 * display_tag accessor, auto-joining event_participants on post approval,
 * and the RewardResource dropdown fields.
 */
class EventThreadIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function makeCharacter(User $user, string $name): Character
    {
        $character = $user->character()->create([
            'name'   => $name,
            'status' => 'active',
        ]);
        $character->stats()->create(['level' => 1, 'exp' => 0, 'exp_to_next' => 10]);

        return $character->fresh();
    }

    private function makeCity(): City
    {
        $kingdom = Kingdom::create(['name' => 'Test Kingdom', 'is_active' => true]);

        // require_approval = true so posts land as 'pending' and approval is
        // a distinct, explicit step (matches the real "approve post" button flow).
        return City::create([
            'kingdom_id'       => $kingdom->id,
            'name'             => 'Test City',
            'write_min_level'  => 0,
            'require_approval' => true,
        ]);
    }

    public function test_event_linked_thread_grants_reward_and_auto_joins_participant_on_post_approval(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->makeCharacter($admin, 'AdminChar');

        $player     = User::factory()->create(['role' => UserRole::Player]);
        $playerChar = $this->makeCharacter($player, 'PlayerChar');

        $city  = $this->makeCity();
        $event = Event::create([
            'title'      => 'Test Flash Event',
            'type'       => 'flash',
            'status'     => 'active',
            'created_by' => $admin->id,
            'exp_reward' => 1,
        ]);

        $item = Item::create(['name' => 'Test Sword', 'type' => 'weapon']);

        // Same fields RewardResource's Select/relationship inputs write on submit —
        // functionally identical to filling out the Filament form.
        $reward = Reward::create([
            'event_id'      => $event->id,
            'item_id'       => $item->id,
            'item_quantity' => 3,
            'gold_amount'   => 50,
            'exp_amount'    => 0,
        ]);

        // ── Create thread as admin, linked to the Event via the real route ──
        $this->actingAs($admin)
            ->post(route('thread.store', $city->id), [
                'title'    => 'Event Thread',
                'content'  => 'Opening post',
                'action'   => 'submit',
                'event_id' => $event->id,
            ])
            ->assertRedirect();

        $thread = Thread::where('title', 'Event Thread')->firstOrFail();
        $this->assertSame($event->id, $thread->event_id, 'Thread should be linked to the Event via event_id');

        // ── Tag rendering must match Event::typeMeta(), not thread_category ──
        $tag = $thread->fresh()->load('event')->display_tag;
        $this->assertNotNull($tag);
        $this->assertSame($event->type_label, $tag['label']);
        $this->assertSame($event->type_color, $tag['text']);

        // ── Player posts, admin approves through the real approve route ─────
        $this->actingAs($player)
            ->post(route('post.store', $thread->id), ['content' => 'My reply'])
            ->assertRedirect();

        $post = Post::where('thread_id', $thread->id)->where('character_id', $playerChar->id)->firstOrFail();
        $this->assertSame('pending', $post->status, 'require_approval city should leave the post pending until approved');

        $this->assertNull(Inventory::where('character_id', $playerChar->id)->where('item_id', $item->id)->first());
        $goldBefore = $playerChar->gold;

        $this->actingAs($admin)
            ->post(route('post.approve', $post->id))
            ->assertRedirect();

        $playerChar->refresh();
        $inventory = Inventory::where('character_id', $playerChar->id)->where('item_id', $item->id)->first();
        $this->assertNotNull($inventory, 'Reward item should have been granted to inventory on approval');
        $this->assertSame(3, $inventory->quantity);
        $this->assertSame($goldBefore + 50, $playerChar->gold, 'Reward gold should have been granted on approval');

        $this->assertDatabaseHas('event_participants', [
            'event_id'     => $event->id,
            'character_id' => $playerChar->id,
        ]);
        $this->assertSame(
            1,
            EventParticipant::where('event_id', $event->id)->where('character_id', $playerChar->id)->count(),
            'Character should have been auto-joined to the Event exactly once'
        );

        // ── Second post + approval must NOT double-grant reward or duplicate the participant row ──
        $this->actingAs($player)
            ->post(route('post.store', $thread->id), ['content' => 'Second reply'])
            ->assertRedirect();

        $post2 = Post::where('thread_id', $thread->id)->where('character_id', $playerChar->id)->latest('id')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('post.approve', $post2->id))
            ->assertRedirect();

        $playerChar->refresh();
        $inventory->refresh();
        $this->assertSame(3, $inventory->quantity, 'Item quantity must not double on a second approved post');
        $this->assertSame($goldBefore + 50, $playerChar->gold, 'Gold must not double on a second approved post');
        $this->assertSame(
            1,
            EventParticipant::where('event_id', $event->id)->where('character_id', $playerChar->id)->count(),
            'Must still be exactly one event_participants row after a second approved post'
        );
        $this->assertSame(
            1,
            RewardLog::where('character_id', $playerChar->id)->where('reward_id', $reward->id)->count(),
            'RewardLog dedup must prevent a second reward grant for the same reward template'
        );
    }

    public function test_thread_without_event_is_completely_unaffected(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->makeCharacter($admin, 'AdminChar2');

        $player     = User::factory()->create(['role' => UserRole::Player]);
        $playerChar = $this->makeCharacter($player, 'PlayerChar2');

        $city = $this->makeCity();

        $this->actingAs($admin)
            ->post(route('thread.store', $city->id), [
                'title'   => 'Plain Thread',
                'content' => 'Opening post',
                'action'  => 'submit',
                // no event_id at all
            ])
            ->assertRedirect();

        $thread = Thread::where('title', 'Plain Thread')->firstOrFail();
        $this->assertNull($thread->event_id);
        $this->assertNull($thread->fresh()->display_tag, 'No thread_category and no event => no tag, same as before');

        $this->actingAs($player)
            ->post(route('post.store', $thread->id), ['content' => 'A reply'])
            ->assertRedirect();

        $post = Post::where('thread_id', $thread->id)->where('character_id', $playerChar->id)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('post.approve', $post->id))
            ->assertRedirect();

        $this->assertDatabaseCount('event_participants', 0);
        $this->assertDatabaseCount('reward_logs', 0);
        $this->assertDatabaseCount('inventories', 0);

        // require_approval=true, no exp_override, no event => resolveExpAmount() returns 0
        $playerChar->refresh();
        $this->assertSame(0, $playerChar->stats->fresh()->exp);
    }

    public function test_thread_category_tag_still_renders_unchanged_when_no_event_linked(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $city  = $this->makeCity();

        $thread = Thread::create([
            'city_id'         => $city->id,
            'created_by'      => $admin->id,
            'title'           => 'Announcement Thread',
            'status'          => 'approved',
            'thread_category' => 'announcement',
        ]);

        $tag = $thread->fresh()->display_tag;
        $this->assertNotNull($tag);
        $this->assertSame(\App\Enums\ThreadCategory::Announcement->getLabel(), $tag['label']);
        $this->assertSame(\App\Enums\ThreadCategory::Announcement->bgHex(), $tag['bg']);
        $this->assertSame(\App\Enums\ThreadCategory::Announcement->textHex(), $tag['text']);
    }

    public function test_reward_resource_form_uses_searchable_relationship_selects_for_event_and_item(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->makeCharacter($admin, 'SuperAdminChar');

        $event = Event::create([
            'title' => 'Dropdown Test Event', 'type' => 'flash',
            'status' => 'active', 'created_by' => $admin->id, 'exp_reward' => 1,
        ]);
        $item = Item::create(['name' => 'Dropdown Test Item', 'type' => 'material']);

        $this->actingAs($admin, 'admin');

        $livewire = Livewire::test(CreateReward::class)
            ->assertFormFieldExists('event_id')
            ->assertFormFieldExists('item_id')
            ->fillForm([
                'event_id'      => $event->id,
                'item_id'       => $item->id,
                'item_quantity' => 1,
                'gold_amount'   => 0,
                'exp_amount'    => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('rewards', [
            'event_id' => $event->id,
            'item_id'  => $item->id,
        ]);

        // The fields must actually be Select components bound to the event/item
        // RELATIONSHIP (resolving by name, searchable) — this is what distinguishes
        // them from the old plain TextInput::make('event_id')->numeric() fields,
        // which would accept the same raw ID but have no relationship/search at all.
        $form = Livewire::test(CreateReward::class)->instance()->getForm('form');

        $eventField = collect($form->getComponents())->first(fn ($c) => $c->getName() === 'event_id');
        $itemField  = collect($form->getComponents())->first(fn ($c) => $c->getName() === 'item_id');

        $this->assertInstanceOf(\Filament\Forms\Components\Select::class, $eventField);
        $this->assertSame('event', $eventField->getRelationshipName());
        $this->assertTrue($eventField->isSearchable());

        $this->assertInstanceOf(\Filament\Forms\Components\Select::class, $itemField);
        $this->assertSame('item', $itemField->getRelationshipName());
        $this->assertTrue($itemField->isSearchable());
    }
}
