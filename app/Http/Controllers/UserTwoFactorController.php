<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserTwoFactorController extends Controller
{
    public function index()
    {
        return view('profile.two-factor', ['user' => auth()->user(), 'secret' => session('user_2fa_setup_secret')]);
    }

    public function setup()
    {
        $secret = app(\PragmaRX\Google2FAQRCode\Google2FA::class)->generateSecretKey(32);
        session(['user_2fa_setup_secret' => $secret]);
        return redirect()->route('profile.two-factor');
    }

    public function confirm(Request $request)
    {
        $data = $request->validate(['code' => 'required|digits:6']);
        $secret = session('user_2fa_setup_secret');
        if (!$secret || !app(\PragmaRX\Google2FAQRCode\Google2FA::class)->verifyKey($secret, $data['code'])) {
            return back()->withErrors(['code' => 'That authenticator code is not valid. Try again.']);
        }
        auth()->user()->update(['two_factor_secret' => encrypt($secret), 'two_factor_enabled' => true, 'two_factor_confirmed_at' => now()]);
        session()->forget('user_2fa_setup_secret');
        return redirect()->route('profile.two-factor')->with('success', 'Authenticator verification is enabled for your account.');
    }

    public function disable(Request $request)
    {
        $data = $request->validate(['current_password' => 'required|string', 'code' => 'required|digits:6']);
        $user = auth()->user();
        if (!Hash::check($data['current_password'], $user->password)) return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        if (!$user->two_factor_secret || !app(\PragmaRX\Google2FAQRCode\Google2FA::class)->verifyKey(decrypt($user->two_factor_secret), $data['code'])) return back()->withErrors(['code' => 'Authenticator code is incorrect.']);
        $user->update(['two_factor_secret' => null, 'two_factor_enabled' => false, 'two_factor_confirmed_at' => null]);
        return redirect()->route('profile.two-factor')->with('success', 'Two-factor authentication was disabled.');
    }

    public function challenge()
    {
        abort_unless(session()->has('user_2fa_challenge_id'), 403);
        return view('auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $data = $request->validate(['code' => 'required|digits:6']);
        $user = \App\Models\User::find(session('user_2fa_challenge_id'));
        if (!$user || !$user->two_factor_enabled || !$user->two_factor_secret || !app(\PragmaRX\Google2FAQRCode\Google2FA::class)->verifyKey(decrypt($user->two_factor_secret), $data['code'])) {
            return back()->withErrors(['code' => 'The authenticator code is incorrect.']);
        }
        $remember = (bool) session('user_2fa_remember', false);
        session()->forget(['user_2fa_challenge_id', 'user_2fa_remember']);
        Auth::login($user, $remember);
        $request->session()->regenerate();
        return redirect()->intended(\App\Providers\RouteServiceProvider::HOME);
    }
}
