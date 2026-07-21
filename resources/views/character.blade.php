@extends('layouts.app')

@section('title', $character->name . ' — Vaelthorn')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    {{-- ── Left rail ──────────────────────────────────────────────────── --}}
    <x-slot:left>
        <div class="sticky top-20 space-y-4">

            {{-- Kingdom card --}}
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Origin</p>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="archive-label">Kingdom</span>
                        <div class="mt-1 font-display text-sm" style="color:{{ $character->kingdom->color ?? '#c8a84b' }}">
                            {{ $character->kingdom->name ?? '—' }}
                        </div>
                    </div>
                    <div class="border-t border-gold/10 pt-3">
                        <span class="archive-label">Current Location</span>
                        @php $locKingdom = $character->currentKingdom ?? $character->kingdom; @endphp
                        <div class="mt-1 text-text">{{ $locKingdom->name ?? '—' }}{{ $character->currentCity ? ', ' . $character->currentCity->name : '' }}</div>
                    </div>
                    <div class="border-t border-gold/10 pt-3">
                        <span class="archive-label">Rank</span>
                        <div class="mt-1 font-display text-sm text-gold">{{ $character->auto_rank }}</div>
                    </div>
                    <div class="border-t border-gold/10 pt-3">
                        <span class="archive-label">Status</span>
                        <div class="mt-1">
                            <span class="rounded-full border px-2 py-0.5 text-xs
                                {{ $character->status === 'approved' ? 'border-emerald-400/30 bg-emerald-950/20 text-emerald-300'
                                   : 'border-amber-400/30 bg-amber-950/20 text-amber-300' }}">
                                {{ ucfirst($character->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stat bars --}}
            <div class="archive-panel p-5">
                <p class="archive-label mb-4">Attributes</p>
                @php $stats = $character->stats; @endphp
                <div class="space-y-3">
                    @foreach([
                        ['STR', $stats->str ?? 10, 100, '#c8a84b'],
                        ['AGI', $stats->agi ?? 10, 100, '#7ab0d4'],
                        ['INT', $stats->int ?? 10, 100, '#9b8fc8'],
                        ['HP',  $stats->hp  ?? 100, 200, '#c05050'],
                        ['MP',  $stats->mana ?? 50, 200, '#7060b8'],
                    ] as [$label, $val, $max, $color])
                        <div>
                            <div class="mb-1 flex justify-between">
                                <span class="archive-label">{{ $label }}</span>
                                <span class="font-display text-xs text-gold/70">{{ $val }}</span>
                            </div>
                            <div class="h-1 overflow-hidden rounded-full bg-[#1e1c18]">
                                <div class="h-full rounded-full" style="width:{{ min(100, ($val / $max) * 100) }}%; background:linear-gradient(90deg, {{ $color }}55, {{ $color }});"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 border-t border-gold/10 pt-3">
                    <div class="flex justify-between text-sm">
                        <span class="archive-label">Level</span>
                        <span class="font-display text-gold">{{ $stats->level ?? 1 }}</span>
                    </div>
                    <div class="mt-1 h-1 overflow-hidden rounded-full bg-[#1e1c18]">
                        @php $expPct = $stats ? min(100, ($stats->exp / max(1, $stats->exp_to_next)) * 100) : 0; @endphp
                        <div class="h-full rounded-full bg-gradient-to-r from-gold/40 to-gold" style="width:{{ $expPct }}%"></div>
                    </div>
                    <div class="mt-1 flex justify-between">
                        <span class="archive-label">EXP</span>
                        <span class="text-xs text-text-muted">{{ $stats->exp ?? 0 }} / {{ $stats->exp_to_next ?? 100 }}</span>
                    </div>
                </div>
            </div>

            {{-- Active events --}}
            @if($character->events->count())
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Active Events</p>
                <div class="space-y-2">
                    @foreach($character->events as $event)
                        <a href="{{ route('events.show', $event->id) }}"
                           class="block rounded border border-gold/10 bg-black/20 p-2 text-xs text-text-muted transition hover:border-gold/30 hover:text-gold">
                            {{ $event->title }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </x-slot:left>

    {{-- ── Main column ─────────────────────────────────────────────────── --}}

    {{-- Profile header --}}
    <div class="archive-panel corner-ornaments mb-6 p-8">
        <div class="flex flex-col items-center gap-6 sm:flex-row sm:items-start">

            {{-- Avatar frame --}}
            <div class="flex-shrink-0">
                <x-avatar-frame
                    :rank="strtolower($character->custom_frame ?? $character->auto_rank)"
                    :size="240"
                    :height="450"
                    :initial="mb_substr($character->name, 0, 1)"
                    :color="$character->kingdom->color ?? '#c8a84b'"
                >
                    @if($character->avatar)
                        <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}"
                             style="width:100%;height:100%;object-fit:cover;">
                    @endif
                </x-avatar-frame>
            </div>

            {{-- Info --}}
            <div class="min-w-0 flex-1">
                <div class="mb-1 flex items-start justify-between gap-3">
                    <p class="archive-label">{{ $character->title ?? $character->auto_rank }}</p>
                    @if(auth()->id() === $character->user_id)
                        <a href="{{ route('character.edit') }}"
                           class="shrink-0 rounded border border-gold/25 bg-gold/5 px-3 py-1 font-display text-xs uppercase tracking-wider text-gold/70 transition hover:bg-gold/10 hover:text-gold">
                            ✎ แก้ไข
                        </a>
                    @endif
                </div>
                <h1 class="font-decorative mb-1 text-3xl text-gold">{{ $character->name }}</h1>
                <p class="font-display mb-4 text-sm tracking-widest"
                   style="color:{{ $character->kingdom->color ?? '#c8a84b' }}">
                    {{ $character->kingdom->name ?? '—' }}
                </p>

                <div class="gold-divider mb-4"><span class="gold-diamond"></span></div>

                <div class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                    @foreach([
                        ['Posts', $character->posts_count, 'chronicles'],
                        ['Level', $character->stats->level ?? 1, 'lvl'],
                        ['Gold',  $character->gold ?? 0, 'g'],
                        ['Badges', $character->badges->count(), 'earned'],
                    ] as [$label, $value, $unit])
                        <div class="border border-gold/12 bg-black/20 p-3 text-center">
                            <div class="font-display text-lg text-gold">{{ $value }}</div>
                            <div class="archive-label mt-0.5">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Backstory --}}
        @if($character->backstory)
            <div class="mt-6 border-t border-gold/10 pt-5">
                <p class="archive-label mb-2">Backstory</p>
                <p class="font-chronicle text-lg leading-relaxed text-text-muted">{{ $character->backstory }}</p>
            </div>
        @endif
    </div>

    {{-- Badges --}}
    @if($character->badges->count())
    <div class="archive-panel mb-6 p-6">
        <h2 class="font-display mb-4 text-base text-gold">Honours & Medals</h2>
        <div class="flex flex-wrap gap-3">
            @foreach($character->badges->sortByDesc('acquired_at') as $cb)
                <div class="flex items-center gap-2 rounded border border-gold/20 bg-gold/5 px-3 py-2">
                    @if($cb->badge?->icon)
                        <span class="text-xl">{{ $cb->badge->icon }}</span>
                    @endif
                    <div>
                        <div class="font-display text-xs text-gold">{{ $cb->badge?->name ?? '—' }}</div>
                        @if($cb->badge?->description)
                            <div class="text-xs text-text-muted">{{ $cb->badge->description }}</div>
                        @endif
                        @if($cb->acquired_at)
                            <div class="mt-0.5 text-[0.65rem] text-text-subtle">{{ $cb->acquired_at->format('d M Y') }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent Chronicles --}}
    <div class="archive-panel p-6">
        <h2 class="font-display mb-4 text-base text-gold">Recent Chronicles</h2>
        @forelse($recentPosts as $post)
            <a href="{{ route('thread', $post->thread_id) }}"
               class="archive-panel-soft group mb-3 block p-4 transition hover:border-gold">
                <div class="mb-1 flex items-start justify-between gap-4">
                    <h3 class="font-medium text-text group-hover:text-gold">
                        {{ $post->thread->title ?? '—' }}
                    </h3>
                    <span class="shrink-0 text-xs text-text-subtle">{{ $post->created_at->diffForHumans() }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-text-muted">
                    <span>{{ $post->thread->city->name ?? '—' }}</span>
                    <span>•</span>
                    <span style="color:{{ $post->thread->city->kingdom->color ?? '#c8a84b' }}">
                        {{ $post->thread->city->kingdom->name ?? '—' }}
                    </span>
                </div>
                <p class="mt-2 line-clamp-2 text-sm text-text-muted/80">
                    {{ strip_tags($post->content) }}
                </p>
            </a>
        @empty
            <p class="text-sm text-text-subtle">ยังไม่มี chronicles ที่ได้รับการอนุมัติ</p>
        @endforelse
    </div>

</x-public.shell>
@endsection
