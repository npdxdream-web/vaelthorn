<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vaelthorn')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-bg antialiased">
    <div class="flex min-h-screen flex-col">
        <header class="border-b border-gold/15 bg-bg/95 px-6 py-4 backdrop-blur-md">
            <a href="{{ route('home') }}" class="group inline-flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center border border-gold/45 bg-bg-elevated text-gold shadow-[0_0_18px_rgba(200,168,75,0.12)]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m4-10H8a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-8a2 2 0 00-2-2z"/>
                    </svg>
                </span>
                <span>
                    <span class="font-decorative block text-sm font-bold leading-none tracking-[0.22em] text-gold">Vaelthorn</span>
                    <span class="font-display mt-1 block text-[0.5rem] uppercase tracking-[0.36em] text-text-subtle">Chronicles</span>
                </span>
            </a>
        </header>

        <main class="relative z-10 flex flex-1 items-center justify-center px-6 py-12">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
