<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSupportTicket extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'question', 'context',
        'admin_reply', 'status', 'user_notified', 'replied_at', 'replied_by',
    ];

    protected $casts = [
        'replied_at'     => 'datetime',
        'user_notified'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function repliedBy()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
