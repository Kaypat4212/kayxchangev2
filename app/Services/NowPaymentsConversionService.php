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
    private const PLATFORM_SWAP_ASSETS = ['BTC', 'ETH', 'USDT', 'USDC', 'BNB', 'SOL', 'XRP', 'DOGE', 'LTC', 'TRX', 'ADA', 'TON'];

    public function __construct(private readonly NowPaymentsService $provider)
    {
    }

    public function totalFeePercent(): float
    {
        $platformFee = max(0, min(19.5, (float) AdminSetting::getSetting('convert_fee_percent', '0.5')));
        return round($platformFee + self::PROVIDER_FEE_PERCENT, 4);
    }

    public function supportedCoins(): array
    {
        $saved = trim((string) AdminSetting::getSetting('swap_supported_coins', ''));
        $coins = $saved === '' ? self::PLATFORM_SWAP_ASSETS : preg_split('/[,\s]+/', strtoupper($saved), -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_intersect(array_unique($coins), self::PLATFORM_SWAP_ASSETS));
    }

    public function supportsCoin(string $coin): bool
    {
        return in_array(strtoupper($coin), $this->supportedCoins(), true);
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
        if ($fromCoin === $toCoin || !$this->supportsCoin($fromCoin) || !$this->supportsCoin($toCoin)) {
            throw new RuntimeException('Choose a supported, different cryptocurrency pair.');
        }
        $amount = round($amount, 8);
        if ($amount <= 0) throw new RuntimeException('Enter a valid swap amount.');
        $existing = Conversion::where('idempotency_key', $requestKey)->where('user_id', $user->id)->first();
        if ($existing) {
            if (strtoupper($existing->from_coin) !== $fromCoin || strtoupper($existing->to_coin) !== $toCoin || abs((float) $existing->from_amount - $amount) > 0.00000001) {
                throw new RuntimeException('This swap confirmation was already used for a different request. Refresh the swap page before submitting again.');
            }
            return $existing;
        }
        // Suppress accidental double submissions even if the browser generated
        // a fresh key after a slow response. Return the in-flight swap instead.
        $duplicate = Conversion::query()->where('user_id', $user->id)->where('mode', 'nowpayments')
            ->where('from_coin', $fromCoin)->where('to_coin', $toCoin)->where('from_amount', $amount)
            ->whereIn('status', ['pending', 'processing'])->where('created_at', '>=', now()->subMinutes(3))
            ->latest('id')->first();
        if ($duplicate) return $duplicate;

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
                    Log::warning('NOWPayments Custody reserve check blocked a swap.', [
                        'user_id' => $user->id, 'asset' => $fromCoin, 'network' => $sourceNetwork,
                        'requested_amount' => $amount, 'provider_available' => $providerAvailable,
                        'platform_wallet_liabilities' => $userLiabilities,
                    ]);
                    throw new RuntimeException('Swaps for ' . $fromCoin . ' are temporarily unavailable while platform liquidity is being checked. Try another asset or contact support if this continues.');
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

    /** Sell an existing KayXchange crypto-wallet balance through NOWPayments Custody and settle NGN after provider confirmation. */
    public function submitWalletSell(User $user, Wallet $wallet, float $amount, array $quote, string $requestKey): Conversion
    {
        if (!$this->provider->isWalletSellReady()) {
            throw new RuntimeException('NOWPayments wallet selling is currently disabled or not fully configured.');
        }
        $this->provider->prepareCustodyAccess();
        $coin = strtoupper((string) $wallet->currency);
        if (!$this->supportsCoin($coin)) throw new RuntimeException('This asset is not enabled for NOWPayments wallet sales.');
        $amount = round($amount, 8);
        if ($amount <= 0) throw new RuntimeException('Enter a valid amount to sell.');

        $existing = Conversion::query()->where('idempotency_key', $requestKey)->where('user_id', $user->id)->first();
        if ($existing) {
            if (strtoupper($existing->from_coin) !== $coin || strtoupper($existing->to_coin) !== 'NGN' || abs((float) $existing->from_amount - $amount) > 0.00000001) {
                throw new RuntimeException('This sell confirmation was already used for a different request. Refresh and try again.');
            }
            return $existing;
        }
        $duplicate = Conversion::query()->where('user_id', $user->id)->where('mode', 'nowpayments')
            ->where('from_coin', $coin)->where('to_coin', 'NGN')->where('from_amount', $amount)
            ->whereIn('status', ['pending', 'processing'])->where('created_at', '>=', now()->subMinutes(3))->latest('id')->first();
        if ($duplicate) return $duplicate;

        $lock = Cache::lock('nowpayments-custody-swap:' . $coin, 90);
        try {
            return $lock->block(10, function () use ($user, $wallet, $coin, $amount, $quote, $requestKey): Conversion {
                $existing = Conversion::query()->where('idempotency_key', $requestKey)->where('user_id', $user->id)->first();
                if ($existing) return $existing;
                $source = $user->wallets()->whereKey($wallet->id)->where('currency', $coin)->where('is_active', true)->first();
                if (!$source || (float) $source->balance + 0.00000001 < $amount) {
                    throw new RuntimeException('Your ' . $coin . ' wallet does not have enough available balance.');
                }

                $network = strtoupper((string) $source->network);
                $fromProviderCode = $this->providerCurrencyCode($coin, $network);
                $toProviderCode = $this->providerCurrencyCode('USDT', 'TRC20');
                $providerAvailable = $this->provider->custodyAvailableBalance($fromProviderCode);
                $liabilities = (float) Wallet::query()->where('currency', $coin)
                    ->where(function ($query) use ($network) { $query->where('network', $network)->orWhereNull('network'); })
                    ->sum('balance');
                if ($providerAvailable + 0.00000001 < $liabilities) {
                    Log::warning('NOWPayments wallet sale reserve check blocked a request.', [
                        'user_id' => $user->id, 'asset' => $coin, 'network' => $network,
                        'requested_amount' => $amount, 'provider_available' => $providerAvailable, 'wallet_liabilities' => $liabilities,
                    ]);
                    throw new RuntimeException('NOWPayments Custody reserves do not currently cover ' . $coin . ' wallet balances. Your crypto is unchanged; please try later or contact support.');
                }

                $conversion = DB::transaction(function () use ($user, $source, $coin, $network, $amount, $quote, $requestKey, $fromProviderCode, $toProviderCode): Conversion {
                    User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
                    $lockedSource = Wallet::query()->whereKey($source->id)->lockForUpdate()->first();
                    if (!$lockedSource || !$lockedSource->is_active || (float) $lockedSource->balance + 0.00000001 < $amount) {
                        throw new RuntimeException('Your ' . $coin . ' wallet balance changed. Refresh the page and try again.');
                    }
                    $lockedSource->balance = round((float) $lockedSource->balance - $amount, 8);
                    $lockedSource->save();
                    app(WalletLedgerService::class)->record($lockedSource, -$amount, 'crypto_wallet_sell.reserved', $requestKey);

                    return Conversion::create([
                        'user_id' => $user->id,
                        'from_coin' => $coin,
                        'to_coin' => 'NGN',
                        'from_amount' => $amount,
                        'to_amount' => round((float) $quote['estimated_naira'], 8),
                        'fee_amount' => 0,
                        'rate_used' => round((float) $quote['estimated_naira'] / $amount, 8),
                        'status' => 'pending',
                        'mode' => 'nowpayments',
                        'idempotency_key' => $requestKey,
                        'cryptomus_response' => [
                            'wallet_sell_to_ngn' => true,
                            'source_network' => $network,
                            'source_provider_currency' => $fromProviderCode,
                            'provider_destination_currency' => $toProviderCode,
                            'ngn_rate_used' => (float) $quote['ngn_rate'],
                            'market_price_usd' => (float) $quote['market_price_usd'],
                            'quoted_usd_value' => (float) $quote['usd_value'],
                            'estimated_quoted_naira' => (float) $quote['estimated_naira'],
                            'platform_fee_percent' => max(0, min(19.5, (float) AdminSetting::getSetting('convert_fee_percent', '0.5'))),
                            'provider_fee_percent_estimate' => self::PROVIDER_FEE_PERCENT,
                            'source_reserved_at' => now()->toIso8601String(),
                        ],
                    ]);
                });

                try {
                    if ($coin === 'USDT') {
                        // USDT is already the settlement asset; after the provider
                        // reserve check there is no useful USDT→USDT conversion to request.
                        $conversion->update(['status' => 'processing', 'provider_status' => 'custody_balance_reserved']);
                        $this->settleWalletSale($conversion->fresh(), ['status' => 'finished', 'source' => 'custody_balance_reserve_check'], $amount);
                        return $conversion->fresh();
                    }
                    $response = $this->provider->createCustodyConversion($fromProviderCode, $toProviderCode, number_format($amount, 8, '.', ''));
                    $externalId = (string) ($response['id'] ?? $response['conversion_id'] ?? '');
                    $status = strtolower((string) ($response['status'] ?? 'created'));
                    if ($externalId === '') {
                        $conversion->update([
                            'status' => 'processing', 'provider_status' => $status,
                            'failure_reason' => 'NOWPayments may have accepted the sale but returned no conversion ID. The crypto remains reserved until it is reconciled.',
                            'cryptomus_response' => array_merge($conversion->cryptomus_response ?? [], ['provider_response' => $response]),
                        ]);
                    } else {
                        $conversion->update([
                            'provider_conversion_id' => $externalId, 'provider_status' => $status, 'status' => 'processing',
                            'cryptomus_response' => array_merge($conversion->cryptomus_response ?? [], ['provider_response' => $response]),
                        ]);
                        $this->applyProviderStatus($conversion->fresh(), $response);
                    }
                } catch (NowPaymentsApiException $exception) {
                    if ($exception->httpStatus === 0 || $exception->httpStatus >= 500) {
                        $conversion->update(['status' => 'processing', 'failure_reason' => 'NOWPayments response is uncertain. The reserved amount will be reconciled before any refund or Naira credit.']);
                    } else {
                        $this->refundSource($conversion, 'NOWPayments rejected the wallet sale.');
                        throw new RuntimeException('NOWPayments could not start the wallet sale. Your crypto balance has been restored.');
                    }
                } catch (\Throwable $exception) {
                    if ($conversion->provider_conversion_id) {
                        Log::warning('NOWPayments wallet sale status check failed after creation.', ['conversion_id' => $conversion->id, 'error' => $exception->getMessage()]);
                        $conversion->update(['status' => 'processing']);
                    } else {
                        $this->refundSource($conversion, $exception->getMessage());
                        throw $exception;
                    }
                }

                return $conversion->fresh();
            });
        } catch (\Illuminate\Cache\LockTimeoutException) {
            throw new RuntimeException('A transaction for this crypto asset is already processing. Please try again shortly.');
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
            if (!empty(data_get($conversion->cryptomus_response, 'wallet_sell_to_ngn'))) {
                $this->settleWalletSale($conversion, $payload, $actualProviderOutput);
                return;
            }
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

    private function settleWalletSale(Conversion $conversion, array $payload, float $providerUsdtOutput): void
    {
        $result = DB::transaction(function () use ($conversion, $payload, $providerUsdtOutput): ?float {
            $locked = Conversion::query()->whereKey($conversion->id)->lockForUpdate()->first();
            if (!$locked || in_array($locked->status, ['completed', 'failed'], true)) return null;
            $platformFeePercent = max(0, min(19.5, (float) data_get($locked->cryptomus_response, 'platform_fee_percent', 0)));
            $platformFeeUsdt = round($providerUsdtOutput * $platformFeePercent / 100, 8);
            $netUsdt = round($providerUsdtOutput - $platformFeeUsdt, 8);
            $ngnRate = (float) data_get($locked->cryptomus_response, 'ngn_rate_used', 0);
            $naira = round($netUsdt * $ngnRate, 2);
            if ($netUsdt <= 0 || $ngnRate <= 0 || $naira <= 0) {
                $locked->status = 'processing';
                $locked->failure_reason = 'NOWPayments completed the crypto conversion, but a valid Naira settlement amount could not be calculated. Admin review is required.';
                $locked->cryptomus_response = array_merge($locked->cryptomus_response ?? [], [
                    'actual_provider_output_usdt' => round($providerUsdtOutput, 8),
                    'settlement_status' => 'needs_admin_review',
                    'provider_result' => $payload,
                ]);
                $locked->save();
                return 0.0;
            }
            app(BalanceSettlementService::class)->creditNgnBalance((int) $locked->user_id, $naira, 'crypto_wallet_sell.completed', 'conversion:' . $locked->id);
            $locked->to_amount = $naira;
            $locked->fee_amount = round($platformFeeUsdt, 8);
            $locked->status = 'completed';
            $locked->provider_status = strtolower((string) ($payload['status'] ?? 'finished'));
            $locked->completed_at = now();
            $locked->failure_reason = null;
            $locked->cryptomus_response = array_merge($locked->cryptomus_response ?? [], [
                'actual_provider_output_usdt' => round($providerUsdtOutput, 8),
                'actual_platform_fee_usdt' => $platformFeeUsdt,
                'actual_net_usdt' => $netUsdt,
                'actual_naira_credited' => $naira,
                'provider_result' => $payload,
                'settled_at' => now()->toIso8601String(),
            ]);
            $locked->save();
            return $naira;
        });

        if ($result === null || $result <= 0) return;
        try {
            \App\Models\Notification::createForUser(
                (int) $conversion->user_id,
                'success',
                'Crypto sale completed',
                'Your ' . $conversion->from_coin . ' sale completed and ₦' . number_format($result, 2) . ' was credited to your Naira wallet.',
                ['conversion_id' => $conversion->id, 'type' => 'crypto_wallet_sell']
            );
        } catch (\Throwable $exception) {
            Log::warning('NOWPayments crypto wallet sale notification failed.', ['conversion_id' => $conversion->id, 'error' => $exception->getMessage()]);
        }
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
            app(WalletLedgerService::class)->record($wallet, (float) $locked->from_amount, !empty(data_get($locked->cryptomus_response, 'wallet_sell_to_ngn')) ? 'crypto_wallet_sell.refund' : 'crypto_swap.refund', 'conversion:' . $locked->id);
            $locked->status = 'failed';
            $locked->failure_reason = $reason;
            $locked->completed_at = now();
            $locked->save();
            return true;
        });
        if ($refunded) {
            try {
                $isWalletSale = !empty(data_get($conversion->cryptomus_response, 'wallet_sell_to_ngn'));
                \App\Models\Notification::createForUser(
                    (int) $conversion->user_id,
                    'error',
                    $isWalletSale ? 'Crypto sale failed' : 'Crypto swap failed',
                    'The ' . ($isWalletSale ? 'sale' : 'swap') . ' could not be completed. Your ' . $conversion->from_coin . ' balance has been restored.',
                    ['conversion_id' => $conversion->id, 'type' => $isWalletSale ? 'crypto_wallet_sell' : 'crypto_swap']
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
            'XRP' => ['XRP' => 'xrp'],
            'DOGE' => ['DOGE' => 'doge'],
            'LTC' => ['LTC' => 'ltc'],
            'TRX' => ['TRC20' => 'trx', 'TRX' => 'trx'],
            'ADA' => ['ADA' => 'ada'],
            'TON' => ['TON' => 'ton'],
        ];
        $code = $map[strtoupper($coin)][strtoupper($network)] ?? null;
        if (!$code && in_array(strtoupper($coin), ['BTC','XRP','DOGE','LTC','ADA','TON'], true) && strtoupper($network) === strtoupper($coin)) {
            $code = strtolower($coin);
        }
        if (!$code) throw new RuntimeException('The selected coin wallet has no supported NOWPayments network.');
        return $code;
    }

    private function defaultNetwork(string $coin): string
    {
        return ['BTC' => 'BTC', 'ETH' => 'ERC20', 'USDT' => 'TRC20', 'USDC' => 'ERC20', 'BNB' => 'BEP20', 'SOL' => 'SOL', 'XRP'=>'XRP', 'DOGE'=>'DOGE', 'LTC'=>'LTC', 'TRX'=>'TRC20', 'ADA'=>'ADA', 'TON'=>'TON'][$coin] ?? 'ERC20';
    }
}
