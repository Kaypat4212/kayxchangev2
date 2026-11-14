<?php

namespace App\Http\Controllers;

use App\Models\CryptoDeposit;
use App\Models\SupportCase;
use Illuminate\Http\Request;

class SupportCaseController extends Controller
{
    public function createDeposit(CryptoDeposit $deposit)
    {
        if ((int) $deposit->user_id !== (int) auth()->id()) abort(404);
        return view('support.deposit-case', compact('deposit'));
    }

    public function index()
    {
        $cases = SupportCase::where('user_id', auth()->id())->latest()->paginate(15);
        return view('support.cases', compact('cases'));
    }

    public function store(Request $request, ?CryptoDeposit $deposit = null)
    {
        if ($deposit && (int) $deposit->user_id !== (int) auth()->id()) abort(404);
        $data = $request->validate([
            'subject' => 'required|string|max:160',
            'message' => 'required|string|min:10|max:4000',
            'category' => 'nullable|in:deposit,withdrawal,swap,bill,general',
        ]);
        if (SupportCase::where('user_id', auth()->id())->whereIn('status', ['open', 'in_progress'])->count() >= 5) {
            return back()->withErrors(['case' => 'You have reached the limit of five open support cases. Please wait for a response on an existing case.'])->withInput();
        }
        $case = SupportCase::create([
            'user_id' => auth()->id(), 'crypto_deposit_id' => $deposit?->id,
            'category' => $deposit ? 'deposit' : ($data['category'] ?? 'general'),
            'subject' => $data['subject'], 'message' => $data['message'],
        ]);
        foreach (\App\Models\User::where('is_admin', true)->get(['id']) as $admin) {
            \App\Models\Notification::create([
                'admin_id' => $admin->id, 'type' => 'info', 'title' => 'New customer support case',
                'message' => 'Case #' . $case->id . ': ' . $case->subject,
                'data' => ['support_case_id' => $case->id, 'category' => $case->category],
            ]);
        }
        return redirect()->route('support.cases.index')->with('success', 'Support case #' . $case->id . ' was submitted.');
    }

    public function adminIndex()
    {
        $cases = SupportCase::with(['user', 'deposit', 'assignedAdmin'])->latest()->paginate(25);
        return view('admin.support-cases', compact('cases'));
    }

    public function respond(Request $request, SupportCase $supportCase)
    {
        $data = $request->validate([
            'admin_response' => 'required|string|min:2|max:4000',
            'status' => 'required|in:in_progress,resolved',
        ]);
        $supportCase->update([
            ...$data, 'assigned_admin_id' => auth()->id(),
            'resolved_at' => $data['status'] === 'resolved' ? now() : null,
        ]);
        \App\Models\Notification::createForUser(
            (int) $supportCase->user_id,
            'info',
            'Support replied to your case',
            'A response was added to support case #' . $supportCase->id . '.',
            ['support_case_id' => $supportCase->id]
        );
        return back()->with('success', 'Support case updated.');
    }
}
