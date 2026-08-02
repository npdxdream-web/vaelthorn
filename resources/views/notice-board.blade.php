@extends('layouts.app')

@section('title', $noticeBoard->name . ' — Vaelthorn')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    <div class="mb-4">
        <a href="{{ route('home') }}"
           class="inline-flex items-center gap-2 font-display text-xs uppercase tracking-widest text-text-subtle hover:text-gold">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            กลับหน้าแรก
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-800 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="archive-panel corner-ornaments mb-6 p-8">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full border-2 bg-bg-elevated/90 text-2xl"
                     style="border-color: {{ $noticeBoard->color }}">
                    {{ $noticeBoard->icon ?? '◆' }}
                </div>
                <h1 class="font-decorative text-3xl" style="color: {{ $noticeBoard->color }}">{{ $noticeBoard->name }}</h1>
            </div>

            @if(auth()->user()->isAtLeastAdmin())
                <a href="{{ route('notice-board.thread.create', $noticeBoard->id) }}" class="btn-primary gap-2 shrink-0">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    สร้างกระทู้
                </a>
            @endif
        </div>

        @if($noticeBoard->description)
            <p class="font-chronicle text-lg leading-relaxed text-text-muted">{{ $noticeBoard->description }}</p>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($threads as $thread)
            <a href="{{ route('notice-board.thread.show', $thread->id) }}"
               class="archive-panel-soft group block p-5 transition hover:border-gold">
                <div class="mb-1 flex items-start justify-between gap-4">
                    <h2 class="font-medium text-text group-hover:text-gold">{{ $thread->title }}</h2>
                    <span class="shrink-0 text-xs text-text-subtle">{{ $thread->created_at->diffForHumans() }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-text-muted">
                    <span>{{ $thread->creator->name ?? '—' }}</span>
                    <span>•</span>
                    <span>{{ $thread->posts_count }} คำตอบ</span>
                </div>
            </a>
        @empty
            <div class="archive-panel-soft p-12 text-center text-text-subtle">
                ยังไม่มีกระทู้ในป้ายประกาศนี้
            </div>
        @endforelse
    </div>

</x-public.shell>
@endsection
