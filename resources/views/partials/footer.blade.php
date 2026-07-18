<footer class="relative z-10 mt-24 border-t border-gold/15 bg-[linear-gradient(180deg,#0a0908_0%,#070605_100%)]">
    <div class="flex items-center justify-center py-6">
        <div class="h-px w-24 bg-gradient-to-r from-transparent to-gold/45"></div>
        <svg class="mx-4 h-4 w-4 text-gold/60" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
        </svg>
        <div class="h-px w-24 bg-gradient-to-l from-transparent to-gold/45"></div>
    </div>

    <div class="mx-auto max-w-[1560px] px-4 pb-12 sm:px-6">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-3">
            <div class="space-y-4">
                <h2 class="font-decorative text-2xl tracking-widest text-gold">Vaelthorn</h2>
                <p class="font-chronicle text-base leading-relaxed text-text-subtle">
                    A living chronicle of the world of Thiran — where every story leaves its mark upon the age.
                </p>
                <p class="text-xs italic text-[#4a4846]">
                    "The age does not forget those who dare to write themselves into it."
                </p>
            </div>

            <div class="space-y-4">
                <h3 class="archive-label">Explore</h3>
                <nav class="flex flex-col gap-2">
                    <a href="{{ route('home') }}" class="text-sm text-text-subtle transition-colors hover:text-gold">World Map</a>
                    @auth
                        @if(isset($character) && $character?->city?->villages?->first())
                            <a href="{{ route('village', $character->city->villages->first()->id) }}" class="text-sm text-text-subtle transition-colors hover:text-gold">Villages & Forums</a>
                        @endif
                    @endauth
                    <a href="{{ route('register') }}" class="text-sm text-text-subtle transition-colors hover:text-gold">Join the Chronicle</a>
                </nav>
            </div>

            <div class="space-y-4">
                <h3 class="archive-label">The World of Thiran</h3>
                <ul class="space-y-2 text-sm text-text-subtle">
                    @foreach(['Aurantia — The Golden Dominion', 'Kalif — The Sandstone Sovereignty', 'Viente', 'Akancia', 'Kingsbridge'] as $place)
                        <li class="flex items-center gap-2 before:block before:h-px before:w-3 before:bg-gold/30">{{ $place }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-12 flex flex-col items-center gap-2 border-t border-gold/10 pt-6 text-center md:flex-row md:justify-between">
            <p class="text-xs text-[#3a3836]">&copy; {{ date('Y') }} Vaelthorn. All chronicles reserved.</p>
            <div class="flex gap-4 text-xs text-[#3a3836]">
                <span>Lore Guidelines</span>
                <span>Community Accord</span>
                <span>Contact the Council</span>
            </div>
        </div>
    </div>
</footer>
