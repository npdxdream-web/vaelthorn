@extends('layouts.app')

@section('title', 'แก้ไขกระทู้ — Vaelthorn')

@push('head')
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,300;0,400;0,700;1,400&family=Prompt:ital,wght@0,300;0,400;0,600;1,400&family=Kanit:ital,wght@0,300;0,400;0,600;1,400&family=Noto+Serif+Thai:wght@400;700&family=Mitr:wght@300;400;600&family=Charm:wght@400;700&family=Trirong:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        .ql-toolbar.ql-snow { background:#0b0b0b; border:1px solid #2a2a2a; border-radius:.5rem .5rem 0 0; }
        .ql-container.ql-snow { background:#090909; border:1px solid #2a2a2a; border-top:none; border-radius:0 0 .5rem .5rem; color:#e8e6e3; }
        .ql-toolbar.ql-snow button, .ql-toolbar.ql-snow .ql-picker-label { color:#e8e6e3; }
        .ql-toolbar.ql-snow button:hover,.ql-toolbar.ql-snow button.ql-active,
        .ql-toolbar.ql-snow .ql-picker-label:hover { color:#D4AF37 !important; }
        .ql-toolbar.ql-snow .ql-stroke { stroke:#e8e6e3; }
        .ql-toolbar.ql-snow button:hover .ql-stroke { stroke:#D4AF37; }
        .ql-toolbar.ql-snow .ql-fill { fill:#e8e6e3; }
        .ql-toolbar.ql-snow button:hover .ql-fill { fill:#D4AF37; }
        .ql-toolbar.ql-snow .ql-picker-options { background:#111; border:1px solid #2a2a2a; }
        .ql-toolbar.ql-snow .ql-picker-item { color:#e8e6e3; }
        .ql-toolbar.ql-snow .ql-picker-item:hover { color:#D4AF37; }
        .ql-editor { min-height:200px; color:#e8e6e3; }
        .ql-editor.ql-blank::before { color:#686664; }
        .ql-font-sarabun        { font-family:'Sarabun','Noto Sans Thai',sans-serif; }
        .ql-font-prompt         { font-family:'Prompt','Noto Sans Thai',sans-serif; }
        .ql-font-kanit          { font-family:'Kanit','Noto Sans Thai',sans-serif; }
        .ql-font-noto-serif-thai{ font-family:'Noto Serif Thai',Georgia,serif; }
        .ql-font-mitr           { font-family:'Mitr','Noto Sans Thai',sans-serif; }
        .ql-font-charm          { font-family:'Charm',cursive; }
        .ql-font-trirong        { font-family:'Trirong',Georgia,serif; }
        .ql-font-monospace      { font-family:SFMono-Regular,Consolas,monospace; }
        .custom-color-controls { display:inline-flex; align-items:center; gap:.5rem; }
        .custom-color-hex { width:110px; padding:.3rem .5rem; border:1px solid #2a2a2a; border-radius:.35rem; background:#111; color:#e8e6e3; font-size:.85rem; }
        .custom-color-hex:focus { outline:1px solid #D4AF37; }
        .custom-color-wheel { width:2.2rem; height:2.2rem; border:1px solid #2a2a2a; border-radius:.35rem; background:#111; cursor:pointer; }
    </style>
@endpush

@section('content')
<x-public.shell>
    <x-slot:left>
        <div class="sticky top-20">
            <div class="archive-panel p-5">
                <h3 class="font-display mb-4 text-base text-gold">{{ $thread->city->name ?? '—' }}</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex flex-col gap-0.5">
                        <span class="archive-label">City</span>
                        <span style="color:{{ $thread->city->kingdom->color ?? '#c8a84b' }}">{{ $thread->city->kingdom->name ?? '—' }}</span>
                    </div>
                    <div class="border-t border-gold/10 pt-3">
                        <a href="{{ route('thread', $thread->id) }}"
                           class="inline-flex items-center gap-1.5 text-xs text-text-muted hover:text-gold transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to thread
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:left>

    <div class="mb-4 flex items-center gap-2 text-sm text-text-muted">
        <a href="{{ route('thread', $thread->id) }}" class="hover:text-gold">← กลับไปกระทู้</a>
    </div>

    {{-- Admin request-edit notice --}}
    @if($thread->status === 'request_edit' && $thread->moderation_message)
        <div class="mb-6 rounded-xl border border-orange-400/30 bg-orange-950/10 p-5">
            <h3 class="font-display mb-1 text-base text-orange-400">Admin ขอให้แก้ไข</h3>
            <p class="text-sm text-text">{{ $thread->moderation_message }}</p>
        </div>
    @endif

    <div class="rounded-xl border border-border bg-bg-elevated p-6">
        <h1 class="font-display mb-6 text-2xl text-gold">แก้ไขกระทู้</h1>

        <form method="POST" action="{{ route('thread.update', $thread->id) }}" id="edit-thread-form">
            @csrf @method('PUT')

            {{-- Title --}}
            <div class="mb-4">
                <label for="title" class="mb-1 block text-sm text-text-muted">หัวข้อกระทู้</label>
                <input type="text" name="title" id="title" value="{{ old('title', $thread->title) }}" required
                       class="w-full rounded-lg border border-[#2a2a2a] bg-[#0a0a0a] px-4 py-2 text-[#e8e6e3] placeholder:text-[#686664] focus:border-[#D4AF37] focus:outline-none">
                @error('title')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Location label --}}
            <div class="mb-4">
                <label for="location_label" class="mb-1 block text-sm text-text-muted">สถานที่ในเรื่อง <span class="text-text-subtle">(ไม่บังคับ)</span></label>
                <input type="text" name="location_label" id="location_label"
                       value="{{ old('location_label', $thread->location_label) }}"
                       placeholder="เช่น ตลาด Akancia, ป่าใกล้ Viente"
                       style="background:#1a1408; border:0.5px solid #c8a84b33; color:#8a7a5a; font-family:'Crimson Text',serif; font-size:14px; padding:8px 12px; width:100%; outline:none;"
                       class="focus:border-gold/50">
            </div>

            {{-- Admin: move city --}}
            @if(auth()->user()->isAdminGroup())
            <div class="mb-4">
                <label for="city_id" class="mb-1 block text-sm text-text-muted">เมือง</label>
                <select name="city_id" id="city_id"
                        class="w-full rounded-lg border border-[#2a2a2a] bg-[#0a0a0a] px-4 py-2 text-[#e8e6e3]">
                    @foreach($cities as $v)
                        <option value="{{ $v->id }}" {{ $v->id == $thread->city_id ? 'selected' : '' }}>
                            {{ $v->kingdom?->name }} → {{ $v->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Submit buttons --}}
            <div class="flex items-center justify-between gap-3 mt-6">
                <a href="{{ route('thread', $thread->id) }}"
                   class="rounded-lg border border-[#2a2a2a] px-4 py-2 text-sm text-text-muted hover:border-[#D4AF37] hover:text-text">
                    ยกเลิก
                </a>
                <button type="submit"
                        class="rounded-lg bg-[#D4AF37] px-5 py-2 text-sm font-medium text-[#0f0f0f] hover:bg-[#B8941F]">
                    @if($thread->status === 'request_edit')
                        ส่งกลับเพื่ออนุมัติ
                    @else
                        บันทึก
                    @endif
                </button>
            </div>
        </form>
    </div>
</x-public.shell>
@endsection
