<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use App\Services\AdminTradeAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    // ── User: send message to support ──────────────────────────────────────
    public function sendMessage(Request $request)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $user = Auth::user();
        // receiver_id null = message goes to admin inbox
        $message = ChatMessage::create([
            'sender_id'   => $user->id,
            'receiver_id' => null,
            'content'     => $request->message,
        ]);

        // Count all unread messages from this user (including the one just created)
        $unreadCount = ChatMessage::where('sender_id', $user->id)
            ->whereNull('receiver_id')
            ->where('is_read', false)
            ->count();

        // Alert admin on Telegram once per unread session (first message or back after all were read)
        if ($unreadCount === 1) {
            try {
                app(AdminTradeAlertService::class)
                    ->sendSupportChatAlert($user, $request->message, $unreadCount);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Support chat Telegram alert failed: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => 'sent', 'message' => $message]);
    }

    // ── User: get own conversation with support ──────────────────────────
    public function getHistory(Request $request, $userId = null)
    {
        $authUser = Auth::user();

        if ($authUser->is_admin && $userId) {
            // Admin fetching a specific user's conversation
            $messages = ChatMessage::where(function ($q) use ($userId) {
                    $q->where('sender_id', $userId)->whereNull('receiver_id');
                })
                ->orWhere(function ($q) use ($userId) {
                    $q->where('receiver_id', $userId);
                })
                ->orderBy('created_at', 'asc')
                ->with('sender:id,name,email')
                ->get();

            // Mark admin's unread messages as read
            ChatMessage::where('sender_id', $userId)->whereNull('receiver_id')->where('is_read', false)->update(['is_read' => true]);
        } else {
            // User fetching their own chat
            $messages = ChatMessage::where(function ($q) use ($authUser) {
                    $q->where('sender_id', $authUser->id)->whereNull('receiver_id');
                })
                ->orWhere('receiver_id', $authUser->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return response()->json($messages);
    }

    // ── User: support chat page ──────────────────────────────────────────
    public function supportChat()
    {
        return view('support.chat');
    }

    // ── Admin: list users with open support messages ──────────────────────
    public function adminChat()
    {
        // Get users who have sent at least one message to support (receiver_id = null)
        $users = User::where('is_admin', false)
            ->whereHas('sentMessages', function ($q) {
                $q->whereNull('receiver_id');
            })
            ->withCount(['sentMessages as unread_count' => function ($q) {
                $q->whereNull('receiver_id')->where('is_read', false);
            }])
            ->orderByDesc('unread_count')
            ->get();

        return view('admin.chat', compact('users'));
    }

    // ── Admin: reply to a user ───────────────────────────────────────────
    public function adminReply(Request $request)
    {
        $request->validate([
            'message'     => 'required|string|max:2000',
            'receiver_id' => 'required|integer|exists:users,id',
        ]);

        $admin = Auth::user();
        $message = ChatMessage::create([
            'sender_id'   => $admin->id,
            'receiver_id' => $request->receiver_id,
            'content'     => $request->message,
        ]);

        return response()->json(['status' => 'sent', 'message' => $message]);
    }

    // ── Polling: how many new messages since last_id ─────────────────────
    public function pollNew(Request $request)
    {
        $user    = Auth::user();
        $lastId  = (int) $request->get('last_id', 0);

        if ($user->is_admin) {
            $messages = ChatMessage::whereNull('receiver_id')
                ->where('id', '>', $lastId)
                ->with('sender:id,name')
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            $messages = ChatMessage::where(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)->whereNull('receiver_id');
                })
                ->orWhere('receiver_id', $user->id)
                ->where('id', '>', $lastId)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return response()->json($messages);
    }

    // ── Admin: AI-powered reply assistant ────────────────────────────────
    // mode=suggest → generate a reply from conversation context
    // mode=rewrite → professionally rewrite the admin's draft
    public function aiAssist(Request $request): JsonResponse
    {
        $request->validate([
            'mode'        => 'required|in:suggest,rewrite',
            'user_id'     => 'required|integer|exists:users,id',
            'draft'       => 'nullable|string|max:2000',
        ]);

        $apiKey = config('services.groq.api_key');
        $apiUrl = config('services.groq.api_url', 'https://api.groq.com/openai/v1/chat/completions');
        $model  = config('services.groq.model', 'llama-3.3-70b-versatile');

        if (empty($apiKey)) {
            return response()->json(['error' => 'AI is not configured (GROQ_API_KEY missing).'], 503);
        }

        $targetUser = User::find($request->user_id);

        // Fetch last 8 messages in this conversation for context
        $history = ChatMessage::where(function ($q) use ($request) {
                $q->where('sender_id', $request->user_id)->whereNull('receiver_id');
            })
            ->orWhere('receiver_id', $request->user_id)
            ->orderBy('created_at', 'asc')
            ->latest('id')
            ->limit(8)
            ->get()
            ->sortBy('id')
            ->values();

        $adminId = Auth::id();
        $chatHistory = $history->map(function ($m) use ($adminId) {
            $role = ($m->sender_id === $adminId) ? 'Support' : 'User';
            return "{$role}: {$m->content}";
        })->join("\n");

        $userContext = $targetUser
            ? "Name: {$targetUser->name} | KYC: " . ($targetUser->kyc_verified ? 'Verified' : 'Not verified') .
              ' | Balance: ₦' . number_format($targetUser->balance ?? 0, 2)
            : 'Unknown user.';

        $system = <<<'PROMPT'
You are a customer support assistant for KayXchange — a Nigerian crypto exchange platform.
The platform lets users sell crypto (BTC, ETH, USDT, TRON, LTC, XRP) for Nigerian Naira (NGN), buy crypto, make NGN deposits and withdrawals, and complete KYC verification.
Admin features include approving trades, managing deposits, verifying KYC, and monitoring wallets.

Your job: help the KayXchange support admin compose professional, warm, and concise replies to users.

Rules:
- Be friendly, professional, and empathetic — you represent the KayXchange brand
- Keep replies concise (2–5 sentences)
- Address the user's specific issue directly
- Plain text only — NO markdown, asterisks, or bullet points in the actual reply
- Never promise specific exchange rates or timelines you cannot guarantee
- If the issue needs admin action (trade approval, KYC, etc.), assure the user the team will handle it promptly
PROMPT;

        if ($request->mode === 'rewrite') {
            $draft = trim($request->draft ?? '');
            if (empty($draft)) {
                return response()->json(['error' => 'No draft text to rewrite.'], 422);
            }
            $prompt = "Customer info: {$userContext}\n\n";
            if ($chatHistory) {
                $prompt .= "Conversation context:\n{$chatHistory}\n\n";
            }
            $prompt .= "Admin's draft reply:\n{$draft}\n\n";
            $prompt .= "Rewrite this reply to sound more professional, clear, and helpful while keeping the same meaning. Output ONLY the rewritten reply text, nothing else.";
        } else {
            // mode=suggest
            if (empty($chatHistory)) {
                return response()->json(['error' => 'No conversation history to generate a reply from.'], 422);
            }
            $prompt = "Customer info: {$userContext}\n\n";
            $prompt .= "Conversation so far:\n{$chatHistory}\n\n";
            $prompt .= "Based on the user's latest message, write a helpful, professional support reply on behalf of KayXchange admin. Output ONLY the reply text, nothing else.";
        }

        try {
            $response = Http::timeout(30)
                ->withToken($apiKey)
                ->post($apiUrl, [
                    'model'       => $model,
                    'temperature' => 0.5,
                    'max_tokens'  => 300,
                    'messages'    => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Chat aiAssist failed', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'AI request failed. Try again.'], 502);
            }

            $result = trim($response->json('choices.0.message.content', ''));
            return response()->json(['suggestion' => $result]);
        } catch (\Throwable $e) {
            Log::error('Chat aiAssist exception', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'AI request failed: ' . $e->getMessage()], 500);
        }
    }
}

