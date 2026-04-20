<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $table = 'referrals';
    protected $fillable = [
        'referrer_id', 'referred_id', 'reward_amount', 'reward_currency', 'status',
        'fraud_flagged', 'fraud_reason', 'blocked_at',
    ];

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
}