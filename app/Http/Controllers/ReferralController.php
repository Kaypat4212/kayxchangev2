<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        /** @var \App\Models\User $user */
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

    public function leaderboard()
    {
        $leaders = DB::table('referrals')
            ->select('referrer_id',
                DB::raw('COUNT(*) as total_referrals'),
                DB::raw('SUM(CASE WHEN status="completed" THEN 1 ELSE 0 END) as completed_referrals'),
                DB::raw('SUM(CASE WHEN status="completed" THEN reward_amount ELSE 0 END) as total_rewards')
            )
            ->groupBy('referrer_id')
            ->orderByDesc('completed_referrals')
            ->orderByDesc('total_rewards')
            ->limit(50)
            ->get();

        $userIds = $leaders->pluck('referrer_id');
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $leaders = $leaders->map(function ($row) use ($users) {
            $user = $users->get($row->referrer_id);
            $count = (int) $row->completed_referrals;
            $tier = match(true) {
                $count >= 25 => ['label' => 'Platinum', 'color' => '#a78bfa', 'icon' => 'bi-gem'],
                $count >= 10 => ['label' => 'Gold',     'color' => '#fbbf24', 'icon' => 'bi-trophy-fill'],
                $count >= 5  => ['label' => 'Silver',   'color' => '#94a3b8', 'icon' => 'bi-award-fill'],
                default      => ['label' => 'Bronze',   'color' => '#cd7f32', 'icon' => 'bi-star-fill'],
            };
            return (object)[
                'name'                => $user ? $user->name : 'Unknown',
                'completed_referrals' => $count,
                'total_referrals'     => (int) $row->total_referrals,
                'total_rewards'       => (float) $row->total_rewards,
                'tier'                => $tier,
                'is_me'               => $row->referrer_id === Auth::id(),
            ];
        });

        $myRank = null;
        foreach ($leaders as $i => $row) {
            if ($row->is_me) { $myRank = $i + 1; break; }
        }

        return view('referrals.leaderboard', compact('leaders', 'myRank'));
    }
}