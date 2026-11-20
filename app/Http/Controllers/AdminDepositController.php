<?php
namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use App\Services\BalanceSettlementService;
use App\Models\CryptoDeposit;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class AdminDepositController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:40'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'method' => ['nullable', 'string', 'max:50'],
            'asset' => ['nullable', 'string', 'max:20'],
        ]);
        $ngnQuery = Deposit::with(['user', 'companyAccount']);
        $cryptoQuery = Schema::hasTable('crypto_deposits') ? CryptoDeposit::with('user') : null;
        if (!empty($filters['q'])) {
            $needle = trim($filters['q']);
            $ngnQuery->where(function ($query) use ($needle) {
                $query->where('transaction_ref', 'like', "%{$needle}%")
                    ->orWhere('gateway_reference', 'like', "%{$needle}%")
                    ->orWhere('payment_method', 'like', "%{$needle}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$needle}%")->orWhere('email', 'like', "%{$needle}%"));
            });
            if ($cryptoQuery) $cryptoQuery->where(function ($query) use ($needle) {
                $query->where('currency', 'like', "%{$needle}%")
                    ->orWhere('cryptomus_payment_id', 'like', "%{$needle}%")
                    ->orWhere('transaction_hash', 'like', "%{$needle}%")
                    ->orWhere('payment_data', 'like', "%{$needle}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$needle}%")->orWhere('email', 'like', "%{$needle}%"));
            });
        }
        if (!empty($filters['status'])) {
            $ngnQuery->where('status', $filters['status']);
            if ($cryptoQuery) $cryptoQuery->where('status', $filters['status']);
        }
        if (!empty($filters['from'])) { $ngnQuery->whereDate('created_at', '>=', $filters['from']); if ($cryptoQuery) $cryptoQuery->whereDate('created_at', '>=', $filters['from']); }
        if (!empty($filters['to'])) { $ngnQuery->whereDate('created_at', '<=', $filters['to']); if ($cryptoQuery) $cryptoQuery->whereDate('created_at', '<=', $filters['to']); }
        if (!empty($filters['method'])) $ngnQuery->where('payment_method', $filters['method']);
        if (!empty($filters['asset']) && $cryptoQuery) $cryptoQuery->where('currency', strtoupper($filters['asset']));

        $deposits = $ngnQuery->latest()->paginate(25, ['*'], 'ngn_page')->withQueryString();
        $cryptoDeposits = $cryptoQuery
            ? $cryptoQuery->latest()->paginate(25, ['*'], 'crypto_page')->withQueryString()
            : collect();
        $methods = Deposit::query()->whereNotNull('payment_method')->where('payment_method', '<>', '')->distinct()->orderBy('payment_method')->pluck('payment_method');
        $assets = $cryptoQuery ? CryptoDeposit::query()->whereNotNull('currency')->distinct()->orderBy('currency')->pluck('currency') : collect();
        $depositStats = [
            'pending' => Deposit::where('status', 'pending')->count(),
            'approved' => Deposit::whereIn('status', ['approved', 'successful', 'completed'])->count(),
            'volume' => (float) Deposit::whereIn('status', ['approved', 'successful', 'completed'])->sum('amount'),
            'crypto_open' => Schema::hasTable('crypto_deposits')
                ? CryptoDeposit::whereIn('status', ['pending', 'awaiting_admin_review'])->count()
                : 0,
        ];
        return view('admin.deposits.index', compact('deposits', 'cryptoDeposits', 'depositStats', 'filters', 'methods', 'assets'));
    }

    public function update(Request $request, Deposit $deposit)
    {
        $request->validate([
            'status'     => 'required|in:approved,cancelled',
            'admin_note' => 'nullable|string|max:255',
        ]);

        $adminNote = Schema::hasColumn('deposits', 'admin_note') ? $request->admin_note : null;
        $changed = app(BalanceSettlementService::class)->setDepositStatus(
            (int) $deposit->id,
            $request->status,
            $adminNote
        );

        return redirect()->route('admin.deposits.index')->with(
            $changed ? 'success' : 'info',
            $changed ? 'Deposit status updated and balance settled.' : 'This deposit has already been processed.'
        );
    }

    public function updateCrypto(Request $request, CryptoDeposit $deposit)
    {
        $data = $request->validate([
            'invoice_usd_amount' => ['required', 'numeric', 'min:0.01', 'max:1000000000'],
            'expected_crypto_amount' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'received_amount' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'transaction_hash' => ['nullable', 'string', 'max:180'],
            'payment_address' => ['nullable', 'string', 'max:255'],
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        DB::transaction(function () use ($deposit, $data): void {
            $record = CryptoDeposit::query()->lockForUpdate()->findOrFail($deposit->id);
            $paymentData = $record->payment_data ?? [];
            $provider = strtolower((string) data_get($paymentData, 'provider', ''));
            $settlement = strtolower((string) data_get($paymentData, 'deposit_input.settlement_preference', 'usd')) === 'ngn' ? 'ngn' : 'usd';
            $oldReceived = (float) ($record->received_amount ?? 0);
            $newReceived = array_key_exists('received_amount', $data) ? (float) ($data['received_amount'] ?? 0) : $oldReceived;
            $receivedDelta = round($newReceived - $oldReceived, 8);
            $usdDelta = 0.0;

            // Completed deposits have already credited a wallet. Keep that credit
            // in sync and fail if a correction would take the balance below zero.
            if ($record->status === 'completed' && abs($receivedDelta) >= 0.000000005) {
                $reference = 'crypto-deposit-edit:' . $record->id . ':' . now()->format('YmdHis');
                if ($settlement === 'ngn') {
                    $marketPrice = (float) data_get($paymentData, 'deposit_valuation.market_price_usd', 0);
                    if ($marketPrice <= 0) $marketPrice = (float) data_get($paymentData, 'deposit_input.quoted_market_price_usd', 0);
                    $rate = (float) ($record->ngn_rate_used ?: data_get($paymentData, 'deposit_input.quoted_ngn_per_usd', 0));
                    if ($marketPrice <= 0 || $rate <= 0) {
                        throw \Illuminate\Validation\ValidationException::withMessages(['received_amount' => 'Cannot adjust this completed Naira-settled deposit because its saved conversion rate is missing.']);
                    }
                    $usdDelta = $receivedDelta * $marketPrice;
                    $ngnDelta = round($receivedDelta * $marketPrice * $rate, 2);
                    app(BalanceSettlementService::class)->adjustNgnBalance((int) $record->user_id, $ngnDelta, 'crypto_deposit.admin_correction', $reference);
                    $record->ngn_credited_amount = max(0, round((float) $record->ngn_credited_amount + $ngnDelta, 2));
                } else {
                    $marketPrice = (float) data_get($paymentData, 'deposit_valuation.market_price_usd', 0);
                    if ($marketPrice <= 0) $marketPrice = (float) data_get($paymentData, 'deposit_input.quoted_market_price_usd', 0);
                    $usdDelta = $marketPrice > 0 ? $receivedDelta * $marketPrice : 0.0;
                    $wallet = Wallet::query()->whereKey($record->wallet_id)->lockForUpdate()->firstOrFail();
                    $adjustedBalance = round((float) $wallet->balance + $receivedDelta, 8);
                    if ($adjustedBalance < 0) throw \Illuminate\Validation\ValidationException::withMessages(['received_amount' => 'The user has already used part of this crypto credit. The correction would make their wallet negative.']);
                    $wallet->balance = $adjustedBalance;
                    $wallet->save();
                    app(\App\Services\WalletLedgerService::class)->record($wallet, $receivedDelta, 'crypto_deposit.admin_correction', $reference, [
                        'deposit_id' => $record->id,
                        'admin_id' => auth()->id(),
                        'reason' => trim($data['reason']),
                        'previous_received_amount' => $oldReceived,
                        'updated_received_amount' => $newReceived,
                    ]);
                }
            }

            $paymentData['deposit_input'] = array_merge((array) data_get($paymentData, 'deposit_input', []), [
                'input_usd_amount' => round((float) $data['invoice_usd_amount'], 2),
            ]);
            $expectedCrypto = array_key_exists('expected_crypto_amount', $data) ? $data['expected_crypto_amount'] : null;
            if ($expectedCrypto !== null && (float) $expectedCrypto > 0) {
                $paymentData['expected_crypto_amount'] = round((float) $expectedCrypto, 8);
            } elseif ($expectedCrypto !== null) {
                unset($paymentData['expected_crypto_amount']);
            }
            if ($provider === 'manual' && $expectedCrypto !== null) {
                $record->expected_amount = round((float) $expectedCrypto, 8);
            } else {
                $record->expected_amount = round((float) $data['invoice_usd_amount'], 2);
            }
            $record->received_amount = $newReceived > 0 ? round($newReceived, 8) : null;
            $record->transaction_hash = filled($data['transaction_hash'] ?? null) ? trim($data['transaction_hash']) : null;
            $record->payment_address = filled($data['payment_address'] ?? null) ? trim($data['payment_address']) : null;
            if ($record->status === 'completed' && abs($usdDelta) > 0.000000005) {
                $paymentData['deposit_valuation']['market_value_usd'] = max(0, round((float) data_get($paymentData, 'deposit_valuation.market_value_usd', 0) + $usdDelta, 8));
            }
            $history = (array) data_get($paymentData, 'admin_edits', []);
            $history[] = [
                'admin_id' => auth()->id(),
                'edited_at' => now()->toIso8601String(),
                'reason' => trim($data['reason']),
                'invoice_usd_amount' => round((float) $data['invoice_usd_amount'], 2),
                'expected_crypto_amount' => $expectedCrypto,
                'received_amount' => $record->received_amount,
                'transaction_hash' => $record->transaction_hash,
                'payment_address' => $record->payment_address,
            ];
            $paymentData['admin_edits'] = array_slice($history, -20);
            $record->payment_data = $paymentData;
            $record->save();
        });

        return back()->with('success', 'Crypto deposit details updated. Any correction to a completed balance was applied to the user wallet and ledger.');
    }
}
