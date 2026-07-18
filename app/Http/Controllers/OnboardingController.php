<?php

namespace App\Http\Controllers;

use App\Services\OnboardingService;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function __construct(private OnboardingService $onboarding) {}

    public function show()
    {
        $character = auth()->user()?->character;

        if (! $character) {
            return redirect()->route('login');
        }

        // Active + level 1+ + no city → choose city
        if ($character->status === 'active' && ($character->stats?->level ?? 0) >= 1 && ! $character->city_id) {
            return redirect()->route('choose-city');
        }

        // Active + has city → game
        if ($character->status === 'active' && $character->city_id) {
            return redirect()->route('home');
        }

        $character->loadMissing(['stats', 'onboardingEntries']);

        $entries     = $character->onboardingEntries->keyBy('stage'); // stage → entry
        $nextStage   = $this->onboarding->nextStage($character);
        $allComplete = $nextStage === null;

        return view('onboarding', compact('character', 'entries', 'nextStage', 'allComplete'));
    }

    public function submitStage(Request $request)
    {
        $character = auth()->user()?->character;

        if (! $character || $character->status === 'active' && $character->city_id) {
            return redirect()->route('home');
        }

        $request->validate([
            'stage'   => 'required|integer|in:1,2,3',
            'content' => 'required|string|min:30|max:5000',
        ]);

        $this->onboarding->submitStage($character, (int) $request->stage, $request->content);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('onboarding')->with('success', 'บันทึกด่านที่ ' . $request->stage . ' เสร็จสิ้น');
    }
}
