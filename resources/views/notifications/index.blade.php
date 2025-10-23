@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Notifications</h1>
                    <p class="text-muted mb-0">Stay updated with your account activities</p>
                </div>
                <div class="d-flex gap-2">
                    <button id="markAllReadBtn" class="btn btn-outline-primary">
                        <i class="bi bi-check-all me-1"></i>Mark All Read
                    </button>
                    <button id="refreshBtn" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="totalNotifications">{{ $notifications->total() }}</h4>
                            <p class="card-text">Total Notifications</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-bell-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="unreadCount">{{ $notifications->where('is_read', false)->count() }}</h4>
                            <p class="card-text">Unread</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-envelope-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="todayCount">{{ $notifications->where('created_at', '>=', today())->count() }}</h4>
                            <p class="card-text">Today</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar-day fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                            <div class="list-group-item list-group-item-action notification-item {{ $notification->is_read ? '' : 'bg-light' }}" 
                                 data-id="{{ $notification->id }}" data-read="{{ $notification->is_read ? 'true' : 'false' }}">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="bi {{ $notification->icon }} fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <h6 class="mb-1 {{ $notification->is_read ? 'text-muted' : 'fw-bold' }}">
                                                    {{ $notification->title }}
                                                </h6>
                                                <div class="d-flex gap-1">
                                                    @if(!$notification->is_read)
                                                    <button class="btn btn-sm btn-outline-primary mark-read" data-id="{{ $notification->id }}">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                    @endif
                                                    <button class="btn btn-sm btn-outline-danger delete-notification" data-id="{{ $notification->id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <p class="mb-1 text-muted">{{ $notification->message }}</p>
                                            <small class="text-muted">
                                                {{ $notification->time_ago }}
                                                @if($notification->is_broadcast)
                                                    <span class="badge bg-primary ms-2">System</span>
                                                @endif
                                                @if($notification->expires_at && $notification->expires_at->isFuture())
                                                    <span class="badge bg-info ms-2">Expires {{ $notification->expires_at->diffForHumans() }}</span>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash fs-1 text-muted"></i>
                            <h5 class="mt-3 text-muted">No notifications yet</h5>
                            <p class="text-muted">You'll see your notifications here when they arrive.</p>
                        </div>
                    @endif
                </div>
                @if($notifications->hasPages())
                <div class="card-footer">
                    {{ $notifications->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Notification Detail Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Notification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="markReadModalBtn" style="display: none;">Mark as Read</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const markReadModalBtn = document.getElementById('markReadModalBtn');
    
    // Mark all as read
    document.getElementById('markAllReadBtn').addEventListener('click', function() {
        if (confirm('Mark all notifications as read?')) {
            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    });
    
    // Mark individual notification as read
    document.querySelectorAll('.mark-read').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            markAsRead(id);
        });
    });
    
    // Delete notification
    document.querySelectorAll('.delete-notification').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (confirm('Delete this notification?')) {
                const id = this.dataset.id;
                deleteNotification(id);
            }
        });
    });
    
    // View notification details
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            const id = this.dataset.id;
            const isRead = this.dataset.read === 'true';
            
            // Load notification details
            fetch(`/notifications/${id}`)
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
                modalTitle.textContent = 'Notification Details';
                markReadModalBtn.style.display = isRead ? 'none' : 'inline-block';
                markReadModalBtn.onclick = () => markAsRead(id);
                modal.show();
            });
        });
    });
    
    function markAsRead(id) {
        fetch(`/notifications/${id}/mark-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`[data-id="${id}"]`);
                item.classList.remove('bg-light');
                item.classList.add('text-muted');
                item.querySelector('h6').classList.remove('fw-bold');
                item.querySelector('h6').classList.add('text-muted');
                item.querySelector('.mark-read').remove();
                item.dataset.read = 'true';
                updateUnreadCount();
            }
        });
    }
    
    function deleteNotification(id) {
        fetch(`/notifications/${id}/delete`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`[data-id="${id}"]`).remove();
                updateUnreadCount();
            }
        });
    }
    
    function updateUnreadCount() {
        const unreadItems = document.querySelectorAll('[data-read="false"]');
        document.getElementById('unreadCount').textContent = unreadItems.length;
    }
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        fetch('/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            document.getElementById('unreadCount').textContent = data.unread_count;
        });
    }, 30000);
});
</script>
@endpush
