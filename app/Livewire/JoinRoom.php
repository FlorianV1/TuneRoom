<?php

namespace App\Livewire;

use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class JoinRoom extends Component
{
    public string $code = '';
    public ?Room $foundRoom = null;
    public ?string $error = null;

    public function updatedCode(string $value)
    {
        $this->error = null;
        $this->foundRoom = null;

        $clean = strtoupper(trim($value));

        // Also handle full URLs like https://tuneroom.test/rooms/PEACH-0424
        if (str_contains($clean, '/ROOMS/')) {
            $clean = last(explode('/', $clean));
        }

        if (strlen($clean) >= 8) {
            $this->foundRoom = Room::where('code', $clean)
                ->where('status', 'active')
                ->with(['host', 'activeMembers', 'playbackState.currentQueueItem'])
                ->first();

            if (!$this->foundRoom) {
                $this->error = 'No active room found with that code.';
            }
        }
    }

    public function join()
    {
        if (!$this->foundRoom) return;

        // Check room isn't full
        if ($this->foundRoom->activeMembers->count() >= 10) {
            $this->error = 'This room is full (10/10).';
            return;
        }

        return redirect()->route('rooms.show', $this->foundRoom->code);
    }

    public function render()
    {
        return view('livewire.join-room')
            ->layout('layouts.app');
    }
}
