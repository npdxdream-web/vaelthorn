@extends('layouts.app')

@section('title', 'Recent Activity — Vaelthorn')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    {{-- ── Left rail ──────────────────────────────────────────────────── --}}
    <x-slot:left>
        <div class="sticky top-20 space-y-4">
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Summary</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-text-muted">Threads Joined</span>
                        <span class="font-display text-gold">{{ $threads->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Total Posts</span>
                        <span class="font-display text-gold">{{ $threads->sum('post_count') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:left>

    {{-- ── Main ─────────────────────────────────────────────────────────── --}}
    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-1">Chronicles</p>
        <h1 class="font-decorative mb-2 text-3xl text-gold">Recent Activity</h1>
        <p class="font-chronicle text-lg text-text-muted">กระทู้และเรื่องราวที่คุณเข้าร่วม</p>
    </div>

    @if($threads->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <svg class="mx-auto mb-4 h-12 w-12 text-gold/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="font-display text-lg text-gold/40">ยังไม่มีกิจกรรม</p>
            <p class="mt-2 text-sm text-text-subtle">เริ่มเขียน Post ในกระทู้เพื่อดูประวัติที่นี่</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block rounded border border-gold/30 bg-gold/5 px-5 py-2 font-display text-sm uppercase tracking-wider text-gold/80 transition hover:bg-gold/10">
                ไปหน้าหลัก
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($threads as $row)
                @php
                    $thread = $row['thread'];
                    $village = $thread->village;
                    $city = $village?->city;
                @endphp
                <a href="{{ route('thread', $thread->id) }}"
                   class="archive-panel-soft group block p-5 transition hover:border-gold/30">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            {{-- Status badge --}}
                            <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                @php
                                    $statusMap = [
                                        'open'     => ['Open',     'text-emerald-400', 'border-emerald-400/30', 'bg-emerald-950/20'],
                                        'locked'   => ['Locked',   'text-amber-400',   'border-amber-400/30',   'bg-amber-950/20'],
                                        'archived' => ['Archived', 'text-text-subtle',  'border-border',          'bg-transparent'],
                                        'pending'  => ['Pending',  'text-sky-400',     'border-sky-400/30',     'bg-sky-950/20'],
                                    ];
                                    [$sLabel, $sText, $sBorder, $sBg] = $statusMap[$thread->status] ?? ['—', 'text-text-subtle', 'border-border', 'bg-transparent'];
                                @endphp
                                <span class="rounded-full border px-2 py-0.5 font-display text-[0.6rem] uppercase tracking-wider {{ $sText }} {{ $sBorder }} {{ $sBg }}">
                                    {{ $sLabel }}
                                </span>
                                @if($row['post_count'] > 0)
                                    <span class="archive-label">{{ $row['post_count'] }} {{ $row['post_count'] === 1 ? 'post' : 'posts' }} โดยคุณ</span>
                                @endif
                            </div>

                            <h3 class="mb-1.5 font-display text-base text-text group-hover:text-gold transition-colors">
                                {{ $thread->title }}
                            </h3>

                            <div class="flex flex-wrap items-center gap-3 text-xs text-text-muted">
                                @if($village)
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $village->name }}
                                    </span>
                                @endif
                                @if($city)
                                    <span style="color:{{ $city->color ?? '#c8a84b' }}">{{ $city->name }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-1 text-xs text-text-subtle">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ \Carbon\Carbon::parse($row['last_posted_at'])->diffForHumans() }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

</x-public.shell>
@endsection
