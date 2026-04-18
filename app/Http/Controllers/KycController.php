<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Mail\TradeNotification;
use App\Models\Kyc;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KycController extends Controller
{
    public function showForm()
    {
        $user = auth()->user();
        $existingKyc = $user ? Kyc::where('user_id', $user->id)->latest()->first() : null;
        return view('kyc.form', compact('existingKyc'));
    }

    public function submit(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_document' => 'required|file|mimes:pdf,jpeg,png,jpg|max:2048',
                'selfie' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $user = auth()->user();

            // Store files
            $idPath = $request->file('id_document')->store('kyc_documents', 'public');
            $selfiePath = $request->file('selfie')->store('kyc_selfies', 'public');

            // Create KYC record
            $kyc = Kyc::create([
                'user_id' => $user->id,
                'id_document_path' => $idPath,
                'selfie_path' => $selfiePath,
                'status' => 'pending',
            ]);

            // Update user KYC status to pending (0)
            $user->update(['kyc_verified' => 0]);

            // Send Telegram notification
            $this->sendTelegramNotification($user, $kyc);

            // Send KYC received email
            try {
                Mail::to($user->email)->send(new TradeNotification(
                    user: $user,
                    templateKey: 'kyc_submitted',
                    data: [],
                    badge: ['text' => 'Under Review', 'color' => '#f0a500'],
                    ctaUrl: url('/dashboard'),
                    ctaText: 'Go to Dashboard',
                ));
            } catch (\Exception $mailEx) {
                Log::warning('KYC submitted email failed: ' . $mailEx->getMessage());
            }

            return redirect()->route('kyc.form')->with('success', 'KYC submitted successfully! Awaiting admin verification.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Submission failed: ' . $e->getMessage());
        }
    }

    public function adminIndex()
    {
        $kycRecords = Kyc::with('user')->latest()->paginate(10);
        return view('admin.kyc', compact('kycRecords'));
    }

    public function verify(Request $request, Kyc $kyc)
    {
        try {
            $status = $request->input('status');
            if (!in_array($status, ['approved', 'rejected'])) {
                return redirect()->back()->with('error', 'Invalid status');
            }

            $kyc->update(['status' => $status]);
            
            if ($status === 'approved') {
                $kyc->user->update(['kyc_verified' => 1]);
            } else {
                $kyc->user->update(['kyc_verified' => 0]);
            }

            // Send KYC outcome email
            try {
                $kycUser = $kyc->user;
                if ($kycUser) {
                    $isApproved = $status === 'approved';
                    Mail::to($kycUser->email)->send(new TradeNotification(
                        user: $kycUser,
                        templateKey: $isApproved ? 'kyc_approved' : 'kyc_rejected',
                        data: [
                            'reason' => request('rejection_reason', 'Documents did not meet our verification requirements.'),
                        ],
                        badge: [
                            'text'  => $isApproved ? 'KYC Approved' : 'KYC Rejected',
                            'color' => $isApproved ? '#00cc00' : '#dc3545',
                        ],
                        ctaUrl: url('/dashboard'),
                        ctaText: 'Go to Dashboard',
                    ));
                }
            } catch (\Exception $mailEx) {
                Log::warning('KYC verify email failed: ' . $mailEx->getMessage());
            }

            return redirect()->route('admin.kyc')->with('success', 'KYC ' . $status . ' successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Verification failed: ' . $e->getMessage());
        }
    }

    private function sendTelegramNotification($user, $kyc)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');
        
        if (!$botToken || !$chatId) {
            Log::warning('Telegram notification not configured');
            return;
        }

        $message = "New KYC Submission\n";
        $message .= "User: {$user->name}\n";
        $message .= "Email: {$user->email}\n";
        $message .= "Submission ID: {$kyc->id}\n";
        $message .= "View: " . route('admin.kyc');

        try {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
        }
    }
}