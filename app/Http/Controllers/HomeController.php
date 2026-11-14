<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return $this->showLanding($request, 'index');
    }

    public function secondary(Request $request)
    {
        return $this->showLanding($request, 'home');
    }

    private function showLanding(Request $request, string $view)
    {
        $mode = $this->captchaMode();
        if ($mode !== 'off' && !$this->isHumanVerified($request, $mode)) {
            $request->session()->put('homepage_captcha_redirect', $view === 'home' ? '/home' : '/');
            $existingChallenge = $request->session()->get('homepage_captcha', []);
            if ($mode === 'custom' && !in_array($existingChallenge['type'] ?? '', ['math', 'symbol', 'sequence'], true)) {
                $request->session()->put('homepage_captcha', $this->makeCustomCaptcha());
            }

            $siteKey = (string) AdminSetting::getSetting('turnstile_site_key', '');
            $secret = (string) AdminSetting::getSetting('turnstile_secret_key', '');
            return response()->view('captcha.homepage', [
                'mode' => $mode,
                'challenge' => $request->session()->get('homepage_captcha', []),
                'siteKey' => $siteKey,
                'providerConfigured' => $mode !== 'turnstile' || ($siteKey !== '' && $secret !== ''),
            ], 200)->header('Cache-Control', 'private, no-store, max-age=0');
        }

        $data = $view === 'home' ? ['blogPosts' => \App\Models\BlogPost::published()->limit(8)->get()] : [];
        return response()->view($view, $data)->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function verifyCaptcha(Request $request)
    {
        $mode = $this->captchaMode();
        if (!in_array($mode, ['custom', 'turnstile'], true)) return redirect()->route('home');

        if ($mode === 'custom') {
            $request->validate(['captcha_answer' => ['required', 'string', 'max:12']]);
            $expected = (string) $request->session()->get('homepage_captcha.answer', '');
            $provided = trim((string) $request->input('captcha_answer'));
            if ($expected === '' || !hash_equals($expected, $provided)) {
                $request->session()->forget('homepage_captcha');
                return redirect()->route('home')->withErrors(['captcha_answer' => 'That answer did not match. Please try the new question.']);
            }
        } else {
            $secret = (string) AdminSetting::getSetting('turnstile_secret_key', '');
            $siteKey = (string) AdminSetting::getSetting('turnstile_site_key', '');
            $token = (string) $request->input('cf-turnstile-response', '');
            if ($secret === '' || $siteKey === '' || $token === '') {
                return redirect()->route('home')->withErrors(['captcha' => 'Human verification is temporarily unavailable. Please contact support.']);
            }
            try {
                $response = Http::asForm()->timeout(8)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);
                $verifiedHost = strtolower((string) $response->json('hostname', ''));
                $expectedHost = strtolower($request->getHost());
                if (!$response->successful() || !$response->json('success') || $verifiedHost === '' || !hash_equals($expectedHost, $verifiedHost)) {
                    return redirect()->route('home')->withErrors(['captcha' => 'Cloudflare could not verify this request. Please try again.']);
                }
            } catch (\Throwable $e) {
                Log::warning('Homepage Turnstile verification request failed.', ['error' => $e->getMessage()]);
                return redirect()->route('home')->withErrors(['captcha' => 'Human verification is temporarily unavailable. Please try again shortly.']);
            }
        }

        $request->session()->forget('homepage_captcha');
        $request->session()->put('homepage_human_verified_at', now()->timestamp);
        $request->session()->put('homepage_human_verified_mode', $mode);
        $target = $request->session()->pull('homepage_captcha_redirect', '/');
        return redirect(in_array($target, ['/', '/home'], true) ? $target : '/');
    }

    private function captchaMode(): string
    {
        $mode = (string) AdminSetting::getSetting('homepage_captcha_mode', 'custom');
        return in_array($mode, ['off', 'custom', 'turnstile'], true) ? $mode : 'custom';
    }

    private function makeCustomCaptcha(): array
    {
        $type = random_int(0, 2);
        if ($type === 0) {
            $left = random_int(4, 18);
            $right = random_int(2, 9);
            $operation = random_int(0, 1) === 1 && $left > $right ? '-' : '+';
            return [
                'type' => 'math',
                'prompt' => 'Solve this quick number check',
                'question' => "{$left} {$operation} {$right} = ?",
                'answer' => (string) ($operation === '+' ? $left + $right : $left - $right),
            ];
        }

        if ($type === 1) {
            $icons = ['🍋', '🍀', '🪙', '🌙', '🔷', '⚡', '🍉', '⭐', '🧊', '🎈', '🪴', '🧿'];
            $target = $icons[array_rand($icons)];
            $choices = array_merge([$target], array_values(array_diff($icons, [$target])));
            $choices = array_slice($choices, 0, 6);
            shuffle($choices);
            return [
                'type' => 'symbol',
                'prompt' => 'Tap the matching symbol',
                'instruction' => "Select the {$target} symbol below.",
                'target' => $target,
                'choices' => $choices,
                'answer' => (string) array_search($target, $choices, true),
            ];
        }

        $start = random_int(2, 9);
        $step = random_int(2, 7);
        $sequence = [];
        for ($i = 0; $i < 5; $i++) $sequence[] = $start + ($step * $i);
        return [
            'type' => 'sequence',
            'prompt' => 'Find the next number',
            'sequence' => $sequence,
            'answer' => (string) ($start + ($step * 5)),
        ];
    }

    private function isHumanVerified(Request $request, string $mode): bool
    {
        $verifiedAt = (int) $request->session()->get('homepage_human_verified_at', 0);
        $verifiedMode = (string) $request->session()->get('homepage_human_verified_mode', '');
        if ($verifiedAt <= 0 || $verifiedMode !== $mode || now()->timestamp - $verifiedAt > 43200) {
            $request->session()->forget(['homepage_human_verified_at', 'homepage_human_verified_mode']);
            return false;
        }
        return true;
    }
}
