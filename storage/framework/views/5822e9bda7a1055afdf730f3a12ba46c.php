<!-- Notification Dropdown -->
<div class="dropdown">
    <button class="btn btn-outline-secondary position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell-fill"></i>
        <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
            0
        </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifications</span>
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-primary" id="markAllReadBtn">
                    <i class="bi bi-check-all"></i>
                </button>
                <a href="<?php echo e(url('/notifications')); ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </li>
        <li><hr class="dropdown-divider"></li>
        <div id="notificationList" class="notification-list">
            <li class="dropdown-item text-center text-muted py-3">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading notifications...</div>
            </li>
        </div>
        <li><hr class="dropdown-divider"></li>
        <li class="dropdown-item text-center">
            <a href="<?php echo e(url('/notifications')); ?>" class="text-decoration-none">View All Notifications</a>
        </li>
    </ul>
</div>

<style>
.notification-dropdown {
    width: 350px;
    max-height: 400px;
    overflow-y: auto;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #dee2e6;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e3f2fd;
    border-left: 3px solid #2196f3;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-content {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.notification-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 12px;
}

.notification-details {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    color: #212529;
}

.notification-message {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.notification-time {
    font-size: 0.75rem;
    color: #adb5bd;
}

.notification-actions {
    display: flex;
    gap: 0.25rem;
    margin-top: 0.5rem;
}

.notification-actions .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.getElementById('notificationDropdown');
    const badge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    
    let notifications = [];
    let isLoading = false;
    
    // Load notifications when dropdown is opened
    dropdown.addEventListener('show.bs.dropdown', function() {
        if (!isLoading) {
            loadNotifications();
        }
    });
    
    // Mark all as read
    markAllReadBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        markAllAsRead();
    });
    
    function loadNotifications() {
        isLoading = true;
        fetch('/api/notifications?limit=5')
        .then(response => response.json())
        .then(data => {
            notifications = data.notifications;
            updateNotificationList();
            updateBadge(data.unread_count);
            isLoading = false;
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            notificationList.innerHTML = '<li class="dropdown-item text-center text-muted py-3">Error loading notifications</li>';
            isLoading = false;
        });
    }
    
    function updateNotificationList() {
        if (notifications.length === 0) {
            notificationList.innerHTML = '<li class="dropdown-item text-center text-muted py-3">No notifications</li>';
            return;
        }
        
        const html = notifications.map(notification => `
            <li class="notification-item ${notification.is_read ? '' : 'unread'}" data-id="${notification.id}">
                <div class="notification-content">
                    <div class="notification-icon ${notification.icon}"></div>
                    <div class="notification-details">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.message}</div>
                        <div class="notification-time">${notification.time_ago}</div>
                        ${!notification.is_read ? `
                        <div class="notification-actions">
                            <button class="btn btn-sm btn-outline-primary mark-read" data-id="${notification.id}">
                                <i class="bi bi-check"></i> Mark Read
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-notification" data-id="${notification.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </li>
        `).join('');
        
        notificationList.innerHTML = html;
        
        // Add event listeners
        document.querySelectorAll('.mark-read').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                markAsRead(this.dataset.id);
            });
        });
        
        document.querySelectorAll('.delete-notification').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                deleteNotification(this.dataset.id);
            });
        });
        
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                if (!this.querySelector('.mark-read')) {
                    window.location.href = '<?php echo e(url("/notifications")); ?>';
                }
            });
        });
    }
    
    function updateBadge(count) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }
    
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
                loadNotifications(); // Reload to update the list
            }
        });
    }
    
    function deleteNotification(id) {
        if (confirm('Delete this notification?')) {
            fetch(`/notifications/${id}/delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications(); // Reload to update the list
                }
            });
        }
    }
    
    function markAllAsRead() {
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
                    loadNotifications(); // Reload to update the list
                }
            });
        }
    }
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        if (!isLoading) {
            loadNotifications();
        }
    }, 30000);
});
</script>
<?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/components/notification-dropdown.blade.php ENDPATH**/ ?>