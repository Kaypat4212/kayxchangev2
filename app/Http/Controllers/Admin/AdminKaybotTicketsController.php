<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminKaybotTicketsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $status = in_array($request->query('status', 'open'), ['open', 'answered', 'closed', 'all'], true)
            ? $request->query('status', 'open') : 'open';
        $search = trim((string) $request->query('search', ''));
        $tickets = AiSupportTicket::with(['user', 'repliedBy'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($search !== '', fn($q) => $q->where(function ($query) use ($search) {
                $query->where('question', 'like', '%' . $search . '%')
                    ->orWhere('context', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn($user) => $user->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%'));
            }))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'open'     => AiSupportTicket::where('status', 'open')->count(),
            'answered' => AiSupportTicket::where('status', 'answered')->count(),
            'closed'   => AiSupportTicket::where('status', 'closed')->count(),
        ];

        return view('admin.kaybot-tickets', compact('tickets', 'status', 'counts', 'search'));
    }

    public function reply(Request $request, AiSupportTicket $ticket)
    {
        $request->validate(['admin_reply' => 'required|string|max:2000']);

        $ticket->update([
            'admin_reply' => $request->admin_reply,
            'status'      => 'answered',
            'replied_at'  => now(),
            'replied_by'  => auth()->id(),
            'user_notified' => false, // will be shown next time user opens KayBot
        ]);

        return back()->with('success', 'Reply saved. The user will see it next time they open KayBot.');
    }

    public function close(AiSupportTicket $ticket)
    {
        $ticket->update(['status' => 'closed']);
        return back()->with('success', 'Ticket closed.');
    }

    public function reopen(AiSupportTicket $ticket)
    {
        $ticket->update(['status' => 'open']);
        return back()->with('success', 'Ticket reopened.');
    }
}
