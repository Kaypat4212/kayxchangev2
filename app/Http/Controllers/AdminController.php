<?php

namespace App\Http\Controllers;

use App\Models\BuyTrade;
use App\Models\CompanyAccount;
use App\Models\Deposit;
use App\Models\Kyc;
use App\Models\Referral;
use App\Models\SellTrade;
use App\Models\SiteContent;
use App\Models\AdminSetting;
use App\Services\AdminTradeAlertService;
use App\Services\PayoutService;
use App\Mail\TradeNotification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Withdrawal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\CryptoRate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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

    public function showForgotPasswordForm()
    {
        return view('admin.forgot-password');
    }

    public function resetPasswordWithSecret(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'secret_key' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $expectedSecret = (string) env('ADMIN_PASSWORD_RESET_SECRET', env('ADMIN_TERMINAL_PIN', ''));
        if ($expectedSecret === '') {
            return back()->withInput($request->except(['secret_key', 'password', 'password_confirmation']))
                ->withErrors(['secret_key' => 'Admin reset secret is not configured.']);
        }

        if (!hash_equals($expectedSecret, (string) $request->secret_key)) {
            return back()->withInput($request->except(['secret_key', 'password', 'password_confirmation']))
                ->withErrors(['secret_key' => 'Invalid secret key.']);
        }

        $admin = User::where('email', $request->email)->first();
        if (!$admin || !$admin->is_admin) {
            return back()->withInput($request->except(['secret_key', 'password', 'password_confirmation']))
                ->withErrors(['email' => 'No admin account was found for this email.']);
        }

        $admin->password = Hash::make($request->password);
        $admin->save();

        return redirect()->route('admin.login')->with('success', 'Admin password reset successful. You can now log in.');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            if (Auth::user()->is_admin) {
                $admin = Auth::user();
                // If 2FA is enabled, redirect to challenge instead of dashboard
                if ($admin->two_factor_enabled && $admin->two_factor_secret) {
                    $request->session()->put('2fa_admin_user_id', $admin->id);
                    Auth::logout();
                    return redirect()->route('admin.2fa.challenge');
                }
                return redirect()->route('admin.dashboard');
            } else {
                Auth::logout();
                return redirect()->route('admin.login')->withErrors(['email' => 'Unauthorized access.']);
            }
        }

        return redirect()->route('admin.login')->withErrors(['email' => 'Invalid credentials.']);
    }

    public function show2faChallenge(Request $request)
    {
        if (!$request->session()->has('2fa_admin_user_id')) {
            return redirect()->route('admin.login');
        }
        return view('admin.2fa-challenge');
    }

    public function verify2faChallenge(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $userId = $request->session()->get('2fa_admin_user_id');
        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $admin = User::find($userId);
        if (!$admin || !$admin->is_admin) {
            $request->session()->forget('2fa_admin_user_id');
            return redirect()->route('admin.login')->withErrors(['code' => 'Session expired.']);
        }

        try {
            $secret = decrypt($admin->two_factor_secret);
            $google2fa = app(\PragmaRX\Google2FAQRCode\Google2FA::class);
            $valid = $google2fa->verifyKey($secret, $request->code);
        } catch (\Throwable $e) {
            $valid = false;
        }

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid or expired authentication code.']);
        }

        $request->session()->forget('2fa_admin_user_id');
        Auth::login($admin);
        return redirect()->route('admin.dashboard');
    }

    public function dashboard()
    {
        $totalUsers = User::count();
        $totalReferrals = Referral::count();
        $totalReferralRewards = Referral::where('status', 'completed')->sum('reward_amount');
        $siteMode = SiteContent::get('site_mode', 'production');

        $cryptoRates = Schema::hasTable('crypto_rates')
            ? CryptoRate::all()->map(fn($r) => ['coin' => $r->coin, 'buy_rate' => $r->buy_rate, 'sell_rate' => $r->sell_rate])->values()
            : collect();

        return view('admin.dashboard', compact('totalUsers', 'totalReferrals', 'totalReferralRewards', 'siteMode', 'cryptoRates'));
    }

    public function toggleSiteMode(Request $request)
    {
        $current = SiteContent::get('site_mode', 'production');
        $next    = $current === 'production' ? 'developer' : 'production';

        SiteContent::updateOrCreate(
            ['key' => 'site_mode'],
            ['group' => 'system', 'label' => 'Site Mode', 'value' => $next]
        );

        Log::info('Site mode changed', [
            'from'       => $current,
            'to'         => $next,
            'changed_by' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['mode' => $next, 'message' => 'Mode switched to ' . ucfirst($next)]);
        }

        return back()->with('success', 'Site mode switched to ' . ucfirst($next) . '.');
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
            $oldStatus = $trade->status;
            $isCompleting = in_array($request->input('status'), ['completed', 'approved', 'successful'], true);

            $validationRules = [
                'status' => ['required', 'in:pending,completed,rejected,approved,successful'],
            ];

            $hasBlockchainCol = Schema::hasColumn('buy_trades', 'blockchain_txid');
            $hasProofCol      = Schema::hasColumn('buy_trades', 'admin_payment_proof');

            if ($hasBlockchainCol) {
                $validationRules['blockchain_txid'] = ['nullable', 'string', 'max:255'];
            }
            if ($hasProofCol) {
                $validationRules['admin_payment_proof'] = [
                    $isCompleting && !$request->filled('blockchain_txid') && $hasBlockchainCol ? 'required' : 'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ];
            }

            $validated = $request->validate($validationRules, [
                'admin_payment_proof.required' => 'Payment proof is required when no blockchain TXID is provided.',
            ]);

            $updatePayload = [
                'status' => $validated['status'],
            ];

            if ($hasBlockchainCol) {
                $updatePayload['blockchain_txid'] = $validated['blockchain_txid'] ?? null;
            }

            if ($hasProofCol && $request->hasFile('admin_payment_proof')) {
                if ($trade->admin_payment_proof) {
                    Storage::disk('public')->delete($trade->admin_payment_proof);
                }
                $updatePayload['admin_payment_proof'] = $request->file('admin_payment_proof')->store('admin-buy-proofs', 'public');
            }

            if ($isCompleting) {
                if (Schema::hasColumn('buy_trades', 'approved_by_admin_id')) {
                    $updatePayload['approved_by_admin_id'] = Auth::id();
                }
                if (Schema::hasColumn('buy_trades', 'approved_at')) {
                    $updatePayload['approved_at'] = now();
                }
            }

            $trade->update($updatePayload);

            if (in_array($request->status, ['completed', 'rejected']) && $oldStatus !== $request->status) {
                try {
                    $tradeUser = User::find($trade->user_id);
                    if ($tradeUser) {
                        $isCompleted = $request->status === 'completed';
                        $buyAdminProofUrl = $trade->admin_payment_proof
                            ? asset('storage/' . $trade->admin_payment_proof)
                            : null;
                        // Calculate crypto equivalent
                        $cryptoAmount = $trade->usd_amount / ($trade->rate_used ?: 1);
                        
                        Mail::to($tradeUser->email)->send(new TradeNotification(
                            user: $tradeUser,
                            templateKey: $isCompleted ? 'buy_trade_completed' : 'buy_trade_rejected',
                            data: [
                                'usd_amount'     => number_format((float)$trade->usd_amount, 2),
                                'crypto_amount'  => number_format($cryptoAmount, 8),
                                'currency'       => $trade->coin,
                                'rate_used'      => number_format((float)$trade->rate_used, 2),
                                'naira_amount'   => number_format((float)$trade->naira_amount, 2),
                                'wallet_address' => $trade->wallet_address ?? 'N/A',
                                'reference'      => $trade->transaction_ref ?? ('BUY-' . $trade->id),
                                'reason'         => $request->input('rejection_reason', 'Your order did not meet our requirements.'),
                                'proof_url'      => $buyAdminProofUrl,
                            ],
                            badge: [
                                'text'  => $isCompleted ? 'Order Completed' : 'Order Rejected',
                                'color' => $isCompleted ? '#00cc00' : '#dc3545',
                            ],
                            ctaUrl: $buyAdminProofUrl ?? url('/dashboard'),
                            ctaText: $buyAdminProofUrl ? 'View Payment Proof' : 'Go to Dashboard',
                        ));
                    }
                } catch (\Exception $mailEx) {
                    Log::warning('Buy status email failed: ' . $mailEx->getMessage());
                }

                // Notify user via Telegram if rejected or completed
                if ($tradeUser && $tradeUser->telegram_chat_id) {
                    try {
                        if (!$isCompleted) {
                            $rejectionReason = $request->input('rejection_reason', '');
                            $reasonLine = $rejectionReason ? "\n\n📋 *Reason:* " . $rejectionReason : "\n\nPlease contact support for more details.";
                            app(\App\Services\TelegramService::class)->sendMessage(
                                (int)$tradeUser->telegram_chat_id,
                                "❌ *Your Buy Order was Rejected*\n\n" .
                                "🔖 Ref: `{$trade->transaction_ref}`\n" .
                                "🪙 {$trade->coin}: \$" . number_format((float)$trade->usd_amount, 2) . "\n" .
                                "💴 Naira: ₦" . number_format((float)$trade->naira_amount, 2) .
                                $reasonLine,
                                'Markdown'
                            );
                        } else {
                            $proofLine = isset($buyAdminProofUrl) && $buyAdminProofUrl
                                ? "\n\n🧾 [View Payment Proof]({$buyAdminProofUrl})"
                                : '';
                            app(\App\Services\TelegramService::class)->sendMessage(
                                (int)$tradeUser->telegram_chat_id,
                                "✅ *Your Buy Order is Completed*\n\n" .
                                "🔖 Ref: `{$trade->transaction_ref}`\n" .
                                "🪙 {$trade->coin}: \$" . number_format((float)$trade->usd_amount, 2) . "\n" .
                                "💴 Naira: ₦" . number_format((float)$trade->naira_amount, 2) . "\n" .
                                "🏦 Wallet: " . ($trade->wallet_address ?? 'N/A') .
                                $proofLine,
                                'Markdown'
                            );
                        }
                    } catch (\Throwable $tgEx) {
                        Log::warning('Buy rejection Telegram notify failed: ' . $tgEx->getMessage());
                    }
                }

                try {
                    app(AdminTradeAlertService::class)->sendStatusChangeAlert('buy', [
                        'reference' => $trade->transaction_ref ?? ('BUY-' . $trade->id),
                        'user_name' => $tradeUser->name ?? ($trade->name ?? 'N/A'),
                        'old_status' => $oldStatus,
                        'new_status' => $request->status,
                    ]);
                } catch (\Throwable $alertEx) {
                    Log::warning('Buy status admin alert failed: ' . $alertEx->getMessage());
                }

                // Check & award trade badges
                if ($isCompleted && isset($tradeUser)) {
                    try { app(\App\Services\BadgeService::class)->checkAndAward($tradeUser->fresh(), 'trade_completed'); } catch (\Throwable) {}
                }
            }

            return back()->with('success', 'Buy trade status updated.');
        } catch (\Exception $e) {
            Log::error('Error updating buy trade status: ' . $e->getMessage());
            return back()->with('error', 'Failed to update buy trade status.');
        }
    }

    public function updateSellStatus(Request $request, $id)    {
        try {
            $trade = SellTrade::findOrFail($id);
            $oldStatus = $trade->status;
            $newStatus = $request->input('status');

            $updatePayload = ['status' => $newStatus];

            // Store admin payment proof if provided
            if ($request->hasFile('admin_payment_proof')) {
                if ($trade->admin_payment_proof) {
                    Storage::disk('public')->delete($trade->admin_payment_proof);
                }
                $updatePayload['admin_payment_proof'] = $request->file('admin_payment_proof')->store('admin-sell-proofs', 'public');
            }

            $trade->update($updatePayload);

            // Auto-payout via Paystack when sell is completed with bank transfer
            if ($newStatus === 'completed' && $oldStatus !== 'completed'
                && $trade->payment_method !== 'wallet_balance'
                && AdminSetting::get('auto_sell_payout_enabled', '0') === '1'
                && (float)($trade->naira_amount ?? 0) > 0
                && !empty($trade->account_number)
            ) {
                try {
                    $bankDetails = [
                        'account_name'   => $trade->account_name ?? '',
                        'account_number' => $trade->account_number,
                        'bank_code'      => $trade->bank_code ?? '',
                    ];
                    $payout = app(PayoutService::class)->payoutViaPaystack(
                        $bankDetails,
                        (float)$trade->naira_amount,
                        'SELL-PAY-' . $trade->id . '-' . time()
                    );
                    if ($payout['success']) {
                        Log::info("[AutoPayout] Sell #{$trade->id} payout initiated: " . ($payout['transfer_code'] ?? ''));
                    } else {
                        Log::warning("[AutoPayout] Sell #{$trade->id} payout failed: " . ($payout['error'] ?? 'unknown'));
                    }
                } catch (\Throwable $payoutEx) {
                    Log::error('[AutoPayout] Exception sell #' . $trade->id . ': ' . $payoutEx->getMessage());
                }
            }

            // Alias for rest of block
            $request->merge(['status' => $newStatus]);

            if (in_array($request->status, ['completed', 'rejected']) && $oldStatus !== $request->status) {
                try {
                    $tradeUser = User::find($trade->user_id);
                    if ($tradeUser) {
                        $isCompleted = $request->status === 'completed';
                        $paymentMethod = $trade->payment_method === 'wallet_balance'
                            ? 'Wallet Balance'
                            : ($trade->bank_name ? $trade->bank_name . ' (' . ($trade->account_number ?? '') . ')' : 'Bank Transfer');
                        $adminProofUrl = $trade->admin_payment_proof
                            ? asset('storage/' . $trade->admin_payment_proof)
                            : null;
                        
                        // Calculate crypto equivalent
                        $usdVal = $trade->usd_amount ?? $trade->amount;
                        $cryptoAmount = $usdVal / ($trade->rate_used ?: 1);
                        
                        Mail::to($tradeUser->email)->send(new TradeNotification(
                            user: $tradeUser,
                            templateKey: $isCompleted ? 'sell_trade_completed' : 'sell_trade_rejected',
                            data: [
                                'usd_amount'     => number_format((float)$usdVal, 2),
                                'crypto_amount'  => number_format($cryptoAmount, 8),
                                'currency'       => $trade->coin,
                                'rate_used'      => number_format((float)$trade->rate_used, 2),
                                'naira_amount'   => number_format((float)$trade->naira_amount, 2),
                                'reference'      => $trade->transaction_ref ?? ('SELL-' . $trade->id),
                                'payment_method' => $paymentMethod,
                                'reason'         => $request->input('rejection_reason', 'Your order did not meet our requirements.'),
                                'proof_url'      => $adminProofUrl,
                            ],
                            badge: [
                                'text'  => $isCompleted ? 'Payment Sent' : 'Order Rejected',
                                'color' => $isCompleted ? '#00cc00' : '#dc3545',
                            ],
                            ctaUrl: $adminProofUrl ?? url('/dashboard'),
                            ctaText: $adminProofUrl ? 'View Payment Proof' : 'Go to Dashboard',
                        ));
                    }
                } catch (\Exception $mailEx) {
                    Log::warning('Sell status email failed: ' . $mailEx->getMessage());
                }

                // Notify user via Telegram if rejected or completed
                if ($tradeUser && $tradeUser->telegram_chat_id) {
                    try {
                        if (!$isCompleted) {
                            $rejectionReason = $request->input('rejection_reason', '');
                            $reasonLine = $rejectionReason ? "\n\n📋 *Reason:* " . $rejectionReason : "\n\nPlease contact support for more details.";
                            app(\App\Services\TelegramService::class)->sendMessage(
                                (int)$tradeUser->telegram_chat_id,
                                "❌ *Your Sell Trade was Rejected*\n\n" .
                                "🔖 Ref: `{$trade->transaction_ref}`\n" .
                                "💴 Amount: ₦" . number_format((float)($trade->naira_amount ?? 0), 2) .
                                $reasonLine,
                                'Markdown'
                            );
                        } else {
                            $proofLine = isset($adminProofUrl) && $adminProofUrl
                                ? "\n\n🧾 [View Payment Proof]({$adminProofUrl})"
                                : '';
                            app(\App\Services\TelegramService::class)->sendMessage(
                                (int)$tradeUser->telegram_chat_id,
                                "✅ *Your Sell Trade is Completed*\n\n" .
                                "🔖 Ref: `{$trade->transaction_ref}`\n" .
                                "🪙 Coin: {$trade->coin}\n" .
                                "💴 Naira Paid: ₦" . number_format((float)($trade->naira_amount ?? 0), 2) .
                                $proofLine,
                                'Markdown'
                            );
                        }
                    } catch (\Throwable $tgEx) {
                        Log::warning('Sell status Telegram notify failed: ' . $tgEx->getMessage());
                    }
                }

                try {
                    app(AdminTradeAlertService::class)->sendStatusChangeAlert('sell', [
                        'reference' => $trade->transaction_ref ?? ('SELL-' . $trade->id),
                        'user_name' => $tradeUser->name ?? ($trade->name ?? 'N/A'),
                        'old_status' => $oldStatus,
                        'new_status' => $request->status,
                    ]);
                } catch (\Throwable $alertEx) {
                    Log::warning('Sell status admin alert failed: ' . $alertEx->getMessage());
                }

                // Check & award trade badges
                if ($isCompleted && isset($tradeUser)) {
                    try { app(\App\Services\BadgeService::class)->checkAndAward($tradeUser->fresh(), 'trade_completed'); } catch (\Throwable) {}
                }
            }

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

    public function getCompanyAccount(Request $request)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return redirect()->route('admin.dashboard')->with('info', 'Manage company account below.');
        }
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
                'account_number' => 'required|string|max:255',
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
            'role' => 'nullable|string|in:user,support,manager,finance,compliance,admin',
            'is_admin' => 'nullable|boolean',
        ]);

        $isAdmin = $request->boolean('is_admin');
        $role = $request->input('role', 'user');
        if ($role === 'admin') {
            $isAdmin = true;
        }
        if ($isAdmin && $role !== 'admin') {
            $role = 'admin';
        }

        if (Auth::id() === $user->id && !$isAdmin) {
            return back()->withErrors(['is_admin' => 'You cannot remove your own admin access.'])->withInput();
        }

         try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'balance' => $request->balance,
                'role' => $role,
                'is_admin' => $isAdmin,
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

    public function updateUserBankDetails(Request $request, User $user)
    {
        $request->validate([
            'bank_name'      => 'required|string|max:255',
            'bank_code'      => 'required|string|max:50',
            'account_number' => 'required|string|size:10',
            'account_name'   => 'required|string|max:255',
        ]);

        try {
            $user->update([
                'bank_name'      => $request->bank_name,
                'bank_code'      => $request->bank_code,
                'account_number' => $request->account_number,
                'account_name'   => $request->account_name,
            ]);
            Log::info('Admin #' . Auth::id() . ' updated bank details for user #' . $user->id);
            return redirect()->route('admin.users.edit', $user->id)->with('success', 'Bank details updated successfully.');
        } catch (\Exception $e) {
            Log::error('Admin bank update failed for user #' . $user->id . ': ' . $e->getMessage());
            return redirect()->route('admin.users.edit', $user->id)->with('error', 'Failed to update bank details.');
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