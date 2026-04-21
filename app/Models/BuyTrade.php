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
        'blockchain_txid',
        'admin_payment_proof',
        'approved_by_admin_id',
        'approved_at',
        'cancelled_at',
        'cancelled_by',
        'status',
        'ip_address',
        'transaction_ref',
        'transaction_type',
    ];

    protected $dates = ['cancelled_at', 'approved_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
