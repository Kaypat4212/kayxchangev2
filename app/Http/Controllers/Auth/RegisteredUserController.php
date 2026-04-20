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
        // ── Normalise email before validation to block alias tricks ───────────
        // Gmail ignores dots and allows +tags — normalise so duplicates are caught.
        $rawEmail = strtolower(trim((string) $request->input('email', '')));
        $normalised = self::normaliseEmail($rawEmail);
        // Merge normalised value back so the unique-DB check uses it
        $request->merge(['email' => $normalised]);

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\']+$/u'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => [
                'required',
                'string',
                'regex:/^(\+?234|0)[7-9][01]\d{8}$/',
                'unique:'.User::class.',phone',
            ],
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
        ], [
            'name.regex'   => 'Please enter your real legal name (letters only — no numbers or special characters).',
            'phone.required' => 'A phone number is required to create an account.',
            'phone.regex'    => 'Please enter a valid Nigerian phone number (e.g. 08012345678 or +2348012345678).',
            'phone.unique'   => 'This phone number is already linked to an account. Each phone number can only be used once.',
        ]);

        $referralCode = strtoupper(trim((string) $request->input('referral_code', '')));

        $user = User::create([
            'name'        => $request->name,
            'email'           => $request->email,
            'phone'           => preg_replace('/\D/', '', $request->phone), // store digits only
            'password'        => Hash::make($request->password),
            'referred_by'     => $referralCode !== '' ? $referralCode : null,
            'registration_ip' => $request->ip(),
        ]);

        // Create Referral record if a valid referrer exists
        // NOTE: UserObserver also handles this via firstOrCreate — this is a safety net
        // for cases where the observer may not fire (e.g. User::create bypasses some events).
        if ($referralCode !== '') {
            $referrer = User::whereRaw('UPPER(referral_code) = ?', [$referralCode])->first();
            if ($referrer) {
                \App\Models\Referral::firstOrCreate(
                    ['referrer_id' => $referrer->id, 'referred_id' => $user->id],
                    [
                        'reward_amount'   => (float) \App\Models\SiteContent::get('referral_reward_amount', '500'),
                        'reward_currency' => 'NGN',
                        'status'          => 'pending',
                    ]
                );
            }
        }

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

    /**
     * Normalise an email address to prevent alias/dot tricks.
     * - Gmail: strip dots from local part, remove +tags
     * - All providers: lowercase, trim
     */
    private static function normaliseEmail(string $email): string
    {
        $email = strtolower(trim($email));
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if (!$domain) return $email;

        // Gmail-specific normalisation (also covers googlemail.com)
        if (in_array($domain, ['gmail.com', 'googlemail.com'], true)) {
            // Remove everything after + (alias)
            $local = explode('+', $local)[0];
            // Remove dots (Gmail ignores them)
            $local = str_replace('.', '', $local);
            // Canonical domain
            $domain = 'gmail.com';
        }

        return $local . '@' . $domain;
    }
}
