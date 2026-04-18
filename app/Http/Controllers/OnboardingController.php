<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class OnboardingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Show the onboarding wizard */
    public function show()
    {
        $user = Auth::user();

        // Already onboarded — send to dashboard
        if ($user->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.index', compact('user'));
    }

    /** AJAX: Save the PIN during onboarding (step 2) */
    public function savePin(Request $request)
    {
        $v = Validator::make($request->all(), [
            'pin'     => ['required', 'digits:4'],
            'pin_confirm' => ['required', 'same:pin'],
        ], [
            'pin.digits'         => 'PIN must be exactly 4 digits.',
            'pin_confirm.same'   => 'PINs do not match.',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $user = Auth::user();
        $user->transaction_pin = Hash::make($request->pin);
        $user->pin_attempts = 0;
        $user->save();

        // Mark this session as PIN-verified since they just set it
        session(['pin_verified_at' => now()->timestamp]);

        return response()->json(['success' => true]);
    }

    /** AJAX: Save bank details during onboarding (step 3 — optional) */
    public function saveBank(Request $request)
    {
        $v = Validator::make($request->all(), [
            'bank_name'      => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'size:10'],
            'account_name'   => ['required', 'string', 'max:100'],
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $user = Auth::user();
        $user->bank_name      = $request->bank_name;
        $user->account_number = $request->account_number;
        $user->account_name   = $request->account_name;
        $user->save();

        return response()->json(['success' => true]);
    }

    /** AJAX/POST: Mark onboarding complete */
    public function complete(Request $request)
    {
        $user = Auth::user();
        $user->onboarding_completed = true;
        $user->save();

        if ($request->expectsJson()) {
            return response()->json(['redirect' => route('dashboard')]);
        }

        return redirect()->route('dashboard');
    }
}
