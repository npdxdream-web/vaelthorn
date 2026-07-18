@extends('layouts.app')

@section('title', $village->name . ' — Vaelthorn')

@section('content')
<x-public.shell>
    {{-- ── Left rail: Village Info ─────────────────────────────────────── --}}
    <x-slot:left>
        <div class="sticky top-20">
            <div class="archive-panel p-5">
                <h3 class="font-display mb-4 text-base text-gold">Village Info</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex flex-col gap-0.5">
                        <span class="archive-label">Active Tales</span>
                        <span class="text-text">{{ $threads->where('status', 'approved')->count() }}</span>
                    </div>
                    <div class="border-t border-gold/10 pt-3 flex flex-col gap-0.5">
                        <span class="archive-label">City</span>
                        <span style="color:{{ $village->city->color }}">{{ $village->city->name }}</span>
                    </div>
                    <div class="border-t border-gold/10 pt-3 flex flex-col gap-0.5">
                        <span class="archive-label">Region</span>
                        <span class="text-text">{{ $village->name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:left>

        {{-- ── Main column ──────────────────────────────────────────────────── --}}

            {{-- Breadcrumb + title --}}
            <div class="archive-panel corner-ornaments mb-6 p-6">
                <div class="mb-2 flex items-center gap-2 text-sm text-text-muted">
                    <a href="{{ route('home') }}" class="hover:text-gold">Thiran</a>
                    <span>/</span>
                    <span style="color:{{ $village->city->color }}">{{ $village->city->name }}</span>
                    <span>/</span>
                    <span class="text-text">{{ $village->name }}</span>
                </div>
                <h1 class="font-decorative mb-2 text-3xl tracking-wide text-gold">{{ $village->name }}</h1>
                @if($village->description)
                    <p class="font-chronicle mb-4 text-lg leading-relaxed text-text-muted">{{ $village->description }}</p>
                @endif

                <div class="flex items-center gap-3">
                    @if($canWrite ?? true)
                        <a href="{{ route('thread.create', $village->id) }}"
                           class="btn-primary gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Start New Tale
                        </a>
                    @else
                        <span class="font-display text-xs uppercase tracking-widest text-text-subtle">
                            พื้นที่นี้จำกัดการเขียน
                        </span>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 rounded-lg border border-emerald-800 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-400">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Thread count --}}
            <div class="mb-4">
                <h2 class="text-sm font-medium text-text-muted">Tales ({{ $threads->count() }})</h2>
            </div>

            {{-- Threads list --}}
            <div class="space-y-4">
                @forelse($threads as $thread)
                    @php
                        $isOwner    = auth()->id() === $thread->created_by;
                        $isPublic   = $thread->isPubliclyVisible();
                    @endphp
                    <a href="{{ route('thread', $thread->id) }}"
                       class="archive-panel-soft group block overflow-hidden transition-all
                              {{ $isPublic ? 'border-border hover:border-gold' : 'border-amber-400/20 hover:border-amber-400/50' }}">
                        <div class="p-5">
                            <div class="mb-3 flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="mb-1 flex items-center gap-2 flex-wrap">
                                        <h2 class="font-medium text-text group-hover:text-gold truncate">{{ $thread->title }}</h2>
                                        <span class="shrink-0 rounded-full border px-2 py-0.5 text-xs {{ $thread->status_color }}">
                                            {{ $thread->status_label }}
                                        </span>
                                        @if(! $isPublic && $isOwner)
                                            <span class="shrink-0 text-xs text-text-subtle">(มองเห็นเฉพาะคุณ)</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-text-muted">
                                        <span>{{ $village->name }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 text-sm text-text-muted shrink-0">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $thread->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            @if($thread->status === 'request_edit' && $isOwner)
                                <div class="mb-2 text-xs text-orange-400">⚠ Admin ขอให้แก้ไข — คลิกเพื่อดูรายละเอียด</div>
                            @endif

                            <div class="flex items-center justify-end gap-1 text-sm text-copper">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span>{{ $thread->posts_count }}</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="archive-panel-soft p-12 text-center text-text-subtle">
                        ยังไม่มีกระทู้ในพื้นที่นี้ — <a href="{{ route('thread.create', $village->id) }}" class="text-gold hover:underline">เริ่มกระทู้แรก</a>
                    </div>
                @endforelse
            </div>

</x-public.shell>
@endsection
