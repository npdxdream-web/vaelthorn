<?php

namespace App\Http\Controllers;

use App\Models\WorldChronicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorldChronicleController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->input('category');

        $query = WorldChronicle::where('is_published', true)
            ->with(['event.kingdom', 'kingdom'])
            ->latest('generated_at');

        if ($category) {
            $query->where('category', $category);
        }

        $chronicles = $query->paginate(10)->withQueryString();

        $categories = WorldChronicle::where('is_published', true)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $currentCharacter = Auth::user()->character
            ?->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])
            ->loadCount('posts');

        return view('chronicles.index', compact('chronicles', 'categories', 'category', 'currentCharacter'));
    }

    public function show($id)
    {
        $chronicle = WorldChronicle::where('is_published', true)
            ->with(['event.kingdom', 'kingdom'])
            ->findOrFail($id);

        $currentCharacter = Auth::user()->character
            ?->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])
            ->loadCount('posts');

        $prev = WorldChronicle::where('is_published', true)
            ->where('generated_at', '<', $chronicle->generated_at)
            ->latest('generated_at')->first();

        $next = WorldChronicle::where('is_published', true)
            ->where('generated_at', '>', $chronicle->generated_at)
            ->oldest('generated_at')->first();

        return view('chronicles.show', compact('chronicle', 'currentCharacter', 'prev', 'next'));
    }
}
