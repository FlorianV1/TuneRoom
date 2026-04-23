<?php

namespace App\Livewire;

use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        $hostedRooms = Room::where('host_user_id', $user->id)
            ->where('status', 'active')
            ->with(['activeMembers', 'playbackState.currentQueueItem'])
            ->latest()
            ->get();

        $joinedRooms = $user->activeRooms()
            ->where('host_user_id', '!=', $user->id)
            ->where('rooms.status', 'active')
            ->with(['activeMembers', 'playbackState.currentQueueItem', 'host'])
            ->get();

        return view('livewire.dashboard', [
            'hostedRooms' => $hostedRooms,
            'joinedRooms' => $joinedRooms,
            'user'        => $user,
        ])->layout('layouts.app');
    }
}
