<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'added_by_user_id',
        'spotify_track_id',
        'title',
        'artist',
        'album',
        'cover_url',
        'duration_ms',
        'position',
        'source',
        'played_at',
    ];

    protected function casts(): array
    {
        return [
            'played_at' => 'datetime',
            'duration_ms' => 'integer',
            'position' => 'integer',
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    public function hasBeenPlayed(): bool
    {
        return !is_null($this->played_at);
    }

    /** Duration formatted as m:ss */
    public function durationFormatted(): string
    {
        $seconds = intdiv($this->duration_ms, 1000);
        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeUpcoming($query)
    {
        return $query->whereNull('played_at')->orderBy('position');
    }

    public function scopePlayed($query)
    {
        return $query->whereNotNull('played_at')->orderBy('played_at');
    }
}
