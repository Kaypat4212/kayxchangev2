<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $coin
 * @property float $usd_amount
 * @property float $naira_amount
 * @property float $rate_used
 * @property string $network
 * @property string $payment_method
 * @property string $wallet_address
 * @property string $payment_proof
 * @property string $blockchain_txid
 * @property string $admin_payment_proof
 * @property int $approved_by_admin_id
 * @property \Carbon\Carbon $approved_at
 * @property \Carbon\Carbon $cancelled_at
 * @property int $cancelled_by
 * @property string $status
 * @property string $ip_address
 * @property string $transaction_ref
 * @property string $transaction_type
 * @property string $source
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class BuyTrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'coin',
        'usd_amount',
        'naira_amount',
        'rate_used',
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
        'source',
    ];

    protected $dates = ['cancelled_at', 'approved_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
