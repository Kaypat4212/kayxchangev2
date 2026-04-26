<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Services\BadgeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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

    /** GET /onboard/banks — return Paystack bank list (cached 6h) */
    public function getBanks()
    {
        $key = config('paystack.secret_key') ?: env('PAYSTACK_SECRET_KEY');

        $banks = Cache::remember('paystack_banks_ng', 21600, function () use ($key) {
            try {
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $key])
                    ->timeout(10)
                    ->get('https://api.paystack.co/bank', [
                        'country'    => 'nigeria',
                        'perPage'    => 200,
                        'use_cursor' => false,
                    ]);

                if ($response->successful()) {
                    $data = $response->json('data', []);
                    usort($data, fn($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
                    return $data ?: null; // don't cache empty
                }

                Log::warning('OnboardingController::getBanks Paystack non-200', [
                    'status' => $response->status(),
                ]);
                return null; // don't cache failures
            } catch (\Throwable $e) {
                Log::warning('OnboardingController::getBanks failed: ' . $e->getMessage());
                return null;
            }
        });

        if (empty($banks)) {
            Cache::forget('paystack_banks_ng');
            return response()->json([], 503);
        }

        return response()->json($banks);
    }

    /** GET /onboard/resolve-account?account_number=xxx&bank_code=xxx */
    public function resolveAccount(Request $request)
    {
        $request->validate([
            'account_number' => ['required', 'string', 'size:10'],
            'bank_code'      => ['required', 'string'],
        ]);

        try {
            $response = Http::withToken(config('paystack.secret_key'))
                ->get('https://api.paystack.co/bank/resolve', [
                    'account_number' => $request->account_number,
                    'bank_code'      => $request->bank_code,
                ]);

            if ($response->successful() && $response->json('status')) {
                return response()->json([
                    'success'      => true,
                    'account_name' => $response->json('data.account_name'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Could not verify account. Please check the account number and bank.',
            ], 422);
        } catch (\Throwable $e) {
            Log::warning('Paystack account resolve failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Bank verification service unavailable. Try again.',
            ], 503);
        }
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

        // Award PIN badge
        try { app(BadgeService::class)->checkAndAward($user, 'pin_set'); } catch (\Throwable) {}

        return response()->json(['success' => true]);
    }

    /** AJAX: Save bank details during onboarding (step 3 — optional) */
    public function saveBank(Request $request)
    {
        $v = Validator::make($request->all(), [
            'bank_name'      => ['required', 'string', 'max:100'],
            'bank_code'      => ['required', 'string', 'max:20'],
            'account_number' => ['required', 'string', 'size:10'],
            'account_name'   => ['required', 'string', 'max:100'],
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        // Re-verify account with Paystack before saving
        try {
            $response = Http::withToken(config('paystack.secret_key'))
                ->get('https://api.paystack.co/bank/resolve', [
                    'account_number' => $request->account_number,
                    'bank_code'      => $request->bank_code,
                ]);

            if (!$response->successful() || !$response->json('status')) {
                return response()->json([
                    'errors' => ['account_number' => ['Account verification failed. Please check your details.']]
                ], 422);
            }

            // Use the verified account name from Paystack
            $verifiedName = $response->json('data.account_name');
        } catch (\Throwable $e) {
            Log::warning('Paystack verification failed on bank save: ' . $e->getMessage());
            // Allow save to proceed if Paystack is unreachable (don't block users)
            $verifiedName = $request->account_name;
        }

        $user = Auth::user();
        $user->bank_name      = $request->bank_name;
        $user->account_number = $request->account_number;
        $user->account_name   = $verifiedName;
        $user->save();

        // Award bank badge
        try { app(BadgeService::class)->checkAndAward($user, 'bank_added'); } catch (\Throwable) {}

        return response()->json(['success' => true, 'account_name' => $verifiedName]);
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
