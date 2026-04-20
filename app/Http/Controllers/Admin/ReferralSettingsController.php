<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Referral;
use App\Models\SiteContent;
use App\Models\SpecialReferralCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReferralSettingsController extends Controller
{
    public function referrals(Request $request)
    {
        $status = $request->input('status', 'all');
        $filter = $request->input('filter', 'all'); // all | flagged

        $query = Referral::with([
            'referrer:id,name,email,kyc_verified,registration_ip,phone',
            'referred:id,name,email,kyc_verified,registration_ip,phone',
        ])->orderByDesc('created_at');

        if (in_array($status, ['pending', 'completed'])) {
            $query->where('status', $status);
        }
        if ($filter === 'flagged') {
            $query->where('fraud_flagged', true);
        }

        $referrals = $query->paginate(30)->withQueryString();

        $stats = [
            'total'     => Referral::count(),
            'pending'   => Referral::where('status', 'pending')->count(),
            'completed' => Referral::where('status', 'completed')->count(),
            'paid'      => Referral::where('status', 'completed')->sum('reward_amount'),
            'flagged'   => Referral::where('fraud_flagged', true)->count(),
            'blocked'   => Referral::whereNotNull('blocked_at')->count(),
        ];

        return view('admin.referrals.index', compact('referrals', 'stats', 'status', 'filter'));
    }

    /** Admin: manually block a referral reward */
    public function block(Request $request, Referral $referral): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $referral->update([
            'fraud_flagged' => true,
            'fraud_reason'  => $data['reason'],
            'blocked_at'    => now(),
        ]);

        Log::warning('Referral manually blocked by admin', [
            'referral_id' => $referral->id,
            'reason'      => $data['reason'],
        ]);

        return back()->with('success', 'Referral blocked and reward held.');
    }

    /** Admin: unblock a referral (clear fraud flag, allow reward to fire on next deposit approval) */
    public function unblock(Referral $referral): RedirectResponse
    {
        $referral->update([
            'fraud_flagged' => false,
            'fraud_reason'  => null,
            'blocked_at'    => null,
        ]);

        Log::info('Referral unblocked by admin', ['referral_id' => $referral->id]);

        // If the referred user already meets all criteria, credit the reward now
        $referred = $referral->referred;
        if ($referred && $referred->kyc_verified) {
            $totalDeposited = \App\Models\Deposit::where('user_id', $referred->id)
                ->where('status', 'approved')
                ->sum('amount');

            if ($totalDeposited >= 10000 && $referral->status === 'pending') {
                $reward = (float) SiteContent::get('referral_reward_amount', '500');
                if ($reward > 0 && $referral->referrer) {
                    $referral->update(['status' => 'completed', 'reward_amount' => $reward]);
                    $referral->referrer->increment('balance', $reward);
                    Log::info('Referral reward credited after admin unblock', [
                        'referral_id' => $referral->id,
                        'reward'      => $reward,
                    ]);
                }
            }
        }

        return back()->with('success', 'Referral unblocked. Reward will be processed if all criteria are met.');
    }

    public function index()
    {
        $defaultReward = (float) SiteContent::get('referral_reward_amount', '2000');
        $defaultSignupBonus = (float) SiteContent::get('special_referral_signup_bonus', '1000');

        $codes = SpecialReferralCode::with('owner:id,name,email')
            ->orderByDesc('is_active')
            ->orderBy('code')
            ->get();

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.referrals.settings', compact('defaultReward', 'defaultSignupBonus', 'codes', 'users'));
    }

    public function updateDefaults(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'referral_reward_amount' => ['required', 'numeric', 'min:0'],
            'special_referral_signup_bonus' => ['required', 'numeric', 'min:0'],
        ]);

        SiteContent::updateOrCreate(
            ['key' => 'referral_reward_amount'],
            ['group' => 'referrals', 'label' => 'Referral Reward Amount (NGN)', 'value' => (string) $data['referral_reward_amount']]
        );

        SiteContent::updateOrCreate(
            ['key' => 'special_referral_signup_bonus'],
            ['group' => 'referrals', 'label' => 'Special Referral Signup Bonus (NGN)', 'value' => (string) $data['special_referral_signup_bonus']]
        );

        return back()->with('success', 'Referral defaults updated successfully.');
    }

    public function storeCode(Request $request): RedirectResponse
    {
        $data = $this->validateCode($request);
        $data['is_active'] = $request->boolean('is_active');

        SpecialReferralCode::create($data);

        return back()->with('success', 'Special referral code created successfully.');
    }

    public function updateCode(Request $request, SpecialReferralCode $specialReferralCode): RedirectResponse
    {
        $data = $this->validateCode($request, $specialReferralCode->id);
        $data['is_active'] = $request->boolean('is_active');

        $specialReferralCode->update($data);

        return back()->with('success', 'Special referral code updated successfully.');
    }

    public function destroyCode(SpecialReferralCode $specialReferralCode): RedirectResponse
    {
        $specialReferralCode->delete();

        return back()->with('success', 'Special referral code deleted successfully.');
    }

    private function validateCode(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:special_referral_codes,code';
        if ($ignoreId) {
            $uniqueRule .= ',' . $ignoreId;
        }

        return $request->validate([
            'code' => ['required', 'string', 'max:50', $uniqueRule],
            'label' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'in:ambassador,influencer,partner'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'referrer_reward' => ['nullable', 'numeric', 'min:0'],
            'signup_bonus' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
