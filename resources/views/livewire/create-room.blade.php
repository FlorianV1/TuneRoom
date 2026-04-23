<div class="min-h-screen bg-[#0f0d0b] flex">

    <aside class="w-56 border-r border-white/[0.08] flex flex-col py-6 px-4 shrink-0">
        <div class="text-base font-extrabold tracking-tight mb-8 px-2">Tuneroom</div>

        <nav class="flex flex-col gap-1">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium bg-white/[0.08] text-white">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                    <circle cx="7" cy="7" r="3"/>
                    <path d="M2 17c0-2.8 2.2-5 5-5s5 2.2 5 5"/>
                    <path d="M13 4a3 3 0 010 6M13 12c2.8 0 5 2.2 5 5"/>
                </svg>
                Rooms
            </a>
            <a href="#"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-white/40 hover:text-white/70 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6"
                     stroke-linecap="round">
                    <path d="M3 5h10M3 10h10M3 15h6"/>
                    <path d="M15 12v5l4-2.5z" fill="currentColor"/>
                </svg>
                Saved queues
            </a>
            <a href="#"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-white/40 hover:text-white/70 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 16.5s-6-3.5-6-8A3.5 3.5 0 0110 6a3.5 3.5 0 016 2.5c0 4.5-6 8-6 8z"/>
                </svg>
                Friends
            </a>
            <a href="#"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-white/40 hover:text-white/70 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                    <circle cx="10" cy="10" r="2.5"/>
                    <path
                        d="M10 2v2M10 16v2M18 10h-2M4 10H2M15.5 4.5l-1.5 1.5M6 14l-1.5 1.5M15.5 15.5L14 14M6 6L4.5 4.5"/>
                </svg>
                Settings
            </a>
        </nav>

        <div class="mt-auto">
            <button wire:click="$dispatch('create')"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-orange-400 text-[#1a0a00] text-sm font-semibold hover:bg-orange-300 transition-colors">
                Create room
            </button>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto">
        <div class="px-10 pt-6">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 text-sm text-white/40 hover:text-white/70 transition-colors px-3 py-1.5 rounded-lg border border-white/[0.08] hover:border-white/[0.16]">
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 4l-6 6 6 6"/>
                </svg>
                Back to rooms
            </a>
        </div>

        <div class="max-w-3xl px-10 py-8">

            <h1 class="text-4xl font-['Instrument_Serif',serif] font-normal mb-1">New room</h1>
            <p class="text-white/40 text-sm mb-10">You'll be the host. Invite up to 9 others. Permissions can be changed
                on the fly.</p>

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
                <p class="text-xs text-white/40 mb-3">When the queue empties, we'll pull from this. Optional but
                    recommended.</p>
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
                        <div class="text-[11px] font-semibold uppercase tracking-widest text-white/30 text-center">Play
                            / Pause
                        </div>
                        <div class="text-[11px] font-semibold uppercase tracking-widest text-white/30 text-center">Skip
                            song
                        </div>
                        <div class="text-[11px] font-semibold uppercase tracking-widest text-white/30 text-center">Add
                            to queue
                        </div>
                    </div>

                    <div class="grid grid-cols-4 px-5 py-4 border-b border-white/[0.06] items-center">
                        <div class="flex items-center gap-2 text-sm font-medium">
                            <span>👑</span> Host (you)
                        </div>
                        <div class="flex justify-center">
                            <x-toggle :checked="true" disabled/>
                        </div>
                        <div class="flex justify-center">
                            <x-toggle :checked="true" disabled/>
                        </div>
                        <div class="flex justify-center">
                            <x-toggle :checked="true" disabled/>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 px-5 py-4 border-b border-white/[0.06] items-center">
                        <div class="text-sm font-medium text-white/80">Co-host</div>
                        <div class="flex justify-center">
                            <x-toggle wire:model="cohost_play"/>
                        </div>
                        <div class="flex justify-center">
                            <x-toggle wire:model="cohost_skip"/>
                        </div>
                        <div class="flex justify-center">
                            <x-toggle wire:model="cohost_add"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 px-5 py-4 items-center">
                        <div class="text-sm font-medium text-white/80">Listener</div>
                        <div class="flex justify-center">
                            <x-toggle wire:model="listener_play"/>
                        </div>
                        <div class="flex justify-center">
                            <x-toggle wire:model="listener_skip"/>
                        </div>
                        <div class="flex justify-center">
                            <x-toggle wire:model="listener_add"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-10">
                <label class="block text-sm font-semibold mb-4">Visibility</label>
                <div class="grid grid-cols-3 gap-3">
                    <button type="button" wire:click="$set('visibility', 'invite')"
                            class="text-left p-4 rounded-xl border transition-all {{ $visibility === 'invite' ? 'border-orange-400/50 bg-orange-400/10' : 'border-white/[0.08] bg-[#1a1715] hover:border-white/[0.16]' }}">
                        <div
                            class="text-sm font-semibold mb-0.5 {{ $visibility === 'invite' ? 'text-orange-300' : '' }}">
                            Invite only
                        </div>
                        <div class="text-xs text-white/40">Share the code or link</div>
                    </button>
                    <button type="button" wire:click="$set('visibility', 'friends')"
                            class="text-left p-4 rounded-xl border transition-all {{ $visibility === 'friends' ? 'border-orange-400/50 bg-orange-400/10' : 'border-white/[0.08] bg-[#1a1715] hover:border-white/[0.16]' }}">
                        <div
                            class="text-sm font-semibold mb-0.5 {{ $visibility === 'friends' ? 'text-orange-300' : '' }}">
                            Friends can find
                        </div>
                        <div class="text-xs text-white/40">Shows in their feed</div>
                    </button>
                    <button type="button" wire:click="$set('visibility', 'public')"
                            class="text-left p-4 rounded-xl border transition-all {{ $visibility === 'public' ? 'border-orange-400/50 bg-orange-400/10' : 'border-white/[0.08] bg-[#1a1715] hover:border-white/[0.16]' }}">
                        <div
                            class="text-sm font-semibold mb-0.5 {{ $visibility === 'public' ? 'text-orange-300' : '' }}">
                            Public
                        </div>
                        <div class="text-xs text-white/40">Discoverable by anyone</div>
                    </button>
                </div>
            </div>

            <button wire:click="create" wire:loading.attr="disabled"
                    class="w-full py-3.5 rounded-xl bg-orange-400 text-[#1a0a00] text-sm font-bold hover:bg-orange-300 transition-colors disabled:opacity-50">
                <span wire:loading.remove>Create room →</span>
                <span wire:loading>Creating...</span>
            </button>

        </div>
    </main>
</div>
