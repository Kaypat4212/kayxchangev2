<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'excerpt',
        'content',
        'cover_image',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published'  => 'boolean',
        'published_at'  => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->orderByDesc('published_at');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Auto-generate slug from title if not provided.
     */
    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $post) {
            if (empty($post->slug)) {
                $post->slug = static::uniqueSlug($post->title);
            }
            if ($post->is_published && empty($post->published_at)) {
                $post->published_at = now();
            }
        });

        static::updating(function (self $post) {
            if ($post->isDirty('is_published') && $post->is_published && empty($post->published_at)) {
                $post->published_at = now();
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    /**
     * A short reading time estimate.
     */
    public function readingTime(): int
    {
        $words = str_word_count(strip_tags($this->content));
        return max(1, (int) ceil($words / 200));
    }
}
