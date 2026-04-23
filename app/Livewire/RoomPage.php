<?php

namespace App\Livewire;

use App\Models\Room;
use App\Models\QueueItem;
use App\Models\RoomMember;
use App\Services\SpotifyService;
use Illuminate\Support\Facades\Auth;
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
        $state->update([
            'status' => $state->isPlaying() ? 'paused' : 'playing',
            'position_ms' => $state->currentPositionMs(),
        ]);
    }

    public function skipNext()
    {
        $this->checkPermission('skip');
        $this->advanceQueue();
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
        ])->layout('layouts.app');
    }
}
