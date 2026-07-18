@php
    $currentRoute = request()->route()?->getName();
@endphp

<nav class="sticky top-0 z-50 border-b border-gold/15 bg-bg/95 backdrop-blur-md">
    <div class="flex h-16 w-full items-center justify-between px-4 sm:px-5">
        <a href="{{ route('home') }}" class="group flex items-center gap-3">
            {{-- Star emblem box --}}
            <div class="relative flex h-11 w-11 shrink-0 items-center justify-center border border-gold/45 bg-bg-elevated shadow-[inset_0_0_18px_rgba(200,168,75,0.06),0_0_16px_rgba(200,168,75,0.1)] transition-all group-hover:border-gold/70 group-hover:shadow-[inset_0_0_22px_rgba(200,168,75,0.1),0_0_26px_rgba(200,168,75,0.2)]">
                {{-- Corner ornament dots --}}
                <span class="absolute left-0.75 top-0.75 h-0.75 w-0.75 bg-gold/60"></span>
                <span class="absolute right-0.75 top-0.75 h-0.75 w-0.75 bg-gold/60"></span>
                <span class="absolute bottom-0.75 left-0.75 h-0.75 w-0.75 bg-gold/60"></span>
                <span class="absolute bottom-0.75 right-0.75 h-0.75 w-0.75 bg-gold/60"></span>
                {{-- 5-pointed star --}}
                <svg class="h-5.5 w-5.5 text-gold drop-shadow-[0_0_5px_rgba(200,168,75,0.7)]"
                     viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="12,2 14.4,8.8 21.5,8.9 15.8,13.2 17.9,20.1 12,16 6.1,20.1 8.2,13.2 2.5,8.9 9.6,8.8"/>
                </svg>
            </div>
            {{-- Logotype — font specified directly to match Cinzel Decorative exactly --}}
            <span class="leading-none">
                <span class="block text-[13px] font-bold leading-none tracking-widest text-gold"
                      style="font-family:'Cinzel Decorative','Cinzel',serif">VAELTHORN</span>
                <span class="mt-1 block text-[7px] font-bold tracking-[0.45em] text-gold/40"
                      style="font-family:'Cinzel Decorative','Cinzel',serif">CHRONICLES</span>
            </span>
        </a>

        <div class="flex items-center gap-1">
            <a href="{{ route('home') }}"
               class="flex h-10 w-10 items-center justify-center rounded-lg transition-colors {{ $currentRoute === 'home' ? 'bg-border text-gold' : 'text-text-muted hover:text-text' }}"
               title="หน้าแรก">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </a>
            @if(isset($character) && $character?->city?->villages?->first())
                <a href="{{ route('village', $character->currentCity?->villages?->first()?->id ?? $character->city->villages->first()->id) }}"
                   class="flex h-10 w-10 items-center justify-center rounded-lg transition-colors {{ $currentRoute === 'village' ? 'bg-border text-gold' : 'text-text-muted hover:text-gold' }}"
                   title="แผนที่">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </a>
            @endif
            {{-- Recent Activity (3rd icon) --}}
            @if(isset($character) && $character)
            <a href="{{ route('activity.index') }}"
               class="flex h-10 w-10 items-center justify-center rounded-lg transition-colors {{ $currentRoute === 'activity.index' ? 'bg-border text-gold' : 'text-text-muted hover:text-gold' }}"
               title="กระทู้ที่เข้าร่วม">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </a>
            @endif
            <a href="{{ route('events.index') }}"
               class="flex h-10 w-10 items-center justify-center rounded-lg transition-colors {{ str_starts_with($currentRoute ?? '', 'events') ? 'bg-border text-gold' : 'text-text-muted hover:text-gold' }}"
               title="Events">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </a>
            <a href="{{ route('chronicles.index') }}"
               class="flex h-10 w-10 items-center justify-center rounded-lg transition-colors {{ str_starts_with($currentRoute ?? '', 'chronicles') ? 'bg-border text-gold' : 'text-text-muted hover:text-gold' }}"
               title="World Chronicles">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </a>
            @if(isset($character) && $character)
            <a href="{{ route('market.index') }}"
               class="flex h-10 w-10 items-center justify-center rounded-lg transition-colors {{ str_starts_with($currentRoute ?? '', 'market') ? 'bg-border text-gold' : 'text-text-muted hover:text-gold' }}"
               title="Market">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </a>
            <a href="{{ route('rewards.index') }}"
               class="flex h-10 w-10 items-center justify-center rounded-lg transition-colors {{ $currentRoute === 'rewards.index' ? 'bg-border text-gold' : 'text-text-muted hover:text-gold' }}"
               title="Reward History">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </a>
            <a href="{{ route('inventory') }}"
               class="flex h-10 w-10 items-center justify-center rounded-lg transition-colors {{ $currentRoute === 'inventory' ? 'bg-border text-gold' : 'text-text-muted hover:text-gold' }}"
               title="Inventory">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </a>
            <a href="{{ route('notifications.index') }}"
               class="relative flex h-10 w-10 items-center justify-center rounded-lg transition-colors {{ $currentRoute === 'notifications.index' ? 'bg-border text-gold' : 'text-text-muted hover:text-gold' }}"
               title="Notifications">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if(!empty($unreadNotifCount) && $unreadNotifCount > 0)
                    <span class="absolute right-0.5 top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-0.5 font-display text-[0.55rem] font-bold leading-none text-white">
                        {{ $unreadNotifCount > 99 ? '99+' : $unreadNotifCount }}
                    </span>
                @endif
            </a>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @if(isset($character) && $character)
                <a href="{{ route('character.show', $character->id) }}"
                   title="{{ $character->name }}"
                   class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full border-2 bg-bg-elevated text-sm text-text shadow-[0_0_16px_rgba(200,168,75,0.1)] transition hover:ring-1 hover:ring-gold/50"
                   style="border-color: {{ $character->city->color ?? '#D4AF37' }}; background: linear-gradient(135deg, {{ $character->city->color ?? '#7a8c9e' }}aa, {{ $character->city->color ?? '#5a6c7e' }}66);">
                    @if($character->avatar)
                        <img src="{{ $character->avatar }}" alt="{{ $character->name }}" class="h-full w-full object-cover">
                    @else
                        {{ mb_substr($character->name, 0, 1) }}
                    @endif
                </a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex h-10 w-10 items-center justify-center rounded-lg text-text-muted transition-colors hover:text-gold"
                        title="ออกจากระบบ">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</nav>
