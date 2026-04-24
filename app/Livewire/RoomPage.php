<?php

namespace App\Livewire;

use App\Events\PlaybackSync;
use App\Models\Room;
use App\Models\QueueItem;
use App\Models\RoomMember;
use App\Services\SpotifyService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Poll;
use Livewire\Component;

class RoomPage extends Component
{
    public Room $room;
    public bool $showMembers = true;
    public bool $showAddModal = false;
    public ?int $permDrawerUserId = null;
    public string $searchQuery = '';
    public array $searchResults = [];
    public bool $searching = false;
    public array $addedTrackIds = [];

    // Track last synced state to avoid unnecessary Spotify calls
    public ?string $lastSyncedTrackId = null;
    public ?string $lastSyncedStatus = null;

    #[Poll(2000)]
    public function syncPlayback(): void
    {
        $this->room->load('playbackState.currentQueueItem');
        $state = $this->room->playbackState;

        if (!$state) return;

        // Auto-advance if song has ended
        if ($state->isPlaying() && $state->currentQueueItem) {
            $currentPos = $state->currentPositionMs();
            $duration = $state->currentQueueItem->duration_ms;

            if ($currentPos >= $duration - 3000) {
                $this->advanceQueue();
                $fresh = $this->room->playbackState->fresh();
                if ($fresh->currentQueueItem) {
                    $this->dispatch('spotify-sync', [
                        'status' => $fresh->status,
                        'position_ms' => 0,
                        'server_time' => now()->valueOf(),
                        'track_id' => $fresh->currentQueueItem->spotify_track_id,
                        'room_id' => $this->room->id,
                    ]);
                }
                return;
            }
        }

        if (!$state->currentQueueItem) return;

        $trackId = $state->currentQueueItem->spotify_track_id;
        $status = $state->status;

        // Only call Spotify if something changed
        $stateKey = $trackId . '_' . $status;
        if ($stateKey === $this->lastSyncedTrackId . '_' . $this->lastSyncedStatus) return;

        $spotify = app(SpotifyService::class);
        $user = Auth::user();

        if ($status === 'playing') {
            $positionMs = $state->currentPositionMs();
            $spotify->play($user, $trackId, $positionMs);
        } elseif ($status === 'paused') {
            $spotify->pause($user);
        }

        $this->lastSyncedTrackId = $trackId;
        $this->lastSyncedStatus = $status;
    }

    public function mount(string $code)
    {
        $room = Room::where('code', $code)
            ->where('status', 'active')
            ->firstOrFail();

        $member = RoomMember::where('room_id', $room->id)
            ->where('user_id', Auth::id())
            ->whereNull('left_at')
            ->first();

        if (!$member) {
            RoomMember::create([
                'room_id' => $room->id,
                'user_id' => Auth::id(),
                'role' => 'listener',
                'joined_at' => now(),
            ]);
        }

        $this->room = $room;
    }

    public function updatedSearchQuery(string $value)
    {
        if (strlen($value) < 2) {
            $this->searchResults = [];
            return;
        }
        $this->searching = true;
        $this->searchResults = app(SpotifyService::class)->searchTracks(Auth::user(), $value, 8);
        $this->searching = false;
    }

    public function addTrack(string $spotifyTrackId, string $title, string $artist, string $album, string $coverUrl, int $durationMs)
    {
        $this->checkPermission('add');

        $position = QueueItem::where('room_id', $this->room->id)
            ->whereNull('played_at')->max('position') ?? -1;

        QueueItem::create([
            'room_id' => $this->room->id,
            'added_by_user_id' => Auth::id(),
            'spotify_track_id' => $spotifyTrackId,
            'title' => $title,
            'artist' => $artist,
            'album' => $album,
            'cover_url' => $coverUrl,
            'duration_ms' => $durationMs,
            'position' => $position + 1,
        ]);

        $this->addedTrackIds[] = $spotifyTrackId;

        $state = $this->room->playbackState;
        if ($state && $state->isStopped() && !$state->current_queue_item_id) {
            $this->advanceQueue();
            $this->broadcastSync($this->room->playbackState->fresh());
        }
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->addedTrackIds = [];
    }

    public function togglePlay()
    {
        $this->checkPermission('play');
        $state = $this->room->playbackState;
        if (!$state) return;

        $newStatus = $state->isPlaying() ? 'paused' : 'playing';
        $state->update([
            'status' => $newStatus,
            'position_ms' => $state->currentPositionMs(),
        ]);

        $fresh = $state->fresh();
        $this->broadcastSync($fresh);

        // Also dispatch directly to this user's browser
        $this->dispatch('spotify-sync', [
            'status' => $fresh->status,
            'position_ms' => $fresh->position_ms,
            'server_time' => now()->valueOf(),
            'track_id' => $fresh->currentQueueItem?->spotify_track_id,
            'room_id' => $this->room->id,
        ]);
    }

    public function skipNext()
    {
        $this->checkPermission('skip');
        $this->advanceQueue();
        $fresh = $this->room->playbackState->fresh();
        $this->broadcastSync($fresh);

        $this->dispatch('spotify-sync', [
            'status' => $fresh->status,
            'position_ms' => $fresh->position_ms,
            'server_time' => now()->valueOf(),
            'track_id' => $fresh->currentQueueItem?->spotify_track_id,
            'room_id' => $this->room->id,
        ]);
    }

    private function broadcastSync($state): void
    {
        if (!$state) return;
        try {
            broadcast(new PlaybackSync(
                roomId: $this->room->id,
                status: $state->status,
                positionMs: $state->position_ms,
                serverTime: now()->valueOf(),
                trackId: $state->currentQueueItem?->spotify_track_id,
            ));
        } catch (\Exception $e) {
            // Reverb not available — direct sync still works
        }
    }

    public function removeFromQueue(int $itemId)
    {
        $this->checkPermission('skip');
        QueueItem::where('id', $itemId)->where('room_id', $this->room->id)->whereNull('played_at')->delete();
        $this->reorderQueue();
    }

    public function promoteMember(int $userId)
    {
        $this->ensureHost();
        $member = RoomMember::where('room_id', $this->room->id)->where('user_id', $userId)->first();
        if ($member) $member->update(['role' => $member->role === 'listener' ? 'cohost' : 'listener']);
    }

    public function togglePermission(int $userId, string $permission)
    {
        $this->ensureHost();
        $member = RoomMember::where('room_id', $this->room->id)->where('user_id', $userId)->first();
        if ($member) {
            $overrides = $member->permission_overrides ?? [];
            $current = $this->room->permissionsFor($member->user)[$permission] ?? false;
            $overrides[$permission] = !$current;
            $member->update(['permission_overrides' => $overrides]);
        }
    }

    public function removeMember(int $userId)
    {
        $this->ensureHost();
        RoomMember::where('room_id', $this->room->id)->where('user_id', $userId)->update(['left_at' => now()]);
    }

    public function leaveRoom()
    {
        RoomMember::where('room_id', $this->room->id)->where('user_id', Auth::id())->update(['left_at' => now()]);
        return redirect()->route('dashboard');
    }

    private function checkPermission(string $permission): void
    {
        if (!$this->room->userCan(Auth::user(), $permission)) {
            $this->dispatch('notify', message: "You don't have permission to do that.");
        }
    }

    private function ensureHost(): void
    {
        $member = RoomMember::where('room_id', $this->room->id)->where('user_id', Auth::id())->first();
        if (!$member || $member->role !== 'host') abort(403);
    }

    private function advanceQueue(): void
    {
        $state = $this->room->playbackState;
        if (!$state) return;
        if ($state->current_queue_item_id) {
            QueueItem::find($state->current_queue_item_id)?->update(['played_at' => now()]);
        }
        $next = QueueItem::where('room_id', $this->room->id)->whereNull('played_at')->orderBy('position')->first();
        $state->update([
            'current_queue_item_id' => $next?->id,
            'status' => $next ? 'playing' : 'stopped',
            'position_ms' => 0,
        ]);
    }

    private function reorderQueue(): void
    {
        QueueItem::where('room_id', $this->room->id)->whereNull('played_at')->orderBy('position')
            ->get()->each(fn($item, $i) => $item->update(['position' => $i]));
    }

    public function render()
    {
        $this->room->load([
            'playbackState.currentQueueItem.addedBy',
            'activeMembers',
            'upcomingQueue.addedBy',
        ]);

        $myMember = RoomMember::where('room_id', $this->room->id)->where('user_id', Auth::id())->first();

        return view('livewire.room', [
            'room' => $this->room,
            'state' => $this->room->playbackState,
            'queue' => $this->room->upcomingQueue,
            'members' => $this->room->activeMembers,
            'myPerms' => $this->room->permissionsFor(Auth::user()),
            'isHost' => $myMember?->role === 'host',
            'myMember' => $myMember,
        ])->layout('layouts.app', [
            'pageTitle' => $this->room->name . ' — Tuneroom',
            'pageDescription' => 'Join ' . $this->room->name . ' and listen together in perfect sync. Room code: ' . $this->room->code,
        ]);
    }
}
