<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'spotify_id',
        'spotify_token',
        'spotify_refresh_token',
        'spotify_token_expires_at',
        'is_banned',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'spotify_token',
        'spotify_refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'spotify_token_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
        ];
    }

    public function hasSpotifyConnected(): bool
    {
        return !is_null($this->spotify_token);
    }

    public function spotifyTokenIsExpired(): bool
    {
        return $this->spotify_token_expires_at?->isPast() ?? true;
    }

    /** Rooms this user created (is host of) */
    public function hostedRooms(): HasMany
    {
        return $this->hasMany(Room::class, 'host_user_id');
    }

    /** All rooms this user is a member of (via pivot) */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_members')
            ->withPivot(['role', 'permission_overrides', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    /** Active room memberships */
    public function activeRooms(): BelongsToMany
    {
        return $this->rooms()->wherePivotNull('left_at');
    }

    /** Songs this user has added to any queue */
    public function queueItems(): HasMany
    {
        return $this->hasMany(QueueItem::class, 'added_by_user_id');
    }
}
