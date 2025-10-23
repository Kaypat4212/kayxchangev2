<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramTestController extends Controller
{
    public function test()
    {
        $token = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        if (empty($token) || empty($chatId)) {
            Log::error('Telegram test failed: Missing token or chat_id');
            return response()->json(['status' => 'error', 'message' => 'Missing Telegram configuration'], 400);
        }

        $message = "🔔 *Telegram Test Message*\n\nSent from kayxchange-laravel at " . now()->toDateTimeString();
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];

        try {
            $response = Http::timeout(10)->post($url, $payload);
            if ($response->successful()) {
                Log::info('Telegram test message sent successfully', [
                    'chat_id' => $chatId,
                    'response' => $response->json()
                ]);
                return response()->json(['status' => 'success', 'message' => 'Test message sent']);
            } else {
                Log::error('Telegram test message failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return response()->json(['status' => 'error', 'message' => 'Failed to send test message', 'details' => $response->body()], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Telegram test message failed: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Exception occurred', 'details' => $e->getMessage()], 500);
        }
    }
}