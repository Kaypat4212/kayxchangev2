<?php

namespace App\Http\Controllers;

use App\Models\SellTrade;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TelegramProofUploadController extends Controller
{
    /**
     * Show the upload form. The URL must contain a valid ?token= parameter.
     */
    public function show(Request $request)
    {
        $token   = $request->query('token');
        $payload = $token ? Cache::get("tg_upload_token_{$token}") : null;

        if (!$payload || now()->timestamp > ($payload['expires'] ?? 0)) {
            return view('telegram.upload-proof', [
                'expired' => true,
                'token'   => null,
            ]);
        }

        return view('telegram.upload-proof', [
            'expired' => false,
            'token'   => $token,
            'type'    => $payload['type'] ?? 'sell_proof',
        ]);
    }

    /**
     * Handle the uploaded proof photo.
     */
    public function store(Request $request)
    {
        $token   = $request->input('token');
        $payload = $token ? Cache::get("tg_upload_token_{$token}") : null;

        if (!$payload || now()->timestamp > ($payload['expires'] ?? 0)) {
            return back()->with('error', 'This upload link has expired. Please request a new one from the bot.');
        }

        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        try {
            $chatId = $payload['chat_id'];
            $type   = $payload['type'] ?? 'sell_proof';

            // Store the uploaded file
            $proofPath = $request->file('proof')->store('payment_proofs', 'public');

            // Consume the token so it can't be reused
            Cache::forget("tg_upload_token_{$token}");

            // Update the bot state so the conversation continues
            $telegramService = app(TelegramService::class);

            // Merge proof into bot session data and advance state
            Cache::put("tg_data_{$chatId}",
                array_merge(Cache::get("tg_data_{$chatId}", []), ['proof' => $proofPath]),
                now()->addHour()
            );
            Cache::put("tg_state_{$chatId}", 'sell_payout', now()->addHour());

            // Resolve saved bank details to build payout keyboard
            $data = Cache::get("tg_data_{$chatId}", []);
            $user = \App\Models\User::find($data['user_id'] ?? 0);
            $hasSavedBank = $user && !empty($user->bank_name) && $user->bank_name !== 'N/A';

            $rows = [];
            if ($hasSavedBank) {
                $rows[] = [['text' => "🏦 Saved Bank ({$user->bank_name} — {$user->account_number})", 'callback_data' => 'sell_payout:default_bank']];
            }
            $rows[] = [['text' => '🏦 External/Different Bank',                'callback_data' => 'sell_payout:external_bank']];
            $rows[] = [['text' => '💰 Wallet Balance (add to app balance)',    'callback_data' => 'sell_payout:wallet_balance']];
            $rows[] = [['text' => '❌ Cancel',                                  'callback_data' => 'cancel']];

            $keyboard = ['inline_keyboard' => $rows];

            $telegramService->sendMessage(
                $chatId,
                "✅ *Proof uploaded successfully!*\n\nNow select your payout method:",
                'Markdown',
                $keyboard
            );

            Log::info('Telegram proof uploaded via web', [
                'chat_id'    => $chatId,
                'proof_path' => $proofPath,
            ]);

            return view('telegram.upload-proof', [
                'expired' => false,
                'token'   => null,
                'success' => true,
            ]);

        } catch (\Throwable $e) {
            Log::error('Telegram proof upload failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Upload failed. Please try again or send the photo directly in Telegram.');
        }
    }
}
