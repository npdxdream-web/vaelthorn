<?php

namespace Tests\Feature;

use App\Models\FriendRequest;
use App\Models\User;
use App\Services\FriendService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FriendshipTest extends TestCase
{
    use RefreshDatabase;

    private function makeCharacter(string $name): array
    {
        $user = User::factory()->create();
        $character = $user->character()->create(['name' => $name, 'status' => 'active']);

        return [$user, $character];
    }

    public function test_send_request_creates_pending_row_and_notification(): void
    {
        [$userA, $charA] = $this->makeCharacter('Alice');
        [$userB, $charB] = $this->makeCharacter('Bob');

        $this->actingAs($userA)
            ->post(route('friend.request.store', $charB->id))
            ->assertRedirect();

        $this->assertDatabaseHas('friend_requests', [
            'from_character_id' => $charA->id,
            'to_character_id'   => $charB->id,
            'status'            => 'pending',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $userB->id,
            'type'    => 'friend_request',
        ]);
    }

    public function test_cannot_send_request_to_self(): void
    {
        [$userA, $charA] = $this->makeCharacter('Alice');

        $this->actingAs($userA)
            ->post(route('friend.request.store', $charA->id))
            ->assertSessionHasErrors('friend');

        $this->assertDatabaseCount('friend_requests', 0);
    }

    public function test_cannot_send_duplicate_pending_request(): void
    {
        [$userA, $charA] = $this->makeCharacter('Alice');
        [$userB, $charB] = $this->makeCharacter('Bob');

        $this->actingAs($userA)->post(route('friend.request.store', $charB->id));

        $this->actingAs($userA)
            ->post(route('friend.request.store', $charB->id))
            ->assertSessionHasErrors('friend');

        $this->assertDatabaseCount('friend_requests', 1);
    }

    public function test_accept_creates_friendship_both_sides_deletes_request_and_notifies_sender(): void
    {
        [$userA, $charA] = $this->makeCharacter('Alice');
        [$userB, $charB] = $this->makeCharacter('Bob');

        $this->actingAs($userA)->post(route('friend.request.store', $charB->id));
        $request = FriendRequest::first();

        $this->actingAs($userB)
            ->post(route('friend.request.accept', $request->id))
            ->assertRedirect(route('notifications.index'));

        $this->assertDatabaseMissing('friend_requests', ['id' => $request->id]);
        $this->assertDatabaseHas('friendships', ['character_id_1' => $charA->id, 'character_id_2' => $charB->id]);
        $this->assertDatabaseHas('friendships', ['character_id_1' => $charB->id, 'character_id_2' => $charA->id]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $userA->id,
            'type'    => 'friend_request_accepted',
        ]);
    }

    public function test_reject_keeps_row_and_blocks_rerequest_within_cooldown(): void
    {
        [$userA, $charA] = $this->makeCharacter('Alice');
        [$userB, $charB] = $this->makeCharacter('Bob');

        $this->actingAs($userA)->post(route('friend.request.store', $charB->id));
        $request = FriendRequest::first();

        $this->actingAs($userB)
            ->post(route('friend.request.reject', $request->id))
            ->assertRedirect(route('notifications.index'));

        $request->refresh();
        $this->assertSame('rejected', $request->status);
        $this->assertNull($request->pair_key);

        // Re-request immediately, in either direction, is blocked by the 24h cooldown.
        $this->actingAs($userA)
            ->post(route('friend.request.store', $charB->id))
            ->assertSessionHasErrors('friend');

        $this->actingAs($userB)
            ->post(route('friend.request.store', $charA->id))
            ->assertSessionHasErrors('friend');

        $this->assertDatabaseCount('friend_requests', 1);
    }

    public function test_rerequest_allowed_after_cooldown_expires(): void
    {
        [$userA, $charA] = $this->makeCharacter('Alice');
        [$userB, $charB] = $this->makeCharacter('Bob');

        $this->actingAs($userA)->post(route('friend.request.store', $charB->id));
        $request = FriendRequest::first();
        $this->actingAs($userB)->post(route('friend.request.reject', $request->id));

        // Backdate the rejection past the 24h cooldown window. `updated_at` isn't mass-
        // assignable and Eloquent normally stomps it back to now() on every save, so both
        // forceFill() (bypass guarded) and timestamps=false (skip the auto-touch) are needed.
        $request->timestamps = false;
        $request->forceFill(['updated_at' => now()->subDays(2)])->save();

        $this->actingAs($userA)
            ->post(route('friend.request.store', $charB->id))
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('friend_requests', [
            'from_character_id' => $charA->id,
            'to_character_id'   => $charB->id,
            'status'            => 'pending',
        ]);
        $this->assertDatabaseCount('friend_requests', 2);
    }

    public function test_unfriend_removes_both_rows(): void
    {
        [$userA, $charA] = $this->makeCharacter('Alice');
        [$userB, $charB] = $this->makeCharacter('Bob');

        $request = FriendRequest::create([
            'from_character_id' => $charA->id,
            'to_character_id'   => $charB->id,
            'status'            => 'pending',
            'pair_key'          => "{$charA->id}-{$charB->id}",
        ]);
        app(FriendService::class)->accept($request);

        $this->assertDatabaseCount('friendships', 2);

        $this->actingAs($userA)
            ->delete(route('friend.destroy', $charB->id))
            ->assertRedirect();

        $this->assertDatabaseCount('friendships', 0);
    }

    /**
     * Regression: an admin/mod account with no Character (a valid, real account
     * shape in this app — see AUDIT #3 for the same class of bug on the thread
     * page) used to hit an uncaught TypeError (500) here, since sendRequest()'s
     * $from parameter isn't nullable. Must fail gracefully instead.
     */
    public function test_user_without_character_gets_graceful_403_not_500(): void
    {
        $adminNoChar = User::factory()->create(['role' => \App\Enums\UserRole::Admin]);
        [, $charB] = $this->makeCharacter('Bob');

        $this->actingAs($adminNoChar)
            ->post(route('friend.request.store', $charB->id))
            ->assertForbidden();
    }

    public function test_non_recipient_cannot_accept_or_reject(): void
    {
        [$userA, $charA] = $this->makeCharacter('Alice');
        [$userB, $charB] = $this->makeCharacter('Bob');
        [$userC, $charC] = $this->makeCharacter('Carol');

        $this->actingAs($userA)->post(route('friend.request.store', $charB->id));
        $request = FriendRequest::first();

        $this->actingAs($userC)
            ->post(route('friend.request.accept', $request->id))
            ->assertForbidden();

        $this->actingAs($userC)
            ->post(route('friend.request.reject', $request->id))
            ->assertForbidden();
    }

    public function test_pair_key_unique_constraint_prevents_duplicate_pending_rows(): void
    {
        [, $charA] = $this->makeCharacter('Alice');
        [, $charB] = $this->makeCharacter('Bob');

        $pairKey = min($charA->id, $charB->id) . '-' . max($charA->id, $charB->id);

        FriendRequest::create([
            'from_character_id' => $charA->id,
            'to_character_id'   => $charB->id,
            'status'            => 'pending',
            'pair_key'          => $pairKey,
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        // Simulates a second near-simultaneous request in the opposite direction, bypassing the
        // service's app-level pre-check — the DB constraint is the last line of defense.
        FriendRequest::create([
            'from_character_id' => $charB->id,
            'to_character_id'   => $charA->id,
            'status'            => 'pending',
            'pair_key'          => $pairKey,
        ]);
    }

    public function test_controller_store_hit_twice_returns_graceful_error_not_500(): void
    {
        [$userA, $charA] = $this->makeCharacter('Alice');
        [, $charB] = $this->makeCharacter('Bob');

        $this->actingAs($userA)->post(route('friend.request.store', $charB->id))->assertRedirect();
        $this->actingAs($userA)->post(route('friend.request.store', $charB->id))->assertSessionHasErrors('friend');

        $this->assertDatabaseCount('friend_requests', 1);
    }
}
