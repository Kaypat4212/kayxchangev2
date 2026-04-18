<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SpecialReferralCode;
use App\Services\TelegramService;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => [
                'nullable',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === null || trim((string) $value) === '') {
                        return;
                    }

                    $code = strtoupper(trim((string) $value));
                    $isUserReferralCode = User::whereRaw('UPPER(referral_code) = ?', [$code])->exists();
                    $isSpecialCode = false;
                    if (Schema::hasTable('special_referral_codes')) {
                        $isSpecialCode = SpecialReferralCode::where('code', $code)
                            ->where('is_active', true)
                            ->exists();
                    }

                    if (!$isUserReferralCode && !$isSpecialCode) {
                        $fail('The referral code is invalid or inactive.');
                    }
                },
            ],
        ]);

        $referralCode = strtoupper(trim((string) $request->input('referral_code', '')));

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'referred_by' => $referralCode !== '' ? $referralCode : null,
        ]);

        try {
            $message = "🆕 *New User Registration*\n\n"
                . "👤 Name: {$user->name}\n"
                . "📧 Email: {$user->email}\n"
                . "🆔 User ID: {$user->id}\n"
                . "🕒 Time: " . now()->format('Y-m-d H:i:s');

            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '👁 View User', 'url' => route('admin.users.show', $user)],
                        ['text' => '🧾 View KYC Queue', 'url' => route('admin.kyc')],
                    ],
                ],
            ];

            app(TelegramService::class)->sendToAdminChats($message, $keyboard);
        } catch (\Throwable $e) {
            Log::warning('Failed to send admin Telegram registration alert: ' . $e->getMessage());
        }

        event(new Registered($user));

        Auth::login($user);

        // New users go through the onboarding wizard
        return redirect()->route('onboard');
    }
}
