<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression: Notification::getUrlAttribute() used to require link_id to be
 * truthy for every link_type, including 'inventory' — a static route with no
 * id segment. That silently broke the "view" link on every item/gold reward
 * notification (both are created with link_id = null by design).
 */
class NotificationUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_link_type_resolves_a_url_even_with_null_link_id(): void
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'user_id'   => $user->id,
            'type'      => 'gold_received',
            'title'     => 'ได้รับ Gold',
            'body'      => 'จำนวน 30 Gold',
            'data'      => ['gold_amount' => 30],
            'link_type' => 'inventory',
            'link_id'   => null,
        ]);

        $this->assertSame(route('inventory'), $notification->url);
    }

    public function test_thread_link_type_still_returns_null_without_a_link_id(): void
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'user_id'   => $user->id,
            'type'      => 'thread_reply',
            'title'     => 'test',
            'body'      => 'test',
            'link_type' => 'thread',
            'link_id'   => null,
        ]);

        $this->assertNull($notification->url);
    }
}
