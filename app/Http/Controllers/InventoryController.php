<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\TravelPermit;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index()
    {
        $character = Auth::user()->character;

        if (! $character) {
            return redirect()->route('register')->with('warning', 'กรุณาสร้างตัวละครก่อน');
        }

        $inventory = Inventory::where('character_id', $character->id)
            ->with(['item.travelPermit.kingdom'])
            ->get()
            ->filter(fn ($inv) => $inv->item !== null)
            ->groupBy(fn ($inv) => $inv->item->type);

        $currentCharacter = $character->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])->loadCount('posts');

        return view('inventory', compact('character', 'inventory', 'currentCharacter'));
    }

    public function activatePermit($id)
    {
        $character = Auth::user()->character;
        $permit = TravelPermit::where('character_id', $character?->id)->findOrFail($id);

        if ($permit->activated_at) {
            return back()->with('error', 'ใบอนุญาตนี้ถูกใช้งานไปแล้ว');
        }

        $permit->activate();

        return back()->with('success', 'ใช้งานใบอนุญาตแล้ว — หมดอายุวันที่ ' . $permit->expires_at->format('d M Y H:i'));
    }
}
