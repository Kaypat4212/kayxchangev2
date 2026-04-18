<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CryptoRate;
use App\Services\CoinGeckoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TelegramSettingsController;

class AdminCryptoRateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
             return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to import crypto rates', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'Failed to import crypto rates. Please check the file format and try again.');
        }
    }

    /**
     * Get live cryptocurrency rates for marquee display
     */
    public function getLiveRates()
    {
        try {
            $coinGeckoService = new CoinGeckoService();
            $liveRates = $coinGeckoService->getCryptoPrices();
            
            return response()->json([
                'success' => true,
                'data' => $liveRates,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch live crypto rates', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch live rates',
                'data' => []
            ], 500);
        }
    }

    /**
     * Display the crypto rates management page
     */
    public function index()
    {
        $coinGeckoService = new CoinGeckoService();
        
        $rates = CryptoRate::orderBy('coin')->get();
        $liveRates = $coinGeckoService->getCryptoPrices();
        
        return view('admin.crypto-rates', compact('rates', 'liveRates'));
    }

    /**
     * Add a new cryptocurrency
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coin' => 'required|string|max:10|unique:crypto_rates,coin',
            'buy_rate' => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Failed to add cryptocurrency. Please check your input.');
        }

        try {
            $rate = CryptoRate::create([
                'coin' => strtoupper($request->coin),
                'buy_rate' => $request->buy_rate,
                'sell_rate' => $request->sell_rate,
            ]);

            Log::info('New cryptocurrency added by admin', [
                'admin_id' => auth()->id(),
                'coin' => $rate->coin,
                'buy_rate' => $rate->buy_rate,
                'sell_rate' => $rate->sell_rate,
            ]);

            return redirect()->back()->with('success', "Successfully added {$rate->coin} to the system!");
        } catch (\Exception $e) {
            Log::error('Failed to add cryptocurrency', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'coin' => $request->coin,
            ]);

            return redirect()->back()->with('error', 'Failed to add cryptocurrency. Please try again.');
        }
    }

    /**
     * Update individual cryptocurrency rate
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'buy_rate' => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Invalid rate values provided.');
        }

        try {
            $rate = CryptoRate::findOrFail($id);
            $oldBuyRate = $rate->buy_rate;
            $oldSellRate = $rate->sell_rate;

            $rate->update([
                'buy_rate' => $request->buy_rate,
                'sell_rate' => $request->sell_rate,
            ]);

            Log::info('Cryptocurrency rate updated by admin', [
                'admin_id' => auth()->id(),
                'coin' => $rate->coin,
                'old_buy_rate' => $oldBuyRate,
                'new_buy_rate' => $rate->buy_rate,
                'old_sell_rate' => $oldSellRate,
                'new_sell_rate' => $rate->sell_rate,
            ]);

            return redirect()->back()->with('success', "Successfully updated {$rate->coin} rates!");
        } catch (\Exception $e) {
            Log::error('Failed to update cryptocurrency rate', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'rate_id' => $id,
            ]);

            return redirect()->back()->with('error', 'Failed to update cryptocurrency rate. Please try again.');
        }
    }

    /**
     * Bulk update multiple cryptocurrency rates
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'selected_rates' => 'required|array|min:1',
            'selected_rates.*' => 'exists:crypto_rates,id',
            'rates' => 'required|array',
            'rates.*.buy_rate' => 'required|numeric|min:0',
            'rates.*.sell_rate' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Invalid data provided for bulk update.');
        }

        try {
            $updatedCount = 0;
            $selectedRates = $request->selected_rates;
            $ratesData = $request->rates;

            foreach ($selectedRates as $rateId) {
                if (isset($ratesData[$rateId])) {
                    $rate = CryptoRate::find($rateId);
                    if ($rate) {
                        $oldBuyRate = $rate->buy_rate;
                        $oldSellRate = $rate->sell_rate;

                        $rate->update([
                            'buy_rate' => $ratesData[$rateId]['buy_rate'],
                            'sell_rate' => $ratesData[$rateId]['sell_rate'],
                        ]);

                        Log::info('Bulk cryptocurrency rate update by admin', [
                            'admin_id' => auth()->id(),
                            'coin' => $rate->coin,
                            'old_buy_rate' => $oldBuyRate,
                            'new_buy_rate' => $rate->buy_rate,
                            'old_sell_rate' => $oldSellRate,
                            'new_sell_rate' => $rate->sell_rate,
                        ]);

                        $updatedCount++;
                    }
                }
            }

            // Notify users about rate changes if significant
            $this->notifyRateChanges($selectedRates);

            return redirect()->back()->with('success', "Successfully updated {$updatedCount} cryptocurrency rates!");
        } catch (\Exception $e) {
            Log::error('Failed to bulk update cryptocurrency rates', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'selected_rates' => $request->selected_rates ?? [],
            ]);

            return redirect()->back()->with('error', 'Failed to update cryptocurrency rates. Please try again.');
        }
    }

    /**
     * Delete a cryptocurrency
     */
    public function delete($id)
    {
        try {
            $rate = CryptoRate::findOrFail($id);
            $coin = $rate->coin;

            $rate->delete();

            Log::info('Cryptocurrency deleted by admin', [
                'admin_id' => auth()->id(),
                'coin' => $coin,
                'rate_id' => $id,
            ]);

            return redirect()->back()->with('success', "Successfully removed {$coin} from the system!");
        } catch (\Exception $e) {
            Log::error('Failed to delete cryptocurrency', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'rate_id' => $id,
            ]);

            return redirect()->back()->with('error', 'Failed to remove cryptocurrency. Please try again.');
        }
    }

    /**
     * Get current market prices from external API
     */
    public function getCurrentPrices(Request $request)
    {
        try {
            $coins = $request->get('coins', []);
            
            if (empty($coins)) {
                $coins = CryptoRate::pluck('coin')->toArray();
            }

            // This is a placeholder for actual API integration (CoinGecko, CoinMarketCap, etc.)
            $prices = [];
            foreach ($coins as $coin) {
                // Mock price data - replace with real API call
                $prices[strtolower($coin)] = [
                    'usd' => rand(1000, 50000) + (rand(0, 99) / 100),
                    'change_24h' => rand(-1000, 1000) / 100,
                ];
            }

            return response()->json([
                'success' => true,
                'prices' => $prices,
                'updated_at' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch current crypto prices', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch current prices',
            ], 500);
        }
    }

    /**
     * Auto-update rates based on market prices
     */
    public function autoUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'margin_percentage' => 'required|numeric|min:0|max:50',
            'selected_coins' => 'array',
            'selected_coins.*' => 'exists:crypto_rates,coin',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Invalid parameters for auto-update.');
        }

        try {
            $marginPercentage = $request->margin_percentage;
            $selectedCoins = $request->selected_coins ?? CryptoRate::pluck('coin')->toArray();
            
            $updatedCount = 0;
            $usdToNaira = $request->get('usd_to_naira_rate', 1600); // Default rate

            foreach ($selectedCoins as $coin) {
                $rate = CryptoRate::where('coin', $coin)->first();
                if ($rate) {
                    // Mock market price - replace with real API
                    $marketPrice = rand(1000, 50000) + (rand(0, 99) / 100);
                    
                    $buyRate = ($marketPrice * $usdToNaira) * (1 + $marginPercentage / 100);
                    $sellRate = ($marketPrice * $usdToNaira) * (1 - $marginPercentage / 100);

                    $rate->update([
                        'buy_rate' => round($buyRate, 2),
                        'sell_rate' => round($sellRate, 2),
                    ]);

                    $updatedCount++;
                }
            }

            Log::info('Auto-update cryptocurrency rates completed by admin', [
                'admin_id' => auth()->id(),
                'margin_percentage' => $marginPercentage,
                'updated_count' => $updatedCount,
                'usd_to_naira_rate' => $usdToNaira,
            ]);

            return redirect()->back()->with('success', "Auto-updated {$updatedCount} cryptocurrency rates with {$marginPercentage}% margin!");
        } catch (\Exception $e) {
            Log::error('Failed to auto-update cryptocurrency rates', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'Failed to auto-update rates. Please try again.');
        }
    }

    /**
     * Notify users about significant rate changes
     */
    private function notifyRateChanges($rateIds)
    {
        try {
            // Get users who have telegram notifications enabled
            $users = \App\Models\User::where('telegram_notifications', true)
                ->whereNotNull('telegram_username')
                ->get();

            $updatedCoins = CryptoRate::whereIn('id', $rateIds)->pluck('coin')->toArray();
            
            if ($users->count() > 0 && !empty($updatedCoins)) {
                $message = "📈 *Rate Update Alert - KayXchange*\n\n" .
                          "The following cryptocurrency rates have been updated:\n\n" .
                          "🪙 **Updated Coins:**\n" .
                          "• " . implode("\n• ", $updatedCoins) . "\n\n" .
                          "Visit the platform to see the latest rates!\n\n" .
                          "_Updated at: " . now()->format('Y-m-d H:i:s') . "_";

                foreach ($users as $user) {
                    TelegramSettingsController::notifyRateUpdate($user, $message);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify users about rate changes', [
                'error' => $e->getMessage(),
                'rate_ids' => $rateIds,
            ]);
        }
    }

    /**
     * Export rates to CSV
     */
    public function export()
    {
        try {
            $rates = CryptoRate::all();
            
            $filename = 'crypto_rates_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($rates) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Coin', 'Buy Rate (NGN)', 'Sell Rate (NGN)', 'Spread %', 'Last Updated']);

                foreach ($rates as $rate) {
                    $spread = $rate->buy_rate > 0 && $rate->sell_rate > 0 
                        ? round((($rate->buy_rate - $rate->sell_rate) / $rate->sell_rate) * 100, 2)
                        : 0;

                    fputcsv($file, [
                        $rate->coin,
                        $rate->buy_rate,
                        $rate->sell_rate,
                        $spread . '%',
                        $rate->updated_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            Log::info('Crypto rates exported by admin', [
                'admin_id' => auth()->id(),
                'rates_count' => $rates->count(),
            ]);

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Failed to export crypto rates', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'Failed to export rates. Please try again.');
        }
    }

    /**
     * Import rates from CSV
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Please upload a valid CSV file.');
        }

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();
            $data = array_map('str_getcsv', file($path));
            
            // Remove header row
            array_shift($data);
            
            $updatedCount = 0;
            $errorCount = 0;

            foreach ($data as $row) {
                if (count($row) >= 3) {
                    $coin = strtoupper(trim($row[0]));
                    $buyRate = floatval($row[1]);
                    $sellRate = floatval($row[2]);

                    if ($coin && $buyRate >= 0 && $sellRate >= 0) {
                        CryptoRate::updateOrCreate(
                            ['coin' => $coin],
                            [
                                'buy_rate' => $buyRate,
                                'sell_rate' => $sellRate,
                            ]
                        );
                        $updatedCount++;
                    } else {
                        $errorCount++;
                    }
                } else {
                    $errorCount++;
                }
            }

            Log::info('Crypto rates imported by admin', [
                'admin_id' => auth()->id(),
                'updated_count' => $updatedCount,
                'error_count' => $errorCount,
            ]);

            $message = "Successfully imported {$updatedCount} cryptocurrency rates!";
            if ($errorCount > 0) {
                $message .= " ({$errorCount} rows had errors and were skipped)";
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to import crypto rates', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'Failed to import rates. Please check your file format and try again.');
        }
    }
}
