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
.home-hero__title {
    font-family: 'Cinzel Decorative', 'Cinzel', serif;
    color: #c8a84b;
    font-size: clamp(1.75rem, 4vw, 3.4rem);
    letter-spacing: 0.08em;
    text-shadow: 0 0 48px rgba(200,168,75,.38), 0 2px 8px rgba(0,0,0,.65);
    margin-bottom: 1.1rem;
    line-height: 1.2;
}
.home-hero__sub {
    font-family: 'EB Garamond', Georgia, serif;
    color: rgba(239,231,210,.72);
    font-size: clamp(1rem, 1.8vw, 1.3rem);
    max-width: 36rem;
    line-height: 1.75;
    text-shadow: 0 1px 6px rgba(0,0,0,.75);
}
@media (max-width: 768px) {
    .home-hero { height: 55vw; min-height: 260px; max-height: 420px; }
    .home-hero__bg { top: 0; bottom: 0; background-position: center 20%; }
    .home-hero__title { font-size: 1.45rem; }
    .home-hero__sub   { font-size: 0.95rem; }
}
@media (prefers-reduced-motion: reduce) {
    .home-hero__bg { will-change: auto; }
}
</style>
@endpush

@section('content')

{{-- ── Hero Banner (เต็มจอ, เหนือ layout 3 คอลัมน์) ──────────── --}}
<section class="home-hero" aria-label="Hero banner">
    <div class="home-hero__bg" id="homHeroBg" aria-hidden="true"></div>
    <div class="home-hero__fade-top" aria-hidden="true"></div>
    <div class="home-hero__fade-bot" aria-hidden="true"></div>
    <div class="home-hero__content">
        <h2 class="home-hero__title">โลกกว้างไร้รอยต่อ</h2>
        <p class="home-hero__sub">
            ป่าและทะเลกว้าง ทะเลทรายและภูเขาหิมะ — ทุกเส้นทางคือเรื่องราวที่รอให้ท่านเขียนลงไป
        </p>
    </div>
</section>
<x-public.shell>
    <x-slot:left>
        <div class="sticky top-20">
            <div class="archive-panel p-5">
                <h3 class="font-display mb-4 text-base text-gold">Kingdoms</h3>
                <div class="space-y-2">
                    @foreach($cities as $city)
                        @php $firstVillage = $city->villages->first(); @endphp
                        <a href="{{ $firstVillage ? route('village', $firstVillage->id) : '#' }}"
                           class="flex items-center gap-2.5 rounded border border-transparent px-2 py-1.5 text-sm text-text-muted transition-colors hover:border-gold/20 hover:text-gold">
                            <span class="text-base leading-none">{{ $city->icon }}</span>
                            <span style="color:{{ $city->color }}">{{ $city->name }}</span>
                        </a>
                    @endforeach
                </div>
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

            {{-- World Map --}}
            <div class="archive-panel relative mb-8 overflow-hidden">
                <div class="relative h-96">
                    <img
                        src="https://images.unsplash.com/photo-1520299607509-dcd935f9a839?auto=format&fit=crop&w=1080&q=80"
                        alt="Map of Thiran"
                        class="h-full w-full object-cover opacity-30"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-bg via-bg/20 to-transparent"></div>

                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="grid grid-cols-2 gap-16">
                            @foreach($cities as $city)
                                @php $firstVillage = $city->villages->first(); @endphp
                                @if($firstVillage)
                                    <a href="{{ route('village', $firstVillage->id) }}" class="group relative flex flex-col items-center">
                                        <div class="mb-2 flex h-16 w-16 items-center justify-center rounded-full border-2 bg-bg-elevated/90 text-2xl backdrop-blur-sm transition-all group-hover:scale-110 group-hover:bg-border"
                                             style="border-color: {{ $city->color }}">
                                            {{ $city->icon }}
                                        </div>
                                        <span class="font-display text-sm transition-colors" style="color: {{ $city->color }}">
                                            {{ $city->name }}
                                        </span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- City Cards --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach($cities as $city)
                    <div class="card transition-all hover:border-gold">
                        <div class="border-b px-4 py-3" style="border-color: {{ $city->color }}40; background-color: {{ $city->color }}10">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ $city->icon }}</span>
                                <h3 class="font-display text-lg" style="color: {{ $city->color }}">{{ $city->name }}</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="mb-4 text-sm text-text-muted">{{ $city->description }}</p>
                            <div class="space-y-2">
                                @foreach($city->villages as $village)
                                    <a href="{{ route('village', $village->id) }}"
                                       class="flex items-center justify-between rounded-lg border border-transparent px-3 py-2 text-sm transition-all hover:border-border hover:bg-bg-subtle">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span class="text-text">{{ $village->name }}</span>
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
                                    <span>{{ $thread->village->name ?? '-' }}</span>
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
</script>
@endpush
