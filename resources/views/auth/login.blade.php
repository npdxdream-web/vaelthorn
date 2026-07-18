@extends('layouts.guest')

@section('title', 'เข้าสู่ระบบ — Vaelthorn')

@section('content')
<div class="w-full max-w-md">
    <div class="glow-gold rounded-xl border border-border bg-bg-elevated p-8">
        <div class="mb-8 text-center">
            <h1 class="font-display mb-2 text-3xl tracking-wide text-gold">Vaelthorn</h1>
            <p class="text-text-muted">ยินดีต้อนรับกลับมา</p>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-800 bg-red-950/50 px-4 py-3">
                @foreach($errors->all() as $error)
                    <p class="text-sm text-red-400">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-2 block text-sm text-gold">ชื่อผู้ใช้</label>
                <input type="text" name="name" value="{{ old('name') }}" class="input-field" required>
            </div>
            <div>
                <label class="mb-2 block text-sm text-gold">Password</label>
                <input type="password" name="password" class="input-field" required>
            </div>
            <button type="submit" class="btn-primary w-full py-3">เข้าสู่ระบบ</button>
        </form>

        <div class="mt-6 text-center text-sm text-text-muted">
            ยังไม่มีบัญชี? <a href="{{ route('register') }}" class="text-gold hover:text-gold-dark">สมัครสมาชิก</a>
        </div>
    </div>
</div>
@endsection
