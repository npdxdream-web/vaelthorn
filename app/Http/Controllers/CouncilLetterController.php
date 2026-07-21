<?php

namespace App\Http\Controllers;

use App\Models\CouncilLetter;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouncilLetterController extends Controller
{
    public function store(Request $request, NotificationService $notifications)
    {
        $character = Auth::user()->character;

        if (! $character) {
            return back()->with('error', 'กรุณาสร้างตัวละครก่อน');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:150',
            'body'    => 'required|string|max:2000',
        ]);

        $letter = CouncilLetter::create([
            'character_id' => $character->id,
            'subject'      => $validated['subject'],
            'body'         => $validated['body'],
            'status'       => 'pending',
        ]);

        $notifications->notifyCouncilLetterReceived($letter);

        return back()->with('success', 'ส่งจดหมายถึงสภาแล้ว');
    }

    public function show($id)
    {
        $letter = CouncilLetter::with(['character.user', 'repliedBy'])->findOrFail($id);

        $user = Auth::user();
        $isOwner = $letter->character?->user_id === $user->id;

        if (! $isOwner && ! $user->isAtLeastModerator()) {
            abort(403);
        }

        $currentCharacter = $user->character
            ?->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])
            ->loadCount('posts');

        return view('council.show', compact('letter', 'currentCharacter', 'isOwner'));
    }

    public function reply(Request $request, $id, NotificationService $notifications)
    {
        if (! Auth::user()->isAtLeastModerator()) {
            abort(403);
        }

        $letter = CouncilLetter::findOrFail($id);

        $validated = $request->validate([
            'admin_reply' => 'required|string|max:2000',
        ]);

        $letter->update([
            'admin_reply' => $validated['admin_reply'],
            'status'      => 'answered',
            'replied_by'  => Auth::id(),
            'replied_at'  => now(),
        ]);

        $notifications->notifyCouncilLetterReplied($letter);

        return redirect()->route('council.show', $letter->id)->with('success', 'ส่งคำตอบแล้ว');
    }
}
