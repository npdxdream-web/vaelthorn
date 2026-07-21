<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vaelthorn')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Cinzel+Decorative:wght@400;700;900&family=EB+Garamond:ital,wght@0,400;0,500;1,400&family=Crimson+Text:ital,wght@0,400;1,400&family=Trirong:wght@700;800&family=Noto+Serif+Thai:wght@400;600&display=swap" rel="stylesheet">
    @isset($viteAssets)
        @vite($viteAssets)
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endisset
    @stack('head')
</head>
<body class="min-h-screen bg-bg antialiased">
    @include('partials.navbar')

    <main class="relative z-10 flex-1">
        @yield('content')
    </main>

    @hasSection('hide_footer')
    @else
        @include('partials.footer')
    @endif

    @stack('scripts')
</body>
</html>
