<?php

namespace App\Services;

use App\Models\BuyTrade;
use App\Models\Conversion;
use App\Models\CryptoDeposit;
use App\Models\CryptoWithdrawal;
use App\Models\Deposit;
use App\Models\GiftCardOrder;
use App\Models\GiftCardSaleOrder;
use App\Models\Notification;
use App\Models\PortfolioWithdrawal;
use App\Models\SellTrade;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UserActivityFeedService
{
    public function forUser(User $user, int $perSourceLimit = 100, ?array $market = null, ?float $usdtRate = null): Collection
    {
        $userId = (int) $user->id;
        $perSourceLimit = max(5, min(200, $perSourceLimit));
        $market ??= app(ExchangeRateService::class)->getUsdRates();
        $usdtRate ??= (float) (app(CryptoTradeRateService::class)->rate('USDT', 'sell') ?? 0);
        $activities = collect();

        $append = function (string $model, string $type, callable $map) use (&$activities, $userId, $perSourceLimit): void {
            try {
                foreach ($model::query()->where('user_id', $userId)->latest()->limit($perSourceLimit)->get() as $row) {
                    $activities->push(array_merge($map($row), [
                        'type' => $type,
                        'created_at' => $row->created_at,
                    ]));
                }
            } catch (\Throwable $exception) {
                Log::notice('Optional user activity source could not be loaded.', [
                    'source' => $type,
                    'user_id' => $userId,
                    'error' => $exception->getMessage(),
                ]);
            }
        };

        $append(BuyTrade::class, 'buy', fn ($row) => [
            'title' => 'Crypto buy request', 'detail' => strtoupper((string)$row->coin) . ' purchase',
            'amount' => $row->naira_amount, 'currency' => 'NGN', 'status' => $row->status,
            'reference' => $row->transaction_ref, 'icon' => 'bi-arrow-down-left',
            'url' => route('buy.success', $row->id), 'action' => 'View trade',
        ]);
        $append(SellTrade::class, 'sell', function ($row) use ($usdtRate) {
            $settled = strtolower((string)$row->status) === 'completed';
            $walletCredit = $settled && (string)$row->payment_method === 'wallet_balance';
            $ngn = (float)($row->naira_amount ?? 0);
            return [
                'title' => 'Crypto sell request',
                'detail' => strtoupper((string)$row->coin) . ' sale · ' . ($walletCredit ? 'Naira wallet credited' : ($settled ? 'Payout marked completed' : 'Payout after approval')),
                'amount' => $ngn, 'currency' => 'NGN', 'amount_ngn' => $ngn,
                'amount_usd' => $usdtRate > 0 ? $ngn / $usdtRate : (float)($row->usd_amount ?? 0),
                'balance_effect' => $walletCredit ? 'Balance increased' : ($settled ? 'Payout completed' : 'Awaiting payout'),
                'direction' => $walletCredit ? 'in' : 'pending', 'status' => $row->status,
                'reference' => $row->transaction_ref, 'icon' => 'bi-arrow-up-right',
                'url' => route('sell.success', $row->id), 'action' => 'View trade',
            ];
        });
        $append(Withdrawal::class, 'withdrawal', function ($row) use ($usdtRate) {
            $approved = in_array(strtolower((string)$row->status), ['approved','successful','completed'], true);
            $ngn = (float)$row->amount + (float)($row->fee_amount ?? 0);
            return [
                'title' => 'Naira withdrawal', 'detail' => 'Bank payout request · ' . ($approved ? 'Naira wallet debited' : 'Debit on approval'),
                'amount' => $ngn, 'currency' => 'NGN', 'amount_ngn' => $ngn,
                'amount_usd' => $usdtRate > 0 ? $ngn / $usdtRate : null,
                'balance_effect' => $approved ? 'Balance decreased' : 'Awaiting approval',
                'direction' => $approved ? 'out' : 'pending', 'status' => $row->status,
                'reference' => $row->reference, 'icon' => 'bi-bank',
                'url' => route('wallet.history'), 'action' => 'View wallet history',
            ];
        });
        $append(Deposit::class, 'deposit', function ($row) use ($usdtRate) {
            $net = max(0, (float)$row->amount - (float)($row->fee_amount ?? 0));
            $posted = in_array(strtolower((string)$row->status), ['approved','completed','successful'], true);
            return [
                'title' => 'Naira deposit',
                'detail' => ucfirst((string)($row->payment_method ?? 'Deposit')) . ($posted ? ' · Naira wallet credited' : ' · Credit after confirmation'),
                'amount' => $net, 'currency' => 'NGN', 'amount_ngn' => $net,
                'amount_usd' => $usdtRate > 0 ? $net / $usdtRate : null,
                'balance_effect' => $posted ? 'Balance increased' : 'Awaiting credit',
                'direction' => $posted ? 'in' : 'pending', 'status' => $row->status,
                'reference' => $row->transaction_ref, 'icon' => 'bi-box-arrow-in-down',
                'url' => route('deposits.index'), 'action' => 'View deposits',
            ];
        });
        $append(CryptoDeposit::class, 'crypto_deposit', function ($row) use ($market, $usdtRate) {
            $coin = strtoupper((string)$row->currency);
            $crypto = (float)($row->received_amount ?: $row->expected_amount);
            $usd = $crypto * (float)($market[$coin] ?? 0);
            $ngn = (float)($row->ngn_credited_amount ?? 0);
            $posted = strtolower((string)$row->status) === 'completed';
            return [
                'title' => 'Crypto deposit',
                'detail' => number_format($crypto, 8) . ' ' . $coin . ($posted ? ' · Crypto balance credited' : ' · Credit after provider confirmation'),
                'amount' => $crypto, 'currency' => $coin, 'amount_usd' => $usd > 0 ? $usd : null,
                'amount_ngn' => $ngn > 0 ? $ngn : ($usd > 0 && $usdtRate > 0 ? $usd * $usdtRate : null),
                'balance_effect' => $posted ? 'Balance increased' : 'Awaiting credit',
                'direction' => $posted ? 'in' : 'pending', 'status' => $row->status,
                'reference' => $row->transaction_hash ?: ('Deposit #' . $row->id), 'icon' => 'bi-currency-bitcoin',
                'url' => route('wallet.deposit.success', $row->id), 'action' => 'View deposit',
            ];
        });
        $append(CryptoWithdrawal::class, 'crypto_withdrawal', function ($row) use ($market, $usdtRate) {
            $row->loadMissing('wallet');
            $coin = strtoupper((string)($row->wallet?->currency ?? 'Crypto'));
            $usd = (float)($row->usd_value_at_request ?? 0);
            if ($usd <= 0) $usd = (float)$row->amount * (float)($market[$coin] ?? 0);
            $posted = strtolower((string)$row->status) === 'completed';
            return [
                'title' => 'Crypto withdrawal',
                'detail' => number_format((float)$row->amount, 8) . ' ' . $coin . ' · ' . ($posted ? 'Crypto wallet debited' : 'Debit on payout completion'),
                'amount' => $row->amount, 'currency' => $coin, 'amount_usd' => $usd > 0 ? $usd : null,
                'amount_ngn' => $usd > 0 && $usdtRate > 0 ? $usd * $usdtRate : null,
                'balance_effect' => $posted ? 'Balance decreased' : 'Awaiting payout',
                'direction' => $posted ? 'out' : 'pending', 'status' => $row->status,
                'reference' => $row->transaction_hash ?: ('Withdrawal #' . $row->id), 'icon' => 'bi-box-arrow-up-right',
                'url' => route('wallet.history'), 'action' => 'View wallet history',
            ];
        });
        $append(Conversion::class, 'swap', fn ($row) => [
            'title' => 'Crypto swap', 'detail' => number_format((float)$row->from_amount, 8) . ' ' . strtoupper((string)$row->from_coin) . ' → ' . strtoupper((string)$row->to_coin),
            'amount' => $row->to_amount, 'currency' => strtoupper((string)$row->to_coin), 'status' => $row->status,
            'reference' => $row->provider_conversion_id ?: ('Swap #' . $row->id), 'icon' => 'bi-arrow-left-right',
            'url' => route('wallet.history'), 'action' => 'View wallet history',
        ]);
        $append(PortfolioWithdrawal::class, 'portfolio', fn ($row) => [
            'title' => 'Crypto portfolio converted', 'detail' => 'Crypto assets converted to your Naira wallet',
            'amount' => $row->naira_amount, 'currency' => 'NGN', 'status' => $row->status,
            'reference' => $row->reference, 'icon' => 'bi-pie-chart',
            'url' => route('portfolio.index'), 'action' => 'View portfolio',
        ]);
        $append(GiftCardOrder::class, 'gift_card_purchase', fn ($row) => [
            'title' => 'Gift card purchase', 'detail' => $row->card_name . ' · ' . $row->country,
            'amount' => $row->total_ngn, 'currency' => 'NGN', 'status' => $row->status,
            'reference' => $row->reference, 'icon' => 'bi-gift',
            'url' => route('gift-cards.index'), 'action' => 'View gift cards',
        ]);
        $append(GiftCardSaleOrder::class, 'gift_card_sale', fn ($row) => [
            'title' => 'Gift card sale', 'detail' => $row->card_name . ' · ' . $row->country,
            'amount' => $row->approved_ngn ?? $row->estimated_ngn, 'currency' => 'NGN', 'status' => $row->status,
            'reference' => $row->reference, 'icon' => 'bi-gift',
            'url' => route('gift-cards.index'), 'action' => 'View gift cards',
        ]);
        $append(\App\Models\BillPayment::class, 'bill', fn ($row) => [
            'title' => 'Bill payment', 'detail' => ucfirst(str_replace('_', ' ', (string)$row->category)),
            'amount' => (float)$row->amount + (float)$row->fee, 'currency' => 'NGN', 'status' => $row->status,
            'reference' => $row->reference, 'icon' => 'bi-receipt',
            'url' => route('bills.history'), 'action' => 'View bill history',
        ]);

        try {
            foreach (\App\Models\P2pTransfer::query()->where(fn ($q) => $q->where('sender_id', $userId)->orWhere('recipient_id', $userId))->latest()->limit($perSourceLimit)->get() as $row) {
                $sent = (int)$row->sender_id === $userId;
                $activities->push([
                    'type' => 'transfer', 'title' => $sent ? 'Wallet transfer sent' : 'Wallet transfer received',
                    'detail' => $sent ? 'You sent funds to another user' : 'Funds received from another user',
                    'amount' => $sent ? $row->amount : $row->recipient_amount, 'currency' => 'NGN',
                    'status' => $row->status, 'reference' => $row->reference, 'icon' => 'bi-arrow-left-right',
                    'url' => route('wallet.transfers'), 'action' => 'View transfers', 'created_at' => $row->created_at,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::notice('User transfer activity could not be loaded.', ['user_id' => $userId, 'error' => $exception->getMessage()]);
        }
        try {
            foreach (\App\Models\LoginLog::query()->where('user_id', $userId)->where('status', 'success')->latest()->limit($perSourceLimit)->get() as $row) {
                $activities->push([
                    'type' => 'security', 'title' => 'Successful sign in', 'detail' => 'Account access · ' . ($row->ip_address ?: 'IP unavailable'),
                    'amount' => null, 'currency' => null, 'status' => 'secure', 'reference' => null, 'icon' => 'bi-shield-check',
                    'url' => route('settings.index'), 'action' => 'Review account security', 'created_at' => $row->created_at,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::notice('User sign-in activity could not be loaded.', ['user_id' => $userId, 'error' => $exception->getMessage()]);
        }
        try {
            foreach (Notification::query()->where('user_id', $userId)->latest()->limit($perSourceLimit)->get() as $row) {
                $activities->push([
                    'type' => 'account', 'title' => $row->title, 'detail' => $row->message,
                    'amount' => null, 'currency' => null, 'status' => 'update', 'reference' => null, 'icon' => 'bi-bell',
                    'notification_id' => $row->id, 'url' => route('notifications.show', $row->id), 'action' => 'View notification',
                    'created_at' => $row->created_at,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::notice('User notification activity could not be loaded.', ['user_id' => $userId, 'error' => $exception->getMessage()]);
        }

        return $activities->map(function (array $activity) {
            $activity['created_at'] = $activity['created_at'] ?? now();
            return $activity;
        })->sortByDesc('created_at')->values();
    }
}
