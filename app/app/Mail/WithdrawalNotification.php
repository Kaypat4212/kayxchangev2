<?php

namespace App\Mail;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WithdrawalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $withdrawal;
    public $bankDetails;
    public $action;

    public function __construct(Withdrawal $withdrawal, array $bankDetails, string $action)
    {
        $this->withdrawal = $withdrawal;
        $this->bankDetails = $bankDetails;
        $this->action = $action;
    }

    public function build()
    {
        $subject = $this->action === 'approved' 
            ? 'Your Withdrawal Request Has Been Approved' 
            : 'Your Withdrawal Request Has Been Cancelled';

        return $this->subject($subject)
                    ->markdown('emails.withdrawal_notification')
                    ->with([
                        'withdrawal' => $this->withdrawal,
                        'bankDetails' => $this->bankDetails,
                        'action' => $this->action,
                    ]);
    }
}