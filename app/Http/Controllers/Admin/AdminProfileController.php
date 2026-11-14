<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminProfileController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        $twoFactorEnabled = (bool) $admin->two_factor_enabled;
        $pendingSetup = session('2fa_pending_secret')
            ? $this->setupPayload($admin, session('2fa_pending_secret'))
            : null;
        return view('admin.profile', compact('admin', 'twoFactorEnabled', 'pendingSetup'));
    }

    // ── Update email ──────────────────────────────────────────────────────
    public function updateEmail(Request $request)
    {
        $request->validate([
            'email'            => 'required|email|unique:users,email,' . Auth::id(),
            'current_password' => 'required|string',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        Auth::user()->update(['email' => $request->email]);

        // Re-login so the session reflects the new email immediately
        Auth::login(Auth::user()->fresh());

        return back()->with('success_email', 'Email updated successfully.');
    }

    // ── Update password ───────────────────────────────────────────────────
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        Auth::user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success_password', 'Password updated successfully.');
    }

    // ── 2FA: generate secret + show QR ───────────────────────────────────
    public function setup2fa()
    {
        abort_if(Auth::user()->two_factor_enabled, 409, 'Two-factor authentication is already enabled.');
        try {
            $admin  = Auth::user();

            // Keep one pending secret for the whole setup attempt. Regenerating it
            // after a bad code made every subsequent authenticator code unusable.
            $secret = session('2fa_pending_secret') ?: app(\PragmaRX\Google2FAQRCode\Google2FA::class)->generateSecretKey(32);

            // Store temporarily in session — only persisted after verification
            session(['2fa_pending_secret' => $secret]);
            return response()->json($this->setupPayload($admin, $secret));
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => 'Unable to start authenticator setup. Please refresh and try again.'], 500);
        }
    }

    public function cancel2faSetup()
    {
        session()->forget('2fa_pending_secret');
        return redirect()->route('admin.profile.index', ['tab' => '2fa'])->with('success_2fa', 'Authenticator setup was cancelled.');
    }

    // ── 2FA: confirm + enable ─────────────────────────────────────────────
    public function confirm2fa(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $secret = session('2fa_pending_secret');
        if (!$secret) {
            return redirect()->route('admin.profile.index', ['tab' => '2fa'])
                ->withErrors(['code' => 'Setup expired. Start authenticator setup again.'])
                ->with('show_2fa_settings', true);
        }

        if (!$this->verifyTotp($secret, $request->code)) {
            return redirect()->route('admin.profile.index', ['tab' => '2fa'])
                ->withErrors(['code' => 'That code is invalid or expired. Check your device time and try the current six-digit code.'])
                ->with('show_2fa_setup', true);
        }

        Auth::user()->update([
            'two_factor_secret'       => encrypt($secret),
            'two_factor_enabled'      => true,
            'two_factor_confirmed_at' => now(),
        ]);

        session()->forget('2fa_pending_secret');
        return redirect()->route('admin.profile.index', ['tab' => '2fa'])
            ->with('success_2fa', 'Two-factor authentication is enabled. The next admin sign-in will require an authenticator code.')
            ->with('show_2fa_settings', true);
    }

    // ── 2FA: disable ──────────────────────────────────────────────────────
    public function disable2fa(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'code' => 'required|digits:6',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return redirect()->route('admin.profile.index', ['tab' => '2fa'])
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->with('show_2fa_settings', true);
        }

        try {
            $secret = Auth::user()->two_factor_secret ? decrypt(Auth::user()->two_factor_secret) : null;
        } catch (\Throwable $e) {
            report($e);
            $secret = null;
        }

        if (!$secret || !$this->verifyTotp($secret, $request->code)) {
            return redirect()->route('admin.profile.index', ['tab' => '2fa'])
                ->withErrors(['code' => 'The authenticator code is invalid or expired.'])
                ->with('show_2fa_settings', true);
        }

        Auth::user()->update([
            'two_factor_secret'       => null,
            'two_factor_enabled'      => false,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('admin.profile.index', ['tab' => '2fa'])
            ->with('success_2fa', 'Two-factor authentication has been disabled.')
            ->with('show_2fa_settings', true);
    }

    private function setupPayload($admin, string $secret): array
    {
        $appName = config('app.name', 'KayXchange');
        $google2fa = app(\PragmaRX\Google2FAQRCode\Google2FA::class);
        $otpUri = $google2fa->getQRCodeUrl($appName, $admin->email, $secret);
        $qrDataUri = null;
        try {
            $qr = $google2fa->getQRCodeInline($appName, $admin->email, $secret, 220);
            // BaconQrCode returns inline SVG on shared hosting without Imagick.
            // Encode locally so the authenticator secret never reaches a QR CDN.
            if (str_contains($qr, '<svg')) {
                $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($qr);
            } elseif (str_starts_with($qr, 'data:image/')) {
                $qrDataUri = $qr;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return [
            'secret' => $secret,
            'otp_uri' => $otpUri,
            'qr_data_uri' => $qrDataUri,
        ];
    }

    // ── TOTP helpers ──────────────────────────────────────────────────────

    /** Verify a six-digit authenticator code using the same RFC-compliant library as admin login. */
    private function verifyTotp(string $secret, string $code): bool
    {
        return app(\PragmaRX\Google2FAQRCode\Google2FA::class)->verifyKey($secret, $code);
    }
}
