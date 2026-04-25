<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Mail\TradeNotification;
use App\Models\Kyc;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Mail;
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
                'id_document'   => 'required|file|mimes:pdf,jpeg,png,jpg|max:2048',
                'selfie'        => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'document_type' => 'required|string|max:100',
                'expiry_date'   => 'nullable|date|after:today|required_if:document_type,drivers_license',
            ]);

            /** @var \App\Models\User $user */
            $user = auth()->user();

            // Store files
            $idPath = $request->file('id_document')->store('kyc_documents', 'public');
            $selfiePath = $request->file('selfie')->store('kyc_selfies', 'public');

            // Create KYC record
            $kyc = Kyc::create([
                'user_id'          => $user->id,
                'id_document_path' => $idPath,
                'selfie_path'      => $selfiePath,
                'document_type'    => $request->document_type,
                'expiry_date'      => $request->expiry_date ?: null,
                'status'           => 'pending',
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

            $rejectionReason = null;
            if ($status === 'rejected') {
                $request->validate([
                    'rejection_reason' => 'required|string|max:1000',
                ]);
                $rejectionReason = trim($request->input('rejection_reason'));
            }

            $kyc->update([
                'status'           => $status,
                'rejection_reason' => $rejectionReason,
                'reviewed_at'      => now(),
            ]);

            if ($status === 'approved') {
                $kyc->user->update(['kyc_verified' => 1]);

                // When KYC is approved, re-check if a pending referral can now be completed
                // (the deposit threshold may already be met but reward was held until KYC)
                try {
                    $kycUser = $kyc->user->fresh();
                    $pendingReferral = \App\Models\Referral::where('referred_id', $kycUser->id)
                        ->where('status', 'pending')
                        ->first();

                    if ($pendingReferral) {
                        $totalDeposited = \App\Models\Deposit::where('user_id', $kycUser->id)
                            ->where('status', 'approved')
                            ->sum('amount');

                        if ($totalDeposited >= 10000) {
                            $reward = (float) \App\Models\SiteContent::get('referral_reward_amount', '500');
                            if ($reward > 0) {
                                $pendingReferral->update(['status' => 'completed', 'reward_amount' => $reward]);
                                $referrer = $pendingReferral->referrer;
                                if ($referrer) {
                                    $referrer->increment('balance', $reward);
                                    Log::info('Referral reward credited on KYC approval', [
                                        'referrer_id' => $referrer->id,
                                        'referred_id' => $kycUser->id,
                                        'reward'      => $reward,
                                    ]);
                                }
                            }
                        }
                    }
                } catch (\Exception $refEx) {
                    Log::warning('KYC referral check failed: ' . $refEx->getMessage());
                }
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
                            'reason' => $rejectionReason ?? '',
                        ],
                        badge: [
                            'text'  => $isApproved ? 'KYC Approved' : 'KYC Rejected',
                            'color' => $isApproved ? '#00cc00' : '#dc3545',
                        ],
                        ctaUrl: url('/kyc'),
                        ctaText: $isApproved ? 'Go to Dashboard' : 'Re-submit Documents',
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

    public function revoke(Request $request, Kyc $kyc)
    {
        try {
            if ($kyc->status !== 'approved') {
                return redirect()->back()->with('error', 'Only approved KYC records can be revoked.');
            }

            $request->validate([
                'rejection_reason' => 'required|string|max:1000',
            ]);

            $rejectionReason = trim($request->input('rejection_reason'));

            $kyc->update([
                'status'           => 'rejected',
                'rejection_reason' => $rejectionReason,
                'reviewed_at'      => now(),
            ]);

            $kyc->user->update(['kyc_verified' => 0]);

            // Notify user via email
            try {
                $kycUser = $kyc->user;
                if ($kycUser) {
                    Mail::to($kycUser->email)->send(new TradeNotification(
                        user: $kycUser,
                        templateKey: 'kyc_rejected',
                        data: ['reason' => $rejectionReason],
                        badge: ['text' => 'KYC Revoked', 'color' => '#dc3545'],
                        ctaUrl: url('/kyc'),
                        ctaText: 'Re-submit Documents',
                    ));
                }
            } catch (\Exception $mailEx) {
                Log::warning('KYC revoke email failed: ' . $mailEx->getMessage());
            }

            return redirect()->route('admin.kyc')->with('success', 'KYC approval revoked for ' . ($kyc->user->name ?? 'user') . '.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Revoke failed: ' . $e->getMessage());
        }
    }

    private function sendTelegramNotification($user, $kyc)
    {
        $message = "🆕 *New KYC Submission*\n\n";
        $message .= "👤 User: {$user->name}\n";
        $message .= "📧 Email: {$user->email}\n";
        $message .= "🆔 Submission: #{$kyc->id}\n";
        $message .= "🕒 Time: " . now()->format('Y-m-d H:i:s');

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Approve KYC', 'callback_data' => "approve_kyc:{$kyc->id}"],
                    ['text' => '❌ Reject KYC', 'callback_data' => "reject_kyc:{$kyc->id}"],
                ],
                [
                    ['text' => '🌐 Open KYC Queue', 'url' => route('admin.kyc')],
                ],
            ],
        ];

        try {
            $sent = app(TelegramService::class)->sendToAdminChats($message, $keyboard);
            if ($sent === 0) {
                Log::warning('No admin Telegram chat configured to receive KYC alerts.');
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
        }
    }
}