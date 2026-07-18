<?php

namespace App\Http\Controllers;

use App\Models\WorldChronicle;
use Illuminate\Support\Facades\Auth;

class WorldChronicleController extends Controller
{
    public function index()
    {
        $chronicles = WorldChronicle::where('is_published', true)
            ->with('event.city')
            ->latest('generated_at')
            ->paginate(10);

        $currentCharacter = Auth::user()->character
            ?->load(['city', 'currentCity', 'stats', 'badges'])
            ->loadCount('posts');

        return view('chronicles.index', compact('chronicles', 'currentCharacter'));
    }

    public function show($id)
    {
        $chronicle = WorldChronicle::where('is_published', true)
            ->with('event.city')
            ->findOrFail($id);

        $currentCharacter = Auth::user()->character
            ?->load(['city', 'currentCity', 'stats', 'badges'])
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
