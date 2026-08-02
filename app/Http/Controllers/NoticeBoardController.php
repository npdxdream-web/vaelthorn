<?php

namespace App\Http\Controllers;

use App\Models\NoticeBoard;
use Illuminate\Support\Facades\Auth;

class NoticeBoardController extends Controller
{
    public function show($id)
    {
        $noticeBoard = NoticeBoard::findOrFail($id);
        $threads = $noticeBoard->threads()->with('creator')->withCount('posts')->latest()->get();
        $currentCharacter = Auth::user()->character;

        return view('notice-board', compact('noticeBoard', 'threads', 'currentCharacter'));
    }
}
