@extends('layouts.app')

@section('title', 'สร้างกระทู้ใหม่ — Vaelthorn')

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
        .ql-toolbar.ql-snow button:hover .ql-stroke,.ql-toolbar.ql-snow button.ql-active .ql-stroke { stroke:#D4AF37; }
        .ql-toolbar.ql-snow .ql-fill { fill:#e8e6e3; }
        .ql-toolbar.ql-snow button:hover .ql-fill { fill:#D4AF37; }
        .ql-toolbar.ql-snow .ql-picker-options { background:#111; border:1px solid #2a2a2a; }
        .ql-toolbar.ql-snow .ql-picker-item { color:#e8e6e3; }
        .ql-toolbar.ql-snow .ql-picker-item:hover,.ql-toolbar.ql-snow .ql-picker-item.ql-selected { color:#D4AF37; }
        .ql-editor { min-height:260px; color:#e8e6e3; }
        .ql-editor.ql-blank::before { color:#686664; }
        .ql-font-sarabun        { font-family:'Sarabun','Noto Sans Thai',sans-serif; }
        .ql-font-prompt         { font-family:'Prompt','Noto Sans Thai',sans-serif; }
        .ql-font-kanit          { font-family:'Kanit','Noto Sans Thai',sans-serif; }
        .ql-font-noto-serif-thai{ font-family:'Noto Serif Thai',Georgia,serif; }
        .ql-font-mitr           { font-family:'Mitr','Noto Sans Thai',sans-serif; }
        .ql-font-charm          { font-family:'Charm',cursive; }
        .ql-font-trirong        { font-family:'Trirong',Georgia,serif; }
        .ql-font-monospace      { font-family:SFMono-Regular,Consolas,monospace; }
        .ql-picker.ql-font .ql-picker-item[data-value="sarabun"]         { font-family:'Sarabun',sans-serif; }
        .ql-picker.ql-font .ql-picker-item[data-value="prompt"]          { font-family:'Prompt',sans-serif; }
        .ql-picker.ql-font .ql-picker-item[data-value="kanit"]           { font-family:'Kanit',sans-serif; }
        .ql-picker.ql-font .ql-picker-item[data-value="noto-serif-thai"] { font-family:'Noto Serif Thai',serif; }
        .ql-picker.ql-font .ql-picker-item[data-value="mitr"]            { font-family:'Mitr',sans-serif; }
        .ql-picker.ql-font .ql-picker-item[data-value="charm"]           { font-family:'Charm',cursive; }
        .ql-picker.ql-font .ql-picker-item[data-value="trirong"]         { font-family:'Trirong',serif; }
        .ql-picker.ql-font .ql-picker-item[data-value="monospace"]       { font-family:monospace; }
        /* ── Color picker ───────────────────────────────────────── */
        .color-picker-wrap { display:inline-flex; align-items:center; gap:.4rem; vertical-align:middle; }
        .color-hue-slider { -webkit-appearance:none; appearance:none; width:100px; height:10px; border-radius:5px; cursor:pointer; border:1px solid #333; background:linear-gradient(to right,#ff0000,#ff8000,#ffff00,#80ff00,#00ff00,#00ff80,#00ffff,#0080ff,#0000ff,#8000ff,#ff00ff,#ff0080,#ff0000); }
        .color-hue-slider::-webkit-slider-thumb { -webkit-appearance:none; width:14px; height:14px; border-radius:50%; background:#fff; border:2px solid #555; cursor:pointer; }
        .color-hue-slider::-moz-range-thumb { width:14px; height:14px; border-radius:50%; background:#fff; border:2px solid #555; cursor:pointer; }
        .color-hex-input { width:90px; padding:.25rem .4rem; border:1px solid #2a2a2a; border-radius:.35rem; background:#111; color:#e8e6e3; font-size:.8rem; }
        .color-hex-input:focus { outline:1px solid #D4AF37; }
        .color-preview-box { width:22px; height:22px; border-radius:4px; border:1px solid #2a2a2a; flex-shrink:0; }
    </style>
@endpush

@section('content')
<x-public.shell>
    <x-slot:left>
        <div class="sticky top-20">
            <div class="archive-panel p-5">
                <h3 class="font-display mb-4 text-base text-gold">{{ $village->name }}</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex flex-col gap-0.5">
                        <span class="archive-label">City</span>
                        <span style="color:{{ $village->city->color ?? '#c8a84b' }}">{{ $village->city->name ?? '—' }}</span>
                    </div>
                    <div class="border-t border-gold/10 pt-3">
                        <a href="{{ route('village', $village->id) }}"
                           class="inline-flex items-center gap-1.5 text-xs text-text-muted hover:text-gold transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to {{ $village->name }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:left>

    {{-- Breadcrumb --}}
    <div class="mb-4 flex items-center gap-2 text-sm text-text-muted">
        <a href="{{ route('home') }}" class="hover:text-gold">Thiran</a>
        <span>/</span>
        <a href="{{ route('village', $village->id) }}" class="hover:text-gold">{{ $village->name }}</a>
        <span>/</span>
        <span class="text-text">สร้างกระทู้ใหม่</span>
    </div>

    <div class="rounded-xl border border-border bg-bg-elevated p-6">
        <h1 class="font-display mb-1 text-2xl text-gold">Start a New Tale</h1>
        <p class="mb-6 text-sm text-text-muted">ใน {{ $village->name }} — กระทู้จะรอ Admin อนุมัติก่อนเผยแพร่</p>

        <form method="POST" action="{{ route('thread.store', $village->id) }}" id="create-thread-form">
            @csrf
            <input type="hidden" name="action" id="form-action" value="submit">
            <input type="hidden" name="content" id="create-content-input">

            {{-- Title --}}
            <div class="mb-4">
                <label for="title" class="mb-1 block text-sm text-text-muted">หัวข้อกระทู้</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                       placeholder="ชื่อเรื่อง / ฉากที่…"
                       class="w-full rounded-lg border border-[#2a2a2a] bg-[#0a0a0a] px-4 py-2 text-[#e8e6e3] placeholder:text-[#686664] focus:border-[#D4AF37] focus:outline-none">
                @error('title')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Quill editor for first post --}}
            <div class="mb-6">
                <label class="mb-1 block text-sm text-text-muted">เนื้อหาโพสต์แรก</label>

                <div id="create-editor-toolbar" class="ql-toolbar ql-snow p-2">
                    <span class="ql-formats">
                        <select class="ql-font" title="เลือกฟอนต์">
                            <option selected value="">ค่าเริ่มต้น</option>
                            <option value="sarabun">Sarabun (ไทย)</option>
                            <option value="prompt">Prompt (ไทย)</option>
                            <option value="kanit">Kanit (ไทย)</option>
                            <option value="noto-serif-thai">Noto Serif Thai</option>
                            <option value="mitr">Mitr (ไทย)</option>
                            <option value="charm">Charm (ไทย)</option>
                            <option value="trirong">Trirong (ไทย)</option>
                            <option value="monospace">Monospace</option>
                        </select>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-bold" title="ตัวหนา"></button>
                        <button class="ql-italic" title="ตัวเอียง"></button>
                        <button class="ql-underline" title="ขีดเส้นใต้"></button>
                        <button class="ql-strike" title="ขีดทับ"></button>
                    </span>
                    <span class="ql-formats">
                        <select class="ql-header" title="ขนาดหัวข้อ">
                            <option value="1">หัวข้อ 1</option>
                            <option value="2">หัวข้อ 2</option>
                            <option value="3">หัวข้อ 3</option>
                            <option selected value="">ปกติ</option>
                        </select>
                        <button class="ql-blockquote" title="บล็อกคำพูด"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-list" value="ordered" title="รายการตัวเลข"></button>
                        <button class="ql-list" value="bullet" title="รายการจุด"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-align" value="" title="ชิดซ้าย"></button>
                        <button class="ql-align" value="center" title="จัดกึ่งกลาง"></button>
                        <button class="ql-align" value="right" title="ชิดขวา"></button>
                        <button class="ql-align" value="justify" title="จัดเต็มแนว"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-link" title="แทรกลิงก์"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-clean" title="ล้างการจัดรูปแบบ"></button>
                    </span>
                    <span class="ql-formats">
                        <div class="color-picker-wrap">
                            <input type="range" min="0" max="360" value="43" class="color-hue-slider" id="create-slider" title="เลื่อนเพื่อเลือกสีข้อความ">
                            <input type="text" class="color-hex-input" id="create-hex" placeholder="#D4AF37" maxlength="7" autocomplete="off">
                            <div class="color-preview-box" id="create-preview" style="background:#D4AF37"></div>
                        </div>
                    </span>
                </div>
                <div id="create-editor" class="min-h-[260px] p-4 text-[#e8e6e3]"></div>

                @error('content')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('village', $village->id) }}"
                   class="rounded-lg border border-[#2a2a2a] px-4 py-2 text-sm text-text-muted hover:border-[#D4AF37] hover:text-text">
                    ยกเลิก
                </a>

                <div class="flex gap-2">
                    <button type="button" id="btn-draft"
                            class="rounded-lg border border-slate-500/40 bg-slate-900/30 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800/50">
                        💾 บันทึกร่าง
                    </button>
                    <button type="submit" id="btn-submit"
                            class="rounded-lg bg-[#D4AF37] px-5 py-2 text-sm font-medium text-[#0f0f0f] hover:bg-[#B8941F]">
                        ส่งเพื่ออนุมัติ
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-public.shell>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
function initColorPicker(sliderId, hexId, previewId, quill) {
    const slider  = document.getElementById(sliderId);
    const hexEl   = document.getElementById(hexId);
    const preview = document.getElementById(previewId);

    function hslToHex(h) {
        const s = 1, l = 0.5, a = s * Math.min(l, 1 - l);
        const f = n => {
            const k = (n + h / 30) % 12;
            const c = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
            return Math.round(255 * c).toString(16).padStart(2, '0');
        };
        return '#' + f(0) + f(8) + f(4);
    }

    function hexToHue(hex) {
        const r = parseInt(hex.slice(1,3), 16) / 255;
        const g = parseInt(hex.slice(3,5), 16) / 255;
        const b = parseInt(hex.slice(5,7), 16) / 255;
        const max = Math.max(r,g,b), min = Math.min(r,g,b), d = max - min;
        if (d === 0) return 0;
        let h = max === r ? ((g - b) / d + (g < b ? 6 : 0))
              : max === g ? ((b - r) / d + 2)
              :              ((r - g) / d + 4);
        return Math.round(h * 60);
    }

    function applyColor(hexVal) {
        if (preview) preview.style.background = hexVal;
        quill.format('color', hexVal);
    }

    slider.addEventListener('input', () => {
        const hexVal = hslToHex(parseInt(slider.value));
        hexEl.value = hexVal.toUpperCase();
        applyColor(hexVal);
    });

    hexEl.addEventListener('input', () => {
        const val = hexEl.value.trim();
        if (/^#([0-9A-Fa-f]{6})$/.test(val)) {
            slider.value = hexToHue(val);
            applyColor(val);
        }
    });

    hexEl.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); hexEl.dispatchEvent(new Event('input')); }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const Font = Quill.import('formats/font');
    Font.whitelist = ['sarabun','prompt','kanit','noto-serif-thai','mitr','charm','trirong','monospace'];
    Quill.register(Font, true);

    const quill = new Quill('#create-editor', {
        modules: { toolbar: '#create-editor-toolbar' },
        theme: 'snow',
        placeholder: 'เขียนโพสต์แรกของกระทู้…',
    });

    const form         = document.getElementById('create-thread-form');
    const actionInput  = document.getElementById('form-action');
    const contentInput = document.getElementById('create-content-input');

    initColorPicker('create-slider', 'create-hex', 'create-preview', quill);

    document.getElementById('btn-draft').addEventListener('click', function () {
        actionInput.value  = 'draft';
        contentInput.value = quill.root.innerHTML;
        form.submit();
    });

    form.addEventListener('submit', function () {
        actionInput.value  = 'submit';
        contentInput.value = quill.root.innerHTML;
    });
});
</script>
@endpush
