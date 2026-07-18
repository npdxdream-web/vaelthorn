@extends('layouts.guest')

@section('title', 'เลือก Kingdom — Vaelthorn')

@section('content')
<div class="w-full max-w-5xl">

    @if($errors->any())
        <div class="mb-6 rounded-lg border border-red-800 bg-red-950/50 px-4 py-3">
            @foreach($errors->all() as $error)
                <p class="text-sm text-red-400">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="mb-8 text-center">
        <h1 class="font-display mb-3 text-4xl tracking-wide text-gold">เลือก Kingdom ของคุณ</h1>
        <p class="text-lg text-text-muted">{{ $character->name }} — Kingdom นี้จะเป็นบ้านของคุณตลอดไป</p>
        <p class="mt-2 text-sm text-copper">⚠ การเลือกนี้ถาวร — ไม่สามารถเปลี่ยนได้ภายหลัง</p>
    </div>

    <form method="POST" action="{{ route('choose-city.store') }}" id="city-form">
        @csrf

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($cities as $city)
                <label data-city-card
                       class="group relative cursor-pointer overflow-hidden rounded-xl border-2 border-border bg-bg-elevated text-left transition-all hover:border-copper">
                    <input type="radio" name="city_id" value="{{ $city->id }}" class="sr-only" {{ old('city_id') == $city->id ? 'checked' : '' }}>

                    {{-- Color gradient header --}}
                    <div class="relative h-32 overflow-hidden">
                        <div class="h-full w-full transition-transform duration-500 group-hover:scale-105"
                             style="background: linear-gradient(135deg, {{ $city->color }}30, {{ $city->color }}08);"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-bg-elevated via-bg-elevated/60 to-transparent"></div>

                        <div class="absolute left-5 top-5 flex h-14 w-14 items-center justify-center rounded-full border-2 bg-bg-elevated/90 text-2xl backdrop-blur-sm"
                             style="border-color: {{ $city->color }}">
                            {{ $city->icon }}
                        </div>

                        <div data-city-check class="absolute right-5 top-5 hidden flex h-9 w-9 items-center justify-center rounded-full bg-gold">
                            <svg class="h-5 w-5 text-bg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>

                    <div class="p-5">
                        <h2 class="font-display mb-1 text-xl" style="color: {{ $city->color }}">{{ $city->name }}</h2>
                        <p class="mb-3 text-xs text-text-muted">{{ $city->description }}</p>

                        <div class="space-y-1 border-t border-border pt-3">
                            <div class="text-[10px] text-text-subtle">VILLAGES:</div>
                            @foreach($city->villages as $village)
                                <div class="text-xs text-text">• {{ $village->name }}</div>
                            @endforeach
                        </div>
                    </div>
                </label>
            @endforeach
        </div>

        @error('city_id')
            <p class="mt-4 text-center text-sm text-red-400">{{ $message }}</p>
        @enderror

        <div class="mt-8 flex justify-center">
            <button type="button" id="city-confirm-btn"
                    class="btn-primary px-10 py-3 text-base opacity-50"
                    disabled
                    onclick="document.getElementById('city-form').submit()">
                ยืนยันเลือก Kingdom นี้
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-text-subtle hover:text-text">ออกจากระบบ</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('[data-city-card]').forEach(card => {
    card.addEventListener('click', () => {
        // Reset all cards
        document.querySelectorAll('[data-city-card]').forEach(c => {
            c.classList.remove('border-gold', 'shadow-[0_0_30px_rgba(212,175,55,0.3)]');
            c.classList.add('border-border');
            c.querySelector('[data-city-check]').classList.add('hidden');
        });
        // Highlight selected
        card.classList.remove('border-border');
        card.classList.add('border-gold', 'shadow-[0_0_30px_rgba(212,175,55,0.3)]');
        card.querySelector('[data-city-check]').classList.remove('hidden');
        // Enable button
        const btn = document.getElementById('city-confirm-btn');
        btn.disabled = false;
        btn.classList.remove('opacity-50');
    });
});
</script>
@endsection
