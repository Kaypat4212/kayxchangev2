<?php

namespace App\Http\Middleware;

use App\Models\Blacklist;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBlacklist
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        // Check IP blacklist
        if (Blacklist::isIpBlocked($ip)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied.'], 403);
            }
            return response()->view('errors.blacklisted', [], 403);
        }

        // Check user blacklist (if logged in)
        if (Auth::check() && Blacklist::isUserBlocked(Auth::id())) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your account has been suspended.'], 403);
            }
            return redirect()->route('login')->withErrors(['email' => 'Your account has been suspended. Please contact support.']);
        }

        return $next($request);
    }
}
