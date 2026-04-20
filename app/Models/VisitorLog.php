<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    protected $fillable = [
        'ip', 'method', 'url', 'route_name', 'user_agent',
        'browser', 'platform', 'is_mobile', 'country', 'country_code',
        'region', 'city', 'isp', 'referer', 'is_bot',
        'user_id', 'telegram_notified',
    ];

    protected $casts = [
        'is_mobile'          => 'boolean',
        'is_bot'             => 'boolean',
        'telegram_notified'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Parse basic browser/platform info from user agent string without extra packages.
     */
    public static function parseUserAgent(string $ua): array
    {
        $browser  = 'Unknown';
        $platform = 'Unknown';
        $isMobile = false;

        // Platform
        if (str_contains($ua, 'Windows NT'))       $platform = 'Windows';
        elseif (str_contains($ua, 'Macintosh'))    $platform = 'macOS';
        elseif (str_contains($ua, 'Linux'))        $platform = 'Linux';
        elseif (str_contains($ua, 'Android'))      { $platform = 'Android'; $isMobile = true; }
        elseif (str_contains($ua, 'iPhone'))       { $platform = 'iOS (iPhone)'; $isMobile = true; }
        elseif (str_contains($ua, 'iPad'))         { $platform = 'iOS (iPad)'; $isMobile = true; }

        // Mobile detection
        if (!$isMobile && preg_match('/Mobile|Android|Touch/i', $ua)) $isMobile = true;

        // Browser
        if (str_contains($ua, 'Edg/') || str_contains($ua, 'EdgA/'))         $browser = 'Edge';
        elseif (str_contains($ua, 'OPR/') || str_contains($ua, 'Opera'))     $browser = 'Opera';
        elseif (str_contains($ua, 'Chrome') && !str_contains($ua, 'Chromium')) $browser = 'Chrome';
        elseif (str_contains($ua, 'Firefox'))                                  $browser = 'Firefox';
        elseif (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome'))  $browser = 'Safari';
        elseif (str_contains($ua, 'MSIE') || str_contains($ua, 'Trident/'))   $browser = 'IE';

        return compact('browser', 'platform', 'isMobile');
    }

    /**
     * Detect bots by user-agent string.
     */
    public static function isBot(string $ua): bool
    {
        $bots = [
            'bot', 'crawl', 'spider', 'slurp', 'scraper', 'scan',
            'httpclient', 'python-requests', 'curl/', 'wget/',
            'go-http', 'java/', 'libwww', 'okhttp', 'axios',
            'facebookexternalhit', 'twitterbot', 'linkedinbot',
            'whatsapp', 'telegrambot', 'googlebot', 'bingbot',
        ];
        $lower = strtolower($ua);
        foreach ($bots as $bot) {
            if (str_contains($lower, $bot)) return true;
        }
        return false;
    }
}
