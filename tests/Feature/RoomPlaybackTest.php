<?php

use App\Livewire\RoomPage;
use App\Models\PlaybackState;
use App\Models\QueueItem;
use App\Models\Room;
use App\Models\RoomMember;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    Http::fake([
        'api.spotify.com/v1/me/player/recently-played*' => Http::response(['items' => []]),
        'api.spotify.com/v1/me/top/tracks*' => Http::response(['items' => []]),
    ]);
});

function playbackUser(): User
{
    return User::factory()->create([
        'spotify_token' => 'token',
        'spotify_refresh_token' => 'refresh-token',
        'spotify_token_expires_at' => now()->addHour(),
    ]);
}

function playbackRoom(User $user, array $attributes = []): Room
{
    $room = Room::create(array_merge([
        'name' => 'Test Room',
        'host_user_id' => $user->id,
        'status' => 'active',
    ], $attributes));

    RoomMember::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'role' => 'host',
        'joined_at' => now(),
    ]);

    PlaybackState::create([
        'room_id' => $room->id,
        'status' => 'stopped',
        'position_ms' => 0,
    ]);

    return $room;
}

function queueTrack(Room $room, User $user, array $attributes = []): QueueItem
{
    return QueueItem::create(array_merge([
        'room_id' => $room->id,
        'added_by_user_id' => $user->id,
        'spotify_track_id' => 'track-1',
        'title' => 'First Track',
        'artist' => 'Artist',
        'album' => 'Album',
        'duration_ms' => 180000,
        'position' => 0,
        'source' => 'user',
    ], $attributes));
}

it('selects the first queued track before starting playback', function () {
    $user = playbackUser();
    $room = playbackRoom($user);
    $track = queueTrack($room, $user);

    Livewire::actingAs($user)
        ->test(RoomPage::class, ['code' => $room->code])
        ->call('togglePlay')
        ->assertDispatched('spotify-sync');

    $state = PlaybackState::where('room_id', $room->id)->first();

    expect($state->current_queue_item_id)->toBe($track->id)
        ->and($state->status)->toBe('playing')
        ->and($state->position_ms)->toBe(0)
        ->and($state->started_at)->not->toBeNull();
});

it('does not enter playing state when there is no playable track', function () {
    $user = playbackUser();
    $room = playbackRoom($user);

    Livewire::actingAs($user)
        ->test(RoomPage::class, ['code' => $room->code])
        ->call('togglePlay')
        ->assertDispatched('notify');

    $state = PlaybackState::where('room_id', $room->id)->first();

    expect($state->current_queue_item_id)->toBeNull()
        ->and($state->status)->toBe('stopped');
});

it('resumes an existing room membership instead of creating a duplicate', function () {
    $user = playbackUser();
    $room = playbackRoom($user);

    RoomMember::where('room_id', $room->id)
        ->where('user_id', $user->id)
        ->update(['left_at' => now()]);

    Livewire::actingAs($user)->test(RoomPage::class, ['code' => $room->code]);

    $memberships = RoomMember::where('room_id', $room->id)
        ->where('user_id', $user->id)
        ->get();

    expect($memberships)->toHaveCount(1)
        ->and($memberships->first()->left_at)->toBeNull();
});

it('reports missing Spotify devices during sync', function () {
    $user = playbackUser();
    $room = playbackRoom($user);

    Http::fake([
        'api.spotify.com/v1/me/player/play' => Http::response(['error' => ['reason' => 'NO_ACTIVE_DEVICE']], 404),
    ]);

    $this->actingAs($user)
        ->postJson(route('rooms.sync-playback'), [
            'room_id' => $room->id,
            'status' => 'playing',
            'track_id' => 'track-1',
            'position_ms' => 0,
            'server_time' => now()->valueOf(),
        ])
        ->assertStatus(409)
        ->assertJson([
            'ok' => false,
            'error' => 'no_device',
        ]);
});
