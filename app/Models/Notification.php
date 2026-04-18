<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'user_id',
        'admin_id',
        'is_read',
        'is_broadcast',
        'read_at',
        'expires_at'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_broadcast' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    protected $appends = ['time_ago', 'icon'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeBroadcast($query)
    {
        return $query->where('is_broadcast', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere(function ($bq) {
                  // User inbox should not include admin trade-management broadcasts.
                  $bq->where('is_broadcast', true)
                     ->whereNull('admin_id')
                     ->where('type', '!=', 'trade_update')
                     ->whereNull('data->trade_type')
                     ->whereNull('data->reference')
                     ->whereNull('data->pending_minutes');
              });
        });
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    // Methods
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getIconAttribute()
    {
        return match($this->type) {
            'success' => 'bi-check-circle-fill text-success',
            'error' => 'bi-exclamation-triangle-fill text-danger',
            'warning' => 'bi-exclamation-circle-fill text-warning',
            'info' => 'bi-info-circle-fill text-info',
            'trade_update' => 'bi-arrow-repeat text-primary',
            'system' => 'bi-gear-fill text-secondary',
            default => 'bi-bell-fill text-primary'
        };
    }

    // Static methods for creating notifications
    public static function createForUser($userId, $type, $title, $message, $data = null, $expiresAt = null)
    {
        return self::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'user_id' => $userId,
            'is_broadcast' => false,
            'expires_at' => $expiresAt
        ]);
    }

    public static function createBroadcast($type, $title, $message, $data = null, $adminId = null, $expiresAt = null)
    {
        return self::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'admin_id' => $adminId,
            'is_broadcast' => true,
            'expires_at' => $expiresAt
        ]);
    }
}