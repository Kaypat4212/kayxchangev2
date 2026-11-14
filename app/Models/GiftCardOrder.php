<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCardOrder extends Model
{
    protected $fillable = [
        'user_id', 'gift_card_rate_id', 'reference', 'card_name', 'country', 'currency',
        'face_value', 'unit_rate_ngn', 'total_ngn', 'recipient_email', 'customer_note',
        'delivery_code', 'admin_note', 'reviewed_by', 'status', 'processed_at',
    ];

    protected $casts = [
        'face_value' => 'decimal:2',
        'unit_rate_ngn' => 'decimal:2',
        'total_ngn' => 'decimal:2',
        'delivery_code' => 'encrypted',
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function rate(): BelongsTo { return $this->belongsTo(GiftCardRate::class, 'gift_card_rate_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
