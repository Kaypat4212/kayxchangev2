@extends('layouts.admin')

@section('title', 'Server Backup')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">🗄️ Server Backup</h4>
            <p class="text-muted small mb-0">Create, download, and manage full server backups</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#runBackupModal">
            <i class="bi bi-cloud-arrow-up me-1"></i> Run Backup Now
        </button>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Info Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary fs-4">
                        <i class="bi bi-database"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Backups</div>
                        <div class="fw-bold fs-4">{{ count($backups) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success fs-4">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Latest Backup</div>
                        <div class="fw-bold">
                            @if(count($backups) > 0)
                                {{ $backups[0]['date'] }}
                            @else
                                <span class="text-muted">None yet</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning fs-4">
                        <i class="bi bi-hdd"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Size</div>
                        <div class="fw-bold">
                            {{ array_sum(array_column($backups, 'size_mb')) }} MB
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- What gets backed up --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 fw-semibold">
            <i class="bi bi-info-circle me-1 text-primary"></i> What Gets Backed Up
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex gap-2 align-items-start">
                        <i class="bi bi-check-circle-fill text-success mt-1"></i>
                        <div>
                            <div class="fw-semibold">Full Database Dump</div>
                            <div class="text-muted small">All tables, data, views, and stored procedures via <code>mysqldump</code></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2 align-items-start">
                        <i class="bi bi-check-circle-fill text-success mt-1"></i>
                        <div>
                            <div class="fw-semibold">User Uploads &amp; Files</div>
                            <div class="text-muted small">Payment proofs, KYC documents, and all public storage</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2 align-items-start">
                        <i class="bi bi-check-circle-fill text-success mt-1"></i>
                        <div>
                            <div class="fw-semibold">Telegram Notification</div>
                            <div class="text-muted small">Backup file sent to admin Telegram (files under 50 MB)</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2 align-items-start">
                        <i class="bi bi-check-circle-fill text-success mt-1"></i>
                        <div>
                            <div class="fw-semibold">Email Notification</div>
                            <div class="text-muted small">Summary email sent to admin. Backups auto-pruned after 7 copies.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Backup list --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-archive me-1"></i> Backup Files</span>
            <span class="badge bg-secondary">{{ count($backups) }} files</span>
        </div>
        <div class="card-body p-0">
            @if(count($backups) === 0)
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No backups yet. Click <strong>Run Backup Now</strong> to create your first one.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Filename</th>
                                <th>Date</th>
                                <th>Size</th>
                                <th>Age</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $backup)
                            <tr>
                                <td>
                                    <i class="bi bi-file-zip me-1 text-warning"></i>
                                    <code>{{ $backup['filename'] }}</code>
                                </td>
                                <td class="text-muted small">{{ $backup['date'] }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $backup['size_mb'] }} MB</span>
                                </td>
                                <td>
                                    @if($backup['age_days'] == 0)
                                        <span class="badge bg-success">Today</span>
                                    @elseif($backup['age_days'] <= 2)
                                        <span class="badge bg-info text-dark">{{ $backup['age_days'] }}d ago</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $backup['age_days'] }}d ago</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.backup.download', $backup['filename']) }}"
                                       class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    <form method="POST" action="{{ route('admin.backup.delete', $backup['filename']) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this backup permanently?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Run Backup Modal --}}
<div class="modal fade" id="runBackupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">🗄️ Run Backup Now</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.backup.run') }}" id="backupForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">This will create a full backup of the database and uploaded files. This may take a minute.</p>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="notify" id="notifyCheck" value="1" checked>
                        <label class="form-check-label" for="notifyCheck">
                            <strong>Notify admin</strong> via Telegram &amp; Email after backup
                        </label>
                    </div>

                    <div class="alert alert-warning mb-0 small">
                        <i class="bi bi-clock me-1"></i>
                        Large sites may take 30–90 seconds. Do not close this page until you see a confirmation.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="backupBtn">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="backupSpinner"></span>
                        <i class="bi bi-play-circle me-1" id="backupIcon"></i> Start Backup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('backupForm').addEventListener('submit', function () {
    document.getElementById('backupSpinner').classList.remove('d-none');
    document.getElementById('backupIcon').classList.add('d-none');
    document.getElementById('backupBtn').disabled = true;
    document.getElementById('backupBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Running…';
});
</script>
@endpush
@endsection
