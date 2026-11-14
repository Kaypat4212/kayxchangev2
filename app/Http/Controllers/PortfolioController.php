<?php

namespace App\Http\Controllers;

use App\Models\PortfolioHolding;
use App\Services\CryptoWalletService;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index(ExchangeRateService $rates, CryptoWalletService $walletService)
    {
        $market = $rates->getUsdRates();
        $holdings = PortfolioHolding::where('user_id', auth()->id())->latest()->get();
        $supported = array_keys($walletService->getSupportedCryptos());
        $prices = collect($supported)->mapWithKeys(fn ($coin) => is_numeric($market[$coin] ?? null) ? [$coin => (float) $market[$coin]] : [])->all();
        $value = 0.0;
        $complete = true;
        foreach ($holdings as $holding) {
            if (!isset($prices[$holding->asset])) { $complete = false; continue; }
            $value += (float) $holding->quantity * $prices[$holding->asset];
        }
        $cost = $holdings->every(fn ($h) => $h->average_cost_usd !== null)
            ? $holdings->sum(fn ($h) => (float) $h->quantity * (float) $h->average_cost_usd) : null;
        return view('portfolio.index', compact('holdings', 'supported', 'prices', 'value', 'complete', 'cost', 'market'));
    }

    public function store(Request $request, CryptoWalletService $walletService)
    {
        $supported = implode(',', array_keys($walletService->getSupportedCryptos()));
        $data = $request->validate([
            'asset' => 'required|string|in:'.$supported,
            'quantity' => 'required|numeric|gt:0|max:1000000000000',
            'average_cost_usd' => 'nullable|numeric|gte:0|max:1000000000',
            'platform' => 'nullable|string|max:80',
            'notes' => 'nullable|string|max:500',
        ]);
        $data['asset'] = strtoupper($data['asset']);
        auth()->user()->portfolioHoldings()->create($data);
        return redirect()->route('portfolio.index')->with('success', 'Holding added to your watch portfolio.');
    }

    public function destroy(PortfolioHolding $holding)
    {
        abort_unless($holding->user_id === auth()->id(), 404);
        $holding->delete();
        return redirect()->route('portfolio.index')->with('success', 'Holding removed.');
    }
}
