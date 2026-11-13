<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CryptoDeposit;
use App\Services\CryptoWalletService;
use App\Services\NowPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NowPaymentsWebhookController extends Controller
{
    public function __invoke(Request $request, NowPaymentsService $provider, CryptoWalletService $wallets)
    {
        $payload = $request->json()->all();
        $signature = $request->header('x-nowpayments-sig');
        if (!$provider->verifyIpn($payload, $signature)) {
            Log::warning('Rejected invalid NOWPayments IPN signature');
            return response()->json(['error'=>'Invalid signature'], 400);
        }

        $orderId = (string)($payload['order_id'] ?? '');
        if (!preg_match('/^dep_(\d+)$/', $orderId, $match)) return response()->json(['status'=>'ignored']);

        $deposit = CryptoDeposit::find((int)$match[1]);
        if (!$deposit) return response()->json(['error'=>'Deposit not found'], 404);
        $metadata = $deposit->payment_data ?? [];
        if (($metadata['provider'] ?? null) !== 'nowpayments'
            || (string)($metadata['provider_payment_id'] ?? '') !== (string)($payload['payment_id'] ?? '')
            || strtolower((string)($metadata['provider_pay_currency'] ?? '')) !== strtolower((string)($payload['pay_currency'] ?? ''))) {
            Log::warning('NOWPayments IPN did not match local deposit', ['deposit_id'=>$deposit->id, 'payment_id'=>$payload['payment_id'] ?? null]);
            return response()->json(['error'=>'Payment does not match this deposit'], 400);
        }

        if (isset($payload['price_amount']) && abs((float)$payload['price_amount'] - (float)$deposit->expected_amount) > 0.02) {
            Log::warning('NOWPayments IPN price amount mismatch', ['deposit_id'=>$deposit->id]);
            return response()->json(['error'=>'Payment amount mismatch'], 400);
        }

        $expectedCrypto = (float)($metadata['expected_crypto_amount'] ?? 0);
        if ($expectedCrypto > 0 && isset($payload['actually_paid']) && (float)$payload['actually_paid'] + 0.00000001 < $expectedCrypto) {
            Log::info('NOWPayments IPN reports an underpayment; deposit remains pending', ['deposit_id'=>$deposit->id]);
            return response()->json(['status'=>'underpaid']);
        }

        $payload['_provider'] = 'nowpayments';
        $wallets->processDepositSuccess($payload);
        return response()->json(['status'=>'ok']);
    }
}
