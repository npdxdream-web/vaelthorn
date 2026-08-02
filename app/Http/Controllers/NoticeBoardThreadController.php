<?php

namespace App\Http\Controllers;

use App\Models\NoticeBoard;
use App\Models\NoticeBoardPost;
use App\Models\NoticeBoardThread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoticeBoardThreadController extends Controller
{
    public function create($noticeBoardId)
    {
        abort_unless(Auth::user()->isAtLeastAdmin(), 403);

        $noticeBoard = NoticeBoard::findOrFail($noticeBoardId);
        $currentCharacter = Auth::user()->character;

        return view('notice-board-thread-create', compact('noticeBoard', 'currentCharacter'));
    }

    public function store(Request $request, $noticeBoardId)
    {
        abort_unless(Auth::user()->isAtLeastAdmin(), 403);

        $noticeBoard = NoticeBoard::findOrFail($noticeBoardId);

        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string|min:1',
        ]);

        $thread = NoticeBoardThread::create([
            'notice_board_id' => $noticeBoard->id,
            'created_by'      => Auth::id(),
            'title'           => $validated['title'],
        ]);

        NoticeBoardPost::create([
            'notice_board_thread_id' => $thread->id,
            'created_by'             => Auth::id(),
            'content'                => $validated['content'],
        ]);

        return redirect()->route('notice-board.thread.show', $thread->id)->with('success', 'สร้างกระทู้แล้ว');
    }

    public function show($id)
    {
        $thread = NoticeBoardThread::with(['noticeBoard', 'creator'])->findOrFail($id);
        $posts  = $thread->posts()->with('creator')->oldest()->get();
        $currentCharacter = Auth::user()->character;

        return view('notice-board-thread', compact('thread', 'posts', 'currentCharacter'));
    }

    public function storePost(Request $request, $id)
    {
        abort_unless(Auth::user()->isAtLeastAdmin(), 403);

        $thread = NoticeBoardThread::findOrFail($id);

        $validated = $request->validate(['content' => 'required|string|min:1']);

        NoticeBoardPost::create([
            'notice_board_thread_id' => $thread->id,
            'created_by'             => Auth::id(),
            'content'                => $validated['content'],
        ]);

        return back()->with('success', 'ตอบกระทู้แล้ว');
    }
}
