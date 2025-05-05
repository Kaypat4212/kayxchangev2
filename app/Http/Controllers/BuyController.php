<?php

namespace App\Http\Controllers;

use App\Models\AccountDetail;
use Illuminate\Http\Request;
use App\Models\BuyTrade;

class BuyController extends Controller
{
    public function submit(Request $request)
    {
        // Define valid networks for each coin
        $validNetworks = [
            'BTC' => ['Bitcoin', 'Lightning'],
            'ETH' => ['ERC20'],
            'USDT' => ['ERC20', 'BEP20', 'TRC20'],
        ];

        // Validate the form inputs
        $request->validate([
            'coin' => 'required|in:BTC,ETH,USDT',
            'amount' => 'required|numeric|min:1',
            'wallet_address' => 'required|string|max:255',
            'network' => ['required', 'string', function ($attribute, $value, $fail) use ($request, $validNetworks) {
                $coin = $request->input('coin');
                if (!in_array($value, $validNetworks[$coin] ?? [])) {
                    $fail('The selected network is not compatible with the chosen coin.');
                }
            }],
        ]);

        // Coin rates
        $rates = [
            'BTC' => 1600,
            'ETH' => 1500,
            'USDT' => 1400,
        ];

        // Get rate based on the selected coin
        $rate = $rates[$request->coin];

        // Calculate USD and Naira amounts based on input type
        if ($request->input('input_type') == 'naira') {
            $naira_amount = $request->amount;
            $usd_amount = $request->amount / $rate;
        } else {
            $usd_amount = $request->amount;
            $naira_amount = $request->amount * $rate;
        }

        // Create a new BuyTrade record
        $buyTrade = BuyTrade::create([
            'user_id' => auth()->id(),
            'coin' => $request->coin,
            'usd_amount' => $usd_amount,
            'naira_amount' => $naira_amount,
            'wallet_address' => $request->wallet_address,
            'network' => $request->network,
            'status' => 'pending',
        ]);

        // Redirect to the trade summary page
        return redirect()->route('buy.summary', ['id' => $buyTrade->id])
            ->with('success', 'Please review your trade details.');
    }

    public function summary($id)
    {
        // Fetch trade details
        $trade = BuyTrade::findOrFail($id);

        // Ensure the user owns the trade
        if ($trade->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        // Render the trade summary page
        return view('buy.trade_summary', compact('trade'));
    }

    public function paymentPage($id)
    {
        // Fetch trade details
        $trade = BuyTrade::findOrFail($id);

        // Ensure the user owns the trade
        if ($trade->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        // Fetch account details
        $accountDetails = AccountDetail::first();

        // Pass trade and account details to the view
        return view('buy.payment', compact('trade', 'accountDetails'));
    }

    public function uploadPayment(Request $request, $id)
    {
        // Validate payment proof file
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $trade = BuyTrade::findOrFail($id);

        // Ensure the user owns the trade
        if ($trade->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        // Save payment proof file
        if ($request->hasFile('payment_proof')) {
            $imagePath = $request->file('payment_proof')->store('payment_proofs', 'public');
            $trade->payment_proof = $imagePath;
            $trade->save();
        }

        // Flash success message and redirect
        return redirect()->route('buy.success', ['id' => $trade->id])
    ->with('success', 'Payment proof uploaded successfully!');
    }

    public function success($id = null)
    {
        $trade = $id ? BuyTrade::findOrFail($id) : null;
        if ($trade && $trade->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        return view('buy.success', compact('trade'));
    }

    public function updateStatus(Request $request, $id)
    {
        $trade = BuyTrade::findOrFail($id);

        // Update trade status
        $trade->update([
            'status' => $request->status
        ]);

        // Flash success message and return
        return back()->with('success', 'Trade status updated successfully.');
    }
}
