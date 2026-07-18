@extends('layouts.guest')

@section('title', 'รอการอนุมัติ — Vaelthorn')

@section('content')
<div class="w-full max-w-md">
    <div class="glow-gold rounded-xl border border-border bg-bg-elevated p-8 text-center">
        <div class="mb-4 text-4xl">⏳</div>
        <h1 class="font-display mb-4 text-2xl text-gold">รอการอนุมัติ</h1>
        <p class="mb-6 text-text-muted">ตัวละครของคุณถูกส่งให้ Admin ตรวจสอบแล้ว</p>

        <div class="mb-6 rounded-lg border border-gold/40 bg-gold/10 px-4 py-3 text-gold">
            สถานะ: <strong>Pending</strong>
        </div>

        <p class="mb-8 text-sm leading-relaxed text-text-muted">
            Admin จะตรวจสอบ Backstory และอนุมัติให้คุณเข้าสู่โลก Thiran โดยเร็วที่สุด
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-outline w-full">ออกจากระบบ</button>
        </form>
    </div>
</div>
@endsection
