<div class="min-h-screen bg-[#0f0d0b]">

    {{-- Nav --}}
    <nav class="sticky top-0 z-50 flex items-center justify-between px-8 h-[60px] border-b border-white/[0.08] bg-[#0f0d0b]">
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-1.5 text-sm text-white/40 hover:text-white/70 transition-colors">
            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M12 4l-6 6 6 6"/>
            </svg>
            Dashboard
        </a>
        <a href="{{ route('rooms.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-orange-400 text-[#1a0a00] text-xs font-bold hover:bg-orange-300 transition-colors">
            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M10 4v12M4 10h12"/>
            </svg>
            Create room
        </a>
    </nav>

    <main class="max-w-5xl mx-auto px-6 py-12">

        <div class="mb-10">
            <h1 class="text-3xl font-extrabold tracking-tight mb-1">Browse rooms</h1>
            <p class="text-white/40 text-sm">Public rooms anyone can jump into.</p>
        </div>

        @if($rooms->isEmpty())
            <div class="text-center py-24 border border-dashed border-white/10 rounded-2xl">
                <div class="text-5xl mb-4">🎵</div>
                <h3 class="text-xl font-bold mb-2">No public rooms right now</h3>
                <p class="text-white/40 text-sm mb-6">Be the first — create a public room and let anyone join.</p>
                <a href="{{ route('rooms.create') }}"
                   class="inline-flex px-5 py-3 rounded-xl bg-orange-400 text-[#1a0a00] text-sm font-semibold hover:bg-orange-300 transition-colors">
                    Create a public room
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($rooms as $room)
                    <a href="{{ route('rooms.show', $room->code) }}"
                       class="block bg-[#26211d] border border-white/[0.08] rounded-2xl p-5 hover:border-white/[0.16] hover:bg-[#2c2620] transition-all group">

                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="text-[15px] font-bold text-[#f4ece2] mb-0.5">{{ $room->name }}</div>
                                <div class="text-[11px] font-medium text-white/30 font-mono">{{ $room->code }}</div>
                            </div>
                            <div class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-green-500/10 border border-green-500/20 text-[10px] font-semibold text-green-400 shrink-0">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>
                                Live
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5 bg-[#1a1715] rounded-xl px-3 py-2.5 mb-4">
                            @if($room->playbackState && $room->playbackState->currentQueueItem)
                                <div class="w-8 h-8 rounded-md shrink-0 bg-white/5 overflow-hidden">
                                    @if($room->playbackState->currentQueueItem->cover_url)
                                        <img src="{{ $room->playbackState->currentQueueItem->cover_url }}" class="w-full h-full object-cover"/>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold truncate">{{ $room->playbackState->currentQueueItem->title }}</div>
                                    <div class="text-[11px] text-white/50 truncate">{{ $room->playbackState->currentQueueItem->artist }}</div>
                                </div>
                            @else
                                <div class="text-xs text-white/30">Nothing playing yet</div>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                @foreach($room->activeMembers->take(5) as $member)
                                    <div class="w-6 h-6 rounded-full bg-orange-500/20 border-2 border-[#26211d] flex items-center justify-center text-[9px] font-semibold text-orange-300 {{ $loop->first ? '' : '-ml-1.5' }} overflow-hidden">
                                        @if($member->avatar)
                                            <img src="{{ $member->avatar }}" class="w-full h-full object-cover"/>
                                        @else
                                            {{ strtoupper(substr($member->name, 0, 2)) }}
                                        @endif
                                    </div>
                                @endforeach
                                @if($room->activeMembers->count() > 5)
                                    <div class="w-6 h-6 rounded-full bg-[#221e1b] border-2 border-[#26211d] flex items-center justify-center text-[9px] font-semibold text-white/40 -ml-1.5">
                                        +{{ $room->activeMembers->count() - 5 }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[11px] text-white/30">{{ $room->activeMembers->count() }} listening</span>
                                <span class="text-[11px] text-white/20">·</span>
                                <span class="text-[11px] text-white/40">by {{ explode(' ', $room->host->name)[0] }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </main>
</div>
