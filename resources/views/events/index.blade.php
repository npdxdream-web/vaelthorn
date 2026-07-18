@extends('layouts.app')

@section('title', 'Events — Vaelthorn')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    {{-- ── Left rail: filters ─────────────────────────────────────────── --}}
    <x-slot:left>
        <div class="sticky top-20 space-y-4">
            <div class="archive-panel p-5">
                <p class="archive-label mb-4">Filter Events</p>
                <form method="GET" action="{{ route('events.index') }}" class="space-y-4">
                    <div>
                        <label class="archive-label mb-2 block">Type</label>
                        <div class="space-y-1.5">
                            <a href="{{ route('events.index', array_merge(request()->except('type'), [])) }}"
                               class="block px-2 py-1.5 text-sm rounded transition
                                      {{ !request('type') ? 'bg-gold/10 text-gold border border-gold/25' : 'text-text-muted hover:text-gold' }}">
                                All Types
                            </a>
                            @foreach(['flash' => '⚡ Flash', 'location' => '📍 Location', 'story_arc' => '📖 Story Arc', 'crisis' => '⚠ Crisis'] as $value => $label)
                                <a href="{{ route('events.index', array_merge(request()->all(), ['type' => $value])) }}"
                                   class="block px-2 py-1.5 text-sm rounded transition
                                          {{ request('type') === $value ? 'bg-gold/10 text-gold border border-gold/25' : 'text-text-muted hover:text-gold' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t border-gold/10 pt-4">
                        <label class="archive-label mb-2 block">Kingdom</label>
                        <div class="space-y-1.5">
                            <a href="{{ route('events.index', array_merge(request()->except('city'), [])) }}"
                               class="block px-2 py-1.5 text-sm rounded transition
                                      {{ !request('city') ? 'bg-gold/10 text-gold border border-gold/25' : 'text-text-muted hover:text-gold' }}">
                                All Kingdoms
                            </a>
                            @foreach($cities as $city)
                                <a href="{{ route('events.index', array_merge(request()->all(), ['city' => $city->id])) }}"
                                   class="block px-2 py-1.5 text-sm rounded transition
                                          {{ request('city') == $city->id ? 'bg-gold/10 text-gold border border-gold/25' : 'text-text-muted hover:text-gold' }}"
                                   style="{{ request('city') == $city->id ? 'color:'.$city->color.';border-color:'.$city->color.'44' : '' }}">
                                    {{ $city->icon ?? '◆' }} {{ $city->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>

            {{-- Type legend --}}
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Event Types</p>
                <div class="space-y-2 text-xs text-text-muted">
                    <div><span class="text-yellow-400">⚡</span> <strong class="text-text">Flash</strong> — 2-6 ชั่วโมง</div>
                    <div><span class="text-blue-400">📍</span> <strong class="text-text">Location</strong> — 1-2 สัปดาห์</div>
                    <div><span class="text-purple-400">📖</span> <strong class="text-text">Story Arc</strong> — 1+ เดือน</div>
                    <div><span class="text-red-400">⚠</span> <strong class="text-text">Crisis</strong> — 24-48 ชั่วโมง</div>
                </div>
            </div>
        </div>
    </x-slot:left>

    {{-- ── Main ─────────────────────────────────────────────────────────── --}}
    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-1">Vaelthorn Chronicles</p>
        <h1 class="font-decorative mb-2 text-3xl text-gold">Active Events</h1>
        <p class="font-chronicle text-lg text-text-muted">
            เหตุการณ์ที่กำลังเปิดรับนักผจญภัยเข้าร่วม — เข้าร่วมเพื่อสร้างบทบาทและรับรางวัล
        </p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-800 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    @forelse($events as $event)
        @php
            $typeColors = [
                'flash'     => ['#f59e0b', '⚡', 'FLASH'],
                'location'  => ['#60a5fa', '📍', 'LOCATION'],
                'story_arc' => ['#a78bfa', '📖', 'STORY ARC'],
                'crisis'    => ['#f87171', '⚠', 'CRISIS'],
            ];
            [$tc, $ti, $tl] = $typeColors[$event->type] ?? ['#c8a84b', '◆', strtoupper($event->type)];
            $cityColor = $event->city?->color ?? '#c8a84b';
            $isJoined = $currentCharacter
                ? $event->participants->contains('character_id', $currentCharacter->id)
                : false;
        @endphp

        <div class="archive-panel-soft mb-4 overflow-hidden transition hover:border-gold/30">
            {{-- Top accent bar --}}
            <div class="h-0.5 w-full" style="background:linear-gradient(90deg, {{ $tc }}88, transparent)"></div>

            <div class="p-5">
                <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="mb-1.5 flex flex-wrap items-center gap-2">
                            <span style="font-family:'Cinzel',serif; font-size:9px; letter-spacing:2px; color:{{ $tc }}; border:0.5px solid {{ $tc }}55; padding:2px 8px;">
                                {{ $ti }} {{ $tl }}
                            </span>
                            @if($event->city)
                                <span style="font-family:'Cinzel',serif; font-size:9px; letter-spacing:1.5px; color:{{ $cityColor }}88; padding:2px 6px;">
                                    {{ $event->city->name }}
                                </span>
                            @endif
                            @if($isJoined)
                                <span style="font-family:'Cinzel',serif; font-size:9px; letter-spacing:1.5px; color:#2d7a3a; border:0.5px solid #2d7a3a44; padding:2px 8px;">
                                    ✓ JOINED
                                </span>
                            @endif
                        </div>
                        <h2 class="font-display text-xl text-text hover:text-gold">
                            <a href="{{ route('events.show', $event->id) }}">{{ $event->title }}</a>
                        </h2>
                    </div>
                    <div class="text-right text-xs text-text-subtle shrink-0">
                        <div>{{ $event->participants_count }} participants</div>
                        @if($event->end_at)
                            <div class="mt-0.5">Ends {{ $event->end_at->diffForHumans() }}</div>
                        @endif
                    </div>
                </div>

                @if($event->description)
                    <p class="mb-4 line-clamp-2 text-sm text-text-muted">{{ $event->description }}</p>
                @endif

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs text-text-subtle">
                        @if($event->start_at)
                            <span>{{ $event->start_at->format('d M Y') }}</span>
                        @endif
                        @if($event->requirements->count())
                            <span>•</span>
                            <span>{{ $event->requirements->count() }} requirements</span>
                        @endif
                    </div>
                    <a href="{{ route('events.show', $event->id) }}"
                       class="btn-outline text-xs">
                        View Details →
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="archive-panel-soft p-16 text-center text-text-subtle">
            <p class="font-display text-lg text-gold/40">ไม่มี Event ที่เปิดรับสมัครอยู่ในขณะนี้</p>
            <p class="mt-2 text-sm">Admin จะประกาศ Event ใหม่เร็วๆ นี้</p>
        </div>
    @endforelse

</x-public.shell>
@endsection
