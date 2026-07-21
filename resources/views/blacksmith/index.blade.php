@extends('layouts.app')

@section('title', 'Blacksmith — Vaelthorn')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    <x-slot:left>
        <div class="sticky top-20 space-y-4">
            @if($myOrders->isNotEmpty())
                <div class="archive-panel p-5">
                    <p class="archive-label mb-3">ใบงานของฉัน</p>
                    <div class="space-y-2">
                        @foreach($myOrders as $order)
                            <a href="{{ route('blacksmith.show', $order->token) }}"
                               class="block rounded border border-gold/15 bg-gold/3 p-2 text-xs transition hover:border-gold/30">
                                <div class="text-text">{{ $order->recipe->resultItem->name }}</div>
                                <div class="text-text-subtle">{{ ucfirst($order->status) }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-slot:left>

    {{-- Main --}}
    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-1">Kalif Market</p>
        <h1 class="font-decorative mb-2 text-3xl text-gold">Blacksmith</h1>
        <p class="font-chronicle text-lg text-text-muted">หลอมไอเทมระดับสูงร่วมกับเพื่อน — สร้างใบงาน แชร์ลิงก์ ให้เพื่อนช่วยส่งวัตถุดิบ</p>
    </div>

    {{-- Section tabs --}}
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('market.index') }}" class="font-display rounded-full border border-border px-4 py-1.5 text-xs uppercase tracking-wider text-text-muted transition hover:text-gold hover:border-gold/30">Player Market</a>
        <a href="{{ route('market.shop') }}" class="font-display rounded-full border border-border px-4 py-1.5 text-xs uppercase tracking-wider text-text-muted transition hover:text-gold hover:border-gold/30">Shop</a>
        <span class="font-display rounded-full border border-gold/50 bg-gold/10 px-4 py-1.5 text-xs uppercase tracking-wider text-gold">Blacksmith</span>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-400/30 bg-emerald-950/20 px-4 py-3 text-sm text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if($recipes->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <p class="font-display text-lg text-gold/40">ยังไม่มีสูตรหลอมในขณะนี้</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach($recipes as $recipe)
                <div class="archive-panel-soft overflow-hidden">
                    <div class="p-5">
                        <h3 class="font-display text-base text-gold">{{ $recipe->resultItem->name }}</h3>
                        <p class="mt-0.5 mb-3 text-xs text-text-subtle">x{{ $recipe->result_quantity }} · ใช้เวลาหลอม {{ $recipe->craft_duration_minutes }} นาที</p>

                        @if($recipe->resultItem->description)
                            <p class="mb-3 text-xs leading-relaxed text-text-muted">{{ $recipe->resultItem->description }}</p>
                        @endif

                        <div class="mb-3 flex flex-wrap gap-1.5 text-xs">
                            @foreach($recipe->materials as $material)
                                <span class="rounded border border-gold/15 bg-gold/5 px-2 py-0.5 text-text-muted">
                                    {{ $material->materialItem->name }} x{{ $material->quantity_required }}
                                </span>
                            @endforeach
                        </div>

                        <form method="POST" action="{{ route('blacksmith.orders.create') }}">
                            @csrf
                            <input type="hidden" name="recipe_id" value="{{ $recipe->id }}">
                            <button type="submit" class="btn-primary w-full py-2 text-sm">
                                เริ่มสร้างใบงานหลอม
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-public.shell>
@endsection
