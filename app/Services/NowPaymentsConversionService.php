<?php

namespace App\Services;

use App\Exceptions\NowPaymentsApiException;
use App\Models\AdminSetting;
use App\Models\Conversion;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Reserves a customer's internal crypto balance, executes the matching
 * merchant-Custody conversion, and credits the destination only after NOWPayments
 * confirms a terminal successful status.
 */
class NowPaymentsConversionService
{
    private const PROVIDER_FEE_PERCENT = 0.5;

    public function __construct(private readonly NowPaymentsService $provider)
    {
    }

    public function totalFeePercent(): float
    {
        $platformFee = max(0, min(19.5, (float) AdminSetting::getSetting('convert_fee_percent', '0.5')));
        return round($platformFee + self::PROVIDER_FEE_PERCENT, 4);
    }

    public function submit(User $user, string $fromCoin, string $toCoin, float $amount, array $quote, string $requestKey): Conversion
    {
        if (!$this->provider->isCustodyConversionReady()) {
            throw new RuntimeException('Crypto swaps are temporarily unavailable. Please contact support.');
        }
        // Authenticate before reserving any user funds. Only the conversion POST
        // itself can create an ambiguous remote outcome.
        $this->provider->prepareCustodyAccess();
        $fromCoin = strtoupper($fromCoin);
        $toCoin = strtoupper($toCoin);
        $swapAssets = ['BTC', 'ETH', 'USDT', 'USDC', 'BNB', 'SOL'];
        if ($fromCoin === $toCoin || !in_array($fromCoin, $swapAssets, true) || !in_array($toCoin, $swapAssets, true)) {
            throw new RuntimeException('Choose a supported, different cryptocurrency pair.');
        }
        $amount = round($amount, 8);
        if ($amount <= 0) throw new RuntimeException('Enter a valid swap amount.');
        $existing = Conversion::where('idempotency_key', $requestKey)->where('user_id', $user->id)->first();
        if ($existing) return $existing;

        // Serialize swaps from the same asset so concurrent requests cannot
        // reserve the same local/provider liquidity at the same time.
        $lock = Cache::lock('nowpayments-custody-swap:' . $fromCoin, 90);
        try {
            return $lock->block(10, function () use ($user, $fromCoin, $toCoin, $amount, $quote, $requestKey): Conversion {
                $existing = Conversion::where('idempotency_key', $requestKey)->where('user_id', $user->id)->first();
                if ($existing) return $existing;

                $sourcePreflight = $user->wallets()->where('currency', $fromCoin)->where('is_active', true)->first();
                if (!$sourcePreflight) throw new RuntimeException('Your ' . $fromCoin . ' wallet is not available for swaps.');
                if ((float) $sourcePreflight->balance + 0.000000001 < $amount) {
                    throw new RuntimeException('Your ' . $fromCoin . ' wallet does not have enough available balance.');
                }
                $sourceNetwork = strtoupper((string) $sourcePreflight->network);
                $sourceProviderCode = $this->providerCurrencyCode($fromCoin, $sourceNetwork);
                $providerAvailable = $this->provider->custodyAvailableBalance($sourceProviderCode);
                $userLiabilities = (float) Wallet::query()->where('currency', $fromCoin)
                    ->where(function ($query) use ($sourceNetwork) {
                        $query->where('network', $sourceNetwork)->orWhereNull('network');
                    })->sum('balance');
                if ($providerAvailable + 0.00000001 < $userLiabilities) {
                    throw new RuntimeException('Swaps for ' . $fromCoin . ' are temporarily paused because confirmed platform Custody reserves do not cover current wallet balances. Please contact support.');
                }

                $conversion = DB::transaction(function () use ($user, $fromCoin, $toCoin, $amount, $quote, $requestKey): Conversion {
                    User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
                    $wallets = $user->wallets()
                        ->whereIn('currency', [$fromCoin, $toCoin])
                        ->orderBy('id')->lockForUpdate()->get();
                    $source = $wallets->first(fn (Wallet $wallet) => strtoupper($wallet->currency) === $fromCoin && $wallet->is_active);
                    if (!$source || (float) $source->balance + 0.000000001 < $amount) {
                        throw new RuntimeException('Your ' . $fromCoin . ' wallet does not have enough available balance.');
                    }

                    $destination = $wallets->first(fn (Wallet $wallet) => strtoupper($wallet->currency) === $toCoin && $wallet->is_active);
                    if (!$destination) {
                        $destination = $user->wallets()->firstOrCreate(
                            ['currency' => $toCoin],
                            ['balance' => 0, 'network' => $this->defaultNetwork($toCoin), 'address' => null, 'is_active' => true]
                        );
                        $destination = Wallet::whereKey($destination->id)->lockForUpdate()->firstOrFail();
                    }
                    if (!$destination->is_active) {
                        throw new RuntimeException('Your destination crypto wallet is currently unavailable.');
                    }
                    $sourceProviderCurrency = $this->providerCurrencyCode($fromCoin, (string) $source->network);
                    $destinationProviderCurrency = $this->providerCurrencyCode($toCoin, (string) $destination->network);

                    $source->balance = round((float) $source->balance - $amount, 8);
                    $source->save();
                    app(WalletLedgerService::class)->record($source, -$amount, 'crypto_swap.reserved', $requestKey);

                    return Conversion::create([
                        'user_id' => $user->id,
                        'from_coin' => $fromCoin,
                        'to_coin' => $toCoin,
                        'from_amount' => $amount,
                        'to_amount' => round((float) $quote['receive_amount'], 8),
                        'fee_amount' => round((float) $quote['fee_amount'], 8),
                        'rate_used' => round((float) $quote['rate'], 8),
                        'status' => 'pending',
                        'mode' => 'nowpayments',
                        'idempotency_key' => $requestKey,
                        'cryptomus_response' => [
                            'estimated_gross_amount' => round((float) $quote['to_amount'], 8),
                            'estimated_receive_amount' => round((float) $quote['receive_amount'], 8),
                            'platform_fee_percent' => max(0, min(19.5, (float) ($quote['fee_percent'] ?? self::PROVIDER_FEE_PERCENT) - self::PROVIDER_FEE_PERCENT)),
                            'provider_fee_percent_estimate' => self::PROVIDER_FEE_PERCENT,
                            'total_fee_percent_shown' => (float) ($quote['fee_percent'] ?? $this->totalFeePercent()),
                            'source_network' => $source->network,
                            'destination_network' => $destination->network,
                            'source_reserved_at' => now()->toIso8601String(),
                        ],
                    ]);
                });

                try {
                    $source = $user->wallets()->where('currency', $fromCoin)->firstOrFail();
                    $destination = $user->wallets()->where('currency', $toCoin)->firstOrFail();
                    $response = $this->provider->createCustodyConversion(
                        $this->providerCurrencyCode($fromCoin, (string) $source->network),
                        $this->providerCurrencyCode($toCoin, (string) $destination->network),
                        number_format($amount, 8, '.', '')
                    );
                    $externalId = (string) ($response['id'] ?? $response['conversion_id'] ?? '');
                    $status = strtolower((string) ($response['status'] ?? 'created'));

                    if ($externalId === '') {
                        $conversion->update([
                            'status' => 'processing',
                            'provider_status' => $status,
                            'failure_reason' => 'Provider accepted or may have accepted this swap but returned no conversion ID. Funds remain reserved pending reconciliation.',
                            'cryptomus_response' => array_merge($conversion->cryptomus_response ?? [], ['provider_response' => $response]),
                        ]);
                    } else {
                        $conversion->update([
                            'provider_conversion_id' => $externalId,
                            'provider_status' => $status,
                            'status' => 'processing',
                            'cryptomus_response' => array_merge($conversion->cryptomus_response ?? [], ['provider_response' => $response]),
                        ]);
                        $this->applyProviderStatus($conversion->fresh(), $response);
                    }
                } catch (NowPaymentsApiException $e) {
                    if ($e->httpStatus === 0 || $e->httpStatus >= 500) {
                        $conversion->update([
                            'status' => 'processing',
                            'failure_reason' => 'Provider response is uncertain. The reserved amount will not be released until the conversion is reconciled.',
                        ]);
                    } else {
                        $this->refundSource($conversion, 'Provider rejected the swap request.');
                        throw new RuntimeException('NOWPayments could not start this swap. Your source balance has been restored.');
                    }
                } catch (\Throwable $e) {
                    if ($conversion->provider_conversion_id) {
                        Log::warning('NOWPayments conversion status check failed after creation', ['conversion_id' => $conversion->id, 'error' => $e->getMessage()]);
                        $conversion->update(['status' => 'processing']);
                    } else {
                        // Validation errors before the remote request are safe to refund.
                        $this->refundSource($conversion, $e->getMessage());
                        throw $e;
                    }
                }

                return $conversion->fresh();
            });
        } catch (\Illuminate\Cache\LockTimeoutException) {
            throw new RuntimeException('Another swap is being processed for this asset. Please try again shortly.');
        }
    }

    public function refresh(Conversion $conversion): Conversion
    {
        if ($conversion->mode !== 'nowpayments' || !in_array($conversion->status, ['pending', 'processing'], true) || !$conversion->provider_conversion_id) {
            return $conversion;
        }

        $providerData = $this->provider->custodyConversionStatus((string) $conversion->provider_conversion_id);
        $this->applyProviderStatus($conversion, $providerData);
        return $conversion->fresh();
    }

    private function applyProviderStatus(Conversion $conversion, array $data): void
    {
        $payload = is_array($data['result'] ?? null) ? $data['result'] : $data;
        $status = strtolower((string) ($payload['status'] ?? $payload['conversion_status'] ?? ''));
        if ($status === '') return;

        $conversion->provider_status = $status;
        $conversion->cryptomus_response = array_merge($conversion->cryptomus_response ?? [], ['latest_provider_status' => $payload]);

        if (in_array($status, ['finished', 'completed', 'success', 'successful'], true)) {
            $providerAmount = $payload['amount_to'] ?? $payload['to_amount'] ?? $payload['received_amount'] ?? null;
            if (!is_numeric($providerAmount) || (float) $providerAmount <= 0) {
                $conversion->status = 'processing';
                $conversion->failure_reason = 'NOWPayments marked the conversion successful without a usable destination amount. Funds remain reserved pending review.';
                $conversion->save();
                return;
            }

            $actualProviderOutput = (float) $providerAmount;
            $platformFeePercent = max(0, min(19.5, (float) data_get($conversion->cryptomus_response, 'platform_fee_percent', 0)));
            $platformFee = round($actualProviderOutput * $platformFeePercent / 100, 8);
            $receiveAmount = round($actualProviderOutput - $platformFee, 8);
            if ($receiveAmount <= 0) {
                $conversion->status = 'processing';
                $conversion->failure_reason = 'The settled amount is too small to credit after the configured fee. Funds remain reserved pending review.';
                $conversion->save();
                return;
            }

            $settled = DB::transaction(function () use ($conversion, $payload, $actualProviderOutput, $platformFee, $receiveAmount): bool {
                $locked = Conversion::whereKey($conversion->id)->lockForUpdate()->first();
                if (!$locked || $locked->status === 'completed' || $locked->status === 'failed') return false;
                $wallet = Wallet::where('user_id', $locked->user_id)->where('currency', $locked->to_coin)->lockForUpdate()->first();
                if (!$wallet) throw new RuntimeException('Destination crypto wallet is missing; swap settlement was not applied.');

                $grossEstimate = (float) $locked->from_amount * (float) $locked->rate_used;
                $providerFeeEstimate = max(0, round($grossEstimate - $actualProviderOutput, 8));
                $locked->to_amount = $receiveAmount;
                $locked->fee_amount = round($providerFeeEstimate + $platformFee, 8);
                $locked->status = 'completed';
                $locked->provider_status = strtolower((string) ($payload['status'] ?? 'finished'));
                $locked->completed_at = now();
                $locked->failure_reason = null;
                $locked->cryptomus_response = array_merge($locked->cryptomus_response ?? [], [
                    'actual_provider_output' => round($actualProviderOutput, 8),
                    'actual_platform_fee' => $platformFee,
                    'actual_receive_amount' => $receiveAmount,
                    'settled_at' => now()->toIso8601String(),
                ]);
                $wallet->balance = round((float) $wallet->balance + $receiveAmount, 8);
                $wallet->save();
                app(WalletLedgerService::class)->record($wallet, $receiveAmount, 'crypto_swap.completed', 'conversion:' . $locked->id);
                $locked->save();
                return true;
            });
            if ($settled) {
                try {
                    \App\Models\Notification::createForUser(
                        (int) $conversion->user_id,
                        'success',
                        'Crypto swap completed',
                        'Your ' . $conversion->from_coin . ' to ' . $conversion->to_coin . ' swap is complete and your wallet has been credited.',
                        ['conversion_id' => $conversion->id]
                    );
                } catch (\Throwable $e) {
                    Log::warning('NOWPayments swap completion notification failed', ['conversion_id' => $conversion->id, 'error' => $e->getMessage()]);
                }
            }
            return;
        }

        if (in_array($status, ['failed', 'rejected', 'cancelled', 'canceled'], true)) {
            $conversion->save();
            $this->refundSource($conversion, 'NOWPayments reported the conversion as ' . $status . '.');
            return;
        }

        $conversion->status = 'processing';
        $conversion->save();
    }

    private function refundSource(Conversion $conversion, string $reason): void
    {
        $refunded = DB::transaction(function () use ($conversion, $reason): bool {
            $locked = Conversion::whereKey($conversion->id)->lockForUpdate()->first();
            if (!$locked || in_array($locked->status, ['completed', 'failed'], true)) return false;
            $wallet = Wallet::where('user_id', $locked->user_id)->where('currency', $locked->from_coin)->lockForUpdate()->first();
            if (!$wallet) throw new RuntimeException('Source wallet is missing; the reserved swap amount could not be restored.');
            $wallet->balance = round((float) $wallet->balance + (float) $locked->from_amount, 8);
            $wallet->save();
            app(WalletLedgerService::class)->record($wallet, (float) $locked->from_amount, 'crypto_swap.refund', 'conversion:' . $locked->id);
            $locked->status = 'failed';
            $locked->failure_reason = $reason;
            $locked->completed_at = now();
            $locked->save();
            return true;
        });
        if ($refunded) {
            try {
                \App\Models\Notification::createForUser(
                    (int) $conversion->user_id,
                    'error',
                    'Crypto swap failed',
                    'The swap could not be completed. Your ' . $conversion->from_coin . ' balance has been restored.',
                    ['conversion_id' => $conversion->id]
                );
            } catch (\Throwable $e) {
                Log::warning('NOWPayments swap refund notification failed', ['conversion_id' => $conversion->id, 'error' => $e->getMessage()]);
            }
        }
    }

    private function providerCurrencyCode(string $coin, string $network): string
    {
        $map = [
            'BTC' => ['BTC' => 'btc'],
            'ETH' => ['ERC20' => 'eth'],
            'USDT' => ['TRC20' => 'usdttrc20', 'ERC20' => 'usdterc20', 'BEP20' => 'usdtbsc'],
            'USDC' => ['ERC20' => 'usdcerc20', 'BEP20' => 'usdcbsc'],
            'BNB' => ['BEP20' => 'bnbbsc'],
            'SOL' => ['SOL' => 'sol'],
        ];
        $code = $map[strtoupper($coin)][strtoupper($network)] ?? null;
        if (!$code) throw new RuntimeException('The selected coin wallet has no supported NOWPayments network.');
        return $code;
    }

    private function defaultNetwork(string $coin): string
    {
        return ['BTC' => 'BTC', 'ETH' => 'ERC20', 'USDT' => 'TRC20', 'USDC' => 'ERC20', 'BNB' => 'BEP20', 'SOL' => 'SOL'][$coin] ?? 'ERC20';
    }
}
