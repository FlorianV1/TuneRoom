<?php

namespace App\Jobs;

use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CloseEmptyRooms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find active rooms where all members have left
        // and the last member left more than 10 minutes ago
        $rooms = Room::where('status', 'active')
            ->with('activeMembers')
            ->get()
            ->filter(function (Room $room) {
                if ($room->activeMembers->count() > 0) return false;

                // Check when the last member left
                $lastLeft = \App\Models\RoomMember::where('room_id', $room->id)
                    ->whereNotNull('left_at')
                    ->max('left_at');

                if (! $lastLeft) return false;

                return now()->diffInMinutes($lastLeft) >= 10;
            });

        foreach ($rooms as $room) {
            $room->update([
                'status'   => 'ended',
                'ended_at' => now(),
            ]);

            $room->playbackState?->update(['status' => 'stopped']);
        }
    }
}
