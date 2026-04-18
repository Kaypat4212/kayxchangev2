<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $templates = [
            [
                'key'         => 'sell_trade_submitted',
                'subject'     => 'Sell Order Received – {{currency}} {{amount}}',
                'description' => 'Sent to user when a sell trade is finalized/submitted.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>We have received your <strong>sell order</strong> for <strong>{{amount}} {{currency}}</strong>.</p>
<p>Expected payout: <strong>₦{{naira_amount}}</strong></p>
<p>Our team is reviewing your transaction. Once your crypto is confirmed, we will process your payment to your preferred payment method.</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>Thank you for trading with <strong>{{app_name}}</strong>.</p>
HTML,
            ],
            [
                'key'         => 'kyc_submitted',
                'subject'     => 'KYC Documents Received – Under Review',
                'description' => 'Sent to user when KYC documents are submitted.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Thank you for submitting your <strong>KYC (Know Your Customer)</strong> documents.</p>
<p>Our compliance team is currently reviewing your submission. This process typically takes <strong>1–24 hours</strong>.</p>
<p>You will receive another email as soon as your verification is complete.</p>
<p>In the meantime, you can continue using <strong>{{app_name}}</strong> with standard limits.</p>
<p>Thank you for your patience.</p>
HTML,
            ],
            [
                'key'         => 'kyc_approved',
                'subject'     => 'KYC Verified – Your Account is Now Fully Verified ✓',
                'description' => 'Sent to user when KYC is approved by admin.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Great news! Your identity verification (KYC) has been <strong>approved</strong>.</p>
<p>Your account is now <strong>fully verified</strong>, giving you access to higher trading limits and all platform features.</p>
<p>Thank you for completing the verification process.</p>
HTML,
            ],
            [
                'key'         => 'kyc_rejected',
                'subject'     => 'KYC Verification Unsuccessful – Action Required',
                'description' => 'Sent to user when KYC is rejected by admin.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Unfortunately, your KYC submission could not be verified at this time.</p>
<p><strong>Reason:</strong> {{reason}}</p>
<p>Please re-submit your documents with the following in mind:</p>
<ul>
  <li>Ensure all images are clear and fully visible</li>
  <li>Government-issued ID must be valid and unexpired</li>
  <li>Selfie must clearly show your face holding the ID</li>
</ul>
<p>If you need assistance, please contact our support team.</p>
HTML,
            ],
            [
                'key'         => 'deposit_initiated',
                'subject'     => 'Deposit Initiated – ₦{{amount}}',
                'description' => 'Sent to user when a deposit payment is initiated.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Your <strong>deposit of ₦{{amount}}</strong> has been initiated via <strong>{{payment_method}}</strong>.</p>
<p><strong>Reference:</strong> {{reference}}</p>
<p>Once your payment is confirmed, your wallet will be credited automatically. This usually happens within a few minutes.</p>
<p>If you experience any issues, please contact our support team with the reference above.</p>
HTML,
            ],
            [
                'key'         => 'withdrawal_approved',
                'subject'     => 'Withdrawal Approved – ₦{{amount}} Sent',
                'description' => 'Sent to user when a withdrawal is approved.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Your withdrawal of <strong>₦{{amount}}</strong> has been <strong>approved and processed</strong>.</p>
<p><strong>Payment Method:</strong> {{payment_method}}<br>
<strong>Account Details:</strong> {{account_details}}</p>
<p>Please allow a few minutes for the funds to reflect in your account.</p>
<p>Thank you for using <strong>{{app_name}}</strong>.</p>
HTML,
            ],
            [
                'key'         => 'withdrawal_cancelled',
                'subject'     => 'Withdrawal Cancelled – ₦{{amount}}',
                'description' => 'Sent to user when a withdrawal is cancelled.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Your withdrawal request for <strong>₦{{amount}}</strong> has been <strong>cancelled</strong>.</p>
<p><strong>Reason:</strong> {{reason}}</p>
<p>If you believe this was done in error or require further assistance, please contact our support team.</p>
<p>Your balance has not been affected.</p>
HTML,
            ],
        ];

        foreach ($templates as $tpl) {
            $tpl['created_at'] = now();
            $tpl['updated_at'] = now();
            DB::table('email_templates')->insertOrIgnore($tpl);
        }
    }

    public function down(): void
    {
        DB::table('email_templates')->whereIn('key', [
            'sell_trade_submitted', 'kyc_submitted', 'kyc_approved', 'kyc_rejected',
            'deposit_initiated', 'withdrawal_approved', 'withdrawal_cancelled',
        ])->delete();
    }
};
