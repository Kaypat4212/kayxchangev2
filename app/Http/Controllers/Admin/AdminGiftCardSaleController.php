<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\GiftCardSaleOrder;
use App\Models\Notification;
use App\Services\BalanceSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminGiftCardSaleController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');
        $orders = GiftCardSaleOrder::with('user')->when($status, fn ($q) => $q->where('status', $status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim((string) $request->query('search'));
                $q->where(fn ($s) => $s->where('reference', 'like', "%{$term}%")
                    ->orWhere('card_name', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%")));
            })->latest()->paginate(20)->withQueryString();

        return view('admin.gift-card-sales.index', compact('orders', 'status'));
    }

    public function image(GiftCardSaleOrder $order)
    {
        abort_unless($order->image_path && Storage::disk('local')->exists($order->image_path), 404);
        return Storage::disk('local')->response($order->image_path, 'gift-card-proof-' . $order->reference);
    }

    public function complete(Request $request, GiftCardSaleOrder $order)
    {
        $data = $request->validate([
            'approved_ngn' => ['required', 'numeric', 'min:0.01', 'max:1000000000'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $order, $data): void {
            $locked = GiftCardSaleOrder::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->status !== 'pending') throw ValidationException::withMessages(['order' => 'Only pending gift card sales can be approved.']);
            $before = ['status' => $locked->status, 'estimated_ngn' => $locked->estimated_ngn];
            $locked->approved_ngn = round((float) $data['approved_ngn'], 2);
            $locked->admin_note = $data['admin_note'] ?? null;
            $locked->reviewed_by = $request->user()->id;
            $locked->status = 'completed';
            $locked->processed_at = now();
            $locked->save();
            app(BalanceSettlementService::class)->adjustNgnBalance((int) $locked->user_id, (float) $locked->approved_ngn, 'gift_card.sale_credit', $locked->reference);
            AdminAuditLog::create([
                'admin_id' => $request->user()->id, 'action' => 'gift_card_sale.completed',
                'subject_type' => GiftCardSaleOrder::class, 'subject_id' => $locked->id,
                'before' => $before, 'after' => ['status' => 'completed', 'credited_ngn' => $locked->approved_ngn, 'reference' => $locked->reference],
                'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
            Notification::createForUser($locked->user_id, 'success', 'Gift card sale completed', '₦' . number_format((float) $locked->approved_ngn, 2) . ' has been credited to your Naira wallet for ' . $locked->reference . '.', ['type' => 'gift_card_sale', 'order_id' => $locked->id]);
        });

        return back()->with('success', 'Gift card sale approved and the Naira wallet credited.');
    }

    public function reject(Request $request, GiftCardSaleOrder $order)
    {
        $data = $request->validate(['admin_note' => ['required', 'string', 'min:5', 'max:1000']]);
        DB::transaction(function () use ($request, $order, $data): void {
            $locked = GiftCardSaleOrder::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->status !== 'pending') throw ValidationException::withMessages(['order' => 'Only pending gift card sales can be rejected.']);
            $locked->status = 'rejected';
            $locked->admin_note = $data['admin_note'];
            $locked->reviewed_by = $request->user()->id;
            $locked->processed_at = now();
            $locked->save();
            AdminAuditLog::create([
                'admin_id' => $request->user()->id, 'action' => 'gift_card_sale.rejected',
                'subject_type' => GiftCardSaleOrder::class, 'subject_id' => $locked->id,
                'before' => ['status' => 'pending'], 'after' => ['status' => 'rejected', 'reason' => $data['admin_note'], 'reference' => $locked->reference],
                'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
            Notification::createForUser($locked->user_id, 'warning', 'Gift card sale declined', 'Your gift card sale ' . $locked->reference . ' was declined. Please review the note in your gift card activity.', ['type' => 'gift_card_sale', 'order_id' => $locked->id]);
        });
        return back()->with('success', 'Gift card sale declined. No wallet balance was changed.');
    }
}
