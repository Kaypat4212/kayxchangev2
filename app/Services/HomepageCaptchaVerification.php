<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomepageCaptchaVerification
{
    private const TTL_SECONDS = 86400;
    private const SESSION_KEYS = [
        'homepage_human_verified_at',
        'homepage_human_verified_mode',
        'homepage_human_verified_ip_hash',
    ];

    public function markVerified(Request $request, string $mode): void
    {
        $ipHash = $this->ipHash($request);
        if ($ipHash === null) return;

        $verifiedAt = now()->timestamp;
        $record = ['mode' => $mode, 'ip_hash' => $ipHash, 'verified_at' => $verifiedAt];
        Cache::put($this->cacheKey($mode, $ipHash), $record, now()->addSeconds(self::TTL_SECONDS));
        $request->session()->forget('homepage_captcha_ip_changed');
        $request->session()->put([
            'homepage_human_verified_at' => $verifiedAt,
            'homepage_human_verified_mode' => $mode,
            'homepage_human_verified_ip_hash' => $ipHash,
        ]);
    }

    public function isVerified(Request $request, string $mode): bool
    {
        $ipHash = $this->ipHash($request);
        if ($ipHash === null) {
            $request->session()->forget(self::SESSION_KEYS);
            return false;
        }

        $sessionMode = (string) $request->session()->get('homepage_human_verified_mode', '');
        $sessionIpHash = (string) $request->session()->get('homepage_human_verified_ip_hash', '');
        $sessionVerifiedAt = (int) $request->session()->get('homepage_human_verified_at', 0);
        if ($this->isFresh($sessionVerifiedAt) && $sessionMode === $mode && hash_equals($ipHash, $sessionIpHash)) {
            return true;
        }

        if ($this->isFresh($sessionVerifiedAt)
            && $sessionMode === $mode
            && $sessionIpHash !== ''
            && !hash_equals($ipHash, $sessionIpHash)) {
            $request->session()->put('homepage_captcha_ip_changed', true);
        }

        // Reuse a valid verification from another session on the same client IP.
        // This keeps the 24-hour rule effective after session cookies expire or change.
        $cached = Cache::get($this->cacheKey($mode, $ipHash));
        if (is_array($cached)
            && ($cached['mode'] ?? null) === $mode
            && is_string($cached['ip_hash'] ?? null)
            && hash_equals($ipHash, $cached['ip_hash'])
            && $this->isFresh((int) ($cached['verified_at'] ?? 0))) {
            $request->session()->put([
                'homepage_human_verified_at' => (int) $cached['verified_at'],
                'homepage_human_verified_mode' => $mode,
                'homepage_human_verified_ip_hash' => $ipHash,
            ]);
            return true;
        }

        $request->session()->forget(self::SESSION_KEYS);
        return false;
    }

    private function isFresh(int $verifiedAt): bool
    {
        return $verifiedAt > 0 && now()->timestamp >= $verifiedAt && now()->timestamp - $verifiedAt < self::TTL_SECONDS;
    }

    private function ipHash(Request $request): ?string
    {
        $ip = trim((string) $request->ip());
        return $ip === '' ? null : hash('sha256', $ip);
    }

    private function cacheKey(string $mode, string $ipHash): string
    {
        return 'homepage_captcha.verified.' . $mode . '.' . $ipHash;
    }
}
