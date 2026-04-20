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
        return view('admin.profile', compact('admin', 'twoFactorEnabled'));
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
        $admin  = Auth::user();
        $secret = $this->generateTotpSecret();

        // Store temporarily in session — only persisted after verification
        session(['2fa_pending_secret' => $secret]);

        $appName = config('app.name', 'KayXchange');
        $email   = rawurlencode($admin->email);
        $otpUri  = "otpauth://totp/{$appName}:{$email}?secret={$secret}&issuer=" . rawurlencode($appName) . "&algorithm=SHA1&digits=6&period=30";

        // Use free qrserver.com API — no library required
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode($otpUri);

        return response()->json([
            'secret' => $secret,
            'qr_url' => $qrUrl,
            'otp_uri' => $otpUri,
        ]);
    }

    // ── 2FA: confirm + enable ─────────────────────────────────────────────
    public function confirm2fa(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $secret = session('2fa_pending_secret');
        if (!$secret) {
            return back()->withErrors(['code' => 'Session expired. Please restart 2FA setup.']);
        }

        if (!$this->verifyTotp($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.'])->with('show_2fa_setup', true);
        }

        Auth::user()->update([
            'two_factor_secret'       => encrypt($secret),
            'two_factor_enabled'      => true,
            'two_factor_confirmed_at' => now(),
        ]);

        session()->forget('2fa_pending_secret');
        return back()->with('success_2fa', '2FA enabled successfully. Your account is now protected.');
    }

    // ── 2FA: disable ──────────────────────────────────────────────────────
    public function disable2fa(Request $request)
    {
        $request->validate(['current_password' => 'required|string']);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        Auth::user()->update([
            'two_factor_secret'       => null,
            'two_factor_enabled'      => false,
            'two_factor_confirmed_at' => null,
        ]);

        return back()->with('success_2fa', '2FA has been disabled.');
    }

    // ── TOTP helpers ──────────────────────────────────────────────────────

    /**
     * Generate a cryptographically random Base32 secret (16 chars = 80 bits).
     */
    private function generateTotpSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        $bytes  = random_bytes(20);
        for ($i = 0; $i < 20; $i++) {
            $secret .= $chars[ord($bytes[$i]) & 0x1F];
        }
        return $secret;
    }

    /**
     * Verify a 6-digit TOTP code against a Base32 secret.
     * Accepts ±1 time-step (30s window) to allow for clock drift.
     */
    private function verifyTotp(string $secret, string $code): bool
    {
        $key = $this->base32Decode($secret);
        $time = (int) floor(time() / 30);

        foreach ([-1, 0, 1] as $offset) {
            $computed = $this->computeHotp($key, $time + $offset);
            if (hash_equals((string) $computed, (string) $code)) {
                return true;
            }
        }
        return false;
    }

    private function computeHotp(string $key, int $counter): string
    {
        $data = pack('J', $counter); // 8-byte big-endian
        $hmac = hash_hmac('sha1', $data, $key, true);
        $offset = ord($hmac[strlen($hmac) - 1]) & 0x0F;
        $code = (
            ((ord($hmac[$offset])     & 0x7F) << 24) |
            ((ord($hmac[$offset + 1]) & 0xFF) << 16) |
            ((ord($hmac[$offset + 2]) & 0xFF) <<  8) |
             (ord($hmac[$offset + 3]) & 0xFF)
        ) % 1_000_000;
        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $b32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output   = '';
        $b32      = strtoupper(rtrim($b32, '='));
        $buffer   = 0;
        $bits     = 0;
        foreach (str_split($b32) as $char) {
            $val = strpos($alphabet, $char);
            if ($val === false) continue;
            $buffer = ($buffer << 5) | $val;
            $bits  += 5;
            if ($bits >= 8) {
                $bits  -= 8;
                $output .= chr(($buffer >> $bits) & 0xFF);
            }
        }
        return $output;
    }
}
