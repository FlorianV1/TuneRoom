@props(['room', 'isHost' => false])

<a href="{{ route('rooms.show', $room->code) }}"
   class="block no-underline bg-[#26211d] border border-white/[0.08] rounded-2xl p-5 hover:border-white/[0.16] hover:bg-[#2c2620] transition-all group">

    {{-- Header --}}
    <div class="flex items-start justify-between mb-4">
        <div>
            <div class="text-[15px] font-bold text-[#f4ece2] mb-0.5">{{ $room->name }}</div>
            <div class="text-[11px] font-medium text-white/30 font-mono">{{ $room->code }}</div>
        </div>
        @if($isHost)
            <span class="px-2 py-0.5 rounded-full bg-orange-400/15 border border-orange-400/30 text-[10px] font-semibold text-orange-300">
                HOST
            </span>
        @endif
    </div>

    {{-- Now playing --}}
    <div class="flex items-center gap-2.5 bg-[#1a1715] rounded-xl px-3 py-2.5 mb-4">
        @if($room->playbackState && $room->playbackState->currentQueueItem)
            {{-- Waveform bars --}}
            <div class="flex items-center gap-[2px] shrink-0">
                @foreach([60, 100, 70, 90, 50] as $i => $h)
                    <div class="w-[3px] bg-orange-400 rounded-sm animate-pulse" style="height: {{ $h/10 }}px; animation-delay: {{ $i * 0.1 }}s"></div>
                @endforeach
            </div>
            <div class="min-w-0">
                <div class="text-xs font-semibold truncate">{{ $room->playbackState->currentQueueItem->title }}</div>
                <div class="text-[11px] text-white/50 truncate">{{ $room->playbackState->currentQueueItem->artist }}</div>
            </div>
        @else
            <div class="text-xs text-white/30">Nothing playing</div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            @foreach($room->activeMembers->take(5) as $member)
                <div class="w-6 h-6 rounded-full bg-orange-500/20 border-2 border-[#26211d] flex items-center justify-center text-[9px] font-semibold text-orange-300 {{ $loop->first ? '' : '-ml-1.5' }}">
                    {{ strtoupper(substr($member->name, 0, 2)) }}
                </div>
            @endforeach
            @if($room->activeMembers->count() > 5)
                <div class="w-6 h-6 rounded-full bg-[#221e1b] border-2 border-[#26211d] flex items-center justify-center text-[9px] font-semibold text-white/40 -ml-1.5">
                    +{{ $room->activeMembers->count() - 5 }}
                </div>
            @endif
        </div>
        <span class="text-[11px] text-white/30">
            {{ $room->activeMembers->count() }} {{ Str::plural('member', $room->activeMembers->count()) }}
        </span>
    </div>
</a>
