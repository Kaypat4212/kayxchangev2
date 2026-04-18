<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Redirect newly-registered users to the onboarding wizard
 * until they complete it.
 */
class CheckOnboarding
{
    /** Routes that should be exempt from the onboarding redirect */
    protected array $except = [
        'onboard*',
        'logout',
        'login',
        'register',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip for admin accounts
        if ($user->is_admin) {
            return $next($request);
        }

        // Skip exempt routes
        if ($request->routeIs(...$this->except)) {
            return $next($request);
        }

        if (!$user->onboarding_completed) {
            return redirect()->route('onboard');
        }

        return $next($request);
    }
}
