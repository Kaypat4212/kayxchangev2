<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureRequest extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'category', 'priority',
        'attachment', 'status', 'admin_notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'high'   => '#f97316',
            'medium' => '#f59e0b',
            'low'    => '#22c55e',
            default  => '#7a8599',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'      => '#f59e0b',
            'in_review'    => '#38bdf8',
            'planned'      => '#a855f7',
            'completed'    => '#00cc00',
            'rejected'     => '#ef4444',
            default        => '#7a8599',
        };
    }
}
