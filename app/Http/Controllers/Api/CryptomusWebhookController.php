<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CryptomusService;
use App\Services\CryptoWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CryptomusWebhookController extends Controller
{
    protected $cryptomusService;
    protected $cryptoWalletService;

    public function __construct(CryptomusService $cryptomusService, CryptoWalletService $cryptoWalletService)
    {
        $this->cryptomusService = $cryptomusService;
        $this->cryptoWalletService = $cryptoWalletService;
    }

    /**
     * Handle Cryptomus webhook
     */
    public function handle(Request $request)
    {
        try {
            $data = $request->all();
            $sign = $request->header('sign');

            Log::info('Cryptomus webhook received', [
                'data' => $data,
                'sign' => $sign,
                'headers' => $request->headers->all()
            ]);

            // Verify webhook signature
            if (!$this->cryptomusService->validateWebhook($data, $sign)) {
                Log::warning('Invalid Cryptomus webhook signature');
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            // Process the webhook based on type
            $type = $data['type'] ?? null;
            $orderId = $data['order_id'] ?? null;

            if ($type === 'payment' && str_starts_with($orderId, 'dep_')) {
                $this->cryptoWalletService->processDepositSuccess($data);
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Cryptomus webhook processing error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
}
