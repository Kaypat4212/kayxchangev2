<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'currency', 'status', 'company_account_id',
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
