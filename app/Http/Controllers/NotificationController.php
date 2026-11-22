<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\ExpoPushNotificationService;

class NotificationController extends Controller
{
    public function __construct(private ExpoPushNotificationService $expoPush) {}

    // User notification methods
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 15);
        
        $notifications = Notification::forUser($user->id)
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        if ($request->expectsJson()) {
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $this->computeUnreadCount($user->id)
            ]);
        }

        return view('notifications.index', compact('notifications'));
    }

    public function show($id)
    {
        $notification = Notification::forUser(Auth::id())
            ->with('admin:id,name,email')
            ->findOrFail($id);

        if (!$notification->is_read) {
            $notification->markAsRead();
        }
        $notificationData = is_array($notification->data) ? $notification->data : [];
        $actionUrl = route('account.activity.index');
        $actionLabel = 'View account activity';
        $supportUrl = route('support.cases.index');
        if (!empty($notificationData['support_case_id']) && \App\Models\SupportCase::query()
            ->where('id', (int)$notificationData['support_case_id'])->where('user_id', Auth::id())->exists()) {
            $actionUrl = route('support.cases.show', (int)$notificationData['support_case_id']);
            $actionLabel = 'Open support case';
        } elseif (!empty($notificationData['deposit_id']) && \App\Models\CryptoDeposit::query()
            ->where('id', (int)$notificationData['deposit_id'])->where('user_id', Auth::id())->exists()) {
            $actionUrl = route('wallet.deposit.success', (int)$notificationData['deposit_id']);
            $actionLabel = 'View deposit status';
            $supportUrl = route('support.cases.deposit.create', (int)$notificationData['deposit_id']);
        } elseif (str_starts_with((string)($notificationData['type'] ?? ''), 'gift_card')) {
            $actionUrl = route('gift-cards.index');
            $actionLabel = 'View gift card activity';
        }

        if (request()->expectsJson() || request()->wantsJson()) {
            $data = $notificationData;

            return response()->json([
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'data' => $data,
                'is_read' => (bool) $notification->is_read,
                'is_broadcast' => (bool) $notification->is_broadcast,
                'time_ago' => $notification->time_ago,
                'created_at' => optional($notification->created_at)->toIso8601String(),
                'read_at' => optional($notification->read_at)->toIso8601String(),
                'expires_at' => optional($notification->expires_at)->toIso8601String(),
                'source' => $notification->is_broadcast
                    ? 'System Broadcast'
                    : ($notification->admin?->name ? 'Admin: ' . $notification->admin->name : 'Account Alert'),
                'action_url' => $actionUrl,
                'action_label' => $actionLabel,
                'support_url' => $supportUrl,
            ]);
        }

        return view('notifications.show', compact('notification', 'notificationData', 'actionUrl', 'actionLabel', 'supportUrl'));
    }

    public function markAsRead($id)
    {
        $notification = Notification::forUser(Auth::id())
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::forUser(Auth::id())
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json(['success' => true]);
    }

    public function delete($id)
    {
        $notification = Notification::forUser(Auth::id())
            ->findOrFail($id);

        $notification->delete();

        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $count = $this->computeUnreadCount(Auth::id());
        return response()->json(['unread_count' => $count]);
    }

    // Admin notification methods
    public function adminIndex(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $type = $request->get('type');
        $status = $request->get('status'); // 'all', 'read', 'unread'

        $query = Notification::with(['user', 'admin']);

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        if ($status === 'read') {
            $query->where('is_read', true);
        } elseif ($status === 'unread') {
            $query->where('is_read', false);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::unread()->count(),
            'broadcast' => Notification::broadcast()->count(),
            'today' => Notification::whereDate('created_at', today())->count()
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'notifications' => $notifications,
                'stats' => $stats
            ]);
        }

        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return view('admin.notifications.index', compact('notifications', 'stats', 'users'));
    }

    public function adminShow($id)
    {
        $notification = Notification::with(['user', 'admin'])->findOrFail($id);
        if (!$notification->is_read) $notification->markAsRead();

        $data = is_array($notification->data) ? $notification->data : [];
        $actionUrl = null;
        $actionLabel = null;
        $caseId = filter_var($data['support_case_id'] ?? null, FILTER_VALIDATE_INT);
        if ($caseId && \App\Models\SupportCase::whereKey($caseId)->exists()) {
            $actionUrl = route('admin.support-cases.index', ['case_id' => $caseId]) . '#support-case-' . $caseId;
            $actionLabel = 'Open case and reply';
        } elseif (($data['type'] ?? null) === 'crypto_withdrawal' || !empty($data['withdrawal_id'])) {
            $actionUrl = route('admin.crypto-withdrawals.index');
            $actionLabel = 'Review crypto withdrawal';
        } elseif (!empty($data['trade_type']) || !empty($data['reference'])) {
            $actionUrl = route('admin.trades');
            $actionLabel = 'Open trades and transactions';
        } else {
            $actionUrl = $this->trustedAdminNotificationUrl($data['url'] ?? null);
            if ($actionUrl) $actionLabel = 'Open related admin page';
        }

        return view('admin.notifications.show', compact('notification', 'data', 'actionUrl', 'actionLabel'));
    }

    private function trustedAdminNotificationUrl($candidate): ?string
    {
        if (!is_string($candidate) || trim($candidate) === '') return null;
        $target = parse_url(trim($candidate));
        if (!$target || isset($target['user']) || isset($target['pass'])) return null;
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        if (isset($target['host']) && strtolower($target['host']) !== strtolower((string) $appHost)) return null;
        $path = $target['path'] ?? '';
        if (!str_starts_with($path, '/admin/') || str_contains($path, '..')) return null;
        return $path . (isset($target['query']) ? '?' . $target['query'] : '') . (isset($target['fragment']) ? '#' . $target['fragment'] : '');
    }

    public function adminCreate()
    {
        $users = User::select('id', 'name', 'email')->get();
        return view('admin.notifications.create', compact('users'));
    }

    public function adminStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:info,success,warning,error,trade_update,system',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target_type' => 'required|in:user,broadcast',
            'user_id' => 'required_if:target_type,user|exists:users,id',
            'expires_at' => 'nullable|date|after:now',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'type' => $request->type,
            'title' => $request->title,
            'message' => $request->message,
            'data' => $request->data,
            'admin_id' => Auth::id(),
            'expires_at' => $request->expires_at ? Carbon::parse($request->expires_at) : null
        ];

        if ($request->target_type === 'user') {
            $data['user_id'] = $request->user_id;
            $data['is_broadcast'] = false;
            $notification = Notification::create($data);
        } else {
            $data['is_broadcast'] = true;
            $notification = Notification::create($data);

            $this->expoPush->sendBroadcast($notification->title, $notification->message, [
                'url' => route('notifications.index'),
                'notification_id' => $notification->id,
                'type' => $notification->type,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully',
            'notification' => $notification
        ]);
    }

    public function adminEdit($id)
    {
        $notification = Notification::findOrFail($id);
        $users = User::select('id', 'name', 'email')->get();
        
        return view('admin.notifications.edit', compact('notification', 'users'));
    }

    public function adminUpdate(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:info,success,warning,error,trade_update,system',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'expires_at' => 'nullable|date|after:now',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $notification->update([
            'type' => $request->type,
            'title' => $request->title,
            'message' => $request->message,
            'data' => $request->data,
            'expires_at' => $request->expires_at ? Carbon::parse($request->expires_at) : null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification updated successfully'
        ]);
    }

    public function adminDelete($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true]);
    }

    public function adminBulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:mark_read,mark_unread,delete',
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = Notification::whereIn('id', $request->notification_ids);

        switch ($request->action) {
            case 'mark_read':
                $query->update(['is_read' => true, 'read_at' => now()]);
                break;
            case 'mark_unread':
                $query->update(['is_read' => false, 'read_at' => null]);
                break;
            case 'delete':
                $query->delete();
                break;
        }

        return response()->json(['success' => true]);
    }

    // Helper methods
    private function computeUnreadCount($userId)
    {
        return Notification::forUser($userId)
            ->unread()
            ->notExpired()
            ->count();
    }

    // API methods for real-time updates
    public function apiIndex(Request $request)
    {
        try {
            $user = Auth::user();
            $limit = min((int) $request->get('limit', 10), 50);

            $notifications = Notification::forUser($user->id)
                ->notExpired()
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $this->computeUnreadCount($user->id)
            ]);
        } catch (\Exception $e) {
            \Log::error('Notification apiIndex error: ' . $e->getMessage());
            return response()->json(['notifications' => [], 'unread_count' => 0], 200);
        }
    }
}
