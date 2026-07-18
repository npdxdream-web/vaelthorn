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
                        $kingdoms = [
                            'Silvaria'  => ['🌲', '#4ade80'],
                            'Aurantia'  => ['⚔', '#f59e0b'],
                            'Kalif'     => ['🏜', '#fb923c'],
                            'Frostwell' => ['❄', '#60a5fa'],
                            'Kyoren'    => ['⛩', '#a78bfa'],
                            'Celestia'  => ['✦', '#c8a84b'],
                        ];
                    @endphp
                    @foreach($kingdoms as $k => [$icon, $color])
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
    <div class="archive-panel corner-ornaments mb-8 p-8 text-center">
        <p class="archive-label mb-2">Vaelthorn</p>
        <h1 class="font-decorative mb-3 text-4xl text-gold">World Chronicles</h1>
        <p class="font-chronicle mx-auto max-w-lg text-xl text-text-muted">
            บันทึกประวัติศาสตร์แห่งโลก — เรื่องราวที่ถูกสร้างขึ้นโดยผู้เล่น สิ้นสุดลงเป็นตำนาน
        </p>
    </div>

    @if($chronicles->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <p class="font-display text-lg text-gold/40">ยังไม่มีบันทึกประวัติศาสตร์</p>
            <p class="mt-2 text-sm text-text-subtle">Chronicles จะถูกสร้างขึ้นหลัง Event สำคัญสิ้นสุดลง</p>
        </div>
    @else
        <div class="space-y-5">
            @foreach($chronicles as $chronicle)
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
                    $excerpt = Str::limit(strip_tags($chronicle->content), 280);
                @endphp
                <article class="archive-panel overflow-hidden transition hover:border-gold/30">
                    <div class="h-0.5" style="background:linear-gradient(90deg,{{ $kcolor }}88,transparent)"></div>
                    <div class="p-6">
                        <div class="mb-4 flex items-start gap-4">
                            <div class="shrink-0 text-center">
                                <div class="font-display text-2xl" style="color:{{ $kcolor }}">
                                    {{ $chronicle->generated_at?->format('d') }}
                                </div>
                                <div class="archive-label text-[0.6rem]" style="color:{{ $kcolor }}88">
                                    {{ $chronicle->generated_at?->format('M Y') }}
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    @if($city)
                                        <span class="archive-label" style="color:{{ $kcolor }}">
                                            {{ $city->name }}
                                        </span>
                                    @endif
                                    @if($chronicle->event)
                                        <span class="archive-label text-text-subtle">
                                            {{ $chronicle->event->title }}
                                        </span>
                                    @endif
                                </div>
                                <p class="font-chronicle text-base leading-relaxed text-text-muted">
                                    {{ $excerpt }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center justify-end border-t border-gold/10 pt-4">
                            <a href="{{ route('chronicles.show', $chronicle->id) }}"
                               class="font-display text-xs uppercase tracking-wider text-gold/70 transition hover:text-gold">
                                Read Chronicle →
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $chronicles->links() }}
        </div>
    @endif

</x-public.shell>
@endsection
