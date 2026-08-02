@extends('layouts.app')

@section('title', 'ทำเนียบสมาชิก — Vaelthorn')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    <x-slot:left>
        <div class="sticky top-20 space-y-4">
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">ทำเนียบสมาชิก</p>
                <p class="font-chronicle text-sm leading-relaxed text-text-muted">
                    รายชื่อตัวละครที่ผ่านการอนุมัติแล้วทั้งหมดในโลก Vaelthorn
                </p>
            </div>

            <div class="archive-panel p-5">
                <a href="{{ route('leaderboard.index') }}" class="archive-label flex items-center gap-2 transition hover:text-gold">
                    🏆 ดูอันดับสมาชิก
                </a>
            </div>
        </div>
    </x-slot:left>

    <div class="archive-panel corner-ornaments mb-6 p-8 text-center">
        <p class="archive-label mb-2">Vaelthorn</p>
        <h1 class="font-decorative mb-3 text-4xl text-gold">ทำเนียบสมาชิก</h1>
        <p class="font-chronicle mx-auto max-w-lg text-xl text-text-muted">
            รายชื่อผู้เดินทางทุกคนที่ได้ก้าวเข้าสู่โลกแห่งนี้
        </p>
    </div>

    {{-- Search + Filter --}}
    <form method="GET" action="{{ route('players.index') }}" class="mb-6 flex flex-col gap-3 border-b border-gold/10 pb-6 sm:flex-row sm:items-center">
        <input
            type="text"
            name="search"
            value="{{ $search }}"
            placeholder="ค้นหาชื่อตัวละคร..."
            class="input-field sm:max-w-xs"
        >
        <select name="kingdom" class="input-field sm:max-w-xs">
            <option value="">ทุกอาณาจักร</option>
            @foreach($kingdoms as $k)
                <option value="{{ $k->id }}" {{ (string) $kingdomId === (string) $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-outline">ค้นหา</button>
        @if($search !== '' || $kingdomId)
            <a href="{{ route('players.index') }}" class="btn-outline">ล้างตัวกรอง</a>
        @endif
    </form>

    @if($players->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <p class="font-display text-lg text-gold/40">ไม่พบสมาชิกที่ตรงกับเงื่อนไข</p>
            <p class="mt-2 text-sm text-text-subtle">
                @if($search !== '' || $kingdomId)
                    ลองเปลี่ยนคำค้นหาหรือตัวกรองอาณาจักร
                @else
                    ยังไม่มีตัวละครที่ผ่านการอนุมัติในระบบ
                @endif
            </p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($players as $player)
                <x-player-card :character="$player" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $players->links() }}
        </div>
    @endif

</x-public.shell>
@endsection
