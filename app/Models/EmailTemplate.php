<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['key', 'subject', 'body', 'description'];

    /**
     * Resolve a template and replace {{placeholders}} with $data values.
     * Returns ['subject' => ..., 'body' => ...] or null if not found.
     */
    public static function resolve(string $key, array $data = []): ?array
    {
        $template = static::where('key', $key)->first();
        if (!$template) {
            return null;
        }

        $search  = array_map(fn($k) => '{{'.$k.'}}', array_keys($data));
        $replace = array_values($data);

        return [
            'subject' => str_replace($search, $replace, $template->subject),
            'body'    => str_replace($search, $replace, $template->body),
        ];
    }
}
