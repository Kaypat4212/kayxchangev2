<?php

namespace App\Http\Controllers;

use App\Models\PriceAlert;
use App\Models\CryptoRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceAlertController extends Controller
{
    public function index()
    {
        $alerts = PriceAlert::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        $platformCoins = CryptoRate::query()->orderBy('coin')->pluck('coin')->map(fn ($coin) => trim(strtoupper((string) $coin)))->filter()->unique()->values();
        $marketCoins = ['BTC', 'ETH', 'USDT', 'SOL', 'BNB'];

        return view('price-alerts.index', compact('alerts', 'platformCoins', 'marketCoins'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'             => 'required|in:platform,market',
            'coin'             => 'required|string|max:10',
            'direction'        => 'required|in:above,below',
            'target_price'     => 'required|numeric|min:0.01',
            'notify_telegram'  => 'nullable|boolean',
            'notify_email'     => 'nullable|boolean',
            'notify_app'       => 'nullable|boolean',
        ]);

        $coin = strtoupper(trim($data['coin']));
        $coinAvailable = $data['type'] === 'platform'
            ? CryptoRate::query()->whereRaw('UPPER(coin) = ?', [$coin])->exists()
            : in_array($coin, ['BTC', 'ETH', 'USDT', 'SOL', 'BNB'], true);
        if (!$coinAvailable) {
            return back()->withInput()->withErrors(['coin' => 'Choose a coin that has a platform rate or a supported live market feed.']);
        }

        // Limit to 20 active alerts per user
        $activeCount = PriceAlert::where('user_id', Auth::id())->where('is_active', true)->count();
        if ($activeCount >= 20) {
            return back()->withErrors(['limit' => 'You can only have 20 active price alerts at a time.']);
        }

        PriceAlert::create([
            'user_id'         => Auth::id(),
            'type'            => $data['type'],
            'coin'            => $coin,
            'direction'       => $data['direction'],
            'target_price'    => $data['target_price'],
            'notify_telegram' => $request->boolean('notify_telegram', true),
            'notify_email'    => $request->boolean('notify_email', true),
            'notify_app'      => $request->boolean('notify_app', true),
            'is_active'       => true,
        ]);

        return back()->with('success', 'Price alert created successfully.');
    }

    public function destroy(PriceAlert $priceAlert)
    {
        if ($priceAlert->user_id !== Auth::id()) {
            abort(403);
        }

        $priceAlert->delete();
        return back()->with('success', 'Alert deleted.');
    }

    public function toggle(PriceAlert $priceAlert)
    {
        if ($priceAlert->user_id !== Auth::id()) {
            abort(403);
        }

        $priceAlert->update([
            'is_active'    => !$priceAlert->is_active,
            'triggered_at' => $priceAlert->is_active ? $priceAlert->triggered_at : null,
        ]);

        return back()->with('success', $priceAlert->is_active ? 'Alert reactivated.' : 'Alert paused.');
    }
}
