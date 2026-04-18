<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use App\Services\AdminTradeAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
