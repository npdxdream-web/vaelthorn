@extends('layouts.app')

@section('title', 'Chronicle Archive')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    {{-- ── Left rail: filters ─────────────────────────────────────────────────── --}}
    <x-slot:left>
        <div class="sticky top-20 space-y-4">
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">กรองตาม</p>
                <form method="GET" action="{{ route('archive.index') }}" class="space-y-3">

                    <div>
                        <label class="mb-1 block text-xs text-text-muted">อาณาจักร</label>
                        <select name="kingdom_id" onchange="this.form.submit()"
                                class="w-full rounded border border-border bg-bg-elevated px-3 py-1.5 text-sm text-text focus:border-gold/50 focus:outline-none">
                            <option value="">— ทั้งหมด —</option>
                            @foreach($kingdoms as $kingdom)
                                <option value="{{ $kingdom->id }}" {{ request('kingdom_id') == $kingdom->id ? 'selected' : '' }}>
                                    {{ $kingdom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(request('kingdom_id'))
                        <div>
                            <label class="mb-1 block text-xs text-text-muted">เมือง</label>
                            <select name="city_id" onchange="this.form.submit()"
                                    class="w-full rounded border border-border bg-bg-elevated px-3 py-1.5 text-sm text-text focus:border-gold/50 focus:outline-none">
                                <option value="">— ทุกเมือง —</option>
                                @foreach($cities as $v)
                                    <option value="{{ $v->id }}" {{ request('city_id') == $v->id ? 'selected' : '' }}>
                                        {{ $v->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if(request('kingdom_id') || request('city_id'))
                        <a href="{{ route('archive.index') }}"
                           class="block w-full rounded border border-gold/20 py-1.5 text-center font-display text-xs uppercase tracking-wider text-gold/60 transition hover:text-gold">
                            ล้างตัวกรอง
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </x-slot:left>

    {{-- ── Header ────────────────────────────────────────────────────────────── --}}
    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-1">บันทึกแห่งประวัติศาสตร์</p>
        <h1 class="font-decorative mb-2 text-3xl text-gold">Chronicle Archive</h1>
        <p class="font-chronicle text-lg text-text-muted">
            เรื่องราวที่ถูกจารึกไว้แล้ว — อ่านได้ แต่ไม่สามารถเขียนต่อได้อีก
        </p>
    </div>

    {{-- ── Thread list ───────────────────────────────────────────────────────── --}}
    @if($threads->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <div class="mb-3 text-4xl text-gold/20">◈</div>
            <p class="font-display text-lg text-gold/40">ยังไม่มีบันทึกที่ถูกจารึก</p>
            <p class="mt-2 text-sm text-text-subtle">เมื่อ Story Arc สิ้นสุด เรื่องราวจะถูกเก็บไว้ที่นี่</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($threads as $thread)
                @php
                    $kingdom = $thread->city?->kingdom;
                @endphp
                <a href="{{ route('thread', $thread->id) }}"
                   class="archive-panel-soft group flex items-start gap-4 p-5 transition hover:border-gold/25 block">

                    {{-- Kingdom color accent --}}
                    <div class="mt-1 h-10 w-1 shrink-0 rounded-full opacity-70"
                         style="background: {{ $kingdom?->color ?? '#c8a84b' }}"></div>

                    <div class="min-w-0 flex-1">
                        <div class="mb-1.5 flex flex-wrap items-center gap-2">
                            @if($kingdom)
                                <span class="archive-label text-[0.6rem]" style="color: {{ $kingdom->color ?? '#c8a84b' }}">
                                    {{ $kingdom->name }}
                                </span>
                            @endif
                            @if($thread->city)
                                <span class="text-xs text-text-subtle">{{ $thread->city->name }}</span>
                            @endif
                            <span class="ml-auto text-xs text-text-subtle">
                                จารึกเมื่อ {{ $thread->archived_at?->diffForHumans() ?? $thread->updated_at->diffForHumans() }}
                            </span>
                        </div>

                        <h3 class="font-display text-base font-semibold leading-snug text-text-primary group-hover:text-gold transition-colors">
                            {{ $thread->title }}
                        </h3>

                        <div class="mt-1.5 flex items-center gap-3 text-xs text-text-subtle">
                            <span>{{ $thread->posts_count }} โพสต์ที่ได้รับอนุมัติ</span>
                            @if($thread->event)
                                <span>· Event: {{ $thread->event->title }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Read-only badge --}}
                    <div class="shrink-0 rounded border border-gold/20 px-2 py-0.5 font-display text-[0.55rem] uppercase tracking-widest text-gold/50">
                        Read Only
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $threads->links() }}
        </div>
    @endif

</x-public.shell>
@endsection
