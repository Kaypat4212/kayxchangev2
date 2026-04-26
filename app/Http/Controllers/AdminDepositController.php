<?php
namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\Deposit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;

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

        $updateData = [
            'status' => $request->status,
        ];

        if (Schema::hasColumn('deposits', 'admin_note')) {
            $updateData['admin_note'] = $request->admin_note;
        }

        $deposit->update($updateData);

        if ($request->status === 'approved') {
            // Calculate fee (use stored fee_amount if already set, else recalculate)
            $feeAmount = (float) ($deposit->fee_amount ?? 0);
            if ($feeAmount === 0.0) {
                $feeType  = AdminSetting::get('deposit_fee_type', 'none');
                $feeValue = (float) AdminSetting::get('deposit_fee_value', '0');
                if ($feeType === 'flat') $feeAmount = $feeValue;
                elseif ($feeType === 'percentage') $feeAmount = round((float)$deposit->amount * $feeValue / 100, 2);
            }

            $creditAmount = max(0, (float)$deposit->amount - $feeAmount);

            // Store fee on deposit record if not already set
            if ((float)($deposit->fee_amount ?? 0) === 0.0 && $feeAmount > 0) {
                $deposit->fee_amount = $feeAmount;
                $deposit->save();
            }

            $deposit->user->wallet()->increment('balance', $creditAmount);
        }

        return redirect()->route('admin.deposits.index')->with('success', 'Deposit status updated.');
    }
}