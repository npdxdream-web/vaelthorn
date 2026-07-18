@if(isset($character) && $character)
@php
    $stats    = $character->stats;
    $cityColor = $character->city->color ?? '#c8a84b';
    $initial  = strtoupper(mb_substr($character->name, 0, 1));
    $statBars = [
        'STR' => ['val' => $stats?->str  ?? 0, 'max' => 100, 'color' => '#c8a84b'],
        'AGI' => ['val' => $stats?->agi  ?? 0, 'max' => 100, 'color' => '#7ab0d4'],
        'HP'  => ['val' => $stats?->hp   ?? 0, 'max' => 200, 'color' => '#c05050'],
        'MP'  => ['val' => $stats?->mana ?? 0, 'max' => 200, 'color' => '#7060b8'],
        'REP' => ['val' => min($character->posts_count ?? 0, 100), 'max' => 100, 'color' => '#50a050'],
    ];
@endphp

<div id="charModule" class="w-full">
    <section class="right-card-shell">

        {{-- Header --}}
        <div class="right-card-header">
            <span class="flex items-center gap-2">
                <svg class="h-3 w-3 text-gold/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 21c1.4-4 4-6 8-6s6.6 2 8 6"/>
                </svg>
                MY CHARACTER
            </span>
            <a href="{{ route('character.edit') }}" class="text-text-subtle transition hover:text-gold" title="Edit Character">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </a>
        </div>

        {{-- ── MINI (collapsed) ─────────────────────────────── --}}
        <div id="charMini" class="right-card-mini">
            <div class="right-mini-avatar" style="border-color:{{ $cityColor }}99">
                @if($character->avatar)
                    <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="h-full w-full object-cover">
                @else
                    <span style="color:{{ $cityColor }}">{{ $initial }}</span>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <a href="{{ route('character.show', $character->id) }}"
                   class="block truncate font-display text-sm text-gold transition hover:underline">
                    {{ $character->name }}
                </a>
                <p class="right-character-role mt-0.5">
                    Lv.{{ $stats->level ?? 1 }} · {{ $character->auto_rank }}
                </p>
            </div>
        </div>

        {{-- ── FULL ─────────────────────────────────────────── --}}
        <div id="charFull" class="right-card-full">
            <div class="right-character-body">

                {{-- Avatar --}}
                <div class="right-avatar-circle" style="border-color:{{ $cityColor }}88">
                    @if($character->avatar)
                        <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="h-full w-full object-cover">
                    @else
                        <span>{{ $initial }}</span>
                    @endif
                </div>

                {{-- Name --}}
                <div class="mt-3 text-center">
                    <a href="{{ route('character.show', $character->id) }}"
                       class="right-character-name hover:underline">{{ $character->name }}</a>
                    <div class="right-character-role">{{ $character->title ?? $character->auto_rank ?? 'Stranger' }}</div>
                </div>

                <div class="right-thin-rule my-3"><span></span><i></i><span></span></div>

                {{-- Info rows — compact divider style --}}
                <div class="divide-y" style="border-color:rgba(200,168,75,.08)">
                    @foreach([
                        'KINGDOM'  => $character->city?->name ?? '-',
                        'LOCATION' => $character->currentCity?->name ?? $character->city?->name ?? '-',
                        'RANK'     => ucfirst($character->auto_rank ?? 'Stranger'),
                        'POSTS'    => ($character->posts_count ?? 0) . ' chronicles',
                    ] as $label => $value)
                        <div class="right-info-row py-2">
                            <span>{{ $label }}</span>
                            <strong>{{ $value }}</strong>
                        </div>
                    @endforeach
                </div>

                {{-- Honours --}}
                <div class="mt-4">
                    <div class="right-section-label">Honours & Medals</div>
                    <div class="mt-1.5 text-[0.78rem] italic text-text-subtle">
                        @if(($character->badges ?? collect())->count())
                            {{ $character->badges->take(2)->pluck('name')->join(', ') }}
                        @else
                            No honours recorded.
                        @endif
                    </div>
                </div>

                <div class="right-thin-rule my-3"><span></span><i></i><span></span></div>

                {{-- Attributes --}}
                <div>
                    <div class="right-section-label">Attributes</div>
                    <div class="mt-2.5 space-y-2">
                        @foreach($statBars as $stat => $cfg)
                            @php $pct = min(100, ($cfg['val'] / max(1, $cfg['max'])) * 100); @endphp
                            <div class="right-attr-row">
                                <div class="right-attr-track">
                                    <div style="width:{{ $pct }}%;background:linear-gradient(90deg,{{ $cfg['color'] }}60,{{ $cfg['color'] }}cc);box-shadow:0 0 4px {{ $cfg['color'] }}50;"></div>
                                </div>
                                <span>{{ $cfg['val'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="right-thin-rule my-3"><span></span><i></i><span></span></div>

                {{-- Quick Actions --}}
                <div class="grid grid-cols-3 gap-1.5">

                    {{-- Inventory --}}
                    <a href="{{ route('inventory') }}"
                       title="Inventory"
                       class="group relative flex flex-col items-center gap-1 rounded border border-gold/12 bg-[#0e0c09] py-2 transition hover:border-gold/30 hover:bg-[#141210]">
                        <svg class="h-4 w-4 text-gold/45 transition group-hover:text-gold/75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="font-display text-[0.5rem] uppercase tracking-wider text-gold/35 transition group-hover:text-gold/60">Inv</span>
                    </a>

                    {{-- Notifications + badge --}}
                    <a href="{{ route('notifications.index') }}"
                       title="Notifications"
                       class="group relative flex flex-col items-center gap-1 rounded border border-gold/12 bg-[#0e0c09] py-2 transition hover:border-gold/30 hover:bg-[#141210]">
                        @if(!empty($unreadNotifCount) && $unreadNotifCount > 0)
                            <span class="absolute right-1 top-1 flex h-3.5 min-w-3.5 items-center justify-center rounded-full bg-red-600 px-0.5 font-display text-[0.45rem] font-bold leading-none text-white">
                                {{ $unreadNotifCount > 99 ? '99+' : $unreadNotifCount }}
                            </span>
                        @endif
                        <svg class="h-4 w-4 text-gold/45 transition group-hover:text-gold/75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="font-display text-[0.5rem] uppercase tracking-wider text-gold/35 transition group-hover:text-gold/60">Noti</span>
                    </a>

                    {{-- Recent Activity --}}
                    <a href="{{ route('activity.index') }}"
                       title="Recent Activity"
                       class="group relative flex flex-col items-center gap-1 rounded border border-gold/12 bg-[#0e0c09] py-2 transition hover:border-gold/30 hover:bg-[#141210]">
                        <svg class="h-4 w-4 text-gold/45 transition group-hover:text-gold/75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-display text-[0.5rem] uppercase tracking-wider text-gold/35 transition group-hover:text-gold/60">Activity</span>
                    </a>

                </div>
            </div>
        </div>

    </section>
</div>

<script>
(function () {
    var mod  = document.getElementById('charModule');
    var mini = document.getElementById('charMini');
    if (!mod) return;

    var pinned = false; // user manually expanded while scrolled

    // บนหน้าที่มี hero banner: module ขยายอยู่จนกว่าจะเลื่อนพ้น hero
    // ไปอีก ~1 viewport (ให้มีเวลาอ่านข้อมูลก่อน) จึงค่อยพับ
    var heroEl = document.querySelector('.home-hero');
    var THRESHOLD = heroEl
        ? heroEl.offsetHeight + window.innerHeight
        : 80;

    function tick() {
        if (window.scrollY > THRESHOLD) {
            if (!pinned) mod.classList.add('is-collapsed');
        } else {
            mod.classList.remove('is-collapsed');
            pinned = false;
        }
    }

    // Click mini → expand and pin open until scroll returns to top
    if (mini) {
        mini.style.cursor = 'pointer';
        mini.title = 'Click to expand';
        mini.addEventListener('click', function () {
            mod.classList.remove('is-collapsed');
            pinned = true;
        });
    }

    window.addEventListener('scroll', tick, { passive: true });
    tick();
}());
</script>
@endif
