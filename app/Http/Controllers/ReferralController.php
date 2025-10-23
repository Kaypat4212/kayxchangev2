<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $referrals = $user->referralsMade()->with('referred')->paginate(10);
        $totalRewards = $user->referralsMade()->where('status', 'completed')->sum('reward_amount');
        $referralLink = url('/register?ref=' . $user->referral_code);

        return view('referrals.index', compact('user', 'referrals', 'totalRewards', 'referralLink'));
    }
}