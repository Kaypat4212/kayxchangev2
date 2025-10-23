<?php
namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminDepositController extends Controller
{
    public function index()
    {
        $deposits = Deposit::with(['user', 'companyAccount'])->latest()->get();
        return view('admin.deposits.index', compact('deposits'));
    }

    public function update(Request $request, Deposit $deposit)
    {
        $request->validate([
            'status' => 'required|in:approved,cancelled',
            'admin_note' => 'nullable|string|max:255',
        ]);

        $deposit->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note,
        ]);

        if ($request->status === 'approved') {
            // Optionally update user's wallet balance
            $deposit->user->wallet()->increment('balance', $deposit->amount);
        }

        return redirect()->route('admin.deposits.index')->with('success', 'Deposit status updated.');
    }
}