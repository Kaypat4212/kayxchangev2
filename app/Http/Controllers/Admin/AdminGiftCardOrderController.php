<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\GiftCardOrder;
use App\Models\Notification;
use App\Services\BalanceSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminGiftCardOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['pending', 'completed', 'rejected'], true) ? $request->query('status') : '';
        $orders = GiftCardOrder::with(['user', 'reviewer'])->when($status, fn($query) => $query->where('status', $status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%' . trim($request->query('search')) . '%';
                $query->where(fn($q) => $q->where('reference', 'like', $term)->orWhere('card_name', 'like', $term)
                    ->orWhere('recipient_email', 'like', $term)->orWhereHas('user', fn($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term)));
            })->latest()->paginate(20)->withQueryString();
        return view('admin.gift-card-orders.index', compact('orders', 'status'));
    }

    public function complete(Request $request, GiftCardOrder $order)
    {
        $data = $request->validate(['delivery_code' => ['required', 'string', 'max:4000'], 'admin_note' => ['nullable', 'string', 'max:1000']]);
        DB::transaction(function () use ($request, $order, $data): void {
            $locked = GiftCardOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') throw ValidationException::withMessages(['order' => 'Only pending gift card purchases can be completed.']);
            $before = ['status' => $locked->status, 'delivery_code' => null];
            $locked->delivery_code = trim($data['delivery_code']);
            $locked->admin_note = $data['admin_note'] ?? null;
            $locked->reviewed_by = $request->user()->id;
            $locked->status = 'completed';
            $locked->processed_at = now();
            $locked->save();
            AdminAuditLog::create(['admin_id' => $request->user()->id, 'action' => 'gift_card_order.completed', 'subject_type' => GiftCardOrder::class, 'subject_id' => $locked->id, 'before' => $before, 'after' => ['status' => 'completed', 'reference' => $locked->reference], 'ip_address' => $request->ip(), 'user_agent' => substr((string)$request->userAgent(), 0, 1000)]);
            Notification::createForUser($locked->user_id, 'success', 'Gift card ready', 'Your ' . $locked->card_name . ' ' . $locked->country . ' gift card is ready in Gift Cards → Purchase history.', ['type' => 'gift_card_order', 'order_id' => $locked->id]);
        });
        return back()->with('success', 'Gift card purchase marked fulfilled. The delivery code is encrypted at rest and visible only to the buyer and admins.');
    }

    public function reject(Request $request, GiftCardOrder $order)
    {
        $data = $request->validate(['admin_note' => ['required', 'string', 'min:5', 'max:1000']]);
        DB::transaction(function () use ($request, $order, $data): void {
            $locked = GiftCardOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') throw ValidationException::withMessages(['order' => 'Only pending gift card purchases can be rejected.']);
            $before = ['status' => $locked->status, 'total_ngn' => $locked->total_ngn];
            app(BalanceSettlementService::class)->adjustNgnBalance((int)$locked->user_id, (float)$locked->total_ngn, 'gift_card.purchase_refund', $locked->reference);
            $locked->status = 'rejected';
            $locked->admin_note = $data['admin_note'];
            $locked->reviewed_by = $request->user()->id;
            $locked->processed_at = now();
            $locked->save();
            AdminAuditLog::create(['admin_id' => $request->user()->id, 'action' => 'gift_card_order.refunded', 'subject_type' => GiftCardOrder::class, 'subject_id' => $locked->id, 'before' => $before, 'after' => ['status' => 'rejected', 'refund_ngn' => $locked->total_ngn, 'reason' => $data['admin_note']], 'ip_address' => $request->ip(), 'user_agent' => substr((string)$request->userAgent(), 0, 1000)]);
            Notification::createForUser($locked->user_id, 'warning', 'Gift card purchase refunded', 'Your ' . $locked->card_name . ' request could not be fulfilled. ₦' . number_format((float)$locked->total_ngn, 2) . ' was returned to your Naira wallet.', ['type' => 'gift_card_order_refund', 'order_id' => $locked->id]);
        });
        return back()->with('success', 'Order rejected and the full Naira amount was refunded.');
    }
}
