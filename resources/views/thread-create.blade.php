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

        /* ── Editor + preview must both match thread.blade.php's actual
               render size exactly — otherwise what the author sees while
               editing (or in the preview toggle) is a different size than
               what actually shows on the thread. .ql-editor inherits from
               its .thread-reading/.lore-post-body ancestor since Quill's
               own CSS doesn't set a conflicting font-size to override. ── */
        .thread-reading {
            font-size: 18px !important;
            line-height: 1.9 !important;
        }
        .thread-reading .ql-editor {
            font-size: inherit !important;
            line-height: inherit !important;
            font-family: inherit !important;
            color: inherit !important;
        }
        .lore-post-body.thread-reading {
            font-family: var(--font-chronicle);
            font-size: 18px !important;
            line-height: 2 !important;
            color: #d8d1bd;
        }
        .lore-post-body p {
            margin-bottom: 1.4em;
        }
        .thread-reading .ql-align-center  { text-align:center; }
        .thread-reading .ql-align-right   { text-align:right; }
        .thread-reading .ql-align-justify { text-align:justify; }
        .thread-reading .ql-indent-1 { padding-left:3em; }
        .thread-reading .ql-indent-2 { padding-left:6em; }
        .thread-reading .ql-indent-3 { padding-left:9em; }
        .thread-reading .ql-indent-4 { padding-left:12em; }
        .thread-reading .ql-indent-5 { padding-left:15em; }
        .thread-reading .ql-indent-6 { padding-left:18em; }
        .thread-reading .ql-indent-7 { padding-left:21em; }
        .thread-reading .ql-indent-8 { padding-left:24em; }
        .thread-reading .ql-rindent-1 { padding-right:3em; }
        .thread-reading .ql-rindent-2 { padding-right:6em; }
        .thread-reading .ql-rindent-3 { padding-right:9em; }
        .thread-reading .ql-rindent-4 { padding-right:12em; }
        .thread-reading .ql-rindent-5 { padding-right:15em; }
        .thread-reading .ql-rindent-6 { padding-right:18em; }
        .thread-reading .ql-rindent-7 { padding-right:21em; }
        .thread-reading .ql-rindent-8 { padding-right:24em; }
        .thread-reading h1 { font-size:2em; }
        .thread-reading h2 { font-size:1.5em; }
        .thread-reading h3 { font-size:1.17em; }
        .thread-reading h4 { font-size:1em; }
        .thread-reading h5 { font-size:.83em; }
        .thread-reading h6 { font-size:.67em; }

        /* ── Drop cap: user-applied via the "Drop Cap" toolbar button ───── */
        /* Fixed to exactly 2 body-text line-boxes tall (18px font-size × line-height
           2 = 36px per line, ×2 = 72px) rather than a bare font-size — Thai vowel/tone
           marks (e.g. the ่ in "ที่") add height above/below the base glyph without
           changing its declared font-size, so pinning only font-size let some letters
           render taller than others and look unaligned ("floating"). Locking line-height
           to the 2-line box and keeping font-size well under it gives every glyph the
           same headroom for combining marks, so the wrap is always symmetric. */
        .drop-cap {
            float: left;
            font-family: var(--font-decorative);
            font-size: 58px;
            line-height: 72px;
            color: #d4af37;
            font-weight: 700;
            padding: 0 8px 0 0;
        }

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
                <h3 class="font-display mb-4 text-base text-gold">{{ $city->name }}</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex flex-col gap-0.5">
                        <span class="archive-label">City</span>
                        <span style="color:{{ $city->kingdom->color ?? '#c8a84b' }}">{{ $city->kingdom->name ?? '—' }}</span>
                    </div>
                    <div class="border-t border-gold/10 pt-3">
                        <a href="{{ route('city', $city->id) }}"
                           class="inline-flex items-center gap-1.5 text-xs text-text-muted hover:text-gold transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to {{ $city->name }}
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
        <a href="{{ route('city', $city->id) }}" class="hover:text-gold">{{ $city->name }}</a>
        <span>/</span>
        <span class="text-text">สร้างกระทู้ใหม่</span>
    </div>

    <div class="rounded-xl border border-border bg-bg-elevated p-6">
        <h1 class="font-display mb-1 text-2xl text-gold">Start a New Tale</h1>
        <p class="mb-6 text-sm text-text-muted">ใน {{ $city->name }} — กระทู้จะรอ Admin อนุมัติก่อนเผยแพร่</p>

        <form method="POST" action="{{ route('thread.store', $city->id) }}" id="create-thread-form" enctype="multipart/form-data">
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

            {{-- Banner image (optional) --}}
            <div class="mb-4">
                <label for="banner_image" class="mb-1 block text-sm text-text-muted">ลิงก์ภาพแบนเนอร์กระทู้ (ไม่บังคับ)</label>
                <input type="url" name="banner_image" id="banner_image" value="{{ old('banner_image') }}"
                       placeholder="https://i.ibb.co/xxxxxxx/banner.jpg"
                       class="w-full rounded-lg border border-[#2a2a2a] bg-[#0a0a0a] px-4 py-2 text-sm text-[#e8e6e3] focus:border-[#D4AF37] focus:outline-none">
                <p class="mt-1 text-xs text-[#686664]">วางลิงก์รูปภาพที่อัปโหลดไว้ที่เว็บฝากรูป (เช่น imgur.com, ibb.co) — จะแสดงเป็นพื้นหลังด้านบนหัวกระทู้</p>
                @error('banner_image')
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
                        <select class="ql-size" title="ขนาดตัวอักษร">
                            <option value="12px">12px</option>
                            <option value="14px">14px</option>
                            <option value="16px">16px</option>
                            <option selected value="">18px (ปกติ)</option>
                            <option value="20px">20px</option>
                            <option value="24px">24px</option>
                            <option value="28px">28px</option>
                            <option value="32px">32px</option>
                            <option value="40px">40px</option>
                            <option value="48px">48px</option>
                        </select>
                        <button class="ql-blockquote" title="บล็อกคำพูด"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-list" value="ordered" title="รายการตัวเลข"></button>
                        <button class="ql-list" value="bullet" title="รายการจุด"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-indent" value="-1" title="ลดย่อหน้า"></button>
                        <button class="ql-indent" value="+1" title="เพิ่มย่อหน้า (Tab)"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-indentright" value="-1" title="ลดย่อหน้าทางขวา"><strong>⇤</strong></button>
                        <button class="ql-indentright" value="+1" title="เพิ่มย่อหน้าทางขวา"><strong>⇥</strong></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-dropcap" type="button" title="Drop Cap — ตัวอักษรแรกของย่อหน้าตัวใหญ่"><strong>A</strong></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-align" value="" title="ชิดซ้าย"></button>
                        <button class="ql-align" value="center" title="จัดกึ่งกลาง"></button>
                        <button class="ql-align" value="right" title="ชิดขวา"></button>
                        <button class="ql-align" value="justify" title="จัดเต็มแนว"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-link" title="แทรกลิงก์"></button>
                        <button class="ql-image" title="แทรกรูปภาพ"></button>
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
                <div id="create-editor" class="thread-reading {{ $isLorePost ? 'lore-post-body' : '' }} min-h-[260px] p-4 text-[#e8e6e3]"></div>
                <div id="create-content-preview" class="thread-reading {{ $isLorePost ? 'lore-post-body' : '' }} prose prose-invert max-w-none hidden p-4"></div>

                @error('content')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('city', $city->id) }}"
                   class="rounded-lg border border-[#2a2a2a] px-4 py-2 text-sm text-text-muted hover:border-[#D4AF37] hover:text-text">
                    ยกเลิก
                </a>

                <div class="flex gap-2">
                    <button type="button" id="create-preview-toggle"
                            class="rounded-lg border border-gold/30 px-4 py-2 text-sm text-gold/80 hover:border-gold hover:text-gold">
                        👁 ดูตัวอย่าง
                    </button>
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

// Quill's block-level 'indent' format shifts every wrapped line of the
// paragraph, not just the first — wrong for a Thai-style first-line
// paragraph indent (ย่อหน้า). So Tab here inserts literal non-breaking
// spaces at the cursor instead of applying a block format: it only ever
// affects the first line, and needs no matching display CSS since it's
// plain text content.
// NBSP is built via fromCharCode (not a literal character in source) so
// this file stays plain ASCII and never risks a mangled encoding.
// Text-before-cursor is read via quill.getText(lineStart, offset) rather
// than context.prefix: Quill's prefix is sliced from the leaf at the
// cursor's index, and at the very start of a line that index is
// ambiguous with the end of the *previous* line's leaf — so prefix can
// come back as the previous paragraph's trailing text instead of "".
// Bails (returns true) when the cursor is inside a list/blockquote/
// code-block, since Tab there should keep doing Quill's own format
// indent (nesting), not insert NBSP text.
function quillIndentAnywhereBindings() {
    var NBSP_CODE = 160;
    var INDENT_UNIT = String.fromCharCode(NBSP_CODE, NBSP_CODE, NBSP_CODE, NBSP_CODE);
    function isNbspOnly(str) {
        for (var i = 0; i < str.length; i++) {
            if (str.charCodeAt(i) !== NBSP_CODE) return false;
        }
        return true;
    }
    function isStructuredFormat(format) {
        return !!(format && (format.list || format.blockquote || format.indent || format['code-block']));
    }
    return {
        'indent-anywhere': {
            key: 9,
            shiftKey: false,
            collapsed: true,
            handler: function (range, context) {
                if (isStructuredFormat(context.format)) return true;
                var lineText = this.quill.getText(range.index - context.offset, context.offset);
                if (!isNbspOnly(lineText)) return true;
                this.quill.insertText(range.index, INDENT_UNIT, Quill.sources.USER);
                this.quill.setSelection(range.index + INDENT_UNIT.length, Quill.sources.SILENT);
                return false;
            },
        },
        'outdent-anywhere': {
            key: 9,
            shiftKey: true,
            collapsed: true,
            handler: function (range, context) {
                if (isStructuredFormat(context.format)) return true;
                var lineStart = range.index - context.offset;
                if (context.offset > 0) {
                    var beforeCursor = this.quill.getText(lineStart, context.offset);
                    if (!isNbspOnly(beforeCursor)) return true;
                    var removeLen = Math.min(INDENT_UNIT.length, beforeCursor.length);
                    this.quill.deleteText(range.index - removeLen, removeLen, Quill.sources.USER);
                    this.quill.setSelection(range.index - removeLen, Quill.sources.SILENT);
                    return false;
                }
                // Cursor at the true start of the line (e.g. after Home) — text
                // "before cursor" is empty by definition, so look at the line's
                // own leading run of NBSP instead.
                var leading = this.quill.getText(lineStart, INDENT_UNIT.length);
                var leadingCount = 0;
                while (leadingCount < leading.length && leading.charCodeAt(leadingCount) === NBSP_CODE) leadingCount++;
                if (leadingCount === 0) return true;
                this.quill.deleteText(lineStart, leadingCount, Quill.sources.USER);
                this.quill.setSelection(lineStart, Quill.sources.SILENT);
                return false;
            },
        },
    };
}

// Quill 1.3.7 registers several of its own Tab bindings (list/blockquote
// indent, code-block indent, a generic unconditional fallback) ahead of
// anything passed via `modules.keyboard.bindings` in the constructor —
// bindings always run first-match-wins in registration order, so our
// handlers above would never even be reached if added the normal way.
// Prepending them directly onto quill.keyboard.bindings[9] after
// construction makes them run first instead.
function installIndentAnywhereBindings(quill) {
    var TAB_KEY = 9;
    var ours = quillIndentAnywhereBindings();
    quill.keyboard.bindings[TAB_KEY] = [ours['indent-anywhere'], ours['outdent-anywhere']]
        .concat(quill.keyboard.bindings[TAB_KEY] || []);
}

// Drop cap is a plain toggleable inline format (like bold/italic), not an
// automatic CSS ::first-letter rule — a post's first line is often itself a
// heading/decorative divider, so "first rendered character" isn't reliably
// the paragraph the author wants capitalized. The user picks the exact
// paragraph by placing the cursor in it and clicking the button.
function registerDropCapFormat() {
    var InlineBlot = Quill.import('blots/inline');
    class DropCap extends InlineBlot {
        static formats() { return true; }
    }
    DropCap.blotName = 'dropcap';
    DropCap.tagName = 'span';
    DropCap.className = 'drop-cap';
    Quill.register(DropCap, true);
}

// Always targets the current paragraph's first non-whitespace character,
// regardless of where the cursor is within that paragraph — not a Tab/marker
// based mechanism, just "wherever the cursor is, capitalize that paragraph".
function dropCapToolbarHandler() {
    var range = this.quill.getSelection();
    if (!range) return;
    var lineInfo = this.quill.getLine(range.index);
    var line = lineInfo[0];
    var offset = lineInfo[1];
    if (!line) return;
    var lineStart = range.index - offset;
    var lineText = this.quill.getText(lineStart, line.length());
    var match = lineText.match(/\S/);
    if (!match) return;
    var charIndex = lineStart + match.index;
    var isActive = !!this.quill.getFormat(charIndex, 1).dropcap;
    this.quill.formatText(charIndex, 1, 'dropcap', !isActive, Quill.sources.USER);
}

// Quill Snow theme's default link/image UI is a floating tooltip positioned
// via raw getBoundingClientRect() math, which desyncs under this site's
// sitewide `zoom: 0.9` (vaelthorn-theme.css) — the tooltip opens off-screen
// (confirmed: negative left offset), so users can't see it at all. Plain
// window.prompt() is a native browser dialog, unaffected by CSS zoom.
function linkToolbarHandler() {
    var range = this.quill.getSelection();
    if (!range) return;
    if (this.quill.getFormat(range).link) {
        this.quill.format('link', false, Quill.sources.USER);
        return;
    }
    if (range.length === 0) return;
    var url = window.prompt('ใส่ URL ลิงก์:');
    if (!url) return;
    this.quill.format('link', url, Quill.sources.USER);
}

function imageToolbarHandler() {
    var range = this.quill.getSelection(true);
    var url = window.prompt('ใส่ URL รูปภาพ:');
    if (!url) return;
    this.quill.insertEmbed(range ? range.index : this.quill.getLength(), 'image', url, Quill.sources.USER);
}

// Right-indent mirrors Quill's own built-in left `indent` format exactly — a
// block-scoped class Attributor (not a Blot), so the toolbar buttons need no
// custom handler at all: Quill's toolbar already calls quill.format(name, value)
// automatically for any registered non-Embed format with no handler, same as
// the existing left indent/outdent buttons.
function registerIndentRightFormat() {
    var Parchment = Quill.import('parchment');
    class IndentRightAttributor extends Parchment.Attributor.Class {
        add(node, value) {
            if (value === '+1' || value === '-1') {
                var current = this.value(node) || 0;
                value = value === '+1' ? current + 1 : current - 1;
            }
            if (value === 0) {
                this.remove(node);
                return true;
            }
            return super.add(node, value);
        }
        canAdd(node, value) {
            // Class-derived values read back as strings ("3"), but the
            // whitelist is numeric ([1..8]) — mirrors Quill's own built-in
            // indent Attributor, which needs the exact same fallback.
            return super.canAdd(node, value) || super.canAdd(node, parseInt(value));
        }
        value(node) {
            return parseInt(super.value(node)) || undefined;
        }
    }
    var IndentRight = new IndentRightAttributor('indentright', 'ql-rindent', {
        scope: Parchment.Scope.BLOCK,
        whitelist: [1, 2, 3, 4, 5, 6, 7, 8],
    });
    Quill.register({ 'formats/indentright': IndentRight }, true);
}

// Explicit px sizes on selected text (Quill's built-in style-based size
// attributor sets inline font-size directly, no matching CSS class needed
// per value) instead of the semantic H1/H2/H3 header dropdown.
function registerSizeFormat() {
    var SizeStyle = Quill.import('attributors/style/size');
    SizeStyle.whitelist = ['12px', '14px', '16px', '18px', '20px', '24px', '28px', '32px', '40px', '48px'];
    Quill.register(SizeStyle, true);
}

document.addEventListener('DOMContentLoaded', function () {
    const Font = Quill.import('formats/font');
    Font.whitelist = ['sarabun','prompt','kanit','noto-serif-thai','mitr','charm','trirong','monospace'];
    Quill.register(Font, true);
    registerDropCapFormat();
    registerIndentRightFormat();
    registerSizeFormat();

    const quill = new Quill('#create-editor', {
        modules: {
            toolbar: {
                container: '#create-editor-toolbar',
                handlers: { dropcap: dropCapToolbarHandler, link: linkToolbarHandler, image: imageToolbarHandler },
            },
        },
        theme: 'snow',
        placeholder: 'เขียนโพสต์แรกของกระทู้…',
    });
    installIndentAnywhereBindings(quill);

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

    // ── Preview toggle: show the post exactly as it will render on the thread ──
    const previewToggle = document.getElementById('create-preview-toggle');
    const previewPane    = document.getElementById('create-content-preview');
    const toolbarEl       = document.getElementById('create-editor-toolbar');
    const editorEl         = document.getElementById('create-editor');
    if (previewToggle && previewPane) {
        previewToggle.addEventListener('click', function () {
            const showingPreview = !previewPane.classList.contains('hidden');
            if (showingPreview) {
                previewPane.classList.add('hidden');
                toolbarEl.classList.remove('hidden');
                editorEl.classList.remove('hidden');
                previewToggle.textContent = '👁 ดูตัวอย่าง';
            } else {
                previewPane.innerHTML = quill.root.innerHTML;
                previewPane.classList.remove('hidden');
                toolbarEl.classList.add('hidden');
                editorEl.classList.add('hidden');
                previewToggle.textContent = '✎ กลับไปแก้ไข';
            }
        });
    }
});
</script>
@endpush
