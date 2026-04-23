<?php

namespace App\Livewire;

use App\Models\Room;
use App\Models\RoomMember;
use App\Models\PlaybackState;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateRoom extends Component
{
    public string $name = '';
    public string $fallback_playlist_url = '';

    public bool $cohost_play = true;
    public bool $cohost_skip = true;
    public bool $cohost_add = true;

    public bool $listener_play = false;
    public bool $listener_skip = false;
    public bool $listener_add = true;

    public string $visibility = 'invite';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:60',
            'fallback_playlist_url' => 'nullable|url',
            'visibility' => 'in:invite,friends,public',
        ];
    }

    public function create()
    {
        $this->validate();

        $room = Room::create([
            'name' => $this->name,
            'host_user_id' => Auth::id(),
            'fallback_playlist_url' => $this->fallback_playlist_url ?: null,
            'default_cohost_permissions' => [
                'play' => $this->cohost_play,
                'skip' => $this->cohost_skip,
                'add' => $this->cohost_add,
            ],
            'default_listener_permissions' => [
                'play' => $this->listener_play,
                'skip' => $this->listener_skip,
                'add' => $this->listener_add,
            ],
            'status' => 'active',
        ]);

        RoomMember::create([
            'room_id' => $room->id,
            'user_id' => Auth::id(),
            'role' => 'host',
            'joined_at' => now(),
        ]);

        PlaybackState::create([
            'room_id' => $room->id,
            'status' => 'stopped',
            'position_ms' => 0,
        ]);

        return redirect()->route('rooms.show', $room->code);
    }

    public function render()
    {
        return view('livewire.create-room')
            ->layout('layouts.app');
    }
}
