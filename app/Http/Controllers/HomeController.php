<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $this->notifyNewVisitor($request);
        return view('index');
    }

    private function notifyNewVisitor(Request $request): void
    {
        $ip = $request->ip();

        // Deduplicate: only alert once per IP per 6 hours
        $cacheKey = 'visitor_notified_' . md5($ip);
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, true, now()->addHours(6));

        try {
            $token  = AdminSetting::get('telegram_token') ?: env('KAYXCHANGE_TELEGRAM_BOT_TOKEN');
            $chatId = AdminSetting::get('telegram_owner_chat_id') ?: env('TELEGRAM_CHAT_ID') ?: env('KAYXCHANGE_TELEGRAM_CHAT_ID');

            if (! $token || ! $chatId) {
                return;
            }

            $userAgent = $request->userAgent() ?? 'Unknown';
            $referer   = $request->headers->get('referer', 'Direct visit');
            $time      = now()->format('d M Y, H:i:s');

            // Condense user-agent to a readable label
            $device = $this->parseDevice($userAgent);

            $message = "👁 *New Website Visitor*\n\n"
                . "🌐 IP: `{$ip}`\n"
                . "📱 Device: {$device}\n"
                . "🔗 Referrer: {$referer}\n"
                . "🕒 Time: {$time}\n\n"
                . "_Visited the KayXchange home page_";

            Http::timeout(6)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Throwable $e) {
            Log::info('HomeController visitor notify skipped: ' . $e->getMessage());
        }
    }

    private function parseDevice(string $ua): string
    {
        $ua = strtolower($ua);

        // OS
        if (str_contains($ua, 'android'))       $os = 'Android';
        elseif (str_contains($ua, 'iphone'))    $os = 'iPhone';
        elseif (str_contains($ua, 'ipad'))      $os = 'iPad';
        elseif (str_contains($ua, 'windows'))   $os = 'Windows';
        elseif (str_contains($ua, 'macintosh')) $os = 'Mac';
        elseif (str_contains($ua, 'linux'))     $os = 'Linux';
        else                                    $os = 'Unknown OS';

        // Browser
        if (str_contains($ua, 'edg/'))          $browser = 'Edge';
        elseif (str_contains($ua, 'opr/'))      $browser = 'Opera';
        elseif (str_contains($ua, 'chrome/'))   $browser = 'Chrome';
        elseif (str_contains($ua, 'firefox/'))  $browser = 'Firefox';
        elseif (str_contains($ua, 'safari/'))   $browser = 'Safari';
        else                                    $browser = 'Unknown Browser';

        return "{$browser} on {$os}";
    }
}
