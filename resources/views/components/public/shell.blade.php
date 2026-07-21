@props([
    'characterStatus' => null,
])

@php
    // Resolve character: explicit prop → auth user's character → null
    $resolvedCharacter = $characterStatus
        ?? (auth()->check()
            ? auth()->user()->character()->with(['kingdom', 'currentKingdom', 'currentCity', 'stats', 'badges'])->withCount('posts')->first()
            : null);
@endphp

<div class="public-shell">
    {{-- 3-column layout: 260px left rail | 1fr main | 260px right rail --}}
    <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[260px_minmax(0,1fr)_260px]">

        <aside class="min-w-0">
            {{ $left ?? '' }}
        </aside>

        <div class="min-w-0">
            {{ $slot }}
        </div>

        <aside class="sticky top-20 min-w-0">
            @include('partials.character-module', ['character' => $resolvedCharacter])

            {{ $rail ?? '' }}
        </aside>

    </div>
</div>
