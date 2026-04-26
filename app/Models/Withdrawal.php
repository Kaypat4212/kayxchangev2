<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property float $fee_amount
 * @property array|null $bank_account
 * @property string $payment_method
 * @property string $status
 * @property string|null $currency
 * @property string $reference
 * @property string|null $payout_gateway
 * @property string|null $payout_reference
 * @property string|null $payout_status
 * @property string|null $payout_recipient_code
 * @property string|null $payout_response
 * @property \Carbon\Carbon|null $processed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'fee_amount',
        'bank_account',
        'payment_method',
        'status',
        'currency',
        'reference',
        'processed_at',
        'payout_gateway',
        'payout_reference',
        'payout_status',
        'payout_recipient_code',
        'payout_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bank_account' => 'array',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}