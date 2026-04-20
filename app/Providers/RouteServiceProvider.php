<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // API: 60 requests/min per user or IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Public pages: 120 requests/min per IP (blocks scrapers / aggressive crawlers)
        RateLimiter::for('web-public', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip())
                ->response(fn() => response()->json(['message' => 'Too many requests. Please slow down.'], 429));
        });

        // Auth endpoints: 10 attempts/min per IP (brute-force protection)
        RateLimiter::for('auth-endpoints', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())
                ->response(fn() => back()->withErrors(['email' => 'Too many attempts. Please wait a moment.']));
        });

        // Newsletter subscribe: 5 per hour per IP (anti-spam)
        RateLimiter::for('newsletter', function (Request $request) {
            return Limit::perHour(5)->by($request->ip())
                ->response(fn() => response()->json(['success' => false, 'message' => 'Too many subscription attempts. Try again later.'], 429));
        });

        // Telegram webhook: 300/min (generous, Telegram can burst)
        RateLimiter::for('telegram-webhook', function (Request $request) {
            return Limit::perMinute(300)->by('telegram');
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
