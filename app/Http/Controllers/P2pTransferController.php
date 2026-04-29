<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\P2pTransfer;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class P2pTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Send form ────────────────────────────────────────────────────────────

    public function showSend()
    {
        $user = Auth::user();

        if (!$user->transaction_pin) {
            return redirect()->route('pin.setup')
                ->with('info', 'Please set a transaction PIN before sending money.');
        }

        return view('wallet.send', compact('user'));
    }

    // ── AJAX: look up a recipient by KX tag or email ─────────────────────────

    public function lookupRecipient(Request $request)
    {
        $query = trim($request->input('query', ''));

        if (strlen($query) < 2) {
            return response()->json(['found' => false]);
        }

        $recipient = User::where('id', '!=', Auth::id())
            ->where('is_admin', false)
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(kx_tag) = ?', [strtolower($query)])
                  ->orWhereRaw('LOWER(email) = ?', [strtolower($query)]);
            })
            ->select('id', 'name', 'kx_tag', 'email')
            ->first();

        if (!$recipient) {
            return response()->json(['found' => false, 'message' => 'No user found with that KX tag or email.']);
        }

        return response()->json([
            'found'   => true,
            'id'      => $recipient->id,
            'name'    => $recipient->name,
            'kx_tag'  => $recipient->kx_tag,
            // Mask email for privacy: j***@gmail.com
            'email'   => preg_replace('/(?<=.).(?=[^@]*?.@)/', '*', $recipient->email),
        ]);
    }

    // ── Process transfer ─────────────────────────────────────────────────────

    public function send(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'amount'       => 'required|numeric|min:100',
            'note'         => 'nullable|string|max:200',
            'pin'          => 'required|digits:4',
        ]);
        /** @var User $sender */        $sender = Auth::user();

        // ── PIN check ────────────────────────────────────────────────────────
        if ($sender->pin_locked_until && Carbon::now()->lt($sender->pin_locked_until)) {
            $mins = (int) Carbon::now()->diffInMinutes($sender->pin_locked_until, false);
            return back()->withErrors(['pin' => "PIN locked. Try again in {$mins} minute(s)."])->withInput();
        }

        if (!Hash::check($request->pin, $sender->transaction_pin)) {
            $sender->pin_attempts = ($sender->pin_attempts ?? 0) + 1;
            if ($sender->pin_attempts >= 5) {
                $sender->pin_locked_until = Carbon::now()->addMinutes(15);
            }
            $sender->save();
            $remaining = max(0, 5 - $sender->pin_attempts);
            return back()->withErrors(['pin' => "Incorrect PIN. {$remaining} attempt(s) remaining."])->withInput();
        }

        // Reset PIN attempts on success
        $sender->pin_attempts = 0;
        $sender->pin_locked_until = null;
        $sender->save();

        // ── Guard: cannot send to self ───────────────────────────────────────
        if ((int) $request->recipient_id === $sender->id) {
            return back()->withErrors(['recipient_id' => 'You cannot send money to yourself.'])->withInput();
        }

        $recipient = User::findOrFail($request->recipient_id);

        // ── Fee calculation (configurable via AdminSetting) ──────────────────
        $feeType  = \App\Models\AdminSetting::getSetting('p2p_fee_type', 'none');
        $feeValue = (float) \App\Models\AdminSetting::getSetting('p2p_fee_value', '0');
        $amount   = (float) $request->amount;

        $fee = match ($feeType) {
            'flat'       => $feeValue,
            'percentage' => round($amount * $feeValue / 100, 2),
            default      => 0.0,
        };

        $totalDebit     = $amount + $fee;
        $recipientCredit = $amount - 0; // recipient always gets full amount; fee is on top

        // ── Balance check ────────────────────────────────────────────────────
        if ($sender->balance < $totalDebit) {
            return back()->withErrors(['amount' => 'Insufficient balance.'])->withInput();
        }

        $reference = 'P2P-' . strtoupper(Str::random(12));

        // ── Atomic DB transaction ────────────────────────────────────────────
        DB::transaction(function () use ($sender, $recipient, $amount, $fee, $recipientCredit, $reference, $request) {
            // Deduct from sender
            $sender->decrement('balance', $amount + $fee);

            // Credit recipient
            $recipient->increment('balance', $recipientCredit);

            // Record transfer
            P2pTransfer::create([
                'sender_id'        => $sender->id,
                'recipient_id'     => $recipient->id,
                'amount'           => $amount,
                'fee'              => $fee,
                'recipient_amount' => $recipientCredit,
                'reference'        => $reference,
                'note'             => $request->note ? strip_tags($request->note) : null,
                'status'           => 'completed',
            ]);
        });

        // ── In-app notifications ─────────────────────────────────────────────
        $this->createNotification(
            userId:  $sender->id,
            type:    'p2p_sent',
            title:   '💸 Money Sent',
            message: "You sent ₦" . number_format($amount, 2) . " to {$recipient->name} ({$recipient->kx_tag}).",
            data:    ['reference' => $reference, 'recipient' => $recipient->name, 'amount' => $amount]
        );

        $this->createNotification(
            userId:  $recipient->id,
            type:    'p2p_received',
            title:   '💰 Money Received',
            message: "You received ₦" . number_format($recipientCredit, 2) . " from {$sender->name} ({$sender->kx_tag}).",
            data:    ['reference' => $reference, 'sender' => $sender->name, 'amount' => $recipientCredit]
        );

        // ── Telegram notifications ───────────────────────────────────────────
        $this->sendTelegramAlerts($sender, $recipient, $amount, $fee, $recipientCredit, $reference, $request->note);

        Log::info('[P2P] Transfer completed', [
            'reference' => $reference,
            'sender'    => $sender->id,
            'recipient' => $recipient->id,
            'amount'    => $amount,
            'fee'       => $fee,
        ]);

        return redirect()->route('wallet.transfers')
            ->with('success', "₦" . number_format($amount, 2) . " sent to {$recipient->name} ({$recipient->kx_tag}) successfully!");
    }

    // ── Transfer history ─────────────────────────────────────────────────────

    public function history(Request $request)
    {
        $user   = Auth::user();
        $userId = $user->id;

        // ── Per-person relationship stats ────────────────────────────────────
        // Amounts sent TO each person (completed only)
        $sentPerPerson = P2pTransfer::where('sender_id', $userId)
            ->where('status', 'completed')
            ->select('recipient_id',
                DB::raw('SUM(amount) as total_sent'),
                DB::raw('COUNT(*) as send_count'))
            ->groupBy('recipient_id')
            ->get()
            ->keyBy('recipient_id');

        // Amounts received FROM each person (completed only)
        $receivedPerPerson = P2pTransfer::where('recipient_id', $userId)
            ->where('status', 'completed')
            ->select('sender_id',
                DB::raw('SUM(recipient_amount) as total_received'),
                DB::raw('COUNT(*) as receive_count'))
            ->groupBy('sender_id')
            ->get()
            ->keyBy('sender_id');

        // Build a unified contacts list (all people ever transacted with)
        $allContactIds = $sentPerPerson->keys()
            ->merge($receivedPerPerson->keys())
            ->unique();

        $contacts = User::whereIn('id', $allContactIds)
            ->select('id', 'name', 'kx_tag')
            ->get()
            ->map(function ($u) use ($sentPerPerson, $receivedPerPerson) {
                $s = $sentPerPerson->get($u->id);
                $r = $receivedPerPerson->get($u->id);
                $u->total_sent         = $s ? (float) $s->total_sent     : 0;
                $u->total_received     = $r ? (float) $r->total_received  : 0;
                $u->send_count         = $s ? (int)   $s->send_count      : 0;
                $u->receive_count      = $r ? (int)   $r->receive_count   : 0;
                $u->net                = $u->total_received - $u->total_sent;
                $u->last_activity      = null; // filled below
                return $u;
            })
            ->sortByDesc('total_sent')
            ->values();

        // ── Paginated transfers (optionally filtered by contact) ─────────────
        $filterUserId = $request->integer('with', 0);

        $query = P2pTransfer::with(['sender:id,name,kx_tag', 'recipient:id,name,kx_tag'])
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('recipient_id', $userId);
            });

        if ($filterUserId) {
            $query->where(function ($q) use ($userId, $filterUserId) {
                $q->where(function ($q2) use ($userId, $filterUserId) {
                    $q2->where('sender_id', $userId)->where('recipient_id', $filterUserId);
                })->orWhere(function ($q2) use ($userId, $filterUserId) {
                    $q2->where('sender_id', $filterUserId)->where('recipient_id', $userId);
                });
            });
        }

        $transfers = $query->latest()->paginate(20)->appends($request->query());

        // Attach the filtered contact's stats for display in the filtered header
        $filterContact = $filterUserId
            ? $contacts->firstWhere('id', $filterUserId)
            : null;

        return view('wallet.transfers', compact(
            'transfers', 'user', 'contacts', 'filterContact', 'filterUserId'
        ));
    }

    // ── Admin: reverse a transfer ─────────────────────────────────────────────

    public function reverse(Request $request, P2pTransfer $transfer)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        if ($transfer->status !== 'completed') {
            return back()->with('error', 'Only completed transfers can be reversed.');
        }

        DB::transaction(function () use ($transfer, $request) {
            // Claw back from recipient
            $transfer->recipient()->update(['balance' => DB::raw("balance - {$transfer->recipient_amount}")]);
            // Refund to sender (amount + fee)
            $transfer->sender()->update(['balance' => DB::raw("balance + " . ($transfer->amount + $transfer->fee))]);

            $transfer->update([
                'status'          => 'reversed',
                'reversed_reason' => $request->reason,
                'reversed_at'     => now(),
            ]);
        });

        $this->createNotification(
            userId:  $transfer->sender_id,
            type:    'p2p_reversed',
            title:   '🔄 Transfer Reversed',
            message: "Your transfer of ₦" . number_format($transfer->amount, 2) . " (ref: {$transfer->reference}) has been reversed. Funds returned to your wallet.",
            data:    ['reference' => $transfer->reference]
        );

        return back()->with('success', 'Transfer reversed and funds refunded.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function createNotification(int $userId, string $type, string $title, string $message, array $data = []): void
    {
        try {
            Notification::create([
                'user_id'      => $userId,
                'type'         => $type,
                'title'        => $title,
                'message'      => $message,
                'data'         => $data,
                'is_broadcast' => false,
                'is_read'      => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[P2P] Notification create failed: ' . $e->getMessage());
        }
    }

    private function sendTelegramAlerts(User $sender, User $recipient, float $amount, float $fee, float $recipientCredit, string $reference, ?string $note): void
    {
        try {
            $telegram = app(TelegramService::class);
            $time     = now()->setTimezone('Africa/Lagos')->format('d M Y, g:i A') . ' (WAT)';
            $noteText = $note ? "\n📝 *Note:* {$note}" : '';

            // Notify sender
            if ($sender->telegram_chat_id && $sender->telegram_notifications && $sender->telegram_verified) {
                $msg = "💸 *Money Sent — KayXchange*\n\n"
                     . "You sent *₦" . number_format($amount, 2) . "* to *{$recipient->name}* ({$recipient->kx_tag}).\n"
                     . ($fee > 0 ? "💳 Fee: ₦" . number_format($fee, 2) . "\n" : '')
                     . "🔖 Ref: `{$reference}`{$noteText}\n"
                     . "🕐 {$time}";
                $telegram->sendMessage($sender->telegram_chat_id, $msg, 'Markdown');
            }

            // Notify recipient
            if ($recipient->telegram_chat_id && $recipient->telegram_notifications && $recipient->telegram_verified) {
                $msg = "💰 *Money Received — KayXchange*\n\n"
                     . "You received *₦" . number_format($recipientCredit, 2) . "* from *{$sender->name}* ({$sender->kx_tag}).\n"
                     . "🔖 Ref: `{$reference}`{$noteText}\n"
                     . "🕐 {$time}";
                $telegram->sendMessage($recipient->telegram_chat_id, $msg, 'Markdown');
            }

            // Notify admin
            $adminMsg = "🔁 *P2P Transfer*\n\n"
                . "From: *{$sender->name}* ({$sender->kx_tag})\n"
                . "To:   *{$recipient->name}* ({$recipient->kx_tag})\n"
                . "Amount: *₦" . number_format($amount, 2) . "*"
                . ($fee > 0 ? " | Fee: ₦" . number_format($fee, 2) : '') . "\n"
                . "Ref: `{$reference}`{$noteText}\n"
                . "🕐 {$time}";
            $telegram->sendToAdminChats($adminMsg, null, 'Markdown');

        } catch (\Throwable $e) {
            Log::warning('[P2P] Telegram alerts failed: ' . $e->getMessage());
        }
    }
}
