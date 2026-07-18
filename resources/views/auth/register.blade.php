@extends('layouts.guest')

@section('title', 'สมัครสมาชิก — Vaelthorn')

@section('content')
<div class="w-full max-w-2xl">
    @if($errors->any())
        <div class="mb-6 rounded-lg border border-red-800 bg-red-950/50 px-4 py-3">
            @foreach($errors->all() as $error)
                <p class="text-sm text-red-400">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="mb-8 text-center">
        <h1 class="font-display mb-3 text-4xl tracking-wide text-gold">Create Your Character</h1>
        <p class="text-lg text-text-muted">Tell your story in the world of Vaelthorn.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="glow-gold rounded-xl border border-border bg-bg-elevated p-8">
        @csrf

        <div class="space-y-6">
            <p class="text-sm font-medium text-gold">ข้อมูลบัญชี</p>

            <div>
                <label class="mb-2 block text-sm text-gold">ชื่อผู้ใช้</label>
                <input type="text" name="name" value="{{ old('name') }}" class="input-field" required>
            </div>
            <div>
                <label class="mb-2 block text-sm text-gold">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="input-field" required>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm text-gold">Password</label>
                    <input type="password" name="password" class="input-field" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm text-gold">ยืนยัน Password</label>
                    <input type="password" name="password_confirmation" class="input-field" required>
                </div>
            </div>

            <div class="border-t border-border pt-6">
                <p class="mb-4 text-sm font-medium text-gold">ตัวละครของคุณ</p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm text-gold">ชื่อ</label>
                        <input type="text" name="char_firstname" value="{{ old('char_firstname') }}"
                               class="input-field" placeholder="First name" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm text-gold">นามสกุล</label>
                        <input type="text" name="char_lastname" value="{{ old('char_lastname') }}"
                               class="input-field" placeholder="Last name" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full py-3 text-base">Create Character</button>
        </div>
    </form>

    <div class="mt-8 text-center text-sm text-text-muted">
        มีบัญชีแล้ว? <a href="{{ route('login') }}" class="text-gold hover:text-gold-dark">เข้าสู่ระบบ</a>
    </div>
</div>
@endsection
