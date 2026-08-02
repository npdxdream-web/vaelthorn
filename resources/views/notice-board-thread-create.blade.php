@extends('layouts.app')

@section('title', 'สร้างกระทู้ — ' . $noticeBoard->name)

@push('head')
@include('partials.quill-editor-head')
@endpush

@section('content')
<x-public.shell :character-status="$currentCharacter">
    <x-slot:left>
        <div class="sticky top-20">
            <div class="archive-panel p-5">
                <h3 class="font-display mb-4 text-base" style="color: {{ $noticeBoard->color }}">{{ $noticeBoard->name }}</h3>
                <div class="border-t border-gold/10 pt-3">
                    <a href="{{ route('notice-board.show', $noticeBoard->id) }}"
                       class="inline-flex items-center gap-1.5 text-xs text-text-muted hover:text-gold transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to {{ $noticeBoard->name }}
                    </a>
                </div>
            </div>
        </div>
    </x-slot:left>

    <div class="mb-4 flex items-center gap-2 text-sm text-text-muted">
        <a href="{{ route('home') }}" class="hover:text-gold">Thiran</a>
        <span>/</span>
        <a href="{{ route('notice-board.show', $noticeBoard->id) }}" class="hover:text-gold">{{ $noticeBoard->name }}</a>
        <span>/</span>
        <span class="text-text">สร้างกระทู้ใหม่</span>
    </div>

    <div class="rounded-xl border border-border bg-bg-elevated p-6">
        <h1 class="font-display mb-1 text-2xl text-gold">สร้างกระทู้ใหม่</h1>
        <p class="mb-6 text-sm text-text-muted">ใน {{ $noticeBoard->name }} — เผยแพร่ทันทีเมื่อกดสร้าง</p>

        <form method="POST" action="{{ route('notice-board.thread.store', $noticeBoard->id) }}" id="create-thread-form">
            @csrf
            <input type="hidden" name="content" id="create-content-input">

            <div class="mb-4">
                <label for="title" class="mb-1 block text-sm text-text-muted">หัวข้อกระทู้</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                       placeholder="ชื่อเรื่อง…"
                       class="w-full rounded-lg border border-[#2a2a2a] bg-[#0a0a0a] px-4 py-2 text-[#e8e6e3] placeholder:text-[#686664] focus:border-[#D4AF37] focus:outline-none">
                @error('title')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

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
                        <button class="ql-code-block" title="บล็อกโค้ด"></button>
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
                <div id="create-editor" class="thread-reading min-h-65 p-4 text-[#e8e6e3]"></div>
                <div id="create-content-preview" class="thread-reading prose prose-invert max-w-none hidden p-4"></div>

                @error('content')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('notice-board.show', $noticeBoard->id) }}"
                   class="rounded-lg border border-[#2a2a2a] px-4 py-2 text-sm text-text-muted hover:border-[#D4AF37] hover:text-text">
                    ยกเลิก
                </a>

                <div class="flex gap-2">
                    <button type="button" id="create-preview-toggle"
                            class="rounded-lg border border-gold/30 px-4 py-2 text-sm text-gold/80 hover:border-gold hover:text-gold">
                        👁 ดูตัวอย่าง
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-[#D4AF37] px-5 py-2 text-sm font-medium text-[#0f0f0f] hover:bg-[#B8941F]">
                        สร้างกระทู้
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-public.shell>
@endsection

@push('scripts')
@include('partials.quill-editor-scripts')
<script>
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
    const contentInput = document.getElementById('create-content-input');

    initColorPicker('create-slider', 'create-hex', 'create-preview', quill);

    form.addEventListener('submit', function () {
        contentInput.value = quill.root.innerHTML;
    });

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
