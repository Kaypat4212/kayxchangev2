<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyTrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'coin',
        'usd_amount',
        'naira_amount',
        'network',
        'payment_method',
        'wallet_address',
        'payment_proof',
        'status',
        'ip_address',
        'transaction_ref',
        'transaction_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
