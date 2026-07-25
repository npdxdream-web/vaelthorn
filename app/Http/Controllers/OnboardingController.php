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

        $nextStage   = $this->onboarding->nextStage($character);
        $allComplete = $nextStage === null;

        $isApprovedOrActive = in_array($character->status, ['approved', 'active'], true);

        // Approved/active + onboarding complete + no kingdom → choose kingdom
        if ($isApprovedOrActive && $allComplete && ! $character->kingdom_id) {
            return redirect()->route('choose-kingdom');
        }

        // Approved/active + onboarding complete + has kingdom → game
        if ($isApprovedOrActive && $allComplete && $character->kingdom_id) {
            return redirect()->route('home');
        }

        $character->loadMissing(['stats', 'onboardingEntries']);

        $entries = $character->onboardingEntries->keyBy('stage'); // stage → entry

        return view('onboarding', compact('character', 'entries', 'nextStage', 'allComplete'));
    }

    public function submitStage(Request $request)
    {
        $character = auth()->user()?->character;

        if (! $character) {
            return redirect()->route('login');
        }

        $allComplete = $this->onboarding->nextStage($character) === null;

        if (in_array($character->status, ['approved', 'active'], true) && $allComplete && $character->kingdom_id) {
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
