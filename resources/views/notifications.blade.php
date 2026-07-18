@extends('layouts.app')

@section('title', 'การแจ้งเตือน')

@section('content')
<x-public.shell :character-status="$currentCharacter">

    {{-- ── Left rail ──────────────────────────────────────────────────────────── --}}
    <x-slot:left>
        <div class="sticky top-20 space-y-4">

            {{-- Filter panel --}}
            <div class="archive-panel p-5">
                <p class="archive-label mb-3">Filter</p>
                <div class="space-y-0.5">
                    @php
                        $filters = [
                            'all'         => ['◈', 'ทั้งหมด'],
                            'post'        => ['✦', 'โพสต์ & กระทู้'],
                            'event'       => ['⚡', 'Events'],
                            'reward'      => ['★', 'รางวัล'],
                            'progression' => ['▲', 'Progression'],
                            'system'      => ['⚙', 'ระบบ'],
                        ];
                    @endphp
                    @foreach($filters as $key => [$icon, $label])
                        <a href="{{ route('notifications.index') }}{{ $key !== 'all' ? '?filter='.$key : '' }}"
                           class="flex items-center gap-2 rounded px-2 py-1.5 text-sm transition
                                  {{ $filter === $key ? 'bg-gold/8 text-gold' : 'text-text-muted hover:text-gold hover:bg-gold/5' }}">
                            <span class="w-4 text-center opacity-70">{{ $icon }}</span>
                            <span>{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Mark all read --}}
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit"
                            class="w-full rounded border border-gold/30 bg-gold/5 px-4 py-2 font-display text-xs uppercase tracking-wider text-gold/80 transition hover:bg-gold/10 hover:text-gold">
                        Mark All Read
                    </button>
                </form>
            @endif

        </div>
    </x-slot:left>

    {{-- ── Main ──────────────────────────────────────────────────────────────── --}}

    {{-- Header --}}
    <div class="archive-panel corner-ornaments mb-6 p-6">
        <p class="archive-label mb-1">System</p>
        <h1 class="font-decorative mb-2 text-3xl text-gold">การแจ้งเตือน</h1>
        <p class="font-chronicle text-lg text-text-muted">
            @if($unreadCount > 0)
                มี <span class="text-gold font-semibold">{{ $unreadCount }}</span> รายการที่ยังไม่ได้อ่าน
            @else
                อ่านทั้งหมดแล้ว
            @endif
        </p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Notification list --}}
    @if($notifications->isEmpty())
        <div class="archive-panel-soft p-16 text-center">
            <div class="mb-3 text-4xl text-gold/20">◈</div>
            <p class="font-display text-lg text-gold/40">ไม่มีการแจ้งเตือน</p>
            <p class="mt-2 text-sm text-text-subtle">การแจ้งเตือนจาก Events, โพสต์ และระบบจะปรากฏที่นี่</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($notifications as $notif)
                @php
                    $typeConfig = [
                        'post_approved'       => ['✓', '#6abf88', 'โพสต์'],
                        'post_rejected'       => ['✗', '#e05555', 'โพสต์'],
                        'item_received'       => ['★', '#c8a84b', 'รางวัล'],
                        'event_ending_soon'   => ['⚡', '#e07855', 'Event'],
                        'event_started'       => ['⚡', '#7ab0d4', 'Event'],
                        'thread_reply'        => ['✦', '#a78bfa', 'กระทู้'],
                        'thread_locked'       => ['◼', '#9ca3af', 'กระทู้'],
                        'level_up'            => ['▲', '#c8a84b', 'เลเวล'],
                        'badge_awarded'       => ['◆', '#f59e0b', 'Badge'],
                        'system_announcement' => ['⚙', '#9ca3af', 'ระบบ'],
                    ];
                    [$icon, $color, $typeLabel] = $typeConfig[$notif->type] ?? ['◈', '#9ca3af', 'แจ้งเตือน'];
                    $isUnread = $notif->read_at === null;
                    $openUrl  = $notif->url ? route('notifications.open', $notif->id) : null;
                @endphp

                <div class="archive-panel-soft group relative overflow-hidden transition duration-150
                            {{ $isUnread ? '' : 'opacity-55 hover:opacity-80' }} hover:border-gold/20">

                    {{-- Unread accent bar --}}
                    @if($isUnread)
                        <div class="absolute left-0 top-0 h-full w-0.5 transition-opacity"
                             style="background: linear-gradient(to bottom, {{ $color }}, {{ $color }}88)"></div>
                    @endif

                    <div class="flex items-start gap-4 p-4 pl-5">

                        {{-- Type icon --}}
                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-base font-bold"
                             style="background: color-mix(in srgb, {{ $color }} 12%, transparent); color: {{ $color }}">
                            {{ $icon }}
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">
                            {{-- Meta row --}}
                            <div class="mb-1.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="archive-label text-[0.6rem]" style="color: {{ $color }}">
                                    {{ $typeLabel }}
                                </span>
                                <span class="text-xs text-text-subtle">
                                    {{ $notif->created_at->diffForHumans() }}
                                </span>
                                @if($isUnread)
                                    <span class="ml-auto rounded-full bg-gold/15 px-2 py-0.5 font-display text-[0.55rem] uppercase tracking-widest text-gold">
                                        ใหม่
                                    </span>
                                @endif
                            </div>

                            {{-- Title --}}
                            <p class="font-display text-sm font-semibold leading-snug text-text-primary
                                       {{ $openUrl ? 'group-hover:text-gold transition-colors' : '' }}">
                                {{ $notif->title }}
                            </p>

                            {{-- Body --}}
                            @if($notif->body)
                                <p class="mt-0.5 font-chronicle text-sm leading-relaxed text-text-muted">
                                    {{ $notif->body }}
                                </p>
                            @endif
                        </div>

                        {{-- Action button --}}
                        <div class="flex shrink-0 flex-col items-end gap-1.5">
                            @if($openUrl)
                                <a href="{{ $openUrl }}"
                                   class="inline-flex items-center gap-1 rounded border border-gold/25 px-3 py-1.5
                                          font-display text-[0.6rem] uppercase tracking-wider text-gold/70
                                          transition hover:border-gold/50 hover:bg-gold/8 hover:text-gold">
                                    ดู
                                    <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @elseif($isUnread)
                                <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="rounded border border-gold/15 px-2.5 py-1.5 font-display
                                                   text-[0.55rem] uppercase tracking-wider text-gold/45
                                                   transition hover:border-gold/35 hover:text-gold/70">
                                        อ่านแล้ว
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif

</x-public.shell>
@endsection
