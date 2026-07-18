@extends('layouts.app')

@section('title', 'Chronicle — ' . ($chronicle->event?->title ?? 'Entry'))

@section('content')
<x-public.shell :character-status="$currentCharacter">

    {{-- ── Left rail ──────────────────────────────────────────────────── --}}
    <x-slot:left>
        <div class="sticky top-20 space-y-4">

            {{-- Chronicle meta --}}
            @php
                $city = $chronicle->event?->city;
                $kingdomColors = [
                    'Silvaria'  => '#4ade80',
                    'Aurantia'  => '#f59e0b',
                    'Kalif'     => '#fb923c',
                    'Frostwell' => '#60a5fa',
                    'Kyoren'    => '#a78bfa',
                    'Celestia'  => '#c8a84b',
                ];
                $kcolor = $city ? ($kingdomColors[$city->name] ?? '#c8a84b') : '#c8a84b';
            @endphp

            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Chronicle Info</p>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="archive-label text-[0.6rem] text-text-subtle">Recorded</p>
                        <p class="font-chronicle text-base text-text-primary">
                            {{ $chronicle->generated_at?->format('d M Y') }}
                        </p>
                    </div>
                    @if($city)
                        <div>
                            <p class="archive-label text-[0.6rem] text-text-subtle">Kingdom</p>
                            <p class="font-display text-sm" style="color:{{ $kcolor }}">
                                {{ $city->name }}
                            </p>
                        </div>
                        <div>
                            <p class="archive-label text-[0.6rem] text-text-subtle">Capital</p>
                            <p class="text-sm text-text-muted">{{ $city->name }}</p>
                        </div>
                    @endif
                    @if($chronicle->event)
                        <div>
                            <p class="archive-label text-[0.6rem] text-text-subtle">Related Event</p>
                            <a href="{{ route('events.show', $chronicle->event_id) }}"
                               class="text-sm text-gold/70 underline-offset-2 transition hover:text-gold hover:underline">
                                {{ $chronicle->event->title }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Navigation --}}
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Navigation</p>
                <div class="space-y-2">
                    @if($prev)
                        <a href="{{ route('chronicles.show', $prev->id) }}"
                           class="block rounded border border-gold/15 bg-gold/3 p-3 transition hover:border-gold/30 hover:bg-gold/5">
                            <p class="archive-label text-[0.6rem] text-text-subtle">← Previous</p>
                            <p class="mt-1 font-display text-xs text-gold/70">
                                {{ Str::limit($prev->event?->title ?? 'Chronicle', 30) }}
                            </p>
                        </a>
                    @endif
                    @if($next)
                        <a href="{{ route('chronicles.show', $next->id) }}"
                           class="block rounded border border-gold/15 bg-gold/3 p-3 transition hover:border-gold/30 hover:bg-gold/5">
                            <p class="archive-label text-[0.6rem] text-text-subtle">Next →</p>
                            <p class="mt-1 font-display text-xs text-gold/70">
                                {{ Str::limit($next->event?->title ?? 'Chronicle', 30) }}
                            </p>
                        </a>
                    @endif
                    <a href="{{ route('chronicles.index') }}"
                       class="block text-center font-display text-xs uppercase tracking-wider text-text-subtle transition hover:text-gold">
                        All Chronicles
                    </a>
                </div>
            </div>
        </div>
    </x-slot:left>

    {{-- ── Main ─────────────────────────────────────────────────────────── --}}

    {{-- Header --}}
    <div class="archive-panel corner-ornaments mb-6 overflow-hidden">
        <div class="h-1" style="background:linear-gradient(90deg,{{ $kcolor }}88,transparent)"></div>
        <div class="p-8">
            <p class="archive-label mb-2" style="color:{{ $kcolor }}">
                {{ $city ? $city->name : 'World Chronicle' }}
            </p>
            @if($chronicle->event)
                <h1 class="font-decorative mb-3 text-4xl text-gold">
                    {{ $chronicle->event->title }}
                </h1>
            @endif
            <div class="flex items-center gap-3 text-sm text-text-subtle">
                <span>{{ $chronicle->generated_at?->format('d M Y, H:i') }}</span>
                @if($chronicle->event)
                    <span>·</span>
                    <span class="rounded border border-gold/20 px-2 py-0.5 font-display text-xs text-gold/60">
                        {{ strtoupper($chronicle->event->type) }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Chronicle body --}}
    <div class="archive-panel p-8">
        <div class="font-chronicle chronicle-reading prose prose-invert max-w-none">
            {!! nl2br(e($chronicle->content)) !!}
        </div>
    </div>

    {{-- Footer nav --}}
    <div class="mt-6 flex items-center justify-between">
        @if($prev)
            <a href="{{ route('chronicles.show', $prev->id) }}"
               class="font-display text-xs uppercase tracking-wider text-gold/60 transition hover:text-gold">
                ← Previous Chronicle
            </a>
        @else
            <span></span>
        @endif

        @if($next)
            <a href="{{ route('chronicles.show', $next->id) }}"
               class="font-display text-xs uppercase tracking-wider text-gold/60 transition hover:text-gold">
                Next Chronicle →
            </a>
        @endif
    </div>

</x-public.shell>
@endsection
