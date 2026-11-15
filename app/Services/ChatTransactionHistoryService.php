<?php

namespace App\Services;

use App\Models\BillPayment;
use App\Models\BuyTrade;
use App\Models\Conversion;
use App\Models\CryptoDeposit;
use App\Models\CryptoWithdrawal;
use App\Models\Deposit;
use App\Models\GiftCardOrder;
use App\Models\GiftCardSaleOrder;
use App\Models\P2pTransfer;
use App\Models\PortfolioWithdrawal;
use App\Models\SellTrade;
use App\Models\User;
use App\Models\Withdrawal;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ChatTransactionHistoryService
{
    public function recent(User $user, int $limit = 8): Collection
    {
        $items = collect();
        $take = max(1, min($limit, 20));
        $sources = [
            [BuyTrade::class, fn ($r) => $this->item('Buy ' . strtoupper((string) $r->coin), $this->tradeAmount($r), $r->status, $r->created_at, $r->transaction_ref)],
            [SellTrade::class, fn ($r) => $this->item('Sell ' . strtoupper((string) $r->coin), $this->tradeAmount($r), $r->status, $r->created_at, $r->transaction_ref)],
            [Deposit::class, fn ($r) => $this->item('Naira deposit', $this->money($r->amount, $r->currency ?: 'NGN'), $r->status, $r->created_at, $r->transaction_ref ?: $r->gateway_reference)],
            [Withdrawal::class, fn ($r) => $this->item('Naira withdrawal', $this->money($r->amount, $r->currency ?: 'NGN'), $r->status, $r->created_at, $r->reference)],
            [CryptoDeposit::class, fn ($r) => $this->item('Crypto deposit', $this->cryptoDepositAmount($r), $r->status, $r->created_at, $r->transaction_hash ?: ('DEPOSIT-' . $r->id))],
            [CryptoWithdrawal::class, fn ($r) => $this->item('Crypto withdrawal', $this->cryptoWithdrawalAmount($r), $r->status, $r->created_at, $r->transaction_hash ?: ('WITHDRAW-' . $r->id))],
            [Conversion::class, fn ($r) => $this->item('Crypto swap', $this->cryptoAmount($r->from_amount, $r->from_coin) . ' → ' . $this->cryptoAmount($r->to_amount, $r->to_coin), $r->status, $r->created_at, $r->provider_conversion_id ?: ('SWAP-' . $r->id))],
            [PortfolioWithdrawal::class, fn ($r) => $this->item('Crypto portfolio → Naira', $this->money($r->naira_amount, 'NGN') . ' · $' . number_format((float) $r->usd_value, 2), $r->status, $r->created_at, $r->reference)],
            [GiftCardOrder::class, fn ($r) => $this->item('Gift card purchase · ' . $r->card_name, $this->money($r->total_ngn, 'NGN'), $r->status, $r->created_at, $r->reference)],
            [GiftCardSaleOrder::class, fn ($r) => $this->item('Gift card sale · ' . $r->card_name, $this->money($r->approved_ngn ?? $r->estimated_ngn, 'NGN'), $r->status, $r->created_at, $r->reference)],
            [BillPayment::class, fn ($r) => $this->item(ucfirst(str_replace('_', ' ', (string) $r->category)) . ' bill', $this->money((float) $r->amount + (float) $r->fee, 'NGN'), $r->status, $r->created_at, $r->reference)],
        ];

        foreach ($sources as [$model, $map]) {
            try {
                if (!Schema::hasTable((new $model())->getTable())) continue;
                $query = $model::query()->where('user_id', $user->id);
                if ($model === CryptoWithdrawal::class) $query->with('wallet');
                foreach ($query->latest()->limit($take)->get() as $record) $items->push($map($record));
            } catch (\Throwable) {
                // Older installations may not have every optional transaction table yet.
            }
        }

        try {
            if (Schema::hasTable((new P2pTransfer())->getTable())) {
                P2pTransfer::query()->where(fn ($q) => $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id))
                    ->latest()->limit($take)->get()->each(function ($r) use ($items, $user) {
                        $sent = (int) $r->sender_id === (int) $user->id;
                        $items->push($this->item(
                            $sent ? 'Wallet transfer sent' : 'Wallet transfer received',
                            $this->money($sent ? $r->amount : $r->recipient_amount, 'NGN'),
                            $r->status, $r->created_at, $r->reference,
                        ));
                    });
            }
        } catch (\Throwable) {
            // P2P history is optional on legacy installs.
        }

        return $items->filter()->sortByDesc('date')->take($take)->values();
    }

    public function statusIcon(string $status): string
    {
        return match (strtolower($status)) {
            'completed', 'approved', 'successful', 'success' => '✅',
            'pending', 'awaiting', 'awaiting_payment', 'partially_paid', 'underpaid' => '⏳',
            'rejected', 'failed', 'cancelled', 'expired', 'reversed' => '❌',
            default => '🔄',
        };
    }

    private function item(string $label, string $amount, mixed $status, mixed $date, mixed $reference): array
    {
        return [
            'label' => trim($label), 'amount' => $amount, 'status' => (string) ($status ?: 'unknown'),
            'date' => $date instanceof CarbonInterface ? $date : null,
            'reference' => $reference ? (string) $reference : null,
        ];
    }

    private function tradeAmount(object $record): string
    {
        $parts = [];
        if ((float) $record->naira_amount > 0) $parts[] = $this->money($record->naira_amount, 'NGN');
        if ((float) $record->usd_amount > 0) $parts[] = '$' . number_format((float) $record->usd_amount, 2);
        return $parts ? implode(' · ', $parts) : 'Amount pending';
    }

    private function cryptoDepositAmount(object $record): string
    {
        $amount = (float) $record->received_amount > 0 ? $record->received_amount : $record->expected_amount;
        $text = $this->cryptoAmount($amount, $record->currency);
        if ((float) $record->ngn_credited_amount > 0) $text .= ' · ' . $this->money($record->ngn_credited_amount, 'NGN') . ' credited';
        return $text;
    }

    private function cryptoWithdrawalAmount(object $record): string
    {
        return $this->cryptoAmount($record->amount, $record->wallet?->currency ?: 'crypto')
            . ($record->network ? ' · ' . $record->network : '');
    }

    private function cryptoAmount(mixed $amount, mixed $coin): string
    {
        $number = rtrim(rtrim(number_format((float) $amount, 8, '.', ','), '0'), '.');
        if ($number === '') $number = '0';
        return $number . ' ' . strtoupper((string) $coin);
    }

    private function money(mixed $amount, mixed $currency): string
    {
        $code = strtoupper((string) $currency);
        return $code === 'NGN' ? '₦' . number_format((float) $amount, 2) : number_format((float) $amount, 2) . ' ' . $code;
    }
}
