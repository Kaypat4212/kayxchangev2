<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCardRate;
use Illuminate\Http\Request;

class AdminGiftCardRateController extends Controller
{
    public function index()
    {
        // Auto-seed defaults if table is empty
        if (GiftCardRate::count() === 0) {
            foreach (GiftCardRate::defaultCards() as $card) {
                GiftCardRate::create(array_merge($card, ['buy_rate' => 0, 'sell_rate' => 0, 'is_active' => true]));
            }
        }

        $rates = GiftCardRate::orderBy('category')->orderBy('name')->orderBy('country')->get();
        $grouped = $rates->groupBy('category');

        return view('admin.gift-card-rates.index', compact('grouped', 'rates'));
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'rates'              => 'required|array',
            'rates.*.buy_rate'   => 'required|numeric|min:0',
            'rates.*.sell_rate'  => 'required|numeric|min:0',
            'rates.*.is_active'  => 'nullable|boolean',
        ]);

        foreach ($request->input('rates') as $id => $row) {
            GiftCardRate::where('id', $id)->update([
                'buy_rate'  => $row['buy_rate'],
                'sell_rate' => $row['sell_rate'],
                'is_active' => isset($row['is_active']) ? 1 : 0,
            ]);
        }

        return back()->with('success', 'Gift card rates updated successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'country'   => 'required|string|max:10',
            'currency'  => 'required|string|max:10',
            'category'  => 'required|string|max:50',
            'buy_rate'  => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0',
        ]);

        GiftCardRate::create(array_merge($data, ['is_active' => true]));

        return back()->with('success', 'Gift card added successfully.');
    }

    public function update(Request $request, $id)
    {
        $rate = GiftCardRate::findOrFail($id);

        $data = $request->validate([
            'buy_rate'  => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $rate->update([
            'buy_rate'  => $data['buy_rate'],
            'sell_rate' => $data['sell_rate'],
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        GiftCardRate::findOrFail($id)->delete();
        return back()->with('success', 'Gift card deleted.');
    }
}
