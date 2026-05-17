<?php

namespace App\Livewire;

use App\Events\PlaybackSync;
use App\Events\QueueUpdated;
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
    public bool $showSettings = false;
    public string $settingsName = '';
    public string $settingsFallbackUrl = '';
    public ?int $permDrawerUserId = null;
    public string $searchQuery = '';
    public array $searchResults = [];
    public bool $searching = false;
    public array $addedTrackIds = [];
    public array $favoriteTracks = [];

    // Track last synced state to avoid unnecessary Spotify calls
    public ?string $lastSyncedTrackId = null;
    public ?string $lastSyncedStatus = null;
    public bool $noDevice = false;

    #[Poll(2000)]
    public function syncPlayback()
    {
        $this->room->refresh();

        // Room was ended by host or auto-closed
        if ($this->room->status === 'ended') {
            return redirect()->route('dashboard')->with('info', 'This room has ended.');
        }

        // Current user was removed by the host
        $myMembership = RoomMember::where('room_id', $this->room->id)->where('user_id', Auth::id())->first();
        if (!$myMembership || $myMembership->left_at !== null) {
            return redirect()->route('dashboard')->with('info', 'You were removed from this room.');
        }
        $this->room->load('playbackState.currentQueueItem');
        $state = $this->room->playbackState?->fresh(); // fresh() ensures timestamps are current
        $state?->load('currentQueueItem');

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
            $result = $spotify->play($user, $trackId, $positionMs);
            $this->noDevice = ($result === 'no_device');
        } elseif ($status === 'paused') {
            $spotify->pause($user);
            $this->noDevice = false;
        }

        $this->lastSyncedTrackId = $trackId;
        $this->lastSyncedStatus = $status;
    }

    public function mount(string $code)
    {
        $room = Room::where('code', $code)
            ->where('status', 'active')
            ->firstOrFail();

        // Check if user is already in a different active room — auto-leave it
        $existingMembership = RoomMember::join('rooms', 'rooms.id', '=', 'room_members.room_id')
            ->where('rooms.status', 'active')
            ->where('room_members.user_id', Auth::id())
            ->whereNull('room_members.left_at')
            ->where('room_members.room_id', '!=', $room->id)
            ->select('room_members.*')
            ->first();

        if ($existingMembership) {
            $existingMembership->update(['left_at' => now()]);
        }

        $member = RoomMember::where('room_id', $room->id)
            ->where('user_id', Auth::id())
            ->whereNull('left_at')
            ->first();

        if (!$member) {
            // Check room isn't full
            $memberCount = RoomMember::where('room_id', $room->id)
                ->whereNull('left_at')
                ->count();

            if ($memberCount >= 10) {
                session()->flash('error', 'This room is full (10/10).');
                redirect()->route('dashboard');
                return;
            }

            RoomMember::create([
                'room_id' => $room->id,
                'user_id' => Auth::id(),
                'role' => 'listener',
                'joined_at' => now(),
            ]);
        }

        $this->room = $room;
        $this->settingsName = $room->name;
        $this->settingsFallbackUrl = $room->fallback_playlist_url ?? '';
        $this->maybeLoadFallback();

        // Preload favorites in background so modal opens instantly
        $this->preloadFavorites();
    }

    private function preloadFavorites(): void
    {
        try {
            $spotify = app(SpotifyService::class);
            $user = Auth::user();
            $recent = $spotify->recentlyPlayed($user, 6);
            $top = $spotify->topTracks($user, 6);
            $this->favoriteTracks = collect(array_merge($recent, $top))
                ->unique('spotify_track_id')
                ->take(8)
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            $this->favoriteTracks = [];
        }
    }

    private function maybeLoadFallback(): void
    {
        if (!$this->room->fallback_playlist_url) return;

        $hasQueue = QueueItem::where('room_id', $this->room->id)
            ->whereNull('played_at')
            ->exists();

        if ($hasQueue) return;

        $state = $this->room->playbackState;
        if (!$state || $state->isPlaying()) return;

        $next = $this->loadFallbackTracks();

        if ($next) {
            $state->update([
                'current_queue_item_id' => $next->id,
                'status' => 'stopped',
                'position_ms' => 0,
            ]);

            // Fill remaining slots with fallback songs
            $this->topUpFallback();
        }
    }

    public function openAddModal(): void
    {
        $this->showAddModal = true;

        // Reload favorites if empty (e.g. failed on mount)
        if (empty($this->favoriteTracks)) {
            $this->preloadFavorites();
        }
    }

    public function updatedSearchQuery(string $value)
    {
        if (strlen($value) < 2) {
            $this->searchResults = [];
            $this->searching = false;
            return;
        }
        $this->searching = true;
    }

    public function search(): void
    {
        if (strlen($this->searchQuery) < 2) return;
        $this->searchResults = app(SpotifyService::class)->searchTracks(Auth::user(), $this->searchQuery, 8);
        $this->searching = false;
    }

    public function addTrack(string $spotifyTrackId, string $title, string $artist, string $album, string $coverUrl, int $durationMs)
    {
        $this->checkPermission('add');

        // Insert before fallback songs so user songs always have priority
        $firstFallback = QueueItem::where('room_id', $this->room->id)
            ->whereNull('played_at')
            ->where('source', 'fallback')
            ->orderBy('position')
            ->first();

        if ($firstFallback) {
            // Shift all fallback songs down by 1
            QueueItem::where('room_id', $this->room->id)
                ->whereNull('played_at')
                ->where('source', 'fallback')
                ->increment('position');
            $position = $firstFallback->position - 1;
        } else {
            $position = QueueItem::where('room_id', $this->room->id)
                ->whereNull('played_at')
                ->max('position') ?? -1;
            $position++;
        }

        QueueItem::create([
            'room_id' => $this->room->id,
            'added_by_user_id' => Auth::id(),
            'spotify_track_id' => $spotifyTrackId,
            'title' => $title,
            'artist' => $artist,
            'album' => $album,
            'cover_url' => $coverUrl,
            'duration_ms' => $durationMs,
            'position' => $position,
            'source' => 'user',
        ]);

        $this->addedTrackIds[] = $spotifyTrackId;
        $this->dispatch('notify', message: 'Added to queue');

        try {
            broadcast(new QueueUpdated(
                room_id: $this->room->id,
                added_by_name: Auth::user()->name,
                track_title: $title,
            ))->toOthers();
        } catch (\Exception) {}

        $state = $this->room->playbackState;
        if ($state && $state->isStopped() && !$state->current_queue_item_id) {
            $this->advanceQueue();
            $this->broadcastSync($this->room->playbackState->fresh());
        }

        // Top up fallback to always keep 10 songs ready
        $this->topUpFallback();
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->addedTrackIds = [];
        $this->favoriteTracks = [];
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
            'started_at' => $newStatus === 'playing' ? now() : $state->started_at,
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

    public function playFromQueue(int $itemId): void
    {
        $this->checkPermission('skip');
        $state = $this->room->playbackState;
        if (!$state) return;

        $item = QueueItem::where('room_id', $this->room->id)->whereNull('played_at')->find($itemId);
        if (!$item) return;

        // Mark everything before the clicked item as played (including currently playing)
        QueueItem::where('room_id', $this->room->id)
            ->whereNull('played_at')
            ->where('position', '<', $item->position)
            ->update(['played_at' => now()]);

        $state->update([
            'current_queue_item_id' => $item->id,
            'status' => 'playing',
            'position_ms' => 0,
            'started_at' => now(),
        ]);

        $fresh = $state->fresh();
        $this->broadcastSync($fresh);
        $this->dispatch('spotify-sync', [
            'status' => 'playing',
            'position_ms' => 0,
            'server_time' => now()->valueOf(),
            'track_id' => $item->spotify_track_id,
            'room_id' => $this->room->id,
        ]);
    }

    public function playPrevious(): void
    {
        $this->checkPermission('skip');
        $state = $this->room->playbackState;
        if (!$state) return;

        $prev = QueueItem::where('room_id', $this->room->id)
            ->whereNotNull('played_at')
            ->orderByDesc('played_at')
            ->first();

        if (!$prev) return;

        $prev->update(['played_at' => null, 'position' => -1]);
        $this->reorderQueue();

        $state->update([
            'current_queue_item_id' => $prev->id,
            'status' => 'playing',
            'position_ms' => 0,
            'started_at' => now(),
        ]);

        $fresh = $state->fresh();
        $this->broadcastSync($fresh);
        $this->dispatch('spotify-sync', [
            'status' => 'playing',
            'position_ms' => 0,
            'server_time' => now()->valueOf(),
            'track_id' => $prev->spotify_track_id,
            'room_id' => $this->room->id,
        ]);
    }

    public function setVolume(int $volume): void
    {
        app(SpotifyService::class)->setVolume(Auth::user(), $volume);
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
        $this->permDrawerUserId = null;
    }

    public function saveSettings(): void
    {
        $this->ensureHost();
        $this->validate([
            'settingsName' => 'required|string|min:2|max:60',
            'settingsFallbackUrl' => 'nullable|url',
        ]);

        $this->room->update([
            'name' => $this->settingsName,
            'fallback_playlist_url' => $this->settingsFallbackUrl ?: null,
        ]);

        $this->showSettings = false;
        $this->dispatch('notify', message: 'Room settings saved.');
    }

    public function endRoom()
    {
        $this->ensureHost();

        $this->room->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        // Pause playback state
        $this->room->playbackState?->update(['status' => 'stopped']);

        // Broadcast to all members that room ended
        try {
            broadcast(new PlaybackSync(
                roomId: $this->room->id,
                status: 'stopped',
                positionMs: 0,
                serverTime: now()->valueOf(),
                trackId: null,
            ));
        } catch (\Exception) {}

        return redirect()->route('dashboard')->with('success', 'Room ended.');
    }

    public function leaveRoom()
    {
        $myMember = RoomMember::where('room_id', $this->room->id)->where('user_id', Auth::id())->first();

        if ($myMember?->role === 'host') {
            $next = RoomMember::where('room_id', $this->room->id)
                ->where('user_id', '!=', Auth::id())
                ->whereNull('left_at')
                ->whereIn('role', ['cohost', 'listener'])
                ->orderByRaw("CASE role WHEN 'cohost' THEN 0 WHEN 'listener' THEN 1 END")
                ->orderBy('joined_at')
                ->first();

            if ($next) {
                $next->update(['role' => 'host']);
                $this->room->update(['host_user_id' => $next->user_id]);
            } else {
                $this->room->update(['status' => 'ended', 'ended_at' => now()]);
                $this->room->playbackState?->update(['status' => 'stopped']);
            }
        }

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

        $next = QueueItem::where('room_id', $this->room->id)
            ->whereNull('played_at')
            ->orderBy('position')
            ->first();

        // Queue is empty — try fallback playlist
        if (!$next && $this->room->fallback_playlist_url) {
            $next = $this->loadFallbackTracks();
        }

        $state->update([
            'current_queue_item_id' => $next?->id,
            'status' => $next ? 'playing' : 'stopped',
            'position_ms' => 0,
            'started_at' => $next ? now() : null,
        ]);

        // Keep queue topped up to 10 songs
        $this->topUpFallback();
    }

    private function topUpFallback(): void
    {
        if (!$this->room->fallback_playlist_url) return;

        $total = QueueItem::where('room_id', $this->room->id)->whereNull('played_at')->count();
        if ($total >= 10) return;

        $needed = 10 - $total;
        $spotify = app(SpotifyService::class);
        $tracks = $spotify->getPlaylistTracks($this->room->host, $this->room->fallback_playlist_url, $needed + 5);
        if (empty($tracks)) return;

        $position = QueueItem::where('room_id', $this->room->id)->whereNull('played_at')->max('position') ?? -1;
        $added = 0;

        foreach ($tracks as $track) {
            if ($added >= $needed) break;
            QueueItem::create([
                'room_id' => $this->room->id,
                'added_by_user_id' => $this->room->host_user_id,
                'spotify_track_id' => $track['spotify_track_id'],
                'title' => $track['title'],
                'artist' => $track['artist'],
                'album' => $track['album'],
                'cover_url' => $track['cover_url'],
                'duration_ms' => $track['duration_ms'],
                'position' => ++$position,
                'source' => 'fallback',
            ]);
            $added++;
        }
    }

    private function loadFallbackTracks(): ?QueueItem
    {
        $spotify = app(SpotifyService::class);
        $host = $this->room->host;
        $tracks = $spotify->getPlaylistTracks($host, $this->room->fallback_playlist_url, 10);

        if (empty($tracks)) return null;

        $position = QueueItem::where('room_id', $this->room->id)->whereNull('played_at')->max('position') ?? -1;
        $firstItem = null;

        foreach ($tracks as $i => $track) {
            $item = QueueItem::create([
                'room_id' => $this->room->id,
                'added_by_user_id' => $host->id,
                'spotify_track_id' => $track['spotify_track_id'],
                'title' => $track['title'],
                'artist' => $track['artist'],
                'album' => $track['album'],
                'cover_url' => $track['cover_url'],
                'duration_ms' => $track['duration_ms'],
                'position' => $position + $i + 1,
                'source' => 'fallback',
            ]);
            if ($i === 0) $firstItem = $item;
        }

        return $firstItem;
    }

    private function reorderQueue(): void
    {
        QueueItem::where('room_id', $this->room->id)->whereNull('played_at')->orderBy('position')
            ->get()->each(fn($item, $i) => $item->update(['position' => $i]));
    }

    public function reorderQueueItems(array $ids): void
    {
        $this->checkPermission('skip');

        foreach ($ids as $position => $id) {
            QueueItem::where('id', $id)
                ->where('room_id', $this->room->id)
                ->whereNull('played_at')
                ->update(['position' => $position]);
        }
    }

    public function render()
    {
        $this->room->load([
            'playbackState.currentQueueItem.addedBy',
            'activeMembers',
            'upcomingQueue.addedBy',
        ]);

        $myMember = RoomMember::where('room_id', $this->room->id)->where('user_id', Auth::id())->first();

        $history = QueueItem::where('room_id', $this->room->id)
            ->whereNotNull('played_at')
            ->orderByDesc('played_at')
            ->limit(5)
            ->get();

        $drawerMember = null;
        $drawerPerms = null;
        if ($this->permDrawerUserId) {
            $drawerMember = RoomMember::with('user')
                ->where('room_id', $this->room->id)
                ->where('user_id', $this->permDrawerUserId)
                ->whereNull('left_at')
                ->first();
            if ($drawerMember) {
                $drawerPerms = $this->room->permissionsFor($drawerMember->user);
            }
        }

        return view('livewire.room', [
            'room' => $this->room,
            'state' => $this->room->playbackState,
            'queue' => $this->room->upcomingQueue->skip(1)->values(), // skip currently playing
            'members' => $this->room->activeMembers,
            'myPerms' => $this->room->permissionsFor(Auth::user()),
            'isHost' => $myMember?->role === 'host',
            'myMember' => $myMember,
            'drawerMember' => $drawerMember,
            'drawerPerms' => $drawerPerms,
            'history' => $history,
        ])->layout('layouts.app', [
            'pageTitle' => $this->room->name . ' — Tuneroom',
            'pageDescription' => 'Join ' . $this->room->name . ' and listen together in perfect sync. Room code: ' . $this->room->code,
        ]);
    }
}
