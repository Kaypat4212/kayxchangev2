<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Wallet;

class UserObserver
{
    public function creating(User $user)
    {
        if (!$user->referral_code) {
            $user->referral_code = User::generateReferralCode();
        }
    }

    public function created(User $user)
    {
        // Create wallet for new user
        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0.00,
            'currency' => 'NGN',
        ]);
        $user->update(['balance' => 0]); // Sync with users.balance

        // Process referral reward if referred
        if ($user->referred_by) {
            $referrer = User::where('referral_code', $user->referred_by)->first();
            if ($referrer) {
                Referral::create([
                    'referrer_id' => $referrer->id,
                    'referred_id' => $user->id,
                    'reward_amount' => 500.00,
                    'reward_currency' => 'NGN',
                    'status' => 'completed',
                ]);

                // Credit referrer's wallet
                $referrer->wallet()->increment('balance', 500.00);
            }
        }
    }
}