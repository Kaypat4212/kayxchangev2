<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCardOrder extends Model
{
    protected $fillable = [
        'user_id', 'gift_card_rate_id', 'reference', 'card_name', 'country', 'currency',
        'face_value', 'unit_rate_ngn', 'total_ngn', 'usd_exchange_rate', 'base_total_ngn',
        'purchase_markup_ngn', 'usd_face_value', 'effective_ngn_per_usd', 'recipient_email', 'customer_note',
        'delivery_code', 'admin_note', 'reviewed_by', 'status', 'processed_at', 'bot_purchase_token',
    ];

    protected $casts = [
        'face_value' => 'decimal:2',
        'unit_rate_ngn' => 'decimal:2',
        'total_ngn' => 'decimal:2',
        'usd_exchange_rate' => 'decimal:8',
        'base_total_ngn' => 'decimal:2',
        'purchase_markup_ngn' => 'decimal:2',
        'usd_face_value' => 'decimal:2',
        'effective_ngn_per_usd' => 'decimal:2',
        'delivery_code' => 'encrypted',
        'processed_at' => 'datetime',
        'delivery_email_sent_at' => 'datetime',
        'buyer_email_sent_at' => 'datetime',
        'delivery_telegram_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function rate(): BelongsTo { return $this->belongsTo(GiftCardRate::class, 'gift_card_rate_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
