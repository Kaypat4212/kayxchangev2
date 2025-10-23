@extends('adminnavlayout')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Notification Management</h1>
                    <p class="text-muted mb-0">Send and manage system notifications</p>
                </div>
                <div class="d-flex gap-2">
                    <button id="createNotificationBtn" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Create Notification
                    </button>
                    <button id="refreshBtn" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['total'] }}</h4>
                            <p class="card-text">Total Notifications</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-bell-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['unread'] }}</h4>
                            <p class="card-text">Unread</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-envelope-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['broadcast'] }}</h4>
                            <p class="card-text">Broadcast</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-broadcast fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $stats['today'] }}</h4>
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

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="typeFilter" class="form-label">Type</label>
                            <select id="typeFilter" class="form-select">
                                <option value="all">All Types</option>
                                <option value="info">Info</option>
                                <option value="success">Success</option>
                                <option value="warning">Warning</option>
                                <option value="error">Error</option>
                                <option value="trade_update">Trade Update</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select id="statusFilter" class="form-select">
                                <option value="all">All Status</option>
                                <option value="unread">Unread</option>
                                <option value="read">Read</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="dateFilter" class="form-label">Date Range</label>
                            <input type="date" id="dateFilter" class="form-control">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button id="applyFilters" class="btn btn-outline-primary me-2">Apply</button>
                            <button id="clearFilters" class="btn btn-outline-secondary">Clear</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Notifications</h5>
                    <div class="d-flex gap-2">
                        <button id="selectAllBtn" class="btn btn-sm btn-outline-primary">Select All</button>
                        <button id="bulkActionBtn" class="btn btn-sm btn-outline-warning" disabled>Bulk Action</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAllCheckbox">
                                    </th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Target</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="notificationsTableBody">
                                @foreach($notifications as $notification)
                                <tr data-id="{{ $notification->id }}">
                                    <td>
                                        <input type="checkbox" class="notification-checkbox" value="{{ $notification->id }}">
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $notification->type === 'success' ? 'success' : ($notification->type === 'error' ? 'danger' : ($notification->type === 'warning' ? 'warning' : 'info')) }}">
                                            {{ ucfirst($notification->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $notification->title }}</td>
                                    <td>{{ Str::limit($notification->message, 50) }}</td>
                                    <td>
                                        @if($notification->is_broadcast)
                                            <span class="badge bg-primary">Broadcast</span>
                                        @else
                                            <span class="text-muted">{{ $notification->user->name ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notification->is_read)
                                            <span class="badge bg-success">Read</span>
                                        @else
                                            <span class="badge bg-warning">Unread</span>
                                        @endif
                                    </td>
                                    <td>{{ $notification->created_at->diffForHumans() }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary view-notification" data-id="{{ $notification->id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning edit-notification" data-id="{{ $notification->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger delete-notification" data-id="{{ $notification->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Create Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="notificationForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label">Type *</label>
                            <select id="type" name="type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="info">Info</option>
                                <option value="success">Success</option>
                                <option value="warning">Warning</option>
                                <option value="error">Error</option>
                                <option value="trade_update">Trade Update</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="target_type" class="form-label">Target Type *</label>
                            <select id="target_type" name="target_type" class="form-select" required>
                                <option value="">Select Target</option>
                                <option value="broadcast">Broadcast (All Users)</option>
                                <option value="user">Specific User</option>
                            </select>
                        </div>
                        <div class="col-12" id="userSelectContainer" style="display: none;">
                            <label for="user_id" class="form-label">Select User</label>
                            <select id="user_id" name="user_id" class="form-select">
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label for="message" class="form-label">Message *</label>
                            <textarea id="message" name="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="expires_at" class="form-label">Expires At</label>
                            <input type="datetime-local" id="expires_at" name="expires_at" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="priority" class="form-label">Priority</label>
                            <select id="priority" name="priority" class="form-select">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Create Notification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Notification Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    
    // Form elements
    const form = document.getElementById('notificationForm');
    const targetTypeSelect = document.getElementById('target_type');
    const userSelectContainer = document.getElementById('userSelectContainer');
    const userSelect = document.getElementById('user_id');
    
    // Event listeners
    document.getElementById('createNotificationBtn').addEventListener('click', function() {
        document.getElementById('modalTitle').textContent = 'Create Notification';
        document.getElementById('submitBtn').textContent = 'Create Notification';
        form.reset();
        form.action = '{{ route("admin.notifications.store") }}';
        notificationModal.show();
    });
    
    targetTypeSelect.addEventListener('change', function() {
        if (this.value === 'user') {
            userSelectContainer.style.display = 'block';
            userSelect.required = true;
        } else {
            userSelectContainer.style.display = 'none';
            userSelect.required = false;
        }
    });
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Add additional data
        if (data.priority) {
            data.data = JSON.stringify({ priority: data.priority });
        }
        
        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notificationModal.hide();
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Something went wrong'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating the notification');
        });
    });
    
    // View notification
    document.querySelectorAll('.view-notification').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            // Load notification details and show modal
            viewModal.show();
        });
    });
    
    // Delete notification
    document.querySelectorAll('.delete-notification').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this notification?')) {
                const id = this.dataset.id;
                fetch(`/admin/notifications/${id}/delete`, {
                    method: 'DELETE',
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
    });
    
    // Select all functionality
    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.notification-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionButton();
    });
    
    document.querySelectorAll('.notification-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionButton);
    });
    
    function updateBulkActionButton() {
        const checkedBoxes = document.querySelectorAll('.notification-checkbox:checked');
        const bulkActionBtn = document.getElementById('bulkActionBtn');
        bulkActionBtn.disabled = checkedBoxes.length === 0;
    }
});
</script>
@endpush
