<?php $__env->startPush('styles'); ?>
<style>
    :root {
        --nx-green: #00cc00;
        --nx-bg: #081108;
        --nx-panel: rgba(255, 255, 255, 0.04);
        --nx-panel-border: rgba(255, 255, 255, 0.08);
        --nx-text: #e9f5ea;
        --nx-muted: rgba(233, 245, 234, 0.58);
    }

    .nx-wrap {
        position: relative;
        isolation: isolate;
    }

    .nx-wrap::before,
    .nx-wrap::after {
        content: "";
        position: absolute;
        z-index: -1;
        filter: blur(48px);
        opacity: 0.55;
        pointer-events: none;
    }

    .nx-wrap::before {
        width: 280px;
        height: 280px;
        top: -30px;
        right: 0;
        background: radial-gradient(circle, rgba(0, 204, 0, 0.35) 0%, rgba(0, 204, 0, 0) 70%);
        animation: nxFloat 9s ease-in-out infinite;
    }

    .nx-wrap::after {
        width: 260px;
        height: 260px;
        bottom: 40px;
        left: -20px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, rgba(56, 189, 248, 0) 72%);
        animation: nxFloat 11s ease-in-out infinite reverse;
    }

    .nx-hero {
        background: linear-gradient(140deg, rgba(0, 204, 0, 0.16), rgba(30, 41, 59, 0.4));
        border: 1px solid rgba(0, 204, 0, 0.24);
        border-radius: 22px;
        padding: 1.1rem 1.2rem;
        backdrop-filter: blur(8px);
    }

    .nx-title {
        color: var(--nx-text);
        font-size: 1.55rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }

    .nx-title i {
        color: var(--nx-green);
    }

    .nx-sub {
        color: var(--nx-muted);
        margin-top: 0.22rem;
        margin-bottom: 0;
        font-size: 0.88rem;
    }

    .nx-btn {
        border-radius: 999px;
        font-weight: 600;
        padding: 0.45rem 0.9rem;
        transition: all 0.2s ease;
    }

    .nx-btn:hover {
        transform: translateY(-1px);
    }

    .nx-stat {
        background: var(--nx-panel);
        border: 1px solid var(--nx-panel-border);
        border-radius: 18px;
        padding: 1rem;
        color: var(--nx-text);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.16);
        transition: transform 0.2s ease, border-color 0.2s ease;
        min-height: 118px;
    }

    .nx-stat:hover {
        transform: translateY(-2px);
        border-color: rgba(0, 204, 0, 0.3);
    }

    .nx-stat-label {
        font-size: 0.78rem;
        color: var(--nx-muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.2rem;
    }

    .nx-stat-value {
        font-size: 1.55rem;
        line-height: 1;
        font-weight: 700;
    }

    .nx-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        border: 1px solid transparent;
    }

    .nx-stat-icon.a { background: rgba(59, 130, 246, 0.14); color: #60a5fa; border-color: rgba(96, 165, 250, 0.3); }
    .nx-stat-icon.b { background: rgba(245, 158, 11, 0.14); color: #fbbf24; border-color: rgba(251, 191, 36, 0.3); }
    .nx-stat-icon.c { background: rgba(20, 184, 166, 0.14); color: #2dd4bf; border-color: rgba(45, 212, 191, 0.3); }

    .nx-list-card {
        background: rgba(6, 15, 8, 0.76);
        border: 1px solid var(--nx-panel-border);
        border-radius: 20px;
        overflow: hidden;
    }

    .nx-list-head {
        padding: 0.9rem 1.1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        font-size: 0.84rem;
        color: var(--nx-muted);
        font-weight: 600;
        letter-spacing: 0.04em;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .nx-list-head i {
        color: var(--nx-green);
    }

    .nx-list {
        padding: 0.75rem;
        display: grid;
        gap: 0.55rem;
    }

    .nx-item {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 16px;
        padding: 0.75rem 0.8rem;
        display: flex;
        gap: 0.7rem;
        cursor: pointer;
        transition: all 0.2s ease;
        animation: nxIn 0.45s ease both;
        animation-delay: calc(var(--i, 0) * 55ms);
    }

    .nx-item:hover {
        border-color: rgba(0, 204, 0, 0.24);
        transform: translateY(-1px);
    }

    .nx-item.nx-unread {
        background: linear-gradient(140deg, rgba(0, 204, 0, 0.09), rgba(255, 255, 255, 0.03));
        border-color: rgba(0, 204, 0, 0.22);
        box-shadow: inset 0 0 0 1px rgba(0, 204, 0, 0.14);
    }

    .nx-bubble-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .nx-main {
        min-width: 0;
        flex: 1;
    }

    .nx-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.55rem;
    }

    .nx-h {
        margin: 0;
        color: var(--nx-text);
        font-size: 0.92rem;
        font-weight: 600;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nx-unread .nx-h {
        font-weight: 700;
    }

    .nx-msg {
        margin: 0.22rem 0 0;
        color: var(--nx-muted);
        font-size: 0.8rem;
        line-height: 1.36;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .nx-meta {
        margin-top: 0.42rem;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.35rem;
        color: rgba(233, 245, 234, 0.42);
        font-size: 0.71rem;
    }

    .nx-pill {
        padding: 0.14rem 0.44rem;
        border-radius: 999px;
        font-size: 0.66rem;
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.15);
        background: rgba(255, 255, 255, 0.05);
    }

    .nx-pill.sys { background: rgba(59, 130, 246, 0.12); color: #60a5fa; border-color: rgba(96, 165, 250, 0.25); }
    .nx-pill.exp { background: rgba(45, 212, 191, 0.1); color: #2dd4bf; border-color: rgba(45, 212, 191, 0.25); }

    .nx-actions {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        margin-left: 0.35rem;
        flex-shrink: 0;
    }

    .nx-icon-btn {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(255, 255, 255, 0.04);
        color: rgba(233, 245, 234, 0.72);
        transition: all 0.18s ease;
    }

    .nx-icon-btn:hover {
        transform: translateY(-1px);
        color: #fff;
        border-color: rgba(0, 204, 0, 0.35);
    }

    .nx-icon-btn.read:hover {
        color: #22c55e;
    }

    .nx-icon-btn.del:hover {
        color: #f87171;
        border-color: rgba(248, 113, 113, 0.35);
    }

    .nx-empty {
        text-align: center;
        padding: 3.5rem 1rem;
        color: var(--nx-muted);
    }

    .nx-empty i {
        font-size: 2.4rem;
        color: rgba(255, 255, 255, 0.26);
    }

    .nx-empty h5 {
        margin-top: 0.7rem;
        color: var(--nx-text);
    }

    .nx-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.55rem;
        margin-top: 0.5rem;
    }

    .nx-detail-box {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.11);
        border-radius: 10px;
        padding: 0.52rem 0.62rem;
    }

    .nx-detail-label {
        font-size: 0.66rem;
        color: rgba(233, 245, 234, 0.52);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.14rem;
    }

    .nx-detail-val {
        font-size: 0.78rem;
        color: rgba(233, 245, 234, 0.9);
        word-break: break-word;
    }

    .nx-context-wrap {
        margin-top: 0.55rem;
        padding: 0.62rem;
        border-radius: 10px;
        background: rgba(0, 204, 0, 0.07);
        border: 1px solid rgba(0, 204, 0, 0.2);
    }

    .nx-context-title {
        font-size: 0.7rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #7ee787;
        margin-bottom: 0.42rem;
    }

    .nx-context-item {
        display: flex;
        justify-content: space-between;
        gap: 0.7rem;
        border-top: 1px dashed rgba(255, 255, 255, 0.12);
        padding: 0.28rem 0;
        font-size: 0.76rem;
    }

    .nx-context-item:first-of-type {
        border-top: none;
        padding-top: 0;
    }

    .nx-context-k {
        color: rgba(233, 245, 234, 0.62);
    }

    .nx-context-v {
        color: rgba(233, 245, 234, 0.92);
        text-align: right;
        word-break: break-word;
    }

    .nx-actions-row {
        margin-top: 0.62rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.38rem;
    }

    .nx-link-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.32rem;
        font-size: 0.72rem;
        padding: 0.24rem 0.5rem;
        border-radius: 999px;
        border: 1px solid rgba(96, 165, 250, 0.35);
        color: #93c5fd;
        background: rgba(96, 165, 250, 0.1);
        text-decoration: none;
    }

    .nx-link-chip:hover {
        color: #dbeafe;
        border-color: rgba(147, 197, 253, 0.55);
    }

    .nx-modal .modal-content {
        background: rgba(9, 18, 11, 0.95);
        color: var(--nx-text);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 18px;
        backdrop-filter: blur(12px);
    }

    .nx-modal .modal-header,
    .nx-modal .modal-footer {
        border-color: rgba(255, 255, 255, 0.09);
    }

    .nx-modal .btn-close {
        filter: invert(1);
        opacity: 0.8;
    }

    @keyframes nxIn {
        from { opacity: 0; transform: translateY(10px) scale(0.99); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes nxFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @media (max-width: 767.98px) {
        .nx-title { font-size: 1.35rem; }
        .nx-hero { padding: 1rem; }
        .nx-actions { margin-left: 0; }
        .nx-detail-grid { grid-template-columns: 1fr; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4 nx-wrap">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 nx-hero">
                <div>
                    <h1 class="nx-title"><i class="bi bi-bell-fill"></i>Notifications</h1>
                    <p class="nx-sub">Your latest alerts in a clean, simple feed.</p>
                </div>
                <div class="d-flex gap-2">
                    <button id="markAllReadBtn" class="btn btn-outline-success nx-btn">
                        <i class="bi bi-check-all me-1"></i>Mark All Read
                    </button>
                    <button id="refreshBtn" class="btn btn-outline-light nx-btn">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="nx-stat">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="nx-stat-label">Total Notifications</div>
                            <div class="nx-stat-value" id="totalNotifications"><?php echo e($notifications->total()); ?></div>
                        </div>
                    </div>
                    <span class="nx-stat-icon a"><i class="bi bi-bell-fill"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="nx-stat">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="nx-stat-label">Unread</div>
                            <div class="nx-stat-value" id="unreadCount"><?php echo e($notifications->where('is_read', false)->count()); ?></div>
                        </div>
                    </div>
                    <span class="nx-stat-icon b"><i class="bi bi-envelope-fill"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="nx-stat">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="nx-stat-label">Today</div>
                            <div class="nx-stat-value" id="todayCount"><?php echo e($notifications->where('created_at', '>=', today())->count()); ?></div>
                        </div>
                    </div>
                    <span class="nx-stat-icon c"><i class="bi bi-calendar-day"></i></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            <div class="nx-list-card">
                <div class="nx-list-head"><i class="bi bi-chat-left-text-fill"></i>Latest Messages & Alerts</div>
                <div class="card-body p-0">
                    <?php if($notifications->count() > 0): ?>
                        <div class="nx-list">
                            <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isUnread = !$notification->is_read;
                                $iconColor = match($notification->type) {
                                    'success' => '#22c55e',
                                    'warning' => '#f59e0b',
                                    'error' => '#ef4444',
                                    'trade_update' => '#a855f7',
                                    default => '#60a5fa',
                                };
                            ?>
                            <div class="nx-item notification-item <?php echo e($isUnread ? 'nx-unread' : ''); ?>"
                                 style="--i: <?php echo e($loop->index); ?>"
                                 data-id="<?php echo e($notification->id); ?>"
                                 data-read="<?php echo e($notification->is_read ? 'true' : 'false'); ?>"
                                 data-title="<?php echo e(e($notification->title)); ?>"
                                 data-message="<?php echo e(e($notification->message)); ?>"
                                 data-time="<?php echo e(e($notification->time_ago)); ?>"
                                 data-system="<?php echo e($notification->is_broadcast ? '1' : '0'); ?>"
                                 data-expires="<?php echo e($notification->expires_at && $notification->expires_at->isFuture() ? e('Expires '.$notification->expires_at->diffForHumans()) : ''); ?>">
                                <div class="nx-bubble-icon" style="color: <?php echo e($iconColor); ?>">
                                    <i class="bi <?php echo e($notification->icon); ?>"></i>
                                </div>
                                <div class="nx-main">
                                    <div class="nx-top">
                                        <h6 class="nx-h"><?php echo e($notification->title); ?></h6>
                                        <div class="nx-actions">
                                            <?php if(!$notification->is_read): ?>
                                            <button class="nx-icon-btn read mark-read" data-id="<?php echo e($notification->id); ?>" title="Mark as read">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button class="nx-icon-btn del delete-notification" data-id="<?php echo e($notification->id); ?>" title="Delete notification">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="nx-msg"><?php echo e(\Illuminate\Support\Str::limit($notification->message, 110)); ?></p>
                                    <div class="nx-meta">
                                        <span><?php echo e($notification->time_ago); ?></span>
                                        <?php if($notification->is_broadcast): ?>
                                            <span class="nx-pill sys">System</span>
                                        <?php endif; ?>
                                        <?php if($notification->expires_at && $notification->expires_at->isFuture()): ?>
                                            <span class="nx-pill exp">Expires <?php echo e($notification->expires_at->diffForHumans()); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="nx-empty">
                            <i class="bi bi-bell-slash"></i>
                            <h5>No notifications yet</h5>
                            <p>When activity happens, your feed will pop here.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if($notifications->hasPages()): ?>
                <div class="card-footer" style="background:rgba(255,255,255,0.02);border-top:1px solid rgba(255,255,255,0.08)">
                    <?php echo e($notifications->links()); ?>

                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Notification Detail Modal -->
<div class="modal fade nx-modal" id="notificationModal" tabindex="-1">
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

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const markAllUrl = <?php echo json_encode(route('notifications.mark-all-read'), 15, 512) ?>;
    const unreadCountUrl = <?php echo json_encode(route('notifications.unread-count'), 15, 512) ?>;
    const notifBaseUrl = <?php echo json_encode(url('/notifications'), 15, 512) ?>;
    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const markReadModalBtn = document.getElementById('markReadModalBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    
    // Mark all as read
    document.getElementById('markAllReadBtn').addEventListener('click', function() {
        if (confirm('Mark all notifications as read?')) {
            fetch(markAllUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
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

    refreshBtn.addEventListener('click', function() {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Refreshing';
        setTimeout(() => window.location.reload(), 260);
    });
    
    // View notification details
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            const id = this.dataset.id;
            const isRead = this.dataset.read === 'true';

            fetch(`${notifBaseUrl}/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.ok ? r.json() : Promise.reject(r.status))
            .then(detail => {
                modalTitle.textContent = detail.title || this.dataset.title || 'Notification';
                modalBody.innerHTML = buildDetailHtml(detail);
                markReadModalBtn.style.display = (detail.is_read || isRead) ? 'none' : 'inline-block';
                markReadModalBtn.onclick = () => markAsRead(id);
                modal.show();
            })
            .catch(() => {
                const fallback = {
                    title: this.dataset.title || 'Notification',
                    message: this.dataset.message || '',
                    time_ago: this.dataset.time || '',
                    is_broadcast: this.dataset.system === '1',
                    expires_at: this.dataset.expires || '',
                    is_read: isRead,
                    source: (this.dataset.system === '1' ? 'System Broadcast' : 'Account Alert'),
                    data: {}
                };
                modalTitle.textContent = fallback.title;
                modalBody.innerHTML = buildDetailHtml(fallback);
                markReadModalBtn.style.display = isRead ? 'none' : 'inline-block';
                markReadModalBtn.onclick = () => markAsRead(id);
                modal.show();
            });
        });
    });
    
    function markAsRead(id) {
        fetch(`${notifBaseUrl}/${id}/mark-read`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`[data-id="${id}"]`);
                item.classList.remove('nx-unread');
                const markBtn = item.querySelector('.mark-read');
                if (markBtn) markBtn.remove();
                item.dataset.read = 'true';
                updateUnreadCount();
            }
        });
    }
    
    function deleteNotification(id) {
        fetch(`${notifBaseUrl}/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
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

    function buildDetailHtml(detail) {
        const message = escapeHtml(truncate(detail.message || '', 200));
        const type = (detail.type || 'info').replace('_', ' ');
        const source = escapeHtml(detail.source || (detail.is_broadcast ? 'System Broadcast' : 'Account Alert'));
        const created = detail.created_at ? new Date(detail.created_at).toLocaleString() : (detail.time_ago || '');

        return `
            <div style="display:flex;flex-direction:column;gap:.52rem">
                <div style="font-size:.95rem;line-height:1.6;color:rgba(233,245,234,.92)">${message}</div>
                <div class="nx-detail-grid">
                    <div class="nx-detail-box"><div class="nx-detail-label">Type</div><div class="nx-detail-val">${escapeHtml(type)}</div></div>
                    <div class="nx-detail-box"><div class="nx-detail-label">Source</div><div class="nx-detail-val">${source}</div></div>
                    <div class="nx-detail-box"><div class="nx-detail-label">Created</div><div class="nx-detail-val">${escapeHtml(created)}</div></div>
                    <div class="nx-detail-box"><div class="nx-detail-label">Status</div><div class="nx-detail-val">${detail.is_read ? 'Read' : 'Unread'}</div></div>
                </div>
            </div>`;
    }

    function truncate(text, max = 200) {
        const s = String(text || '');
        return s.length > max ? s.slice(0, max - 1) + '…' : s;
    }

    function formatValue(v) {
        if (v === null || v === undefined) return '—';
        if (typeof v === 'object') return JSON.stringify(v);
        return String(v);
    }

    function buildQuickLinks(dataObj) {
        const links = [];
        if (dataObj.reference) {
            links.push({ label: `Ref ${dataObj.reference}`, url: `${notifBaseUrl}?ref=${encodeURIComponent(dataObj.reference)}`, icon: 'bi-hash' });
        }
        if (dataObj.trade_id || dataObj.transaction_id) {
            links.push({ label: 'View Transactions', url: '/transactions/history', icon: 'bi-clock-history' });
        }
        if (dataObj.action_url) {
            links.push({ label: 'Open Related Page', url: dataObj.action_url, icon: 'bi-box-arrow-up-right' });
        }
        return links;
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        fetch(unreadCountUrl)
        .then(response => response.json())
        .then(data => {
            document.getElementById('unreadCount').textContent = data.unread_count;
        });
    }, 30000);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\notifications\index.blade.php ENDPATH**/ ?>