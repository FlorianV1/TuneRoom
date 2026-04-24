<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>{{ $pageTitle ?? config('app.name', 'Tuneroom') }}</title>

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $pageTitle ?? 'Tuneroom — Listen together, down to the millisecond.' }}"/>
    <meta property="og:description"
          content="{{ $pageDescription ?? 'Small rooms. Shared queue. Host controls who can play, skip, and add. Everyone hears the same beat at the same moment.' }}"/>
    <meta property="og:url" content="{{ url()->current() }}"/>
    <meta property="og:type" content="website"/>
    <meta property="og:site_name" content="Tuneroom"/>
    <meta name="twitter:card" content="summary"/>
    <meta name="twitter:title" content="{{ $pageTitle ?? 'Tuneroom — Listen together, down to the millisecond.' }}"/>
    <meta name="twitter:description"
          content="{{ $pageDescription ?? 'Small rooms. Shared queue. Host controls who can play, skip, and add. Everyone hears the same beat at the same moment.' }}"/>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#0f0d0b] text-[#f4ece2] antialiased min-h-screen">
{{ $slot }}
@livewireScripts
</body>
</html>
