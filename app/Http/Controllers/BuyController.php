<?php

namespace App\Http\Controllers;

use App\Models\AccountDetail;
use Illuminate\Http\Request;
use App\Models\BuyTrade;

class BuyController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'coin' => 'required|in:BTC,ETH,USDT',
            'amount' => 'required|numeric|min:1',
            'wallet_address' => 'required|string|max:255',
        ]);

        // Rates
        $rates = [
            'BTC' => 1600,
            'ETH' => 1500,
            'USDT' => 1400,
        ];

        $rate = $rates[$request->coin];

        if ($request->input('input_type') == 'naira') {
            // User entered in Naira
            $naira_amount = $request->amount;
            $usd_amount = $request->amount / $rate;
        } else {
            // User entered in USD
            $usd_amount = $request->amount;
            $naira_amount = $request->amount * $rate;
        }

        $buyTrade = BuyTrade::create([
            'user_id' => auth()->id(), // if using auth
            'coin' => $request->coin,
            'usd_amount' => $usd_amount,
            'naira_amount' => $naira_amount,
            'wallet_address' => $request->wallet_address,
            'status' => 'pending',
        ]);

        return redirect()->route('buy.payment', ['id' => $buyTrade->id]);
    }

    public function paymentPage($id)
    {
        // Fetch the trade details
    $trade = BuyTrade::findOrFail($id);
    
    // Fetch account details (adjust this based on how you store account info)
    $accountDetails = AccountDetail::find(1); // Replace with the actual logic to fetch account details

    // Pass both the trade and account details to the view
    return view('buy.payment', compact('trade', 'accountDetails'));
    }

    public function uploadPayment(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $trade = BuyTrade::findOrFail($id);

        if ($request->hasFile('payment_proof')) {
            $imagePath = $request->file('payment_proof')->store('payment_proofs', 'public');
            $trade->payment_proof = $imagePath;
            $trade->save();
        }

        return redirect()->route('buy.success')->with('success', 'Payment proof uploaded successfully.');
    }

    public function success()
    {
        return view('buy.success');
    }

    public function updateStatus(Request $request, $id)
    {
        $trade = BuyTrade::findOrFail($id);
        $trade->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Trade status updated successfully.');
    }
    
}
