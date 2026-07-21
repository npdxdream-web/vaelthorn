@extends('layouts.app')

@section('title', 'World Chronicles')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    {{-- ── Left rail ──────────────────────────────────────────────────── --}}
    <x-slot:left>
        <div class="sticky top-20 space-y-4">
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">The Chronicles</p>
                <p class="font-chronicle text-sm leading-relaxed text-text-muted">
                    บันทึกประวัติศาสตร์ของโลก Vaelthorn — เรื่องราวที่ถูกบันทึกหลังจาก Event สำคัญสิ้นสุดลง
                    เป็นส่วนหนึ่งของ World Canon ที่เปลี่ยนแปลงโลกอย่างถาวร
                </p>
            </div>

            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Kingdoms</p>
                <div class="space-y-1">
                    @php
                        $kingdomIcons = [
                            'Silvaria'  => ['🌲', '#4ade80'],
                            'Aurantia'  => ['⚔', '#f59e0b'],
                            'Kalif'     => ['🏜', '#fb923c'],
                            'Frostwell' => ['❄', '#60a5fa'],
                            'Kyoren'    => ['⛩', '#a78bfa'],
                            'Celestia'  => ['✦', '#c8a84b'],
                        ];
                    @endphp
                    @foreach($kingdomIcons as $k => [$icon, $color])
                        <div class="flex items-center gap-2 px-2 py-1 text-sm text-text-muted">
                            <span>{{ $icon }}</span>
                            <span style="color:{{ $color }}">{{ $k }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-slot:left>

    {{-- ── Main ─────────────────────────────────────────────────────────── --}}
    <div class="archive-panel corner-ornaments mb-6 p-8 text-center">
        <p class="archive-label mb-2">Vaelthorn</p>
        <h1 class="font-decorative mb-3 text-4xl text-gold">World Chronicles</h1>
        <p class="font-chronicle mx-auto max-w-lg text-xl text-text-muted">
            บันทึกประวัติศาสตร์แห่งโลก — เรื่องราวที่ถูกสร้างขึ้นโดยผู้เล่น สิ้นสุดลงเป็นตำนาน
        </p>
    </div>

    {{-- ── Category tabs ──────────────────────────────────────────────── --}}
    @if($categories->isNotEmpty())
        <div class="mb-8 flex flex-wrap items-center gap-2.5 border-b border-gold/10 pb-5">
            <a href="{{ route('chronicles.index') }}"
               class="font-display rounded-full border px-4 py-1.5 text-xs uppercase tracking-wider transition
                      {{ ! $category ? 'border-gold/50 bg-gold/10 text-gold shadow-[0_0_16px_rgba(200,168,75,0.15)]' : 'border-border text-text-muted hover:text-gold hover:border-gold/30' }}">
                ทั้งหมด
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('chronicles.index', ['category' => $cat]) }}"
                   class="font-display rounded-full border px-4 py-1.5 text-xs uppercase tracking-wider transition
                          {{ $category === $cat ? 'border-gold/50 bg-gold/10 text-gold shadow-[0_0_16px_rgba(200,168,75,0.15)]' : 'border-border text-text-muted hover:text-gold hover:border-gold/30' }}">
                    {{ strtoupper($cat) }}
                </a>
            @endforeach
        </div>
    @endif

    @if($chronicles->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <p class="font-display text-lg text-gold/40">ยังไม่มีบันทึกประวัติศาสตร์</p>
            <p class="mt-2 text-sm text-text-subtle">
                @if($category)
                    ยังไม่มีบันทึกในหมวด "{{ $category }}"
                @else
                    Chronicles จะถูกสร้างขึ้นหลัง Event สำคัญสิ้นสุดลง
                @endif
            </p>
        </div>
    @else
        <div class="chronicle-grid">
            @foreach($chronicles as $chronicle)
                @php
                    $kingdom = $chronicle->display_kingdom;
                    $kcolor = $kingdom?->color ?? '#c8a84b';
                    $kicon = $kingdom?->icon ?? '✦';
                    $cover = $chronicle->cover_image_url;
                @endphp
                <a href="{{ route('chronicles.show', $chronicle->id) }}" class="chronicle-card">
                    @if($cover)
                        <div class="chronicle-card-media" style="background-image:url('{{ $cover }}')"></div>
                    @else
                        <div class="chronicle-card-media"
                             style="background:radial-gradient(circle at 30% 18%, {{ $kcolor }}59, transparent 60%), linear-gradient(160deg, {{ $kcolor }}3d 0%, #120c04 78%);"></div>
                        <div class="chronicle-card-watermark" style="color:{{ $kcolor }}">{{ $kicon }}</div>
                    @endif
                    <div class="chronicle-card-scrim"></div>
                    <div class="chronicle-card-body">
                        @if($chronicle->category)
                            <span class="chronicle-card-tag">{{ strtoupper($chronicle->category) }}</span>
                        @endif
                        <p class="chronicle-card-date">{{ $chronicle->generated_at?->format('d M Y') }}</p>
                        <h2 class="chronicle-card-title">{{ $chronicle->display_title }}</h2>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $chronicles->links() }}
        </div>
    @endif

</x-public.shell>
@endsection
