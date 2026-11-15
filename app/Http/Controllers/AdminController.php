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
use App\Models\Conversion;
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
        $request->session()->forget(['2fa_admin_user_id', '2fa_admin_started_at']);
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            if (Auth::user()->is_admin) {
                $admin = Auth::user();
                // Fail closed if an admin has 2FA flagged on but their secret is missing.
                if ($admin->two_factor_enabled) {
                    if (!$admin->two_factor_secret) {
                        Auth::logout();
                        $request->session()->regenerateToken();
                        return redirect()->route('admin.login')->withErrors([
                            'email' => 'Two-factor protection is enabled but its authenticator setup is incomplete. Contact a platform owner to recover this admin account.',
                        ]);
                    }
                    $request->session()->regenerate();
                    $request->session()->put('2fa_admin_user_id', $admin->id);
                    $request->session()->put('2fa_admin_started_at', now()->timestamp);
                    Auth::logout();
                    return redirect()->route('admin.2fa.challenge');
                }
                $request->session()->regenerate();
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
        $startedAt = (int) $request->session()->get('2fa_admin_started_at', 0);
        if (!$request->session()->has('2fa_admin_user_id') || !$startedAt || now()->timestamp - $startedAt > 600) {
            $request->session()->forget(['2fa_admin_user_id', '2fa_admin_started_at']);
            return redirect()->route('admin.login');
        }
        return view('admin.2fa-challenge');
    }

    public function verify2faChallenge(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $userId = $request->session()->get('2fa_admin_user_id');
        $startedAt = (int) $request->session()->get('2fa_admin_started_at', 0);
        if (!$userId || !$startedAt || now()->timestamp - $startedAt > 600) {
            $request->session()->forget(['2fa_admin_user_id', '2fa_admin_started_at']);
            return redirect()->route('admin.login');
        }

        $admin = User::find($userId);
        if (!$admin || !$admin->is_admin || !$admin->two_factor_enabled || !$admin->two_factor_secret) {
            $request->session()->forget(['2fa_admin_user_id', '2fa_admin_started_at']);
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

        $request->session()->forget(['2fa_admin_user_id', '2fa_admin_started_at']);
        Auth::login($admin);
        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    }

    public function dashboard()
    {
        $totalUsers = User::count();
        $totalReferrals = Referral::count();
        $totalReferralRewards = Referral::where('status', 'completed')->sum('reward_amount');
        $siteMode = AdminSetting::getSetting('runtime_mode', 'production');
        $pendingCounts = $this->dashboardPendingCounts();
        $newUsersThisMonth = User::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();

        // Swap totals are shown alongside the provider feature switches.
        $totalConversions = Schema::hasTable('conversions') ? Conversion::count() : 0;
        $pendingConversions = Schema::hasTable('conversions') ? Conversion::where('status', 'pending')->count() : 0;
        $completedConversions = Schema::hasTable('conversions') ? Conversion::where('status', 'completed')->count() : 0;
        $depositProvider = (string) AdminSetting::getSetting('crypto_deposit_provider', 'auto');
        $nowPaymentsActive = AdminSetting::getSetting('nowpayments_enabled', '0') === '1';
        $cryptomusActive = AdminSetting::getSetting('cryptomus_enabled', '0') === '1';
        $cryptoDepositsEnabled = match ($depositProvider) {
            'nowpayments' => $nowPaymentsActive,
            'cryptomus' => $cryptomusActive,
            default => $nowPaymentsActive || $cryptomusActive,
        };
        $cryptoDepositProviderName = $cryptoDepositsEnabled
            ? match ($depositProvider) {
                'nowpayments' => 'NOWPayments',
                'cryptomus' => 'Cryptomus',
                default => ($nowPaymentsActive ? 'NOWPayments' : 'Cryptomus') . ' · Automatic',
            }
            : 'No active provider';
        $swapEnabled = AdminSetting::getSetting('nowpayments_conversion_enabled', '0') === '1';

        $cryptoRates = Schema::hasTable('crypto_rates')
            ? CryptoRate::all()->map(fn($r) => ['coin' => $r->coin, 'buy_rate' => $r->buy_rate, 'sell_rate' => $r->sell_rate])->values()
            : collect();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalReferrals',
            'totalReferralRewards',
            'siteMode',
            'cryptoRates',
            'totalConversions',
            'pendingConversions',
            'completedConversions',
            'pendingCounts',
            'newUsersThisMonth',
            'cryptoDepositsEnabled',
            'cryptoDepositProviderName',
            'swapEnabled'
        ));
    }

    private function dashboardPendingCounts(): array
    {
        $counts = [
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
            'pending_kyc' => Kyc::where('status', 'pending')->count(),
            'pending_buy_trades' => BuyTrade::where('status', 'pending')->count(),
            'pending_sell_trades' => SellTrade::where('status', 'pending')->count(),
            'pending_deposits' => Schema::hasTable('deposits') ? Deposit::where('status', 'pending')->count() : 0,
            'crypto_deposits_review' => 0,
            'crypto_withdrawals' => 0,
            'bill_requests' => 0,
            'open_support' => 0,
        ];

        if (Schema::hasTable('crypto_deposits')) {
            $counts['crypto_deposits_review'] = \App\Models\CryptoDeposit::where('status', 'awaiting_admin_review')->count()
                + \App\Models\CryptoDeposit::where('status', 'pending')->where('payment_data->provider', 'manual')->count();
        }
        if (Schema::hasTable('crypto_withdrawals')) {
            $counts['crypto_withdrawals'] = \App\Models\CryptoWithdrawal::where('status', 'pending')->count();
        }
        if (Schema::hasTable('bill_payments')) {
            $counts['bill_requests'] = \App\Models\BillPayment::where('status', 'pending')->count();
        }
        if (Schema::hasTable('support_cases')) {
            $counts['open_support'] = \App\Models\SupportCase::whereIn('status', ['open', 'in_progress'])->count();
        }

        $counts['pending_trades'] = $counts['pending_buy_trades'] + $counts['pending_sell_trades'];
        return $counts;
    }

    public function toggleSiteMode(Request $request)
    {
        $current = (string) AdminSetting::getSetting('runtime_mode', 'production');
        $next = $current === 'local' ? 'production' : 'local';

        AdminSetting::seedDefaults();
        AdminSetting::set('runtime_mode', $next);

        // Keep the legacy mode key aligned for existing diagnostics and integrations.
        SiteContent::updateOrCreate(
            ['key' => 'site_mode'],
            ['group' => 'system', 'label' => 'Site Mode', 'value' => $next === 'local' ? 'developer' : 'production']
        );

        Log::info('Site mode changed', [
            'from'       => $current,
            'to'         => $next,
            'changed_by' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['mode' => $next, 'message' => 'Runtime mode switched to ' . ucfirst($next)]);
        }

        return back()->with('success', 'Runtime mode switched to ' . ucfirst($next) . '.');
    }

    public function getPendingCounts()
    {
        try {
            return response()->json($this->dashboardPendingCounts());
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

        // One admin-facing feed covering every financial module, including
        // crypto deposits/withdrawals, swaps, bills, transfers and gift cards.
        $allActivity = collect();
        $pushActivity = function ($record, string $type, $user, string $asset, $amount, string $currency, $reference = null, string $detail = '') use (&$allActivity): void {
            $allActivity->push((object)[
                'id' => $record->id, 'type' => $type, 'user' => $user,
                'asset' => $asset, 'amount' => $amount, 'currency' => $currency,
                'reference' => $reference ?: '—', 'detail' => $detail,
                'status' => (string)$record->status, 'created_at' => $record->created_at,
            ]);
        };
        foreach ($buyTrades as $row) $pushActivity($row, 'buy', $row->user, strtoupper((string)$row->coin), $row->naira_amount, 'NGN', $row->transaction_ref, 'Crypto purchase');
        foreach ($sellTrades as $row) $pushActivity($row, 'sell', $row->user, strtoupper((string)$row->coin), $row->naira_amount ?? $row->amount, 'NGN', $row->transaction_ref, 'Crypto sale payout');
        foreach ($deposits as $row) $pushActivity($row, 'deposit', $row->user, 'NGN wallet', $row->amount, 'NGN', $row->transaction_ref ?? $row->gateway_reference, 'Naira deposit');
        foreach ($withdrawals as $row) $pushActivity($row, 'withdrawal', $row->user, 'NGN wallet', (float)$row->amount + (float)($row->fee_amount ?? 0), 'NGN', $row->reference, 'Bank withdrawal');
        $loadActivity = function (string $modelClass, string $type, string|array $relation, callable $mapper) use (&$allActivity, $pushActivity): void {
            try {
                foreach ($modelClass::with($relation)->latest()->limit(100)->get() as $row) {
                    [$user, $asset, $amount, $currency, $reference, $detail] = $mapper($row);
                    $pushActivity($row, $type, $user, (string)$asset, $amount, (string)$currency, $reference, (string)$detail);
                }
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::warning('Admin all-activity feed could not load a transaction module.', ['type'=>$type,'error'=>$exception->getMessage()]);
            }
        };
        $loadActivity(\App\Models\CryptoDeposit::class, 'crypto_deposit', 'user', fn($r)=>[$r->user,strtoupper((string)$r->currency),$r->received_amount ?: $r->expected_amount,strtoupper((string)$r->currency),$r->transaction_hash,'Crypto wallet deposit']);
        $loadActivity(\App\Models\CryptoWithdrawal::class, 'crypto_withdrawal', ['user','wallet'], fn($r)=>[$r->user,strtoupper((string)($r->wallet?->currency ?? 'CRYPTO')),$r->amount,strtoupper((string)($r->wallet?->currency ?? 'CRYPTO')),$r->transaction_hash ?: $r->address,'Crypto wallet withdrawal']);
        $loadActivity(\App\Models\Conversion::class, 'crypto_swap', 'user', fn($r)=>[$r->user,strtoupper((string)$r->from_coin).' → '.strtoupper((string)$r->to_coin),$r->from_amount,strtoupper((string)$r->from_coin),$r->provider_conversion_id,'Crypto swap · receive '.number_format((float)$r->to_amount,8).' '.strtoupper((string)$r->to_coin)]);
        $loadActivity(\App\Models\BillPayment::class, 'bill_payment', 'user', fn($r)=>[$r->user,ucwords(str_replace('_',' ',(string)$r->category)),(float)$r->amount+(float)$r->fee,'NGN',$r->reference,'Bill or utility payment']);
        $loadActivity(\App\Models\P2pTransfer::class, 'transfer', 'sender', fn($r)=>[$r->sender,'NGN wallet',$r->amount,'NGN',$r->reference,'Wallet transfer']);
        $loadActivity(\App\Models\PortfolioWithdrawal::class, 'portfolio_withdrawal', 'user', fn($r)=>[$r->user,'Crypto portfolio → NGN',$r->naira_amount,'NGN',$r->reference,'Portfolio withdrawal']);
        $loadActivity(\App\Models\GiftCardOrder::class, 'gift_card_purchase', 'user', fn($r)=>[$r->user,(string)$r->card_name,(float)$r->total_ngn,'NGN',$r->reference,'Gift card purchase']);
        $loadActivity(\App\Models\GiftCardSaleOrder::class, 'gift_card_sale', 'user', fn($r)=>[$r->user,(string)$r->card_name,(float)($r->approved_ngn ?? $r->estimated_ngn),'NGN',$r->reference,'Gift card sale']);
        $allActivity = $allActivity->sortByDesc('created_at')->take(400)->values();

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
        foreach ([
            \App\Models\CryptoDeposit::class, \App\Models\CryptoWithdrawal::class,
            Conversion::class, \App\Models\BillPayment::class, \App\Models\P2pTransfer::class,
            \App\Models\PortfolioWithdrawal::class, \App\Models\GiftCardOrder::class,
            \App\Models\GiftCardSaleOrder::class,
        ] as $extraModel) {
            try {
                $statistics['total_transactions'] += $extraModel::count();
                $statistics['pending_transactions'] += $extraModel::whereIn('status', ['pending','processing','waiting','confirming','partially_paid','awaiting_admin_review'])->count();
                $statistics['completed_transactions'] += $extraModel::whereIn('status', ['successful','completed','approved'])->count();
            } catch (\Throwable $exception) {
                // Some older installations may not have enabled every module table yet.
            }
        }

        return view('admin.trades', compact('buyTrades', 'sellTrades', 'deposits', 'withdrawals', 'statistics', 'allActivity'));
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
                        'payout_method' => $trade->payment_method,
                        'payout_destination' => $trade->payment_method === 'wallet_balance'
                            ? 'KayXchange Naira wallet'
                            : trim(($trade->bank_name ?? '') . ' ' . ($trade->account_number ?? '')),
                        'naira_amount' => number_format((float) ($trade->naira_amount ?? 0), 2),
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
            $validated = $request->validate([
                'status' => 'required|in:pending,completed,rejected',
                'admin_payment_proof' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            ]);
            $newStatus = $validated['status'];

            $transition = app(\App\Services\BalanceSettlementService::class)
                ->updateSellStatus((int) $id, $newStatus);
            $trade = $transition['trade'];
            $oldStatus = $transition['old_status'];
            if (!$transition['changed']) {
                return back()->with('info', 'This sell trade already has that status.');
            }

            // Store admin payment proof if provided
            if ($request->hasFile('admin_payment_proof')) {
                if ($trade->admin_payment_proof) {
                    Storage::disk('public')->delete($trade->admin_payment_proof);
                }
                $trade->admin_payment_proof = $request->file('admin_payment_proof')->store('admin-sell-proofs', 'public');
                $trade->save();
            }

            // Auto-payout via Paystack when sell is completed with bank transfer
            if ($newStatus === 'completed' && $oldStatus !== 'completed'
                && $trade->payment_method !== 'wallet_balance'
                && AdminSetting::getSetting('auto_sell_payout_enabled', '0') === '1'
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
        $user->load('badges');
        $latestKyc = Kyc::where('user_id', $user->id)->latest()->first();
        return view('admin.users.show', compact('user', 'latestKyc'));
    }

    public function sendKycReminder(User $user)
    {
        if ($user->kyc_verified) {
            return back()->with('success', 'This account is already KYC verified.');
        }

        $latestKyc = \App\Models\Kyc::where('user_id', $user->id)->latest()->first();
        if ($latestKyc && $latestKyc->status === 'pending') {
            return back()->with('success', 'This user has already submitted KYC documents. Their application is awaiting review.');
        }

        try {
            Mail::to($user->email)->send(new \App\Mail\KycReminderMail($user));
            Log::info('Admin sent KYC reminder email', ['admin_id' => Auth::id(), 'user_id' => $user->id]);
            return back()->with('success', 'KYC reminder sent to ' . $user->email . '.');
        } catch (\Throwable $e) {
            Log::warning('Admin KYC reminder email failed', ['admin_id' => Auth::id(), 'user_id' => $user->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['kyc_reminder' => 'The reminder email could not be sent. Check the email delivery settings and try again.']);
        }
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
            \Illuminate\Support\Facades\DB::transaction(function () use ($user, $request, $role, $isAdmin): void {
                $user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'role' => $role,
                    'is_admin' => $isAdmin,
                ]);
                app(\App\Services\BalanceSettlementService::class)
                    ->setNgnBalance((int) $user->id, (float) $request->balance);
            });
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

        $amount = (float) $request->amount;
        try {
            app(\App\Services\BalanceSettlementService::class)->adjustNgnBalance(
                (int) $user->id,
                $request->action === 'subtract' ? -$amount : $amount
            );
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.users.index')->with('error', $e->getMessage());
        }
        return redirect()->route('admin.users.index')->with('success', 'Balance updated successfully');
    }
    public function adjustBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'action' => 'required|in:add,subtract',
        ]);

        try {
            $amount = (float) $request->amount;
            app(\App\Services\BalanceSettlementService::class)->adjustNgnBalance(
                (int) $user->id,
                $request->action === 'subtract' ? -$amount : $amount
            );
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
