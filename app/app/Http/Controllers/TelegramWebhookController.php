<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(Request $request)
    {
        Log::info("Telegram webhook received", ["payload" => $request->all()]);

        try {
            $update = $request->all();
            
            if (empty($update)) {
                Log::warning("Empty webhook payload received");
                return response()->json(["status" => "error", "message" => "Empty payload"], 400);
            }

            $result = $this->telegramService->processUpdate($update);

            if ($result) {
                Log::info("Webhook processed successfully");
                return response()->json(["status" => "ok"]);
            } else {
                Log::warning("Failed to process webhook");
                return response()->json(["status" => "error"], 500);
            }

        } catch (\Exception $e) {
            Log::error("Error processing Telegram webhook", [
                "error" => $e->getMessage(),
                "request" => $request->all(),
            ]);

            return response()->json(["status" => "error", "message" => $e->getMessage()], 500);
        }
    }

    public function setup()
    {
        Log::info("Setting up Telegram webhook");
        
        try {
            $result = $this->telegramService->setWebhook(url("/api/telegram/webhook"));
            
            Log::info("Webhook setup result", ["result" => $result]);
            
            if ($result["ok"]) {
                return response()->json([
                    "success" => true,
                    "message" => "Webhook set up successfully",
                    "webhook_url" => url("/api/telegram/webhook")
                ]);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Failed to set up webhook: " . ($result["description"] ?? "Unknown error")
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error("Error setting up webhook", ["error" => $e->getMessage()]);
            
            return response()->json([
                "success" => false,
                "message" => "Error setting up webhook: " . $e->getMessage()
            ], 500);
        }
    }

    public function botInfo()
    {
        try {
            $result = $this->telegramService->getBotInfo();

            if ($result && $result["ok"]) {
                return response()->json([
                    "success" => true,
                    "bot_info" => $result["result"]
                ]);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Failed to get bot info: " . ($result["description"] ?? "Unknown error")
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error("Error getting bot info", ["error" => $e->getMessage()]);
            
            return response()->json([
                "success" => false,
                "message" => "Error getting bot info: " . $e->getMessage()
            ], 500);
        }
    }
}