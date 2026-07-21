<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\MarketListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketListing::active()
            ->with(['item', 'seller'])
            ->latest();

        if ($request->filled('type')) {
            $query->whereHas('item', fn ($q) => $q->where('type', $request->type));
        }

        if ($request->filled('search')) {
            $query->whereHas('item', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        $listings   = $query->paginate(20)->withQueryString();
        $currentCharacter = Auth::user()->character
            ?->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])
            ->loadCount('posts');

        return view('market.index', compact('listings', 'currentCharacter'));
    }

    public function create()
    {
        $character = Auth::user()->character;

        if (! $character) {
            return redirect()->route('home');
        }

        $inventory = Inventory::where('character_id', $character->id)
            ->with('item')
            ->get()
            ->filter(fn ($inv) => $inv->item?->is_tradeable && $inv->quantity > 0);

        $currentCharacter = $character->load(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])->loadCount('posts');

        return view('market.create', compact('character', 'inventory', 'currentCharacter'));
    }

    public function store(Request $request)
    {
        $character = Auth::user()->character;

        if (! $character) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'inventory_id' => 'required|integer',
            'quantity'     => 'required|integer|min:1',
            'price'        => 'required|integer|min:1|max:9999999',
        ]);

        $inv = Inventory::where('id', $validated['inventory_id'])
            ->where('character_id', $character->id)
            ->with('item')
            ->firstOrFail();

        if (! $inv->item->is_tradeable) {
            return back()->withErrors(['inventory_id' => 'ไอเทมนี้ไม่สามารถซื้อขายได้']);
        }

        if ($inv->quantity < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'มีไอเทมไม่เพียงพอ']);
        }

        DB::transaction(function () use ($inv, $character, $validated) {
            $inv->decrement('quantity', $validated['quantity']);
            if ($inv->quantity - $validated['quantity'] <= 0) {
                $inv->delete();
            }

            MarketListing::create([
                'seller_id' => $character->id,
                'item_id'   => $inv->item_id,
                'quantity'  => $validated['quantity'],
                'price'     => $validated['price'],
                'status'    => 'active',
            ]);
        });

        return redirect()->route('market.index')->with('success', 'ลงขายไอเทมเรียบร้อยแล้ว');
    }

    public function cancel($id)
    {
        $character = Auth::user()->character;
        $listing   = MarketListing::active()->where('seller_id', $character->id)->findOrFail($id);

        DB::transaction(function () use ($listing, $character) {
            $listing->update(['status' => 'cancelled']);

            $inv = Inventory::firstOrCreate(
                ['character_id' => $character->id, 'item_id' => $listing->item_id],
                ['quantity' => 0]
            );
            $inv->increment('quantity', $listing->quantity);
        });

        return back()->with('success', 'ยกเลิกรายการขายแล้ว ไอเทมคืนสู่คลัง');
    }

    public function buy($id)
    {
        $buyer   = Auth::user()->character;
        $listing = MarketListing::active()->with(['item', 'seller'])->findOrFail($id);

        if ($listing->seller_id === $buyer->id) {
            return back()->withErrors(['buy' => 'ไม่สามารถซื้อไอเทมของตัวเองได้']);
        }

        if ($buyer->gold < $listing->price) {
            return back()->withErrors(['buy' => 'Gold ไม่เพียงพอ (ต้องการ ' . number_format($listing->price) . ' Gold)']);
        }

        try {
            DB::transaction(function () use ($buyer, $listing) {
                // Row-lock the listing — without this, two concurrent buyers could both pass
                // the active() check above and both purchase the same listing.
                $locked = MarketListing::where('id', $listing->id)->lockForUpdate()->firstOrFail();

                if ($locked->status !== 'active') {
                    throw new \RuntimeException('already_sold');
                }

                $locked->update(['status' => 'sold']);

                $buyer->decrement('gold', $listing->price);
                $listing->seller->increment('gold', $listing->price);

                $inv = Inventory::firstOrCreate(
                    ['character_id' => $buyer->id, 'item_id' => $listing->item_id],
                    ['quantity' => 0]
                );
                $inv->increment('quantity', $listing->quantity);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['buy' => 'รายการนี้ถูกซื้อหรือยกเลิกไปแล้ว']);
        }

        return back()->with('success', 'ซื้อ ' . $listing->item->name . ' เรียบร้อยแล้ว');
    }
}
