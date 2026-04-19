<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TelegramBotMessage;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminTelegramController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin) {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    /**
     * Admin Telegram control panel
     */
    public function index()
    {
        $telegram = new TelegramService();

        // Bot info
        $botInfo       = $telegram->getBotInfo();
        $webhookInfo   = $this->getWebhookInfo();
        $isProduction  = $telegram->isProductionMode();

        // User stats
        $totalVerified        = User::where('telegram_verified', true)->count();
        $totalWithNotifications = User::where('telegram_notifications', true)
                                       ->where('telegram_verified', true)->count();
        $recentlyLinked       = User::where('telegram_verified', true)
                                     ->whereNotNull('telegram_chat_id')
                                     ->orderByDesc('updated_at')
                                     ->take(10)
                                     ->get(['id', 'name', 'email', 'telegram_username', 'telegram_chat_id',
                                            'telegram_notifications', 'telegram_verified', 'updated_at']);

        return view('admin.telegram', compact(
            'botInfo', 'webhookInfo', 'isProduction',
            'totalVerified', 'totalWithNotifications', 'recentlyLinked'
        ));
    }

    /**
     * Send a broadcast message to all verified + notifications-enabled users
     */
    public function broadcast(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:4096',
        ]);

        $telegram = new TelegramService();
        $users    = User::where('telegram_verified', true)
                        ->where('telegram_notifications', true)
                        ->whereNotNull('telegram_chat_id')
                        ->get();

        $sent   = 0;
        $failed = 0;

        foreach ($users as $user) {
            $result = $telegram->sendMessage(
                $user->telegram_chat_id,
                "📢 *Message from KayXchange Admin*\n\n" . $request->message
            );
            $result ? $sent++ : $failed++;
        }

        Log::info('Admin telegram broadcast', [
            'admin'   => auth()->user()->email,
            'sent'    => $sent,
            'failed'  => $failed,
            'message' => $request->message,
        ]);

        return back()->with('success', "Broadcast sent: {$sent} delivered, {$failed} failed.");
    }

    /**
     * Send a direct message to a specific user
     */
    public function sendDirect(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:4096',
        ]);

        $user = User::findOrFail($request->user_id);

        if (!$user->telegram_chat_id || !$user->telegram_verified) {
            return back()->with('error', 'This user has not linked their Telegram account.');
        }

        $telegram = new TelegramService();
        $result   = $telegram->sendMessage(
            $user->telegram_chat_id,
            "📩 *Message from KayXchange Support*\n\n" . $request->message
        );

        if ($result) {
            Log::info('Admin sent direct telegram message', [
                'admin'    => auth()->user()->email,
                'user_id'  => $user->id,
                'message'  => $request->message,
            ]);
            return back()->with('success', "Message sent to {$user->name}.");
        }

        return back()->with('error', 'Failed to send message. Check the bot token and try again.');
    }

    /**
     * Set the Telegram webhook to the current APP_URL
     */
    public function setWebhook()
    {
        $telegram    = new TelegramService();
        $webhookUrl  = rtrim(config('app.url'), '/') . '/api/telegram/webhook';
        $result      = $telegram->setWebhook($webhookUrl);

        if (!empty($result['ok'])) {
            return back()->with('success', "Webhook set: {$webhookUrl}");
        }

        $err = $result['description'] ?? 'Unknown error';
        return back()->with('error', "Failed to set webhook: {$err}");
    }

    /**
     * Delete the webhook (switch to polling mode)
     */
    public function deleteWebhook()
    {
        $telegram = new TelegramService();
        $result   = $telegram->removeWebhook();

        if (!empty($result['ok'])) {
            return back()->with('success', 'Webhook removed. You can now use polling locally.');
        }

        return back()->with('error', 'Failed to remove webhook.');
    }

    /**
     * Unlink a user's Telegram account (admin action)
     */
    public function unlinkUser(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user = User::findOrFail($request->user_id);
        $user->update([
            'telegram_chat_id'        => null,
            'telegram_username'       => null,
            'telegram_verified'       => false,
            'telegram_notifications'  => false,
        ]);

        Log::info('Admin unlinked telegram for user', [
            'admin'   => auth()->user()->email,
            'user_id' => $user->id,
        ]);

        return back()->with('success', "{$user->name}'s Telegram account has been unlinked.");
    }

    /**
     * Get webhook info from Telegram API
     */
    private function getWebhookInfo(): array
    {
        try {
            $token    = env('KAYXCHANGE_TELEGRAM_BOT_TOKEN');
            $response = Http::get("https://api.telegram.org/bot{$token}/getWebhookInfo");
            return $response->json()['result'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Bot message inbox — all messages received from users.
     */
    public function messages(Request $request)
    {
        $query = TelegramBotMessage::with('user')->orderByDesc('created_at');

        // Filter by chat_id / username search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('chat_id', 'like', "%{$search}%")
                  ->orWhere('message_text', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('message_type', $type);
        }

        $messages = $query->paginate(50)->withQueryString();

        // Unique senders for stats
        $totalMessages = TelegramBotMessage::count();
        $uniqueSenders = TelegramBotMessage::distinct('chat_id')->count('chat_id');
        $todayMessages = TelegramBotMessage::whereDate('created_at', today())->count();

        return view('admin.telegram-messages', compact(
            'messages', 'totalMessages', 'uniqueSenders', 'todayMessages'
        ));
    }

    /**
     * Proxy a Telegram file (photo, document, sticker) to the browser.
     * Avoids exposing the bot token in front-end URLs.
     */
    public function serveFile(string $fileId)
    {
        // Validate fileId is a safe alphanumeric Telegram file identifier
        if (!preg_match('/^[A-Za-z0-9_\-]{10,200}$/', $fileId)) {
            abort(400);
        }

        $token   = env('KAYXCHANGE_TELEGRAM_BOT_TOKEN');
        $infoRes = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getFile", ['file_id' => $fileId]);

        if (!$infoRes->successful() || empty($infoRes->json('result.file_path'))) {
            abort(404, 'File not found on Telegram.');
        }

        $filePath = $infoRes->json('result.file_path');
        $ext      = strtolower(pathinfo($filePath, PATHINFO_EXTENSION) ?: 'bin');
        $mime     = match($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'tgs'         => 'application/x-tgsticker',
            'pdf'         => 'application/pdf',
            'mp4'         => 'video/mp4',
            'mp3'         => 'audio/mpeg',
            'ogg'         => 'audio/ogg',
            default       => 'application/octet-stream',
        };

        $content = Http::timeout(30)->get("https://api.telegram.org/file/bot{$token}/{$filePath}")->body();
        if (empty($content)) {
            abort(502, 'Failed to fetch file from Telegram.');
        }

        return response($content, 200)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'private, max-age=3600');
    }

    /**
     * Serve a proof image from local storage (for admin access)
     */
    public function serveProof(string $path)
    {
        // Ensure admin access
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403);
        }

        // Validate path to prevent directory traversal
        if (strpos($path, '..') !== false || strpos($path, '/') === 0) {
            abort(400);
        }

        $fullPath = storage_path("app/public/payment_proofs/{$path}");

        if (!file_exists($fullPath)) {
            abort(404);
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Reply directly to a chat_id (linked or unlinked users)
     */
    public function replyToChatId(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|numeric',
            'message' => 'required|string|max:4096',
        ]);

        $telegram = new TelegramService();
        $result   = $telegram->sendMessage(
            $request->chat_id,
            "📩 *Message from KayXchange Support*\n\n" . $request->message
        );

        Log::info('Admin replied to telegram chat_id', [
            'admin'   => auth()->user()->email,
            'chat_id' => $request->chat_id,
            'message' => $request->message,
        ]);

        if ($result) {
            return back()->with('success', 'Reply sent successfully.');
        }

        return back()->with('error', 'Failed to send reply. The user may have blocked the bot.');
    }

    /**
     * AI-powered reply suggestion for the admin reply modal.
     * Uses Groq to generate a contextual, professional support reply.
     */
    public function aiSuggestReply(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id'      => 'required|numeric',
            'user_message' => 'required|string|max:2000',
        ]);

        $apiKey = config('services.groq.api_key');
        $apiUrl = config('services.groq.api_url', 'https://api.groq.com/openai/v1/chat/completions');
        $model  = config('services.groq.model', 'llama-3.3-70b-versatile');

        if (empty($apiKey)) {
            return response()->json(['error' => 'AI is not configured (GROQ_API_KEY missing).'], 503);
        }

        // Fetch last 6 messages from this user for context
        $history = TelegramBotMessage::where('chat_id', $request->chat_id)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get(['message_text', 'message_type', 'created_at'])
            ->reverse()
            ->values();

        $chatHistory = $history->map(function ($m) {
            $time = $m->created_at->format('H:i');
            $type = $m->message_type !== 'text' ? "[{$m->message_type}] " : '';
            return "User [{$time}]: {$type}{$m->message_text}";
        })->join("\n");

        // Linked user context
        $linkedUser  = User::where('telegram_chat_id', (string) $request->chat_id)->first();
        $userContext = $linkedUser
            ? "Name: {$linkedUser->name} | KYC: " . ($linkedUser->kyc_verified ? 'Verified' : 'Not verified') .
              ' | Balance: ₦' . number_format($linkedUser->balance, 2)
            : 'Not linked to any account.';

        $system = <<<PROMPT
You are a customer support assistant for KayXchange — a Nigerian crypto trading platform (BTC, ETH, USDT, LTC, XRP → NGN).
Your job: help the admin compose a professional, friendly, and concise Telegram reply to a user.

Rules:
- Always be warm but professional
- Keep it brief (2–4 sentences max)
- Use plain text only — NO markdown, NO asterisks, NO bullet points
- Address the user's actual issue directly
- If the issue is a trade, remind them that support is available 24/7
- Never promise specific rates or values
PROMPT;

        $prompt = "Customer info: {$userContext}\n\n";
        if ($chatHistory) {
            $prompt .= "Recent conversation:\n{$chatHistory}\n\n";
        }
        $prompt .= "Latest message from user: {$request->user_message}\n\n";
        $prompt .= "Write a helpful admin reply:";

        try {
            $response = Http::timeout(30)
                ->withToken($apiKey)
                ->post($apiUrl, [
                    'model'       => $model,
                    'temperature' => 0.55,
                    'max_tokens'  => 250,
                    'messages'    => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('AdminTelegram aiSuggestReply failed', ['status' => $response->status()]);
                return response()->json(['error' => 'AI request failed. Try again.'], 502);
            }

            $suggestion = trim($response->json('choices.0.message.content', ''));
            return response()->json(['suggestion' => $suggestion]);
        } catch (\Throwable $e) {
            Log::error('AdminTelegram aiSuggestReply exception', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'AI request failed: ' . $e->getMessage()], 500);
        }
    }
}
