<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Blacklist extends Model
{
    protected $fillable = [
        'type',
        'value',
        'reason',
        'blocked_by',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Check if a given IP is blocked.
     */
    public static function isIpBlocked(string $ip): bool
    {
        return static::where('type', 'ip')
            ->where('value', $ip)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }

    /**
     * Check if a given user_id is blocked.
     */
    public static function isUserBlocked(int $userId): bool
    {
        return static::where('type', 'user')
            ->where('value', (string) $userId)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }
}
