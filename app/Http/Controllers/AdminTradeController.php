<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellTrade;
use App\Models\User;
use App\Services\AdminTradeAlertService;
use App\Mail\TradeNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminTradeController extends Controller
{
    /**
     * Update the status of a sell trade.
     *
     * @param Request $request
     * @param int $trade_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $trade_id)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,rejected',
        ]);

        $trade = SellTrade::findOrFail($trade_id);
        $originalStatus = $trade->status;
        $tradeUser = User::find($trade->user_id);

        // Handle admin payment proof upload
        if ($request->hasFile('admin_payment_proof')) {
            if ($trade->admin_payment_proof) {
                Storage::disk('public')->delete($trade->admin_payment_proof);
            }
            $trade->admin_payment_proof = $request->file('admin_payment_proof')->store('admin-sell-proofs', 'public');
        }

        $trade->status = $request->status;

        Log::info('Attempting to update trade status', [
            'trade_id' => $trade->id,
            'old_status' => $originalStatus,
            'new_status' => $trade->status,
        ]);

        try {
            $trade->save();
            Log::info('Trade status updated successfully', ['trade_id' => $trade->id]);

            if ($trade->payment_method === 'wallet_balance' && $trade->status === 'completed' && $originalStatus !== 'completed') {
                $user = User::findOrFail($trade->user_id);
                $oldBalance = $user->balance ?? 0;
                $user->balance = $oldBalance + $trade->naira_amount;

                Log::info('Attempting to update user balance for completed trade', [
                    'user_id' => $user->id,
                    'trade_id' => $trade->id,
                    'old_balance' => $oldBalance,
                    'new_balance' => $user->balance,
                    'naira_amount' => $trade->naira_amount,
                ]);

                try {
                    $user->save();
                    Log::info('User balance updated successfully', [
                        'user_id' => $user->id,
                        'new_balance' => $user->balance,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update user balance', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw new \Exception('Failed to update user balance: ' . $e->getMessage());
                }
            }

            // Send email notification to user
            try {
                if ($tradeUser && in_array($trade->status, ['completed', 'rejected'])) {
                    $isCompleted = $trade->status === 'completed';
                    $paymentMethod = $trade->payment_method === 'wallet_balance'
                        ? 'Wallet Balance'
                        : ($trade->bank_name ? $trade->bank_name . ' (' . $trade->account_number . ')' : 'Bank Transfer');
                    $adminProofUrl = $trade->admin_payment_proof
                        ? asset('storage/' . $trade->admin_payment_proof)
                        : null;
                    
                    // Calculate crypto equivalent
                    $usdVal = $trade->usd_amount ?? $trade->amount;
                    $cryptoAmount = $usdVal / ($trade->rate_used ?: 1);
                    
                    Mail::to($tradeUser->email)->send(new TradeNotification(
                        user: $tradeUser,
                        templateKey: $isCompleted ? 'sell_trade_completed' : 'sell_trade_rejected',
                        data: [
                            'usd_amount'     => number_format((float)$usdVal, 2),
                            'crypto_amount'  => number_format($cryptoAmount, 8),
                            'currency'       => $trade->coin,
                            'rate_used'      => number_format((float)$trade->rate_used, 2),
                            'naira_amount'   => number_format((float)$trade->naira_amount, 2),
                            'reference'      => $trade->transaction_ref ?? ('SELL-' . $trade->id),
                            'payment_method' => $paymentMethod,
                            'reason'         => request('rejection_reason', 'Your trade did not meet our requirements.'),
                            'proof_url'      => $adminProofUrl,
                        ],
                        badge: [
                            'text'  => $isCompleted ? 'Payment Sent' : 'Order Rejected',
                            'color' => $isCompleted ? '#00cc00' : '#dc3545',
                        ],
                        ctaUrl: $adminProofUrl ?? url('/dashboard'),
                        ctaText: $adminProofUrl ? 'View Payment Proof' : 'Go to Dashboard',
                    ));
                }
            } catch (\Exception $mailEx) {
                Log::warning('Sell trade status email failed: ' . $mailEx->getMessage());
            }

            // Telegram notification on completion
            if (isset($isCompleted) && $isCompleted && $tradeUser && $tradeUser->telegram_chat_id) {
                try {
                    $proofLine = isset($adminProofUrl) && $adminProofUrl
                        ? "\n\n🧾 [View Payment Proof]({$adminProofUrl})"
                        : '';
                    app(\App\Services\TelegramService::class)->sendMessage(
                        (int)$tradeUser->telegram_chat_id,
                        "✅ *Your Sell Trade is Completed*\n\n" .
                        "🔖 Ref: `{$trade->transaction_ref}`\n" .
                        "🪙 Coin: {$trade->coin}\n" .
                        "💴 Naira Paid: ₦" . number_format((float)($trade->naira_amount ?? 0), 2) .
                        $proofLine,
                        'Markdown'
                    );
                } catch (\Throwable $tgEx) {
                    Log::warning('Sell complete Telegram notify failed: ' . $tgEx->getMessage());
                }
            }

            if ($originalStatus !== $trade->status) {
                try {
                    app(AdminTradeAlertService::class)->sendStatusChangeAlert('sell', [
                        'reference' => $trade->transaction_ref ?? ('SELL-' . $trade->id),
                        'user_name' => $tradeUser->name ?? ($trade->name ?? 'N/A'),
                        'old_status' => $originalStatus,
                        'new_status' => $trade->status,
                    ]);
                } catch (\Throwable $alertEx) {
                    Log::warning('AdminTradeController status alert failed: ' . $alertEx->getMessage());
                }
            }

            return redirect()->back()->with('success', 'Trade status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update trade status', [
                'trade_id' => $trade->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['status' => 'Failed to update trade status: ' . $e->getMessage()]);
        }
    }
}