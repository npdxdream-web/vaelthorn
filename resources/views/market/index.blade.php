@extends('layouts.app')

@section('title', 'Market — Vaelthorn')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    <x-slot:left>
        <div class="sticky top-20 space-y-4">

            {{-- Sell item button --}}
            @if($currentCharacter)
                <a href="{{ route('market.create') }}"
                   class="flex w-full items-center justify-center gap-2 rounded border border-gold/40 bg-gold/10 px-4 py-3 font-display text-sm uppercase tracking-wider text-gold transition hover:bg-gold/20">
                    + ลงขายไอเทม
                </a>
            @endif

            {{-- Filter by type --}}
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Item Type</p>
                <div class="space-y-1">
                    @php
                        $typeIcons = [
                            ''           => ['◈', 'All Items'],
                            'weapon'     => ['⚔', 'Weapons'],
                            'armor'      => ['🛡', 'Armor'],
                            'consumable' => ['🧪', 'Consumables'],
                            'material'   => ['💎', 'Materials'],
                            'key_item'   => ['🗝', 'Key Items'],
                        ];
                        $activeType = request('type', '');
                    @endphp
                    @foreach($typeIcons as $t => [$icon, $label])
                        <a href="{{ route('market.index') }}{{ $t ? '?type='.$t : '' }}"
                           class="flex items-center gap-2 rounded px-2 py-1.5 text-sm transition
                                  {{ $activeType === $t ? 'text-gold bg-gold/5' : 'text-text-muted hover:text-gold' }}">
                            <span>{{ $icon }}</span><span>{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Gold balance --}}
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
        <h1 class="font-decorative mb-2 text-3xl text-gold">Market</h1>
        <p class="font-chronicle text-lg text-text-muted">ซื้อขายไอเทมระหว่างผู้เล่น — ตลาดกลางแห่ง Vaelthorn</p>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('market.index') }}" class="mb-5">
        @if(request('type'))
            <input type="hidden" name="type" value="{{ request('type') }}">
        @endif
        <div class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="ค้นหาไอเทม..."
                   class="flex-1 rounded border border-gold/20 bg-bg-elevated px-3 py-2 text-sm text-text focus:border-gold/40 focus:outline-none">
            <button type="submit"
                    class="rounded border border-gold/30 bg-gold/5 px-4 py-2 font-display text-xs uppercase tracking-wider text-gold/80 transition hover:bg-gold/10">
                ค้นหา
            </button>
        </div>
    </form>

    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-400/30 bg-emerald-950/20 px-4 py-3 text-sm text-emerald-300">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded border border-red-400/30 bg-red-950/20 px-4 py-3 text-sm text-red-300">
            {{ $errors->first() }}
        </div>
    @endif

    @if($listings->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <p class="font-display text-lg text-gold/40">ไม่มีรายการขายในขณะนี้</p>
            <p class="mt-2 text-sm text-text-subtle">เป็นคนแรกที่ลงขายไอเทม!</p>
        </div>
    @else
        @php
            $rarityColors = [
                'common'    => '#9ca3af',
                'uncommon'  => '#4ade80',
                'rare'      => '#60a5fa',
                'epic'      => '#a78bfa',
                'legendary' => '#f59e0b',
            ];
        @endphp
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach($listings as $listing)
                @php
                    $rc = $rarityColors[$listing->item->rarity] ?? '#9ca3af';
                    $isOwn = $currentCharacter?->id === $listing->seller_id;
                    $canAfford = $currentCharacter && $currentCharacter->gold >= $listing->price;
                @endphp
                <div class="archive-panel-soft overflow-hidden">
                    <div class="h-0.5" style="background:linear-gradient(90deg,{{ $rc }}66,transparent)"></div>
                    <div class="p-4">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-display text-sm" style="color:{{ $rc }}">
                                    {{ $listing->item->name }}
                                </h3>
                                <div class="mt-0.5 flex items-center gap-2">
                                    <span class="archive-label text-[0.6rem]" style="color:{{ $rc }}77">
                                        {{ ucfirst($listing->item->rarity) }} · {{ ucfirst($listing->item->type) }}
                                    </span>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="font-display text-lg text-yellow-400">
                                    {{ number_format($listing->price) }}
                                </div>
                                <div class="archive-label text-[0.6rem] text-yellow-400/60">GOLD</div>
                            </div>
                        </div>

                        @if($listing->item->description)
                            <p class="mb-3 text-xs leading-relaxed text-text-subtle">
                                {{ Str::limit($listing->item->description, 80) }}
                            </p>
                        @endif

                        <div class="flex items-center justify-between border-t border-gold/10 pt-3">
                            <div class="text-xs text-text-subtle">
                                Qty: <span class="font-display text-text-muted">{{ $listing->quantity }}</span>
                                · Sold by: <span class="text-text-muted">{{ $listing->seller->name }}</span>
                            </div>
                            @if($isOwn)
                                <form method="POST" action="{{ route('market.cancel', $listing->id) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="rounded border border-red-400/20 px-3 py-1 font-display text-xs text-red-400/70 transition hover:border-red-400/40 hover:text-red-400"
                                            onclick="return confirm('ยกเลิกรายการขายนี้?')">
                                        ยกเลิก
                                    </button>
                                </form>
                            @elseif($currentCharacter)
                                <form method="POST" action="{{ route('market.buy', $listing->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="rounded border px-3 py-1 font-display text-xs uppercase tracking-wider transition
                                                   {{ $canAfford
                                                       ? 'border-gold/40 bg-gold/10 text-gold hover:bg-gold/20'
                                                       : 'cursor-not-allowed border-gold/10 text-gold/30' }}"
                                            {{ $canAfford ? '' : 'disabled' }}
                                            {{ $canAfford ? 'onclick="return confirm(\'ซื้อ '.$listing->item->name.' ในราคา '.number_format($listing->price).' Gold?\')"' : '' }}>
                                        {{ $canAfford ? 'ซื้อ' : 'Gold ไม่พอ' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $listings->links() }}</div>
    @endif

</x-public.shell>
@endsection
