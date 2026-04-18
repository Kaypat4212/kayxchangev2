<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BugReport extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'severity', 'category',
        'page_url', 'browser', 'attachment', 'status', 'admin_notes', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => '#ef4444',
            'high'     => '#f97316',
            'medium'   => '#f59e0b',
            'low'      => '#22c55e',
            default    => '#7a8599',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open'          => '#f59e0b',
            'investigating' => '#38bdf8',
            'resolved'      => '#00cc00',
            'closed'        => '#7a8599',
            default         => '#7a8599',
        };
    }
}
