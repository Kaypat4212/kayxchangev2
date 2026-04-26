<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property float $fee_amount
 * @property string|null $currency
 * @property string $status
 * @property int|null $company_account_id
 * @property string|null $transaction_ref
 * @property string|null $proof_of_payment
 * @property string|null $admin_note
 * @property string|null $payment_method
 * @property string|null $proof
 * @property string|null $gateway
 * @property string|null $gateway_reference
 * @property string|null $gateway_response
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Deposit extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'fee_amount', 'currency', 'status', 'company_account_id',
        'transaction_ref', 'proof_of_payment', 'admin_note', 'payment_method', 'proof',
        'gateway', 'gateway_reference', 'gateway_response',
    ];

    protected $casts = [
        'status' => 'string', // Ensure status is treated as a string
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function companyAccount()
    {
        return $this->belongsTo(CompanyAccount::class);
    }
}
