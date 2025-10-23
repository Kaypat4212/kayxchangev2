<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        if (!Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'You do not have admin privileges.');
        }

        return $next($request);
    }
}


