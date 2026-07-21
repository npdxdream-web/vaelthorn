<?php

namespace App\Http\Controllers;

use App\Models\CraftingOrder;
use App\Models\CraftingRecipe;
use App\Models\Inventory;
use App\Models\RewardLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BlacksmithController extends Controller
{
    public function index()
    {
        $character = Auth::user()->character;

        if (! $character) {
            return redirect()->route('register')->with('warning', 'กรุณาสร้างตัวละครก่อน');
        }

        $recipes = CraftingRecipe::where('category', 'blacksmith')
            ->where('is_active', true)
            ->with(['resultItem', 'materials.materialItem'])
            ->get();

        $myOrders = CraftingOrder::where('created_by', $character->id)
            ->whereIn('status', ['open', 'crafting', 'ready'])
            ->with('recipe.resultItem')
            ->latest()
            ->get();

        $currentCharacter = $character->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])->loadCount('posts');

        return view('blacksmith.index', compact('recipes', 'myOrders', 'character', 'currentCharacter'));
    }

    public function createOrder(Request $request)
    {
        $character = Auth::user()->character;

        if (! $character) {
            return redirect()->route('register')->with('warning', 'กรุณาสร้างตัวละครก่อน');
        }

        $request->validate(['recipe_id' => 'required|exists:crafting_recipes,id']);

        $recipe = CraftingRecipe::where('category', 'blacksmith')
            ->where('is_active', true)
            ->findOrFail($request->input('recipe_id'));

        $order = CraftingOrder::create([
            'recipe_id'  => $recipe->id,
            'created_by' => $character->id,
            'status'     => 'open',
        ]);

        return redirect()->route('blacksmith.show', $order->token)
            ->with('success', 'สร้างใบงานหลอมแล้ว — แชร์ลิงก์นี้ให้เพื่อนช่วยส่งวัตถุดิบได้เลย');
    }

    public function show($token)
    {
        $order = CraftingOrder::where('token', $token)
            ->with(['recipe.resultItem', 'recipe.materials.materialItem', 'contributions.character', 'creator', 'claimant'])
            ->firstOrFail();

        $character = Auth::user()->character;
        $contributedTotals = $order->contributedTotals();

        $inventory = $character
            ? Inventory::where('character_id', $character->id)->with('item')->get()
            : collect();

        $currentCharacter = $character
            ? $character->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])->loadCount('posts')
            : null;

        return view('blacksmith.show', compact('order', 'contributedTotals', 'inventory', 'character', 'currentCharacter'));
    }

    public function contribute(Request $request, $token)
    {
        $character = Auth::user()->character;

        if (! $character) {
            return back()->with('error', 'กรุณาสร้างตัวละครก่อน');
        }

        $order = CraftingOrder::where('token', $token)->with('recipe.materials')->firstOrFail();

        if ($order->status !== 'open') {
            return back()->with('error', 'ใบงานนี้ปิดรับวัตถุดิบแล้ว');
        }

        $request->validate([
            'item_id'  => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $itemId    = (int) $request->input('item_id');
        $requested = (int) $request->input('quantity');

        $material = $order->recipe->materials->firstWhere('material_item_id', $itemId);
        if (! $material) {
            return back()->with('error', 'ไอเทมนี้ไม่ใช่วัตถุดิบที่ใบงานนี้ต้องการ');
        }

        $alreadyContributed = $order->contributedTotals()[$itemId] ?? 0;
        $remaining = $material->quantity_required - $alreadyContributed;

        if ($remaining <= 0) {
            return back()->with('error', 'วัตถุดิบชนิดนี้ครบตามที่ต้องการแล้ว');
        }

        $inv = Inventory::where('character_id', $character->id)->where('item_id', $itemId)->first();
        $owned = $inv?->quantity ?? 0;

        if ($owned < 1) {
            return back()->with('error', 'คุณไม่มีไอเทมนี้ในกระเป๋า');
        }

        try {
            $accepted = DB::transaction(function () use ($order, $character, $itemId, $requested, $owned, $material, $inv) {
                // Lock the order row so concurrent contributions to the same order serialize —
                // without this, two simultaneous contributions could both read the same
                // "remaining" total and together overshoot quantity_required, or one could land
                // after another request already flipped the order to "crafting".
                $locked = CraftingOrder::where('id', $order->id)->with('recipe.materials')->lockForUpdate()->firstOrFail();

                if ($locked->status !== 'open') {
                    throw new \RuntimeException('closed');
                }

                $alreadyContributed = $locked->contributedTotals()[$itemId] ?? 0;
                $remaining = $material->quantity_required - $alreadyContributed;

                if ($remaining <= 0) {
                    throw new \RuntimeException('complete');
                }

                $accepted = min($requested, $remaining, $owned);

                $inv->decrement('quantity', $accepted);
                if ($inv->quantity <= 0) {
                    $inv->delete();
                }

                $locked->contributions()->create([
                    'character_id' => $character->id,
                    'item_id'      => $itemId,
                    'quantity'     => $accepted,
                ]);

                if ($locked->fresh(['recipe.materials'])->isMaterialsComplete()) {
                    $locked->update([
                        'status'     => 'crafting',
                        'started_at' => now(),
                        'ready_at'   => now()->addMinutes($locked->recipe->craft_duration_minutes),
                    ]);
                }

                return $accepted;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage() === 'closed' ? 'ใบงานนี้ปิดรับวัตถุดิบแล้ว' : 'วัตถุดิบชนิดนี้ครบตามที่ต้องการแล้ว');
        }

        return back()->with('success', "ส่งวัตถุดิบแล้ว {$accepted} ชิ้น");
    }

    public function claim($token)
    {
        $character = Auth::user()->character;

        if (! $character) {
            return back()->with('error', 'กรุณาสร้างตัวละครก่อน');
        }

        $order = CraftingOrder::where('token', $token)->with('recipe')->firstOrFail();

        // Only the creator or someone who contributed materials may claim the result.
        $isEligible = $order->created_by === $character->id
            || $order->contributions()->where('character_id', $character->id)->exists();

        if (! $isEligible) {
            return back()->with('error', 'คุณไม่ได้เป็นผู้สร้างหรือผู้ร่วมส่งวัตถุดิบในใบงานนี้');
        }

        if ($order->status === 'claimed') {
            return back()->with('error', 'ใบงานนี้ถูกรับของไปแล้ว');
        }

        if (! $order->isReadyToClaim()) {
            return back()->with('error', 'ยังหลอมไม่เสร็จ');
        }

        try {
            DB::transaction(function () use ($order, $character) {
                // Re-check under a row lock — two eligible claimants could otherwise both pass
                // the status checks above and both receive the item.
                $locked = CraftingOrder::where('id', $order->id)->with('recipe')->lockForUpdate()->firstOrFail();

                if ($locked->status === 'claimed') {
                    throw new \RuntimeException('already_claimed');
                }
                if (! $locked->isReadyToClaim()) {
                    throw new \RuntimeException('not_ready');
                }

                $locked->update([
                    'status'     => 'claimed',
                    'claimed_by' => $character->id,
                    'claimed_at' => now(),
                ]);

                $inv = Inventory::firstOrCreate(
                    ['character_id' => $character->id, 'item_id' => $locked->recipe->result_item_id],
                    ['quantity' => 0]
                );
                $inv->increment('quantity', $locked->recipe->result_quantity);

                RewardLog::create([
                    'character_id'  => $character->id,
                    'item_id'       => $locked->recipe->result_item_id,
                    'item_quantity' => $locked->recipe->result_quantity,
                    'gold_received' => 0,
                    'exp_received'  => 0,
                    'given_at'      => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage() === 'already_claimed' ? 'ใบงานนี้ถูกรับของไปแล้ว' : 'ยังหลอมไม่เสร็จ');
        }

        return redirect()->route('blacksmith.show', $order->token)
            ->with('success', 'รับของสำเร็จแล้ว!');
    }
}
