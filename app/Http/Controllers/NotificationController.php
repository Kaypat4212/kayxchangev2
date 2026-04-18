<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class NotificationController extends Controller
{
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
            ->findOrFail($id);

        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return view('notifications.show', compact('notification'));
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
        $user = Auth::user();
        $limit = $request->get('limit', 10);
        
        $notifications = Notification::forUser($user->id)
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $this->computeUnreadCount($user->id)
        ]);
    }
}