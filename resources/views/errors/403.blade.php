@extends('layouts.app')

@section('content')
@php
    $path = request()->path();
    $destination = $path;

    try {
        if (preg_match('/^cities\/(\d+)/', $path, $m)) {
            $place = \App\Models\City::with('kingdom')->find($m[1]);
            if ($place) $destination = $place->name . ', ' . ($place->kingdom?->name ?? '');
        } elseif (preg_match('/^threads\/(\d+)/', $path, $m)) {
            $place = \App\Models\Thread::with('city.kingdom')->find($m[1]);
            if ($place) $destination = ($place->city?->name ?? '') . ', ' . ($place->city?->kingdom?->name ?? '');
        } elseif (preg_match('/^posts\/(\d+)/', $path, $m)) {
            $place = \App\Models\Post::with('thread.city.kingdom')->find($m[1]);
            if ($place) $destination = ($place->thread?->city?->name ?? '') . ', ' . ($place->thread?->city?->kingdom?->name ?? '');
        } elseif (preg_match('/^admin/', $path)) {
            $destination = 'Council Chamber (Admin)';
        }
    } catch (\Exception $e) {}
@endphp

<div style="min-height:80vh; display:flex; align-items:center; justify-content:center; padding:2rem;">

  <div style="background:#12100a; border:1px solid #c8a84b55; max-width:640px; width:100%;">

    {{-- Header --}}
    <div style="border-bottom:1px solid #c8a84b33; padding:24px 40px; display:flex; align-items:center; justify-content:space-between;">
      <div>
        <span style="font-family:var(--font-display); font-size:11px; letter-spacing:3px; color:#6b5f45; display:block; margin-bottom:7px;">
          Kingdom of Vaelthorn
        </span>
        <span style="font-family:var(--font-display); font-size:20px; color:#c8a84b; letter-spacing:2px;">
          Travel Permit
        </span>
      </div>
      <div style="width:72px; height:72px; border:1px solid #c8a84b33; display:flex; align-items:center; justify-content:center; position:relative; flex-shrink:0;">
        <div style="position:absolute; inset:5px; border:0.5px solid #c8a84b1a;"></div>
        <svg width="32" height="32" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
          <line x1="4" y1="4" x2="18" y2="18" stroke="#8B2020" stroke-width="2" stroke-linecap="round"/>
          <line x1="18" y1="4" x2="4" y2="18" stroke="#8B2020" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
    </div>

    {{-- Fields --}}
    <div style="padding:32px 40px;">
      <div style="display:flex; justify-content:space-between; align-items:baseline; padding:12px 0; border-bottom:0.5px solid #c8a84b15;">
        <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; color:#4a4030;">Bearer</span>
        <span style="font-family:'Crimson Text',serif; font-size:19px; color:#8a7a5a;">
          {{ auth()->check() ? auth()->user()->character?->name ?? auth()->user()->name : 'Unknown Traveler' }}
        </span>
      </div>
      <div style="display:flex; justify-content:space-between; align-items:baseline; padding:12px 0; border-bottom:0.5px solid #c8a84b15;">
        <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; color:#4a4030;">Destination</span>
        <span style="font-family:'Crimson Text',serif; font-size:19px; color:#4a4030; font-style:italic;">
          {{ $destination }}
        </span>
      </div>
      <div style="display:flex; justify-content:space-between; align-items:baseline; padding:12px 0; border-bottom:0.5px solid #c8a84b15;">
        <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; color:#4a4030;">Status</span>
        <span style="font-family:'Crimson Text',serif; font-size:19px; color:#8B2020; font-style:italic;">ไม่ได้รับอนุญาต</span>
      </div>
      <div style="display:flex; justify-content:space-between; align-items:baseline; padding:12px 0;">
        <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; color:#4a4030;">Issued by</span>
        <span style="font-family:'Crimson Text',serif; font-size:19px; color:#3a3020; font-style:italic;">— ไม่มีตราประทับ —</span>
      </div>

      {{-- Divider + message --}}
      <div style="margin:26px 0; text-align:center;">
        <div style="display:flex; align-items:center; gap:8px; justify-content:center; margin-bottom:16px;">
          <div style="width:70px; height:0.5px; background:#c8a84b22;"></div>
          <div style="width:5px; height:5px; background:#c8a84b33; transform:rotate(45deg);"></div>
          <div style="width:70px; height:0.5px; background:#c8a84b22;"></div>
        </div>
        <p style="font-family:'Crimson Text',serif; font-size:17px; color:#3a3020; font-style:italic; line-height:2; margin:0;">
          เจ้าไม่มีใบอนุญาตผ่านแดนสำหรับพื้นที่นี้<br>
          <span style="color:#6b5f45; font-style:normal;">ติดต่อ Admin เพื่อขอรับสิทธิ์</span>
        </p>
      </div>
    </div>

    {{-- Footer --}}
    <div style="border-top:1px solid #c8a84b22; padding:20px 40px; display:flex; gap:12px; justify-content:center;">
      <a href="{{ url('/') }}"
         style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; padding:13px 28px; background:#c8a84b; color:#0d0a03; text-decoration:none; display:inline-block;">
        กลับหน้าหลัก
      </a>
      <a href="mailto:{{ config('mail.from.address', 'admin@vaelthorn.test') }}"
         style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; padding:13px 28px; background:transparent; border:0.5px solid #c8a84b33; color:#6b5f45; text-decoration:none; display:inline-block;">
        ติดต่อ Admin
      </a>
    </div>

  </div>
</div>
@endsection
