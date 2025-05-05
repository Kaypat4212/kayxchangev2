<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyTrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coin',
        'usd_amount',
        'naira_amount',
        'network',
        'wallet_address',
        'payment_proof',
        'status',
    ];
}
