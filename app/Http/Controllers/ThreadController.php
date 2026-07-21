<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Notification;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\Thread;
use App\Models\User;
use App\Services\LevelingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThreadController extends Controller
{
    public function __construct(
        private NotificationService $notifications,
        private LevelingService $leveling,
    ) {}

    // ─── Blade Views ──────────────────────────────────────────────────────────

    public function show($id)
    {
        $thread = Thread::with(['city.kingdom', 'creator'])->findOrFail($id);
        $user   = Auth::user();
        $currentCharacter = $user->character;

        if (! $user->isAdminGroup() && ! $currentCharacter) {
            return redirect()->route('register')->with('warning', 'กรุณาสร้างตัวละครก่อนเข้าชมกระทู้');
        }

        // Non-admins cannot see non-public threads unless they own it
        if (! $user->isAtLeastModerator() && ! $thread->isPubliclyVisible()) {
            if ($thread->created_by !== $user->id) {
                abort(403, 'ไม่มีสิทธิ์ดูกระทู้นี้');
            }
        }

        $characterEager = [
            'character' => fn ($q) => $q->withCount('posts'),
            'character.kingdom',
            'character.currentKingdom',
            'character.stats',
            'character.badges',
        ];

        if ($user->isAtLeastModerator()) {
            $posts = Post::where('thread_id', $thread->id)
                ->with([...$characterEager, 'character.user'])
                ->oldest()->get();
        } elseif ($currentCharacter) {
            $posts = Post::where('thread_id', $thread->id)
                ->where(function ($q) use ($currentCharacter) {
                    $q->where('status', 'approved')
                      ->orWhere('character_id', $currentCharacter->id);
                })
                ->with($characterEager)
                ->oldest()->get();
        } else {
            $posts = Post::where('thread_id', $thread->id)
                ->where('status', 'approved')
                ->with($characterEager)
                ->oldest()->get();
        }

        $participants = $posts->where('status', 'approved')->pluck('character')->unique('id')->filter()->values();
        $cities       = City::with('kingdom')->orderBy('name')->get();

        $notices     = $currentCharacter
            ? Notification::where('user_id', Auth::id())
                ->orderByRaw('read_at IS NULL DESC')->latest()->take(5)->get()
            : collect();
        $unreadCount = $notices->filter(fn ($n) => $n->read_at === null)->count();

        $myPosts = $currentCharacter
            ? $posts->where('character_id', $currentCharacter->id)->values()
            : collect();

        return view('thread', compact(
            'thread', 'posts', 'participants', 'currentCharacter', 'cities',
            'notices', 'unreadCount', 'myPosts'
        ));
    }

    public function create($cityId)
    {
        $city = City::with('kingdom')->findOrFail($cityId);
        $user = Auth::user();

        if (! $user->isAdminGroup() && ! $user->character) {
            return redirect()->route('register')->with('warning', 'กรุณาสร้างตัวละครก่อนสร้างกระทู้');
        }

        if (! $city->canWrite($user, $user->character)) {
            return redirect()->route('city', $city->id)->with('error', 'คุณไม่มีสิทธิ์เขียนในพื้นที่นี้');
        }

        return view('thread-create', compact('city'));
    }

    public function storeThread(Request $request, $cityId)
    {
        $city      = City::findOrFail($cityId);
        $user      = Auth::user();
        $character = $user->character;

        if (! $character) {
            return redirect()->route('register')->with('warning', 'กรุณาสร้างตัวละครก่อนสร้างกระทู้');
        }

        $stats = $character->stats;
        if ($stats && $stats->level < 1) {
            return back()->with('error', 'คุณยังไม่ผ่าน Onboarding — กรุณาทำแบบทดสอบให้เสร็จก่อน');
        }

        // City write gate (Level 1+)
        if (! $city->canWrite($user, $character)) {
            return back()->with('error', 'คุณไม่มีสิทธิ์เขียนในพื้นที่นี้');
        }

        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string|min:1',
            'action'  => 'in:draft,submit',
        ]);

        $isLivePost = ($stats?->level >= 1) && ! $city->require_approval;

        $postStatus   = $isLivePost ? 'approved' : 'pending';
        $threadStatus = $isLivePost
            ? 'approved'
            : ($request->input('action') === 'draft' ? 'draft' : 'pending');

        $thread = Thread::create([
            'city_id'    => $city->id,
            'created_by' => $user->id,
            'title'      => $request->input('title'),
            'status'     => $threadStatus,
        ]);

        $post = Post::create([
            'thread_id'    => $thread->id,
            'character_id' => $character->id,
            'content'      => $request->input('content'),
            'status'       => $postStatus,
        ]);

        if ($isLivePost) {
            $this->dispatchThreadReplyNotifications($thread, $post);
            $this->leveling->handlePostApproved($post);
            $msg = 'โพสต์ขึ้นแล้ว!';
        } else {
            $msg = $threadStatus === 'draft' ? 'บันทึกฉบับร่างแล้ว' : 'ส่งกระทู้เพื่อรออนุมัติแล้ว';
        }

        return redirect()->route('thread', $thread->id)->with('success', $msg);
    }

    public function edit($id)
    {
        $thread = Thread::with(['city.kingdom'])->findOrFail($id);
        $user   = Auth::user();

        if (! $user->isAdminGroup()) {
            if ($thread->created_by !== $user->id) {
                abort(403);
            }
            if (! in_array($thread->status, ['pending', 'draft', 'request_edit'], true)) {
                abort(403, 'ไม่สามารถแก้ไขกระทู้ที่ได้รับการอนุมัติหรือปิดแล้ว');
            }
        }

        $cities = City::with('kingdom')->orderBy('name')->get();

        return view('thread-edit', compact('thread', 'cities'));
    }

    public function update(Request $request, $id)
    {
        $thread = Thread::findOrFail($id);
        $user   = Auth::user();

        if (! $user->isAdminGroup()) {
            if ($thread->created_by !== $user->id) {
                abort(403);
            }
            if (! in_array($thread->status, ['pending', 'draft', 'request_edit'], true)) {
                abort(403);
            }
        }

        $request->validate([
            'title'          => 'required|string|max:255',
            'location_label' => 'nullable|string|max:100',
        ]);

        $data = [
            'title'          => $request->input('title'),
            'location_label' => $request->input('location_label'),
        ];

        if ($user->isAdminGroup()) {
            if ($request->filled('city_id')) {
                $data['city_id'] = $request->input('city_id');
            }
            if ($request->filled('status')) {
                $data['status'] = $request->input('status');
            }
        } elseif ($thread->status === 'request_edit') {
            $data['status']              = 'pending';
            $data['moderation_message']  = null;
        }

        $thread->update($data);

        return redirect()->route('thread', $thread->id)->with('success', 'อัปเดตกระทู้แล้ว');
    }

    public function moderate(Request $request, $id)
    {
        $thread = Thread::findOrFail($id);
        $user   = Auth::user();

        if (! $user->isAtLeastModerator()) {
            abort(403);
        }

        $action = $request->input('action');

        if ($action === 'approve') {
            $thread->update(['status' => 'approved', 'moderation_message' => null]);
        } elseif ($action === 'lock') {
            $thread->update(['status' => 'locked']);
            $this->notifications->notifyThreadLocked($thread);
        } elseif ($action === 'archive') {
            $thread->update(['status' => 'archived', 'archived_at' => now()]);
            $this->notifications->notifyThreadLocked($thread);
        } elseif ($action === 'request_edit') {
            $request->validate(['message' => 'required|string|max:1000']);
            $thread->update(['status' => 'request_edit', 'moderation_message' => $request->input('message')]);
        } elseif ($action === 'reject') {
            $request->validate(['message' => 'required|string|max:1000']);
            $thread->update(['status' => 'rejected', 'moderation_message' => $request->input('message')]);
        } elseif ($action === 'move') {
            $request->validate(['city_id' => 'required|exists:cities,id']);
            $thread->update(['city_id' => $request->input('city_id')]);
        } elseif ($action === 'unlock') {
            $thread->update(['status' => 'approved']);
        } elseif ($action === 'unarchive') {
            $thread->update(['status' => 'approved']);
        } else {
            abort(422, 'Unknown action');
        }

        return back()->with('success', 'ดำเนินการสำเร็จ');
    }

    public function destroy($id)
    {
        $thread = Thread::findOrFail($id);
        $user   = Auth::user();

        if (! $user->isAtLeastAdmin() && $thread->created_by !== $user->id) {
            abort(403);
        }

        $cityId = $thread->city_id;
        $thread->delete(); // soft delete — restorable within 3 days

        return redirect()->route('city', $cityId)
            ->with('success', 'ย้ายกระทู้ไปถังขยะแล้ว — Admin กู้คืนได้ภายใน 3 วัน');
    }

    public function restore($id)
    {
        $thread = Thread::withTrashed()->findOrFail($id);
        $user   = Auth::user();

        if (! $user->isAtLeastAdmin()) {
            abort(403);
        }

        $thread->restore();

        return back()->with('success', 'กู้คืนกระทู้แล้ว');
    }

    public function forceDestroy($id)
    {
        $thread = Thread::withTrashed()->findOrFail($id);
        $user   = Auth::user();

        if (! $user->isAtLeastAdmin()) {
            abort(403);
        }

        $thread->forceDelete();

        return back()->with('success', 'ลบกระทู้ถาวรแล้ว');
    }

    public function approvePost($id)
    {
        $post = Post::with(['character.user', 'thread'])->findOrFail($id);

        if (! Auth::user()->isAtLeastModerator()) {
            abort(403);
        }

        $post->update(['status' => 'approved']);

        $this->notifications->notifyPostApproved($post);
        $this->dispatchThreadReplyNotifications($post->thread, $post);
        $this->leveling->handlePostApproved($post);

        return back()->with('success', 'อนุมัติโพสต์แล้ว');
    }

    public function editPost($id)
    {
        $post = Post::with(['thread', 'character'])->findOrFail($id);
        $user = Auth::user();

        if (! $user->isAdminGroup()) {
            if ($post->character->user_id !== $user->id) {
                abort(403);
            }
            if ($post->status !== 'pending') {
                abort(403, 'ไม่สามารถแก้ไขโพสต์ที่อนุมัติแล้ว');
            }
        }

        return view('post-edit', compact('post'));
    }

    public function updatePost(Request $request, $id)
    {
        $post = Post::with(['thread', 'character'])->findOrFail($id);
        $user = Auth::user();

        if (! $user->isAdminGroup()) {
            if ($post->character->user_id !== $user->id) {
                abort(403);
            }
            if ($post->status !== 'pending') {
                abort(403);
            }
        }

        $request->validate(['content' => 'required|string|min:1']);
        $post->update(['content' => $request->input('content')]);

        return redirect()->route('thread', $post->thread_id)->with('success', 'แก้ไขโพสต์แล้ว');
    }

    public function destroyPost($id)
    {
        $post = Post::with(['thread', 'character'])->findOrFail($id);
        $user = Auth::user();

        if (! $user->isAdminGroup()) {
            if ($post->character->user_id !== $user->id) {
                abort(403);
            }
            if ($post->status !== 'pending') {
                abort(403);
            }
        }

        $threadId = $post->thread_id;
        $post->delete();

        $remainingPosts = Post::where('thread_id', $threadId)->count();
        if ($remainingPosts === 0) {
            return redirect()->route('thread', $threadId)
                ->with('confirm_delete_thread', true);
        }

        return redirect()->route('thread', $threadId)->with('success', 'ลบโพสต์แล้ว');
    }

    // ─── Witness System ───────────────────────────────────────────────────────

    public function reactPost(Request $request, $id)
    {
        $post      = Post::findOrFail($id);
        $character = Auth::user()->character;

        if (! $character) {
            return back()->with('error', 'กรุณาสร้างตัวละครก่อน');
        }
        if ($post->character_id === $character->id) {
            return back()->with('error', 'ไม่สามารถ Witness โพสต์ของตัวเองได้');
        }
        if ($post->status !== 'approved') {
            return back()->with('error', 'โพสต์ยังไม่ได้รับการอนุมัติ');
        }

        $allowed = ['witness', 'inspired', 'moved'];
        $type    = $request->input('type', 'witness');
        if (! in_array($type, $allowed, true)) {
            abort(422);
        }

        $existing = PostReaction::where('post_id', $post->id)
            ->where('character_id', $character->id)
            ->where('type', $type)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            PostReaction::create([
                'post_id'      => $post->id,
                'character_id' => $character->id,
                'type'         => $type,
            ]);
        }

        return back();
    }

    // ─── Blade: reply store ────────────────────────────────────────────────────

    public function store(Request $request, $id)
    {
        $thread    = Thread::with('city')->findOrFail($id);
        $user      = Auth::user();
        $character = $user->character;

        if (! $character) {
            return redirect()->route('register')->with('warning', 'กรุณาสร้างตัวละครก่อนส่งโพสต์');
        }

        $request->validate(['content' => 'required|string|min:1']);

        $stats = $character->stats;
        $city  = $thread->city;

        if ($stats && $stats->level < 1) {
            return back()->with('error', 'คุณยังไม่ผ่าน Onboarding — กรุณาทำแบบทดสอบให้เสร็จก่อน');
        }

        // City write gate (Level 1+)
        if ($city && ! $city->canWrite($user, $character)) {
            return back()->with('error', 'คุณไม่มีสิทธิ์เขียนในพื้นที่นี้');
        }

        $isLivePost = ($stats?->level >= 1) && ! ($city?->require_approval);
        $postStatus = $isLivePost ? 'approved' : 'pending';

        $post = Post::create([
            'thread_id'    => $thread->id,
            'character_id' => $character->id,
            'content'      => $request->content,
            'status'       => $postStatus,
        ]);

        if ($isLivePost) {
            $this->dispatchThreadReplyNotifications($thread, $post);
            $this->leveling->handlePostApproved($post);
            return back()->with('success', 'โพสต์ขึ้นแล้ว!');
        }

        return back()->with('success', 'ส่งโพสต์แล้ว รอ Admin อนุมัติ');
    }

    // ─── API (React SPA) ───────────────────────────────────────────────────────

    public function apiPosts($id)
    {
        $thread   = Thread::with(['city.kingdom', 'author.kingdom'])->findOrFail($id);
        $user     = Auth::user();
        $character = $user?->character;
        $isAdmin  = $user?->isAtLeastModerator() ?? false;

        if ($isAdmin) {
            $postsQuery = Post::where('thread_id', $thread->id)
                ->with(['character.kingdom', 'character.stats', 'character.user']);
        } elseif ($character) {
            $postsQuery = Post::where('thread_id', $thread->id)
                ->where(function ($q) use ($character) {
                    $q->where('status', 'approved')
                      ->orWhere('character_id', $character->id);
                })
                ->with(['character.kingdom', 'character.stats']);
        } else {
            $postsQuery = Post::where('thread_id', $thread->id)
                ->where('status', 'approved')
                ->with(['character.kingdom', 'character.stats']);
        }

        $posts = $postsQuery->oldest()->get()->map(function (Post $post) use ($character, $isAdmin) {
            $isOwner  = $character && $post->character_id === $character->id;
            $canEdit  = $isAdmin || ($isOwner && $post->status === 'pending');
            return [
                'id'        => $post->id,
                'content'   => $post->content,
                'status'    => $post->status,
                'is_owner'  => $isOwner,
                'can_edit'  => $canEdit,
                'can_approve' => $isAdmin && $post->status === 'pending',
                'created_at' => $post->created_at->toDateTimeString(),
                'character' => [
                    'id'   => $post->character?->id,
                    'name' => $post->character?->name ?? 'Unknown',
                    'kingdom' => $post->character?->kingdom?->name,
                    'stats' => $post->character?->stats?->mapWithKeys(fn ($s) => [$s->name => $s->value])->toArray() ?? [],
                ],
            ];
        });

        return response()->json([
            'thread' => [
                'id'                 => $thread->id,
                'title'              => $thread->title,
                'status'             => $thread->status,
                'status_label'       => $thread->status_label,
                'moderation_message' => $thread->moderation_message,
                'created_by'         => $thread->created_by,
                'author' => [
                    'id'         => $thread->author?->id,
                    'name'       => $thread->author?->name ?? 'Unknown',
                    'avatar_url' => $thread->author?->avatar_url,
                    'kingdom'    => $thread->author?->kingdom?->name,
                ],
                'city' => [
                    'id'      => $thread->city->id,
                    'name'    => $thread->city->name,
                    'kingdom' => $thread->city->kingdom?->name,
                ],
            ],
            'posts'  => $posts,
            'viewer' => [
                'is_admin'     => $isAdmin,
                'character_id' => $character?->id,
                'user_id'      => $user?->id,
            ],
        ]);
    }

    public function apiStore(Request $request, $id)
    {
        $thread    = Thread::with('city')->findOrFail($id);
        $user      = Auth::user();
        $character = $user->character;

        if (! $character) {
            return response()->json(['message' => 'กรุณาสร้างตัวละครก่อนส่งโพสต์'], 403);
        }

        $request->validate(['content' => 'required|string|min:1']);

        $stats = $character->stats;
        $city  = $thread->city;

        if ($stats && $stats->level < 1) {
            return response()->json(['message' => 'คุณยังไม่ผ่าน Onboarding — กรุณาทำแบบทดสอบให้เสร็จก่อน'], 403);
        }

        // City write gate (Level 1+)
        if ($city && ! $city->canWrite($user, $character)) {
            return response()->json(['message' => 'คุณไม่มีสิทธิ์เขียนในพื้นที่นี้'], 403);
        }

        $isLivePost = ($stats?->level >= 1) && ! ($city?->require_approval);
        $postStatus = $isLivePost ? 'approved' : 'pending';

        $post = Post::create([
            'thread_id'    => $thread->id,
            'character_id' => $character->id,
            'content'      => $request->input('content'),
            'status'       => $postStatus,
        ]);

        if ($isLivePost) {
            $this->dispatchThreadReplyNotifications($thread, $post);
            $this->leveling->handlePostApproved($post);
            $message = 'โพสต์ขึ้นแล้ว!';
        } else {
            $message = 'โพสต์ของคุณถูกส่งแล้ว รอ Admin อนุมัติ';
        }

        return response()->json([
            'message' => $message,
            'post'    => [
                'id'          => $post->id,
                'content'     => $post->content,
                'status'      => $post->status,
                'is_owner'    => true,
                'can_edit'    => $post->status === 'pending',
                'can_approve' => false,
                'created_at'  => $post->created_at->toDateTimeString(),
            ],
        ], 201);
    }

    public function apiApprovePost($id)
    {
        $post = Post::with(['character.user', 'thread'])->findOrFail($id);

        if (! Auth::user()?->isAtLeastModerator()) {
            return response()->json(['message' => 'ไม่มีสิทธิ์'], 403);
        }

        $post->update(['status' => 'approved']);

        $this->notifications->notifyPostApproved($post);
        $this->dispatchThreadReplyNotifications($post->thread, $post);
        $this->leveling->handlePostApproved($post);

        return response()->json(['message' => 'อนุมัติโพสต์แล้ว']);
    }

    public function apiDestroyPost($id)
    {
        $post = Post::with('character')->findOrFail($id);
        $user = Auth::user();

        if (! $user->isAdminGroup()) {
            if ($post->character->user_id !== $user->id || $post->status !== 'pending') {
                return response()->json(['message' => 'ไม่มีสิทธิ์'], 403);
            }
        }

        $post->delete();

        return response()->json(['message' => 'ลบโพสต์แล้ว']);
    }

    public function apiUpdatePost(Request $request, $id)
    {
        $post = Post::with('character')->findOrFail($id);
        $user = Auth::user();

        if (! $user->isAdminGroup()) {
            if ($post->character->user_id !== $user->id || $post->status !== 'pending') {
                return response()->json(['message' => 'ไม่มีสิทธิ์'], 403);
            }
        }

        $request->validate(['content' => 'required|string|min:1']);
        $post->update(['content' => $request->input('content')]);

        return response()->json(['message' => 'แก้ไขโพสต์แล้ว']);
    }

    public function apiModerate(Request $request, $id)
    {
        $thread = Thread::findOrFail($id);
        $user   = Auth::user();

        if (! $user?->isAtLeastModerator()) {
            return response()->json(['message' => 'ไม่มีสิทธิ์'], 403);
        }

        $action = $request->input('action');

        if ($action === 'approve') {
            $thread->update(['status' => 'approved', 'moderation_message' => null]);
        } elseif ($action === 'lock') {
            $thread->update(['status' => 'locked']);
            $this->notifications->notifyThreadLocked($thread);
        } elseif ($action === 'archive') {
            $thread->update(['status' => 'archived', 'archived_at' => now()]);
            $this->notifications->notifyThreadLocked($thread);
        } elseif ($action === 'request_edit') {
            $thread->update(['status' => 'request_edit', 'moderation_message' => $request->input('message')]);
        } elseif ($action === 'reject') {
            $thread->update(['status' => 'rejected', 'moderation_message' => $request->input('message')]);
        } elseif ($action === 'move') {
            $thread->update(['city_id' => $request->input('city_id')]);
        } elseif ($action === 'unlock') {
            $thread->update(['status' => 'approved']);
        } elseif ($action === 'unarchive') {
            $thread->update(['status' => 'approved']);
        }

        return response()->json(['message' => 'ดำเนินการสำเร็จ', 'status' => $thread->fresh()->status]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function dispatchThreadReplyNotifications(Thread $thread, Post $approvedPost): void
    {
        $posterUserId = $approvedPost->character?->user_id;

        $participantUserIds = Post::where('thread_id', $thread->id)
            ->where('id', '!=', $approvedPost->id)
            ->where('status', 'approved')
            ->with('character:id,user_id')
            ->get()
            ->pluck('character.user_id')
            ->push($thread->created_by)
            ->unique()
            ->filter()
            ->reject(fn ($uid) => $uid === $posterUserId);

        $recipients = User::whereIn('id', $participantUserIds)->get();

        foreach ($recipients as $recipient) {
            $this->notifications->notifyThreadReply($thread, $recipient, $approvedPost);
        }
    }
}
