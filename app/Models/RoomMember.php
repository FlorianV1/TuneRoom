<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RoomMember extends Pivot
{
    protected $table = 'room_members';

    protected $fillable = [
        'room_id',
        'user_id',
        'role',
        'permission_overrides',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'permission_overrides' => 'array',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isHost(): bool
    {
        return $this->role === 'host';
    }

    public function isCohost(): bool
    {
        return $this->role === 'cohost';
    }

    public function isListener(): bool
    {
        return $this->role === 'listener';
    }
}
