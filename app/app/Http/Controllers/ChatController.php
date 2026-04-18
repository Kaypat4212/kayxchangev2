<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $message = ChatMessage::create([
            'sender_id' => $user->id,
            'receiver_id' => null, // Support team (null for now)
            'content' => $request->message,
        ]);

        broadcast(new MessageSent($user, $message))->toOthers();

        return response()->json(['status' => 'Message sent']);
    }

    public function getHistory()
    {
        $user = Auth::user();
        $messages = ChatMessage::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orWhereNull('receiver_id')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function adminChat()
    {
        $users = \App\Models\User::where('is_admin', false)->get();
        return view('admin.chat', compact('users'));
    }
}