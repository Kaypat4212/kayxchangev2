<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailDeliveryLog extends Model
{
    protected $fillable = [
        'trace_id', 'message_id', 'recipient', 'subject', 'mailer', 'status',
        'retry_type', 'retry_of_id', 'attempts', 'last_error', 'accepted_at',
    ];

    protected $casts = ['accepted_at' => 'datetime'];
}
