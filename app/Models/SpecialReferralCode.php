<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialReferralCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'label',
        'category',
        'owner_user_id',
        'referrer_reward',
        'signup_bonus',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'signup_bonus' => 'decimal:2',
        'referrer_reward' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (SpecialReferralCode $code) {
            $code->code = strtoupper(trim((string) $code->code));
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
