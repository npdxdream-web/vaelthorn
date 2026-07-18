<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private const FILTER_MAP = [
        'post'        => ['post_approved', 'post_rejected', 'thread_reply', 'thread_locked'],
        'event'       => ['event_started', 'event_ending_soon'],
        'reward'      => ['item_received'],
        'progression' => ['level_up', 'badge_awarded'],
        'system'      => ['system_announcement'],
    ];

    public function index()
    {
        $currentCharacter = Auth::user()->character?->load(['city', 'currentCity', 'stats', 'badges'])->loadCount('posts');

        $filter = request('filter', 'all');
        $query  = Notification::where('user_id', Auth::id())->latest();

        if ($filter !== 'all' && isset(self::FILTER_MAP[$filter])) {
            $query->whereIn('type', self::FILTER_MAP[$filter]);
        }

        $notifications = $query->paginate(20)->withQueryString();
        $unreadCount   = Notification::where('user_id', Auth::id())->unread()->count();

        return view('notifications', compact('notifications', 'currentCharacter', 'unreadCount', 'filter'));
    }

    public function open($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->url;

        return $url ? redirect($url) : redirect()->route('notifications.index');
    }

    public function markRead($id)
    {
        Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->first()
            ?->markAsRead();

        return back();
    }

    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        return back()->with('success', 'อ่านทั้งหมดแล้ว');
    }
}
