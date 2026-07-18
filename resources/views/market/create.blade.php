@extends('layouts.app')

@section('title', 'ลงขายไอเทม')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    <x-slot:left>
        <div class="sticky top-20 space-y-4">
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">กฎตลาด</p>
                <ul class="space-y-2 text-xs text-text-muted">
                    <li>• ไอเทมจะถูกหักออกจากคลังทันที</li>
                    <li>• ถ้ายกเลิก ไอเทมจะคืนสู่คลัง</li>
                    <li>• เฉพาะไอเทม Tradeable เท่านั้น</li>
                    <li>• Gold ที่ได้รับจะเข้าบัญชีทันที</li>
                </ul>
            </div>
            <div class="archive-panel p-5">
                <p class="archive-label mb-2">Gold ปัจจุบัน</p>
                <p class="font-display text-2xl text-yellow-400">{{ number_format($currentCharacter->gold ?? 0) }}</p>
            </div>
        </div>
    </x-slot:left>

    {{-- Main --}}
    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-1">Market</p>
        <h1 class="font-decorative mb-2 text-3xl text-gold">ลงขายไอเทม</h1>
        <p class="font-chronicle text-lg text-text-muted">เลือกไอเทมจากคลังและตั้งราคา</p>
    </div>

    @if($inventory->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <p class="font-display text-lg text-gold/40">ไม่มีไอเทมที่สามารถขายได้</p>
            <p class="mt-2 text-sm text-text-subtle">เฉพาะไอเทมที่มี Tradeable เท่านั้นที่ลงขายได้</p>
            <a href="{{ route('market.index') }}" class="mt-4 inline-block rounded border border-gold/30 bg-gold/5 px-4 py-2 font-display text-xs uppercase tracking-wider text-gold transition hover:bg-gold/10">
                ← กลับตลาด
            </a>
        </div>
    @else
        @if($errors->any())
            <div class="mb-4 rounded border border-red-400/30 bg-red-950/20 px-4 py-3 text-sm text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('market.store') }}" class="space-y-5">
            @csrf

            {{-- Select item --}}
            <div class="archive-panel p-6">
                <p class="archive-label mb-4">เลือกไอเทม</p>
                @php
                    $rarityColors = ['common'=>'#9ca3af','uncommon'=>'#4ade80','rare'=>'#60a5fa','epic'=>'#a78bfa','legendary'=>'#f59e0b'];
                @endphp
                <div class="space-y-2" id="item-list">
                    @foreach($inventory as $inv)
                        @php $rc = $rarityColors[$inv->item->rarity] ?? '#9ca3af'; @endphp
                        <label class="flex cursor-pointer items-center gap-4 rounded border border-gold/10 p-3 transition hover:border-gold/30 has-[:checked]:border-gold/50 has-[:checked]:bg-gold/5">
                            <input type="radio" name="inventory_id" value="{{ $inv->id }}"
                                   data-max="{{ $inv->quantity }}"
                                   class="mt-0.5 accent-amber-500"
                                   {{ old('inventory_id') == $inv->id ? 'checked' : '' }}>
                            <div class="min-w-0 flex-1">
                                <div class="font-display text-sm" style="color:{{ $rc }}">{{ $inv->item->name }}</div>
                                <div class="mt-0.5 flex items-center gap-2">
                                    <span class="archive-label text-[0.6rem]" style="color:{{ $rc }}66">{{ ucfirst($inv->item->rarity) }}</span>
                                    <span class="archive-label text-[0.6rem] text-text-subtle">{{ ucfirst($inv->item->type) }}</span>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="font-display text-sm text-text-muted">×{{ $inv->quantity }}</div>
                                <div class="archive-label text-[0.6rem] text-text-subtle">in stock</div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('inventory_id')
                    <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Quantity & Price --}}
            <div class="archive-panel p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="archive-label mb-2 block">จำนวนที่ขาย</label>
                        <input type="number" name="quantity" id="qty-input"
                               value="{{ old('quantity', 1) }}" min="1"
                               class="w-full rounded border border-gold/20 bg-bg-elevated px-3 py-2 font-display text-sm text-gold focus:border-gold/50 focus:outline-none">
                        @error('quantity')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="archive-label mb-2 block">ราคา (Gold)</label>
                        <input type="number" name="price"
                               value="{{ old('price', 100) }}" min="1" max="9999999"
                               class="w-full rounded border border-gold/20 bg-bg-elevated px-3 py-2 font-display text-sm text-yellow-400 focus:border-gold/50 focus:outline-none">
                        @error('price')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('market.index') }}"
                   class="font-display text-xs uppercase tracking-wider text-text-subtle transition hover:text-text">
                    ← ยกเลิก
                </a>
                <button type="submit"
                        class="rounded border border-gold/40 bg-gold/10 px-6 py-2.5 font-display text-sm uppercase tracking-wider text-gold transition hover:bg-gold/20">
                    ลงขาย
                </button>
            </div>
        </form>
    @endif

</x-public.shell>

<script>
    // Update max quantity when item is selected
    document.querySelectorAll('input[name="inventory_id"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const maxQty = parseInt(this.dataset.max) || 1;
            const qtyInput = document.getElementById('qty-input');
            if (qtyInput) {
                qtyInput.max = maxQty;
                if (parseInt(qtyInput.value) > maxQty) qtyInput.value = maxQty;
            }
        });
    });
</script>
@endsection
