<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\NoticeBoard;
use App\Models\NoticeBoardPost;
use App\Models\NoticeBoardThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the NoticeBoard system (admin-only announcement boards, read-only
 * for players) — built before this session, had zero test coverage.
 */
class NoticeBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_thread_with_opening_post(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $noticeBoard = NoticeBoard::create(['name' => 'Announcements', 'color' => '#c8a84b', 'is_active' => true]);

        $this->actingAs($admin)
            ->post(route('notice-board.thread.store', $noticeBoard->id), [
                'title'   => 'Server maintenance',
                'content' => '<p>Downtime this weekend.</p>',
            ])
            ->assertRedirect();

        $thread = NoticeBoardThread::where('title', 'Server maintenance')->firstOrFail();
        $this->assertSame($admin->id, $thread->created_by);

        $this->assertDatabaseHas('notice_board_posts', [
            'notice_board_thread_id' => $thread->id,
            'created_by'             => $admin->id,
        ]);
    }

    public function test_player_cannot_create_thread_or_reply(): void
    {
        $player = User::factory()->create(['role' => UserRole::Player]);
        $noticeBoard = NoticeBoard::create(['name' => 'Announcements', 'color' => '#c8a84b', 'is_active' => true]);

        $this->actingAs($player)
            ->get(route('notice-board.thread.create', $noticeBoard->id))
            ->assertForbidden();

        $this->actingAs($player)
            ->post(route('notice-board.thread.store', $noticeBoard->id), [
                'title' => 'x', 'content' => 'x',
            ])
            ->assertForbidden();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $thread = NoticeBoardThread::create([
            'notice_board_id' => $noticeBoard->id, 'created_by' => $admin->id, 'title' => 'Existing',
        ]);

        $this->actingAs($player)
            ->post(route('notice-board.thread.post.store', $thread->id), ['content' => 'reply'])
            ->assertForbidden();
    }

    public function test_player_can_read_board_and_thread(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $player = User::factory()->create(['role' => UserRole::Player]);
        $player->character()->create(['name' => 'Reader', 'status' => 'active']);

        $noticeBoard = NoticeBoard::create(['name' => 'Announcements', 'color' => '#c8a84b', 'is_active' => true]);
        $thread = NoticeBoardThread::create([
            'notice_board_id' => $noticeBoard->id, 'created_by' => $admin->id, 'title' => 'Welcome',
        ]);
        NoticeBoardPost::create([
            'notice_board_thread_id' => $thread->id, 'created_by' => $admin->id, 'content' => '<p>Hello!</p>',
        ]);

        $this->actingAs($player)
            ->get(route('notice-board.show', $noticeBoard->id))
            ->assertOk()
            ->assertSee('Welcome');

        $this->actingAs($player)
            ->get(route('notice-board.thread.show', $thread->id))
            ->assertOk()
            ->assertSee('Hello!', false);
    }

    public function test_empty_board_does_not_error(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $noticeBoard = NoticeBoard::create(['name' => 'Empty Board', 'color' => '#c8a84b', 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('notice-board.show', $noticeBoard->id))
            ->assertOk()
            ->assertSee('ยังไม่มีกระทู้ในป้ายประกาศนี้');
    }
}
