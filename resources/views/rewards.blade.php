@extends('layouts.app')

@section('title', 'Reward History')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    <x-slot:left>
        <div class="sticky top-20 space-y-4">
            {{-- Totals --}}
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">All Time</p>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-text-muted">Events</span>
                        <span class="font-display text-gold">{{ number_format($totals->total_events ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-text-muted">Gold Earned</span>
                        <span class="font-display text-yellow-400">{{ number_format($totals->total_gold ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-text-muted">EXP Earned</span>
                        <span class="font-display text-blue-400">{{ number_format($totals->total_exp ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <div class="archive-panel p-5">
                <p class="archive-label mb-2">Legend</p>
                <div class="space-y-2 text-xs text-text-subtle">
                    <div class="flex items-center gap-2"><span class="text-yellow-400">◈</span> Gold received</div>
                    <div class="flex items-center gap-2"><span class="text-blue-400">◈</span> EXP received</div>
                    <div class="flex items-center gap-2"><span class="text-gold/60">◈</span> Item received</div>
                </div>
            </div>
        </div>
    </x-slot:left>

    {{-- Main --}}
    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-1">{{ $currentCharacter->name }}</p>
        <h1 class="font-decorative mb-2 text-3xl text-gold">Reward History</h1>
        <p class="font-chronicle text-lg text-text-muted">รางวัลทั้งหมดที่ได้รับจาก Events</p>
    </div>

    @if($logs->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <p class="font-display text-lg text-gold/40">ยังไม่มีประวัติรางวัล</p>
            <p class="mt-2 text-sm text-text-subtle">เข้าร่วม Event และรับรางวัลเพื่อสะสมประวัติที่นี่</p>
            <a href="{{ route('events.index') }}" class="mt-4 inline-block rounded border border-gold/30 bg-gold/5 px-4 py-2 font-display text-xs uppercase tracking-wider text-gold transition hover:bg-gold/10">
                ดู Events
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($logs as $log)
                @php
                    $kingdom = $log->event?->kingdom;
                    $kingdomColors = [
                        'Silvaria'  => '#4ade80',
                        'Aurantia'  => '#f59e0b',
                        'Kalif'     => '#fb923c',
                        'Frostwell' => '#60a5fa',
                        'Kyoren'    => '#a78bfa',
                        'Celestia'  => '#c8a84b',
                    ];
                    $kc = $kingdom ? ($kingdomColors[$kingdom->name] ?? '#c8a84b') : '#c8a84b';
                @endphp
                <div class="archive-panel-soft overflow-hidden">
                    <div class="h-px" style="background:linear-gradient(90deg,{{ $kc }}55,transparent)"></div>
                    <div class="flex items-start gap-4 p-4">
                        {{-- Date --}}
                        <div class="w-12 shrink-0 text-center">
                            <div class="font-display text-lg leading-none" style="color:{{ $kc }}">
                                {{ $log->given_at?->format('d') }}
                            </div>
                            <div class="archive-label text-[0.6rem]" style="color:{{ $kc }}66">
                                {{ $log->given_at?->format('M') }}
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="min-w-0 flex-1">
                            @if($log->event)
                                <a href="{{ route('events.show', $log->event_id) }}"
                                   class="font-display text-sm transition hover:text-gold" style="color:{{ $kc }}">
                                    {{ $log->event->title }}
                                </a>
                                @if($kingdom)
                                    <span class="ml-2 archive-label" style="color:{{ $kc }}66">{{ $kingdom->name }}</span>
                                @endif
                            @else
                                <span class="font-display text-sm text-text-muted">System Reward</span>
                            @endif

                            {{-- Rewards row --}}
                            <div class="mt-2 flex flex-wrap items-center gap-3">
                                @if($log->gold_received)
                                    <span class="flex items-center gap-1 rounded border border-yellow-500/20 bg-yellow-950/20 px-2 py-0.5 font-display text-xs text-yellow-400">
                                        <span>◈</span> {{ number_format($log->gold_received) }} Gold
                                    </span>
                                @endif
                                @if($log->exp_received)
                                    <span class="flex items-center gap-1 rounded border border-blue-500/20 bg-blue-950/20 px-2 py-0.5 font-display text-xs text-blue-400">
                                        <span>◈</span> {{ number_format($log->exp_received) }} EXP
                                    </span>
                                @endif
                                @if($log->item && $log->item_quantity)
                                    <span class="flex items-center gap-1 rounded border border-gold/20 bg-gold/5 px-2 py-0.5 font-display text-xs text-gold/80">
                                        <span>◈</span> {{ $log->item->name }} ×{{ $log->item_quantity }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="shrink-0 text-xs text-text-subtle">
                            {{ $log->given_at?->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $logs->links() }}</div>
    @endif

</x-public.shell>
@endsection
