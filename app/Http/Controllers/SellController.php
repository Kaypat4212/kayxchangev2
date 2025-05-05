<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellTrade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Rate;

class SellController extends Controller
{
    public function step1()
    {
        $rates = Rate::pluck('sell_rate', 'coin')->toArray();
        return view('sell.step1', compact('rates'));
    }

    public function postStep1(Request $request)
    {
        $request->validate([
            'coin' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        $coin = $request->coin;
        $usdAmount = $request->amount;

        // Get the rate from DB
        $rate = Rate::where('coin', $coin)->value('sell_rate') ?? 0;

        // Calculate Naira amount
        $nairaAmount = $usdAmount * $rate;

        // Store values in session
        Session::put('sell.coin', $coin);
        Session::put('sell.amount', $nairaAmount);    // Naira amount
        Session::put('sell.usd_amount', $usdAmount);  // USD amount

        return redirect()->route('sell.step2');
    }

    public function step2()
    {
        if (!Session::has('sell.coin') || !Session::has('sell.amount')) {
            return redirect()->route('sell.step1')->with('error', 'Please complete step 1 first.');
        }

        $coin = Session::get('sell.coin');
        $amountInUsd = Session::get('sell.usd_amount');
        $nairaAmount = Session::get('sell.amount');

        // Get wallet address based on coin
        $walletMap = [
            'BTC' => 'bc1qexamplebtcaddress',
            'ETH' => '0xexampleethaddress',
            'USDT' => 'TXexampleusdtaddress',
        ];

        $walletAddress = $walletMap[$coin] ?? 'N/A';

        return view('sell.step2', compact('coin', 'amountInUsd', 'nairaAmount', 'walletAddress'));
    }

    public function postStep2(Request $request)
    {
        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Store proof in the public disk
        $proofPath = $request->file('proof')->store('payment_proofs', 'public');
        Session::put('sell.proof', $proofPath);

        return redirect()->route('sell.step3');
    }

    public function step3()
    {
        if (!Session::has('sell.proof')) {
            return redirect()->route('sell.step2')->with('error', 'Please upload payment proof first.');
        }

        $user = Auth::user(); // Get the authenticated user
        $userData = [
            'bank_name' => $user->bank_name,
            'account_number' => $user->account_number,
            'account_name' => $user->account_name,
            'wallet_balance' => $user->wallet_balance, // Assuming you have this attribute
        ];

        return view('sell.step3', compact('userData'));
    }

    public function finalize(Request $request)
    {
        $request->validate([
            'payout_method' => 'required|string|in:default_bank,external_bank,wallet_balance',
            'alt_bank' => 'nullable|string',
            'alt_account_number' => 'nullable|string',
            'alt_account_name' => 'nullable|string',
        ]);

        $user = Auth::user();

        $sellTrade = new SellTrade();
        $sellTrade->user_id = $user->id;
        $sellTrade->coin = Session::get('sell.coin');
        $sellTrade->amount = Session::get('sell.amount');    // Naira amount
        $sellTrade->usd_amount = Session::get('sell.usd_amount');  // USD amount
        $sellTrade->naira_amount = Session::get('sell.amount');    // Store Naira amount
        $sellTrade->proof = Session::get('sell.proof');
        $sellTrade->payment_method = $request->payout_method;
        $sellTrade->status = 'Pending';
        $sellTrade->name = $user->name ?? 'Unknown';

        // Map wallet address based on the coin
        $walletMap = [
            'BTC' => 'bc1qexamplebtcaddress', // Default BTC address, you can change this dynamically as needed
            'ETH' => '0xexampleethaddress',   // Default ETH address
            'USDT' => 'TXexampleusdtaddress', // Default USDT address
        ];

        // Retrieve wallet address for the selected coin
        $coin = Session::get('sell.coin');
        $walletAddress = $walletMap[$coin] ?? 'N/A'; // Set default address if coin is unknown

        // Set the wallet_address dynamically
        $sellTrade->wallet_address = $walletAddress;

        // Handling payout method
        if ($request->payout_method === 'default_bank') {
            $sellTrade->bank_name = $user->bank_name;
            $sellTrade->account_number = $user->account_number;
            $sellTrade->account_name = $user->account_name;
        } elseif ($request->payout_method === 'external_bank') {
            $sellTrade->bank_name = $request->alt_bank;
            $sellTrade->account_number = $request->alt_account_number;
            $sellTrade->account_name = $request->alt_account_name;
        } elseif ($request->payout_method === 'wallet_balance') {
            $sellTrade->bank_name = 'WALLET BALANCE';
            $sellTrade->account_number = 'N/A';
            $sellTrade->account_name = $user->name;
        }

        // Save the trade
        $sellTrade->save();

        // Clear the session data
        Session::forget('sell');

        // Redirect to the trade summary
        return redirect()->route('trade.summary', ['trade_id' => $sellTrade->id])
                         ->with('success', 'Sell trade submitted successfully.');
    }

    public function tradeSummary($trade_id)
{
    // Retrieve trade information
    $trade = SellTrade::where('id', $trade_id)
                      ->where('user_id', Auth::id())
                      ->firstOrFail();

    // Get the sell rate from the database
    $rate = Rate::where('coin', $trade->coin)->value('sell_rate') ?? 0;
    $nairaEquivalent = $trade->usd_amount * $rate;

    // Pass data to view
    return view('trade.summary', compact('trade', 'nairaEquivalent'));
}

}
