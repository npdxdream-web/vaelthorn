<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CitySelectionController extends Controller
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

        if ($character->city_id) {
            return redirect()->route('home');
        }

        $cities = City::with('villages')
            ->where('is_active', true)
            ->where('is_locked', false)
            ->get();

        return view('choose-city', compact('character', 'cities'));
    }

    public function store(Request $request)
    {
        $character = auth()->user()?->character;

        if (! $character || $character->status !== 'active') {
            abort(403);
        }

        // Backend enforcement — once set, kingdom is permanent
        if ($character->city_id !== null) {
            abort(403, 'Kingdom ถูกเลือกแล้ว ไม่สามารถเปลี่ยนได้');
        }

        $request->validate([
            'city_id' => 'required|exists:cities,id',
        ]);

        $city = City::where('id', $request->city_id)
            ->where('is_active', true)
            ->where('is_locked', false)
            ->firstOrFail();

        $character->update([
            'city_id'         => $city->id,
            'current_city_id' => $city->id,
        ]);

        return redirect()->route('home')->with('success', 'ยินดีต้อนรับสู่ ' . $city->name . '!');
    }
}
