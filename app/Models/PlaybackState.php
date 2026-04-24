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
        'started_at',
    ];

    protected function casts(): array
    {
        return [
            'position_ms' => 'integer',
            'updated_at'  => 'datetime',
            'started_at'  => 'datetime',
        ];
    }

    public function currentPositionMs(): int
    {
        if ($this->status !== 'playing') {
            return $this->position_ms;
        }

        // Use started_at if available for more accurate calculation
        $reference = $this->started_at ?? $this->updated_at;
        $elapsedMs = (int) ($reference->diffInMilliseconds(now()));
        return $this->position_ms + $elapsedMs;
    }

    public function syncPayload(): array
    {
        return [
            'status'        => $this->status,
            'position_ms'   => $this->currentPositionMs(),
            'server_time'   => now()->valueOf(),
            'track_id'      => $this->currentQueueItem?->spotify_track_id,
            'queue_item_id' => $this->current_queue_item_id,
        ];
    }

    public function isPlaying(): bool { return $this->status === 'playing'; }
    public function isPaused(): bool  { return $this->status === 'paused';  }
    public function isStopped(): bool { return $this->status === 'stopped'; }

    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function currentQueueItem(): BelongsTo { return $this->belongsTo(QueueItem::class, 'current_queue_item_id'); }
}
