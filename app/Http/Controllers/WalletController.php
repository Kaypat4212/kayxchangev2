<?php

namespace App\Http\Controllers;

use App\Exceptions\DepositMinimumException;
use App\Models\Wallet;
use App\Models\CryptoDeposit;
use App\Models\CryptoRate;
use App\Models\AdminSetting;
use App\Models\SiteContent;
use App\Services\CryptoWalletService;
use App\Services\ExchangeRateService;
use App\Services\AdminTradeAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    protected $cryptoWalletService;

    public function __construct(CryptoWalletService $cryptoWalletService)
    {
        $this->cryptoWalletService = $cryptoWalletService;
    }

    /**
     * Display user's wallet dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $user->wallets()->firstOrCreate(['currency' => 'USD'], ['balance' => 0, 'network' => null, 'address' => null, 'is_active' => true]);
        $wallets = $this->cryptoWalletService->getUserWallets($user);
        $market = app(ExchangeRateService::class)->getUsdRates();
        $ngnPerUsd = $this->ngnPerUsdRate();
        $liveCoins = $market['_live_coins'] ?? [];
        $prices = [];
        foreach ($liveCoins as $coin) {
            if (is_numeric($market[$coin] ?? null)) {
                $prices[$coin] = ['price_usd' => (float) $market[$coin], 'change_24h' => $market['_change_24h'][$coin] ?? null];
            }
        }
        $portfolioUsd = $ngnPerUsd > 0 ? (float) $user->balance / $ngnPerUsd : 0.0;
        $valuationComplete = true;
        foreach ($wallets as $wallet) {
            $currency = strtoupper($wallet->currency);
            if ($currency === 'NGN') continue; // User::balance is the canonical NGN balance.
            if ($currency === 'USD') {
                $portfolioUsd += (float) $wallet->balance;
                continue;
            }
            if (isset($prices[$currency])) $portfolioUsd += (float) $wallet->balance * $prices[$currency]['price_usd'];
            elseif ((float) $wallet->balance > 0) $valuationComplete = false;
        }
        if (!$valuationComplete) $portfolioUsd = null;
        $portfolioNgn = $portfolioUsd !== null && $ngnPerUsd > 0 ? $portfolioUsd * $ngnPerUsd : null;
        $marketMeta = [
            'source' => $market['_source'] ?? 'unavailable',
            'updated_at' => $market['_updated_at'] ?? null,
            'stale' => (bool) ($market['_stale'] ?? false),
            'is_live' => !empty($prices) && !($market['_stale'] ?? false) && ($market['_source'] ?? 'fallback') !== 'fallback',
        ];

        // Get recent transactions (deposits, withdrawals, transfers)
        $recentDeposits = CryptoDeposit::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('wallet.index', compact('wallets', 'recentDeposits', 'prices', 'marketMeta', 'ngnPerUsd', 'portfolioUsd', 'portfolioNgn'));
    }

    public function marketPrices(ExchangeRateService $marketService)
    {
        $market = $marketService->getUsdRates();
        $liveCoins = $market['_live_coins'] ?? [];
        $prices = [];
        foreach ($liveCoins as $coin) {
            if (is_numeric($market[$coin] ?? null)) {
                $prices[$coin] = ['price_usd' => (float) $market[$coin], 'change_24h' => $market['_change_24h'][$coin] ?? null];
            }
        }
        return response()->json([
            'prices' => $prices,
            'source' => $market['_source'] ?? 'unavailable',
            'updated_at' => $market['_updated_at'] ?? null,
            'stale' => (bool) ($market['_stale'] ?? false),
            'ngn_per_usd' => $this->ngnPerUsdRate(),
            'ngn_balance' => (float) Auth::user()->balance,
            'wallets' => Auth::user()->wallets()->where('is_active', true)->get(['currency', 'balance'])->mapWithKeys(fn ($wallet) => [strtoupper($wallet->currency) => (float) $wallet->balance]),
        ])->header('Cache-Control', 'private, no-store');
    }

    private function ngnPerUsdRate(): float
    {
        $configured = (float) AdminSetting::getSetting('usd_wallet_sell_rate_ngn', 0);
        return $configured > 0 ? $configured : (float) (CryptoRate::where('coin', 'USDT')->value('sell_rate') ?? 0);
    }

    public function usd()
    {
        $wallet = Auth::user()->wallets()->firstOrCreate(
            ['currency' => 'USD'],
            ['balance' => 0, 'network' => null, 'address' => null, 'is_active' => true]
        );
        $buyRate = (float) AdminSetting::getSetting('usd_wallet_buy_rate_ngn', 0);
        $sellRate = (float) AdminSetting::getSetting('usd_wallet_sell_rate_ngn', 0);
        return view('wallet.usd', compact('wallet', 'buyRate', 'sellRate'));
    }

    public function crypto()
    {
        $wallets = Auth::user()->wallets()->where('is_active', true)->whereNotIn('currency', ['NGN', 'USD', 'EUR'])->orderBy('currency')->get();
        $rates = CryptoRate::orderBy('coin')->get()->keyBy(fn ($rate) => strtoupper($rate->coin));
        return view('wallet.crypto', compact('wallets', 'rates'));
    }

    /**
     * Show crypto deposit page
     */
    public function deposit()
    {
        $user = Auth::user();
        $wallets = $this->cryptoWalletService->getUserWallets($user);
        $supportedCryptos = $this->cryptoWalletService->getSupportedCryptos();

        $depositProvider = $this->cryptoWalletService->depositProviderLabel();
        return view('wallet.deposit', compact('wallets', 'supportedCryptos', 'depositProvider'));
    }

    /**
     * Create a crypto deposit invoice
     */
    public function createDeposit(Request $request)
    {
        $request->validate([
            'currency' => 'required|string|in:' . implode(',', array_keys($this->cryptoWalletService->getSupportedCryptos())),
            'amount' => 'required|numeric|min:0.00000001',
            'input_type' => 'required|in:usd,crypto',
            'network' => 'required|string|max:20',
        ]);

        try {
            $user = Auth::user();
            $currency = strtoupper($request->input('currency'));
            $network = strtoupper($request->input('network'));
            $supported = $this->cryptoWalletService->getSupportedCryptos();
            if (!in_array($network, $supported[$currency]['networks'] ?? [], true)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['network' => 'Choose a supported network for the selected asset.']);
            }
            $inputAmount = (float) $request->input('amount');
            $market = app(ExchangeRateService::class)->getUsdRates();
            $liveCoins = $market['_live_coins'] ?? [];
            $marketPrice = in_array($currency, $liveCoins, true) ? (float) ($market[$currency] ?? 0) : 0.0;
            if ($request->input('input_type') === 'crypto' && ($marketPrice <= 0 || ($market['_stale'] ?? false))) {
                throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'A live market price is unavailable for this asset. Select USD input or try again shortly.']);
            }
            $usdAmount = $request->input('input_type') === 'crypto' ? $inputAmount * $marketPrice : $inputAmount;
            if ($usdAmount < 0.01) {
                throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'The USD value must be at least $0.01.']);
            }
            $inputDetails = [
                'input_type' => $request->input('input_type'),
                'network' => $network,
                'input_amount' => $inputAmount,
                'input_currency' => $request->input('input_type') === 'crypto' ? $currency : 'USD',
                'input_usd_amount' => round($usdAmount, 8),
                'quoted_market_price_usd' => $marketPrice > 0 ? $marketPrice : null,
                'market_source' => $market['_source'] ?? null,
                'quoted_at' => $market['_updated_at'] ?? null,
            ];
            $result = $this->cryptoWalletService->createCryptoDeposit(
                $user,
                $currency,
                $usdAmount,
                $inputDetails
            );

            if ($result['payment_url']) {
                return redirect($result['payment_url']);
            }

            return redirect()->route('wallet.deposit.success', $result['deposit']->id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (DepositMinimumException $e) {
            return back()->with('deposit_error', [
                'title'=>'Deposit amount is below the minimum',
                'message'=>'The minimum for this asset and network is $' . number_format($e->minimumUsd, 2) . ' USD. Increase the amount and try again. No balance was deducted.',
            ]);
        } catch (\Exception $e) {
            $mode = SiteContent::get('site_mode', 'production');
            $rawMessage = $e->getMessage();
            $normalized = strtoupper($rawMessage);
            if (str_contains($normalized, 'AMOUNT_MINIMAL_ERROR') || str_contains($normalized, 'LESS THAN MINIMAL')) {
                $title = 'Deposit amount is below the minimum';
                $message = 'This amount is below the minimum for the selected asset and network. Increase the amount or choose another network, then try again. No balance was deducted.';
            } elseif (str_contains($normalized, 'INVALID_API_KEY') || str_contains($normalized, 'REJECTED THE API KEY')) {
                $title = 'Crypto deposits are temporarily unavailable';
                $message = 'The payment provider could not authorize this deposit. Please try again later or contact support. No balance was deducted.';
            } elseif (str_contains($normalized, 'TEMPORARILY UNAVAILABLE') || str_contains($normalized, 'TEMPORARILY UNREACHABLE')) {
                $title = 'Payment service is temporarily unavailable';
                $message = 'The payment service did not respond in time. Please try again shortly or contact support. No balance was deducted.';
            } elseif (str_contains($normalized, 'NETWORK') || str_contains($normalized, 'CURRENCY')) {
                $title = 'This deposit option is unavailable';
                $message = 'The selected asset or network could not be used right now. Choose another available network or try again later. No balance was deducted.';
            } else {
                $title = 'We could not create your deposit';
                $message = 'Please check your amount and network, then try again in a few minutes. If the problem continues, contact support. No balance was deducted.';
            }

            Log::error('Crypto deposit invoice creation failed', [
                'user_id' => Auth::id(),
                'currency' => strtoupper((string)$request->input('currency')),
                'network' => strtoupper((string)$request->input('network')),
                'site_mode' => $mode,
                'exception' => $rawMessage,
            ]);

            $depositError = ['title'=>$title, 'message'=>$message];
            if ($mode === 'developer' && Auth::user()?->is_admin) {
                $depositError['diagnostic'] = $rawMessage;
            }
            return back()->with('deposit_error', $depositError);
        }
    }

    /**
     * Deposit success page
     */
    public function depositSuccess($id)
    {
        $deposit = CryptoDeposit::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $market = app(ExchangeRateService::class)->getUsdRates();
        $symbol = strtoupper($deposit->currency);
        $liveCoins = $market['_live_coins'] ?? [];
        $currentSpot = !($market['_stale'] ?? false) && in_array($symbol, $liveCoins, true)
            ? (float) ($market[$symbol] ?? 0) : 0.0;
        $currentMarketValue = $deposit->status === 'completed' && $currentSpot > 0 && $deposit->received_amount
            ? (float) $deposit->received_amount * $currentSpot : null;
        $paymentData = $deposit->payment_data ?? [];
        $depositInput = $paymentData['deposit_input'] ?? [];
        $depositValuation = $paymentData['deposit_valuation'] ?? [];

        return view('wallet.deposit-success', compact('deposit', 'depositInput', 'depositValuation', 'currentMarketValue'));
    }

    /**
     * Deposit failed page
     */
    public function depositFailed($id)
    {
        $deposit = CryptoDeposit::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('wallet.deposit-failed', compact('deposit'));
    }

    public function submitManualDepositHash(Request $request, int $id)
    {
        $data = $request->validate([
            'transaction_hash'=>['required','string','min:20','max:150','regex:/^[A-Za-z0-9_-]+$/'],
        ]);
        $deposit = CryptoDeposit::query()->where('user_id', Auth::id())->findOrFail($id);
        if (data_get($deposit->payment_data, 'provider') !== 'manual' || $deposit->status !== 'pending') {
            return back()->with('error', 'This deposit cannot accept a transaction reference.');
        }
        if ($deposit->transaction_hash) return back()->with('info', 'A transaction reference has already been submitted for this deposit.');

        $paymentData = $deposit->payment_data ?? [];
        $paymentData['provider_status'] = 'submitted_for_review';
        $paymentData['user_submitted_tx_at'] = now()->toIso8601String();
        $deposit->transaction_hash = $data['transaction_hash'];
        $deposit->payment_data = $paymentData;
        $deposit->save();

        try {
            app(AdminTradeAlertService::class)->sendTriggeredAlert('crypto_deposit', [
                'reference' => 'DEP-' . $deposit->id, 'user_name' => Auth::user()->name,
                'user_email' => Auth::user()->email, 'coin' => $deposit->currency,
                'usd_amount' => $deposit->expected_amount, 'status' => 'transaction hash submitted for review',
                'wallet_address' => $deposit->payment_address,
            ]);
        } catch (\Throwable $e) { Log::warning('Manual crypto deposit admin alert failed', ['deposit_id'=>$deposit->id,'error'=>$e->getMessage()]); }

        return redirect()->route('wallet.deposit.success', $deposit->id)->with('success', 'Transaction reference submitted. An admin will verify it before crediting your wallet.');
    }

    /** Return current provider state and on-chain BTC confirmation progress. */
    public function depositStatus($id)
    {
        $deposit = CryptoDeposit::where('user_id', Auth::id())->findOrFail($id);
        $deposit = $this->cryptoWalletService->refreshDepositStatus($deposit);
        $paymentData = $deposit->payment_data ?? [];
        $required = (int) ($paymentData['bitcoin_required_confirmations'] ?? 3);
        $confirmations = (int) ($paymentData['bitcoin_confirmations'] ?? 0);
        $market = app(ExchangeRateService::class)->getUsdRates();
        $symbol = strtoupper($deposit->currency);
        $currentMarketValue = $deposit->status === 'completed'
            && !($market['_stale'] ?? false)
            && in_array($symbol, $market['_live_coins'] ?? [], true)
            && $deposit->received_amount
                ? (float) $deposit->received_amount * (float) ($market[$symbol] ?? 0)
                : null;

        return response()->json([
            'status' => $deposit->status,
            'provider_status' => $paymentData['provider_status'] ?? 'waiting',
            'payment_id' => $paymentData['provider_payment_id'] ?? null,
            'expected_crypto_amount' => (float)data_get($paymentData, 'expected_crypto_amount', 0),
            'partial_payment' => ($paymentData['provider_status'] ?? '') === 'partially_paid',
            'remaining_crypto_amount' => data_get($paymentData, 'partial_remaining_crypto'),
            'remaining_usd_amount' => data_get($paymentData, 'partial_remaining_usd'),
            'transaction_hash' => $deposit->transaction_hash,
            'explorer_url' => $deposit->transaction_hash && strtoupper($deposit->currency) === 'BTC'
                ? 'https://blockstream.info/tx/' . rawurlencode($deposit->transaction_hash)
                : null,
            'confirmations' => $confirmations,
            'required_confirmations' => $required,
            'remaining_confirmations' => $deposit->status === 'completed' ? 0 : max(0, $required - $confirmations),
            'ngn_credited_amount' => $deposit->ngn_credited_amount,
            'ngn_rate_used' => $deposit->ngn_rate_used,
            'received_amount' => $deposit->received_amount,
            'deposit_valuation' => data_get($deposit->payment_data, 'deposit_valuation', []),
            'current_market_value_usd' => $currentMarketValue,
            'currency' => $deposit->currency,
        ]);
    }

    /**
     * Show withdrawal page
     */
    public function withdraw()
    {
        $user = Auth::user();
        $wallets = $this->cryptoWalletService->getUserWallets($user);
        foreach ($wallets as $wallet) {
            $reserved = \App\Models\CryptoWithdrawal::where('wallet_id', $wallet->id)
                ->whereIn('status', ['pending', 'processing'])->sum(DB::raw('amount + fee'));
            $wallet->available_balance = max(0, (float) $wallet->balance - (float) $reserved);
        }

        return view('wallet.withdraw', compact('wallets'));
    }

    /**
     * Create a withdrawal request
     */
    public function createWithdrawal(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0.00000001',
            'address' => 'required|string',
            'network' => 'required|string',
        ]);

        $user = Auth::user();
        $withdrawal = DB::transaction(function () use ($request, $user) {
            $wallet = $user->wallets()->whereKey($request->wallet_id)->lockForUpdate()->firstOrFail();
            if (!$wallet->isCrypto() || !$wallet->is_active) {
                throw \Illuminate\Validation\ValidationException::withMessages(['wallet_id' => 'Select an active crypto wallet.']);
            }
            if ($wallet->network && strcasecmp($wallet->network, $request->network) !== 0) {
                throw \Illuminate\Validation\ValidationException::withMessages(['network' => 'The selected network does not match this wallet.']);
            }
            $reserved = \App\Models\CryptoWithdrawal::where('wallet_id', $wallet->id)
                ->whereIn('status', ['pending', 'processing'])->sum(DB::raw('amount + fee'));
            if ((float) $wallet->balance - (float) $reserved < (float) $request->amount) {
                throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'Insufficient available balance after pending withdrawals.']);
            }

            return \App\Models\CryptoWithdrawal::create([
                'user_id' => $user->id, 'wallet_id' => $wallet->id,
                'amount' => $request->amount, 'address' => $request->address,
                'network' => $request->network, 'status' => 'pending', 'fee' => 0,
            ]);
        });

        try {
            app(AdminTradeAlertService::class)->sendTriggeredAlert('withdrawal', [
                'reference' => 'CW-' . $withdrawal->id, 'user_name' => $user->name,
                'user_email' => $user->email, 'coin' => $withdrawal->wallet?->currency,
                'usd_amount' => $withdrawal->amount, 'wallet_address' => $withdrawal->address,
                'network' => $withdrawal->network, 'status' => 'pending admin review',
                'naira_amount' => 0,
            ]);
        } catch (\Throwable $e) { Log::warning('Crypto withdrawal admin alert failed', ['withdrawal_id'=>$withdrawal->id,'error'=>$e->getMessage()]); }

        return redirect()->route('wallet.history')->with('success', 'Withdrawal request submitted');
    }

    /**
     * Show transaction history
     */
    public function history()
    {
        $user = Auth::user();

        $deposits = CryptoDeposit::where('user_id', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'deposits_page');

        $withdrawals = \App\Models\CryptoWithdrawal::where('user_id', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'withdrawals_page');

        $awaitingCount = CryptoDeposit::where('user_id', $user->id)->whereIn('status', ['pending', 'awaiting_admin_review'])->count()
            + \App\Models\CryptoWithdrawal::where('user_id', $user->id)->whereIn('status', ['pending', 'processing'])->count();

        return view('wallet.history', compact('deposits', 'withdrawals', 'awaitingCount'));
    }
}
