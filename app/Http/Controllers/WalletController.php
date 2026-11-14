<?php

namespace App\Http\Controllers;

use App\Exceptions\DepositMinimumException;
use App\Models\Wallet;
use App\Models\CryptoDeposit;
use App\Models\AdminSetting;
use App\Models\SiteContent;
use App\Services\CryptoWalletService;
use App\Services\ExchangeRateService;
use App\Services\AdminTradeAlertService;
use App\Services\CryptoTradeRateService;
use App\Services\BalanceSettlementService;
use App\Services\WalletLedgerService;
use App\Models\PortfolioWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
        $portfolioUsd = 0.0;
        $valuationComplete = true;
        foreach ($wallets as $wallet) {
            $currency = strtoupper($wallet->currency);
            if (in_array($currency, ['NGN', 'USD', 'EUR'], true)) continue;
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
        return $configured > 0 ? $configured : (float) (app(CryptoTradeRateService::class)->rate('USDT', 'sell') ?? 0);
    }

    public function usd(CryptoTradeRateService $tradeRates)
    {
        $user = Auth::user();
        $wallets = $user->wallets()->where('is_active', true)->whereNotIn('currency', ['NGN', 'USD', 'EUR'])->orderBy('currency')->get();
        $market = app(ExchangeRateService::class)->getUsdRates();
        $marketPrices = [];
        $portfolioValueUsd = 0.0;
        $valuationComplete = true;
        foreach ($wallets as $assetWallet) {
            $coin = strtoupper((string) $assetWallet->currency);
            $price = is_numeric($market[$coin] ?? null) ? (float) $market[$coin] : 0.0;
            $marketPrices[$coin] = $price;
            if ($price > 0) $portfolioValueUsd += (float) $assetWallet->balance * $price;
            elseif ((float) $assetWallet->balance > 0) $valuationComplete = false;
        }
        $usdtRates = $tradeRates->rates(['USDT'])['USDT'] ?? ['buy_rate' => null, 'sell_rate' => null];
        $configuredSellRate = (float) AdminSetting::getSetting('usd_wallet_sell_rate_ngn', 0);
        $sellRate = $configuredSellRate > 0 ? $configuredSellRate : (float) ($usdtRates['sell_rate'] ?? 0);
        $marketStale = (bool) ($market['_stale'] ?? false);
        return view('wallet.usd', compact('wallets', 'marketPrices', 'portfolioValueUsd', 'valuationComplete', 'marketStale', 'sellRate'));
    }

    public function withdrawUsdToNaira(Request $request, CryptoTradeRateService $tradeRates)
    {
        $request->validate(['pin' => 'required|digits:4']);
        $assetAmounts = collect((array) $request->input('asset_amounts', []))
            ->mapWithKeys(fn ($amount, $asset) => [strtoupper((string) $asset) => is_numeric($amount) ? round((float) $amount, 8) : 0.0])
            ->filter(fn ($amount) => $amount > 0);
        if ($assetAmounts->keys()->contains(fn ($coin) => in_array($coin, ['NGN', 'USD', 'EUR'], true))) {
            return back()->withErrors(['asset_amounts' => 'Choose a crypto wallet to convert. The USD portfolio figure itself is read-only.'])->withInput();
        }
        if ($assetAmounts->isEmpty()) {
            return back()->withErrors(['asset_amounts' => 'Enter an amount for at least one wallet to withdraw.'])->withInput();
        }
        if ($assetAmounts->count() > 12 || $assetAmounts->contains(fn ($amount) => $amount < 0.00000001 || $amount > 1000000000)) {
            return back()->withErrors(['asset_amounts' => 'One or more asset amounts are outside the supported range.'])->withInput();
        }

        $market = app(ExchangeRateService::class)->getUsdRates();
        if (($market['_stale'] ?? false) === true) {
            return back()->withErrors(['asset_amounts' => 'A fresh crypto price is required for this withdrawal. Please try again shortly.'])->withInput();
        }
        $snapshot = [];
        $usdValue = 0.0;
        foreach ($assetAmounts as $coin => $amount) {
            $price = (float) ($market[$coin] ?? 0);
            if ($price <= 0) {
                return back()->withErrors(['asset_amounts' => "A live USD price is unavailable for {$coin}. Remove it or try again later."])->withInput();
            }
            $lineUsd = round($amount * $price, 8);
            $usdValue += $lineUsd;
            $snapshot[$coin] = ['amount' => $amount, 'price_usd' => $price, 'value_usd' => $lineUsd];
        }
        $configuredRate = (float) AdminSetting::getSetting('usd_wallet_sell_rate_ngn', 0);
        $rate = $configuredRate > 0 ? $configuredRate : (float) ($tradeRates->rate('USDT', 'sell') ?? 0);
        if ($rate <= 0) {
            return back()->withErrors(['amount' => 'USD conversion is temporarily unavailable because the platform rate has not been configured.'])->withInput();
        }

        $amountNgn = round($usdValue * $rate, 2);
        if ($amountNgn < 1) return back()->withErrors(['asset_amounts' => 'The selected wallet amounts are too small to convert.'])->withInput();
        $reference = 'USD-NGN-' . Str::upper(Str::random(12));
        $result = DB::transaction(function () use ($request, $assetAmounts, $usdValue, $amountNgn, $rate, $reference, $snapshot) {
            $user = \App\Models\User::query()->lockForUpdate()->findOrFail(Auth::id());
            if ($user->pin_locked_until && Carbon::now()->lt($user->pin_locked_until)) {
                $minutes = max(1, (int) ceil(Carbon::now()->diffInSeconds($user->pin_locked_until) / 60));
                return ['error' => "PIN locked. Try again in {$minutes} minute(s)."];
            }
            if (!$user->transaction_pin || !Hash::check($request->input('pin'), $user->transaction_pin)) {
                $user->pin_attempts = (int) ($user->pin_attempts ?? 0) + 1;
                if ($user->pin_attempts >= 5) $user->pin_locked_until = Carbon::now()->addMinutes(15);
                $user->save();
                $remaining = max(0, 5 - $user->pin_attempts);
                return ['error' => "Incorrect transaction PIN. {$remaining} attempt(s) remaining."];
            }

            $user->pin_attempts = 0;
            $user->pin_locked_until = null;
            $user->save();
            $lockedWallets = Wallet::query()->where('user_id', $user->id)->whereIn('currency', $assetAmounts->keys())->orderBy('currency')->lockForUpdate()->get()->keyBy(fn ($wallet) => strtoupper((string) $wallet->currency));
            foreach ($assetAmounts as $coin => $amount) {
                $wallet = $lockedWallets->get($coin);
                if (!$wallet || (float) $wallet->balance + 0.00000001 < $amount) {
                    return ['error' => "Insufficient {$coin} wallet balance. Refresh the page and try again."];
                }
            }
            foreach ($assetAmounts as $coin => $amount) {
                $wallet = $lockedWallets->get($coin);
                $wallet->balance = round((float) $wallet->balance - $amount, 8);
                $wallet->save();
                app(WalletLedgerService::class)->record($wallet, -$amount, 'crypto_portfolio.withdraw_to_ngn', $reference, [
                    'asset' => $coin,
                    'usd_value' => $snapshot[$coin]['value_usd'],
                    'market_price_usd' => $snapshot[$coin]['price_usd'],
                    'ngn_rate_used' => $rate,
                    'ngn_credit' => $amountNgn,
                ]);
            }
            app(BalanceSettlementService::class)->adjustNgnBalance((int) $user->id, $amountNgn, 'usd_wallet.withdraw_to_ngn', $reference);
            PortfolioWithdrawal::create([
                'user_id' => $user->id,
                'reference' => $reference,
                'asset_amounts' => $assetAmounts->all(),
                'usd_value' => $usdValue,
                'ngn_rate' => $rate,
                'naira_amount' => $amountNgn,
                'status' => 'completed',
                'rate_snapshot' => $snapshot,
            ]);
            return ['success' => true];
        });

        if (!empty($result['error'])) return back()->withErrors(['pin' => $result['error']])->withInput();
        return redirect()->route('wallet.usd', ['withdraw' => 1])->with('success', '₦' . number_format($amountNgn, 2) . ' has been added to your Naira wallet. The selected crypto was converted at your confirmed portfolio rates.');
    }

    public function crypto(\App\Services\CryptoTradeRateService $tradeRates)
    {
        $wallets = Auth::user()->wallets()->where('is_active', true)->whereNotIn('currency', ['NGN', 'USD', 'EUR'])->orderBy('currency')->get();
        $rates = $tradeRates->rates($wallets->pluck('currency')->map(fn ($coin) => strtoupper($coin))->all());
        $completedDeposits = CryptoDeposit::where('user_id', Auth::id())->where('status', 'completed')->get(['currency', 'received_amount', 'payment_data']);
        $depositedTotals = $completedDeposits->groupBy(fn ($deposit) => strtoupper((string) $deposit->currency))
            ->map(fn ($items) => (float) $items->sum(fn ($deposit) => (float) ($deposit->received_amount ?? 0)))->all();
        $depositedValueUsd = (float) $completedDeposits->sum(fn ($deposit) => (float) data_get($deposit->payment_data, 'deposit_valuation.market_value_usd', 0));
        return view('wallet.crypto', compact('wallets', 'rates', 'depositedTotals', 'depositedValueUsd', 'completedDeposits'));
    }

    /**
     * Show crypto deposit page
     */
    public function deposit(Request $request)
    {
        $user = Auth::user();
        $wallets = $this->cryptoWalletService->getUserWallets($user);
        $supportedCryptos = $this->cryptoWalletService->getSupportedCryptos();

        $selectedCurrency = strtoupper((string) old('currency', $request->query('currency', '')));
        if (!array_key_exists($selectedCurrency, $supportedCryptos)) $selectedCurrency = '';
        $selectedNetwork = strtoupper((string) old('network', $request->query('network', '')));
        if (!in_array($selectedNetwork, $supportedCryptos[$selectedCurrency]['networks'] ?? [], true)) {
            $selectedNetwork = $supportedCryptos[$selectedCurrency]['networks'][0] ?? '';
        }

        $depositProvider = $this->cryptoWalletService->depositProviderLabel();
        $settlementPreference = Auth::user()->crypto_deposit_settlement_preference === 'ngn' ? 'ngn' : 'usd';
        return view('wallet.deposit', compact('wallets', 'supportedCryptos', 'depositProvider', 'selectedCurrency', 'selectedNetwork', 'settlementPreference'));
    }

    public function depositMinimum(Request $request, \App\Services\CryptoDepositConfiguration $configuration, \App\Services\NowPaymentsService $nowPayments)
    {
        $currency = strtoupper((string) $request->query('currency', ''));
        $network = strtoupper((string) $request->query('network', ''));
        $supported = $this->cryptoWalletService->getSupportedCryptos();
        abort_unless(in_array($network, $supported[$currency]['networks'] ?? [], true), 422, 'Choose a supported asset and network.');

        $adminFloor = $configuration->minimumUsd($currency, $network);
        $providerMinimum = null;
        $providerStatus = 'not_active';
        if ($this->cryptoWalletService->depositProviderLabel() === 'NOWPayments') {
            try {
                $code = $this->cryptoWalletService->nowPaymentsCurrencyCode($currency, $network);
                $minimum = $nowPayments->minimumUsd($code);
                if ($minimum > 0) {
                    $providerMinimum = round($minimum, 2);
                    $providerStatus = 'live';
                } else {
                    $providerStatus = 'unavailable';
                }
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::warning('Unable to load live NOWPayments deposit minimum for customer quote.', [
                    'currency' => $currency,
                    'network' => $network,
                    'error' => $exception->getMessage(),
                ]);
                $providerStatus = 'unavailable';
            }
        }

        return response()->json([
            'currency' => $currency,
            'network' => $network,
            'admin_minimum_usd' => round($adminFloor, 2),
            'provider_minimum_usd' => $providerMinimum,
            'minimum_usd' => max($adminFloor, $providerMinimum ?? 0),
            'provider_status' => $providerStatus,
        ])->header('Cache-Control', 'private, no-store, max-age=0');
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
                'settlement_preference' => $user->crypto_deposit_settlement_preference === 'ngn' ? 'ngn' : 'usd',
                'quoted_ngn_per_usd' => app(CryptoTradeRateService::class)->rate($currency, 'sell'),
            ];
            $result = $this->cryptoWalletService->createCryptoDeposit(
                $user,
                $currency,
                $usdAmount,
                $inputDetails
            );

            // Keep NOWPayments customers on our live status page so partial
            // payment instructions and provider status polling stay visible.
            if (($result['provider'] ?? null) === 'nowpayments') {
                return redirect()->route('wallet.deposit.success', $result['deposit']->id);
            }

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
            } elseif (str_contains($normalized, 'INVALID_API_KEY') || str_contains($normalized, 'REJECTED THE API KEY') || str_contains($normalized, 'UNAUTHORIZED')) {
                $title = 'Crypto deposits are temporarily unavailable';
                $message = 'We could not verify the secure payment service configuration. Please try again later or contact support. No balance was deducted.';
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
            if (str_contains($normalized, 'INVALID_API_KEY') || str_contains($normalized, 'REJECTED THE API KEY') || str_contains($normalized, 'UNAUTHORIZED')) {
                Cache::put('provider_health.nowpayments.last_error', [
                    'at' => now()->toIso8601String(),
                    'message' => 'Invoice authorization failed. Verify the saved API key and Sandbox/Live environment in payment settings.',
                    'environment' => AdminSetting::getSetting('nowpayments_sandbox', '0') === '1' ? 'sandbox' : 'live',
                ], now()->addDays(7));
            }

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
            'expires_at' => optional($deposit->expires_at)->toIso8601String(),
            'local_window_expired' => (bool)data_get($paymentData, 'local_window_expired', false),
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
    public function withdraw(Request $request)
    {
        return response()->view('wallet.withdraw-unavailable');
    }

    /**
     * Create a withdrawal request
     */
    public function createWithdrawal(Request $request)
    {
        return redirect()->route('wallet.withdraw')->with('error', 'External crypto withdrawals are not available yet. Your crypto remains safely in your KayXchange wallet.');
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

        $ledgerReady = \Illuminate\Support\Facades\Schema::hasTable('wallet_ledger_entries');
        $ledgerEntries = $ledgerReady
            ? \App\Models\WalletLedgerEntry::where('user_id', $user->id)->latest()->paginate(20, ['*'], 'ledger_page')
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, \Illuminate\Pagination\Paginator::resolveCurrentPage('ledger_page'), [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'pageName' => 'ledger_page',
            ]);

        $awaitingCount = CryptoDeposit::where('user_id', $user->id)->whereIn('status', ['pending', 'awaiting_admin_review'])->count()
            + \App\Models\CryptoWithdrawal::where('user_id', $user->id)->whereIn('status', ['pending', 'processing'])->count();

        return view('wallet.history', compact('deposits', 'withdrawals', 'awaitingCount', 'ledgerEntries', 'ledgerReady'));
    }
}
