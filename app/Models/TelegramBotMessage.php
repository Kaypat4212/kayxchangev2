<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramBotMessage extends Model
{
    protected $fillable = [
        'chat_id',
        'username',
        'first_name',
        'user_id',
        'message_text',
        'message_type',
        'file_id',
        'file_name',
        'state_at_time',
        'is_command',
    ];

    protected $casts = [
        'is_command' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSenderNameAttribute(): string
    {
        return $this->first_name ?: ($this->username ? '@' . $this->username : "Chat #{$this->chat_id}");
    }
}
