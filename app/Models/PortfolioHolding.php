<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioHolding extends Model
{
    protected $fillable = ['user_id', 'asset', 'quantity', 'average_cost_usd', 'platform', 'notes'];
    protected $casts = ['quantity' => 'decimal:12', 'average_cost_usd' => 'decimal:8'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
