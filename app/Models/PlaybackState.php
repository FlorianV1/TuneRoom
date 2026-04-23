<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaybackState extends Model
{
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = null;

    protected $fillable = [
        'room_id',
        'current_queue_item_id',
        'status',
        'position_ms',
    ];

    protected function casts(): array
    {
        return [
            'position_ms' => 'integer',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Calculate the current playback position in ms, accounting for
     * elapsed time since the state was last updated.
     *
     * This is what gets broadcast to clients so they can sync.
     */
    public function currentPositionMs(): int
    {
        if ($this->status !== 'playing') {
            return $this->position_ms;
        }

        $elapsedMs = (int)($this->updated_at->diffInMilliseconds(now()));
        return $this->position_ms + $elapsedMs;
    }

    /**
     * Returns the data payload clients need to sync playback.
     * Clients receive this and offset by their own network latency.
     */
    public function syncPayload(): array
    {
        return [
            'status' => $this->status,
            'position_ms' => $this->currentPositionMs(),
            'server_time' => now()->valueOf(),
            'track_id' => $this->currentQueueItem?->spotify_track_id,
            'queue_item_id' => $this->current_queue_item_id,
        ];
    }

    public function isPlaying(): bool
    {
        return $this->status === 'playing';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isStopped(): bool
    {
        return $this->status === 'stopped';
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function currentQueueItem(): BelongsTo
    {
        return $this->belongsTo(QueueItem::class, 'current_queue_item_id');
    }
}
