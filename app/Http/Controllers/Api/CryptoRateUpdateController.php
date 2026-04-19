<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CryptoRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CryptoRateUpdateController extends Controller
{
    // No sanctum middleware — admin uses web session (cookie-based auth)

    /**
     * Fetch all crypto rates.
     */
    public function index()
    {
        try {
            // Guard: if table doesn't exist yet, return empty list gracefully
            if (!\Illuminate\Support\Facades\Schema::hasTable('crypto_rates')) {
                Log::warning('crypto_rates table does not exist — run php artisan migrate');
                return response()->json([]);
            }

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
            return response()->json([]);
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
                'rates.*.coin' => 'required|string|max:10',
                'rates.*.buy_rate' => 'required|numeric|min:0',
                'rates.*.sell_rate' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed for rates update', ['errors' => $validator->errors()]);
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $rates = $request->input('rates');

            foreach ($rates as $rateData) {
                CryptoRate::updateOrCreate(
                    ['coin' => strtoupper($rateData['coin'])],
                    [
                        'buy_rate'  => $rateData['buy_rate'],
                        'sell_rate' => $rateData['sell_rate'],
                    ]
                );
                Log::info('Updated rate for coin: ' . $rateData['coin'], [
                    'buy_rate'  => $rateData['buy_rate'],
                    'sell_rate' => $rateData['sell_rate'],
                ]);
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