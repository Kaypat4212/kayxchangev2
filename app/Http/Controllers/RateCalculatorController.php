<?php

namespace App\Http\Controllers;

use App\Models\CryptoRate;
use Illuminate\Http\Request;

class RateCalculatorController extends Controller
{
    /**
     * Show the rate calculator page.
     */
    public function index()
    {
        $rates = CryptoRate::select('coin', 'buy_rate', 'sell_rate')->get()->keyBy('coin');
        return view('rates.calculator', compact('rates'));
    }

    /**
     * AJAX: return the current rates as JSON (for live refresh).
     */
    public function apiRates()
    {
        $rates = CryptoRate::select('coin', 'buy_rate', 'sell_rate')->get()->keyBy('coin');
        return response()->json($rates);
    }
}
