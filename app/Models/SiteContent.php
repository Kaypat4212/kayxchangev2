<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    protected $fillable = ['key', 'group', 'label', 'value'];

    /** Return all site content as a flat key => value array (cached per request) */
    public static function allKeyed(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = static::pluck('value', 'key')->toArray();
        }
        return $cache;
    }

    /** Get a single value with an optional default */
    public static function get(string $key, string $default = ''): string
    {
        return static::allKeyed()[$key] ?? $default;
    }
}
