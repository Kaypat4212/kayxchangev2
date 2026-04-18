<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class PinController extends Controller
{
    /** How long a verified PIN session lasts (minutes) */
    const SESSION_MINUTES = 30;

    /** Max wrong attempts before lockout */
    const MAX_ATTEMPTS = 5;

    /** Lockout duration in minutes */
    const LOCKOUT_MINUTES = 15;

    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─────────────────────────────────────────
    // Verify PIN (prompt screen)
    // ─────────────────────────────────────────

    /** Show the PIN entry screen */
    public function showVerify(Request $request)
    {
        $user = Auth::user();

        // No PIN set — send to setup
        if (!$user->transaction_pin) {
            return redirect()->route('pin.setup')->with('info', 'Please set up your PIN first.');
        }

        // Already verified this session — go to intended
        if ($this->isPinVerifiedInSession()) {
            return redirect($request->get('redirect', route('dashboard')));
        }

        $redirect = $request->get('redirect', route('dashboard'));
        return view('pin.verify', compact('redirect', 'user'));
    }

    /** Handle PIN verification attempt */
    public function verify(Request $request)
    {
        $request->validate(['pin' => 'required|digits:4', 'redirect' => 'nullable|string']);

        $user = Auth::user();

        if (!$user->transaction_pin) {
            return back()->withErrors(['pin' => 'No PIN set. Please set up your PIN.']);
        }

        // Check lockout
        if ($user->pin_locked_until && Carbon::now()->lt($user->pin_locked_until)) {
            $remaining = (int) Carbon::now()->diffInMinutes($user->pin_locked_until, false);
            return back()->withErrors(['pin' => "PIN locked. Try again in {$remaining} minute(s)."]);
        }

        if (Hash::check($request->pin, $user->transaction_pin)) {
            // Success — mark session verified
            $user->pin_attempts = 0;
            $user->pin_locked_until = null;
            $user->save();

            session(['pin_verified_at' => now()->timestamp]);

            $redirect = $request->get('redirect', route('dashboard'));
            return redirect($redirect);
        }

        // Wrong PIN
        $user->pin_attempts = ($user->pin_attempts ?? 0) + 1;

        if ($user->pin_attempts >= self::MAX_ATTEMPTS) {
            $user->pin_locked_until = Carbon::now()->addMinutes(self::LOCKOUT_MINUTES);
            $user->save();
            return back()->withErrors(['pin' => 'Too many wrong attempts. PIN locked for ' . self::LOCKOUT_MINUTES . ' minutes.']);
        }

        $remaining = self::MAX_ATTEMPTS - $user->pin_attempts;
        $user->save();

        return back()->withErrors(['pin' => "Incorrect PIN. {$remaining} attempt(s) remaining."])
                     ->withInput($request->only('redirect'));
    }

    // ─────────────────────────────────────────
    // Setup PIN (first time)
    // ─────────────────────────────────────────

    public function showSetup()
    {
        return view('pin.setup', ['user' => Auth::user()]);
    }

    public function setup(Request $request)
    {
        $v = Validator::make($request->all(), [
            'pin'         => ['required', 'digits:4'],
            'pin_confirm' => ['required', 'same:pin'],
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $user = Auth::user();
        $user->transaction_pin = Hash::make($request->pin);
        $user->pin_attempts    = 0;
        $user->pin_locked_until = null;
        $user->save();

        session(['pin_verified_at' => now()->timestamp]);

        return redirect()->route('dashboard')->with('success', 'PIN set successfully!');
    }

    // ─────────────────────────────────────────
    // Change PIN (from settings)
    // ─────────────────────────────────────────

    public function showChange()
    {
        return view('pin.change', ['user' => Auth::user()]);
    }

    public function change(Request $request)
    {
        $user = Auth::user();
        $hasPinAlready = (bool) $user->transaction_pin;

        $rules = [
            'new_pin'     => ['required', 'digits:4'],
            'pin_confirm' => ['required', 'same:new_pin'],
        ];

        if ($hasPinAlready) {
            $rules['current_pin'] = ['required', 'digits:4'];
        }

        $v = Validator::make($request->all(), $rules, [
            'new_pin.digits'      => 'New PIN must be 4 digits.',
            'pin_confirm.same'    => 'PIN confirmation does not match.',
            'current_pin.required'=> 'Current PIN is required.',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        if ($hasPinAlready && !Hash::check($request->current_pin, $user->transaction_pin)) {
            return back()->withErrors(['current_pin' => 'Current PIN is incorrect.'])->withInput();
        }

        // Prevent re-use of same PIN
        if ($hasPinAlready && Hash::check($request->new_pin, $user->transaction_pin)) {
            return back()->withErrors(['new_pin' => 'New PIN must be different from current PIN.'])->withInput();
        }

        $user->transaction_pin  = Hash::make($request->new_pin);
        $user->pin_attempts     = 0;
        $user->pin_locked_until = null;
        $user->save();

        session(['pin_verified_at' => now()->timestamp]);

        return redirect()->route('settings.index')->with('success', 'PIN updated successfully!');
    }

    // ─────────────────────────────────────────
    // AJAX verify (for inline PIN modal on withdraw)
    // ─────────────────────────────────────────

    public function ajaxVerify(Request $request)
    {
        $request->validate(['pin' => 'required|digits:4']);

        $user = Auth::user();

        if (!$user->transaction_pin) {
            return response()->json(['error' => 'No PIN set.'], 422);
        }

        if ($user->pin_locked_until && Carbon::now()->lt($user->pin_locked_until)) {
            $remaining = (int) Carbon::now()->diffInMinutes($user->pin_locked_until, false);
            return response()->json(['error' => "PIN locked. Try again in {$remaining} minute(s)."], 423);
        }

        if (Hash::check($request->pin, $user->transaction_pin)) {
            $user->pin_attempts = 0;
            $user->pin_locked_until = null;
            $user->save();
            session(['pin_verified_at' => now()->timestamp]);
            return response()->json(['success' => true]);
        }

        $user->pin_attempts = ($user->pin_attempts ?? 0) + 1;
        if ($user->pin_attempts >= self::MAX_ATTEMPTS) {
            $user->pin_locked_until = Carbon::now()->addMinutes(self::LOCKOUT_MINUTES);
        }
        $user->save();

        $remaining = max(0, self::MAX_ATTEMPTS - $user->pin_attempts);
        return response()->json(['error' => "Incorrect PIN. {$remaining} attempt(s) remaining."], 422);
    }

    // ─────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────

    public static function isPinVerifiedInSession(): bool
    {
        $ts = session('pin_verified_at');
        if (!$ts) return false;
        return (now()->timestamp - $ts) < (self::SESSION_MINUTES * 60);
    }
}
