<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Thread;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $user             = Auth::user();
        $currentCharacter = $user->character?->load(['city', 'currentCity', 'stats', 'badges'])->loadCount('posts');

        $query = Thread::where('status', 'archived')
            ->with('village.city')
            ->withCount(['posts' => fn ($q) => $q->where('status', 'approved')])
            ->orderByDesc('archived_at');

        // Filter by city
        if ($cityId = $request->input('city_id')) {
            $query->whereHas('village', fn ($q) => $q->where('city_id', $cityId));
        }

        // Filter by village
        if ($villageId = $request->input('village_id')) {
            $query->where('village_id', $villageId);
        }

        $threads  = $query->paginate(20)->withQueryString();
        $cities   = City::orderBy('name')->get();
        $villages = $cityId
            ? Village::where('city_id', $cityId)->orderBy('name')->get()
            : Village::orderBy('name')->get();

        return view('archive', compact('threads', 'cities', 'villages', 'currentCharacter'));
    }
}
