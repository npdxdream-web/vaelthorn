@extends('layouts.app')

@section('title', $thread->title . ' — Vaelthorn')

@push('head')
    {{-- Quill CSS --}}
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    {{-- Google Fonts (Thai support) --}}
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,300;0,400;0,700;1,400&family=Prompt:ital,wght@0,300;0,400;0,600;1,400&family=Kanit:ital,wght@0,300;0,400;0,600;1,400&family=Noto+Serif+Thai:wght@400;700&family=Mitr:wght@300;400;600&family=Charm:wght@400;700&family=Trirong:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        /* ── Quill theme ───────────────────────────────────────── */
        .ql-toolbar.ql-snow { background:#0b0b0b; border:1px solid #2a2a2a; border-radius:0.5rem 0.5rem 0 0; }
        .ql-container.ql-snow { background:#090909; border:1px solid #2a2a2a; border-top:none; border-radius:0 0 0.5rem 0.5rem; color:#e8e6e3; }
        .ql-toolbar.ql-snow button, .ql-toolbar.ql-snow .ql-picker-label { color:#e8e6e3; }
        .ql-toolbar.ql-snow button:hover, .ql-toolbar.ql-snow .ql-picker-label:hover,
        .ql-toolbar.ql-snow button.ql-active, .ql-toolbar.ql-snow .ql-picker-label.ql-active { color:#D4AF37 !important; }
        .ql-toolbar.ql-snow .ql-stroke { stroke:#e8e6e3; }
        .ql-toolbar.ql-snow button:hover .ql-stroke, .ql-toolbar.ql-snow button.ql-active .ql-stroke { stroke:#D4AF37; }
        .ql-toolbar.ql-snow .ql-fill { fill:#e8e6e3; }
        .ql-toolbar.ql-snow button:hover .ql-fill { fill:#D4AF37; }
        .ql-toolbar.ql-snow .ql-picker-options { background:#111; border:1px solid #2a2a2a; }
        .ql-toolbar.ql-snow .ql-picker-item { color:#e8e6e3; }
        .ql-toolbar.ql-snow .ql-picker-item:hover, .ql-toolbar.ql-snow .ql-picker-item.ql-selected { color:#D4AF37; }
        .ql-editor { min-height:200px; color:#e8e6e3; }
        .ql-editor.ql-blank::before { color:#686664; }
        .ql-editor a { color:#D4AF37; }
        .ql-editor p { margin-bottom:.75rem; }

        /* ── Google Font classes ───────────────────────────────── */
        .ql-font-sarabun        { font-family:'Sarabun','Noto Sans Thai',sans-serif; }
        .ql-font-prompt         { font-family:'Prompt','Noto Sans Thai',sans-serif; }
        .ql-font-kanit          { font-family:'Kanit','Noto Sans Thai',sans-serif; }
        .ql-font-noto-serif-thai{ font-family:'Noto Serif Thai',Georgia,serif; }
        .ql-font-mitr           { font-family:'Mitr','Noto Sans Thai',sans-serif; }
        .ql-font-charm          { font-family:'Charm',cursive; }
        .ql-font-trirong        { font-family:'Trirong',Georgia,serif; }
        .ql-font-monospace      { font-family:SFMono-Regular,Consolas,'Liberation Mono',monospace; }

        /* ── Font picker: show each option in its own font ──────── */
        .ql-picker.ql-font .ql-picker-item[data-value="sarabun"]         { font-family:'Sarabun',sans-serif; }
        .ql-picker.ql-font .ql-picker-item[data-value="prompt"]          { font-family:'Prompt',sans-serif; }
        .ql-picker.ql-font .ql-picker-item[data-value="kanit"]           { font-family:'Kanit',sans-serif; }
        .ql-picker.ql-font .ql-picker-item[data-value="noto-serif-thai"] { font-family:'Noto Serif Thai',serif; }
        .ql-picker.ql-font .ql-picker-item[data-value="mitr"]            { font-family:'Mitr',sans-serif; }
        .ql-picker.ql-font .ql-picker-item[data-value="charm"]           { font-family:'Charm',cursive; }
        .ql-picker.ql-font .ql-picker-item[data-value="trirong"]         { font-family:'Trirong',serif; }
        .ql-picker.ql-font .ql-picker-item[data-value="monospace"]       { font-family:monospace; }

        /* ── Rendered post body: Quill's own align/heading rules are
               scoped to .ql-editor, which .thread-reading doesn't carry,
               so they never applied on the read view. Re-declare them
               here (mirrors quill.snow.css) so posts render exactly as
               authored in the editor. ───────────────────────────────── */
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

        /* ── Color picker ───────────────────────────────────────── */
        .color-picker-wrap { display:inline-flex; align-items:center; gap:.4rem; vertical-align:middle; }
        .color-hue-slider { -webkit-appearance:none; appearance:none; width:100px; height:10px; border-radius:5px; cursor:pointer; border:1px solid #333; background:linear-gradient(to right,#ff0000,#ff8000,#ffff00,#80ff00,#00ff00,#00ff80,#00ffff,#0080ff,#0000ff,#8000ff,#ff00ff,#ff0080,#ff0000); }
        .color-hue-slider::-webkit-slider-thumb { -webkit-appearance:none; width:14px; height:14px; border-radius:50%; background:#fff; border:2px solid #555; cursor:pointer; }
        .color-hue-slider::-moz-range-thumb { width:14px; height:14px; border-radius:50%; background:#fff; border:2px solid #555; cursor:pointer; }
        .color-hex-input { width:90px; padding:.25rem .4rem; border:1px solid #2a2a2a; border-radius:.35rem; background:#111; color:#e8e6e3; font-size:.8rem; }
        .color-hex-input:focus { outline:1px solid #D4AF37; }
        .color-preview-box { width:22px; height:22px; border-radius:4px; border:1px solid #2a2a2a; flex-shrink:0; }

        /* ── Post pending badge ─────────────────────────────────── */
        .post-pending { border-color: rgba(251,191,36,.3) !important; }

        /* Thread-page specific layout overrides */
        .public-shell {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
        .public-shell > .grid {
            grid-template-columns: 260px minmax(0, 1fr) 260px !important;
            gap: 18px !important;
        }
        .thread-panel,
        .thread-post-card {
            border-radius: 0 !important;
        }
        .thread-reading {
            font-size: 18px !important;
            line-height: 1.9 !important;
        }
        .thread-author-panel .font-display.text-lg {
            font-size: 1.2rem !important;
        }
        #charModule {
            margin-bottom: 1rem;
        }
        @media (min-width: 1024px) {
            .thread-post-grid {
                grid-template-columns: 280px minmax(0, 1fr) !important;
            }
        }
        @media (max-width: 1023px) {
            .public-shell > .grid {
                grid-template-columns: 1fr !important;
            }
            .public-shell > .grid > aside {
                display: none !important;
            }
        }

        /* ── Compact thread header: always the same shape, banner or not ── */
        .thread-header-compact__breadcrumb {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: var(--font-display);
            font-size: 0.6rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #746a5a;
            text-decoration: none;
            margin-bottom: 10px;
        }
        .thread-header-compact__breadcrumb:hover {
            color: #d4af37;
        }
        .thread-header-compact__title {
            font-family: var(--font-decorative);
            font-size: 19px;
            font-weight: 700;
            color: #d4af37;
            line-height: 1.25;
            margin: 0 0 6px;
        }
        .thread-header-compact__meta {
            font-family: var(--font-display);
            font-size: 0.7rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6b5f45;
        }
        /* Hairline under the header meta line */
        .thread-header-hairline {
            height: 1px;
            background: #2a2620;
            margin-top: 16px;
        }

        /* ── Hero banner (shown only when city.banner_image is set): title/meta
               overlay directly on the image as one unit, not a separate strip ── */
        .thread-hero-banner {
            position: relative;
            min-height: 200px;
            overflow: hidden;
            background-size: cover;
            background-position: center;
            background-color: #141210;
            display: flex;
            align-items: flex-end;
        }
        .thread-hero-banner__shadow {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(6,5,4,0.1) 0%, rgba(6,5,4,0.3) 55%, rgba(6,5,4,0.9) 100%);
        }
        .thread-hero-banner__content {
            position: relative;
            z-index: 1;
            width: 100%;
            padding: 16px 22px 18px;
        }
        .thread-hero-banner .thread-header-compact__breadcrumb,
        .thread-hero-banner .thread-header-compact__meta {
            color: #cbbfa0;
        }
        .thread-hero-banner .thread-header-compact__breadcrumb:hover {
            color: #f0d580;
        }
        .thread-hero-banner .thread-header-compact__title {
            font-size: 24px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.65);
        }
        @media (max-width: 639px) {
            .thread-header-compact__title { font-size: 17px; }
            .thread-hero-banner { min-height: 150px; }
            .thread-hero-banner .thread-header-compact__title { font-size: 19px; }
        }

        /* ── Lore post: opening manuscript-style post by an admin ───────── */
        .lore-post {
            padding: 8px 4px 4px;
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
        .lore-post-hr {
            height: 1px;
            background: #2a2620;
            margin-top: 28px;
        }

        /* ── Drop cap: user-applied via the "Drop Cap" toolbar button ─────
           Fixed to exactly 2 body-text line-boxes tall (18px font-size × line-height
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
    </style>
@endpush

@section('content')
<x-public.shell :character-status="$currentCharacter">
    {{-- ── Left rail: City Info ────────────────────────────────────────── --}}
    <x-slot:left>
        @php
            $recentThreads = $thread->city?->threads()
                ->where('id', '!=', $thread->id)
                ->latest()
                ->take(3)
                ->get() ?? collect();
        @endphp

        <div class="sticky top-20 space-y-4">
            <div class="thread-side-panel">
                <div class="flex h-20 items-center justify-center border-b border-gold/10 bg-gold/3">
                    <svg class="h-8 w-8 text-gold/35" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 3 3 8v2h18V8l-9-5Zm-6 9v7H4v2h16v-2h-2v-7h-2v7h-3v-7h-2v7H8v-7H6Z"/>
                    </svg>
                </div>
                <div class="thread-side-body">
                    <h3 class="font-display text-sm text-gold">{{ $thread->city->name }}</h3>
                    <p class="mt-1 font-display text-[11px] uppercase tracking-[0.2em] text-text-subtle">{{ $thread->city->kingdom?->name ?? 'Unknown City' }}</p>
                    <p class="mt-3 text-xs leading-relaxed text-text-muted/75">{{ $thread->city->description ?? 'A quiet chronicle hall where stories gather in shadow and gold.' }}</p>
                    <div class="mt-4 grid grid-cols-3 gap-2">
                        <div class="border border-gold/12 bg-black/20 p-2 text-center">
                            <div class="font-display text-sm text-gold">{{ $thread->city?->threads()->count() ?? 1 }}</div>
                            <div class="archive-label text-[11px]">Threads</div>
                        </div>
                        <div class="border border-gold/12 bg-black/20 p-2 text-center">
                            <div class="font-display text-sm text-gold">{{ $participants->count() }}</div>
                            <div class="archive-label text-[11px]">Members</div>
                        </div>
                        <div class="border border-gold/12 bg-black/20 p-2 text-center">
                            <div class="font-display text-sm text-gold">{{ $posts->count() }}</div>
                            <div class="archive-label text-[11px]">Online</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="thread-side-panel">
                <div class="thread-side-heading">
                    <span>Recent Threads</span>
                    <span class="text-gold/35">⌃</span>
                </div>
                <div class="thread-side-body">
                    @forelse($recentThreads as $recent)
                        <a href="{{ route('thread', $recent->id) }}" class="thread-mini-row group">
                            <span class="h-1.5 w-1.5 rounded-full {{ $recent->isLive() ? 'bg-emerald-500' : 'bg-gold/45' }}"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-xs text-text-muted group-hover:text-gold">{{ $recent->title }}</span>
                                <span class="font-display text-[11px] uppercase tracking-[0.18em] text-text-subtle">{{ $recent->approvedPostsCount() }} posts</span>
                            </span>
                        </a>
                    @empty
                        <p class="text-xs text-text-subtle">No other threads yet.</p>
                    @endforelse
                    <a href="{{ route('city', $thread->city->id) }}" class="mt-3 block border border-gold/12 px-3 py-2 text-center font-display text-[11px] uppercase tracking-[0.18em] text-gold/55 hover:border-gold/35 hover:text-gold">All Threads</a>
                </div>
            </div>

            @if($participants->count())
            <div class="thread-side-panel">
                <div class="thread-side-heading">
                    <span>City Council</span>
                    <span class="text-gold/35">⌃</span>
                </div>
                <div class="thread-side-body">
                    @foreach($participants->take(3) as $participant)
                        @php $pColor = $participant->kingdom->color ?? '#c8a84b'; @endphp
                        <div class="thread-mini-row">
                            <x-avatar-frame :rank="strtolower($participant->auto_rank)" :size="30" :initial="mb_substr($participant->name, 0, 1)" :color="$pColor">
                                @if($participant->avatar)
                                    <img src="{{ $participant->avatar }}" alt="{{ $participant->name }}" style="width:100%;height:100%;object-fit:cover;">
                                @endif
                            </x-avatar-frame>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-xs text-text-muted">{{ $participant->name }}</div>
                                <div class="font-display text-[11px] uppercase tracking-[0.18em] text-text-subtle">{{ $participant->auto_rank }}</div>
                            </div>
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="thread-side-panel">
                <div class="thread-side-heading">
                    <span>City Lore</span>
                    <span class="text-gold/35">⌄</span>
                </div>
            </div>
        </div>

        <div class="hidden">
            <div class="archive-panel p-5">
                <h3 class="font-display mb-4 text-base text-gold">{{ $thread->city->name }}</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex flex-col gap-0.5">
                        <span class="archive-label">City</span>
                        <span style="color:{{ $thread->city->kingdom?->color ?? '#c8a84b' }}">{{ $thread->city->kingdom?->name ?? '—' }}</span>
                    </div>
                    <div class="border-t border-gold/10 pt-3 flex flex-col gap-0.5">
                        <span class="archive-label">Region</span>
                        <span class="text-text">{{ $thread->city->name }}</span>
                    </div>
                    <div class="border-t border-gold/10 pt-3 flex flex-col gap-0.5">
                        <span class="archive-label">Posts</span>
                        <span class="text-text">{{ $posts->count() }}</span>
                    </div>
                    <div class="border-t border-gold/10 pt-3 flex flex-col gap-0.5">
                        <span class="archive-label">Status</span>
                        <span class="rounded-full border px-2 py-0.5 text-xs {{ $thread->status_color }}">{{ $thread->status_label }}</span>
                    </div>
                    @if($thread->display_tag)
                        <div class="border-t border-gold/10 pt-3 flex flex-col gap-0.5">
                            <span class="archive-label">{{ $thread->event ? 'ประเภท Event' : 'หมวดหมู่' }}</span>
                            <span class="rounded px-2 py-0.5 text-xs w-fit"
                                  style="background:{{ $thread->display_tag['bg'] }}; color:{{ $thread->display_tag['text'] }};">
                                {{ $thread->display_tag['label'] }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-slot:left>

        {{-- ── Main column ──────────────────────────────────────────────────── --}}

            {{-- Back link + title: single hero unit when the city has a banner,
                 otherwise the same plain compact header as always --}}
            @php
                $locationText = $thread->location_label ?? $thread->city?->kingdom?->name;
            @endphp
            @if($thread->banner_image)
                <div class="thread-hero-banner mb-6" style="background-image:url('{{ $thread->banner_image_url }}');">
                    <div class="thread-hero-banner__shadow"></div>
                    <div class="thread-hero-banner__content">
                        <a href="{{ route('city', $thread->city->id) }}" class="thread-header-compact__breadcrumb">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to {{ $thread->city->name }}
                        </a>
                        <h1 class="thread-header-compact__title">{{ $thread->title }}</h1>
                        <div class="thread-header-compact__meta">
                            {{ $thread->isLive() ? 'LIVE' : 'CLOSED' }}
                            @if($locationText) &middot; {{ $locationText }} @endif
                            &middot; {{ $thread->approvedPostsCount() }} posts
                        </div>
                    </div>
                </div>
            @else
                <div class="thread-header-compact mb-6">
                    <a href="{{ route('city', $thread->city->id) }}" class="thread-header-compact__breadcrumb">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to {{ $thread->city->name }}
                    </a>
                    <h1 class="thread-header-compact__title">{{ $thread->title }}</h1>
                    <div class="thread-header-compact__meta">
                        {{ $thread->isLive() ? 'LIVE' : 'CLOSED' }}
                        @if($locationText) &middot; {{ $locationText }} @endif
                        &middot; {{ $thread->approvedPostsCount() }} posts
                    </div>
                    <div class="thread-header-hairline"></div>
                </div>
            @endif

            {{-- Owner actions: edit (pending/draft/request_edit) + delete (draft only).
                 Admins/moderators also get the edit link regardless of status — the
                 controller (ThreadController::edit/update) already lets admins edit a
                 live thread, this just exposes that in the UI (e.g. to fix a thread's
                 banner image after it's gone live). --}}
            @if((auth()->id() === $thread->created_by && in_array($thread->status, ['pending','draft','request_edit'])) || auth()->user()->isAtLeastModerator())
                <div class="mb-6 flex items-center gap-2">
                    <a href="{{ route('thread.edit', $thread->id) }}"
                       class="inline-flex items-center gap-2 rounded-lg border border-amber-400/40 bg-amber-950/20 px-4 py-2 text-sm text-amber-300 hover:bg-amber-950/40">
                        ✏️ แก้ไขกระทู้
                    </a>

                    @if($thread->status === 'draft' && auth()->id() === $thread->created_by)
                    <form method="POST" action="{{ route('thread.destroy', $thread->id) }}"
                          onsubmit="return confirm('ลบกระทู้ร่างนี้ถาวรหรือไม่?\n\nกระทู้จะถูกย้ายไปถังขยะและลบหลัง 3 วัน')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg border border-rose-400/30 bg-rose-950/10 px-4 py-2 text-sm text-rose-400 hover:bg-rose-950/30">
                            🗑 ลบร่าง
                        </button>
                    </form>
                    @endif
                </div>
            @endif

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-6 rounded-lg border border-emerald-800 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-400">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Confirm delete thread (shown when last post was deleted) --}}
            @if(session('confirm_delete_thread'))
            <div class="archive-panel-soft mb-6 p-5">
                <h3 class="font-display mb-2 text-base text-rose-400">กระทู้นี้ไม่มีโพสต์แล้ว</h3>
                <p class="mb-4 text-sm text-text">ต้องการลบกระทู้ทั้งหมดด้วยไหม?</p>
                <div class="flex gap-3">
                    @if(auth()->user()->isAtLeastAdmin() || auth()->id() === $thread->created_by)
                    <form method="POST" action="{{ route('thread.destroy', $thread->id) }}">
                        @csrf @method('DELETE')
                        <button class="rounded-lg bg-rose-700 px-4 py-2 text-sm text-white hover:bg-rose-600">
                            ใช่ ลบกระทู้ด้วย
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('thread', $thread->id) }}"
                       class="rounded-lg border border-[#2a2a2a] px-4 py-2 text-sm text-text-muted hover:border-[#D4AF37]">
                        ไม่ เก็บกระทู้ไว้
                    </a>
                </div>
            </div>
            @endif

            {{-- Moderation notices for thread owner --}}
            @if($thread->status === 'request_edit' && auth()->id() === $thread->created_by)
                <div class="archive-panel-soft mb-6 border-orange-400/30 p-5">
                    <h3 class="font-display mb-1 text-base text-orange-400">Admin ขอให้แก้ไขกระทู้นี้</h3>
                    <p class="mb-3 text-sm text-text">{{ $thread->moderation_message }}</p>
                    <a href="{{ route('thread.edit', $thread->id) }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-orange-600 px-4 py-2 text-sm text-white hover:bg-orange-500">
                        แก้ไขและส่งใหม่
                    </a>
                </div>
            @endif

            @if($thread->status === 'rejected' && auth()->id() === $thread->created_by)
                <div class="archive-panel-soft mb-6 border-rose-400/30 p-5">
                    <h3 class="font-display mb-1 text-base text-rose-400">กระทู้ถูกปฏิเสธ</h3>
                    <p class="text-sm text-text">{{ $thread->moderation_message }}</p>
                </div>
            @endif

            @if($thread->status === 'pending' && auth()->id() === $thread->created_by)
                <div class="archive-panel-soft mb-6 border-amber-400/30 p-4 text-sm text-amber-300">
                    กระทู้นี้กำลังรอการอนุมัติจาก Admin — เฉพาะคุณที่เห็นอยู่ขณะนี้
                </div>
            @endif

            {{-- ── Admin tools ──────────────────────────────────────────── --}}
            @if(auth()->user()->isAtLeastModerator())
            <div class="archive-panel-soft mb-6 p-5">
                <h3 class="archive-label mb-3">Admin Tools</h3>

                <div class="flex flex-wrap gap-2 mb-3">
                    @if($thread->status !== 'approved')
                    <form method="POST" action="{{ route('thread.moderate', $thread->id) }}">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <button class="rounded-lg bg-emerald-700 px-3 py-1.5 text-xs text-white hover:bg-emerald-600">✓ อนุมัติ</button>
                    </form>
                    @endif

                    <button onclick="openModForm('request_edit')"
                            class="rounded-lg border border-orange-400/40 bg-orange-950/20 px-3 py-1.5 text-xs text-orange-300 hover:bg-orange-950/40">
                        ✏ ขอแก้ไข
                    </button>
                    <button onclick="openModForm('reject')"
                            class="rounded-lg border border-rose-400/40 bg-rose-950/20 px-3 py-1.5 text-xs text-rose-300 hover:bg-rose-950/40">
                        ✕ ปฏิเสธ
                    </button>

                    @if($thread->status !== 'locked')
                    <form method="POST" action="{{ route('thread.moderate', $thread->id) }}">
                        @csrf
                        <input type="hidden" name="action" value="lock">
                        <button class="rounded-lg border border-slate-400/30 px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-800/40">
                            🔒 ล็อค
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('thread.moderate', $thread->id) }}">
                        @csrf
                        <input type="hidden" name="action" value="unlock">
                        <button class="rounded-lg border border-emerald-400/30 px-3 py-1.5 text-xs text-emerald-300 hover:bg-emerald-950/30">
                            🔓 ปลดล็อค
                        </button>
                    </form>
                    @endif

                    @if($thread->status !== 'archived')
                    <form method="POST" action="{{ route('thread.moderate', $thread->id) }}">
                        @csrf
                        <input type="hidden" name="action" value="archive">
                        <button class="rounded-lg border border-indigo-400/30 px-3 py-1.5 text-xs text-indigo-300 hover:bg-indigo-950/30">
                            📁 เก็บถาวร
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('thread.moderate', $thread->id) }}">
                        @csrf
                        <input type="hidden" name="action" value="unarchive">
                        <button class="rounded-lg border border-sky-400/30 px-3 py-1.5 text-xs text-sky-300 hover:bg-sky-950/30">
                            📂 ยกเลิกเก็บถาวร
                        </button>
                    </form>
                    @endif

                    <button onclick="openModForm('move')"
                            class="rounded-lg border border-sky-400/30 px-3 py-1.5 text-xs text-sky-300 hover:bg-sky-950/30">
                        ↗ ย้ายหมู่บ้าน
                    </button>

                    @if(auth()->user()->isAtLeastAdmin())
                    <form method="POST" action="{{ route('thread.destroy', $thread->id) }}"
                          onsubmit="return confirm('ยืนยันการลบกระทู้นี้?')">
                        @csrf @method('DELETE')
                        <button class="rounded-lg bg-rose-800 px-3 py-1.5 text-xs text-white hover:bg-rose-700">
                            🗑 ย้ายไปถังขยะ
                        </button>
                    </form>
                    @endif
                </div>

                {{-- Dynamic moderation form --}}
                <div id="mod-form-area" class="archive-panel-soft hidden mt-3 p-4">
                    <form method="POST" action="{{ route('thread.moderate', $thread->id) }}" id="mod-form">
                        @csrf
                        <input type="hidden" name="action" id="mod-action">

                        <div id="mod-msg-wrap" class="hidden mb-3">
                            <label class="mb-1 block text-xs text-text-muted">ข้อความถึงผู้เขียน</label>
                            <textarea name="message" rows="3"
                                      class="w-full rounded border border-[#2a2a2a] bg-[#0a0a0a] p-2 text-sm text-[#e8e6e3]"
                                      placeholder="ระบุเหตุผล..."></textarea>
                        </div>

                        <div id="mod-city-wrap" class="hidden mb-3">
                            <label class="mb-1 block text-xs text-text-muted">เมืองปลายทาง</label>
                            <select name="city_id"
                                    class="w-full rounded border border-[#2a2a2a] bg-[#0a0a0a] p-2 text-sm text-[#e8e6e3]">
                                @foreach($cities as $v)
                                    <option value="{{ $v->id }}" {{ $v->id == $thread->city_id ? 'selected' : '' }}>
                                        {{ $v->kingdom?->name }} → {{ $v->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="rounded-lg bg-amber-600 px-4 py-1.5 text-xs text-white hover:bg-amber-500">
                                ยืนยัน
                            </button>
                            <button type="button" onclick="closeModForm()"
                                    class="rounded-lg border border-[#2a2a2a] px-4 py-1.5 text-xs text-text-muted hover:border-[#D4AF37]">
                                ยกเลิก
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- ── Posts list ────────────────────────────────────────── --}}
            <div class="mb-6 space-y-6">
                @forelse($posts as $post)
                    @php
                        $isPostOwner   = $currentCharacter && $currentCharacter->id === $post->character_id;
                        $isAdmin       = auth()->user()->isAtLeastModerator();
                        $isPending     = $post->status === 'pending';
                        $postCharacter = $post->character;
                        $postStats     = $postCharacter?->stats;
                        $postCity      = $postCharacter?->kingdom;
                        $postLocation  = $postCharacter?->currentKingdom ?? $postCity;
                        $postColor     = $postCity?->color ?? '#c8a84b';
                        $postName      = $postCharacter?->name ?? 'Unknown Character';
                        $postRank      = $postCharacter?->title ?? $postCharacter?->auto_rank ?? 'Unrecorded Rank';
                        $postStatus    = $postCharacter?->status ? ucfirst($postCharacter->status) : 'Unknown';
                        // custom_frame overrides rank-based frame; portrait 3:4.12 ratio
                        $portraitRank  = $postCharacter?->custom_frame
                                         ?? strtolower($postCharacter?->auto_rank ?? 'stranger');
                        $portraitW     = 240;
                        $portraitH     = 450;
                        $isLorePost    = $loop->first && optional($postCharacter?->user)->isAtLeastAdmin();
                    @endphp

                    @if($isLorePost)
                        <div class="lore-post">
                            <div class="lore-post-body thread-reading prose prose-invert max-w-none">
                                {!! $post->content !!}
                            </div>

                            {{-- Status badge + council actions --}}
                            <div style="display:flex; align-items:center; justify-content:center; gap:10px; padding:8px 0; clear:both;">
                                @if($post->status === 'approved')
                                    {{-- Approved is the normal/default state — hidden to reduce badge noise --}}
                                @elseif($post->status === 'pending')
                                    <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; color:#c8a84b; border:0.5px solid #c8a84b44; padding:3px 10px;">● PENDING</span>
                                @else
                                    <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; color:#8B2020; border:0.5px solid #8B202044; padding:3px 10px;">{{ strtoupper($post->status) }}</span>
                                @endif

                                @if($isAdmin || ($isPostOwner && $isPending))
                                    @if($isAdmin)
                                    <span style="font-family:var(--font-display); font-size:8px; letter-spacing:2px; color:#3a3020;">COUNCIL</span>
                                    @endif

                                    @if($isAdmin && $isPending)
                                    <form method="POST" action="{{ route('post.approve', $post->id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" style="font-family:var(--font-display); font-size:11px; letter-spacing:1px; color:#2d7a3a; border:0.5px solid #2d7a3a55; background:transparent; padding:4px 11px; cursor:pointer;">✓ APPROVE</button>
                                    </form>
                                    @endif

                                    <a href="{{ route('post.edit', $post->id) }}"
                                       style="font-family:var(--font-display); font-size:11px; letter-spacing:1px; color:#c8a84b; border:0.5px solid #c8a84b33; background:transparent; padding:4px 11px; text-decoration:none; display:inline-block;">✎ EDIT</a>

                                    <form method="POST" action="{{ route('post.destroy', $post->id) }}" style="display:inline;"
                                          onsubmit="return confirm('ลบโพสต์นี้?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="font-family:var(--font-display); font-size:11px; letter-spacing:1px; color:#6b3030; border:0.5px solid #6b303044; background:transparent; padding:4px 11px; cursor:pointer;">✕ DELETE</button>
                                    </form>
                                @endif
                            </div>

                            <div class="lore-post-hr"></div>
                        </div>
                    @else
                    <div class="thread-post-card {{ $isPending ? 'post-pending' : '' }}">
                        <div class="thread-post-grid">
                            <aside class="thread-author-panel p-4 xl:p-5">
                                {{-- Portrait Art Deco frame —  replaces .thread-portrait --}}
                                <div class="mb-4 flex justify-center">
                                    <x-avatar-frame
                                        :rank="$portraitRank"
                                        :size="$portraitW"
                                        :height="$portraitH"
                                        :initial="mb_substr($postName, 0, 1)"
                                        :color="$postColor"
                                    >
                                        @if($postCharacter?->avatar)
                                            <img src="{{ $postCharacter->avatar }}" alt="{{ $postName }}"
                                                 style="width:100%;height:100%;object-fit:cover;">
                                        @endif
                                    </x-avatar-frame>
                                </div>

                                <div class="text-center">
                                    <div class="font-display text-lg text-gold">{{ $postName }}</div>
                                    <div class="archive-label mt-1">{{ $postRank }}</div>
                                </div>

                                <div class="mt-4 space-y-2 text-sm">
                                    <div class="flex items-center justify-between gap-3 border-b border-gold/10 pb-2">
                                        <span class="thread-meta-label">Kingdom</span>
                                        <span class="text-right text-text" style="color:{{ $postColor }}">{{ $postCity?->name ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 border-b border-gold/10 pb-2">
                                        <span class="thread-meta-label">Location</span>
                                        <span class="text-right text-text">{{ $postLocation?->name ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 border-b border-gold/10 pb-2">
                                        <span class="thread-meta-label">Status</span>
                                        <span class="rounded-full border border-emerald-400/25 bg-emerald-950/25 px-2 py-0.5 text-xs text-emerald-300">{{ $postStatus }}</span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 pt-2">
                                        <div class="rounded border border-gold/10 bg-black/20 p-2 text-center">
                                            <div class="archive-label text-[11px]">Level</div>
                                            <div class="text-gold">{{ $postStats->level ?? 1 }}</div>
                                        </div>
                                        <div class="rounded border border-gold/10 bg-black/20 p-2 text-center">
                                            <div class="archive-label text-[11px]">Posts</div>
                                            <div class="text-gold">{{ $postCharacter?->posts_count ?? '—' }}</div>
                                        </div>
                                        <div class="rounded border border-gold/10 bg-black/20 p-2 text-center">
                                            <div class="archive-label text-[11px]">Badges</div>
                                            <div class="text-gold">{{ $postCharacter?->badges?->count() ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 border-t border-gold/10 pt-3">
                                    <div class="archive-label mb-2">Honours</div>
                                    @if($postCharacter?->badges && $postCharacter->badges->count())
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($postCharacter->badges->take(4) as $badge)
                                                <span class="rounded-full border border-gold/20 bg-gold/5 px-2 py-1 text-xs text-text-muted" title="{{ $badge->description }}">
                                                    {{ $badge->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-xs text-text-subtle">No honours recorded.</p>
                                    @endif
                                </div>

                                @if($postStats)
                                    <div class="mt-4 border-t border-gold/10 pt-3">
                                        <div class="archive-label mb-2">Attributes</div>
                                        <div class="space-y-2">
                                            @foreach([
                                                ['STR', $postStats->str ?? 10, '#c8a84b'],
                                                ['AGI', $postStats->agi ?? 10, '#7ab0d4'],
                                                ['HP', $postStats->hp ?? 100, '#c05050'],
                                                ['MP', $postStats->mana ?? 50, '#7060b8'],
                                            ] as [$label, $value, $color])
                                                <div class="flex items-center gap-2">
                                                    <span class="thread-meta-label w-6">{{ $label }}</span>
                                                    <span class="h-0.75 flex-1 overflow-hidden rounded-full bg-[#1e1c18]">
                                                        <span class="block h-full rounded-full" style="width:{{ min(100, max(0, (int) $value)) }}%; background:linear-gradient(90deg, {{ $color }}66, {{ $color }}cc); box-shadow:0 0 4px {{ $color }}55"></span>
                                                    </span>
                                                    <span class="w-7 text-right font-display text-[11px] text-gold/60">{{ $value }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </aside>

                            <article class="flex min-h-95 min-w-0 flex-col bg-[linear-gradient(180deg,rgba(20,18,16,.72),rgba(9,8,7,.92))]">
                                <header class="border-b border-gold/10 px-5 py-3 sm:px-6"
                                        style="background-color:{{ $postColor }}08">
                                    <div class="flex flex-wrap items-center gap-2">
                                        {{-- Frame / class badge --}}
                                        <span style="font-family:var(--font-display); font-size:8px; letter-spacing:2px; color:{{ $postColor }}; border:0.5px solid {{ $postColor }}44; padding:2px 8px; flex-shrink:0;">
                                            {{ strtoupper($postCharacter?->custom_frame ?? $postCharacter?->auto_rank ?? 'Wanderer') }}
                                        </span>
                                        <span class="gold-diamond"></span>
                                        <span class="font-display text-[11px] uppercase tracking-wider text-text-subtle">{{ $postRank }}</span>
                                        <span class="gold-diamond"></span>
                                        <span class="font-display text-[11px] uppercase tracking-wider" style="color:{{ $postColor }}88;">{{ $postCity?->name ?? '—' }}</span>
                                        <span class="ml-auto font-display text-[11px] uppercase tracking-wider text-text-subtle">{{ $post->created_at->diffForHumans() }}</span>
                                    </div>
                                </header>

                                <div class="flex-1 p-5 sm:p-6">
                                    <div class="thread-reading prose prose-invert max-w-none">
                                        {!! $post->content !!}
                                    </div>
                                </div>

                                {{-- Post footer: status badge + council actions --}}
                                <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 20px; border-top:0.5px solid #c8a84b15;">
                                    {{-- Status badge --}}
                                    <div>
                                        @if($post->status === 'approved')
                                            {{-- Approved is the normal/default state — hidden to reduce badge noise --}}
                                        @elseif($post->status === 'pending')
                                            <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; color:#c8a84b; border:0.5px solid #c8a84b44; padding:3px 10px;">● PENDING</span>
                                        @else
                                            <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; color:#8B2020; border:0.5px solid #8B202044; padding:3px 10px;">{{ strtoupper($post->status) }}</span>
                                        @endif
                                    </div>

                                    {{-- Council Actions (owner can edit/delete pending; admin can do all) --}}
                                    @if($isAdmin || ($isPostOwner && $isPending))
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        @if($isAdmin)
                                        <span style="font-family:var(--font-display); font-size:8px; letter-spacing:2px; color:#3a3020; margin-right:2px;">COUNCIL</span>
                                        @endif

                                        @if($isAdmin && $isPending)
                                        <form method="POST" action="{{ route('post.approve', $post->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" style="font-family:var(--font-display); font-size:11px; letter-spacing:1px; color:#2d7a3a; border:0.5px solid #2d7a3a55; background:transparent; padding:4px 11px; cursor:pointer;">✓ APPROVE</button>
                                        </form>
                                        @endif

                                        @if($isAdmin || ($isPostOwner && $isPending))
                                        <a href="{{ route('post.edit', $post->id) }}"
                                           style="font-family:var(--font-display); font-size:11px; letter-spacing:1px; color:#c8a84b; border:0.5px solid #c8a84b33; background:transparent; padding:4px 11px; text-decoration:none; display:inline-block;">✎ EDIT</a>
                                        @endif

                                        @if($isAdmin || ($isPostOwner && $isPending))
                                        <form method="POST" action="{{ route('post.destroy', $post->id) }}" style="display:inline;"
                                              onsubmit="return confirm('ลบโพสต์นี้?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" style="font-family:var(--font-display); font-size:11px; letter-spacing:1px; color:#6b3030; border:0.5px solid #6b303044; background:transparent; padding:4px 11px; cursor:pointer;">✕ DELETE</button>
                                        </form>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </article>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="archive-panel-soft p-12 text-center text-text-subtle">
                        ยังไม่มีโพสต์ในกระทู้นี้
                    </div>
                @endforelse
            </div>

            {{-- ── Reply form (only when thread allows posting) ─────── --}}
            @php
                $isDraftEmpty = $thread->status === 'draft' && $posts->isEmpty();

                // Onboarding gate check for level-0 characters
                $viewerStats     = $currentCharacter?->stats;
                $viewerIsLevel0  = $viewerStats && $viewerStats->level === 0;
                $threadCity   = $thread->city;
                $replyBlocked    = $viewerIsLevel0;

                // City write gate check (Level 1+)
                $writeBlocked = $currentCharacter
                    && ! auth()->user()->isAdminGroup()
                    && $threadCity
                    && ! $threadCity->canWrite(auth()->user(), $currentCharacter);

                $fullyBlocked = $replyBlocked || $writeBlocked;
            @endphp

            @if($fullyBlocked && $currentCharacter)
            {{-- Reply blocked: onboarding gate (Level 0) or city write gate (Level 1+) --}}
            <div class="thread-panel p-0">
                <div style="padding:20px 24px; border-bottom:0.5px solid #c8a84b22; background:rgba(200,168,75,.04)">

                @if($writeBlocked && ! $replyBlocked)
                    {{-- City write gate banner --}}
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px">
                        <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2.5px; color:#c8a84b; border:0.5px solid #c8a84b55; padding:3px 10px">ZONE RESTRICTION</span>
                        <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; color:#6b6050">พื้นที่จำกัดการเขียน</span>
                    </div>
                    <p style="color:#c4b898; font-size:14px">
                        พื้นที่นี้กำหนดให้เขียนได้เฉพาะผู้ที่ผ่านเกณฑ์ที่กำหนดเท่านั้น
                        @if($threadCity?->write_min_level > 0)
                            — ต้องเป็น Level {{ $threadCity->write_min_level }} ขึ้นไป
                        @endif
                    </p>
                @else
                    {{-- Onboarding gate banner --}}
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px">
                        <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2.5px; color:#c8a84b; border:0.5px solid #c8a84b55; padding:3px 10px">ONBOARDING</span>
                        <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; color:#6b6050">ระบบนำทางผู้เล่นใหม่</span>
                    </div>
                    <p style="color:#c4b898; font-size:14px; margin-bottom:10px">
                        คุณยังไม่ผ่าน Onboarding — ทำแบบทดสอบ 3 ด่านให้เสร็จก่อนจึงจะเขียนโพสต์ได้
                    </p>
                    <a href="{{ route('onboarding') }}" style="color:#6b6050; font-size:12px; margin-top:10px; font-family:var(--font-display); letter-spacing:1px; display:inline-block">
                        → ไปที่หน้า Onboarding
                    </a>
                @endif
                </div>
                <div style="padding:14px 24px; opacity:.45; pointer-events:none">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center border border-gold/35 text-gold/70">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </span>
                        <span style="font-family:var(--font-display); font-size:11px; letter-spacing:2px; color:#6b6050">REPLY LOCKED</span>
                    </div>
                </div>
            </div>
            @elseif(in_array($thread->status, ['approved', 'pending', 'request_edit', 'draft']))
            @if($isDraftEmpty && auth()->id() === $thread->created_by)
            <div id="first-post-prompt" class="archive-panel-soft p-6 text-center">
                <p class="mb-3 text-sm text-text-muted">กระทู้นี้ยังไม่มีโพสต์แรก</p>
                <button onclick="document.getElementById('first-post-prompt').classList.add('hidden');document.getElementById('reply-form-wrap').classList.remove('hidden')"
                        class="btn-primary">
                    ✍ เริ่มเขียนโพสต์แรก
                </button>
            </div>
            <div id="reply-form-wrap" class="hidden">
            @else
            <div id="reply-form-wrap">
            @endif
            <div class="thread-panel p-0">
                <div class="flex flex-col gap-3 border-b border-gold/10 bg-gold/2.5 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center border border-gold/35 text-gold/70">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="font-decorative text-[0.95rem] tracking-wider text-gold">Continue the Tale</h3>
                            <p class="font-display text-[11px] uppercase tracking-wider text-text-subtle">Write your reply in character</p>
                        </div>
                    </div>
                    <div class="inline-flex items-center gap-2 border border-gold/20 bg-gold/5 px-3 py-1.5 text-xs text-gold/75">
                        <span class="font-display text-[11px] uppercase tracking-wider">Posting as</span>
                        <span class="font-display text-gold">{{ $currentCharacter?->name ?? 'Admin' }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('post.store', $thread->id) }}" id="thread-reply-form">
                    @csrf
                    <input type="hidden" name="content" id="thread-content-input" value="{{ old('content') }}">

                    {{-- Quill toolbar --}}
                    <div>
                        <div id="thread-editor-toolbar" class="ql-toolbar ql-snow p-2">
                            {{-- Font selector --}}
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
                            {{-- Text formatting --}}
                            <span class="ql-formats">
                                <button class="ql-bold" title="ตัวหนา (Ctrl+B)"></button>
                                <button class="ql-italic" title="ตัวเอียง (Ctrl+I)"></button>
                                <button class="ql-underline" title="ขีดเส้นใต้ (Ctrl+U)"></button>
                                <button class="ql-strike" title="ขีดทับ"></button>
                            </span>
                            {{-- Font size / blockquote / code --}}
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
                            {{-- Lists --}}
                            <span class="ql-formats">
                                <button class="ql-list" value="ordered" title="รายการตัวเลข"></button>
                                <button class="ql-list" value="bullet" title="รายการจุด"></button>
                            </span>
                            {{-- Indent --}}
                            <span class="ql-formats">
                                <button class="ql-indent" value="-1" title="ลดย่อหน้า"></button>
                                <button class="ql-indent" value="+1" title="เพิ่มย่อหน้า (Tab)"></button>
                            </span>
                            {{-- Indent right --}}
                            <span class="ql-formats">
                                <button class="ql-indentright" value="-1" title="ลดย่อหน้าทางขวา"><strong>⇤</strong></button>
                                <button class="ql-indentright" value="+1" title="เพิ่มย่อหน้าทางขวา"><strong>⇥</strong></button>
                            </span>
                            {{-- Drop cap --}}
                            <span class="ql-formats">
                                <button class="ql-dropcap" type="button" title="Drop Cap — ตัวอักษรแรกของย่อหน้าตัวใหญ่"><strong>A</strong></button>
                            </span>
                            {{-- Alignment --}}
                            <span class="ql-formats">
                                <button class="ql-align" value="" title="ชิดซ้าย"></button>
                                <button class="ql-align" value="center" title="จัดกึ่งกลาง"></button>
                                <button class="ql-align" value="right" title="ชิดขวา"></button>
                                <button class="ql-align" value="justify" title="จัดเต็มแนว"></button>
                            </span>
                            {{-- Link / image --}}
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
                        <p class="mb-3 text-sm text-red-400">{{ $message }}</p>
                    @enderror

                    @if(is_null($thread->exp_override) && $thread->city->require_approval && ! $thread->event)
                        <div class="mx-5 mb-3 rounded-lg border border-amber-400/30 bg-amber-950/20 px-4 py-3 text-xs text-amber-300">
                            ⚠ โซนนี้ต้องรออนุมัติและกระทู้นี้ไม่ได้ผูกกับ Event — โพสต์นี้จะไม่ได้รับ EXP
                        </div>
                    @endif

                    <div class="flex flex-col gap-3 border-t border-gold/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <span class="text-xs text-text-muted">
                            โพสต์ในฐานะ {{ $currentCharacter?->name ?? 'Admin' }}
                        </span>
                        <div class="flex items-center gap-2">
                            <button type="button" id="thread-preview-toggle"
                                    class="rounded-lg border border-gold/30 px-4 py-2 text-sm text-gold/80 hover:border-gold hover:text-gold">
                                👁 ดูตัวอย่าง
                            </button>
                            <button type="submit" class="btn-primary">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Post Reply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            </div>{{-- reply-form-wrap --}}
            @elseif($thread->status === 'locked')
                <div class="archive-panel-soft border-slate-600/30 p-6 text-center text-sm text-slate-400">
                    🔒 กระทู้นี้ถูกล็อค — ไม่สามารถโพสต์ได้
                </div>
            @elseif($thread->status === 'archived')
                <div class="archive-panel-soft border-indigo-600/30 p-6 text-center text-sm text-indigo-400">
                    📁 กระทู้นี้ถูกเก็บถาวร — อ่านได้อย่างเดียว
                </div>
            @endif

        <x-slot:rail>
            <div class="mt-4 space-y-3">

                {{-- ── Quick Actions (always visible, no collapse) ─────── --}}
                <div class="thread-side-panel is-right">
                    <div class="thread-side-heading">
                        <span>Quick Actions</span>
                    </div>
                    <div class="thread-side-body">
                        <a href="{{ route('thread.create', $thread->city->id) }}" class="right-flat-row">
                            <span>New Thread</span><span>›</span>
                        </a>
                        <a href="{{ route('chronicles.index') }}" class="right-flat-row">
                            <span>World Chronicles</span><span>›</span>
                        </a>
                    </div>
                </div>

                {{-- ── Event & Rewards — collapsible, default open, disclosed
                       up front per vaelthorn-event-thread-ux-plan.md เฟส 3 ──── --}}
                @if($thread->event)
                <div class="thread-side-panel is-right" data-collapsible>
                    <div class="thread-side-heading rail-toggle-btn" onclick="railToggle(this)">
                        <span style="color:{{ $thread->event->type_color }}">
                            {{ $thread->event->type_icon }} {{ $thread->event->type_label }} Event
                        </span>
                        <i class="rail-chevron">▾</i>
                    </div>
                    <div class="rail-body">
                        <div class="thread-side-body" style="padding:0">
                            @if($thread->event->flash_time_remaining_label)
                                <div class="right-flat-row cursor-default opacity-80">
                                    <span>⏳ เวลาที่เหลือ</span>
                                    <span style="color:{{ $thread->event->type_color }}">{{ $thread->event->flash_time_remaining_label }}</span>
                                </div>
                            @endif
                            @forelse($thread->event->rewards as $reward)
                                @php $earned = $earnedRewardIds->contains($reward->id); @endphp
                                <div class="right-notice-row border-b border-gold/8">
                                    <span class="right-notice-pill text-[9px]"
                                          style="background:{{ $earned ? 'rgba(74,222,128,.15)' : 'rgba(200,168,75,.15)' }};color:{{ $earned ? '#4ade80' : '#c8a84b' }}">
                                        {{ $earned ? '✓' : '★' }}
                                    </span>
                                    <span class="right-notice-copy min-w-0 flex-1" style="color:#c4b898">
                                        @if($reward->item && $reward->item_quantity > 0){{ $reward->item->name }} ×{{ $reward->item_quantity }}@endif
                                        @if($reward->gold_amount) · {{ number_format($reward->gold_amount) }} Gold @endif
                                        @if($reward->exp_amount) · {{ $reward->exp_amount }} EXP @endif
                                        @if($reward->note)
                                            <span class="block text-[0.7rem] italic opacity-70">{{ $reward->note }}</span>
                                        @endif
                                        @if($currentCharacter)
                                            <span class="block text-[0.65rem]" style="color:{{ $earned ? '#4ade80' : '#9a8968' }}">
                                                {{ $earned ? 'คุณได้รับแล้ว' : 'จะได้รับอัตโนมัติเมื่อโพสต์ของคุณได้รับอนุมัติ' }}
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            @empty
                                <div class="px-4 py-3 text-[0.78rem] italic" style="color:#6b6050">Event นี้ยังไม่มี reward ที่ตั้งไว้</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endif

                {{-- ── Notices — collapsible, default open ────────────── --}}
                <div class="thread-side-panel is-right" data-collapsible>
                    <div class="thread-side-heading rail-toggle-btn" onclick="railToggle(this)">
                        <span style="color:rgba(245,158,11,.8)">
                            Notices
                            @if($unreadCount > 0)
                                <span class="right-unread-badge ml-1.5 text-[9px]">{{ $unreadCount }}</span>
                            @endif
                        </span>
                        <i class="rail-chevron">▾</i>
                    </div>
                    <div class="rail-body">
                        <div class="thread-side-body" style="padding:0">
                            <div class="right-notice-row border-b border-gold/8">
                                <span class="right-notice-pill" style="background:rgba(90,120,200,.15);color:#7090d0">✦</span>
                                <span class="right-notice-copy" style="color:#7ab0d4">
                                    {{ $thread->approvedPostsCount() }} approved chronicles in this thread
                                </span>
                            </div>
                            @forelse($notices->take(3) as $notice)
                                @php
                                    $nIcon  = match($notice->type) { 'event'=>'⚡','reward'=>'★','post'=>'✦', default=>'!' };
                                    $nColor = match($notice->type) { 'event'=>'#7ab0d4','reward'=>'#c8a84b','post'=>'#a78bfa', default=>'#f59e0b' };
                                    $nBg    = match($notice->type) { 'event'=>'rgba(122,176,212,.15)','reward'=>'rgba(200,168,75,.15)','post'=>'rgba(167,139,250,.15)', default=>'rgba(245,158,11,.15)' };
                                @endphp
                                <div class="right-notice-row border-b border-gold/8 {{ $notice->is_read ? 'opacity-50' : '' }}">
                                    <span class="right-notice-pill" style="background:{{ $nBg }};color:{{ $nColor }}">{{ $nIcon }}</span>
                                    <span class="right-notice-copy" style="color:{{ $nColor }}">
                                        {{ Str::limit($notice->message, 75) }}
                                    </span>
                                </div>
                            @empty
                                <div class="px-4 py-3 text-[0.78rem] italic" style="color:#6b6050">No new notices.</div>
                            @endforelse
                            <a href="{{ route('notifications.index') }}" class="right-view-all">View All Notifications</a>
                        </div>
                    </div>
                </div>

                {{-- ── My Activity — collapsible, default collapsed ─────── --}}
                @if($currentCharacter)
                <div class="thread-side-panel is-right is-collapsed" data-collapsible>
                    <div class="thread-side-heading rail-toggle-btn" onclick="railToggle(this)">
                        <span>My Activity
                            @if($myPosts->count())
                                <span class="ml-1.5 text-[0.7rem]" style="color:rgba(200,168,75,.4)">({{ $myPosts->count() }})</span>
                            @endif
                        </span>
                        <i class="rail-chevron">▾</i>
                    </div>
                    <div class="rail-body is-collapsed">
                        <div class="thread-side-body" style="padding:0">
                            @forelse($myPosts as $mp)
                                @php
                                    $mpColor = $mp->status === 'approved' ? '#4ade80' : '#f59e0b';
                                    $mpLabel = $mp->status === 'approved' ? 'A' : 'P';
                                @endphp
                                <div class="right-notice-row border-b border-gold/8">
                                    <span class="right-notice-pill text-[9px]"
                                          style="background:{{ $mpColor }}18;color:{{ $mpColor }}">{{ $mpLabel }}</span>
                                    <span class="right-notice-copy min-w-0 flex-1 truncate" style="color:#c4b898">
                                        {{ Str::limit(strip_tags($mp->content), 65) }}
                                    </span>
                                </div>
                            @empty
                                <div class="px-4 py-3 text-[0.78rem] italic" style="color:#6b6050">
                                    No posts in this thread yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endif

                {{-- ── World Navigation — collapsible, default collapsed ── --}}
                @php $navCity = $thread->city->kingdom; @endphp
                <div class="thread-side-panel is-right is-collapsed" data-collapsible>
                    <div class="thread-side-heading rail-toggle-btn" onclick="railToggle(this)">
                        <span>World Navigation</span>
                        <i class="rail-chevron">▾</i>
                    </div>
                    <div class="rail-body is-collapsed">
                        <div class="thread-side-body" style="padding:0">
                            <div class="right-flat-row cursor-default opacity-60">
                                <span class="truncate">{{ $thread->city->name }}</span>
                                <span class="archive-label shrink-0">City</span>
                            </div>
                            <a href="{{ route('city', $thread->city->id) }}" class="right-flat-row">
                                <span class="truncate" style="color:{{ $navCity->color ?? '#c8a84b' }}">
                                    {{ $navCity->name ?? '—' }}
                                </span>
                                <span class="archive-label shrink-0">Kingdom</span>
                            </a>
                            <a href="{{ route('home') }}" class="right-flat-row">
                                <span>All Kingdoms</span><span>›</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ── In This Thread — collapsible, default open ──────── --}}
                <div class="thread-side-panel is-right" data-collapsible>
                    <div class="thread-side-heading rail-toggle-btn" onclick="railToggle(this)">
                        <span style="color:rgba(74,222,128,.8)">
                            In This Thread
                            <span class="ml-1 text-[0.7rem]" style="color:rgba(74,222,128,.4)">{{ $participants->count() }}</span>
                        </span>
                        <i class="rail-chevron">▾</i>
                    </div>
                    <div class="rail-body">
                        <div class="thread-side-body" style="padding:0">
                            @forelse($participants->take(6) as $participant)
                                @php $pColor = $participant->kingdom->color ?? '#c8a84b'; @endphp
                                <div class="right-flat-row" style="min-height:46px">
                                    <div class="flex min-w-0 flex-1 items-center gap-2.5">
                                        <div class="right-mini-avatar shrink-0"
                                             style="width:28px;height:28px;border-color:{{ $pColor }}88">
                                            @if($participant->avatar)
                                                <img src="{{ $participant->avatar }}" alt="{{ $participant->name }}"
                                                     class="h-full w-full object-cover">
                                            @else
                                                <span style="color:{{ $pColor }};font-size:11px;font-family:var(--font-display)">
                                                    {{ strtoupper(mb_substr($participant->name, 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('character.show', $participant->id) }}"
                                               class="block truncate text-[0.82rem] transition hover:text-gold"
                                               style="color:#c4b898">
                                                {{ $participant->name }}
                                            </a>
                                            <div class="archive-label">{{ $participant->auto_rank }}</div>
                                        </div>
                                    </div>
                                    <div class="h-1.5 w-1.5 shrink-0 rounded-full" style="background:{{ $pColor }}88"></div>
                                </div>
                            @empty
                                <div class="px-4 py-3 text-[0.78rem] italic" style="color:#6b6050">No participants yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

            <script>
            function railToggle(btn) {
                var panel = btn.closest('[data-collapsible]');
                var body  = panel.querySelector('.rail-body');
                var now   = panel.classList.toggle('is-collapsed');
                body.classList.toggle('is-collapsed', now);
            }
            </script>
        </x-slot:rail>
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
    var DIALOGUE_QUOTE = '"';
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
                var lineStart = range.index - context.offset;
                var lineText = this.quill.getText(lineStart, context.offset);
                if (!isNbspOnly(lineText)) return true;

                // Dialogue shortcut: a *second* Tab pressed right after the
                // first, on a line that is still completely blank otherwise
                // (exactly one indent unit, nothing before or after it),
                // swaps that indent for an opening quote mark instead of
                // adding a second indent unit — a quick way to start a
                // spoken line. Only fires for this exact "fresh empty line,
                // two Tabs, nothing typed in between" gesture; a third Tab
                // (line no longer NBSP-only) falls through normally.
                if (lineText.length === INDENT_UNIT.length) {
                    var lineInfo   = this.quill.getLine(range.index);
                    var lineBlot   = lineInfo[0];
                    var lineLength = lineBlot ? lineBlot.length() - 1 : lineText.length; // -1 excludes trailing "\n"
                    if (lineLength === INDENT_UNIT.length) {
                        this.quill.deleteText(lineStart, INDENT_UNIT.length, Quill.sources.USER);
                        this.quill.insertText(lineStart, DIALOGUE_QUOTE, Quill.sources.USER);
                        this.quill.setSelection(lineStart + DIALOGUE_QUOTE.length, Quill.sources.SILENT);
                        return false;
                    }
                }

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

    const quill = new Quill('#thread-editor', {
        modules: {
            toolbar: {
                container: '#thread-editor-toolbar',
                handlers: { dropcap: dropCapToolbarHandler, link: linkToolbarHandler, image: imageToolbarHandler },
            },
        },
        theme: 'snow',
        placeholder: 'เขียนโพสต์ของคุณที่นี่…',
    });
    installIndentAnywhereBindings(quill);

    const hiddenInput = document.getElementById('thread-content-input');
    const form        = document.getElementById('thread-reply-form');

    if (document.getElementById('reply-slider')) {
        initColorPicker('reply-slider', 'reply-hex', 'reply-preview', quill);
    }

    if (form) {
        form.addEventListener('submit', function () {
            hiddenInput.value = quill.root.innerHTML;
        });
        if (hiddenInput.value) {
            quill.root.innerHTML = hiddenInput.value;
        }
    }

    // ── Preview toggle: show the post exactly as it will render on the thread ──
    const previewToggle = document.getElementById('thread-preview-toggle');
    const previewPane    = document.getElementById('thread-preview');
    const toolbarEl       = document.getElementById('thread-editor-toolbar');
    const editorEl         = document.getElementById('thread-editor');
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

    // ── Admin moderation form toggle ──────────────────────────────────
    window.openModForm = function (action) {
        document.getElementById('mod-action').value = action;
        document.getElementById('mod-msg-wrap').classList.toggle('hidden', !['request_edit','reject'].includes(action));
        document.getElementById('mod-city-wrap').classList.toggle('hidden', action !== 'move');
        document.getElementById('mod-form-area').classList.remove('hidden');
    };

    window.closeModForm = function () {
        document.getElementById('mod-form-area').classList.add('hidden');
    };
});
</script>
@endpush
