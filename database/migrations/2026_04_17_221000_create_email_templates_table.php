<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('subject');
            $table->longText('body');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default templates
        $templates = [
            [
                'key'         => 'buy_trade_submitted',
                'subject'     => 'Buy Order Received – {{currency}} {{amount}}',
                'description' => 'Sent to user when a buy trade is submitted.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>We have received your <strong>buy order</strong> for <strong>{{amount}} {{currency}}</strong> worth <strong>₦{{naira_amount}}</strong>.</p>
<p>Your order is now <strong>pending</strong>. Once you upload your payment proof, our team will verify it and process your order promptly.</p>
<p><strong>Order Reference:</strong> {{reference}}<br>
<strong>Wallet Address:</strong> {{wallet_address}}</p>
<p>If you have any questions, please contact our support team.</p>
<p>Thank you for trading with <strong>{{app_name}}</strong>.</p>
HTML,
            ],
            [
                'key'         => 'buy_trade_payment_uploaded',
                'subject'     => 'Payment Proof Received – {{reference}}',
                'description' => 'Sent to user when payment proof is uploaded for a buy trade.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>We have received your payment proof for your <strong>buy order</strong> of <strong>{{amount}} {{currency}}</strong>.</p>
<p>Our admin team is now reviewing your payment. This usually takes just a few minutes.</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>You will receive another email once your order is approved and your crypto is released.</p>
<p>Thank you for your patience.</p>
HTML,
            ],
            [
                'key'         => 'buy_trade_completed',
                'subject'     => 'Buy Order Completed – {{amount}} {{currency}} Sent',
                'description' => 'Sent to user when a buy trade is marked completed.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Great news! Your buy order has been <strong>approved and completed</strong>.</p>
<p><strong>{{amount}} {{currency}}</strong> has been sent to your wallet address:</p>
<p style="word-break:break-all;"><strong>{{wallet_address}}</strong></p>
<p>Please allow a few minutes for the transaction to reflect on the blockchain.</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>Thank you for trading with <strong>{{app_name}}</strong>. We hope to serve you again soon!</p>
HTML,
            ],
            [
                'key'         => 'buy_trade_rejected',
                'subject'     => 'Buy Order Rejected – {{reference}}',
                'description' => 'Sent to user when a buy trade is rejected.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Unfortunately, your buy order for <strong>{{amount}} {{currency}}</strong> has been <strong>rejected</strong>.</p>
<p><strong>Reason:</strong> {{reason}}</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>If you believe this is an error or need further assistance, please contact our support team with your order reference number.</p>
<p>Thank you for understanding.</p>
HTML,
            ],
            [
                'key'         => 'sell_trade_submitted',
                'subject'     => 'Sell Order Received – {{currency}} {{amount}}',
                'description' => 'Sent to user when a sell trade is submitted.',
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
                'key'         => 'sell_trade_completed',
                'subject'     => 'Sell Order Completed – Payment Sent',
                'description' => 'Sent to user when a sell trade is marked completed.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Your sell order has been <strong>approved and completed</strong>!</p>
<p><strong>₦{{naira_amount}}</strong> has been sent to your <strong>{{payment_method}}</strong>.</p>
<p>Please allow a few minutes for the payment to reflect in your account.</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>Thank you for trading with <strong>{{app_name}}</strong>. We hope to see you again soon!</p>
HTML,
            ],
            [
                'key'         => 'sell_trade_rejected',
                'subject'     => 'Sell Order Rejected – {{reference}}',
                'description' => 'Sent to user when a sell trade is rejected.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Unfortunately, your sell order for <strong>{{amount}} {{currency}}</strong> has been <strong>rejected</strong>.</p>
<p><strong>Reason:</strong> {{reason}}</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>If you believe this is an error or would like further clarification, please contact our support team with your order reference.</p>
HTML,
            ],
            [
                'key'         => 'withdrawal_submitted',
                'subject'     => 'Withdrawal Request Received – ₦{{amount}}',
                'description' => 'Sent to user when a withdrawal request is submitted.',
                'body'        => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>We have received your <strong>withdrawal request</strong> for <strong>₦{{amount}}</strong>.</p>
<p><strong>Payment Method:</strong> {{payment_method}}<br>
<strong>Account Details:</strong> {{account_details}}</p>
<p>Our admin team will process your withdrawal and send the funds to your account within a few minutes.</p>
<p>Thank you for your patience.</p>
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
        ];

        foreach ($templates as $tpl) {
            $tpl['created_at'] = now();
            $tpl['updated_at'] = now();
            DB::table('email_templates')->insertOrIgnore($tpl);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
