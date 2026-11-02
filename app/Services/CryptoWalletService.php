<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\User;
use App\Models\CryptoDeposit;
use App\Models\CryptoRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptoWalletService
{
    protected $cryptomusService;

    public function __construct()
    {
        $this->cryptomusService = new CryptomusService();
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
    public function createCryptoDeposit(User $user, string $currency, float $amount): array
    {
        if (!$this->cryptomusService->isEnabled()) {
            throw new \Exception('Crypto payments are not available');
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
        ]);

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
                'payment_data' => $payment['result'],
            ]);

            return [
                'deposit' => $deposit,
                'payment_url' => $payment['result']['url'] ?? null,
                'payment_address' => $payment['result']['address'] ?? null,
                'expected_amount' => $amount,
                'currency' => $currency,
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
        if (!$deposit || $deposit->status !== 'pending') {
            return;
        }

        $providerStatus = strtolower((string) ($webhookData['status'] ?? $webhookData['payment_status'] ?? ''));
        $paymentData = array_merge($deposit->payment_data ?? [], [
            'provider_status' => $providerStatus ?: 'unknown',
            'latest_webhook' => $webhookData,
            'updated_at' => now()->toIso8601String(),
        ]);
        $deposit->payment_data = $paymentData;
        if (!empty($webhookData['txid'])) {
            $deposit->transaction_hash = (string) $webhookData['txid'];
        }

        // Cryptomus reports confirm_check while confirmations are still pending.
        // Record progress but never credit on a non-final status.
        if (!in_array($providerStatus, ['paid', 'paid_over'], true)) {
            if (in_array($providerStatus, ['fail', 'cancel', 'system_fail'], true)) {
                $deposit->status = 'failed';
            }
            $deposit->save();
            return;
        }

        DB::transaction(function () use ($deposit, $webhookData, $providerStatus) {
            $locked = CryptoDeposit::whereKey($deposit->id)->lockForUpdate()->first();
            if (!$locked || $locked->status !== 'pending') {
                return;
            }

            $txid = (string) ($webhookData['txid'] ?? $locked->transaction_hash ?? '');
            $paidCrypto = (float) ($webhookData['payer_amount'] ?? $webhookData['payment_amount'] ?? 0);

            if (strtoupper($locked->currency) === 'BTC') {
                $payerCurrency = strtoupper((string) ($webhookData['payer_currency'] ?? $webhookData['currency'] ?? 'BTC'));
                if ($payerCurrency !== 'BTC') {
                    throw new \RuntimeException('Cryptomus reported a non-BTC payer currency for a BTC deposit.');
                }
                if ($paidCrypto <= 0) {
                    throw new \RuntimeException('Cryptomus did not provide the actual BTC amount received.');
                }

                $usdValue = (float) ($webhookData['payment_amount_usd'] ?? 0);
                if ($usdValue <= 0 && $providerStatus === 'paid') {
                    $usdValue = (float) ($webhookData['amount'] ?? 0);
                }
                if ($usdValue <= 0 && $providerStatus === 'paid') {
                    $usdValue = (float) $locked->expected_amount;
                }
                $rate = CryptoRate::where('coin', 'BTC')->value('sell_rate');
                if (!$rate || $usdValue <= 0) {
                    throw new \RuntimeException('BTC NGN conversion rate or verified USD payment amount is unavailable.');
                }

                $ngnAmount = round($usdValue * (float) $rate, 2);
                $user = $locked->user()->lockForUpdate()->first();
                if (!$user) {
                    throw new \RuntimeException('BTC deposit user record was not found.');
                }

                // User.balance is the canonical NGN balance used by trade flows.
                $user->increment('balance', $ngnAmount);
                $locked->ngn_credited_amount = $ngnAmount;
                $locked->ngn_rate_used = $rate;
                $locked->received_amount = $paidCrypto > 0 ? $paidCrypto : null;
            } else {
                $locked->received_amount = $paidCrypto > 0 ? $paidCrypto : (float) ($webhookData['amount'] ?? $locked->expected_amount);
                $wallet = $locked->wallet;
                if (!$wallet) {
                    throw new \RuntimeException('Crypto deposit wallet record was not found.');
                }
                $wallet->credit((float) $locked->received_amount);
            }

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

    /** Refresh provider status and public Bitcoin confirmation progress for an owned deposit. */
    public function refreshDepositStatus(CryptoDeposit $deposit): CryptoDeposit
    {
        if ($deposit->status === 'pending' && $deposit->cryptomus_payment_id) {
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
}
