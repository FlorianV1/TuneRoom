<div class="min-h-screen bg-[#0f0d0b] flex items-center justify-center px-4">

    {{-- Ambient glow --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-orange-500/[0.06] rounded-full blur-[120px]"></div>
    </div>

    {{-- Back button --}}
    <a href="{{ route('dashboard') }}"
       class="absolute top-6 left-6 flex items-center gap-2 text-sm text-white/40 hover:text-white/70 transition-colors px-3 py-1.5 rounded-lg border border-white/[0.08] hover:border-white/[0.16]">
        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4l-6 6 6 6"/></svg>
        Back
    </a>

    {{-- Card --}}
    <div class="w-full max-w-md bg-[#1a1715] border border-white/[0.08] rounded-2xl p-8 z-10">

        {{-- Title --}}
        <h1 class="text-4xl mb-1">
            <span class="font-['Inter_Tight',sans-serif] font-bold">Join a </span>
            <span class="font-['Instrument_Serif',serif] italic text-orange-400">room</span>
        </h1>
        <p class="text-sm text-white/40 mb-8">Paste the invite link or type the room code.</p>

        {{-- Code input --}}
        <div class="mb-4">
            <label class="block text-[11px] font-semibold uppercase tracking-widest text-white/30 mb-3">Room code</label>
            <input
                wire:model.live="code"
                type="text"
                placeholder="e.g. PEACH-0424"
                maxlength="60"
                class="w-full bg-[#0f0d0b] border border-white/[0.08] rounded-xl px-4 py-4 text-center text-xl font-mono font-bold tracking-widest uppercase text-white placeholder-white/10 focus:outline-none focus:border-orange-400/50 transition-colors"
            />
            @if($error)
                <p class="text-red-400 text-xs mt-2 text-center">{{ $error }}</p>
            @endif
        </div>

        {{-- Room preview --}}
        @if($foundRoom)
            <div class="bg-[#0f0d0b] border border-white/[0.08] rounded-xl p-4 mb-5 flex items-center gap-4">
                {{-- Album art / placeholder --}}
                <div class="w-12 h-12 rounded-xl shrink-0 bg-gradient-to-br from-orange-500/40 to-purple-700/40 flex items-center justify-center overflow-hidden">
                    @if($foundRoom->playbackState?->currentQueueItem?->cover_url)
                        <img src="{{ $foundRoom->playbackState->currentQueueItem->cover_url }}" class="w-full h-full object-cover" />
                    @else
                        <svg class="w-5 h-5 text-white/20" viewBox="0 0 20 20" fill="currentColor"><path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/></svg>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold truncate">{{ $foundRoom->name }}</div>
                    <div class="text-xs text-white/40 flex items-center gap-1.5 mt-0.5">
                        <div class="w-4 h-4 rounded-full bg-orange-500/20 flex items-center justify-center text-[8px] font-bold text-orange-300">
                            {{ strtoupper(substr($foundRoom->host->name, 0, 1)) }}
                        </div>
                        Hosted by {{ explode(' ', $foundRoom->host->name)[0] }}
                        <span class="text-white/20">·</span>
                        {{ $foundRoom->activeMembers->count() }} listening
                    </div>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>
                    <span class="text-xs font-medium text-green-400">Live</span>
                </div>
            </div>

            {{-- Join button --}}
            <button wire:click="join"
                    class="w-full py-3.5 rounded-xl bg-orange-400 text-[#1a0a00] text-sm font-bold hover:bg-orange-300 transition-colors flex items-center justify-center gap-2">
                Join as Listener →
            </button>
        @else
            {{-- Disabled join button --}}
            <button disabled
                    class="w-full py-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white/20 text-sm font-bold cursor-not-allowed">
                Join as Listener →
            </button>
        @endif

        {{-- Divider --}}
        <div class="flex items-center gap-3 my-5">
            <div class="flex-1 h-px bg-white/[0.06]"></div>
            <span class="text-xs text-white/20">OR</span>
            <div class="flex-1 h-px bg-white/[0.06]"></div>
        </div>

        {{-- Create room --}}
        <a href="{{ route('rooms.create') }}"
           class="w-full py-3 rounded-xl bg-[#0f0d0b] border border-white/[0.08] text-sm font-medium text-white/50 hover:text-white hover:border-white/[0.16] transition-all flex items-center justify-center gap-2">
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10 4v12M4 10h12"/></svg>
            Start your own room
        </a>

    </div>
</div>
