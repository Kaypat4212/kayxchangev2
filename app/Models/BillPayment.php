<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPayment extends Model
{
    protected $fillable = [
        'user_id', 'reference', 'provider', 'category', 'biller_code', 'service_code', 'product_code',
        'customer_identifier', 'customer_name', 'phone', 'amount', 'fee', 'status', 'provider_reference',
        'validation_data', 'provider_response', 'receipt_token', 'validated_at', 'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2', 'fee' => 'decimal:2',
        'validation_data' => 'encrypted:array', 'provider_response' => 'encrypted:array',
        'validated_at' => 'datetime', 'processed_at' => 'datetime',
        'customer_identifier' => 'encrypted',
        'refunded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
