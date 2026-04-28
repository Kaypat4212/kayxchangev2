<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update buy_trade_submitted
        DB::table('email_templates')
            ->where('key', 'buy_trade_submitted')
            ->update([
                'subject' => 'Buy Order Received – ${{usd_amount}} ({{crypto_amount}} {{currency}})',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>We have received your <strong>buy order</strong> for <strong>${{usd_amount}} ({{crypto_amount}} {{currency}})</strong> worth <strong>₦{{naira_amount}}</strong>.</p>
<p>Your order is now <strong>pending</strong>. Once you upload your payment proof, our team will verify it and process your order promptly.</p>
<p><strong>Order Reference:</strong> {{reference}}<br>
<strong>Wallet Address:</strong> {{wallet_address}}</p>
<p>If you have any questions, please contact our support team.</p>
<p>Thank you for trading with <strong>{{app_name}}</strong>.</p>
HTML,
            ]);

        // Update buy_trade_payment_uploaded
        DB::table('email_templates')
            ->where('key', 'buy_trade_payment_uploaded')
            ->update([
                'subject' => 'Payment Proof Received – {{reference}}',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>We have received your payment proof for your <strong>buy order</strong> of <strong>${{usd_amount}} ({{crypto_amount}} {{currency}})</strong>.</p>
<p>Our admin team is now reviewing your payment. This usually takes just a few minutes.</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>You will receive another email once your order is approved and your crypto is released.</p>
<p>Thank you for your patience.</p>
HTML,
            ]);

        // Update buy_trade_completed
        DB::table('email_templates')
            ->where('key', 'buy_trade_completed')
            ->update([
                'subject' => 'Buy Order Completed – ${{usd_amount}} ({{crypto_amount}} {{currency}}) Sent',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Great news! Your buy order has been <strong>approved and completed</strong>.</p>
<p><strong>${{usd_amount}} ({{crypto_amount}} {{currency}})</strong> has been sent to your wallet address:</p>
<p style="word-break:break-all;"><strong>{{wallet_address}}</strong></p>
<p>Please allow a few minutes for the transaction to reflect on the blockchain.</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>Thank you for trading with <strong>{{app_name}}</strong>. We hope to serve you again soon!</p>
HTML,
            ]);

        // Update buy_trade_rejected
        DB::table('email_templates')
            ->where('key', 'buy_trade_rejected')
            ->update([
                'subject' => 'Buy Order Rejected – {{reference}}',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Unfortunately, your buy order for <strong>${{usd_amount}} ({{crypto_amount}} {{currency}})</strong> has been <strong>rejected</strong>.</p>
<p><strong>Reason:</strong> {{reason}}</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>If you believe this is an error or need further assistance, please contact our support team with your order reference number.</p>
<p>Thank you for understanding.</p>
HTML,
            ]);

        // Update sell_trade_submitted
        DB::table('email_templates')
            ->where('key', 'sell_trade_submitted')
            ->update([
                'subject' => 'Sell Order Received – ${{usd_amount}} ({{crypto_amount}} {{currency}})',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>We have received your <strong>sell order</strong> for <strong>${{usd_amount}} ({{crypto_amount}} {{currency}})</strong>.</p>
<p>Expected payout: <strong>₦{{naira_amount}}</strong></p>
<p>Our team is reviewing your transaction. Once your crypto is confirmed, we will process your payment to your preferred payment method.</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>Thank you for trading with <strong>{{app_name}}</strong>.</p>
HTML,
            ]);

        // Update sell_trade_completed
        DB::table('email_templates')
            ->where('key', 'sell_trade_completed')
            ->update([
                'subject' => 'Sell Order Completed – Payment Sent',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Your sell order has been <strong>approved and completed</strong>!</p>
<p><strong>₦{{naira_amount}}</strong> has been sent to your <strong>{{payment_method}}</strong>.</p>
<p>You sold: <strong>${{usd_amount}} ({{crypto_amount}} {{currency}})</strong></p>
<p>Please allow a few minutes for the payment to reflect in your account.</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>Thank you for trading with <strong>{{app_name}}</strong>. We hope to see you again soon!</p>
HTML,
            ]);

        // Update sell_trade_rejected
        DB::table('email_templates')
            ->where('key', 'sell_trade_rejected')
            ->update([
                'subject' => 'Sell Order Rejected – {{reference}}',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Unfortunately, your sell order for <strong>${{usd_amount}} ({{crypto_amount}} {{currency}})</strong> has been <strong>rejected</strong>.</p>
<p><strong>Reason:</strong> {{reason}}</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>If you believe this is an error or would like further clarification, please contact our support team with your order reference.</p>
HTML,
            ]);
    }

    public function down(): void
    {
        // Revert to old placeholders
        DB::table('email_templates')
            ->where('key', 'buy_trade_submitted')
            ->update([
                'subject' => 'Buy Order Received – {{currency}} {{amount}}',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>We have received your <strong>buy order</strong> for <strong>{{amount}} {{currency}}</strong> worth <strong>₦{{naira_amount}}</strong>.</p>
<p>Your order is now <strong>pending</strong>. Once you upload your payment proof, our team will verify it and process your order promptly.</p>
<p><strong>Order Reference:</strong> {{reference}}<br>
<strong>Wallet Address:</strong> {{wallet_address}}</p>
<p>If you have any questions, please contact our support team.</p>
<p>Thank you for trading with <strong>{{app_name}}</strong>.</p>
HTML,
            ]);

        DB::table('email_templates')
            ->where('key', 'buy_trade_payment_uploaded')
            ->update([
                'subject' => 'Payment Proof Received – {{reference}}',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>We have received your payment proof for your <strong>buy order</strong> of <strong>{{amount}} {{currency}}</strong>.</p>
<p>Our admin team is now reviewing your payment. This usually takes just a few minutes.</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>You will receive another email once your order is approved and your crypto is released.</p>
<p>Thank you for your patience.</p>
HTML,
            ]);

        DB::table('email_templates')
            ->where('key', 'buy_trade_completed')
            ->update([
                'subject' => 'Buy Order Completed – {{amount}} {{currency}} Sent',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Great news! Your buy order has been <strong>approved and completed</strong>.</p>
<p><strong>{{amount}} {{currency}}</strong> has been sent to your wallet address:</p>
<p style="word-break:break-all;"><strong>{{wallet_address}}</strong></p>
<p>Please allow a few minutes for the transaction to reflect on the blockchain.</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>Thank you for trading with <strong>{{app_name}}</strong>. We hope to serve you again soon!</p>
HTML,
            ]);

        DB::table('email_templates')
            ->where('key', 'buy_trade_rejected')
            ->update([
                'subject' => 'Buy Order Rejected – {{reference}}',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Unfortunately, your buy order for <strong>{{amount}} {{currency}}</strong> has been <strong>rejected</strong>.</p>
<p><strong>Reason:</strong> {{reason}}</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>If you believe this is an error or need further assistance, please contact our support team with your order reference number.</p>
<p>Thank you for understanding.</p>
HTML,
            ]);

        DB::table('email_templates')
            ->where('key', 'sell_trade_submitted')
            ->update([
                'subject' => 'Sell Order Received – {{currency}} {{amount}}',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>We have received your <strong>sell order</strong> for <strong>{{amount}} {{currency}}</strong>.</p>
<p>Expected payout: <strong>₦{{naira_amount}}</strong></p>
<p>Our team is reviewing your transaction. Once your crypto is confirmed, we will process your payment to your preferred payment method.</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>Thank you for trading with <strong>{{app_name}}</strong>.</p>
HTML,
            ]);

        DB::table('email_templates')
            ->where('key', 'sell_trade_completed')
            ->update([
                'subject' => 'Sell Order Completed – Payment Sent',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Your sell order has been <strong>approved and completed</strong>!</p>
<p><strong>₦{{naira_amount}}</strong> has been sent to your <strong>{{payment_method}}</strong>.</p>
<p>Please allow a few minutes for the payment to reflect in your account.</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>Thank you for trading with <strong>{{app_name}}</strong>. We hope to see you again soon!</p>
HTML,
            ]);

        DB::table('email_templates')
            ->where('key', 'sell_trade_rejected')
            ->update([
                'subject' => 'Sell Order Rejected – {{reference}}',
                'body'    => <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Unfortunately, your sell order for <strong>{{amount}} {{currency}}</strong> has been <strong>rejected</strong>.</p>
<p><strong>Reason:</strong> {{reason}}</p>
<p><strong>Order Reference:</strong> {{reference}}</p>
<p>If you believe this is an error or would like further clarification, please contact our support team with your order reference.</p>
HTML,
            ]);
    }
};
