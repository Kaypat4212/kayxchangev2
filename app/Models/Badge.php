<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $emoji
 * @property string|null $description
 * @property string $category
 * @property string $criteria_type
 * @property int|null $criteria_value
 * @property string|null $color
 * @property string $rarity
 * @property bool $is_special
 * @property int $sort_order
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
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
