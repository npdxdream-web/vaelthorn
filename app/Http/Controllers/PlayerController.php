<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Kingdom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $kingdomId = $request->input('kingdom');

        $players = Character::query()
            ->where('status', 'active')
            ->whereHas('stats')
            ->with(['user', 'kingdom', 'stats'])
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($kingdomId, fn ($q) => $q->where('kingdom_id', $kingdomId))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $kingdoms = Kingdom::where('is_active', true)->orderBy('sort_order')->get();

        $currentCharacter = Auth::user()->character
            ?->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])
            ->loadCount('posts');

        return view('players.index', compact('players', 'kingdoms', 'search', 'kingdomId', 'currentCharacter'));
    }

    public function leaderboard()
    {
        $players = Character::query()
            ->join('character_stats', 'character_stats.character_id', '=', 'characters.id')
            ->where('characters.status', 'active')
            ->orderByDesc('character_stats.level')
            ->orderByDesc('character_stats.exp')
            ->select('characters.*')
            ->with(['user', 'kingdom', 'stats'])
            ->get();

        $currentCharacter = Auth::user()->character
            ?->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])
            ->loadCount('posts');

        return view('players.leaderboard', compact('players', 'currentCharacter'));
    }
}
