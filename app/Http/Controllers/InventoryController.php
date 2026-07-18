<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
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
            ->with('item')
            ->get()
            ->filter(fn ($inv) => $inv->item !== null)
            ->groupBy(fn ($inv) => $inv->item->type);

        $currentCharacter = $character->load(['city', 'currentCity', 'stats', 'badges'])->loadCount('posts');

        return view('inventory', compact('character', 'inventory', 'currentCharacter'));
    }
}
