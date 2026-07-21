<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecentActivityController extends Controller
{
    public function index()
    {
        $character = Auth::user()->character;
        $currentCharacter = $character?->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])->loadCount('posts');

        $threads = collect();
        if ($character) {
            $threads = Post::where('character_id', $character->id)
                ->with(['thread.city.kingdom'])
                ->select('thread_id', DB::raw('MAX(created_at) as last_posted_at'), DB::raw('COUNT(*) as post_count'))
                ->groupBy('thread_id')
                ->orderByDesc('last_posted_at')
                ->get()
                ->map(fn ($row) => [
                    'thread'         => $row->thread,
                    'last_posted_at' => $row->last_posted_at,
                    'post_count'     => $row->post_count,
                ])
                ->filter(fn ($row) => $row['thread'] !== null)
                ->values();
        }

        return view('activity', compact('threads', 'currentCharacter'));
    }
}
