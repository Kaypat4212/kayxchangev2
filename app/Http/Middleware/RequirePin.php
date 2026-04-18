<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\PinController;

/**
 * Require a verified PIN before allowing access to sensitive routes.
 * Users who have not set a PIN are redirected to setup.
 */
class RequirePin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admins bypass PIN
        if ($user->is_admin) {
            return $next($request);
        }

        // No PIN set yet → send to setup
        if (!$user->transaction_pin) {
            return redirect()->route('pin.setup')
                ->with('info', 'Please set up your security PIN before proceeding.');
        }

        // PIN already verified this session?
        if (PinController::isPinVerifiedInSession()) {
            return $next($request);
        }

        // Redirect to PIN verify screen with the intended URL
        $redirect = $request->fullUrl();
        return redirect()->route('pin.verify', ['redirect' => $redirect]);
    }
}
