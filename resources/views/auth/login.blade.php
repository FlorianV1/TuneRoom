<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign in — Tuneroom</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#0f0d0b] text-[#f4ece2] font-['Inter_Tight',sans-serif] antialiased min-h-screen">

<div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

    {{-- ── Left — form ─────────────────────────────────────────────── --}}
    <div class="flex flex-col justify-center px-8 py-12 lg:px-16">

        {{-- Logo --}}
        <a href="{{ route('landing') }}" class="flex items-center gap-2 mb-12">
            <div class="w-7 h-7 rounded-full bg-orange-400 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-[#1a0a00]" viewBox="0 0 20 20" fill="currentColor"><path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/></svg>
            </div>
            <span class="font-extrabold tracking-tight">Tuneroom</span>
        </a>

        <div class="max-w-sm w-full">
            <h1 class="text-4xl mb-2">
                <span class="font-bold">Welcome </span>
                <em class="font-['Instrument_Serif',serif] italic text-orange-400">back.</em>
            </h1>
            <p class="text-white/40 text-sm mb-8">Sign in to rejoin your rooms or start a new one.</p>

            {{-- Session errors --}}
            @if(session('error'))
                <div class="mb-6 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-sm text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Email/password form --}}
            <form method="POST" action="{{ route('login.post') }}" class="space-y-4 mb-6">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-widest text-white/30 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full bg-[#1a1715] border border-white/[0.08] rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-orange-400/50 transition-colors @error('email') border-red-500/50 @enderror"
                           placeholder="you@example.com" />
                    @error('email')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[11px] font-semibold uppercase tracking-widest text-white/30">Password</label>
                        <a href="#" class="text-xs text-orange-400 hover:text-orange-300 transition-colors">Forgot?</a>
                    </div>
                    <input type="password" name="password" required
                           class="w-full bg-[#1a1715] border border-white/[0.08] rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-orange-400/50 transition-colors @error('password') border-red-500/50 @enderror"
                           placeholder="••••••••••••" />
                    @error('password')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full py-3.5 rounded-xl bg-orange-400 text-[#1a0a00] text-sm font-bold hover:bg-orange-300 transition-colors flex items-center justify-center gap-2">
                    Sign in →
                </button>
            </form>

            {{-- Divider --}}
            <div class="flex items-center gap-3 mb-6">
                <div class="flex-1 h-px bg-white/[0.06]"></div>
                <span class="text-xs text-white/20">OR</span>
                <div class="flex-1 h-px bg-white/[0.06]"></div>
            </div>

            {{-- Spotify --}}
            <a href="{{ route('auth.spotify') }}"
               class="w-full flex items-center justify-center gap-3 py-3.5 rounded-xl bg-[#1a1715] border border-white/[0.12] text-sm font-semibold hover:border-white/25 hover:bg-[#221e1b] transition-all">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="#1ed760"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                Continue with Spotify
            </a>

            <p class="text-center text-xs text-white/30 mt-6">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-orange-400 hover:text-orange-300 transition-colors">Create one</a>
            </p>
            <p class="text-center text-[11px] text-white/20 mt-2">
                By continuing you agree to our Terms · Privacy · Community guidelines
            </p>
        </div>
    </div>

    {{-- ── Right — decorative ───────────────────────────────────────── --}}
    <div class="hidden lg:flex flex-col justify-between p-12 bg-[#0a0806] border-l border-white/[0.06] relative overflow-hidden">

        {{-- Glow --}}
        <div class="absolute top-1/3 right-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-[100px] pointer-events-none"></div>

        {{-- Live counter --}}
        <div class="text-right z-10">
            <div class="text-xs text-white/30 font-medium mb-1">Rooms live now</div>
            <div class="text-6xl font-extrabold tracking-tight">
                {{ number_format(\App\Models\Room::where('status', 'active')->count()) }}
            </div>
        </div>

        {{-- Room preview card --}}
        <div class="bg-[#1a1715] border border-white/[0.1] rounded-2xl p-5 z-10">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></div>
                <span class="text-xs font-medium text-white/40 uppercase tracking-wider">Room live</span>
                <span class="ml-auto text-xs font-mono text-white/30">PEACH-0424</span>
            </div>
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-pink-500/70 to-purple-700/70 shrink-0"></div>
                <div>
                    <div class="font-bold">Carmine Hours</div>
                    <div class="text-sm text-white/40">The Slow Paper</div>
                    <div class="flex items-center gap-[2px] mt-1.5">
                        @foreach([0.4,0.9,0.6,1,0.7,0.5,0.8,0.9,0.6,0.4,0.7,1] as $i => $h)
                            <div class="w-[3px] bg-orange-400 rounded-sm" style="height: {{ (int)($h * 16) }}px"></div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex -space-x-1.5">
                    @foreach(['MR','PS','JC','KT'] as $i => $initials)
                        <div class="w-6 h-6 rounded-full border-2 border-[#1a1715] flex items-center justify-center text-[8px] font-bold"
                             style="background: oklch(0.6 0.2 {{ [18,310,165,250][$i] }})">{{ $initials }}</div>
                    @endforeach
                </div>
                <span class="text-xs text-white/40">4 listening in sync</span>
                <span class="text-xs font-mono text-orange-400/60">±12ms</span>
            </div>
        </div>

        {{-- Testimonial --}}
        <div class="z-10">
            <blockquote class="font-['Instrument_Serif',serif] italic text-xl text-white/70 leading-relaxed mb-4">
                "Finally, long-distance movie-night energy but for albums. My book club uses it every Thursday."
            </blockquote>
            <div class="text-sm text-white/30">— Priya S. · Listener since March</div>
        </div>
    </div>

</div>

</body>
</html>
