<?php

namespace App\Http\Controllers;

use App\Models\CryptoRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CryptoRateController extends Controller
{
    /**
     * Display a listing of the crypto rates.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function index()
    // {
    //     $rates = CryptoRate::all(['coin', 'buy_rate', 'sell_rate']);
    //     return response()->json($rates);
    // }

    /**
     * Update crypto rates.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function index()
    {
        // Example rates (replace with your actual logic, e.g., fetching from a database or API)
      $rates = CryptoRate::all();
        return view('rates.crypto', compact('rates'));
    }
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rates' => 'required|array',
                'rates.*.coin' => 'required|string',
                'rates.*.buy_rate' => 'required|numeric|min:0',
                'rates.*.sell_rate' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            foreach ($request->rates as $rate) {
                CryptoRate::updateOrCreate(
                    ['coin' => $rate['coin']],
                    [
                        'buy_rate' => $rate['buy_rate'],
                        'sell_rate' => $rate['sell_rate'],
                    ]
                );
            }

            return response()->json(['message' => 'Rates updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Error updating rates: ' . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Server error occurred: ' . $e->getMessage()], 500);
        }
    }
}
