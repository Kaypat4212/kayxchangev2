<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Admin audit records are immutable.'));
        static::deleting(fn () => throw new \LogicException('Admin audit records are immutable.'));
    }

    protected $fillable = [
        'admin_id', 'action', 'subject_type', 'subject_id', 'before', 'after',
        'ip_address', 'user_agent',
    ];

    protected $casts = ['before' => 'array', 'after' => 'array'];

    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
}
