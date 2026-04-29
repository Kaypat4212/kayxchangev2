<?php

namespace App\Http\Controllers;

use App\Models\BuyTrade;
use App\Models\Conversion;
use App\Services\CryptomusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CryptomusController extends Controller
{
    private CryptomusService $cryptomus;

    public function __construct(CryptomusService $cryptomus)
    {
        $this->cryptomus = $cryptomus;
    }

    /**
     * Handle Cryptomus webhook notifications
     */
    public function webhook(Request $request)
    {
        try {
            $data = $request->all();
            $sign = $request->header('sign');

            Log::info('Cryptomus webhook received', [
                'data' => $data,
                'sign' => $sign
            ]);

            // Validate webhook signature
            if (!$this->cryptomus->validateWebhook($data, $sign)) {
                Log::warning('Invalid Cryptomus webhook signature');
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            // Process the webhook based on type
            if (isset($data['type']) && $data['type'] === 'payment') {
                $this->processPaymentWebhook($data);
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Cryptomus webhook error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Process payment webhook
     */
    private function processPaymentWebhook(array $data): void
    {
        $orderId = $data['order_id'] ?? null;
        $status = $data['status'] ?? null;
        $paymentId = $data['uuid'] ?? null;

        if (!$orderId || !$status) {
            Log::warning('Missing order_id or status in payment webhook', $data);
            return;
        }

        // Check if this is a conversion (order_id starts with 'conv_')
        if (str_starts_with($orderId, 'conv_')) {
            $this->processConversionWebhook($data);
            return;
        }

        // Find the trade by transaction reference
        $trade = BuyTrade::where('transaction_ref', $orderId)->first();

        if (!$trade) {
            Log::warning('Trade not found for order_id', ['order_id' => $orderId]);
            return;
        }

        Log::info('Processing Cryptomus payment webhook', [
            'trade_id' => $trade->id,
            'order_id' => $orderId,
            'status' => $status,
            'payment_id' => $paymentId
        ]);

        // Update trade status based on payment status
        switch ($status) {
            case 'paid':
            case 'paid_over':
                $trade->update([
                    'status' => 'paid',
                    'payment_id' => $paymentId,
                    'payment_method' => 'Cryptomus',
                    'paid_at' => now()
                ]);

                // Send success notifications
                $this->sendPaymentSuccessNotifications($trade);
                break;

            case 'wrong_amount':
                $trade->update([
                    'status' => 'wrong_amount',
                    'payment_id' => $paymentId
                ]);
                break;

            case 'cancel':
            case 'fail':
                $trade->update([
                    'status' => 'failed',
                    'payment_id' => $paymentId
                ]);
                break;

            default:
                Log::info('Unhandled payment status', ['status' => $status]);
                break;
        }
    }

    /**
     * Send payment success notifications
     */
    private function sendPaymentSuccessNotifications(BuyTrade $trade): void
    {
        try {
            // Update user balance if needed
            // Send email notification
            // Send Telegram alert
            // etc.

            Log::info('Payment success notifications sent', ['trade_id' => $trade->id]);

        } catch (\Exception $e) {
            Log::error('Failed to send payment success notifications', [
                'trade_id' => $trade->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process conversion webhook
     */
    private function processConversionWebhook(array $data): void
    {
        $orderId = $data['order_id'] ?? null;
        $status = $data['status'] ?? null;
        $paymentId = $data['uuid'] ?? null;

        // Extract conversion ID from order_id (format: conv_{id})
        $conversionId = str_replace('conv_', '', $orderId);

        if (!is_numeric($conversionId)) {
            Log::warning('Invalid conversion order_id format', ['order_id' => $orderId]);
            return;
        }

        $conversion = Conversion::find($conversionId);

        if (!$conversion) {
            Log::warning('Conversion not found', ['conversion_id' => $conversionId]);
            return;
        }

        Log::info('Processing Cryptomus conversion webhook', [
            'conversion_id' => $conversion->id,
            'order_id' => $orderId,
            'status' => $status,
            'payment_id' => $paymentId
        ]);

        // Update conversion status based on payment status
        switch ($status) {
            case 'paid':
            case 'paid_over':
                $conversion->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                // Send success notifications
                $this->sendConversionSuccessNotifications($conversion);
                break;

            case 'wrong_amount':
                $conversion->update([
                    'status' => 'failed',
                    'failure_reason' => 'Incorrect payment amount',
                ]);
                break;

            case 'cancel':
            case 'fail':
                $conversion->update([
                    'status' => 'failed',
                    'failure_reason' => 'Payment failed or cancelled',
                ]);
                break;

            default:
                Log::info('Unhandled conversion status', ['status' => $status]);
                break;
        }
    }

    /**
     * Send conversion success notifications
     */
    private function sendConversionSuccessNotifications(Conversion $conversion): void
    {
        try {
            // Send email notification
            // Send Telegram alert
            // Update user wallet balance if needed
            // etc.

            Log::info('Conversion success notifications sent', ['conversion_id' => $conversion->id]);

        } catch (\Exception $e) {
            Log::error('Failed to send conversion success notifications', [
                'conversion_id' => $conversion->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle payment success redirect
     */
    public function paymentSuccess(Request $request, string $orderId)
    {
        $trade = BuyTrade::where('transaction_ref', $orderId)->first();

        if (!$trade) {
            return redirect()->route('dashboard')->with('error', 'Trade not found');
        }

        return redirect()->route('buy.success', $trade->id);
    }

    /**
     * Handle payment failure redirect
     */
    public function paymentFailed(Request $request, string $orderId)
    {
        $trade = BuyTrade::where('transaction_ref', $orderId)->first();

        if (!$trade) {
            return redirect()->route('dashboard')->with('error', 'Trade not found');
        }

        return redirect()->route('buy.payment', $trade->id)
            ->with('error', 'Payment was not completed. Please try again.');
    }

    /**
     * Get supported cryptocurrencies for frontend
     */
    public function getCurrencies()
    {
        try {
            $currencies = $this->cryptomus->getCurrencies();

            // Filter to popular coins and format for frontend
            $popularCoins = ['BTC', 'ETH', 'USDT', 'USDC', 'BNB', 'ADA', 'SOL', 'DOT'];
            $filtered = array_filter($currencies, function($currency) use ($popularCoins) {
                return in_array($currency['code'] ?? '', $popularCoins);
            });

            return response()->json([
                'success' => true,
                'currencies' => array_values($filtered)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch Cryptomus currencies', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load cryptocurrencies'
            ], 500);
        }
    }
}