<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Models\SpecialReferralCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferralSettingsController extends Controller
{
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
