<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioWithdrawal extends Model
{
    protected $fillable = ['user_id', 'reference', 'asset_amounts', 'usd_value', 'ngn_rate', 'naira_amount', 'status', 'rate_snapshot'];

    protected $casts = [
        'asset_amounts' => 'array',
        'rate_snapshot' => 'array',
        'usd_value' => 'decimal:8',
        'ngn_rate' => 'decimal:4',
        'naira_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
