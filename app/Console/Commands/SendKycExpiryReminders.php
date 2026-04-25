<?php

namespace App\Console\Commands;

use App\Models\Kyc;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendKycExpiryReminders extends Command
{
    protected $signature = 'kyc:expiry-reminders';
    protected $description = 'Send reminders to users whose KYC document expires within 30 days';

    public function handle(): void
    {
        $soon = now()->addDays(30);

        $kycs = Kyc::where('status', 'approved')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $soon->toDateString())
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->with('user')
            ->get();

        if ($kycs->isEmpty()) {
            $this->info('No expiring KYC documents found.');
            return;
        }

        foreach ($kycs as $kyc) {
            $user = $kyc->user;
            if (!$user) {
                continue;
            }

            $daysLeft = now()->diffInDays($kyc->expiry_date, false);

            try {
                Mail::send('emails.kyc-expiry-reminder', [
                    'user'       => $user,
                    'kyc'        => $kyc,
                    'daysLeft'   => $daysLeft,
                    'expiryDate' => $kyc->expiry_date->format('M d, Y'),
                ], function ($msg) use ($user) {
                    $msg->to($user->email, $user->name)
                        ->subject('Action Required: Your KYC Document Expires Soon');
                });

                $this->info("Reminder sent to {$user->email} (expires in {$daysLeft} days)");
            } catch (\Exception $e) {
                $this->error("Failed to send to {$user->email}: " . $e->getMessage());
            }
        }

        $this->info("Done. Processed {$kycs->count()} KYC record(s).");
    }
}
