@extends('layouts.app')

@section('title', 'จดหมายถึงสภา — ' . $letter->subject)

@section('content')
<x-public.shell :character-status="$currentCharacter">

    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-2">Letters to the Council</p>
        <h1 class="font-decorative mb-1 text-2xl text-gold">{{ $letter->subject }}</h1>
        <p class="font-chronicle text-sm text-text-subtle">
            จาก {{ $letter->character?->name ?? 'ไม่ทราบชื่อ' }} — {{ $letter->created_at->format('d M Y, H:i') }}
        </p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-400/30 bg-emerald-950/20 px-4 py-3 text-sm text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="archive-panel p-6">
        <p class="archive-label mb-2">ข้อความ</p>
        <p class="font-chronicle whitespace-pre-line text-base leading-relaxed text-text">
            {{ $letter->body }}
        </p>
    </div>

    @if($letter->status === 'answered')
        <div class="archive-panel-soft mt-5 p-6" style="border-color: rgba(200,168,75,.3)">
            <p class="archive-label mb-2" style="color:#c8a84b">คำตอบจากสภา</p>
            <p class="font-chronicle whitespace-pre-line text-base leading-relaxed text-text">
                {{ $letter->admin_reply }}
            </p>
            <p class="mt-3 text-xs text-text-subtle">
                ตอบโดย {{ $letter->repliedBy?->name ?? 'สภา' }} — {{ $letter->replied_at?->format('d M Y, H:i') }}
            </p>
        </div>
    @elseif(auth()->user()->isAtLeastModerator())
        <div class="archive-panel-soft mt-5 p-6">
            <p class="archive-label mb-3">ตอบจดหมายฉบับนี้</p>
            <form method="POST" action="{{ route('council.reply', $letter->id) }}" class="space-y-3">
                @csrf
                <textarea name="admin_reply" rows="4" required maxlength="2000"
                          class="input-field text-sm" placeholder="พิมพ์คำตอบ..."></textarea>
                <button type="submit" class="btn-primary text-sm">ส่งคำตอบ</button>
            </form>
        </div>
    @else
        <div class="archive-panel-soft mt-5 p-6 text-center">
            <p class="text-sm text-text-subtle">สภายังไม่ได้ตอบจดหมายฉบับนี้</p>
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('home') }}" class="font-display text-xs uppercase tracking-wider text-gold/60 transition hover:text-gold">
            ← กลับหน้าแรก
        </a>
    </div>

</x-public.shell>
@endsection
