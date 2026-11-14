<?php

namespace App\Http\Middleware;

use App\Models\AdminSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class PlatformAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Admins must retain access to reverse an outage or change these settings.
        $isAdmin = Auth::check() && (bool) Auth::user()->is_admin;
        $adminPath = $request->is('admin', 'admin/*');
        $providerCallback = $request->is('deposits/webhook/*', 'withdrawals/webhook/*', 'whatsapp/webhook', 'cryptomus/webhook');
        if ($isAdmin || $adminPath || $providerCallback) return $next($request);

        $mode = (string) AdminSetting::getSetting('runtime_mode', 'production');
        $maintenance = $mode === 'off' || (string) AdminSetting::getSetting('site_maintenance', '0') === '1';
        if ($maintenance) {
            if ($request->expectsJson()) return response()->json(['message' => 'The platform is temporarily unavailable for maintenance.'], 503);
            return response()->view('errors.maintenance', [], 503)->header('Retry-After', '900');
        }

        $blocked = $this->blockedCountryCodes();
        if ($blocked !== []) {
            $country = $this->countryCode($request);
            if ($country && in_array($country, $blocked, true)) {
                if ($request->expectsJson()) return response()->json(['message' => 'This service is not available in your region.'], 451);
                return response()->view('errors.unavailable-region', [], 451)->header('Cache-Control', 'no-store');
            }
        }

        return $next($request);
    }

    private function blockedCountryCodes(): array
    {
        $value = AdminSetting::getSetting('blocked_countries', '[]');
        $codes = is_array($value) ? $value : json_decode((string) $value, true);
        if (!is_array($codes)) $codes = preg_split('/[,\s]+/', strtoupper((string) $value), -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_unique(array_filter(array_map(
            fn ($code) => preg_match('/^[A-Z]{2}$/', strtoupper((string) $code)) ? strtoupper((string) $code) : null,
            $codes
        ))));
    }

    private function countryCode(Request $request): ?string
    {
        $cfCountry = strtoupper((string) $request->header('CF-IPCountry', ''));
        $peerIp = (string) $request->server('REMOTE_ADDR', '');
        // Only trust visitor-country headers when the actual network peer belongs
        // to Cloudflare, so direct-origin clients cannot spoof a country header.
        if ($request->headers->has('CF-Ray') && preg_match('/^[A-Z]{2}$/', $cfCountry) && $this->isCloudflarePeer($peerIp)) {
            return $cfCountry;
        }

        // Use the socket peer for direct requests; forwarded headers are user input
        // unless their proxy has been verified above.
        if ($request->headers->has('CF-Ray')) return null;
        $ip = $peerIp;
        if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return null;

        return Cache::remember('visitor_country_' . hash('sha256', $ip), now()->addDay(), function () use ($ip) {
            try {
                $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=status,countryCode");
                $code = strtoupper((string) $response->json('countryCode', ''));
                return $response->successful() && $response->json('status') === 'success' && preg_match('/^[A-Z]{2}$/', $code)
                    ? $code
                    : null;
            } catch (\Throwable) {
                return null; // Fail open if geolocation is unavailable.
            }
        });
    }

    private function isCloudflarePeer(string $ip): bool
    {
        if (!Cache::has('cloudflare_public_ip_cidrs')) {
            try {
                $response = Http::timeout(2)->get('https://api.cloudflare.com/client/v4/ips');
                $result = $response->successful() ? $response->json('result', []) : [];
                $cidrs = array_merge($result['ipv4_cidrs'] ?? [], $result['ipv6_cidrs'] ?? []);
                Cache::put('cloudflare_public_ip_cidrs', $cidrs, now()->addDays($cidrs === [] ? 1 : 7));
            } catch (\Throwable) {
                Cache::put('cloudflare_public_ip_cidrs', [], now()->addMinutes(5));
                return false;
            }
        }
        $cidrs = Cache::get('cloudflare_public_ip_cidrs', []);

        foreach ($cidrs as $cidr) {
            if (is_string($cidr) && $this->ipMatchesCidr($ip, $cidr)) return true;
        }
        return false;
    }

    private function ipMatchesCidr(string $ip, string $cidr): bool
    {
        [$network, $prefix] = array_pad(explode('/', $cidr, 2), 2, null);
        $ipBinary = @inet_pton($ip);
        $networkBinary = @inet_pton((string) $network);
        if ($ipBinary === false || $networkBinary === false || strlen($ipBinary) !== strlen($networkBinary)) return false;
        $prefix = $prefix === null ? strlen($ipBinary) * 8 : (int) $prefix;
        if ($prefix < 0 || $prefix > strlen($ipBinary) * 8) return false;
        $wholeBytes = intdiv($prefix, 8);
        $remainingBits = $prefix % 8;
        if ($wholeBytes > 0 && substr($ipBinary, 0, $wholeBytes) !== substr($networkBinary, 0, $wholeBytes)) return false;
        if ($remainingBits === 0) return true;
        $mask = (0xff << (8 - $remainingBits)) & 0xff;
        return (ord($ipBinary[$wholeBytes]) & $mask) === (ord($networkBinary[$wholeBytes]) & $mask);
    }
}
