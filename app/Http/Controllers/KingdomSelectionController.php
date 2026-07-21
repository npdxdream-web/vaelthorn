<?php

namespace App\Http\Controllers;

use App\Models\Kingdom;
use Illuminate\Http\Request;

class KingdomSelectionController extends Controller
{
    public function show()
    {
        $character = auth()->user()?->character;

        if (! $character) {
            return redirect()->route('login');
        }

        if ($character->status !== 'active' || ($character->stats?->level ?? 0) < 1) {
            return redirect()->route('onboarding');
        }

        if ($character->kingdom_id) {
            return redirect()->route('home');
        }

        $kingdoms = Kingdom::with('cities')
            ->where('is_active', true)
            ->get();

        return view('choose-kingdom', compact('character', 'kingdoms'));
    }

    public function store(Request $request)
    {
        $character = auth()->user()?->character;

        if (! $character || $character->status !== 'active') {
            abort(403);
        }

        // Backend enforcement — once set, kingdom is permanent
        if ($character->kingdom_id !== null) {
            abort(403, 'Kingdom ถูกเลือกแล้ว ไม่สามารถเปลี่ยนได้');
        }

        $request->validate([
            'kingdom_id' => 'required|exists:kingdoms,id',
        ]);

        $kingdom = Kingdom::where('id', $request->kingdom_id)
            ->where('is_active', true)
            ->firstOrFail();

        $character->update([
            'kingdom_id'         => $kingdom->id,
            'current_kingdom_id' => $kingdom->id,
        ]);

        return redirect()->route('home')->with('success', 'ยินดีต้อนรับสู่ ' . $kingdom->name . '!');
    }
}
