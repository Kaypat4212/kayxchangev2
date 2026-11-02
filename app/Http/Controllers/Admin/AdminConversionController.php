<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminConversionController extends Controller
{
    /**
     * List conversions (manual ones first, filterable).
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $mode   = $request->query('mode');

        $query = Conversion::with('user')->latest();

        if ($status && in_array($status, ['pending', 'processing', 'completed', 'failed'], true)) {
            $query->where('status', $status);
        }
        if ($mode && in_array($mode, ['cryptomus', 'manual'], true)) {
            $query->where('mode', $mode);
        }

        $conversions = $query->paginate(20)->withQueryString();

        $counts = [
            'pending'    => Conversion::where('status', 'pending')->count(),
            'processing' => Conversion::where('status', 'processing')->count(),
            'completed'  => Conversion::where('status', 'completed')->count(),
            'failed'     => Conversion::where('status', 'failed')->count(),
        ];

        return view('admin.conversions.index', compact('conversions', 'counts', 'status', 'mode'));
    }

    /**
     * Conversion detail / processing page.
     */
    public function show($id)
    {
        $conversion = Conversion::with(['user', 'processor'])->findOrFail($id);

        return view('admin.conversions.show', compact('conversion'));
    }

    /**
     * Mark a conversion as completed (admin has sent the target crypto).
     */
    public function complete(Request $request, $id)
    {
        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $conversion = Conversion::findOrFail($id);

        if ($conversion->isCompleted()) {
            return back()->with('error', 'This conversion is already completed.');
        }

        $conversion->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'admin_note'   => $request->input('admin_note'),
            'processed_by' => Auth::id(),
        ]);

        Log::info('Conversion completed by admin', [
            'conversion_id' => $conversion->id,
            'admin_id'      => Auth::id(),
        ]);

        return back()->with('success', "Conversion #{$conversion->id} marked as completed.");
    }

    /**
     * Mark a conversion as failed.
     */
    public function fail(Request $request, $id)
    {
        $request->validate([
            'failure_reason' => ['required', 'string', 'max:1000'],
        ]);

        $conversion = Conversion::findOrFail($id);

        if ($conversion->isCompleted()) {
            return back()->with('error', 'This conversion is already completed.');
        }

        $conversion->update([
            'status'         => 'failed',
            'failure_reason' => $request->input('failure_reason'),
            'admin_note'     => $request->input('admin_note'),
            'processed_by'   => Auth::id(),
        ]);

        Log::info('Conversion failed by admin', [
            'conversion_id' => $conversion->id,
            'admin_id'      => Auth::id(),
            'reason'        => $request->input('failure_reason'),
        ]);

        return back()->with('success', "Conversion #{$conversion->id} marked as failed.");
    }
}
