@extends('layouts.app')

@section('title', 'Vaelthorn — The World of Thiran')

@push('head')
<style>
/* ── Home Hero Banner ──────────────────────────────────────── */
.home-hero {
    position: relative;
    width: 100%;
    height: 70vh;
    min-height: 420px;
    max-height: 820px;
    overflow: hidden;
}
.home-hero__bg {
    position: absolute;
    inset: 0;
    top: -35%;
    bottom: -35%;
    background: url('/images/hero/bg_main_intro.jpg') center 30% / cover no-repeat;
    will-change: transform;
}
.home-hero__fade-top {
    position: absolute; inset: 0; pointer-events: none;
    background: linear-gradient(to bottom,
        rgba(9,8,7,.72) 0%, rgba(9,8,7,.15) 38%, transparent 100%);
}
.home-hero__fade-bot {
    position: absolute; inset: 0; pointer-events: none;
    background: linear-gradient(to top,
        rgba(9,8,7,1) 0%,
        rgba(9,8,7,.92) 12%,
        rgba(9,8,7,.60) 28%,
        rgba(9,8,7,.18) 48%,
        transparent 100%);
}
.home-hero__content {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    text-align: center;
    padding: 0 1.5rem;
    pointer-events: none;
}
.home-hero__logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 1.1rem;
}
.home-hero__logo-main {
    font-family: var(--font-decorative);
    font-weight: 700;
    color: #c8a84b;
    font-size: clamp(2.2rem, 6vw, 4.6rem);
    letter-spacing: 0.14em;
    line-height: 1;
    text-shadow: 0 0 48px rgba(200,168,75,.38), 0 2px 8px rgba(0,0,0,.65);
}
.home-hero__logo-sub {
    font-family: var(--font-decorative);
    font-weight: 700;
    color: rgba(200,168,75,.45);
    font-size: clamp(0.7rem, 1.6vw, 1.05rem);
    letter-spacing: 0.5em;
    margin-top: 0.6rem;
    text-shadow: 0 1px 6px rgba(0,0,0,.5);
}
.home-hero__sub {
    font-family: var(--font-chronicle);
    color: rgba(239,231,210,.72);
    font-size: clamp(0.7rem, 1.1vw, 0.95rem);
    max-width: 56rem;
    white-space: nowrap;
    line-height: 1.75;
    text-shadow: 0 1px 6px rgba(0,0,0,.75);
}
@media (max-width: 1023px) {
    .home-hero__sub { white-space: normal; max-width: 36rem; }
}
@media (max-width: 768px) {
    .home-hero { height: 55vw; min-height: 260px; max-height: 420px; }
    .home-hero__bg { top: 0; bottom: 0; background-position: center 20%; }
    .home-hero__logo-main { font-size: 1.75rem; }
    .home-hero__logo-sub  { font-size: 0.6rem; }
    .home-hero__sub   { font-size: 0.95rem; }
}
@media (prefers-reduced-motion: reduce) {
    .home-hero__bg { will-change: auto; }
    .home-hero__rune { animation: none; opacity: 0; }
}

/* ── Floating rune particles (Hero decoration) ───────────────── */
@keyframes float-rune {
    0%   { transform: translateY(0) translateX(0); opacity: 0; }
    10%  { opacity: 1; }
    90%  { opacity: 1; }
    100% { transform: translateY(-90px) translateX(var(--drift)); opacity: 0; }
}
.home-hero__runes {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}
.home-hero__rune {
    position: absolute;
    user-select: none;
    animation: float-rune var(--dur) var(--delay) ease-in-out infinite;
}
</style>
@endpush

@section('content')

@php
    $heroRuneChars = ['ᚠ','ᚢ','ᚦ','ᚨ','ᚱ','ᚲ','ᚷ','ᚹ','ᚾ','ᛁ','ᛇ','ᛊ','ᛏ','ᛒ','ᛖ'];
    $heroRunes = collect(range(0, 15))->map(function ($i) use ($heroRuneChars) {
        return [
            'x' => mt_rand(0, 10000) / 100,
            'y' => mt_rand(0, 10000) / 100,
            'rune' => $heroRuneChars[array_rand($heroRuneChars)],
            'size' => 10 + mt_rand(0, 1000) / 100,
            'duration' => 12 + mt_rand(0, 1600) / 100,
            'delay' => -1 * mt_rand(0, 2400) / 100,
            'opacity' => 0.06 + mt_rand(0, 1200) / 10000,
            'drift' => (mt_rand(0, 10000) / 10000 - 0.5) * 30,
            'color' => $i % 2 === 0 ? '#c8a84b' : '#a78bfa',
        ];
    });
@endphp

{{-- ── Hero Banner (เต็มจอ, เหนือ layout 3 คอลัมน์) ──────────── --}}
<section class="home-hero" aria-label="Hero banner">
    <div class="home-hero__bg" id="homHeroBg" aria-hidden="true"></div>
    <div class="home-hero__fade-top" aria-hidden="true"></div>
    <div class="home-hero__fade-bot" aria-hidden="true"></div>
    <div class="home-hero__runes" aria-hidden="true">
        @foreach($heroRunes as $r)
            <span class="home-hero__rune"
                  style="left:{{ $r['x'] }}%; top:{{ $r['y'] }}%; font-size:{{ $r['size'] }}px; color:{{ $r['color'] }}; opacity:{{ $r['opacity'] }}; --dur:{{ $r['duration'] }}s; --delay:{{ $r['delay'] }}s; --drift:{{ $r['drift'] }}px;">{{ $r['rune'] }}</span>
        @endforeach
    </div>
    <div class="home-hero__content">
        <div class="home-hero__logo">
            <span class="home-hero__logo-main">VAELTHORN</span>
            <span class="home-hero__logo-sub">CHRONICLES</span>
        </div>
        <p class="home-hero__sub">
            ป่าและทะเลกว้าง ทะเลทรายและภูเขาหิมะ — ทุกเส้นทางคือเรื่องราวที่รอให้ท่านเขียนลงไป
        </p>

        @auth
            @php $homeCharacter = auth()->user()->character; @endphp
            @if($homeCharacter && $homeCharacter->status !== 'active')
                <div class="mt-7 flex flex-col items-center gap-3 rounded border border-gold/30 bg-bg/70 px-6 py-4 backdrop-blur-sm" style="pointer-events:auto">
                    <p class="font-chronicle text-sm text-text-muted sm:text-base">
                        ก่อนก้าวเข้าสู่โลกนี้ ท่านต้องผ่านพิธีเข้าสู่โลกก่อน — บันทึกที่มาของท่านให้ผู้พิทักษ์ทราบ
                    </p>
                    <a href="{{ route('onboarding') }}"
                       class="font-display rounded border border-gold/50 px-5 py-2 text-sm tracking-wide text-gold transition-colors hover:bg-gold/10">
                        เริ่มพิธีเข้าสู่โลก
                    </a>
                </div>
            @endif
        @endauth
    </div>
</section>
<x-public.shell>
    <x-slot:left>
        <div class="sticky top-20">
            <div class="archive-panel overflow-hidden">
                {{-- Echo / Flash Event marquee --}}
                <div class="marquee-bar {{ $flashEvents->isNotEmpty() ? 'is-flash' : '' }}">
                    <div class="marquee-track">
                        @php
                            $marqueeText = $flashEvents->isNotEmpty()
                                ? $flashEvents->map(fn ($e) => '⚡ ' . $e->title)->implode('　　·　　')
                                : ($echoText ?? '');
                        @endphp
                        <span class="marquee-content">{{ $marqueeText }}</span>
                        <span class="marquee-content" aria-hidden="true">{{ $marqueeText }}</span>
                    </div>
                </div>

                {{-- Honored — Top 5 by Level --}}
                <div class="border-b border-gold/10">
                    <div class="honored-header">
                        <div class="honored-title">👑 ผู้ทรงเกียรติแห่งทิรัน</div>
                        <div class="honored-updated">อัปเดตล่าสุด เมื่อสักครู่</div>
                    </div>

                    <div class="honored-list">
                        @forelse($topPlayers as $index => $player)
                            <div class="honored-row {{ $loop->last ? '' : 'honored-row-border' }}">
                                <div class="honored-rank">
                                    @if($loop->first)
                                        <span class="honored-crown">👑</span>
                                    @else
                                        <span class="honored-rank-num honored-rank-{{ $index + 1 }}">{{ $index + 1 }}</span>
                                    @endif
                                </div>

                                <div class="honored-info">
                                    <div class="honored-name honored-name-{{ $index + 1 }}">{{ $player['name'] }}</div>
                                    <div class="honored-meta">
                                        <span class="honored-kingdom">{{ $player['kingdom'] }}</span>
                                        @if($index < 3)
                                            <div class="honored-progress-track">
                                                <div class="honored-progress-fill honored-progress-{{ $index + 1 }}"
                                                     style="width: {{ $player['exp_percent'] }}%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="honored-level honored-level-{{ $index + 1 }}">Lv.{{ $player['level'] }}</div>
                            </div>
                        @empty
                            <p class="text-xs text-text-subtle">ยังไม่มีข้อมูล</p>
                        @endforelse
                    </div>

                    <div class="honored-footer">
                        @if($myRank && $myRank <= 5)
                            คุณติดอันดับ {{ $myRank }} แล้ว!
                        @elseif($myRank)
                            อันดับของคุณ: <span class="honored-my-rank">#{{ $myRank }}</span>
                            — อีก {{ $expToTop5 }} exp ถึงอันดับ 5
                        @else
                            เข้าสู่ระบบเพื่อดูอันดับของคุณ
                        @endif
                    </div>
                </div>

                {{-- Letters to the Council --}}
                @auth
                    @if(auth()->user()->character)
                        <div class="p-4">
                            <button type="button" onclick="toggleCouncilForm()"
                                    class="font-display w-full rounded border border-gold/30 py-2 text-xs uppercase tracking-wider text-gold/70 transition hover:border-gold/50 hover:text-gold">
                                กระซิบถึงสภา
                            </button>
                            <div id="councilFormWrap" class="mt-3 hidden">
                                <form method="POST" action="{{ route('council.store') }}" class="space-y-2">
                                    @csrf
                                    <input type="text" name="subject" maxlength="150" required placeholder="หัวข้อ"
                                           class="input-field text-sm">
                                    <textarea name="body" rows="3" maxlength="2000" required placeholder="รายละเอียด..."
                                              class="input-field text-sm"></textarea>
                                    <button type="submit" class="btn-primary w-full text-xs">ส่งจดหมาย</button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </x-slot:left>

            <div class="archive-panel corner-ornaments mb-6 p-6">
                <p class="archive-label mb-2">Vaelthorn Chronicles</p>
                <h1 class="font-decorative mb-2 text-3xl tracking-wide text-gold">The World of Thiran</h1>
                <p class="font-chronicle text-lg leading-relaxed text-text-muted">
                    Choose your path across legendary cities, each with their own tales to tell.
                </p>
            </div>

            {{-- Kingdoms --}}
            <div class="archive-panel corner-ornaments mb-8 p-10">
                <div class="mx-auto grid max-w-2xl grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3">
                    @foreach($kingdoms as $kingdom)
                        @php $firstCity = $kingdom->cities->first(); @endphp
                        @if($firstCity)
                            <a href="{{ route('city', $firstCity->id) }}" class="group relative flex flex-col items-center">
                                <div class="mb-2 flex h-16 w-16 items-center justify-center rounded-full border-2 bg-bg-elevated/90 text-2xl backdrop-blur-sm transition-all group-hover:scale-110 group-hover:bg-border"
                                     style="border-color: {{ $kingdom->color }}">
                                    {{ $kingdom->icon }}
                                </div>
                                <span class="font-display text-center text-sm transition-colors" style="color: {{ $kingdom->color }}">
                                    {{ $kingdom->name }}
                                </span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- City Cards --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach($kingdoms as $kingdom)
                    <div class="card transition-all hover:border-gold">
                        <div class="border-b px-4 py-3" style="border-color: {{ $kingdom->color }}40; background-color: {{ $kingdom->color }}10">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ $kingdom->icon }}</span>
                                <h3 class="font-display text-lg" style="color: {{ $kingdom->color }}">{{ $kingdom->name }}</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="mb-4 text-sm text-text-muted">{{ $kingdom->description }}</p>
                            <div class="space-y-2">
                                @foreach($kingdom->cities as $city)
                                    <a href="{{ route('city', $city->id) }}"
                                       class="flex items-center justify-between rounded-lg border border-transparent px-3 py-2 text-sm transition-all hover:border-border hover:bg-bg-subtle">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span class="text-text">{{ $city->name }}</span>
                                        </div>
                                        <svg class="h-4 w-4 text-text-subtle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Recent Tales --}}
            @if($recentThreads->count())
                <div class="mt-8">
                    <h2 class="font-display mb-4 text-xl text-gold">Recent Tales</h2>
                    <div class="space-y-3">
                        @foreach($recentThreads as $thread)
                            <a href="{{ route('thread', $thread->id) }}"
                               class="archive-panel-soft group block p-4 transition-all hover:border-gold">
                                <div class="mb-2 flex items-start justify-between">
                                    <h3 class="font-medium text-text group-hover:text-gold">{{ $thread->title }}</h3>
                                    <span class="text-xs text-text-subtle">{{ $thread->updated_at->diffForHumans() }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-text-muted">
                                    <span>{{ $thread->city->name ?? '-' }}</span>
                                    <span>•</span>
                                    <span>{{ $thread->posts_count }} replies</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
</x-public.shell>
@endsection

@push('scripts')
<script>
(function () {
    if (window.innerWidth <= 768) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var bg = document.getElementById('homHeroBg');
    if (!bg) return;
    var ticking = false;
    var FACTOR = 0.28;
    function applyParallax() {
        bg.style.transform = 'translateY(' + (window.scrollY * FACTOR) + 'px)';
        ticking = false;
    }
    window.addEventListener('scroll', function () {
        if (!ticking) {
            window.requestAnimationFrame(applyParallax);
            ticking = true;
        }
    }, { passive: true });
}());

function toggleCouncilForm() {
    var wrap = document.getElementById('councilFormWrap');
    if (wrap) wrap.classList.toggle('hidden');
}
</script>
@endpush
