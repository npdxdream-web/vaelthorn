@extends('layouts.app')

@section('title', 'Inventory — ' . $character->name)

@section('content')
<x-public.shell :character-status="$currentCharacter">

    {{-- ── Left rail ──────────────────────────────────────────────────── --}}
    <x-slot:left>
        <div class="sticky top-20 space-y-4">

            {{-- Summary --}}
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Inventory Summary</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-text-muted">Total Items</span>
                        <span class="font-display text-gold">{{ $inventory->flatten()->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Gold</span>
                        <span class="font-display text-yellow-400">{{ number_format($character->gold ?? 0) }}</span>
                    </div>
                </div>
            </div>

            {{-- Type filter --}}
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Item Types</p>
                <div class="space-y-1">
                    @php
                        $typeIcons = [
                            'weapon'     => ['⚔', 'Weapons'],
                            'armor'      => ['🛡', 'Armor'],
                            'consumable' => ['🧪', 'Consumables'],
                            'material'   => ['💎', 'Materials'],
                            'key_item'   => ['🗝', 'Key Items'],
                            'currency'   => ['💰', 'Currency'],
                        ];
                    @endphp
                    @foreach($typeIcons as $type => [$icon, $label])
                        @php $count = $inventory->get($type)?->count() ?? 0; @endphp
                        @if($count > 0)
                        <a href="#type-{{ $type }}"
                           class="flex items-center justify-between rounded px-2 py-1.5 text-sm text-text-muted transition hover:text-gold">
                            <span>{{ $icon }} {{ $label }}</span>
                            <span class="font-display text-xs text-gold/70">{{ $count }}</span>
                        </a>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Rarity legend --}}
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Rarity</p>
                <div class="space-y-1.5 text-xs">
                    @foreach([
                        ['common',    '#9ca3af', 'Common'],
                        ['uncommon',  '#4ade80', 'Uncommon'],
                        ['rare',      '#60a5fa', 'Rare'],
                        ['epic',      '#a78bfa', 'Epic'],
                        ['legendary', '#f59e0b', 'Legendary'],
                    ] as [$r, $color, $label])
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full" style="background:{{ $color }}"></span>
                            <span style="color:{{ $color }}">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-slot:left>

    {{-- ── Main ─────────────────────────────────────────────────────────── --}}
    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-1">{{ $character->name }}</p>
        <h1 class="font-decorative mb-2 text-3xl text-gold">Inventory</h1>
        <p class="font-chronicle text-lg text-text-muted">ไอเทมและทรัพย์สินทั้งหมดของตัวละคร</p>
    </div>

    @if($inventory->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <p class="font-display text-lg text-gold/40">ยังไม่มีไอเทมในคลัง</p>
            <p class="mt-2 text-sm text-text-subtle">เข้าร่วม Event เพื่อรับรางวัลและไอเทม</p>
            <a href="{{ route('events.index') }}" class="btn-primary mt-4 inline-block text-sm">ดู Events</a>
        </div>
    @else
        @php
            $typeIcons = [
                'weapon'     => ['⚔', 'Weapons'],
                'armor'      => ['🛡', 'Armor'],
                'consumable' => ['🧪', 'Consumables'],
                'material'   => ['💎', 'Materials'],
                'key_item'   => ['🗝', 'Key Items'],
                'currency'   => ['💰', 'Currency'],
            ];
            $rarityColors = [
                'common'    => '#9ca3af',
                'uncommon'  => '#4ade80',
                'rare'      => '#60a5fa',
                'epic'      => '#a78bfa',
                'legendary' => '#f59e0b',
            ];
        @endphp

        @foreach($typeIcons as $type => [$icon, $label])
            @if($inventory->has($type))
                <div id="type-{{ $type }}" class="mb-6">
                    <div class="mb-3 flex items-center gap-3">
                        <span class="text-xl">{{ $icon }}</span>
                        <h2 class="font-display text-lg text-gold">{{ $label }}</h2>
                        <div class="h-px flex-1 bg-gradient-to-r from-gold/20 to-transparent"></div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($inventory->get($type) as $inv)
                            @php
                                $item = $inv->item;
                                $rc   = $rarityColors[$item->rarity] ?? '#9ca3af';
                                $bonuses = array_filter([
                                    'STR' => $item->bonus_str,
                                    'AGI' => $item->bonus_agi,
                                    'INT' => $item->bonus_int,
                                    'HP'  => $item->bonus_hp,
                                    'MP'  => $item->bonus_mana,
                                ]);
                            @endphp
                            <div class="archive-panel-soft overflow-hidden">
                                <div class="h-0.5" style="background:linear-gradient(90deg, {{ $rc }}88, transparent)"></div>
                                <div class="p-4">
                                    <div class="mb-2 flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <h3 class="font-display text-sm" style="color:{{ $rc }}">
                                                {{ $item->name }}
                                            </h3>
                                            <div class="mt-0.5 flex items-center gap-2">
                                                <span class="archive-label" style="color:{{ $rc }}88">
                                                    {{ ucfirst($item->rarity) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="shrink-0 rounded border px-2 py-1 text-center"
                                             style="border-color:{{ $rc }}33; background:{{ $rc }}0d">
                                            <div class="font-display text-base" style="color:{{ $rc }}">
                                                {{ $inv->quantity }}
                                            </div>
                                            <div class="archive-label text-[0.6rem]">QTY</div>
                                        </div>
                                    </div>

                                    @if($item->description)
                                        <p class="mb-2 text-xs leading-relaxed text-text-muted">
                                            {{ $item->description }}
                                        </p>
                                    @endif

                                    @if(!empty($bonuses))
                                        <div class="flex flex-wrap gap-2 border-t border-gold/10 pt-2">
                                            @foreach($bonuses as $stat => $val)
                                                <span class="rounded border border-gold/15 bg-gold/5 px-2 py-0.5 font-display text-xs text-gold/80">
                                                    +{{ $val }} {{ $stat }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($item->is_tradeable)
                                        <div class="mt-2 font-display text-[0.6rem] uppercase tracking-wider text-text-subtle">
                                            ⇄ Tradeable
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endif

</x-public.shell>
@endsection
