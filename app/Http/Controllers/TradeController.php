<?php

namespace App\Http\Controllers;

use App\Models\BuyTrade;
use App\Models\SellTrade;
use App\Mail\TradeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TradeController extends Controller
{
    /**
     * User cancels a pending buy trade (allowed only after 30 minutes).
     */
    public function cancelBuy(Request $request, $id)
    {
        $trade = BuyTrade::findOrFail($id);

        if ($trade->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        if ($trade->status !== 'pending') {
            return back()->with('error', 'Only pending trades can be cancelled.');
        }

        $minutesElapsed = $trade->created_at->diffInMinutes(now());
        if ($minutesElapsed < 30) {
            $remaining = 30 - $minutesElapsed;
            return back()->with('error', "You can cancel this trade in {$remaining} minute(s). Cancellation is allowed 30 minutes after submission.");
        }

        $trade->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id(),
        ]);

        // Notify user by email
        try {
            Mail::to(Auth::user()->email)->send(new TradeNotification(
                user: Auth::user(),
                templateKey: 'buy_trade_rejected',
                data: [
                    'amount'    => number_format((float) $trade->usd_amount, 6),
                    'currency'  => $trade->coin,
                    'naira_amount' => number_format((float) $trade->naira_amount, 2),
                    'reference' => $trade->transaction_ref ?? ('BUY-' . $trade->id),
                    'reason'    => 'You cancelled this trade.',
                ],
                badge: ['text' => 'Trade Cancelled', 'color' => '#6b7280'],
                ctaUrl: url('/dashboard'),
                ctaText: 'Back to Dashboard',
            ));
        } catch (\Exception $e) {
            Log::warning('Cancel buy trade email failed: ' . $e->getMessage());
        }

        Log::info('Buy trade cancelled by user', ['trade_id' => $trade->id, 'user_id' => Auth::id()]);

        return redirect()->route('dashboard')->with('success', 'Buy trade cancelled successfully.');
    }

    /**
     * User cancels a pending sell trade (allowed only after 30 minutes).
     */
    public function cancelSell(Request $request, $id)
    {
        $trade = SellTrade::findOrFail($id);

        if ($trade->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        if ($trade->status !== 'pending') {
            return back()->with('error', 'Only pending trades can be cancelled.');
        }

        $minutesElapsed = $trade->created_at->diffInMinutes(now());
        if ($minutesElapsed < 30) {
            $remaining = 30 - $minutesElapsed;
            return back()->with('error', "You can cancel this trade in {$remaining} minute(s). Cancellation is allowed 30 minutes after submission.");
        }

        $trade->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id(),
        ]);

        // Notify user by email
        try {
            Mail::to(Auth::user()->email)->send(new TradeNotification(
                user: Auth::user(),
                templateKey: 'sell_trade_rejected',
                data: [
                    'amount'    => number_format((float) ($trade->usd_amount ?? $trade->amount), 6),
                    'currency'  => $trade->coin,
                    'naira_amount' => number_format((float) $trade->naira_amount, 2),
                    'reference' => $trade->transaction_ref ?? ('SELL-' . $trade->id),
                    'reason'    => 'You cancelled this trade.',
                    'payment_method' => $trade->bank_name ?? 'N/A',
                ],
                badge: ['text' => 'Trade Cancelled', 'color' => '#6b7280'],
                ctaUrl: url('/dashboard'),
                ctaText: 'Back to Dashboard',
            ));
        } catch (\Exception $e) {
            Log::warning('Cancel sell trade email failed: ' . $e->getMessage());
        }

        Log::info('Sell trade cancelled by user', ['trade_id' => $trade->id, 'user_id' => Auth::id()]);

        return redirect()->route('dashboard')->with('success', 'Sell trade cancelled successfully.');
    }

    /**
     * Admin cancels any pending buy trade immediately.
     */
    public function adminCancelBuy(Request $request, $id)
    {
        $trade = BuyTrade::findOrFail($id);

        if ($trade->status !== 'pending') {
            return back()->with('error', 'Only pending trades can be cancelled.');
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        $trade->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id(),
        ]);

        // Notify user
        try {
            $tradeUser = $trade->user;
            if ($tradeUser) {
                Mail::to($tradeUser->email)->send(new TradeNotification(
                    user: $tradeUser,
                    templateKey: 'buy_trade_rejected',
                    data: [
                        'amount'    => number_format((float) $trade->usd_amount, 6),
                        'currency'  => $trade->coin,
                        'naira_amount' => number_format((float) $trade->naira_amount, 2),
                        'reference' => $trade->transaction_ref ?? ('BUY-' . $trade->id),
                        'reason'    => $request->input('reason', 'This trade was cancelled by an administrator.'),
                    ],
                    badge: ['text' => 'Trade Cancelled', 'color' => '#6b7280'],
                    ctaUrl: url('/dashboard'),
                    ctaText: 'Back to Dashboard',
                ));
            }
        } catch (\Exception $e) {
            Log::warning('Admin cancel buy trade email failed: ' . $e->getMessage());
        }

        Log::info('Buy trade cancelled by admin', ['trade_id' => $trade->id, 'admin_id' => Auth::id()]);

        return back()->with('success', 'Buy trade #' . $trade->id . ' cancelled.');
    }

    /**
     * Admin cancels any pending sell trade immediately.
     */
    public function adminCancelSell(Request $request, $id)
    {
        $trade = SellTrade::findOrFail($id);

        if ($trade->status !== 'pending') {
            return back()->with('error', 'Only pending trades can be cancelled.');
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        $trade->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id(),
        ]);

        // Notify user
        try {
            $tradeUser = $trade->user;
            if ($tradeUser) {
                Mail::to($tradeUser->email)->send(new TradeNotification(
                    user: $tradeUser,
                    templateKey: 'sell_trade_rejected',
                    data: [
                        'amount'    => number_format((float) ($trade->usd_amount ?? $trade->amount), 6),
                        'currency'  => $trade->coin,
                        'naira_amount' => number_format((float) $trade->naira_amount, 2),
                        'reference' => $trade->transaction_ref ?? ('SELL-' . $trade->id),
                        'reason'    => $request->input('reason', 'This trade was cancelled by an administrator.'),
                        'payment_method' => $trade->bank_name ?? 'N/A',
                    ],
                    badge: ['text' => 'Trade Cancelled', 'color' => '#6b7280'],
                    ctaUrl: url('/dashboard'),
                    ctaText: 'Back to Dashboard',
                ));
            }
        } catch (\Exception $e) {
            Log::warning('Admin cancel sell trade email failed: ' . $e->getMessage());
        }

        Log::info('Sell trade cancelled by admin', ['trade_id' => $trade->id, 'admin_id' => Auth::id()]);

        return back()->with('success', 'Sell trade #' . $trade->id . ' cancelled.');
    }
}
