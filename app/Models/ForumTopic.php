<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumTopic extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'blog_post_id', 'title', 'slug', 'body', 'status',
        'is_pinned', 'is_locked', 'views_count', 'replies_count', 'last_activity_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(ForumCategory::class, 'category_id'); }
    public function blogPost(): BelongsTo { return $this->belongsTo(BlogPost::class); }
    public function replies(): HasMany { return $this->hasMany(ForumReply::class, 'topic_id'); }
    public function reactions(): HasMany { return $this->hasMany(ForumReaction::class, 'topic_id'); }
    public function reports(): HasMany { return $this->hasMany(ForumReport::class, 'topic_id'); }
}
