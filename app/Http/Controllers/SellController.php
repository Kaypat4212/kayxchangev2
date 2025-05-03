<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellTrade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

class SellController extends Controller
{
    /**
     * Step 1: Show coin and amount input page
     */
    public function showSellForm()
{
    return view('sell.step1'); // Or whatever your first form step view is
}

    public function step1()
    {
        return view('sell.step1');
    }

    /**
     * Handle Step 1 submission and redirect to Step 2
     */
    public function postStep1(Request $request)
    {
        $request->validate([
            'coin' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        Session::put('sell.coin', $request->coin);
        Session::put('sell.amount', $request->amount);

        return redirect()->route('sell.step2');
    }

    /**
     * Step 2: Show wallet address and proof upload
     */
    public function step2()
    {
        if (!Session::has('sell.coin') || !Session::has('sell.amount')) {
            return redirect()->route('sell.step1')->with('error', 'Please complete step 1 first.');
        }

        $coin = Session::get('sell.coin');

        $walletMap = [
            'BTC' => 'bc1qexamplebtcaddress',
            'ETH' => '0xexampleethaddress',
            'USDT' => 'TXexampleusdtaddress',
        ];

        return view('sell.step2', [
            'coin' => $coin,
            'walletAddress' => $walletMap[$coin] ?? 'N/A',
        ]);
    }

    /**
     * Handle Step 2 submission and redirect to Step 3
     */
    public function postStep2(Request $request)
    {
        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $proofPath = $request->file('proof')->store('payment_proofs', 'public');
        Session::put('sell.proof', $proofPath);

        return redirect()->route('sell.step3');
    }

    /**
     * Step 3: Show payout method form
     */
    public function step3()
    {
        if (!Session::has('sell.proof')) {
            return redirect()->route('sell.step2')->with('error', 'Please upload payment proof first.');
        }

        return view('sell.step3');
    }

    /**
     * Final Submission - Create the trade
     */
    public function finalize(Request $request)
    {
        $request->validate([
            'payout_method' => 'required|string',
            'alt_bank' => 'nullable|string',
            'alt_account_number' => 'nullable|string',
            'alt_account_name' => 'nullable|string',
        ]);

        $sellTrade = new SellTrade();
        $sellTrade->user_id = Auth::id();
        $sellTrade->coin = Session::get('sell.coin');
        $sellTrade->amount = Session::get('sell.amount');
        $sellTrade->proof = Session::get('sell.proof');

        $sellTrade->payment_method = $request->payout_method;
        $sellTrade->alt_bank = $request->alt_bank;
        $sellTrade->alt_account_number = $request->alt_account_number;
        $sellTrade->alt_account_name = $request->alt_account_name;
        $sellTrade->status = 'Pending';
        $sellTrade->save();

        // Clear session
        Session::forget('sell');

        return redirect()->route('trade.summary', ['trade_id' => $sellTrade->id])
                         ->with('success', 'Sell trade submitted successfully.');
    }

    /**
     * Summary page
     */
    public function tradeSummary($trade_id)
    {
        $trade = SellTrade::where('id', $trade_id)
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        $usdToNairaRate = 1580; // Replace with dynamic DB lookup if available
        $nairaEquivalent = $trade->amount * $usdToNairaRate;

        $walletMap = [
            'BTC' => 'bc1qexamplebtcaddress',
            'ETH' => '0xexampleethaddress',
            'USDT' => 'TXexampleusdtaddress',
        ];

        return view('trade.summary', compact('trade', 'nairaEquivalent', 'walletMap'));
    }
}
