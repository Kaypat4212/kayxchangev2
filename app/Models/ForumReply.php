<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumReply extends Model
{
    protected $fillable = ['topic_id', 'user_id', 'body', 'status'];

    public function topic(): BelongsTo { return $this->belongsTo(ForumTopic::class, 'topic_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reactions(): HasMany { return $this->hasMany(ForumReaction::class, 'reply_id'); }
    public function reports(): HasMany { return $this->hasMany(ForumReport::class, 'reply_id'); }
}
