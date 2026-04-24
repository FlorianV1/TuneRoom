<div class="h-screen bg-[#0f0d0b] flex flex-col overflow-hidden"
     x-data="{ showAddModal: @entangle('showAddModal'), permDrawer: @entangle('permDrawerUserId') }">

    {{-- ── Top bar ──────────────────────────────────────────────────── --}}
    <header class="flex items-center justify-between px-5 h-14 border-b border-white/[0.08] shrink-0">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-1.5 text-xs text-white/40 hover:text-white/70 transition-colors">
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round">
                    <path d="M12 4l-6 6 6 6"/>
                </svg>
                Rooms
            </a>
            <div class="w-px h-4 bg-white/10"></div>
            <div>
                <span class="text-sm font-bold">{{ $room->name }}</span>
                <span
                    class="ml-2 px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-500/20 text-green-400 uppercase tracking-wider">Live</span>
            </div>
            <span
                class="text-xs text-white/30">Hosted by {{ $room->host->name }} · {{ $members->count() }} listening</span>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.08]">
                <span class="text-[11px] text-white/30 font-medium">Code</span>
                <span class="text-xs font-mono font-bold">{{ $room->code }}</span>
                <button onclick="navigator.clipboard.writeText('{{ $room->code }}')"
                        class="text-white/30 hover:text-white/70 transition-colors">
                    <svg class="w-3 h-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                        <rect x="6" y="6" width="10" height="10" rx="1.5"/>
                        <path d="M4 12V5a1 1 0 011-1h7"/>
                    </svg>
                </button>
            </div>
            <button id="invite-btn" onclick="copyInviteLink(this)"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.08] text-xs font-medium hover:bg-white/[0.08] transition-colors">
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="5" cy="10" r="2"/>
                    <circle cx="15" cy="4" r="2"/>
                    <circle cx="15" cy="16" r="2"/>
                    <path d="M7 9l6-4M7 11l6 4"/>
                </svg>
                <span id="invite-btn-text">Invite</span>
            </button>
        </div>
    </header>

    {{-- ── 3-column body ────────────────────────────────────────────── --}}
    <div class="flex flex-1 min-h-0">

        {{-- ── Queue column ─────────────────────────────────────────── --}}
        <aside class="w-[340px] border-r border-white/[0.08] flex flex-col shrink-0">
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/[0.08]">
                <div>
                    <div class="text-sm font-semibold">Up next</div>
                    <div class="text-[11px] text-white/30 mt-0.5">{{ $queue->count() }} songs</div>
                </div>
                @if($myPerms['add'])
                    <button wire:click="openAddModal"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-400 text-[#1a0a00] text-xs font-bold hover:bg-orange-300 transition-colors">
                        <svg class="w-3 h-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"
                             stroke-linecap="round">
                            <path d="M10 4v12M4 10h12"/>
                        </svg>
                        Add
                    </button>
                @endif
            </div>
            <div class="flex-1 overflow-y-auto py-2" id="queue-list">
                @forelse($queue as $i => $item)
                    <div
                        class="flex items-center gap-2 px-3 py-2.5 {{ $i === 0 ? 'bg-white/[0.04]' : 'hover:bg-white/[0.02] drag-handle cursor-grab active:cursor-grabbing' }} group transition-colors {{ $i > 0 && $myPerms['skip'] ? 'drag-handle' : '' }}"
                        data-item-id="{{ $item->id }}"
                        data-index="{{ $i }}">

                        {{-- Position / playing indicator --}}
                        <div class="w-5 text-center shrink-0">
                            @if($i === 0)
                                <div class="flex items-center justify-center gap-[2px]">
                                    @foreach([1,1.5,0.8,1.3,0.9] as $j => $h)
                                        <div class="w-[2px] bg-orange-400 rounded-sm animate-pulse"
                                             style="height: {{ $h * 8 }}px; animation-delay: {{ $j * 0.1 }}s"></div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-[11px] text-white/20 font-mono">{{ $i + 1 }}</span>
                            @endif
                        </div>

                        {{-- Album art --}}
                        <div
                            class="w-9 h-9 rounded-md shrink-0 bg-gradient-to-br from-orange-500/40 to-purple-600/40 flex items-center justify-center overflow-hidden">
                            @if($item->cover_url)
                                <img src="{{ $item->cover_url }}" class="w-full h-full rounded-md object-cover"/>
                            @else
                                <svg class="w-4 h-4 text-white/20" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                </svg>
                            @endif
                        </div>

                        {{-- Track info --}}
                        <div class="flex-1 min-w-0">
                            <div
                                class="text-xs font-semibold truncate {{ $i === 0 ? 'text-orange-300' : 'text-white' }}">{{ $item->title }}</div>
                            <div class="text-[11px] text-white/40 truncate flex items-center gap-1.5">
                                {{ $item->artist }}
                                <span class="text-white/20">·</span>
                                <div
                                    class="w-3.5 h-3.5 rounded-full bg-white/10 flex items-center justify-center text-[8px] font-bold overflow-hidden">
                                    @if($item->addedBy?->avatar)
                                        <img src="{{ $item->addedBy->avatar }}" class="w-full h-full object-cover"/>
                                    @else
                                        {{ strtoupper(substr($item->addedBy->name ?? '?', 0, 1)) }}
                                    @endif
                                </div>
                                {{ explode(' ', $item->addedBy->name ?? '')[0] }}
                                @if(isset($item->source) && $item->source === 'fallback')
                                    <span
                                        class="px-1 py-0.5 rounded text-[9px] font-medium bg-white/[0.06] text-white/20">fallback</span>
                                @endif
                            </div>
                        </div>

                        {{-- Duration + remove --}}
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="text-[11px] text-white/30 font-mono">{{ $item->durationFormatted() }}</span>
                            @if($myPerms['skip'] && $i > 0)
                                <button wire:click="removeFromQueue({{ $item->id }})"
                                        class="opacity-0 group-hover:opacity-100 text-white/20 hover:text-red-400 transition-all">
                                    <svg class="w-3 h-3" viewBox="0 0 20 20" stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" fill="none">
                                        <path d="M5 5l10 10M15 5L5 15"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full py-16 text-center px-6">
                        <div class="text-3xl mb-3">🎵</div>
                        <div class="text-sm font-semibold mb-1">Queue is empty</div>
                        <div class="text-xs text-white/30">Add songs to get the music going</div>
                    </div>
                @endforelse
            </div>
        </aside>

        {{-- ── Now playing column ───────────────────────────────────── --}}
        <main class="flex-1 flex flex-col items-center justify-between py-8 px-8 min-w-0 relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div
                    class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-orange-500/10 rounded-full blur-[120px]"></div>
            </div>

            {{-- Sync status --}}
            <div class="flex items-center gap-2 text-xs text-white/40 z-10">
                <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>
                All {{ $members->count() }} in sync
                <span class="text-white/20">·</span>
                <span class="font-mono" id="latency-display">±12ms</span>
                @if($state && $state->currentQueueItem)
                    <span class="text-white/20">·</span>
                    Added by {{ explode(' ', $state->currentQueueItem->addedBy->name ?? '')[0] }}
                @endif
            </div>

            {{-- Album art --}}
            <div class="flex flex-col items-center gap-6 z-10">
                <div
                    class="w-[280px] h-[280px] rounded-2xl bg-gradient-to-br from-orange-500/60 to-purple-700/60 flex items-center justify-center shadow-2xl shadow-orange-500/20 relative overflow-hidden">
                    @if($state && $state->currentQueueItem && $state->currentQueueItem->cover_url)
                        <img src="{{ $state->currentQueueItem->cover_url }}"
                             class="w-full h-full object-cover rounded-2xl"/>
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-orange-500/40 to-purple-700/40"></div>
                        <div
                            class="absolute bottom-5 left-5 right-5 font-['Instrument_Serif',serif] italic text-3xl text-white/90 leading-tight">
                            {{ $state?->currentQueueItem?->title ?? 'Nothing playing' }}
                        </div>
                        <div class="absolute top-4 left-4 text-[10px] tracking-[0.2em] text-white/60 uppercase">
                            {{ $state?->currentQueueItem?->artist ?? '' }}
                        </div>
                    @endif
                </div>
                <div class="text-center">
                    <div
                        class="text-2xl font-bold tracking-tight mb-1">{{ $state?->currentQueueItem?->title ?? 'Nothing playing' }}</div>
                    <div class="text-sm text-white/50">
                        {{ $state?->currentQueueItem?->artist ?? '' }}
                        @if($state?->currentQueueItem?->album)
                            <span class="text-white/20">·</span> {{ $state->currentQueueItem->album }}
                        @endif
                    </div>
                </div>
            </div>

            {{-- Controls --}}
            <div class="w-full max-w-md z-10">
                @php
                    $duration = $state?->currentQueueItem?->duration_ms ?? 1;
                    $position = $state?->currentPositionMs() ?? 0;
                    $pct = min(100, ($position / $duration) * 100);
                    $isPlaying = $state?->isPlaying() ?? false;
                @endphp
                <div class="mb-5">
                    <div class="relative h-1 bg-white/10 rounded-full mb-2 cursor-pointer group" id="progress-track">
                        <div id="progress-bar"
                             class="absolute left-0 top-0 bottom-0 bg-orange-400 rounded-full transition-none"
                             style="width: {{ $pct }}%"
                             data-position="{{ $position }}"
                             data-duration="{{ $duration }}"
                             data-server-time="{{ now()->valueOf() }}"
                             data-playing="{{ $isPlaying ? '1' : '0' }}">
                        </div>
                        <div id="progress-thumb"
                             class="absolute top-1/2 -translate-y-1/2 w-3 h-3 rounded-full bg-orange-400 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity"
                             style="left: calc({{ $pct }}% - 6px)">
                        </div>
                    </div>
                    <div class="flex justify-between text-[11px] text-white/30 font-mono">
                        <span id="current-time">{{ gmdate('i:s', ($position / 1000)) }}</span>
                        <span id="total-time">{{ gmdate('i:s', ($duration / 1000)) }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-center gap-4">
                    <div
                        class="flex items-center gap-1 bg-white/[0.05] border border-white/[0.08] rounded-full px-2 py-1.5">
                        @foreach(['🔥', '💜', '🎸', '✨', '🎧', '🙌'] as $emoji)
                            <button
                                class="w-7 h-7 rounded-full hover:bg-white/10 transition-colors text-sm flex items-center justify-center">{{ $emoji }}</button>
                        @endforeach
                    </div>

                    @if($myPerms['skip'])
                        <button
                            class="w-10 h-10 rounded-full bg-white/[0.06] border border-white/[0.08] flex items-center justify-center text-white/50 hover:text-white hover:bg-white/[0.1] transition-all">
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M16 4L6 10l10 6V4z"/>
                                <rect x="4" y="4" width="2" height="12" rx="0.5"/>
                            </svg>
                        </button>
                    @endif

                    @if($myPerms['play'])
                        <button wire:click="togglePlay"
                                class="w-14 h-14 rounded-full bg-white flex items-center justify-center text-[#0f0d0b] hover:bg-white/90 transition-colors shadow-xl">
                            @if($state && $state->isPlaying())
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                    <rect x="5" y="3" width="4" height="14" rx="1"/>
                                    <rect x="11" y="3" width="4" height="14" rx="1"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 translate-x-0.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M5 3l12 7-12 7V3z"/>
                                </svg>
                            @endif
                        </button>
                    @endif

                    @if($myPerms['skip'])
                        <button wire:click="skipNext"
                                class="w-10 h-10 rounded-full bg-white/[0.06] border border-white/[0.08] flex items-center justify-center text-white/50 hover:text-white hover:bg-white/[0.1] transition-all">
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M4 4l10 6-10 6V4z"/>
                                <rect x="14" y="4" width="2" height="12" rx="0.5"/>
                            </svg>
                        </button>
                    @endif

                    <div class="flex items-center gap-2 text-white/30">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6"
                             stroke-linecap="round">
                            <path d="M3 8v4h3l4 3V5L6 8H3z" fill="currentColor"/>
                            <path d="M14 7a4 4 0 010 6"/>
                        </svg>
                        <input type="range" min="0" max="100" value="80" class="w-20 accent-orange-400"/>
                    </div>
                </div>

                @if($queue->count() > 1)
                    <div class="text-center mt-4 text-xs text-white/30">
                        Up next <span class="text-white/50 font-medium">{{ $queue->skip(1)->first()?->title }}</span>
                    </div>
                @endif
            </div>
        </main>

        {{-- ── Members column ──────────────────────────────────────── --}}
        @if($showMembers)
            <aside class="w-[280px] border-l border-white/[0.08] flex flex-col shrink-0">
                <div class="flex items-center justify-between px-5 py-4 border-b border-white/[0.08]">
                    <div>
                        <div class="text-sm font-semibold">In the room</div>
                        <div class="text-[11px] text-white/30 mt-0.5">{{ $members->count() }} of 10</div>
                    </div>
                    <button wire:click="$set('showMembers', false)"
                            class="text-white/20 hover:text-white/60 transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" fill="none">
                            <path d="M5 5l10 10M15 5L5 15"/>
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto">
                    @foreach($members as $member)
                        @php
                            $isYou = $member->id === auth()->id();
                            $memberRecord = \App\Models\RoomMember::where('room_id', $room->id)->where('user_id', $member->id)->first();
                            $role = $memberRecord?->role ?? 'listener';
                        @endphp
                        <button
                            @if($isHost && !$isYou) wire:click="$set('permDrawerUserId', {{ $member->id }})" @endif
                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-white/[0.03] transition-colors text-left {{ $isYou ? 'cursor-default' : 'cursor-pointer' }}">
                            <div
                                class="w-9 h-9 rounded-full bg-orange-500/20 border border-orange-400/20 flex items-center justify-center text-xs font-bold text-orange-300 shrink-0 overflow-hidden">
                                @if($member->avatar)
                                    <img src="{{ $member->avatar }}" class="w-full h-full object-cover"/>
                                @else
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium flex items-center gap-1.5">
                                    {{ explode(' ', $member->name)[0] }}
                                    @if($isYou)
                                        <span class="text-white/30 text-xs font-normal">(you)</span>
                                    @endif
                                    @if($role === 'host')
                                        <span class="text-orange-400">👑</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    @if($role === 'host')
                                        <span
                                            class="text-[10px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded bg-orange-400/15 text-orange-300">Host</span>
                                    @elseif($role === 'cohost')
                                        <span
                                            class="text-[10px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded bg-purple-400/15 text-purple-300">Co-host</span>
                                    @else
                                        <span
                                            class="text-[10px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded bg-white/[0.06] text-white/30">Listener</span>
                                    @endif
                                </div>
                            </div>
                            @if($isHost && !$isYou)
                                <svg class="w-4 h-4 text-white/20 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <circle cx="4" cy="10" r="1.5"/>
                                    <circle cx="10" cy="10" r="1.5"/>
                                    <circle cx="16" cy="10" r="1.5"/>
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
                <div class="p-4 border-t border-white/[0.08] flex flex-col gap-2">
                    <button onclick="copyInviteLink()"
                            class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border border-white/[0.08] text-xs font-medium text-white/50 hover:text-white hover:border-white/[0.16] transition-all">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                             stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="5" cy="10" r="2"/>
                            <circle cx="15" cy="4" r="2"/>
                            <circle cx="15" cy="16" r="2"/>
                            <path d="M7 9l6-4M7 11l6 4"/>
                        </svg>
                        Copy invite link
                    </button>
                    <button wire:click="leaveRoom"
                            class="w-full py-2 text-xs text-white/20 hover:text-red-400 transition-colors">
                        Leave room
                    </button>
                </div>
            </aside>
        @else
            <button wire:click="$set('showMembers', true)"
                    class="absolute right-4 top-20 p-2.5 rounded-xl bg-[#1a1715] border border-white/[0.08] text-white/30 hover:text-white/60 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                    <circle cx="7" cy="7" r="3"/>
                    <path d="M2 17c0-2.8 2.2-5 5-5s5 2.2 5 5"/>
                    <path d="M13 4a3 3 0 010 6M13 12c2.8 0 5 2.2 5 5"/>
                </svg>
            </button>
        @endif
    </div>

    {{-- ── Add songs modal ─────────────────────────────────────────── --}}
    @if($showAddModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-start justify-center pt-20 z-50 px-4"
             wire:click.self="closeAddModal">
            <div class="w-full max-w-lg bg-[#1a1715] border border-white/[0.16] rounded-2xl overflow-hidden shadow-2xl">
                <div class="flex items-center gap-3 px-4 py-3 border-b border-white/[0.08]">
                    @if($searching)
                        <div
                            class="w-4 h-4 border-2 border-orange-400/30 border-t-orange-400 rounded-full animate-spin shrink-0"></div>
                    @else
                        <svg class="w-4 h-4 text-white/30 shrink-0" viewBox="0 0 20 20" fill="none"
                             stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <circle cx="8.5" cy="8.5" r="5"/>
                            <path d="M12.5 12.5L16 16"/>
                        </svg>
                    @endif
                    <input wire:model.live.debounce.400ms="searchQuery" type="text"
                           placeholder="Search Spotify or paste a track link…"
                           class="flex-1 bg-transparent text-sm text-white placeholder-white/20 outline-none"
                           autofocus/>
                    <button wire:click="closeAddModal" class="text-white/20 hover:text-white/60 transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" fill="none">
                            <path d="M5 5l10 10M15 5L5 15"/>
                        </svg>
                    </button>
                </div>
                <div class="max-h-[420px] overflow-y-auto">
                    @if(empty($searchResults) && strlen($searchQuery) < 2)
                        @if(!empty($favoriteTracks))
                            <div class="px-4 pt-3 pb-1">
                                <div class="text-[10px] font-semibold uppercase tracking-widest text-white/30">Your
                                    favorites
                                </div>
                            </div>
                            @foreach($favoriteTracks as $track)
                                <div
                                    class="flex items-center gap-3 px-4 py-3 border-b border-white/[0.06] hover:bg-white/[0.03] transition-colors">
                                    <div class="w-10 h-10 rounded-lg shrink-0 bg-white/10 overflow-hidden">
                                        @if($track['cover_url'])
                                            <img src="{{ $track['cover_url'] }}" class="w-full h-full object-cover"/>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-semibold truncate">{{ $track['title'] }}</div>
                                        <div class="text-xs text-white/40 truncate">{{ $track['artist'] }}
                                            · {{ $track['album'] }}</div>
                                    </div>
                                    <div
                                        class="text-xs text-white/30 font-mono shrink-0">{{ gmdate('i:s', $track['duration_ms'] / 1000) }}</div>
                                    @if(in_array($track['spotify_track_id'], $addedTrackIds))
                                        <div
                                            class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-green-500/10 border border-green-500/20 text-xs font-medium text-green-400 shrink-0">
                                            <svg class="w-3 h-3" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 10l4 4 8-8"/>
                                            </svg>
                                            Added
                                        </div>
                                    @else
                                        <button
                                            wire:click="addTrack('{{ $track['spotify_track_id'] }}', '{{ addslashes($track['title']) }}', '{{ addslashes($track['artist']) }}', '{{ addslashes($track['album']) }}', '{{ $track['cover_url'] }}', {{ $track['duration_ms'] }})"
                                            class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-xs font-medium text-white/60 hover:bg-orange-400 hover:border-orange-400 hover:text-[#1a0a00] transition-all shrink-0">
                                            <svg class="w-3 h-3" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                                 stroke-width="2.5" stroke-linecap="round">
                                                <path d="M10 4v12M4 10h12"/>
                                            </svg>
                                            Add
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="py-12 text-center">
                                <div class="text-2xl mb-2">🎵</div>
                                <div class="text-sm text-white/30">Search for a song, artist or album</div>
                            </div>
                        @endif
                    @elseif(empty($searchResults) && strlen($searchQuery) >= 2)
                        <div class="py-12 text-center text-sm text-white/30">No results found</div>
                    @else
                        @foreach($searchResults as $track)
                            <div
                                class="flex items-center gap-3 px-4 py-3 border-b border-white/[0.06] hover:bg-white/[0.03] transition-colors">
                                <div class="w-10 h-10 rounded-lg shrink-0 bg-white/10 overflow-hidden">
                                    @if($track['cover_url'])
                                        <img src="{{ $track['cover_url'] }}" class="w-full h-full object-cover"/>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold truncate">{{ $track['title'] }}</div>
                                    <div class="text-xs text-white/40 truncate">{{ $track['artist'] }}
                                        · {{ $track['album'] }}</div>
                                </div>
                                <div
                                    class="text-xs text-white/30 font-mono shrink-0">{{ gmdate('i:s', $track['duration_ms'] / 1000) }}</div>
                                @if(in_array($track['spotify_track_id'], $addedTrackIds))
                                    <div
                                        class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-green-500/10 border border-green-500/20 text-xs font-medium text-green-400 shrink-0">
                                        <svg class="w-3 h-3" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 10l4 4 8-8"/>
                                        </svg>
                                        Added
                                    </div>
                                @else
                                    <button
                                        wire:click="addTrack('{{ $track['spotify_track_id'] }}', '{{ addslashes($track['title']) }}', '{{ addslashes($track['artist']) }}', '{{ addslashes($track['album']) }}', '{{ $track['cover_url'] }}', {{ $track['duration_ms'] }})"
                                        class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-xs font-medium text-white/60 hover:bg-orange-400 hover:border-orange-400 hover:text-[#1a0a00] transition-all shrink-0">
                                        <svg class="w-3 h-3" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                             stroke-width="2.5" stroke-linecap="round">
                                            <path d="M10 4v12M4 10h12"/>
                                        </svg>
                                        Add
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="px-4 py-3 bg-[#0f0d0b]/50 flex justify-between text-[11px] text-white/20">
                    <span>Songs added go to the bottom of the queue</span>
                    <span class="font-mono">esc to close</span>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Scripts ──────────────────────────────────────────────────── --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        // ── Copy invite link ──────────────────────────────────────────
        function copyInviteLink(btn) {
            const url = '{{ url()->current() }}';
            const label = document.getElementById('invite-btn-text');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(() => {
                    if (label) {
                        label.textContent = 'Copied!';
                        setTimeout(() => label.textContent = 'Invite', 2000);
                    }
                }).catch(() => fallbackCopy(url, label));
            } else {
                fallbackCopy(url, label);
            }
        }

        function fallbackCopy(text, label) {
            const el = document.createElement('textarea');
            el.value = text;
            el.style.position = 'fixed';
            el.style.opacity = '0';
            document.body.appendChild(el);
            el.focus();
            el.select();
            try {
                document.execCommand('copy');
                if (label) {
                    label.textContent = 'Copied!';
                    setTimeout(() => label.textContent = 'Invite', 2000);
                }
            } catch (e) {
                if (label) label.textContent = 'Failed';
            }
            document.body.removeChild(el);
        }

        // ── Progress bar ─────────────────────────────────────────────
        let progressInterval = null;

        function startProgress() {
            if (progressInterval) clearInterval(progressInterval);
            progressInterval = setInterval(() => {
                const bar = document.getElementById('progress-bar');
                const thumb = document.getElementById('progress-thumb');
                const timeEl = document.getElementById('current-time');
                if (!bar) return;

                const playing = bar.dataset.playing === '1';
                const posMs = parseFloat(bar.dataset.position);
                const durMs = parseFloat(bar.dataset.duration);
                const serverTime = parseFloat(bar.dataset.serverTime);
                if (!durMs) return;

                const elapsedMs = playing ? (Date.now() - serverTime) : 0;
                const currentMs = Math.min(posMs + elapsedMs, durMs);
                const pct = Math.min(100, (currentMs / durMs) * 100);

                bar.style.width = pct + '%';
                if (thumb) thumb.style.left = 'calc(' + pct + '% - 6px)';
                if (timeEl) {
                    const s = Math.floor(currentMs / 1000);
                    timeEl.textContent = Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
                }
            }, 250);
        }

        document.addEventListener('livewire:updated', startProgress);
        document.addEventListener('DOMContentLoaded', startProgress);
        startProgress();

        // ── Drag to reorder queue ─────────────────────────────────────
        function initSortable() {
            const list = document.getElementById('queue-list');
            if (!list) return;
            if (list._sortable) {
                list._sortable.destroy();
            }

            list._sortable = new Sortable(list, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'opacity-30',
                filter: '[data-index="0"]',
                onEnd: function (evt) {
                    const items = [...list.querySelectorAll('[data-item-id]')];
                    const order = items.map(el => parseInt(el.dataset.itemId));
                    @this.
                    reorderQueueItems(order);
                }
            });
        }

        document.addEventListener('livewire:updated', initSortable);
        document.addEventListener('DOMContentLoaded', initSortable);
        initSortable();

        // ── Spotify sync ─────────────────────────────────────────────
        function syncSpotify(data) {
            fetch('{{ route('rooms.sync-playback') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(data),
            })
                .then(r => r.json())
                .then(r => {
                    const el = document.getElementById('latency-display');
                    if (el) el.textContent = '±' + r.latency_ms + 'ms';
                    console.log('Synced, latency:', r.latency_ms + 'ms');
                })
                .catch(console.error);
        }

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('spotify-sync', (data) => {
                syncSpotify(Array.isArray(data) ? data[0] : data);
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Echo === 'undefined') return;
            Echo.join(`room.{{ $room->id }}`)
                .listen('.playback.sync', (data) => {
                    syncSpotify({
                        room_id: {{ $room->id }},
                        status: data.status,
                        track_id: data.track_id,
                        position_ms: data.position_ms,
                        server_time: data.server_time,
                    });
                });
        });
    </script>

</div>
