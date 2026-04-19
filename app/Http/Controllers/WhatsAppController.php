<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    private WhatsAppService $wa;

    public function __construct(WhatsAppService $wa)
    {
        $this->wa = $wa;
    }

    /**
     * GET /whatsapp/webhook
     * Meta verification handshake — called once when you register the webhook.
     */
    public function verify(Request $request)
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $expectedToken = config('services.whatsapp.verify_token');

        if ($mode === 'subscribe' && $token === $expectedToken) {
            Log::info('WhatsApp webhook verified');
            return response((string) $challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('WhatsApp webhook verification failed', [
            'mode'  => $mode,
            'token' => $token,
        ]);
        return response('Forbidden', 403);
    }

    /**
     * POST /whatsapp/webhook
     * Receives all incoming WhatsApp messages and status updates.
     */
    public function webhook(Request $request)
    {
        // Log raw payload for debugging (remove in production if noisy)
        Log::debug('WhatsApp webhook payload', ['body' => $request->all()]);

        $payload = $request->all();

        // Basic structural check
        if (($payload['object'] ?? '') !== 'whatsapp_business_account') {
            return response()->json(['status' => 'ignored'], 200);
        }

        $this->wa->processWebhook($payload);

        // Always respond 200 quickly — Meta will retry if we don't
        return response()->json(['status' => 'ok'], 200);
    }
}
