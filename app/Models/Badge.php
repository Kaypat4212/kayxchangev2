<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    protected $fillable = [
        'slug', 'name', 'emoji', 'description', 'category',
        'criteria_type', 'criteria_value', 'color', 'rarity',
        'is_special', 'sort_order',
    ];

    protected $casts = [
        'is_special'     => 'boolean',
        'criteria_value' => 'integer',
        'sort_order'     => 'integer',
    ];

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
                    ->withPivot(['awarded_at', 'awarded_by', 'is_pinned', 'pin_position'])
                    ->withTimestamps();
    }

    /** CSS ring-colour class helper */
    public function rarityClass(): string
    {
        return match ($this->rarity) {
            'legendary' => 'badge-legendary',
            'rare'      => 'badge-rare',
            default     => 'badge-common',
        };
    }
}
