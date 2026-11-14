<?php

namespace App\Services;

use App\Models\Referral;
use Illuminate\Support\Facades\DB;

class ReferralRewardService
{
    public function settle(int $referralId, float $amount, string $source = 'referral.reward'): bool
    {
        if ($amount <= 0) return false;
        return DB::transaction(function () use ($referralId, $amount, $source): bool {
            $referral = Referral::query()->lockForUpdate()->findOrFail($referralId);
            if ($referral->status !== 'pending') return false;
            $referral->status = 'completed';
            $referral->reward_amount = round($amount, 2);
            $referral->save();
            app(BalanceSettlementService::class)->creditNgnBalance(
                (int) $referral->referrer_id, $amount, $source, 'referral:' . $referral->id
            );
            return true;
        });
    }
}
