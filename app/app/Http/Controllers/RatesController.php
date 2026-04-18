<?php 

namespace App\Http\Controllers;

use App\Models\Rate;
use Illuminate\Http\Request;

class RatesController extends Controller
{
    // Display the rates page
    public function index()
    {
  
        
        return view('rates.index');
    }

    // Display the rates for cryptocurrencies
    public function cryptoRates()
    {
     try {
            $rates = CryptoRate::all(['coin', 'buy_rate', 'sell_rate']);
            Log::info('Fetched crypto rates: ' . json_encode($rates));
            return view('rates.crypto', compact('rates'));
        } catch (\Exception $e) {
            Log::error('Error fetching crypto rates: ' . $e->getMessage());
            return view('rates.crypto', ['rates' => [], 'error' => 'Unable to fetch rates']);
        }
    }

    // Display the rates for gift cards
    public function giftCardRates()
    {
        return view('rates.giftcard');
    }

    // Show the edit form for a specific rate
    public function editRate($id)
    {
        $rate = Rate::findOrFail($id);
        return view('rates.edit-rate', compact('rate'));
    }

    // Update the rate in the database
    public function updateRate(Request $request, $id)
    {
        $rate = Rate::findOrFail($id);

        // Validate and update rates
        $request->validate([
            'buy_rate' => 'required|numeric',
            'sell_rate' => 'required|numeric',
        ]);

        $rate->update([
            'buy_rate' => $request->buy_rate,
            'sell_rate' => $request->sell_rate,
        ]);

        return redirect()->route('rates.crypto')->with('success', 'Rates updated successfully.');
    }
}
