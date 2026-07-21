@extends('layouts.app')

@section('title', 'แก้ไขตัวละคร — ' . $character->name)

@section('content')
<x-public.shell :character-status="$currentCharacter">

    <x-slot:left>
        <div class="sticky top-20 space-y-4">
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Profile</p>
                <div class="space-y-2 text-sm text-text-muted">
                    <div>Kingdom: <span class="font-display text-gold">{{ $character->kingdom->name ?? '—' }}</span></div>
                    <div>Status: <span class="font-display {{ $character->status === 'approved' ? 'text-emerald-400' : 'text-amber-400' }}">{{ ucfirst($character->status) }}</span></div>
                    <div>Rank: <span class="font-display text-gold">{{ $character->auto_rank }}</span></div>
                </div>
            </div>
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Tips</p>
                <ul class="space-y-2 text-xs text-text-subtle">
                    <li>• ชื่อตัวละครจะแสดงในทุกที่</li>
                    <li>• Avatar ใส่ URL รูปภาพ (HTTPS)</li>
                    <li>• Backstory แสดงในหน้าโปรไฟล์</li>
                </ul>
            </div>
        </div>
    </x-slot:left>

    {{-- Main --}}
    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-1">ตัวละคร</p>
        <h1 class="font-decorative mb-2 text-3xl text-gold">แก้ไขโปรไฟล์</h1>
        <p class="font-chronicle text-lg text-text-muted">{{ $character->name }}</p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-400/30 bg-emerald-950/20 px-4 py-3 text-sm text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('character.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Avatar preview --}}
        <div class="archive-panel p-6">
            <p class="archive-label mb-4">Avatar</p>
            <div class="flex items-start gap-5">
                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-full border-2 border-gold/30 bg-bg-elevated">
                    @if($character->avatar_url)
                        <img src="{{ $character->avatar_url }}" alt="avatar" class="h-full w-full object-cover" id="avatar-preview">
                    @else
                        <div class="flex h-full w-full items-center justify-center font-display text-2xl text-gold" id="avatar-preview-placeholder">
                            {{ mb_substr($character->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <label class="archive-label mb-2 block">URL รูปภาพ</label>
                    <input type="url" name="avatar" id="avatar-input"
                           value="{{ old('avatar', $character->avatar) }}"
                           placeholder="https://example.com/avatar.jpg"
                           class="w-full rounded border border-gold/20 bg-bg-elevated px-3 py-2 text-sm text-text focus:border-gold/50 focus:outline-none">
                    @error('avatar')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-text-subtle">ใส่ URL รูปภาพ HTTPS เท่านั้น</p>
                </div>
            </div>
        </div>

        {{-- Name --}}
        <div class="archive-panel p-6">
            <p class="archive-label mb-4">ข้อมูลพื้นฐาน</p>
            <div>
                <label class="archive-label mb-2 block">ชื่อตัวละคร <span class="text-red-400">*</span></label>
                <input type="text" name="name" required maxlength="100"
                       value="{{ old('name', $character->name) }}"
                       class="w-full rounded border border-gold/20 bg-bg-elevated px-3 py-2 font-display text-sm text-gold focus:border-gold/50 focus:outline-none">
                @error('name')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Backstory --}}
        <div class="archive-panel p-6">
            <label class="archive-label mb-4 block">Backstory</label>
            <textarea name="backstory" rows="10" maxlength="5000"
                      placeholder="เล่าประวัติของตัวละคร — มาจากไหน ผ่านอะไรมา มีจุดหมายอะไร..."
                      class="w-full rounded border border-gold/20 bg-bg-elevated px-3 py-2 font-chronicle text-base leading-relaxed text-text focus:border-gold/50 focus:outline-none">{{ old('backstory', $character->backstory) }}</textarea>
            <p class="mt-1 text-right text-xs text-text-subtle">
                <span id="backstory-count">{{ mb_strlen($character->backstory ?? '') }}</span> / 5000
            </p>
            @error('backstory')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('character.show', $character->id) }}"
               class="font-display text-xs uppercase tracking-wider text-text-subtle transition hover:text-text">
                ← ยกเลิก
            </a>
            <button type="submit"
                    class="rounded border border-gold/40 bg-gold/10 px-6 py-2.5 font-display text-sm uppercase tracking-wider text-gold transition hover:bg-gold/20">
                บันทึกการเปลี่ยนแปลง
            </button>
        </div>
    </form>

    {{-- Stat Point Allocation — only shown when unspent points are available --}}
    @php $stats = $character->stats; @endphp
    @if($stats && $stats->stat_points_available > 0)
    <div class="archive-panel mt-6 p-6">
        <p class="archive-label mb-1">Attributes</p>
        <h2 class="font-decorative mb-1 text-xl text-gold">จัดสรร Stat Points</h2>
        <p class="mb-5 text-sm text-text-muted">
            แต้มคงเหลือ: <span class="font-display text-gold text-lg">{{ $stats->stat_points_available }}</span> แต้ม
        </p>

        @if(session('success'))
            <div class="mb-4 rounded border border-emerald-400/30 bg-emerald-950/20 px-4 py-2 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded border border-rose-400/30 bg-rose-950/20 px-4 py-2 text-sm text-rose-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                'str'  => ['STR — กำลัง', $stats->str,  '#c8a84b'],
                'agi'  => ['AGI — ความเร็ว', $stats->agi,  '#7ab0d4'],
                'int'  => ['INT — เวทย์มนตร์', $stats->int,  '#9b8fc8'],
                'hp'   => ['HP — พลังชีวิต', $stats->hp,   '#c05050'],
                'mana' => ['MP — มานา', $stats->mana, '#7060b8'],
            ] as $key => [$label, $current, $color])
            <div class="flex items-center justify-between rounded border border-gold/15 bg-bg-elevated px-4 py-3">
                <div>
                    <span class="archive-label text-xs">{{ $label }}</span>
                    <div class="font-display text-xl" style="color:{{ $color }}">{{ $current }}</div>
                </div>
                <form method="POST" action="{{ route('character.stat.allocate') }}">
                    @csrf
                    <input type="hidden" name="stat" value="{{ $key }}">
                    <input type="hidden" name="points" value="1">
                    <button type="submit"
                            class="rounded border border-gold/40 bg-gold/10 px-3 py-1.5 font-display text-xs uppercase tracking-wider text-gold transition hover:bg-gold/20">
                        + 1
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</x-public.shell>

<script>
    // Live avatar preview
    document.getElementById('avatar-input')?.addEventListener('input', function () {
        const url = this.value.trim();
        const preview = document.getElementById('avatar-preview');
        const placeholder = document.getElementById('avatar-preview-placeholder');
        if (url && preview) {
            preview.src = url;
        } else if (!url && placeholder) {
            placeholder.style.display = 'flex';
        }
    });

    // Backstory character count
    const backstory = document.querySelector('textarea[name="backstory"]');
    const counter   = document.getElementById('backstory-count');
    if (backstory && counter) {
        backstory.addEventListener('input', () => {
            counter.textContent = backstory.value.length;
        });
    }
</script>
@endsection
