<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportCaseMessage extends Model
{
    protected $fillable = ['support_case_id', 'sender_id', 'sender_role', 'message'];

    public function supportCase() { return $this->belongsTo(SupportCase::class); }
    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
}
