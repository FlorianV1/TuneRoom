<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Made by Florian — Tuneroom</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body
    class="bg-[#0f0d0b] text-[#f4ece2] font-['Inter_Tight',sans-serif] antialiased min-h-screen flex flex-col items-center justify-center px-6">

{{-- Glow --}}
<div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-orange-500/[0.07] rounded-full blur-[120px]"></div>
</div>

<div class="relative z-10 max-w-lg w-full text-center">

    {{-- Back --}}
    <a href="{{ route('landing') }}"
       class="inline-flex items-center gap-2 text-xs text-white/30 hover:text-white/60 transition-colors mb-12">
        <svg class="w-3 h-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round">
            <path d="M12 4l-6 6 6 6"/>
        </svg>
        Back to Tuneroom
    </a>

    {{-- Avatar --}}
    <div
        class="w-20 h-20 rounded-full bg-gradient-to-br from-orange-400/40 to-orange-600/40 border border-orange-400/20 flex items-center justify-center text-2xl font-bold text-orange-300 mx-auto mb-6">
        FL
    </div>

    {{-- Name --}}
    <h1 class="text-4xl mb-2">
        <span class="font-bold">Made by </span>
        <em class="font-['Instrument_Serif',serif] italic text-orange-400">Florian</em>
    </h1>

    <p class="text-white/40 text-sm leading-relaxed mb-8 max-w-sm mx-auto">
        Built Tuneroom from scratch — a real-time music sync app where small groups listen together down to the
        millisecond.
    </p>

    {{-- Tech stack --}}
    <div class="flex flex-wrap items-center justify-center gap-2 mb-10">
        @foreach(['Laravel', 'Livewire', 'Filament', 'Tailwind', 'Spotify API', 'Reverb'] as $tech)
            <span
                class="px-3 py-1 rounded-full bg-white/[0.05] border border-white/[0.08] text-xs font-medium text-white/50">
                    {{ $tech }}
                </span>
        @endforeach
    </div>

    {{-- Links --}}
    <div class="flex items-center justify-center gap-4">
        {{-- GitHub --}}
        <a href="https://github.com/FlorianV1" target="_blank"
           class="flex items-center gap-2.5 px-5 py-3 rounded-xl bg-[#1a1715] border border-white/[0.08] text-sm font-medium text-white/70 hover:text-white hover:border-white/[0.2] transition-all">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
            </svg>
            GitHub
        </a>

        {{-- Discord --}}
        <a href="https://discord.com/users/officialflorian" target="_blank"
           class="flex items-center gap-2.5 px-5 py-3 rounded-xl bg-[#1a1715] border border-white/[0.08] text-sm font-medium text-white/70 hover:text-white hover:border-indigo-400/40 hover:text-indigo-300 transition-all">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057c.002.022.015.043.033.055a19.978 19.978 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.201 13.201 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
            </svg>
            Discord
        </a>
    </div>
</div>

</body>
</html>
