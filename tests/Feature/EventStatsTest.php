<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\EventResource\Pages\ListEvents;
use App\Models\Character;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Item;
use App\Models\Reward;
use App\Models\RewardLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers Phase 5: participant/reward stats surfaced as read-only columns on
 * the existing EventResource table — no new table/migration, since
 * event_participants + reward_logs (from Phase 1) already hold everything
 * these numbers need.
 */
class EventStatsTest extends TestCase
{
    use RefreshDatabase;

    private function makeCharacter(User $user, string $name): Character
    {
        $character = $user->character()->create(['name' => $name, 'status' => 'active']);
        $character->stats()->create(['level' => 1, 'exp' => 0, 'exp_to_next' => 10]);

        return $character->fresh();
    }

    public function test_event_table_shows_participant_count_and_reward_rate(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->makeCharacter($admin, 'AdminChar');

        $rewardedPlayer    = $this->makeCharacter(User::factory()->create(['role' => UserRole::Player]), 'Rewarded');
        $unrewardedPlayer  = $this->makeCharacter(User::factory()->create(['role' => UserRole::Player]), 'Unrewarded');

        $event = Event::create([
            'title' => 'Stats Test Event', 'type' => 'flash',
            'status' => 'active', 'created_by' => $admin->id, 'exp_reward' => 1,
        ]);

        EventParticipant::create(['event_id' => $event->id, 'character_id' => $rewardedPlayer->id, 'joined_at' => now()]);
        EventParticipant::create(['event_id' => $event->id, 'character_id' => $unrewardedPlayer->id, 'joined_at' => now()]);

        $item   = Item::create(['name' => 'Stats Test Item', 'type' => 'material']);
        $reward = Reward::create(['event_id' => $event->id, 'item_id' => $item->id, 'item_quantity' => 1, 'gold_amount' => 0, 'exp_amount' => 0]);

        RewardLog::create([
            'character_id' => $rewardedPlayer->id, 'event_id' => $event->id, 'reward_id' => $reward->id,
            'item_id' => $item->id, 'item_quantity' => 1, 'gold_received' => 0, 'exp_received' => 0, 'given_at' => now(),
        ]);

        $this->actingAs($admin, 'admin');

        Livewire::test(ListEvents::class)
            ->assertSee('2') // participants_count
            ->assertSee('1/2 คน'); // reward_stats
    }
}
