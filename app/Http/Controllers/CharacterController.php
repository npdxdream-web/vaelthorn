<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CharacterController extends Controller
{
    public function show($id)
    {
        $character = Character::with([
            'user',
            'city',
            'currentCity',
            'stats',
            'badges',
            'events' => fn ($q) => $q->where('status', 'active')->with('city'),
        ])->withCount('posts')->findOrFail($id);

        $recentPosts = Post::where('character_id', $character->id)
            ->where('status', 'approved')
            ->with('thread.village.city')
            ->latest()
            ->take(5)
            ->get();

        $currentCharacter = Auth::user()->character;

        return view('character', compact('character', 'recentPosts', 'currentCharacter'));
    }

    public function edit()
    {
        $character = Auth::user()->character;

        if (! $character) {
            return redirect()->route('home');
        }

        $currentCharacter = $character->load(['city', 'currentCity', 'stats', 'badges'])->loadCount('posts');

        return view('character-edit', compact('character', 'currentCharacter'));
    }

    public function update(Request $request)
    {
        $character = Auth::user()->character;

        if (! $character) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'title'     => 'nullable|string|max:100',
            'backstory' => 'nullable|string|max:5000',
            'avatar'    => 'nullable|url|max:500',
        ]);

        $character->update($validated);

        return redirect()->route('character.show', $character->id)
            ->with('success', 'อัปเดตตัวละครเรียบร้อยแล้ว');
    }

    public function allocateStat(Request $request)
    {
        $character = Auth::user()->character;

        if (! $character) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'stat'   => 'required|in:str,agi,int,hp,mana',
            'points' => 'required|integer|min:1|max:50',
        ]);

        $stats = $character->stats;

        if (! $stats || $stats->stat_points_available < $validated['points']) {
            return back()->with('error', 'แต้ม stat ไม่เพียงพอ');
        }

        DB::transaction(function () use ($stats, $validated) {
            $stats->decrement('stat_points_available', $validated['points']);
            $stats->increment($validated['stat'], $validated['points']);
        });

        return back()->with('success', strtoupper($validated['stat']) . ' +' . $validated['points']);
    }
}
