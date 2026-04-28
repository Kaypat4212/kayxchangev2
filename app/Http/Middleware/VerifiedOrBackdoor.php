<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifiedOrBackdoor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\Http\Foundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow access if user is verified OR if this is a backdoor session (admin logged in as user)
        if (Auth::check() && (Auth::user()->hasVerifiedEmail() || $request->session()->has('admin_id'))) {
            return $next($request);
        }

        // If not verified and not a backdoor session, redirect to email verification
        return redirect()->route('verification.notice');
    }
}
