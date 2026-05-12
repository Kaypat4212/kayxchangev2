<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\User;
use App\Models\CryptoDeposit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            // Generate a unique wallet address for the user
            $address = $this->generateWalletAddress($crypto, $user->id);

            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'currency' => $crypto,
                'address' => $address,
                'network' => $this->getDefaultNetwork($crypto),
                'is_active' => true,
            ]);
        }
    }

    /**
     * Generate a unique wallet address for user
     * In production, this would integrate with actual wallet providers
     */
    private function generateWalletAddress(string $crypto, int $userId): string
    {
        // For demo purposes, generate a mock address
        // In production, integrate with wallet providers like Coinbase Commerce, NOWPayments, etc.
        $prefixes = [
            'BTC' => 'bc1',
            'ETH' => '0x',
            'USDT' => 'T', // TRC20
            'USDC' => '0x',
            'BNB' => 'bnb',
            'SOL' => '',
        ];

        $prefix = $prefixes[$crypto] ?? '';
        return $prefix . Str::random(32 + strlen($prefix));
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
        $orderId = $webhookData['order_id'] ?? null;
        if (!str_starts_with($orderId, 'dep_')) {
            return;
        }

        $depositId = str_replace('dep_', '', $orderId);
        $deposit = CryptoDeposit::find($depositId);

        if (!$deposit || $deposit->status !== 'pending') {
            return;
        }

        $deposit->update([
            'status' => 'completed',
            'received_amount' => $webhookData['amount'] ?? $deposit->expected_amount,
            'transaction_hash' => $webhookData['txid'] ?? null,
            'completed_at' => now(),
        ]);

        // Credit the user's wallet
        $wallet = $deposit->wallet;
        $wallet->credit($deposit->received_amount);

        Log::info('Crypto deposit completed', [
            'deposit_id' => $deposit->id,
            'user_id' => $deposit->user_id,
            'amount' => $deposit->received_amount,
            'currency' => $deposit->currency,
        ]);
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