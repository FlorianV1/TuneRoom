<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'host_user_id',
        'fallback_playlist_url',
        'fallback_playlist_name',
        'default_cohost_permissions',
        'default_listener_permissions',
        'status',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'default_cohost_permissions' => 'array',
            'default_listener_permissions' => 'array',
            'ended_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Room $room) {
            if (empty($room->code)) {
                $room->code = static::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode(): string
    {
        $words = ['PEACH', 'VINYL', 'CORAL', 'LUNAR', 'AMBER', 'MISTY', 'PRISM', 'CEDAR'];
        do {
            $code = $words[array_rand($words)] . '-' . rand(1000, 9999);
        } while (static::where('code', $code)->exists());

        return $code;
    }


    /**
     * Resolve effective permissions for a user in this room.
     * Priority: user override > role default > host (always all true)
     */
    public function permissionsFor(User $user): array
    {
        $member = $this->members()->where('user_id', $user->id)->first();

        if (!$member) {
            return ['play' => false, 'skip' => false, 'add' => false];
        }

        if ($member->pivot->role === 'host') {
            return ['play' => true, 'skip' => true, 'add' => true];
        }

        $roleDefaults = $member->pivot->role === 'cohost'
            ? ($this->default_cohost_permissions ?? ['play' => true, 'skip' => true, 'add' => true])
            : ($this->default_listener_permissions ?? ['play' => false, 'skip' => false, 'add' => true]);

        $overrides = $member->pivot->permission_overrides ?? [];

        return array_merge($roleDefaults, array_filter(
            $overrides,
            fn($v) => !is_null($v)
        ));
    }

    public function userCan(User $user, string $permission): bool
    {
        return (bool)($this->permissionsFor($user)[$permission] ?? false);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'room_members')
            ->withPivot(['role', 'permission_overrides', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function activeMembers(): BelongsToMany
    {
        return $this->members()->wherePivotNull('left_at');
    }

    public function queue(): HasMany
    {
        return $this->hasMany(QueueItem::class)->orderBy('position');
    }

    public function upcomingQueue(): HasMany
    {
        return $this->queue()->whereNull('played_at');
    }

    public function playbackState(): HasOne
    {
        return $this->hasOne(PlaybackState::class);
    }


    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
