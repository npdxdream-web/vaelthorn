<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Event;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;

class NotificationService
{
    public function notifyPostApproved(Post $post): void
    {
        $post->loadMissing(['character.user', 'thread']);

        $user = $post->character?->user;
        if (! $user) {
            return;
        }

        Notification::create([
            'user_id'   => $user->id,
            'type'      => 'post_approved',
            'title'     => 'โพสต์ของคุณได้รับการอนุมัติแล้ว',
            'body'      => "ใน \"{$post->thread->title}\"",
            'data'      => ['post_id' => $post->id, 'thread_id' => $post->thread_id],
            'link_type' => 'thread',
            'link_id'   => $post->thread_id,
        ]);
    }

    public function notifyPostRejected(Post $post, ?string $reason = null): void
    {
        $post->loadMissing(['character.user', 'thread']);

        $user = $post->character?->user;
        if (! $user) {
            return;
        }

        Notification::create([
            'user_id'   => $user->id,
            'type'      => 'post_rejected',
            'title'     => 'โพสต์ของคุณถูกตีกลับ',
            'body'      => $reason ?? "ใน \"{$post->thread->title}\"",
            'data'      => ['post_id' => $post->id, 'thread_id' => $post->thread_id, 'reason' => $reason],
            'link_type' => 'thread',
            'link_id'   => $post->thread_id,
        ]);
    }

    public function notifyItemReceived(User $user, Item $item, int $quantity): void
    {
        Notification::create([
            'user_id'   => $user->id,
            'type'      => 'item_received',
            'title'     => "ได้รับไอเทม: {$item->name}",
            'body'      => "จำนวน ×{$quantity}",
            'data'      => ['item_id' => $item->id, 'item_name' => $item->name, 'quantity' => $quantity],
            'link_type' => 'inventory',
            'link_id'   => null,
        ]);
    }

    public function notifyEventEndingSoon(Event $event, User $user): void
    {
        Notification::create([
            'user_id'   => $user->id,
            'type'      => 'event_ending_soon',
            'title'     => 'Event กำลังจะสิ้นสุด',
            'body'      => "\"{$event->title}\" จะปิดในอีกไม่นาน",
            'data'      => ['event_id' => $event->id, 'event_title' => $event->title, 'end_at' => $event->end_at?->toDateTimeString()],
            'link_type' => 'event',
            'link_id'   => $event->id,
        ]);
    }

    public function notifyThreadReply(Thread $thread, User $recipient, Post $newPost): void
    {
        $newPost->loadMissing('character');

        if ($newPost->character?->user_id === $recipient->id) {
            return;
        }

        Notification::create([
            'user_id'   => $recipient->id,
            'type'      => 'thread_reply',
            'title'     => 'มีโพสต์ใหม่ใน Thread ที่คุณมีส่วนร่วม',
            'body'      => "\"{$thread->title}\"",
            'data'      => ['thread_id' => $thread->id, 'post_id' => $newPost->id],
            'link_type' => 'thread',
            'link_id'   => $thread->id,
        ]);
    }

    public function notifyThreadLocked(Thread $thread): void
    {
        $thread->loadMissing('posts.character');

        $userIds = collect([$thread->created_by]);
        foreach ($thread->posts as $post) {
            if ($post->character?->user_id) {
                $userIds->push($post->character->user_id);
            }
        }
        $userIds = $userIds->unique()->filter();

        foreach ($userIds as $userId) {
            Notification::create([
                'user_id'   => $userId,
                'type'      => 'thread_locked',
                'title'     => 'Thread ที่คุณมีส่วนร่วมถูกล็อค',
                'body'      => "\"{$thread->title}\"",
                'data'      => ['thread_id' => $thread->id, 'thread_title' => $thread->title],
                'link_type' => 'thread',
                'link_id'   => $thread->id,
            ]);
        }
    }

    public function notifyLevelUp(Character $character, int $oldLevel, int $newLevel): void
    {
        Notification::create([
            'user_id'   => $character->user_id,
            'type'      => 'level_up',
            'title'     => 'เลเวลอัพ! ยินดีด้วย',
            'body'      => "เลเวล {$oldLevel} → {$newLevel} — คุณได้รับ Stat Point เพิ่ม",
            'data'      => ['old_level' => $oldLevel, 'new_level' => $newLevel, 'character_id' => $character->id],
            'link_type' => 'character',
            'link_id'   => $character->id,
        ]);
    }

    public function notifyEventStarted(Event $event, User $user): void
    {
        Notification::create([
            'user_id'   => $user->id,
            'type'      => 'event_started',
            'title'     => 'มี Event ใหม่ในพื้นที่ของคุณ',
            'body'      => "\"{$event->title}\" เปิดแล้ว",
            'data'      => ['event_id' => $event->id, 'event_title' => $event->title],
            'link_type' => 'event',
            'link_id'   => $event->id,
        ]);
    }

    public function notifyBadgeAwarded(User $user, string $badgeName): void
    {
        Notification::create([
            'user_id'   => $user->id,
            'type'      => 'badge_awarded',
            'title'     => 'ได้รับ Badge ใหม่',
            'body'      => $badgeName,
            'data'      => ['badge_name' => $badgeName],
            'link_type' => 'character',
            'link_id'   => $user->character?->id,
        ]);
    }

    public function notifySystemAnnouncement(User $user, string $title, string $body): void
    {
        Notification::create([
            'user_id'   => $user->id,
            'type'      => 'system_announcement',
            'title'     => $title,
            'body'      => $body,
            'data'      => null,
            'link_type' => null,
            'link_id'   => null,
        ]);
    }

    public function notifyAdminsCharacterReady(Character $character): void
    {
        // Send only once per character — prevent duplicate admin pings
        $alreadySent = Notification::where('type', 'character_review_ready')
            ->whereJsonContains('data->character_id', $character->id)
            ->exists();

        if ($alreadySent) {
            return;
        }

        $admins = User::whereIn('role', ['moderator', 'admin', 'superadmin'])->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id'   => $admin->id,
                'type'      => 'character_review_ready',
                'title'     => 'ตัวละครใหม่รอการตรวจ',
                'body'      => "{$character->name} ผ่าน 3 ด่านครบแล้ว กรุณาตรวจและ approve",
                'data'      => ['character_id' => $character->id, 'character_name' => $character->name],
                'link_type' => 'character',
                'link_id'   => $character->id,
            ]);
        }
    }
}
