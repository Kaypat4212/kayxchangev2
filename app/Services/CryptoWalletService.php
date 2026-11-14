<?php

namespace App\Services;

use App\Exceptions\DepositMinimumException;
use App\Exceptions\NowPaymentsApiException;
use App\Models\Wallet;
use App\Models\User;
use App\Models\CryptoDeposit;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CryptoWalletService
{
    protected $cryptomusService;
    protected NowPaymentsService $nowPaymentsService;
    protected CryptoDepositConfiguration $depositConfiguration;

    public function __construct()
    {
        $this->cryptomusService = new CryptomusService();
        $this->nowPaymentsService = new NowPaymentsService();
        $this->depositConfiguration = new CryptoDepositConfiguration();
    }

    /**
     * Create crypto wallets for a user
     */
    public function createUserCryptoWallets(User $user): void
    {
        $supportedCryptos = ['BTC', 'ETH', 'USDT', 'USDC', 'BNB', 'SOL'];

        foreach ($supportedCryptos as $crypto) {
            Wallet::firstOrCreate([
                'user_id' => $user->id,
                'currency' => $crypto,
            ], [
                'balance' => 0,
                'address' => null,
                'network' => $this->getDefaultNetwork($crypto),
                'is_active' => true,
            ]);
        }
    }

    /**
     * Get default network for crypto
     */
    private function getDefaultNetwork(string $crypto): string
    {
        $networks = [
            'BTC' => 'BTC',
            'ETH' => 'ERC20',
            'USDT' => 'TRC20',
            'USDC' => 'ERC20',
            'BNB' => 'BEP20',
            'SOL' => 'SOL',
        ];

        return $networks[$crypto] ?? 'ERC20';
    }

    /**
     * Get user's crypto wallets
     */
    public function getUserWallets(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->wallets()->where('is_active', true)->get();
    }

    /**
     * Create a crypto deposit invoice using Cryptomus
     */
    public function createCryptoDeposit(User $user, string $currency, float $amount, array $inputDetails = []): array
    {
        $provider = $this->selectedDepositProvider();
        $network = strtoupper((string)($inputDetails['network'] ?? $this->getDefaultNetwork($currency)));
        $payCurrency = null;
        $fallbackContext = null;

        if ($provider === 'nowpayments') {
            $payCurrency = $this->nowPaymentsCurrencyCode($currency, $network);
            $minimumUsd = $this->depositConfiguration->minimumUsd($currency, $network);
            if ($amount + 0.000001 < $minimumUsd) throw new DepositMinimumException($minimumUsd);
            try {
                $minimumUsd = max($minimumUsd, $this->nowPaymentsService->minimumUsd($payCurrency));
                if ($amount + 0.000001 < $minimumUsd) throw new DepositMinimumException($minimumUsd);
            } catch (DepositMinimumException $e) {
                throw $e;
            } catch (\Throwable $e) {
                // The payment endpoint performs its own authoritative amount and
                // currency validation. A separate minimum-quote outage should
                // not stop an otherwise valid invoice from being created.
                Log::warning('NOWPayments minimum quote check failed; continuing to authoritative payment creation.', [
                    'currency' => $currency,
                    'network' => $network,
                    'pay_currency' => $payCurrency,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        $wallet = $user->wallets()->where('currency', $currency)->first();
        if (!$wallet) {
            throw new \Exception('Wallet not found for currency: ' . $currency);
        }

        // Create deposit record
        $deposit = CryptoDeposit::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'currency' => $currency,
            'expected_amount' => $amount,
            'status' => 'pending',
            'expires_at' => now()->addHours(24),
            'payment_data' => ['deposit_input' => $inputDetails],
        ]);

        if ($provider === 'nowpayments') {
            if ($fallbackContext) return $this->createManualFallbackDeposit($deposit, $fallbackContext, $inputDetails);
            try {
                $payment = $this->nowPaymentsService->createPayment(
                    $amount,
                    $payCurrency,
                    'dep_' . $deposit->id,
                    route('nowpayments.webhook'),
                    'KayXchange crypto wallet deposit #' . $deposit->id
                );
            } catch (NowPaymentsApiException $e) {
                if ($e->isTransient()) {
                    $fallbackContext = $this->buildManualFallbackContext($currency, $network, $amount, $inputDetails);
                    if ($fallbackContext) {
                        $fallbackContext['reason'] = 'Provider temporarily unavailable while creating payment.';
                        return $this->createManualFallbackDeposit($deposit, $fallbackContext, $inputDetails);
                    }
                }
                $deposit->update(['status'=>'failed', 'payment_data'=>array_merge($deposit->payment_data ?? [], [
                    'provider'=>'nowpayments', 'provider_status'=>'creation_failed', 'provider_error'=>$e->getMessage(),
                ])]);
                throw $e;
            } catch (\Throwable $e) {
                $deposit->update(['status'=>'failed', 'payment_data'=>array_merge($deposit->payment_data ?? [], [
                    'provider'=>'nowpayments', 'provider_status'=>'creation_failed', 'provider_error'=>$e->getMessage(),
                ])]);
                throw $e;
            }
            $paymentData = array_merge($deposit->payment_data ?? [], [
                'provider' => 'nowpayments',
                'provider_payment_id' => (string)$payment['payment_id'],
                'provider_status' => $payment['payment_status'] ?? 'waiting',
                'provider_pay_currency' => $payCurrency,
                'expected_crypto_amount' => $payment['pay_amount'] ?? null,
                'provider_response' => $payment,
                'deposit_input' => $inputDetails,
            ]);
            $deposit->update([
                'payment_address' => $payment['pay_address'],
                'payment_url' => $payment['invoice_url'] ?? null,
                'payment_data' => $paymentData,
                'expires_at' => now()->addMinutes(max(30, min(60, (int) AdminSetting::getSetting('nowpayments_payment_window_minutes', '45')))),
            ]);
            return ['deposit'=>$deposit, 'payment_url'=>$deposit->payment_url, 'payment_address'=>$deposit->payment_address,
                'expected_amount'=>(float)($payment['pay_amount'] ?? 0), 'currency'=>$currency, 'provider'=>'nowpayments'];
        }

        // Create Cryptomus payment
        $paymentData = [
            'amount' => $amount,
            'currency' => 'USD', // Accept payment in USD equivalent
            'crypto_currency' => $currency,
            'order_id' => 'dep_' . $deposit->id,
            'url_callback' => route('cryptomus.webhook'),
            'url_return' => route('wallet.deposit.success', $deposit->id),
            'url_failed' => route('wallet.deposit.failed', $deposit->id),
            'lifetime' => 86400, // 24 hours
            'additional_data' => [
                'deposit_id' => $deposit->id,
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
            ]
        ];

        $payment = $this->cryptomusService->createPayment($paymentData);

        if (isset($payment['result'])) {
            $deposit->update([
                'cryptomus_payment_id' => $payment['result']['uuid'],
                'payment_url' => $payment['result']['url'] ?? null,
                'payment_address' => $payment['result']['address'] ?? null,
                'payment_data' => array_merge($deposit->payment_data ?? [], $payment['result'], [
                    'provider' => 'cryptomus',
                    'provider_payment_id' => $payment['result']['uuid'] ?? null,
                    'provider_status' => $payment['result']['payment_status'] ?? 'waiting',
                    'deposit_input' => $inputDetails,
                ]),
            ]);

            return [
                'deposit' => $deposit,
                'payment_url' => $payment['result']['url'] ?? null,
                'payment_address' => $payment['result']['address'] ?? null,
                'expected_amount' => $amount,
                'currency' => $currency,
                'provider' => 'cryptomus',
            ];
        }

        throw new \Exception('Failed to create crypto deposit invoice');
    }

    /**
     * Process successful crypto deposit
     */
    public function processDepositSuccess(array $webhookData): void
    {
        $orderId = (string) ($webhookData['order_id'] ?? '');
        if (!str_starts_with($orderId, 'dep_')) {
            return;
        }

        $depositId = substr($orderId, 4);
        $deposit = CryptoDeposit::find($depositId);
        $provider = strtolower((string)($webhookData['_provider'] ?? data_get($deposit?->payment_data, 'provider', 'cryptomus')));
        $isLateLocalPayment = $deposit && $provider === 'nowpayments' && (
            ($deposit->status === 'expired' && data_get($deposit->payment_data, 'local_window_expired') === true)
            || ($deposit->status === 'pending' && $deposit->expires_at && $deposit->expires_at->isPast())
        );
        if (!$deposit || (!in_array($deposit->status, ['pending', 'awaiting_admin_review'], true) && !$isLateLocalPayment)) {
            return;
        }

        $providerStatus = strtolower((string) ($webhookData['payment_status'] ?? $webhookData['status'] ?? ''));
        $paidNow = (float)($webhookData['actually_paid'] ?? $webhookData['payer_amount'] ?? 0);
        $expectedNow = (float)data_get($deposit->payment_data, 'expected_crypto_amount', 0);
        $hasPartialAmount = $expectedNow > 0 && $paidNow > 0 && $paidNow + 0.00000001 < $expectedNow;
        if ($isLateLocalPayment && $provider === 'nowpayments' && $paidNow > 0) {
            $lateNoticeSent = (bool)data_get($deposit->payment_data, 'late_payment_notice_sent', false);
            $deposit->received_amount = $paidNow;
            $deposit->transaction_hash = (string)($webhookData['txid'] ?? $deposit->transaction_hash ?? '');
            $deposit->payment_data = array_merge($deposit->payment_data ?? [], [
                'provider_status' => $providerStatus ?: 'payment_after_local_window',
                'latest_webhook' => $webhookData,
                'late_payment_after_window' => true,
                'late_payment_notice_sent' => true,
                'updated_at' => now()->toIso8601String(),
            ]);
            $deposit->save();
            if (!$lateNoticeSent) {
                \App\Models\Notification::createForUser(
                    (int)$deposit->user_id,
                    'warning',
                    'Late deposit payment detected',
                    'We detected a payment for a deposit after its payment window ended. It is being checked and requires admin review before any wallet credit.',
                    ['type'=>'crypto_deposit_late_payment','deposit_id'=>$deposit->id,'currency'=>$deposit->currency]
                );
            }
            if ($providerStatus === 'partially_paid' || $hasPartialAmount) {
                $this->recordPartialNowPayment($deposit, $webhookData, $providerStatus ?: 'partially_paid', $paidNow, $expectedNow);
                return;
            }
        }
        if ($provider === 'nowpayments' && ($providerStatus === 'partially_paid' || $hasPartialAmount)) {
            $this->recordPartialNowPayment($deposit, $webhookData, $providerStatus, $paidNow, $expectedNow);
            return;
        }
        $paymentData = array_merge($deposit->payment_data ?? [], [
            'provider_status' => $providerStatus ?: 'unknown',
            'latest_webhook' => $webhookData,
            'updated_at' => now()->toIso8601String(),
        ]);
        $deposit->payment_data = $paymentData;
        if (!empty($webhookData['txid'])) {
            $deposit->transaction_hash = (string) $webhookData['txid'];
        }

        // Neither provider gets balance credit until its final successful status.
        $isFinalPayment = $provider === 'nowpayments'
            ? $providerStatus === 'finished'
            : in_array($providerStatus, ['paid', 'paid_over'], true);
        if (!$isFinalPayment) {
            $failedStatuses = $provider === 'nowpayments' ? ['failed', 'expired', 'refunded'] : ['fail', 'cancel', 'system_fail'];
            if (in_array($providerStatus, $failedStatuses, true)) {
                $deposit->status = 'failed';
            }
            $deposit->save();
            return;
        }

        $finalReceivedAmount = (float)($webhookData['actually_paid'] ?? $webhookData['payer_amount'] ?? $webhookData['payment_amount'] ?? 0);
        if ($finalReceivedAmount > 0) {
            $deposit->received_amount = $finalReceivedAmount;
            $deposit->save();
        }

        if (($isLateLocalPayment || data_get($deposit->payment_data, 'late_payment_after_window')
            || filter_var(AdminSetting::getSetting('crypto_deposit_admin_review_enabled', '1'), FILTER_VALIDATE_BOOLEAN))
            && empty($webhookData['manual_review'])
            && empty(data_get($deposit->payment_data, 'admin_review_approved'))) {
            if ($deposit->status === 'awaiting_admin_review' && !empty(data_get($deposit->payment_data, 'admin_review_required'))) {
                $deposit->save();
                return; // Prevent repeated IPNs/poller runs from repeatedly alerting the review queue.
            }
            $deposit->received_amount = $finalReceivedAmount > 0 ? $finalReceivedAmount : (float)($deposit->received_amount ?? 0);
            $deposit->status = 'awaiting_admin_review';
            $deposit->payment_data = array_merge($deposit->payment_data ?? [], [
                'admin_review_required' => true,
                'admin_review_received_at' => now()->toIso8601String(),
            ]);
            $deposit->save();
            try {
                app(AdminTradeAlertService::class)->sendTriggeredAlert('crypto_deposit', [
                    'reference' => 'DEP-' . $deposit->id,
                    'user_name' => $deposit->user?->name, 'user_email' => $deposit->user?->email,
                    'coin' => $deposit->currency, 'usd_amount' => $deposit->expected_amount,
                    'naira_amount' => $deposit->ngn_credited_amount, 'status' => 'awaiting admin review',
                    'wallet_address' => $deposit->payment_address,
                ]);
            } catch (\Throwable $e) { Log::warning('Crypto deposit admin alert failed', ['deposit_id'=>$deposit->id,'error'=>$e->getMessage()]); }
            return;
        }

        // Take a server-side market snapshot at settlement so the history keeps
        // the value of the actual received crypto at the time it was deposited.
        $marketRates = app(ExchangeRateService::class)->getUsdRates();

        DB::transaction(function () use ($deposit, $webhookData, $providerStatus, $marketRates) {
            $locked = CryptoDeposit::whereKey($deposit->id)->lockForUpdate()->first();
            if (!$locked || !in_array($locked->status, ['pending', 'awaiting_admin_review'], true)) {
                return;
            }

            $txid = (string) ($webhookData['txid'] ?? $locked->transaction_hash ?? '');
            $paidCrypto = (float) ($webhookData['actually_paid'] ?? $webhookData['payer_amount'] ?? $webhookData['payment_amount'] ?? 0);
            $currency = strtoupper($locked->currency);
            if ($currency === 'BTC') {
                $payerCurrency = strtoupper((string) ($webhookData['pay_currency'] ?? $webhookData['payer_currency'] ?? $webhookData['currency'] ?? 'BTC'));
                if (!str_starts_with($payerCurrency, 'BTC')) throw new \RuntimeException('Payment provider reported a non-BTC payer currency for a BTC deposit.');
            }
            if ($paidCrypto <= 0) throw new \RuntimeException('The payment provider did not report the actual crypto amount received.');

            $locked->received_amount = $paidCrypto;
            $liveCoins = $marketRates['_live_coins'] ?? array_keys(array_filter($marketRates, 'is_numeric'));
            $livePrice = !($marketRates['_stale'] ?? false) && in_array($currency, $liveCoins, true) && is_numeric($marketRates[$currency] ?? null)
                ? (float) $marketRates[$currency] : null;
            $quotedPrice = (float) data_get($locked->payment_data, 'deposit_input.quoted_market_price_usd', 0);
            $marketPrice = $livePrice ?: ($quotedPrice > 0 ? $quotedPrice : null);
            $usdValue = $marketPrice !== null ? round($paidCrypto * $marketPrice, 8) : 0.0;
            if ($usdValue <= 0) {
                $expectedCrypto = (float) data_get($locked->payment_data, 'expected_crypto_amount', 0);
                $quotedUsd = (float) data_get($locked->payment_data, 'deposit_input.input_usd_amount', $locked->expected_amount);
                if ($expectedCrypto > 0 && $quotedUsd > 0) $usdValue = round($quotedUsd * ($paidCrypto / $expectedCrypto), 8);
                if ($usdValue <= 0) $usdValue = (float) ($webhookData['price_amount'] ?? $webhookData['payment_amount_usd'] ?? 0);
            }
            if ($usdValue <= 0) throw new \RuntimeException('A deposit USD valuation is unavailable; no wallet was credited.');

            $preference = strtolower((string) data_get($locked->payment_data, 'deposit_input.settlement_preference', 'usd'));
            if ($preference === 'ngn') {
                $rate = (float) data_get($locked->payment_data, 'deposit_input.quoted_ngn_per_usd', 0);
                if ($rate <= 0) $rate = (float) (app(\App\Services\CryptoTradeRateService::class)->rate($currency, 'sell') ?? 0);
                if ($rate <= 0) throw new \RuntimeException('The saved Naira settlement rate is unavailable; deposit requires admin review.');
                $ngnAmount = round($usdValue * $rate, 2);
                app(BalanceSettlementService::class)->creditNgnBalance((int) $locked->user_id, $ngnAmount, 'crypto_deposit.completed', 'crypto-deposit:' . $locked->id);
                $locked->ngn_credited_amount = $ngnAmount;
                $locked->ngn_rate_used = $rate;
            } else {
                $wallet = $locked->wallet()->lockForUpdate()->first();
                if (!$wallet) throw new \RuntimeException('Crypto deposit wallet record was not found.');
                $wallet->credit($paidCrypto, 'crypto_deposit.completed', 'crypto-deposit:' . $locked->id);
            }

            $paymentData = $locked->payment_data ?? [];
            $paymentData['deposit_valuation'] = [
                'market_price_usd' => $marketPrice,
                'market_value_usd' => $usdValue,
                'source' => $livePrice !== null ? ($marketRates['_source'] ?? 'live') : ($quotedPrice > 0 ? 'invoice_quote' : 'provider_quote'),
                'priced_at' => $livePrice !== null ? ($marketRates['_updated_at'] ?? now()->toIso8601String()) : data_get($locked->payment_data, 'deposit_input.quoted_at'),
                'settlement_preference' => $preference === 'ngn' ? 'ngn' : 'usd',
            ];
            $locked->payment_data = $paymentData;
            if ($currency === 'BTC' && $marketPrice !== null) $locked->btc_usd_price = $marketPrice;

            $locked->transaction_hash = $txid ?: $locked->transaction_hash;
            $locked->status = 'completed';
            $locked->completed_at = now();
            $locked->payment_data = array_merge($locked->payment_data ?? [], [
                'provider_status' => $providerStatus,
                'latest_webhook' => $webhookData,
                'updated_at' => now()->toIso8601String(),
            ]);
            $locked->save();

            Log::info('Crypto deposit completed', [
                'deposit_id' => $locked->id,
                'user_id' => $locked->user_id,
                'crypto_amount' => $locked->received_amount,
                'ngn_amount' => $locked->ngn_credited_amount,
                'currency' => $locked->currency,
            ]);
        });
    }

    /** Persist partial provider payments and notify the account once per newly received amount. */
    private function recordPartialNowPayment(CryptoDeposit $deposit, array $webhookData, string $providerStatus, float $paid, float $expected): void
    {
        DB::transaction(function () use ($deposit, $webhookData, $providerStatus, $paid, $expected): void {
            $locked = CryptoDeposit::whereKey($deposit->id)->lockForUpdate()->first();
            if (!$locked || !in_array($locked->status, ['pending', 'expired', 'awaiting_admin_review'], true)) return;

            $data = $locked->payment_data ?? [];
            $previousNotified = (float)($data['partial_notification_amount'] ?? 0);
            $received = $paid > 0 ? $paid : (float)($locked->received_amount ?? 0);
            $remaining = $expected > 0 ? max(0, $expected - $received) : null;
            $data = array_merge($data, [
                'provider_status' => 'partially_paid',
                'provider_reported_status' => $providerStatus,
                'latest_webhook' => $webhookData,
                'underpaid_amount' => $received,
                'partial_remaining_crypto' => $remaining,
                'partial_remaining_usd' => ($expected > 0 && $remaining !== null)
                    ? round((float)$locked->expected_amount * ($remaining / $expected), 2) : null,
                'updated_at' => now()->toIso8601String(),
            ]);
            $locked->payment_data = $data;
            if (data_get($data, 'local_window_expired') || data_get($data, 'late_payment_after_window')) {
                $locked->status = 'awaiting_admin_review';
                $locked->payment_data = array_merge($data, ['late_payment_after_window' => true, 'admin_review_required' => true]);
            }
            if ($received > 0) $locked->received_amount = $received;
            if (!empty($webhookData['txid'])) $locked->transaction_hash = (string)$webhookData['txid'];

            $needsNotice = $received > $previousNotified + 0.00000001
                || ($received <= 0 && empty($data['partial_notification_sent']));
            if ($needsNotice) {
                if ($received > 0) $data['partial_notification_amount'] = $received;
                $data['partial_notification_sent'] = true;
                $locked->payment_data = $data;
                \App\Models\Notification::createForUser(
                    (int)$locked->user_id,
                    'warning',
                    'Partial crypto deposit received',
                    ($received > 0
                        ? 'We detected ' . number_format($received, 8, '.', '') . ' ' . strtoupper($locked->currency)
                            . ' toward your $' . number_format((float)$locked->expected_amount, 2) . ' deposit. '
                            . ($remaining !== null && $remaining > 0
                                ? 'About ' . number_format($remaining, 8, '.', '') . ' ' . strtoupper($locked->currency)
                                    . ' (approximately $' . number_format((float)$locked->expected_amount * ($remaining / max($expected, 0.00000001)), 2) . ' USD) remains.'
                                : 'We are waiting for the payment status to update.')
                        : 'The payment service marked your $' . number_format((float)$locked->expected_amount, 2)
                            . ' deposit as partially paid. Open the deposit status page for the latest amount and remaining balance.'),
                    [
                        'type' => 'crypto_deposit_partial', 'deposit_id' => $locked->id,
                        'payment_id' => data_get($data, 'provider_payment_id'),
                        'received_amount' => $received, 'remaining_amount' => $remaining,
                        'remaining_usd_amount' => ($remaining !== null && $expected > 0)
                            ? round((float)$locked->expected_amount * ($remaining / $expected), 2) : null,
                        'currency' => strtoupper($locked->currency),
                    ]
                );
            }
            $locked->save();
        });
    }

    /** Refresh provider status and public Bitcoin confirmation progress for an owned deposit. */
    public function refreshDepositStatus(CryptoDeposit $deposit): CryptoDeposit
    {
        $paymentData = $deposit->payment_data ?? [];
        $provider = strtolower((string)($paymentData['provider'] ?? 'cryptomus'));
        if (in_array($deposit->status, ['pending', 'expired'], true) && $provider === 'nowpayments' && !empty($paymentData['provider_payment_id'])) {
            $lastCheck = (int) Cache::get('nowpayments.deposit.status_check.' . $deposit->id, 0);
            if (time() - $lastCheck < 30) return $deposit;
            Cache::put('nowpayments.deposit.status_check.' . $deposit->id, time(), now()->addMinutes(10));
            try {
                $providerData = $this->nowPaymentsService->paymentStatus((string)$paymentData['provider_payment_id']);
                $providerData['order_id'] = $providerData['order_id'] ?? 'dep_' . $deposit->id;
                $providerData['_provider'] = 'nowpayments';
                $this->processDepositSuccess($providerData);
                $deposit->refresh();
                if ($deposit->status === 'pending' && $deposit->expires_at && $deposit->expires_at->isPast()
                    && in_array(strtolower((string)($providerData['payment_status'] ?? '')), ['waiting', 'partially_paid'], true)) {
                    $data = $deposit->payment_data ?? [];
                    $data['local_window_expired'] = true;
                    $data['local_window_expired_at'] = now()->toIso8601String();
                    $data['provider_reported_status'] = $providerData['payment_status'] ?? 'waiting';
                    $data['provider_status'] = 'local_window_expired';
                    $deposit->payment_data = $data;
                    $deposit->status = 'expired';
                    $deposit->save();
                }
            } catch (\Throwable $e) {
                Cache::forget('nowpayments.deposit.status_check.' . $deposit->id);
                Log::warning('Could not refresh NOWPayments deposit status', ['deposit_id'=>$deposit->id,'error'=>$e->getMessage()]);
            }
        } elseif ($deposit->status === 'pending' && $deposit->cryptomus_payment_id) {
            try {
                $response = $this->cryptomusService->checkPayment($deposit->cryptomus_payment_id);
                $providerData = $response['result'] ?? [];
                if (!empty($providerData)) {
                    $providerData['order_id'] = $providerData['order_id'] ?? 'dep_' . $deposit->id;
                    $this->processDepositSuccess($providerData);
                    $deposit->refresh();
                }
            } catch (\Throwable $e) {
                Log::warning('Could not refresh Cryptomus deposit status', [
                    'deposit_id' => $deposit->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (strtoupper($deposit->currency) === 'BTC' && $deposit->transaction_hash) {
            try {
                if (!preg_match('/^[a-fA-F0-9]{64}$/', $deposit->transaction_hash)) {
                    return $deposit->fresh();
                }
                $txResponse = Http::timeout(5)->get('https://blockstream.info/api/tx/' . rawurlencode($deposit->transaction_hash) . '/status');
                if ($txResponse->successful()) {
                    $txStatus = $txResponse->json();
                    $confirmations = 0;
                    if (!empty($txStatus['confirmed']) && !empty($txStatus['block_height'])) {
                        $tipResponse = Http::timeout(5)->get('https://blockstream.info/api/blocks/tip/height');
                        if ($tipResponse->successful()) {
                            $confirmations = max(0, (int) $tipResponse->body() - (int) $txStatus['block_height'] + 1);
                        }
                    }
                    $data = $deposit->payment_data ?? [];
                    $data['bitcoin_confirmations'] = $confirmations;
                    $data['bitcoin_required_confirmations'] = 3;
                    $deposit->payment_data = $data;
                    $deposit->save();
                }
            } catch (\Throwable $e) {
                Log::notice('Could not fetch Bitcoin confirmation count', [
                    'deposit_id' => $deposit->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $deposit->fresh();
    }

    /**
     * Get supported cryptocurrencies for deposits
     */
    public function getSupportedCryptos(): array
    {
        return [
            'BTC' => ['name' => 'Bitcoin', 'networks' => ['BTC']],
            'ETH' => ['name' => 'Ethereum', 'networks' => ['ERC20']],
            'USDT' => ['name' => 'Tether', 'networks' => ['TRC20', 'ERC20', 'BEP20']],
            'USDC' => ['name' => 'USD Coin', 'networks' => ['ERC20', 'BEP20']],
            'BNB' => ['name' => 'Binance Coin', 'networks' => ['BEP20']],
            'SOL' => ['name' => 'Solana', 'networks' => ['SOL']],
        ];
    }

    private function buildManualFallbackContext(string $currency, string $network, float $usdAmount, array $inputDetails): ?array
    {
        if (!$this->depositConfiguration->fallbackEnabled()) return null;
        // A positive static floor is required when the provider is down, because
        // its live minimum endpoint is unavailable during manual fallback.
        if ($this->depositConfiguration->minimumUsd($currency, $network) <= 0) return null;
        $address = $this->depositConfiguration->fallbackAddress($currency, $network);
        if ($address === '') return null;

        $market = app(ExchangeRateService::class)->getUsdRates();
        if (($market['_stale'] ?? true) || !in_array($currency, $market['_live_coins'] ?? [], true) || !is_numeric($market[$currency] ?? null)) return null;
        $price = (float)$market[$currency];
        if ($price <= 0) return null;
        return [
            'currency'=>$currency, 'network'=>$network, 'address'=>$address,
            'expected_crypto_amount'=>round($usdAmount / $price, 8),
            'quote_price_usd'=>$price,
            'provider_pay_currency'=>$this->nowPaymentsCurrencyCode($currency, $network),
        ];
    }

    private function createManualFallbackDeposit(CryptoDeposit $deposit, array $context, array $inputDetails): array
    {
        $reason = $context['reason'] ?? 'Provider temporarily unavailable during payment setup.';
        $paymentData = array_merge($deposit->payment_data ?? [], [
            'provider'=>'manual', 'provider_status'=>'waiting_for_transfer',
            'provider_pay_currency'=>$context['provider_pay_currency'],
            'expected_crypto_amount'=>$context['expected_crypto_amount'],
            'fallback_quote_price_usd'=>$context['quote_price_usd'],
            'fallback_reason'=>$reason,
            'deposit_input'=>$inputDetails,
        ]);
        $deposit->update([
            'payment_address'=>$context['address'],
            'payment_url'=>null,
            'payment_data'=>$paymentData,
            'expires_at'=>now()->addHours((int)\App\Models\AdminSetting::getSetting('crypto_deposit_expiry_hours', '24')),
        ]);
        Log::warning('Crypto deposit switched to manual wallet fallback', ['deposit_id'=>$deposit->id,'currency'=>$deposit->currency,'network'=>$context['network']]);
        return ['deposit'=>$deposit, 'payment_url'=>null, 'payment_address'=>$context['address'],
            'expected_amount'=>(float)$context['expected_crypto_amount'], 'currency'=>$deposit->currency, 'provider'=>'manual'];
    }

    public function depositProviderLabel(): ?string
    {
        try { return $this->selectedDepositProvider() === 'nowpayments' ? 'NOWPayments' : 'Cryptomus'; }
        catch (\Throwable) { return null; }
    }

    private function selectedDepositProvider(): string
    {
        $selected = (string)(AdminSetting::getSetting('crypto_deposit_provider', 'auto') ?: 'auto');
        $nowEnabled = $this->nowPaymentsService->isEnabled();
        $cryptomusEnabled = $this->cryptomusService->isEnabled();
        if ($selected === 'nowpayments' && $nowEnabled) return 'nowpayments';
        if ($selected === 'cryptomus' && $cryptomusEnabled) return 'cryptomus';
        if ($selected === 'auto') {
            if ($nowEnabled) return 'nowpayments';
            if ($cryptomusEnabled) return 'cryptomus';
        }
        throw new \RuntimeException('Crypto deposits are currently unavailable. The selected provider is not enabled or its API credentials are incomplete.');
    }

    private function nowPaymentsCurrencyCode(string $currency, string $network): string
    {
        $map = [
            'BTC'=>['BTC'=>'btc'], 'ETH'=>['ERC20'=>'eth'],
            'USDT'=>['TRC20'=>'usdttrc20','ERC20'=>'usdterc20','BEP20'=>'usdtbsc'],
            'USDC'=>['ERC20'=>'usdcerc20','BEP20'=>'usdcbsc'],
            'BNB'=>['BEP20'=>'bnbbsc'], 'SOL'=>['SOL'=>'sol'],
        ];
        $code = $map[strtoupper($currency)][strtoupper($network)] ?? null;
        if (!$code) throw new \RuntimeException('That asset and network combination is not supported by NOWPayments.');
        return $code;
    }
}
