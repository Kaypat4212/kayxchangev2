<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\BugReport;

class BugReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showForm()
    {
        return view('bug-report');
    }

    public function submit(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:20|max:5000',
            'severity'    => 'required|in:low,medium,high,critical',
            'category'    => 'required|in:general,ui,payment,trade,account,other',
            'page_url'    => 'nullable|url|max:500',
            'attachment'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,gif,webp|max:5120',
        ]);

        try {
            $data = [
                'user_id'     => Auth::id(),
                'title'       => $request->title,
                'description' => $request->description,
                'severity'    => $request->severity,
                'category'    => $request->category,
                'page_url'    => $request->page_url,
                'browser'     => substr($request->userAgent() ?? '', 0, 255),
                'status'      => 'open',
            ];

            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment')
                    ->store('bug_report_attachments', 'public');
            }

            $bug = BugReport::create($data);

            // Telegram alert to admin
            $user    = Auth::user();
            $severity_icon = match($request->severity) {
                'critical' => '🔴', 'high' => '🟠', 'medium' => '🟡', default => '🟢'
            };
            $message = "{$severity_icon} *New Bug Report* #{$bug->id}\n\n"
                . "👤 {$user->name} ({$user->email})\n"
                . "📌 Severity: *{$bug->severity}* | Category: {$bug->category}\n"
                . "📝 {$bug->title}\n\n"
                . substr($bug->description, 0, 200) . (strlen($bug->description) > 200 ? '…' : '');

            $botToken = env('TELEGRAM_BOT_TOKEN');
            $chatId   = env('TELEGRAM_CHAT_ID');
            if ($botToken && $chatId) {
                Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id'    => $chatId,
                    'text'       => $message,
                    'parse_mode' => 'Markdown',
                ]);
            }

            return redirect()->route('bug.report.form')
                ->with('success', "Bug report #{$bug->id} submitted! Our team will investigate.");
        } catch (\Exception $e) {
            Log::error('Bug report submission failed', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return back()->withInput()
                ->withErrors(['error' => 'Failed to submit report. Please try again.']);
        }
    }

    public function myReports()
    {
        $reports = BugReport::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('bug-reports-history', compact('reports'));
    }
}
