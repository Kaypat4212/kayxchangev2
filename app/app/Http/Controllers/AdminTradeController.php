<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellTrade;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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