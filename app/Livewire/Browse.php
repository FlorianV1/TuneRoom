<?php

namespace App\Livewire;

use App\Models\Room;
use Livewire\Component;

class Browse extends Component
{
    public function render()
    {
        $rooms = Room::where('status', 'active')
            ->where('visibility', 'public')
            ->with(['host', 'activeMembers', 'playbackState.currentQueueItem'])
            ->get()
            ->sortByDesc(fn($r) => $r->activeMembers->count());

        return view('livewire.browse', ['rooms' => $rooms])
            ->layout('layouts.app', ['pageTitle' => 'Browse Rooms — Tuneroom']);
    }
}
