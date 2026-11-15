<?php
namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use App\Services\BalanceSettlementService;
use App\Models\CryptoDeposit;

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
}
