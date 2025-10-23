<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'username',
        'first_name',
        'user_id',
        'is_verified',
        'verification_step',
        'verification_code'
    ];

    protected $casts = [
        'chat_id' => 'string',
        'is_verified' => 'boolean',
    ];

    /**
     * Get the user associated with this Telegram user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}