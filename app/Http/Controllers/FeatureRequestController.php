<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeatureRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FeatureRequestController extends Controller
{
    /**
     * Show the feature request form.
     *
     * @return \Illuminate\View\View
     */
    public function showForm()
    {
        Log::info('Accessing feature request form', ['user_id' => Auth::id()]);
        return view('feature-request');
    }

    /**
     * Handle feature request submission.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            $featureRequest = new FeatureRequest();
            $featureRequest->user_id = Auth::id();
            $featureRequest->title = $request->title;
            $featureRequest->description = $request->description;
            $featureRequest->status = 'pending';

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('feature_request_attachments', 'public');
                $featureRequest->attachment = $path;
                Log::info('Attachment uploaded', ['path' => $path]);
            }

            $featureRequest->save();
            Log::info('Feature request saved', ['id' => $featureRequest->id]);

            // Send Telegram notification
            $message = "<b>New Feature Request</b>\n";
            $message .= "ID: {$featureRequest->id}\n";
            $message .= "User: " . (Auth::user()->name ?? Auth::user()->email) . "\n";
            $message .= "Title: {$featureRequest->title}\n";
            $message .= "Description: " . substr($featureRequest->description, 0, 100) . (strlen($featureRequest->description) > 100 ? '...' : '') . "\n";
            if ($featureRequest->attachment) {
                $message .= "Attachment: <a href=\"" . asset('storage/' . $featureRequest->attachment) . "\">View</a>\n";
            }
            $message .= "Status: {$featureRequest->status}\n";

            $botToken = env('TELEGRAM_BOT_TOKEN');
            $chatId = env('TELEGRAM_CHAT_ID');
            if ($botToken && $chatId) {
                $response = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]);
                if ($response->successful()) {
                    Log::info('Telegram notification sent for feature request', ['response' => $response->body()]);
                } else {
                    Log::warning('Telegram notification failed for feature request', ['response' => $response->body()]);
                }
            }

            return redirect()->route('feature.request.form')->with('success', 'Feature request submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Feature request submission failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Failed to submit feature request: ' . $e->getMessage()]);
        }
    }
}