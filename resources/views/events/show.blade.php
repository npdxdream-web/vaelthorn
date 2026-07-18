@extends('layouts.app')

@section('title', $event->title . ' — Vaelthorn')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    {{-- ── Left rail ──────────────────────────────────────────────────── --}}
    <x-slot:left>
        <div class="sticky top-20 space-y-4">

            {{-- Event info --}}
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Event Info</p>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="archive-label">Type</span>
                        <div class="mt-1 text-text">{{ ucfirst(str_replace('_', ' ', $event->type)) }}</div>
                    </div>
                    @if($event->city)
                    <div class="border-t border-gold/10 pt-3">
                        <span class="archive-label">Kingdom</span>
                        <div class="mt-1 font-display text-sm" style="color:{{ $event->city->color }}">
                            {{ $event->city->name }}
                        </div>
                    </div>
                    @endif
                    @if($event->start_at)
                    <div class="border-t border-gold/10 pt-3">
                        <span class="archive-label">Starts</span>
                        <div class="mt-1 text-text">{{ $event->start_at->format('d M Y, H:i') }}</div>
                    </div>
                    @endif
                    @if($event->end_at)
                    <div class="border-t border-gold/10 pt-3">
                        <span class="archive-label">Ends</span>
                        <div class="mt-1 text-text">{{ $event->end_at->format('d M Y, H:i') }}</div>
                        <div class="mt-0.5 text-xs text-text-subtle">{{ $event->end_at->diffForHumans() }}</div>
                    </div>
                    @endif
                    <div class="border-t border-gold/10 pt-3">
                        <span class="archive-label">Participants</span>
                        <div class="mt-1 font-display text-gold">{{ $event->participants->count() }}</div>
                    </div>
                </div>
            </div>

            {{-- Requirements --}}
            @if($event->requirements->count())
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Requirements</p>
                <div class="space-y-2">
                    @foreach($requirementResults as $req)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-text-muted capitalize">
                                {{ str_replace('_', ' ', $req['label']) }}
                            </span>
                            <span class="{{ $req['met'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $req['met'] ? '✓' : '✕' }} {{ $req['min'] }}+
                            </span>
                        </div>
                    @endforeach
                    @if($currentCharacter && !$meetsRequirements)
                        <p class="mt-2 text-xs text-rose-400/80">ตัวละครของคุณยังไม่ผ่านเงื่อนไขทั้งหมด</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Rewards --}}
            @if($event->rewards->count())
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Rewards</p>
                <div class="space-y-2">
                    @foreach($event->rewards as $reward)
                        <div class="rounded border border-gold/12 bg-black/20 p-2">
                            @if($reward->gold_amount)
                                <div class="text-xs text-gold">💰 {{ $reward->gold_amount }} Gold</div>
                            @endif
                            @if($reward->exp_amount)
                                <div class="text-xs text-blue-300">⭐ {{ $reward->exp_amount }} EXP</div>
                            @endif
                            @if($reward->item)
                                <div class="text-xs text-text-muted">🎁 {{ $reward->item->name }} ×{{ $reward->item_quantity }}</div>
                            @endif
                            @if($reward->note)
                                <div class="mt-1 text-xs text-text-subtle">{{ $reward->note }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </x-slot:left>

    {{-- ── Main column ─────────────────────────────────────────────────── --}}

    {{-- Back --}}
    <div class="mb-4">
        <a href="{{ route('events.index') }}"
           class="inline-flex items-center gap-2 font-display text-xs uppercase tracking-widest text-text-subtle hover:text-gold">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Events
        </a>
    </div>

    {{-- Header --}}
    @php
        $typeColors = [
            'flash'     => ['#f59e0b', '⚡', 'FLASH'],
            'location'  => ['#60a5fa', '📍', 'LOCATION'],
            'story_arc' => ['#a78bfa', '📖', 'STORY ARC'],
            'crisis'    => ['#f87171', '⚠', 'CRISIS'],
        ];
        [$tc, $ti, $tl] = $typeColors[$event->type] ?? ['#c8a84b', '◆', strtoupper($event->type)];
    @endphp

    <div class="archive-panel corner-ornaments mb-6 overflow-hidden">
        <div class="h-1 w-full" style="background:linear-gradient(90deg, {{ $tc }}, transparent)"></div>
        <div class="p-7">
            <div class="mb-3 flex flex-wrap items-center gap-3">
                <span style="font-family:'Cinzel',serif; font-size:9px; letter-spacing:2.5px; color:{{ $tc }}; border:0.5px solid {{ $tc }}66; padding:3px 10px;">
                    {{ $ti }} {{ $tl }}
                </span>
                <span class="font-display text-xs uppercase tracking-widest"
                      style="color:{{ $event->city->color ?? '#c8a84b' }}88">
                    {{ $event->status }}
                </span>
            </div>
            <h1 class="font-decorative mb-4 text-3xl text-gold">{{ $event->title }}</h1>

            @if($event->description)
                <p class="font-chronicle text-lg leading-relaxed text-text-muted">{{ $event->description }}</p>
            @endif

            {{-- Join / Leave button --}}
            <div class="mt-6 flex flex-wrap items-center gap-3">
                @if($currentCharacter)
                    @if($isJoined)
                        <form method="POST" action="{{ route('events.leave', $event->id) }}">
                            @csrf
                            <button type="submit"
                                    class="btn-outline text-sm"
                                    onclick="return confirm('ออกจาก Event นี้?')">
                                ออกจาก Event
                            </button>
                        </form>
                        <span class="font-display text-xs uppercase tracking-widest text-emerald-400">
                            ✓ คุณได้เข้าร่วม Event นี้แล้ว
                        </span>
                    @elseif($event->status === 'active')
                        <form method="POST" action="{{ route('events.join', $event->id) }}">
                            @csrf
                            <button type="submit"
                                    class="btn-primary text-sm"
                                    @if(!$meetsRequirements) title="ไม่ผ่านเงื่อนไข" @endif>
                                @if($meetsRequirements)
                                    เข้าร่วม Event
                                @else
                                    เข้าร่วม (ไม่ผ่านเงื่อนไข)
                                @endif
                            </button>
                        </form>
                    @else
                        <span class="text-sm text-text-subtle">Event นี้ปิดรับสมัครแล้ว</span>
                    @endif
                @else
                    <a href="{{ route('register') }}" class="btn-primary text-sm">สร้างตัวละครเพื่อเข้าร่วม</a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-800 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded border border-rose-800 bg-rose-950/50 px-4 py-3 text-sm text-rose-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- Participants --}}
    @if($event->participants->count())
    <div class="archive-panel p-6">
        <h2 class="font-display mb-4 text-base text-gold">
            Participants ({{ $event->participants->count() }})
        </h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach($event->participants as $ep)
                @php $char = $ep->character; @endphp
                @if($char)
                <a href="{{ route('character.show', $char->id) }}"
                   class="flex items-center gap-3 rounded border border-gold/10 bg-black/20 p-3 transition hover:border-gold/30">
                    <x-avatar-frame
                        :rank="strtolower($char->auto_rank)"
                        :size="40"
                        :initial="mb_substr($char->name, 0, 1)"
                        :color="$char->city->color ?? '#c8a84b'"
                    >
                        @if($char->avatar)
                            <img src="{{ $char->avatar_url }}" alt="{{ $char->name }}"
                                 style="width:100%;height:100%;object-fit:cover;">
                        @endif
                    </x-avatar-frame>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm text-text">{{ $char->name }}</div>
                        <div class="archive-label mt-0.5">{{ $char->auto_rank }}</div>
                    </div>
                </a>
                @endif
            @endforeach
        </div>
    </div>
    @endif

</x-public.shell>
@endsection
