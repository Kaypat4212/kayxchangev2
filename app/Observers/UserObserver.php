<?php

namespace App\Observers;

use App\Models\Referral;
use App\Models\SiteContent;
use App\Models\SpecialReferralCode;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Schema;

class UserObserver
{
    public function creating(User $user)
    {
        if (!$user->referral_code) {
            $user->referral_code = User::generateReferralCode();
        }

        if (!$user->kx_tag) {
            $user->kx_tag = User::generateKxTag();
        }
    }

    public function created(User $user)
    {
        // Create NGN wallet for new user
        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0.00,
            'currency' => 'NGN',
        ]);
        $user->update(['balance' => 0]); // Sync with users.balance

        // Create crypto wallets for new user
        $cryptoWalletService = app(\App\Services\CryptoWalletService::class);
        $cryptoWalletService->createUserCryptoWallets($user);

        // Process referral — create as PENDING; reward credited after referred user deposits ₦10,000
        if ($user->referred_by) {
            $code = strtoupper(trim((string) $user->referred_by));
            $defaultReferrerReward = 0.00;
            $defaultSignupBonus = 0.00;
            if (Schema::hasTable('site_contents')) {
                $defaultReferrerReward = (float) SiteContent::get('referral_reward_amount', '500');
                $defaultSignupBonus = (float) SiteContent::get('special_referral_signup_bonus', '0');
            }

            $specialCode = null;
            if (Schema::hasTable('special_referral_codes')) {
                $specialCode = SpecialReferralCode::whereRaw('UPPER(code) = ?', [$code])
                    ->where('is_active', true)
                    ->first();
            }

            if ($specialCode) {
                $reward = $specialCode->referrer_reward !== null
                    ? (float) $specialCode->referrer_reward
                    : $defaultReferrerReward;

                if ($specialCode->owner_user_id && $specialCode->owner_user_id !== $user->id) {
                    $referrer = User::find($specialCode->owner_user_id);

                    if ($referrer) {
                        // Create pending — credit only after ₦10,000 deposit
                        Referral::create([
                            'referrer_id'     => $referrer->id,
                            'referred_id'     => $user->id,
                            'reward_amount'   => $reward,
                            'reward_currency' => 'NGN',
                            'status'          => 'pending',
                        ]);
                    }
                }

                $signupBonus = $specialCode->signup_bonus !== null
                    ? (float) $specialCode->signup_bonus
                    : $defaultSignupBonus;

                if ($signupBonus > 0) {
                    $this->creditWallet($user, $signupBonus);
                }

                return;
            }

            $referrer = User::whereRaw('UPPER(referral_code) = ?', [$code])->first();
            if ($referrer && $referrer->id !== $user->id) {
                // Create pending referral — reward credited after ₦10,000 deposit
                Referral::firstOrCreate(
                    ['referrer_id' => $referrer->id, 'referred_id' => $user->id],
                    [
                        'reward_amount'   => $defaultReferrerReward,
                        'reward_currency' => 'NGN',
                        'status'          => 'pending',
                    ]
                );
            }
        }
    }

    private function creditWallet(User $user, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $wallet = $user->wallet;
        if (!$wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0.00,
                'currency' => 'NGN',
            ]);
        }

        $wallet->increment('balance', $amount);
        $user->increment('balance', $amount);
    }
}