<div class="min-h-screen bg-[#0f0d0b] overflow-y-auto">

    <div class="max-w-3xl mx-auto px-10 pt-6">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-2 text-sm text-white/40 hover:text-white/70 transition-colors px-3 py-1.5 rounded-lg border border-white/[0.08] hover:border-white/[0.16]">
            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 4l-6 6 6 6"/>
            </svg>
            Back to rooms
        </a>
    </div>

    <div class="max-w-3xl mx-auto px-10 py-8">

        <h1 class="text-4xl font-['Instrument_Serif',serif] font-normal mb-1">New room</h1>
        <p class="text-white/40 text-sm mb-10">You'll be the host. Invite up to 9 others. Permissions can be changed on the fly.</p>

        <div class="mb-8">
            <label class="block text-sm font-semibold mb-1">Room name</label>
            <p class="text-xs text-white/40 mb-3">Shows up in the shareable link.</p>
            <input
                wire:model="name"
                type="text"
                placeholder="e.g. Sunday Slow Burn"
                class="w-full bg-[#1a1715] border border-white/[0.08] rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-orange-400/50 transition-colors"
            />
            @error('name')
                <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-8">
            <label class="block text-sm font-semibold mb-1">Fallback playlist</label>
            <p class="text-xs text-white/40 mb-3">When the queue empties, we'll pull from this. Optional but recommended.</p>
            <div class="relative">
                <div class="absolute left-4 top-1/2 -translate-y-1/2">
                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="#1ed760">
                        <circle cx="10" cy="10" r="9"/>
                    </svg>
                </div>
                <input
                    wire:model="fallback_playlist_url"
                    type="text"
                    placeholder="Paste a Spotify playlist link..."
                    class="w-full bg-[#1a1715] border border-white/[0.08] rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-orange-400/50 transition-colors font-mono"
                />
            </div>
            @error('fallback_playlist_url')
                <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-8">
            <label class="block text-sm font-semibold mb-1">Who can do what</label>
            <p class="text-xs text-white/40 mb-4">Set defaults now — you can override per person later.</p>

            <div class="bg-[#1a1715] border border-white/[0.08] rounded-2xl overflow-hidden">
                <div class="grid grid-cols-4 px-5 py-3 border-b border-white/[0.06]">
                    <div class="text-[11px] font-semibold uppercase tracking-widest text-white/30">Role</div>
                    <div class="text-[11px] font-semibold uppercase tracking-widest text-white/30 text-center">Play / Pause</div>
                    <div class="text-[11px] font-semibold uppercase tracking-widest text-white/30 text-center">Skip song</div>
                    <div class="text-[11px] font-semibold uppercase tracking-widest text-white/30 text-center">Add to queue</div>
                </div>

                <div class="grid grid-cols-4 px-5 py-4 border-b border-white/[0.06] items-center">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <span>👑</span> Host (you)
                    </div>
                    <div class="flex justify-center"><x-toggle :checked="true" disabled/></div>
                    <div class="flex justify-center"><x-toggle :checked="true" disabled/></div>
                    <div class="flex justify-center"><x-toggle :checked="true" disabled/></div>
                </div>

                <div class="grid grid-cols-4 px-5 py-4 border-b border-white/[0.06] items-center">
                    <div class="text-sm font-medium text-white/80">Co-host</div>
                    <div class="flex justify-center"><x-toggle wire:model="cohost_play" :checked="$cohost_play"/></div>
                    <div class="flex justify-center"><x-toggle wire:model="cohost_skip" :checked="$cohost_skip"/></div>
                    <div class="flex justify-center"><x-toggle wire:model="cohost_add" :checked="$cohost_add"/></div>
                </div>

                <div class="grid grid-cols-4 px-5 py-4 items-center">
                    <div class="text-sm font-medium text-white/80">Listener</div>
                    <div class="flex justify-center"><x-toggle wire:model="listener_play" :checked="$listener_play"/></div>
                    <div class="flex justify-center"><x-toggle wire:model="listener_skip" :checked="$listener_skip"/></div>
                    <div class="flex justify-center"><x-toggle wire:model="listener_add" :checked="$listener_add"/></div>
                </div>
            </div>
        </div>

        <button wire:click="create" wire:loading.attr="disabled"
                class="w-full py-3.5 rounded-xl bg-orange-400 text-[#1a0a00] text-sm font-bold hover:bg-orange-300 transition-colors disabled:opacity-50">
            <span wire:loading.remove>Create room →</span>
            <span wire:loading>Creating...</span>
        </button>

    </div>
</div>
