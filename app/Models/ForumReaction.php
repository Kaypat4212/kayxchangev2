<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumReaction extends Model
{
    protected $fillable = ['user_id', 'topic_id', 'reply_id', 'reaction'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function topic(): BelongsTo { return $this->belongsTo(ForumTopic::class, 'topic_id'); }
    public function reply(): BelongsTo { return $this->belongsTo(ForumReply::class, 'reply_id'); }
}
