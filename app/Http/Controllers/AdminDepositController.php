<?php
namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use App\Services\BalanceSettlementService;

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
            'status'     => 'required|in:approved,cancelled',
            'admin_note' => 'nullable|string|max:255',
        ]);

        $adminNote = Schema::hasColumn('deposits', 'admin_note') ? $request->admin_note : null;
        $changed = app(BalanceSettlementService::class)->setDepositStatus(
            (int) $deposit->id,
            $request->status,
            $adminNote
        );

        return redirect()->route('admin.deposits.index')->with(
            $changed ? 'success' : 'info',
            $changed ? 'Deposit status updated and balance settled.' : 'This deposit has already been processed.'
        );
    }
}
