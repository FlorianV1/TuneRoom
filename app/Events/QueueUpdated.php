<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $room_id,
        public readonly string $added_by_name,
        public readonly string $track_title,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel("room.{$this->room_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'queue.updated';
    }
}
