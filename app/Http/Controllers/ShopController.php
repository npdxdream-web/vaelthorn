<?php

namespace App\Http\Controllers;

use App\Models\CraftingRecipe;
use App\Models\Inventory;
use App\Models\RewardLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function index()
    {
        $character = Auth::user()->character;

        if (! $character) {
            return redirect()->route('register')->with('warning', 'กรุณาสร้างตัวละครก่อน');
        }

        $recipes = CraftingRecipe::where('category', 'shop')
            ->where('is_active', true)
            ->with(['resultItem', 'materials.materialItem'])
            ->get();

        $ownedQuantities = Inventory::where('character_id', $character->id)
            ->pluck('quantity', 'item_id');

        $currentCharacter = $character->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])->loadCount('posts');

        return view('shop.index', compact('recipes', 'ownedQuantities', 'character', 'currentCharacter'));
    }

    public function buy(Request $request, $recipeId)
    {
        $character = Auth::user()->character;

        if (! $character) {
            return back()->with('error', 'กรุณาสร้างตัวละครก่อน');
        }

        $recipe = CraftingRecipe::where('category', 'shop')
            ->where('is_active', true)
            ->with('materials')
            ->findOrFail($recipeId);

        $request->validate([
            'payment_method' => 'required|in:gold,materials',
        ]);

        $paymentMethod = $request->input('payment_method');

        if ($paymentMethod === 'gold') {
            if (! $recipe->gold_cost) {
                return back()->with('error', 'ไอเทมนี้ไม่สามารถซื้อด้วยเงินได้');
            }
            if ($character->gold < $recipe->gold_cost) {
                return back()->with('error', 'Gold ไม่เพียงพอ');
            }
        } else {
            foreach ($recipe->materials as $material) {
                $owned = Inventory::where('character_id', $character->id)
                    ->where('item_id', $material->material_item_id)
                    ->value('quantity') ?? 0;

                if ($owned < $material->quantity_required) {
                    return back()->with('error', 'วัตถุดิบไม่เพียงพอ');
                }
            }
        }

        DB::transaction(function () use ($character, $recipe, $paymentMethod) {
            if ($paymentMethod === 'gold') {
                $character->decrement('gold', $recipe->gold_cost);
            } else {
                foreach ($recipe->materials as $material) {
                    $inv = Inventory::where('character_id', $character->id)
                        ->where('item_id', $material->material_item_id)
                        ->firstOrFail();
                    $inv->decrement('quantity', $material->quantity_required);
                    if ($inv->quantity <= 0) {
                        $inv->delete();
                    }
                }
            }

            $resultInv = Inventory::firstOrCreate(
                ['character_id' => $character->id, 'item_id' => $recipe->result_item_id],
                ['quantity' => 0]
            );
            $resultInv->increment('quantity', $recipe->result_quantity);

            RewardLog::create([
                'character_id'  => $character->id,
                'item_id'       => $recipe->result_item_id,
                'item_quantity' => $recipe->result_quantity,
                'gold_received' => 0,
                'exp_received'  => 0,
                'given_at'      => now(),
            ]);
        });

        return back()->with('success', 'ซื้อ ' . $recipe->resultItem->name . ' เรียบร้อยแล้ว');
    }
}
