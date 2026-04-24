<?php

namespace App\Jobs;

use App\Models\PlaybackState;
use App\Models\QueueItem;
use App\Services\SpotifyService;
use App\Models\RoomMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AdvanceQueues implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SpotifyService $spotify): void
    {
        // Get all playing rooms
        $states = PlaybackState::where('status', 'playing')
            ->with(['currentQueueItem', 'room.activeMembers'])
            ->get();

        foreach ($states as $state) {
            if (!$state->currentQueueItem) continue;

            $duration = $state->currentQueueItem->duration_ms;
            $currentPos = $state->currentPositionMs();

            // Song has ended (with 3 second buffer)
            if ($currentPos >= $duration - 3000) {
                $this->advanceRoom($state, $spotify);
            }
        }
    }

    private function advanceRoom(PlaybackState $state, SpotifyService $spotify): void
    {
        // Mark current as played
        QueueItem::find($state->current_queue_item_id)?->update(['played_at' => now()]);

        // Get next unplayed item
        $next = QueueItem::where('room_id', $state->room_id)
            ->whereNull('played_at')
            ->orderBy('position')
            ->first();

        // If no next — try fallback playlist (future feature) or stop
        $state->update([
            'current_queue_item_id' => $next?->id,
            'status' => $next ? 'playing' : 'stopped',
            'position_ms' => 0,
        ]);

        if (!$next) return;

        // Tell every member's Spotify to play the next track
        foreach ($state->room->activeMembers as $member) {
            if (!$member->hasSpotifyConnected()) continue;

            try {
                $spotify->play($member, $next->spotify_track_id, 0);
            } catch (\Exception $e) {
                // Member's Spotify not available — skip
            }
        }
    }
}
