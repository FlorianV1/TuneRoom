<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaybackSync implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int     $roomId,
        public readonly string  $status,
        public readonly int     $positionMs,
        public readonly int     $serverTime,
        public readonly ?string $trackId,
    )
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel("room.{$this->roomId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'playback.sync';
    }
}
