@extends('layouts.app')

@section('title', 'อันดับสมาชิก — Vaelthorn')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    <x-slot:left>
        <div class="sticky top-20 space-y-4">
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">อันดับสมาชิก</p>
                <p class="font-chronicle text-sm leading-relaxed text-text-muted">
                    จัดอันดับผู้เดินทางทั้งหมดตามเลเวลปัจจุบัน (Prototype)
                </p>
            </div>

            <div class="archive-panel p-5">
                <a href="{{ route('players.index') }}" class="archive-label flex items-center gap-2 transition hover:text-gold">
                    📜 ดูทำเนียบสมาชิกทั้งหมด
                </a>
            </div>
        </div>
    </x-slot:left>

    <div class="archive-panel corner-ornaments mb-6 p-8 text-center">
        <p class="archive-label mb-2">Vaelthorn</p>
        <h1 class="font-decorative mb-3 text-4xl text-gold">อันดับสมาชิก</h1>
        <p class="font-chronicle mx-auto max-w-lg text-xl text-text-muted">
            เรียงลำดับตามเลเวล — จากมากไปน้อย
        </p>
    </div>

    @if($players->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <p class="font-display text-lg text-gold/40">ยังไม่มีข้อมูลผู้เล่น</p>
            <p class="mt-2 text-sm text-text-subtle">อันดับจะปรากฏเมื่อมีตัวละครที่ผ่านการอนุมัติในระบบ</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($players as $index => $player)
                <x-player-card :character="$player" :rank="$index + 1" />
            @endforeach
        </div>
    @endif

</x-public.shell>
@endsection
