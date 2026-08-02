@extends('layouts.app')

@section('title', $thread->title . ' — ' . $thread->noticeBoard->name)

@push('head')
@include('partials.quill-editor-head')
@endpush

@section('content')
<x-public.shell :character-status="$currentCharacter">

    <div class="mb-4">
        <a href="{{ route('notice-board.show', $thread->notice_board_id) }}"
           class="inline-flex items-center gap-2 font-display text-xs uppercase tracking-widest text-text-subtle hover:text-gold">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            กลับไปที่ {{ $thread->noticeBoard->name }}
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-800 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-1" style="color: {{ $thread->noticeBoard->color }}">{{ $thread->noticeBoard->name }}</p>
        <h1 class="font-decorative text-2xl text-gold">{{ $thread->title }}</h1>
    </div>

    <div class="space-y-4">
        @foreach($posts as $post)
            <div class="archive-panel-soft p-5">
                <div class="mb-2 flex items-center justify-between gap-4">
                    <span class="font-display text-sm text-gold">{{ $post->creator->name ?? '—' }}</span>
                    <span class="text-xs text-text-subtle">{{ $post->created_at->diffForHumans() }}</span>
                </div>
                <div class="thread-reading prose prose-invert max-w-none">{!! $post->content !!}</div>
            </div>
        @endforeach
    </div>

    @if(auth()->user()->isAtLeastAdmin())
        <div class="thread-panel mt-6 p-0">
            <div class="border-b border-gold/10 bg-gold/2.5 px-5 py-4 sm:px-6">
                <h3 class="font-decorative text-[0.95rem] tracking-wider text-gold">ตอบกระทู้</h3>
            </div>
            <form method="POST" action="{{ route('notice-board.thread.post.store', $thread->id) }}" id="thread-reply-form">
                @csrf
                <input type="hidden" name="content" id="thread-content-input">

                <div>
                    <div id="thread-editor-toolbar" class="ql-toolbar ql-snow p-2">
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
                            <button class="ql-bold" title="ตัวหนา (Ctrl+B)"></button>
                            <button class="ql-italic" title="ตัวเอียง (Ctrl+I)"></button>
                            <button class="ql-underline" title="ขีดเส้นใต้ (Ctrl+U)"></button>
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
                            <button class="ql-clean" title="ล้างการจัดรูปแบบทั้งหมด"></button>
                        </span>
                        <span class="ql-formats">
                            <div class="color-picker-wrap">
                                <input type="range" min="0" max="360" value="43" class="color-hue-slider" id="reply-slider" title="เลื่อนเพื่อเลือกสีข้อความ">
                                <input type="text" class="color-hex-input" id="reply-hex" placeholder="#D4AF37" maxlength="7" autocomplete="off">
                                <div class="color-preview-box" id="reply-preview" style="background:#D4AF37"></div>
                            </div>
                        </span>
                    </div>
                    <div id="thread-editor" class="min-h-65 p-5 text-[#e8e6e3]"></div>
                    <div id="thread-preview" class="thread-reading prose prose-invert max-w-none hidden p-5"></div>
                </div>

                @error('content')
                    <p class="mb-3 px-5 text-sm text-red-400">{{ $message }}</p>
                @enderror

                <div class="flex flex-col gap-3 border-t border-gold/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <span class="text-xs text-text-muted">ตอบในฐานะแอดมิน</span>
                    <div class="flex items-center gap-2">
                        <button type="button" id="thread-preview-toggle"
                                class="rounded-lg border border-gold/30 px-4 py-2 text-sm text-gold/80 hover:border-gold hover:text-gold">
                            👁 ดูตัวอย่าง
                        </button>
                        <button type="submit" class="btn-primary">ส่งคำตอบ</button>
                    </div>
                </div>
            </form>
        </div>
    @endif

</x-public.shell>
@endsection

@push('scripts')
@include('partials.quill-editor-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editorEl = document.getElementById('thread-editor');
    if (!editorEl) return;

    const Font = Quill.import('formats/font');
    Font.whitelist = ['sarabun','prompt','kanit','noto-serif-thai','mitr','charm','trirong','monospace'];
    Quill.register(Font, true);
    registerDropCapFormat();
    registerIndentRightFormat();
    registerSizeFormat();

    const quill = new Quill('#thread-editor', {
        modules: {
            toolbar: {
                container: '#thread-editor-toolbar',
                handlers: { dropcap: dropCapToolbarHandler, link: linkToolbarHandler, image: imageToolbarHandler },
            },
        },
        theme: 'snow',
        placeholder: 'เขียนคำตอบของคุณที่นี่…',
    });
    installIndentAnywhereBindings(quill);

    initColorPicker('reply-slider', 'reply-hex', 'reply-preview', quill);

    const hiddenInput = document.getElementById('thread-content-input');
    const form        = document.getElementById('thread-reply-form');
    form.addEventListener('submit', function () {
        hiddenInput.value = quill.root.innerHTML;
    });

    const previewToggle = document.getElementById('thread-preview-toggle');
    const previewPane    = document.getElementById('thread-preview');
    const toolbarEl       = document.getElementById('thread-editor-toolbar');
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
