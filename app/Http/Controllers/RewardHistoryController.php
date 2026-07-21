<?php

namespace App\Http\Controllers;

use App\Models\RewardLog;
use Illuminate\Support\Facades\Auth;

class RewardHistoryController extends Controller
{
    public function index()
    {
        $character = Auth::user()->character;

        if (! $character) {
            return redirect()->route('home');
        }

        $logs = RewardLog::where('character_id', $character->id)
            ->with(['event.kingdom', 'item'])
            ->latest('given_at')
            ->paginate(20);

        $currentCharacter = $character->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])->loadCount('posts');

        $totals = RewardLog::where('character_id', $character->id)->selectRaw('
            SUM(gold_received) as total_gold,
            SUM(exp_received) as total_exp,
            COUNT(DISTINCT event_id) as total_events
        ')->first();

        return view('rewards', compact('logs', 'currentCharacter', 'totals'));
    }
}
