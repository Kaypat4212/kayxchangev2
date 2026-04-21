<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellTrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'coin',
        'network',
        'amount',
        'proof',
        'status',
        'account_name',
        'account_number',
        'bank_name',
        'payment_method',
        'usd_amount', 
        'naira_amount',
        'wallet_address',
        'payment_proof',
        'transaction_ref',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $dates = ['cancelled_at'];

    protected $casts = [
        'amount' => 'decimal:2',
        'usd_amount' => 'decimal:2',
        'naira_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}