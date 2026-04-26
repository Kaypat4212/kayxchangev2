<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $coin
 * @property string|null $network
 * @property string|null $wallet_address
 * @property string|null $proof
 * @property string|null $payment_method
 * @property string $status
 * @property string|null $bank_name
 * @property string|null $account_number
 * @property string|null $account_name
 * @property string|null $transaction_ref
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property string|null $cancelled_by
 * @property string|null $source
 * @property string|null $rate_used
 * @property float|null $usd_amount
 * @property float|null $naira_amount
 * @property string|null $payment_proof
 * @property string|null $admin_payment_proof
 */
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
        'rate_used',
        'wallet_address',
        'payment_proof',
        'admin_payment_proof',
        'transaction_ref',
        'cancelled_at',
        'cancelled_by',
        'source',
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