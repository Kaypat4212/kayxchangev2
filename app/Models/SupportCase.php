<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportCase extends Model
{
    protected $fillable = [
        'user_id', 'crypto_deposit_id', 'category', 'subject', 'message',
        'status', 'admin_response', 'assigned_admin_id', 'resolved_at',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function deposit() { return $this->belongsTo(CryptoDeposit::class, 'crypto_deposit_id'); }
    public function assignedAdmin() { return $this->belongsTo(User::class, 'assigned_admin_id'); }
    public function messages() { return $this->hasMany(SupportCaseMessage::class)->with('sender')->orderBy('created_at'); }
}
