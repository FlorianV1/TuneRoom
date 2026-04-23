<div class="min-h-screen bg-[#0f0d0b]">

    {{-- Nav --}}
    <nav class="sticky top-0 z-50 flex items-center justify-between px-8 h-[60px] border-b border-white/[0.08] bg-[#0f0d0b]">
        <span class="text-lg font-extrabold tracking-tight">Tuneroom</span>

        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-orange-500/20 border border-orange-400/40 flex items-center justify-center text-xs font-semibold text-orange-300">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <span class="text-sm text-white/60">{{ $user->name }}</span>

            @if($user->hasSpotifyConnected())
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-500/10 border border-green-500/30 text-[11px] font-medium text-green-400">
                    <div class="w-1.5 h-1.5 rounded-full bg-green-400"></div>
                    Spotify
                </div>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-white/30 hover:text-white/60 transition-colors px-2 py-1">
                    Sign out
                </button>
            </form>
        </div>
    </nav>

    {{-- Main --}}
    <main class="max-w-4xl mx-auto px-6 py-12">

        {{-- Header --}}
        <div class="mb-10">
            <h1 class="text-3xl font-extrabold tracking-tight mb-1">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }},
                {{ explode(' ', $user->name)[0] }} 👋
            </h1>
            <p class="text-white/50 text-sm">Ready to listen together?</p>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 mb-12">
            <a href="{{ route('rooms.create') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-orange-400 text-[#1a0a00] text-sm font-semibold hover:bg-orange-300 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M10 4v12M4 10h12"/>
                </svg>
                Create room
            </a>
            <a href="{{ route('rooms.join') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#1a1715] border border-white/[0.16] text-sm font-medium hover:bg-[#221e1b] transition-colors">
                Join a room
            </a>
        </div>

        {{-- Your rooms --}}
        @if($hostedRooms->count() > 0)
            <section class="mb-10">
                <h2 class="text-[11px] font-semibold uppercase tracking-widest text-white/30 mb-4">Your rooms</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($hostedRooms as $room)
                        <x-room-card :room="$room" :is-host="true" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Joined rooms --}}
        @if($joinedRooms->count() > 0)
            <section class="mb-10">
                <h2 class="text-[11px] font-semibold uppercase tracking-widest text-white/30 mb-4">Joined rooms</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($joinedRooms as $room)
                        <x-room-card :room="$room" :is-host="false" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Empty state --}}
        @if($hostedRooms->isEmpty() && $joinedRooms->isEmpty())
            <div class="text-center py-24 border border-dashed border-white/10 rounded-2xl">
                <div class="text-5xl mb-4">🎵</div>
                <h3 class="text-xl font-bold mb-2">No rooms yet</h3>
                <p class="text-white/40 text-sm mb-6">Create a room and invite friends to listen together in sync.</p>
                <a href="{{ route('rooms.create') }}"
                   class="inline-flex px-5 py-3 rounded-xl bg-orange-400 text-[#1a0a00] text-sm font-semibold hover:bg-orange-300 transition-colors">
                    Create your first room
                </a>
            </div>
        @endif

    </main>
</div>
