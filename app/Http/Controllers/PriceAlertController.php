<?php

namespace App\Http\Controllers;

use App\Models\PriceAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceAlertController extends Controller
{
    public function index()
    {
        $alerts = PriceAlert::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('price-alerts.index', compact('alerts'));
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

        // Limit to 20 active alerts per user
        $activeCount = PriceAlert::where('user_id', Auth::id())->where('is_active', true)->count();
        if ($activeCount >= 20) {
            return back()->withErrors(['limit' => 'You can only have 20 active price alerts at a time.']);
        }

        PriceAlert::create([
            'user_id'         => Auth::id(),
            'type'            => $data['type'],
            'coin'            => strtoupper($data['coin']),
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
