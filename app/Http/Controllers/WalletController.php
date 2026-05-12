<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\CryptoDeposit;
use App\Services\CryptoWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Get recent transactions (deposits, withdrawals, transfers)
        $recentDeposits = CryptoDeposit::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('wallet.index', compact('wallets', 'recentDeposits'));
    }

    /**
     * Show crypto deposit page
     */
    public function deposit()
    {
        $user = Auth::user();
        $wallets = $this->cryptoWalletService->getUserWallets($user);
        $supportedCryptos = $this->cryptoWalletService->getSupportedCryptos();

        return view('wallet.deposit', compact('wallets', 'supportedCryptos'));
    }

    /**
     * Create a crypto deposit invoice
     */
    public function createDeposit(Request $request)
    {
        $request->validate([
            'currency' => 'required|string|in:' . implode(',', array_keys($this->cryptoWalletService->getSupportedCryptos())),
            'amount' => 'required|numeric|min:0.00000001',
        ]);

        try {
            $user = Auth::user();
            $result = $this->cryptoWalletService->createCryptoDeposit(
                $user,
                $request->currency,
                $request->amount
            );

            if ($result['payment_url']) {
                return redirect($result['payment_url']);
            }

            return redirect()->route('wallet.deposit.success', $result['deposit']->id);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create deposit: ' . $e->getMessage());
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

        return view('wallet.deposit-success', compact('deposit'));
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

    /**
     * Show withdrawal page
     */
    public function withdraw()
    {
        $user = Auth::user();
        $wallets = $this->cryptoWalletService->getUserWallets($user);

        return view('wallet.withdraw', compact('wallets'));
    }

    /**
     * Create a withdrawal request
     */
    public function createWithdrawal(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0.00000001',
            'address' => 'required|string',
            'network' => 'required|string',
        ]);

        $user = Auth::user();
        $wallet = $user->wallets()->findOrFail($request->wallet_id);

        // Check balance
        if ($wallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance');
        }

        // For now, just create a pending withdrawal
        // In production, integrate with crypto withdrawal services
        $withdrawal = \App\Models\CryptoWithdrawal::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'address' => $request->address,
            'network' => $request->network,
            'status' => 'pending',
        ]);

        return redirect()->route('wallet.history')->with('success', 'Withdrawal request submitted');
    }

    /**
     * Show transaction history
     */
    public function history()
    {
        $user = Auth::user();

        $deposits = CryptoDeposit::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        $withdrawals = \App\Models\CryptoWithdrawal::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('wallet.history', compact('deposits', 'withdrawals'));
    }
}
