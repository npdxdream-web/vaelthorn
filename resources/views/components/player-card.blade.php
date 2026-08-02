@props([
    'character',
    'rank' => null,
])

@php
    $stats = $character->stats;
    $kingdom = $character->kingdom;
    $kc = $kingdom->color ?? '#c8a84b';
    $roleLabel = $character->user->role->label();
@endphp

<div class="archive-panel-soft flex items-center gap-4 p-4">
    @if($rank)
        <div class="w-10 shrink-0 text-center">
            <span class="honored-rank-num honored-rank-{{ min($rank, 5) }}" style="font-size:1.4rem">
                {{ $rank }}
            </span>
        </div>
    @endif

    <a href="{{ route('character.show', $character->id) }}" class="shrink-0">
        <x-avatar-frame
            :rank="strtolower($character->custom_frame ?? $character->auto_rank)"
            :size="64"
            :initial="mb_substr($character->name, 0, 1)"
            :color="$kc"
        >
            @if($character->avatar)
                <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" style="width:100%;height:100%;object-fit:cover;">
            @endif
        </x-avatar-frame>
    </a>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('character.show', $character->id) }}" class="font-display text-base text-text transition hover:text-gold">
                {{ $character->name }}
            </a>
            <span class="forum-status-badge rounded border-gold/25 bg-gold/5 text-[0.65rem] text-gold/70">
                {{ $roleLabel }}
            </span>
        </div>
        <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-text-muted">
            <span style="color:{{ $kc }}">{{ $kingdom->name ?? 'ไม่มีอาณาจักร' }}</span>
            <span class="text-text-subtle">·</span>
            <span>{{ $character->title ?? $character->auto_rank }}</span>
        </div>
    </div>

    <div class="shrink-0 text-right">
        <p class="archive-label text-[0.6rem]">Level</p>
        <p class="font-display text-xl text-gold">{{ $stats->level ?? 1 }}</p>
    </div>
</div>
