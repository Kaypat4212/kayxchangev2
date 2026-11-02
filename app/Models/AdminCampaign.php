<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminCampaign extends Model
{
    protected $fillable = [
        'created_by', 'title', 'body', 'channel', 'audience', 'status',
        'target_count', 'sent_count', 'failed_count', 'sent_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
