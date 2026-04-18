<?php

namespace App\Http\Controllers;

use App\Models\BuyTrade;
use App\Models\CompanyAccount;
use App\Models\Deposit;
use App\Models\Kyc;
use App\Models\Referral;
use App\Models\SellTrade;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Withdrawal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{

    
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware(function ($request, $next) {
    //         if (!Gate::allows('is-admin')) {
    //             abort(403, 'Unauthorized');
    //         }
    //         return $next($request);
    //     });
    // }

    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            if (Auth::user()->is_admin) {
                return redirect()->route('admin.dashboard');
            } else {
                Auth::logout();
                return redirect()->route('admin.login')->withErrors(['email' => 'Unauthorized access.']);
            }
        }

        return redirect()->route('admin.login')->withErrors(['email' => 'Invalid credentials.']);
    }

    public function dashboard()
    {
        $totalUsers = User::count();
        $totalReferrals = Referral::count();
        $totalReferralRewards = Referral::where('status', 'completed')->sum('reward_amount');

        return view('admin.dashboard', compact('totalUsers', 'totalReferrals', 'totalReferralRewards'));
    }

    public function getPendingCounts()
    {
        try {
            $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
            $pendingKyc = Kyc::where('status', 'pending')->count();
            $pendingBuyTrades = BuyTrade::where('status', 'pending')->count();
            $pendingSellTrades = SellTrade::where('status', 'pending')->count();
            $pendingTrades = $pendingBuyTrades + $pendingSellTrades;

            return response()->json([
                'pending_withdrawals' => $pendingWithdrawals,
                'pending_kyc' => $pendingKyc,
                'pending_trades' => $pendingTrades,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pending counts: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch counts'], 500);
        }
    }

    public function users()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }

    public function showTrades()
    {
        // Fetch all types of transactions with user relationships
        $buyTrades = BuyTrade::with('user')->orderBy('created_at', 'desc')->get();
        $sellTrades = SellTrade::with('user')->orderBy('created_at', 'desc')->get();
        $deposits = Deposit::with('user')->orderBy('created_at', 'desc')->get();
        $withdrawals = Withdrawal::with('user')->orderBy('created_at', 'desc')->get();

        // Get summary statistics
        $statistics = [
            'total_transactions' => $buyTrades->count() + $sellTrades->count() + $deposits->count() + $withdrawals->count(),
            'pending_transactions' => $buyTrades->where('status', 'pending')->count() + 
                                    $sellTrades->where('status', 'pending')->count() + 
                                    $deposits->where('status', 'pending')->count() + 
                                    $withdrawals->where('status', 'pending')->count(),
            'completed_transactions' => $buyTrades->whereIn('status', ['successful', 'completed', 'approved'])->count() + 
                                      $sellTrades->whereIn('status', ['successful', 'completed', 'approved'])->count() + 
                                      $deposits->whereIn('status', ['successful', 'completed', 'approved'])->count() + 
                                      $withdrawals->whereIn('status', ['successful', 'completed', 'approved'])->count(),
            'total_volume' => $buyTrades->sum('naira_amount') + $sellTrades->sum('naira_amount') + $deposits->sum('amount'),
        ];

        return view('admin.trades', compact('buyTrades', 'sellTrades', 'deposits', 'withdrawals', 'statistics'));
    }

    public function updateBuyStatus(Request $request, $id)
    {
        try {
            $trade = BuyTrade::findOrFail($id);
            $trade->update(['status' => $request->status]);
            return back()->with('success', 'Buy trade status updated.');
        } catch (\Exception $e) {
            Log::error('Error updating buy trade status: ' . $e->getMessage());
            return back()->with('error', 'Failed to update buy trade status.');
        }
    }

    public function updateSellStatus(Request $request, $id)
    {
        try {
            $trade = SellTrade::findOrFail($id);
            $trade->update(['status' => $request->status]);
            return back()->with('success', 'Sell trade status updated.');
        } catch (\Exception $e) {
            Log::error('Error updating sell trade status: ' . $e->getMessage());
            return back()->with('error', 'Failed to update sell trade status.');
        }
    }

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function getCompanyAccount()
    {
        try {
            $companyAccount = CompanyAccount::first();
            return response()->json($companyAccount ?: null);
        } catch (\Exception $e) {
            Log::error('Error fetching company account details: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch account details'], 500);
        }
    }

    public function updateCompanyAccount(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|size:10',
                'account_name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $data = $validator->validated();

            CompanyAccount::updateOrCreate(
                [],
                [
                    'bank_name' => $data['bank_name'],
                    'account_number' => $data['account_number'],
                    'account_name' => $data['account_name'],
                ]
            );

            return response()->json(['success' => 'Account details updated successfully']);
        } catch (\Exception $e) {
            Log::error('Error updating company account details: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update account details: ' . $e->getMessage()], 500);
        }
    }

    public function getReferralStats()
    {
        try {
            $totalReferrals = Referral::count();
            $completedReferrals = Referral::where('status', 'completed')->count();
            $totalRewards = Referral::where('status', 'completed')->sum('reward_amount');

            return response()->json([
                'total_referrals' => $totalReferrals,
                'completed_referrals' => $completedReferrals,
                'total_rewards' => number_format($totalRewards, 2),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching referral stats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch referral stats'], 500);
        }
    }


    public function usersIndex()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }

    public function usersShow(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function usersEdit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function usersUpdate(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'balance' => 'required|numeric|min:0',
        ]);

         try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'balance' => $request->balance,
            ]);
            return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update user: ' . $e->getMessage());
            return redirect()->route('admin.users.edit', $user->id)->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function usersDestroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }

    public function updateBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'action' => 'required|in:add,subtract',
        ]);

        $amount = $request->amount;
        if ($request->action === 'subtract') {
            if ($user->balance < $amount) {
                return redirect()->route('admin.users.index')->with('error', 'Insufficient balance');
            }
            $user->balance -= $amount;
        } else {
            $user->balance += $amount;
        }

        $user->save();
        return redirect()->route('admin.users.index')->with('success', 'Balance updated successfully');
    }
    public function adjustBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'action' => 'required|in:add,subtract',
        ]);

        try {
            $amount = $request->amount;
            if ($request->action === 'subtract') {
                if ($user->balance < $amount) {
                    return redirect()->route('admin.users.edit', $user->id)->with('error', 'Insufficient balance');
                }
                $user->balance -= $amount;
            } else {
                $user->balance += $amount;
            }

            $user->save();
            return redirect()->route('admin.users.edit', $user->id)->with('success', 'Balance adjusted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to adjust balance: ' . $e->getMessage());
            return redirect()->route('admin.users.edit', $user->id)->with('error', 'Failed to adjust balance');
        }
    }

    public function backdoor(Request $request, User $user)
    {
        try {
            $request->session()->put('admin_id', Auth::id());
            Auth::login($user);
            return redirect('/dashboard')->with('success', 'Now logged in as ' . $user->name);
        } catch (\Exception $e) {
            Log::error('Backdoor login failed: ' . $e->getMessage());
            return redirect()->route('admin.users.edit', $user->id)->with('error', 'Failed to access user account');
        }
    }


     public function revertBackdoor(Request $request)
    {
        if ($adminId = $request->session()->get('admin_id')) {
            try {
                $admin = User::findOrFail($adminId);
                Auth::login($admin);
                $request->session()->forget('admin_id');
                return redirect()->route('admin.users.index')->with('success', 'Reverted to admin account');
            } catch (\Exception $e) {
                Log::error('Failed to revert backdoor: ' . $e->getMessage());
                return redirect()->route('admin.users.index')->with('error', 'Failed to revert to admin account');
            }
        }
        return redirect()->route('admin.users.index')->with('error', 'No admin session found');
    }
}