<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $table = 'referrals';
    protected $fillable = [
        'referrer_id', 'referred_id', 'reward_amount', 'reward_currency', 'status',
    ];
}