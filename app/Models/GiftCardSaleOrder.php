<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCardSaleOrder extends Model
{
    protected $fillable = [
        'user_id', 'gift_card_rate_id', 'reference', 'card_name', 'country', 'currency',
        'face_value', 'unit_rate_ngn', 'estimated_ngn', 'approved_ngn', 'card_code',
        'image_path', 'customer_note', 'admin_note', 'reviewed_by', 'status', 'processed_at',
    ];

    protected $casts = [
        'face_value' => 'decimal:2',
        'unit_rate_ngn' => 'decimal:2',
        'estimated_ngn' => 'decimal:2',
        'approved_ngn' => 'decimal:2',
        'card_code' => 'encrypted',
        'processed_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function rate() { return $this->belongsTo(GiftCardRate::class, 'gift_card_rate_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
