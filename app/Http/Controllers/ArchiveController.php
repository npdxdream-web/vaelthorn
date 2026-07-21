<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Kingdom;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $user             = Auth::user();
        $currentCharacter = $user->character?->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])->loadCount('posts');

        $query = Thread::where('status', 'archived')
            ->with('city.kingdom')
            ->withCount(['posts' => fn ($q) => $q->where('status', 'approved')])
            ->orderByDesc('archived_at');

        // Filter by kingdom
        if ($kingdomId = $request->input('kingdom_id')) {
            $query->whereHas('city', fn ($q) => $q->where('kingdom_id', $kingdomId));
        }

        // Filter by city
        if ($cityId = $request->input('city_id')) {
            $query->where('city_id', $cityId);
        }

        $threads  = $query->paginate(20)->withQueryString();
        $kingdoms = Kingdom::orderBy('name')->get();
        $cities   = $kingdomId
            ? City::where('kingdom_id', $kingdomId)->orderBy('name')->get()
            : City::orderBy('name')->get();

        return view('archive', compact('threads', 'kingdoms', 'cities', 'currentCharacter'));
    }
}
