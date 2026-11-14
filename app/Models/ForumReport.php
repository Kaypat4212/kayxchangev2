<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumReport extends Model
{
    protected $fillable = ['reporter_id', 'topic_id', 'reply_id', 'reason', 'details', 'status', 'reviewed_by', 'admin_note', 'reviewed_at'];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reporter_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function topic(): BelongsTo { return $this->belongsTo(ForumTopic::class, 'topic_id'); }
    public function reply(): BelongsTo { return $this->belongsTo(ForumReply::class, 'reply_id'); }
}
