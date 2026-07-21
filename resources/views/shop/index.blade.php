@extends('layouts.app')

@section('title', 'Shop — Vaelthorn')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    <x-slot:left>
        <div class="sticky top-20 space-y-4">
            @if($currentCharacter)
                <div class="archive-panel p-5">
                    <p class="archive-label mb-2">Your Gold</p>
                    <p class="font-display text-2xl text-yellow-400">{{ number_format($currentCharacter->gold ?? 0) }}</p>
                </div>
            @endif
        </div>
    </x-slot:left>

    {{-- Main --}}
    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-1">Kalif Market</p>
        <h1 class="font-decorative mb-2 text-3xl text-gold">Shop</h1>
        <p class="font-chronicle text-lg text-text-muted">ซื้อไอเทมจากร้านค้าระบบ — เลือกจ่ายด้วยเงินหรือวัตถุดิบ</p>
    </div>

    {{-- Section tabs --}}
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('market.index') }}" class="font-display rounded-full border border-border px-4 py-1.5 text-xs uppercase tracking-wider text-text-muted transition hover:text-gold hover:border-gold/30">Player Market</a>
        <span class="font-display rounded-full border border-gold/50 bg-gold/10 px-4 py-1.5 text-xs uppercase tracking-wider text-gold">Shop</span>
        <a href="{{ route('blacksmith.index') }}" class="font-display rounded-full border border-border px-4 py-1.5 text-xs uppercase tracking-wider text-text-muted transition hover:text-gold hover:border-gold/30">Blacksmith</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-400/30 bg-emerald-950/20 px-4 py-3 text-sm text-emerald-300">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded border border-red-400/30 bg-red-950/20 px-4 py-3 text-sm text-red-300">
            {{ session('error') }}
        </div>
    @endif

    @if($recipes->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <p class="font-display text-lg text-gold/40">ร้านค้ายังไม่มีสินค้า</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach($recipes as $recipe)
                @php
                    $canAffordGold = $recipe->gold_cost && $currentCharacter && $currentCharacter->gold >= $recipe->gold_cost;
                    $canAffordMaterials = $recipe->materials->every(fn ($m) => ($ownedQuantities[$m->material_item_id] ?? 0) >= $m->quantity_required);
                @endphp
                <div class="archive-panel-soft overflow-hidden">
                    <div class="p-5">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-display text-base text-gold">{{ $recipe->resultItem->name }}</h3>
                                <p class="mt-0.5 text-xs text-text-subtle">x{{ $recipe->result_quantity }}</p>
                            </div>
                        </div>

                        @if($recipe->resultItem->description)
                            <p class="mb-3 text-xs leading-relaxed text-text-muted">{{ $recipe->resultItem->description }}</p>
                        @endif

                        <div class="space-y-2 border-t border-gold/10 pt-3">
                            @if($recipe->gold_cost)
                                <form method="POST" action="{{ route('market.shop.buy', $recipe->id) }}" class="flex items-center justify-between">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="gold">
                                    <span class="text-sm text-yellow-400">{{ number_format($recipe->gold_cost) }} Gold</span>
                                    <button type="submit"
                                            class="rounded border px-3 py-1 font-display text-xs uppercase tracking-wider transition
                                                   {{ $canAffordGold ? 'border-gold/40 bg-gold/10 text-gold hover:bg-gold/20' : 'cursor-not-allowed border-gold/10 text-gold/30' }}"
                                            {{ $canAffordGold ? '' : 'disabled' }}
                                            onclick="return confirm('ซื้อ {{ addslashes($recipe->resultItem->name) }} x{{ $recipe->result_quantity }} ในราคา {{ number_format($recipe->gold_cost) }} Gold?')">
                                        ซื้อด้วยเงิน
                                    </button>
                                </form>
                            @endif

                            @if($recipe->materials->isNotEmpty())
                                <form method="POST" action="{{ route('market.shop.buy', $recipe->id) }}">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="materials">
                                    <div class="mb-2 flex flex-wrap gap-1.5 text-xs">
                                        @foreach($recipe->materials as $material)
                                            @php $owned = $ownedQuantities[$material->material_item_id] ?? 0; @endphp
                                            <span class="rounded border px-2 py-0.5 {{ $owned >= $material->quantity_required ? 'border-emerald-400/30 text-emerald-300' : 'border-red-400/30 text-red-300' }}">
                                                {{ $material->materialItem->name }} {{ $owned }}/{{ $material->quantity_required }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <button type="submit"
                                            class="w-full rounded border px-3 py-1 font-display text-xs uppercase tracking-wider transition
                                                   {{ $canAffordMaterials ? 'border-gold/40 bg-gold/10 text-gold hover:bg-gold/20' : 'cursor-not-allowed border-gold/10 text-gold/30' }}"
                                            {{ $canAffordMaterials ? '' : 'disabled' }}
                                            onclick="return confirm('ซื้อ {{ addslashes($recipe->resultItem->name) }} x{{ $recipe->result_quantity }} ด้วยวัตถุดิบ?')">
                                        ซื้อด้วยวัตถุดิบ
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-public.shell>
@endsection
