<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CryptoRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CryptoRateUpdateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin'])->only('update');
    }

    /**
     * Fetch all crypto rates.
     */
    public function index()
    {
        try {
            $rates = CryptoRate::all()->map(function ($rate) {
                return [
                    'coin' => $rate->coin,
                    'buy_rate' => $rate->buy_rate,
                    'sell_rate' => $rate->sell_rate,
                ];
            });

            Log::info('Fetched crypto rates', ['count' => $rates->count()]);

            return response()->json($rates);
        } catch (\Exception $e) {
            Log::error('Error fetching crypto rates: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch rates'], 500);
        }
    }

    /**
     * Update crypto rates.
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rates' => 'required|array|min:1',
                'rates.*.coin' => 'required|in:BTC,ETH,USDT',
                'rates.*.buy_rate' => 'required|numeric|min:0.01',
                'rates.*.sell_rate' => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed for rates update', ['errors' => $validator->errors()]);
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $rates = $request->input('rates');

            foreach ($rates as $rateData) {
                $rate = CryptoRate::where('coin', $rateData['coin'])->first();
                if ($rate) {
                    $rate->buy_rate = $rateData['buy_rate'];
                    $rate->sell_rate = $rateData['sell_rate'];
                    $rate->save();
                    Log::info('Updated rate for coin: ' . $rateData['coin'], [
                        'buy_rate' => $rateData['buy_rate'],
                        'sell_rate' => $rateData['sell_rate'],
                    ]);
                } else {
                    Log::warning('Coin not found for update: ' . $rateData['coin']);
                    return response()->json(['error' => 'Coin not found: ' . $rateData['coin']], 404);
                }
            }

            return response()->json(['message' => 'Rates updated successfully']);
        } catch (\Exception $e) {
            Log::error('Error updating crypto rates: ' . $e->getMessage(), [
                'payload' => $request->all(),
            ]);
            return response()->json(['error' => 'Unable to update rates: ' . $e->getMessage()], 500);
        }
    }
}