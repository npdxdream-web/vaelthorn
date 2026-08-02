<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\EventResource\Pages\ListEvents;
use App\Models\City;
use App\Models\Character;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Kingdom;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the Filament "ปิด Event" row action (Phase 4): closing an Event
 * locks every Thread still linked to it and notifies each thread's
 * participants — it does NOT re-run reward distribution, since
 * LevelingService::distributeEventRewards already grants rewards in
 * real time on post approval (see Phase 3).
 */
class EventCloseActionTest extends TestCase
{
    use RefreshDatabase;

    private function makeCharacter(User $user, string $name): Character
    {
        $character = $user->character()->create(['name' => $name, 'status' => 'active']);
        $character->stats()->create(['level' => 1, 'exp' => 0, 'exp_to_next' => 10]);

        return $character->fresh();
    }

    public function test_closing_an_event_locks_its_threads_and_notifies_participants(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->makeCharacter($admin, 'AdminChar');

        $player     = User::factory()->create(['role' => UserRole::Player]);
        $playerChar = $this->makeCharacter($player, 'PlayerChar');

        $kingdom = Kingdom::create(['name' => 'Test Kingdom', 'is_active' => true]);
        $city    = City::create(['kingdom_id' => $kingdom->id, 'name' => 'Test City', 'write_min_level' => 0]);

        $event = Event::create([
            'title' => 'Closing Test Event', 'type' => 'flash',
            'status' => 'active', 'created_by' => $admin->id, 'exp_reward' => 1,
        ]);

        EventParticipant::create(['event_id' => $event->id, 'character_id' => $playerChar->id, 'joined_at' => now()]);

        $openThread = Thread::create([
            'city_id' => $city->id, 'event_id' => $event->id, 'created_by' => $admin->id,
            'title' => 'Open Thread', 'status' => 'approved',
        ]);
        Post::create([
            'thread_id' => $openThread->id, 'character_id' => $playerChar->id,
            'content' => 'A reply', 'status' => 'approved',
        ]);
        $alreadyLockedThread = Thread::create([
            'city_id' => $city->id, 'event_id' => $event->id, 'created_by' => $admin->id,
            'title' => 'Already Locked', 'status' => 'locked',
        ]);
        $unrelatedThread = Thread::create([
            'city_id' => $city->id, 'created_by' => $admin->id,
            'title' => 'Unrelated Thread', 'status' => 'approved',
        ]);

        $this->actingAs($admin, 'admin');

        Livewire::test(ListEvents::class)
            ->callTableAction('closeEvent', $event)
            ->assertHasNoTableActionErrors();

        $event->refresh();
        $this->assertSame('closed', $event->status);

        $this->assertSame('locked', $openThread->fresh()->status, 'Thread linked to the closed Event should be locked');
        $this->assertSame('locked', $alreadyLockedThread->fresh()->status, 'Already-locked thread stays locked');
        $this->assertSame('approved', $unrelatedThread->fresh()->status, 'Thread not linked to this Event must be untouched');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $player->id,
            'type'    => 'thread_locked',
        ]);
    }

    /**
     * Regression: closing an Event must never force an unmoderated thread
     * (pending/draft/rejected/request_edit) straight to 'locked' — 'locked'
     * is in Thread::isPubliclyVisible()'s list, so doing that would publish
     * never-approved content to every visitor, bypassing moderation.
     */
    public function test_closing_an_event_does_not_touch_unmoderated_threads(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->makeCharacter($admin, 'AdminChar3');

        $kingdom = Kingdom::create(['name' => 'Test Kingdom 2', 'is_active' => true]);
        $city    = City::create(['kingdom_id' => $kingdom->id, 'name' => 'Test City 2', 'write_min_level' => 0]);

        $event = Event::create([
            'title' => 'Event With Unmoderated Threads', 'type' => 'flash',
            'status' => 'active', 'created_by' => $admin->id, 'exp_reward' => 1,
        ]);

        $pendingThread = Thread::create([
            'city_id' => $city->id, 'event_id' => $event->id, 'created_by' => $admin->id,
            'title' => 'Pending Thread', 'status' => 'pending',
        ]);
        $draftThread = Thread::create([
            'city_id' => $city->id, 'event_id' => $event->id, 'created_by' => $admin->id,
            'title' => 'Draft Thread', 'status' => 'draft',
        ]);
        $rejectedThread = Thread::create([
            'city_id' => $city->id, 'event_id' => $event->id, 'created_by' => $admin->id,
            'title' => 'Rejected Thread', 'status' => 'rejected',
        ]);

        $this->actingAs($admin, 'admin');

        Livewire::test(ListEvents::class)
            ->callTableAction('closeEvent', $event)
            ->assertHasNoTableActionErrors();

        $this->assertSame('pending', $pendingThread->fresh()->status, 'A pending thread must not be force-locked on Event close');
        $this->assertSame('draft', $draftThread->fresh()->status, 'A draft thread must not be force-locked on Event close');
        $this->assertSame('rejected', $rejectedThread->fresh()->status, 'A rejected thread must not be force-locked on Event close');
    }

    public function test_close_action_is_hidden_for_a_non_active_event(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->makeCharacter($admin, 'AdminChar2');

        $event = Event::create([
            'title' => 'Already Closed', 'type' => 'flash',
            'status' => 'closed', 'created_by' => $admin->id, 'exp_reward' => 1,
        ]);

        $this->actingAs($admin, 'admin');

        Livewire::test(ListEvents::class)
            ->assertTableActionHidden('closeEvent', $event);
    }
}
