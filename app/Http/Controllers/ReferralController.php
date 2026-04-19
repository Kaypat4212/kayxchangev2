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

        // Paginated referrals for the table
        $referrals = $user->referralsMade()->with('referred')->latest()->paginate(10);

        // Accurate counts — queried directly, not from the paginator
        $totalCount     = $user->referralsMade()->count();
        $completedCount = $user->referralsMade()->where('status', 'completed')->count();
        $pendingCount   = $user->referralsMade()->where('status', 'pending')->count();
        $totalRewards   = $user->referralsMade()->where('status', 'completed')->sum('reward_amount');

        $referralLink = url('/register?ref=' . $user->referral_code);

        return view('referrals.index', compact(
            'user', 'referrals', 'totalCount', 'completedCount', 'pendingCount', 'totalRewards', 'referralLink'
        ));
    }
}