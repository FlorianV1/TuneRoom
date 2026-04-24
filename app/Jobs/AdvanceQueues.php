<?php

namespace App\Jobs;

use App\Models\PlaybackState;
use App\Models\QueueItem;
use App\Services\SpotifyService;
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
        $states = PlaybackState::where('status', 'playing')
            ->with(['currentQueueItem', 'room.activeMembers', 'room.host'])
            ->get();

        foreach ($states as $state) {
            if (!$state->currentQueueItem) continue;

            $duration = $state->currentQueueItem->duration_ms;
            $currentPos = $state->currentPositionMs();

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

        // Try fallback playlist if queue is empty
        if (!$next && $state->room->fallback_playlist_url) {
            $next = $this->loadFallbackTracks($state, $spotify);
        }

        $state->update([
            'current_queue_item_id' => $next?->id,
            'status' => $next ? 'playing' : 'stopped',
            'position_ms' => 0,
        ]);

        if (!$next) return;

        foreach ($state->room->activeMembers as $member) {
            if (!$member->hasSpotifyConnected()) continue;
            try {
                $spotify->play($member, $next->spotify_track_id, 0);
            } catch (\Exception $e) {
                // Member's Spotify not available
            }
        }
    }

    private function loadFallbackTracks(PlaybackState $state, SpotifyService $spotify): ?QueueItem
    {
        $host = $state->room->host;
        $tracks = $spotify->getPlaylistTracks($host, $state->room->fallback_playlist_url, 10);

        if (empty($tracks)) return null;

        $position = QueueItem::where('room_id', $state->room_id)->whereNull('played_at')->max('position') ?? -1;
        $firstItem = null;

        foreach ($tracks as $i => $track) {
            $item = QueueItem::create([
                'room_id' => $state->room_id,
                'added_by_user_id' => $host->id,
                'spotify_track_id' => $track['spotify_track_id'],
                'title' => $track['title'],
                'artist' => $track['artist'],
                'album' => $track['album'],
                'cover_url' => $track['cover_url'],
                'duration_ms' => $track['duration_ms'],
                'position' => $position + $i + 1,
            ]);
            if ($i === 0) $firstItem = $item;
        }

        return $firstItem;
    }
}
