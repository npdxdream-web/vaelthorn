@extends('layouts.app')

@section('title', 'ใบงานหลอม — ' . $order->recipe->resultItem->name)

@section('content')
<x-public.shell :character-status="$currentCharacter">

    <x-slot:left>
        <div class="sticky top-20 space-y-4">
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">ใบงาน</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-text-muted">สร้างโดย</span>
                        <span class="text-text">{{ $order->creator->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">สถานะ</span>
                        <span class="text-gold">{{ ucfirst($order->status) }}</span>
                    </div>
                </div>
            </div>

            @if($order->status === 'open')
                <div class="archive-panel p-5">
                    <p class="archive-label mb-2">แชร์ลิงก์นี้ให้เพื่อน</p>
                    <input type="text" readonly value="{{ url()->current() }}"
                           class="w-full rounded border border-gold/20 bg-bg-elevated px-2 py-1.5 text-xs text-text-muted"
                           onclick="this.select()">
                </div>
            @endif
        </div>
    </x-slot:left>

    {{-- Main --}}
    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-1">Blacksmith</p>
        <h1 class="font-decorative mb-2 text-3xl text-gold">{{ $order->recipe->resultItem->name }}</h1>
        <p class="font-chronicle text-lg text-text-muted">x{{ $order->recipe->result_quantity }}</p>
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

    {{-- Materials progress --}}
    <div class="archive-panel mb-6 p-6">
        <h2 class="font-display mb-4 text-base text-gold">วัตถุดิบ</h2>
        <div class="space-y-3">
            @foreach($order->recipe->materials as $material)
                @php
                    $contributed = $contributedTotals[$material->material_item_id] ?? 0;
                    $pct = min(100, ($contributed / max(1, $material->quantity_required)) * 100);
                    $done = $contributed >= $material->quantity_required;
                @endphp
                <div>
                    <div class="mb-1 flex justify-between text-xs">
                        <span class="{{ $done ? 'text-emerald-300' : 'text-text-muted' }}">{{ $material->materialItem->name }}</span>
                        <span class="{{ $done ? 'text-emerald-300' : 'text-text-subtle' }}">{{ $contributed }}/{{ $material->quantity_required }}</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-[#1e1c18]">
                        <div class="h-full rounded-full {{ $done ? 'bg-emerald-500' : 'bg-gold/60' }}" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Action area --}}
    @if($order->status === 'open')
        @if($character)
            @php
                $materialIds = $order->recipe->materials->pluck('material_item_id');
                $eligibleInventory = $inventory->whereIn('item_id', $materialIds);
            @endphp
            <div class="archive-panel p-6">
                <h2 class="font-display mb-4 text-base text-gold">ส่งวัตถุดิบจากกระเป๋าของคุณ</h2>
                @if($eligibleInventory->isEmpty())
                    <p class="text-sm text-text-subtle">คุณไม่มีวัตถุดิบที่ใบงานนี้ต้องการในกระเป๋า</p>
                @else
                    <form method="POST" action="{{ route('blacksmith.contribute', $order->token) }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs text-text-muted">ไอเทม</label>
                            <select name="item_id" class="rounded border border-gold/20 bg-bg-elevated px-3 py-2 text-sm text-text">
                                @foreach($eligibleInventory as $inv)
                                    <option value="{{ $inv->item_id }}">{{ $inv->item->name }} (มี {{ $inv->quantity }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-text-muted">จำนวน</label>
                            <input type="number" name="quantity" min="1" value="1"
                                   class="w-24 rounded border border-gold/20 bg-bg-elevated px-3 py-2 text-sm text-text">
                        </div>
                        <button type="submit" class="btn-primary px-6 py-2 text-sm">ส่งวัตถุดิบ</button>
                    </form>
                @endif
            </div>
        @endif
    @elseif($order->status === 'crafting' && ! $order->isReadyToClaim())
        <div class="archive-panel-soft p-10 text-center">
            <p class="font-display text-lg text-gold/70">กำลังหลอม...</p>
            <p class="mt-2 text-sm text-text-subtle">จะเสร็จเมื่อ {{ $order->ready_at->format('d M Y H:i') }}</p>
        </div>
    @elseif($order->isReadyToClaim())
        <div class="archive-panel-soft p-10 text-center">
            <p class="font-display mb-4 text-lg text-emerald-300">หลอมเสร็จแล้ว!</p>
            @if($character)
                <form method="POST" action="{{ route('blacksmith.claim', $order->token) }}" class="inline-block">
                    @csrf
                    <button type="submit" class="btn-primary px-8 py-2.5 text-sm">รับของ</button>
                </form>
            @endif
        </div>
    @elseif($order->status === 'claimed')
        <div class="archive-panel-soft p-10 text-center">
            <p class="font-display text-lg text-gold/70">รับของไปแล้วโดย {{ $order->claimant->name }}</p>
            <p class="mt-2 text-sm text-text-subtle">{{ $order->claimed_at->format('d M Y H:i') }}</p>
        </div>
    @endif

</x-public.shell>
@endsection
