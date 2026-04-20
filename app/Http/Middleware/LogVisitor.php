<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogVisitor
{
    /**
     * Paths that should never be logged (assets, health-checks, ajax polls, etc.)
     */
    private const SKIP_PREFIXES = [
        '/storage/', '/css/', '/js/', '/images/', '/Assests/', '/assets/',
        '/barcodes/', '/favicon', '/robots.txt', '/sitemap', '/manifest.json',
        '/service-worker', '/serviceworker', '/sw.js',
        '/telescope', '/horizon', '/debugbar',
        '/ajax/', '/api/',
        '/telegram/webhook',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Run logging asynchronously after response is sent
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        try {
            $this->logVisit($request);
        } catch (\Throwable $e) {
            Log::warning('LogVisitor middleware error: ' . $e->getMessage());
        }

        return $response;
    }

    private function logVisit(Request $request): void
    {
        $path = $request->path();

        // Skip static assets and internal paths
        foreach (self::SKIP_PREFIXES as $prefix) {
            if (str_starts_with('/' . $path, $prefix)) return;
        }

        // Skip if extension looks like a static file
        if (preg_match('/\.(png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot|css|js|map|webp)$/i', $path)) return;

        $ua     = $request->userAgent() ?? '';
        $isBot  = VisitorLog::isBot($ua);
        $ip     = $request->ip();

        // Parse UA
        $parsed   = VisitorLog::parseUserAgent($ua);
        $geo      = $this->getGeo($ip);
        $routeName = $request->route()?->getName();
        $referer  = $request->headers->get('referer');

        $isHomepage = in_array($routeName, ['home', null]) && ($path === '' || $path === '/');

        // Write log record
        $log = VisitorLog::create([
            'ip'           => $ip,
            'method'       => $request->method(),
            'url'          => substr($request->fullUrl(), 0, 500),
            'route_name'   => $routeName,
            'user_agent'   => substr($ua, 0, 500),
            'browser'      => $parsed['browser'],
            'platform'     => $parsed['platform'],
            'is_mobile'    => $parsed['isMobile'],
            'country'      => $geo['country'] ?? null,
            'country_code' => $geo['countryCode'] ?? null,
            'region'       => $geo['regionName'] ?? null,
            'city'         => $geo['city'] ?? null,
            'isp'          => $geo['isp'] ?? null,
            'referer'      => $referer ? substr($referer, 0, 500) : null,
            'is_bot'       => $isBot,
            'user_id'      => Auth::id(),
        ]);

        // Only notify for homepage visits by real humans
        if (!$isBot && $isHomepage) {
            $this->maybeSendTelegram($log, $ip);
        }
    }

    /**
     * Geolocate an IP using ip-api.com (free, no key needed, 45 req/min).
     * Results are cached per IP for 24 hours.
     */
    private function getGeo(string $ip): array
    {
        // Private / local IPs
        if (in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return ['country' => 'Local', 'countryCode' => 'LC', 'regionName' => 'Local', 'city' => 'Local', 'isp' => 'Local'];
        }

        return Cache::remember("geo_{$ip}", 86400, function () use ($ip) {
            try {
                $res = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,regionName,city,isp");
                if ($res->ok() && $res->json('status') === 'success') {
                    return $res->json();
                }
            } catch (\Throwable) {}
            return [];
        });
    }

    /**
     * Send Telegram notification for new unique IPs visiting the homepage.
     * Throttled: once per IP per 12 hours.
     */
    private function maybeSendTelegram(VisitorLog $log, string $ip): void
    {
        $cacheKey = "visitor_tg_{$ip}";
        if (Cache::has($cacheKey)) return;

        $ownerChatId = env('TELEGRAM_OWNER_CHAT_ID');
        if (!$ownerChatId) return;

        Cache::put($cacheKey, true, 43200); // 12 hours

        $log->update(['telegram_notified' => true]);

        $flag    = $log->country_code ? $this->countryFlag($log->country_code) : '🌐';
        $device  = $log->is_mobile ? '📱 Mobile' : '🖥️ Desktop';
        $browser = $log->browser ?? 'Unknown';
        $os      = $log->platform ?? 'Unknown';
        $loc     = implode(', ', array_filter([$log->city, $log->region, $log->country])) ?: 'Unknown';
        $isp     = $log->isp ? "\n🏢 *ISP:* " . $this->esc($log->isp) : '';
        $ref     = $log->referer ? "\n🔗 *Referrer:* " . $this->esc(parse_url($log->referer, PHP_URL_HOST) ?: $log->referer) : '';
        $user    = $log->user_id ? "\n👤 *User ID:* #{$log->user_id} (logged in)" : '';
        $time    = now()->format('d M Y, H:i') . ' (UTC)';

        $msg =
            "👁️ *New Homepage Visit*\n\n" .
            "{$flag} *Location:* " . $this->esc($loc) . "\n" .
            "🌐 *IP:* `{$ip}`\n" .
            "{$device} — {$browser} on {$os}" .
            $isp .
            $ref .
            $user . "\n" .
            "🕐 *Time:* {$time}";

        try {
            $token = env('TELEGRAM_TOKEN');
            if (!$token) return;
            Http::timeout(4)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id'    => $ownerChatId,
                'text'       => $msg,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Visitor Telegram notify failed: ' . $e->getMessage());
        }
    }

    private function countryFlag(string $code): string
    {
        // Convert country code to flag emoji
        $code = strtoupper($code);
        if (strlen($code) !== 2) return '🌐';
        return mb_convert_encoding(
            '&#' . (0x1F1E0 + ord($code[0]) - ord('A')) . ';' .
            '&#' . (0x1F1E0 + ord($code[1]) - ord('A')) . ';',
            'UTF-8', 'HTML-ENTITIES'
        );
    }

    private function esc(string $text): string
    {
        return str_replace(['_', '*', '[', ']', '`'], ['\\_', '\\*', '\\[', '\\]', '\\`'], $text);
    }
}
