<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Tuneroom — Listen together, down to the millisecond.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#0f0d0b] text-[#f4ece2] font-['Inter_Tight',sans-serif] antialiased overflow-x-hidden">

{{-- ── Nav ──────────────────────────────────────────────────────── --}}
<nav class="flex items-center justify-between px-8 py-5 max-w-7xl mx-auto">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-full bg-orange-400 flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-[#1a0a00]" viewBox="0 0 20 20" fill="currentColor">
                <path
                    d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
            </svg>
        </div>
        <span class="text-base font-extrabold tracking-tight">Tuneroom</span>
    </div>

    <div class="hidden md:flex items-center gap-8 text-sm text-white/50">
        <a href="#how" class="hover:text-white transition-colors">How it works</a>
        <a href="#features" class="hover:text-white transition-colors">Features</a>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('login') }}"
           class="px-4 py-2 text-sm font-medium text-white/60 hover:text-white transition-colors border border-white/[0.12] rounded-xl hover:border-white/25">
            Sign in
        </a>
        <a href="{{ route('auth.spotify') }}"
           class="px-4 py-2 text-sm font-semibold bg-orange-400 text-[#1a0a00] rounded-xl hover:bg-orange-300 transition-colors">
            Get started
        </a>
    </div>
</nav>

{{-- ── Hero ─────────────────────────────────────────────────────── --}}
<section class="max-w-7xl mx-auto px-8 pt-16 pb-24 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

    {{-- Left --}}
    <div>
        {{-- Live badge --}}
        <div
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-white/[0.1] bg-white/[0.04] text-xs font-medium text-white/60 mb-8">
            <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>
            {{ \App\Models\Room::where('status', 'active')->count() }} rooms live right now
        </div>

        {{-- Headline --}}
        <h1 class="text-5xl lg:text-6xl leading-[1.05] mb-6">
            <em class="font-['Instrument_Serif',serif] not-italic italic text-[#f4ece2]">Listen</em>
            <span class="font-extrabold"> together.</span><br>
            <span class="font-extrabold text-white/30">Down to the</span><br>
            <em class="font-['Instrument_Serif',serif] not-italic italic text-orange-400">millisecond.</em>
        </h1>

        <p class="text-white/50 text-lg leading-relaxed mb-10 max-w-md">
            Small rooms. Shared queue. Host controls who can play, skip, and add.
            Everyone hears the same beat at the same moment — no group chat, no "wait, are you on the chorus yet?"
        </p>

        {{-- CTAs --}}
        <div class="flex flex-wrap items-center gap-4 mb-10">
            <a href="{{ route('auth.spotify') }}"
               class="flex items-center gap-3 px-6 py-3.5 rounded-xl bg-orange-400 text-[#1a0a00] font-semibold text-sm hover:bg-orange-300 transition-colors">
                <div class="w-4 h-4 rounded-full bg-[#1a0a00]/20 flex items-center justify-center">
                    <svg class="w-2.5 h-2.5" viewBox="0 0 20 20" fill="currentColor">
                        <circle cx="10" cy="10" r="9"/>
                    </svg>
                </div>
                Connect Spotify
            </a>
            <a href="{{ route('rooms.join') }}"
               class="px-6 py-3.5 rounded-xl border border-white/[0.12] text-sm font-medium text-white/70 hover:text-white hover:border-white/25 transition-all">
                Join a room
            </a>
        </div>

        {{-- Social proof --}}
        @php $userCount = \App\Models\User::count(); @endphp
        @if($userCount > 0)
            <div class="flex items-center gap-3">
                <div class="flex -space-x-2">
                    @foreach(\App\Models\User::latest()->take(5)->get() as $u)
                        <div
                            class="w-7 h-7 rounded-full bg-orange-500/30 border-2 border-[#0f0d0b] flex items-center justify-center text-[9px] font-bold text-orange-300">
                            {{ strtoupper(substr($u->name, 0, 2)) }}
                        </div>
                    @endforeach
                </div>
                <span class="text-xs text-white/40">
                    {{ $userCount }} {{ Str::plural('person', $userCount) }} joined
                </span>
            </div>
        @endif
    </div>

    {{-- Right — floating room card --}}
    <div class="relative flex items-center justify-center">
        {{-- Glow --}}
        <div class="absolute w-80 h-80 bg-orange-500/15 rounded-full blur-[80px]"></div>

        {{-- Main card --}}
        <div
            class="relative w-full max-w-sm bg-[#1a1715] border border-white/[0.1] rounded-2xl overflow-hidden shadow-2xl">
            {{-- Card header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-white/[0.06]">
                <div class="flex items-center gap-2 text-xs text-white/40">
                    <span class="font-medium">ROOM CODE</span>
                    <span class="font-mono font-bold text-white">PEACH-0424</span>
                </div>
                <button class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/[0.06] text-xs text-white/50">
                    <svg class="w-3 h-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                        <rect x="6" y="6" width="10" height="10" rx="1.5"/>
                        <path d="M4 12V5a1 1 0 011-1h7"/>
                    </svg>
                    Copy
                </button>
            </div>

            {{-- Now playing --}}
            <div class="p-4">
                <div class="flex items-center gap-4 mb-4">
                    {{-- Album art --}}
                    <div
                        class="w-16 h-16 rounded-xl bg-gradient-to-br from-pink-500/70 to-purple-700/70 flex items-center justify-center shrink-0 relative overflow-hidden">
                        <div
                            class="absolute bottom-2 left-2 text-[8px] font-['Instrument_Serif',serif] italic text-white/80 leading-tight">
                            Carmine<br>Hours
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-base mb-0.5">Carmine Hours</div>
                        <div class="text-sm text-white/40">The Slow Paper</div>
                        {{-- Waveform --}}
                        <div class="flex items-center gap-[2px] mt-2">
                            @foreach([0.4,0.9,0.6,1,0.7,0.5,0.8,0.9,0.6,0.4,0.7,1,0.8,0.5,0.6,0.9,0.7,0.4,0.8,0.6,0.9,0.5,0.7,1,0.6] as $i => $h)
                                <div class="w-[3px] bg-orange-400 rounded-sm"
                                     style="height: {{ (int)($h * 20) }}px; animation: pulse {{ 0.6 + ($i % 5) * 0.15 }}s ease-in-out infinite alternate; animation-delay: {{ ($i % 7) * 0.1 }}s"></div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Members --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-1.5">
                            @foreach(['MR','PS','JC','KT'] as $i => $initials)
                                <div
                                    class="w-6 h-6 rounded-full border-2 border-[#1a1715] flex items-center justify-center text-[8px] font-bold"
                                    style="background: oklch(0.6 0.2 {{ [18,310,165,250][$i] }})">
                                    {{ $initials }}
                                </div>
                            @endforeach
                        </div>
                        <span class="text-xs text-white/40">4 listening in sync</span>
                    </div>
                    <span class="text-xs font-mono text-orange-400/60">±12ms</span>
                </div>
            </div>
        </div>

        {{-- Floating vibing card --}}
        <div class="absolute -bottom-4 -left-4 bg-[#1a1715] border border-white/[0.1] rounded-xl p-3 shadow-xl w-48">
            <div class="text-[10px] font-semibold uppercase tracking-widest text-white/30 mb-2">Vibing right now</div>
            @foreach([['MR', 18, 'Marco'], ['PS', 310, 'Priya'], ['JC', 165, 'Jules']] as [$initials, $hue, $name])
                <div class="flex items-center gap-2 mb-1.5">
                    <div class="w-5 h-5 rounded-full shrink-0 flex items-center justify-center text-[8px] font-bold"
                         style="background: oklch(0.6 0.2 {{ $hue }})">{{ $initials }}</div>
                    <span class="text-xs text-white/60">{{ $name }}</span>
                    <div class="flex-1 flex items-center gap-[2px]">
                        @foreach([0.5,0.8,0.4,0.9,0.6] as $h)
                            <div class="flex-1 bg-orange-400/60 rounded-sm"
                                 style="height: {{ (int)($h * 10) }}px"></div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Features ─────────────────────────────────────────────────── --}}
<section id="features" class="max-w-7xl mx-auto px-8 py-24 border-t border-white/[0.06]">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach([
            ['🔄', 'Perfect sync', 'Latency-compensated playback keeps everyone within milliseconds of each other.'],
            ['🔐', 'Granular permissions', 'Set who can play, skip, or add songs — per role or per person.'],
            ['🎵', 'Fallback playlists', 'Queue runs out? We pull from your fallback Spotify playlist automatically.'],
            ['📱', 'Nothing to install', 'Works in any browser. Your friends just need Spotify Premium.'],
        ] as [$emoji, $title, $desc])
            <div class="bg-[#1a1715] border border-white/[0.06] rounded-2xl p-5">
                <div class="text-2xl mb-3">{{ $emoji }}</div>
                <div class="font-semibold text-sm mb-1.5">{{ $title }}</div>
                <div class="text-xs text-white/40 leading-relaxed">{{ $desc }}</div>
            </div>
        @endforeach
    </div>
</section>

{{-- ── Tuneroom vs Spotify Jam ─────────────────────────────────── --}}
<section class="max-w-7xl mx-auto px-8 py-24 border-t border-white/[0.06]">
    <div class="text-center mb-14">
        <h2 class="text-3xl font-extrabold mb-3">Why not just use Spotify Jam?</h2>
        <p class="text-white/40 text-base max-w-xl mx-auto">Jam is great for casual listening. Tuneroom is for when you
            actually want control.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-4xl mx-auto">
        {{-- Spotify Jam --}}
        <div class="bg-[#1a1715] border border-white/[0.06] rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-full bg-[#1ed760]/10 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="#1ed760">
                        <path
                            d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-sm">Spotify Jam</div>
                    <div class="text-xs text-white/30">Built into Spotify</div>
                </div>
            </div>
            <ul class="space-y-3">
                @foreach([
                    [false, 'Anyone can skip — no control'],
                    [false, 'No permission system'],
                    [false, 'No host controls'],
                    [false, 'Locked to Spotify\'s UI'],
                    [false, 'No queue management'],
                    [true,  'Works inside Spotify app'],
                ] as [$good, $text])
                    <li class="flex items-center gap-3 text-sm">
                        <div
                            class="w-4 h-4 rounded-full shrink-0 flex items-center justify-center {{ $good ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400' }}">
                            @if($good)
                                <svg class="w-2.5 h-2.5" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 10l4 4 8-8"/>
                                </svg>
                            @else
                                <svg class="w-2.5 h-2.5" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2.5"
                                     stroke-linecap="round" fill="none">
                                    <path d="M5 5l10 10M15 5L5 15"/>
                                </svg>
                            @endif
                        </div>
                        <span class="{{ $good ? 'text-white/70' : 'text-white/40' }}">{{ $text }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Tuneroom --}}
        <div class="bg-orange-400/[0.06] border border-orange-400/20 rounded-2xl p-6 relative overflow-hidden">
            <div
                class="absolute top-4 right-4 px-2 py-0.5 rounded-full bg-orange-400/15 text-[10px] font-semibold text-orange-300 uppercase tracking-wider">
                Recommended
            </div>
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-full bg-orange-400/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-sm">Tuneroom</div>
                    <div class="text-xs text-white/30">Works in any browser</div>
                </div>
            </div>
            <ul class="space-y-3">
                @foreach([
                    [true,  'Host controls who can skip & add'],
                    [true,  'Per-person permission overrides'],
                    [true,  'Your own queue, independent of Spotify'],
                    [true,  'Share via link — no app needed'],
                    [true,  'Fallback playlist when queue empties'],
                    [true,  'Up to 10 people per room'],
                ] as [$good, $text])
                    <li class="flex items-center gap-3 text-sm">
                        <div
                            class="w-4 h-4 rounded-full shrink-0 bg-orange-400/15 text-orange-400 flex items-center justify-center">
                            <svg class="w-2.5 h-2.5" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 10l4 4 8-8"/>
                            </svg>
                        </div>
                        <span class="text-white/70">{{ $text }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- ── Spotify Premium required ─────────────────────────────────── --}}
<section class="max-w-7xl mx-auto px-8 pb-24">
    <div
        class="bg-[#1a1715] border border-white/[0.06] rounded-2xl p-8 flex flex-col md:flex-row items-start md:items-center gap-6">
        <div
            class="w-12 h-12 rounded-2xl bg-yellow-400/10 border border-yellow-400/20 flex items-center justify-center shrink-0 text-2xl">
            ⚠️
        </div>
        <div class="flex-1">
            <h3 class="font-bold text-base mb-1">Spotify Premium is required</h3>
            <p class="text-white/40 text-sm leading-relaxed">
                Tuneroom controls Spotify playback via Spotify's official API, which only supports Premium accounts.
                Everyone in the room — host and listeners — needs an active Spotify Premium subscription for music to
                play in sync.
                Free Spotify accounts cannot be controlled remotely.
            </p>
        </div>
        <a href="https://www.spotify.com/premium" target="_blank"
           class="shrink-0 px-4 py-2.5 rounded-xl bg-[#1ed760]/10 border border-[#1ed760]/20 text-sm font-medium text-[#1ed760] hover:bg-[#1ed760]/15 transition-colors whitespace-nowrap">
            Get Spotify Premium →
        </a>
    </div>
</section>

{{-- ── Footer CTA ───────────────────────────────────────────────── --}}
<section class="max-w-7xl mx-auto px-8 py-24 text-center border-t border-white/[0.06]">
    <h2 class="text-4xl font-extrabold mb-4">Ready to listen together?</h2>
    <p class="text-white/40 text-lg mb-8">Create a room in seconds. Invite your friends. Hit play.</p>
    <a href="{{ route('auth.spotify') }}"
       class="inline-flex items-center gap-3 px-8 py-4 rounded-xl bg-orange-400 text-[#1a0a00] font-semibold text-base hover:bg-orange-300 transition-colors">
        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
            <circle cx="10" cy="10" r="9"/>
        </svg>
        Get started with Spotify
    </a>
</section>

<style>
    @keyframes pulse {
        from {
            transform: scaleY(0.4);
            opacity: 0.7;
        }
        to {
            transform: scaleY(1);
            opacity: 1;
        }
    }
</style>

</body>
</html>
