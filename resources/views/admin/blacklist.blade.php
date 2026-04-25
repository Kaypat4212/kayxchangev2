@extends('adminlayout')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#e4e8f0;">IP &amp; User Blacklist</h4>
            <p class="text-muted small mb-0">Block suspicious IPs or suspend users from trading</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        {{-- Add to blacklist --}}
        <div class="col-md-4">
            <div class="card h-100" style="background:#161b27;border:1px solid rgba(255,255,255,.08);border-radius:16px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:#e4e8f0;"><i class="bi bi-shield-x me-2 text-danger"></i>Add to Blacklist</h6>
                    <form method="POST" action="{{ route('admin.blacklist.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small text-muted">Type</label>
                            <select name="type" class="form-select form-select-sm" style="background:#0d1117;border-color:rgba(255,255,255,.1);color:#e4e8f0;" required>
                                <option value="ip">IP Address</option>
                                <option value="user">User ID</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Value (IP or User ID)</label>
                            <input type="text" name="value" class="form-control form-control-sm"
                                style="background:#0d1117;border-color:rgba(255,255,255,.1);color:#e4e8f0;"
                                placeholder="e.g. 192.168.1.1 or 42" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Reason (optional)</label>
                            <input type="text" name="reason" class="form-control form-control-sm"
                                style="background:#0d1117;border-color:rgba(255,255,255,.1);color:#e4e8f0;"
                                placeholder="e.g. Fraud attempt">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Expires At (leave blank = permanent)</label>
                            <input type="datetime-local" name="expires_at" class="form-control form-control-sm"
                                style="background:#0d1117;border-color:rgba(255,255,255,.1);color:#e4e8f0;">
                        </div>
                        <button type="submit" class="btn btn-sm btn-danger w-100">
                            <i class="bi bi-ban me-1"></i> Add to Blacklist
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- List --}}
        <div class="col-md-8">
            <div class="card" style="background:#161b27;border:1px solid rgba(255,255,255,.08);border-radius:16px;">
                <div class="card-body p-0">
                    {{-- Filters --}}
                    <div class="d-flex gap-2 p-3 border-bottom" style="border-color:rgba(255,255,255,.07) !important;">
                        <form method="GET" class="d-flex gap-2 flex-wrap w-100">
                            <select name="type" class="form-select form-select-sm w-auto"
                                style="background:#0d1117;border-color:rgba(255,255,255,.1);color:#e4e8f0;">
                                <option value="">All Types</option>
                                <option value="ip" {{ request('type')=='ip' ? 'selected' : '' }}>IP</option>
                                <option value="user" {{ request('type')=='user' ? 'selected' : '' }}>User</option>
                            </select>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search value..."
                                class="form-control form-control-sm w-auto"
                                style="background:#0d1117;border-color:rgba(255,255,255,.1);color:#e4e8f0;">
                            <button class="btn btn-sm btn-outline-secondary">Filter</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0" style="font-size:.83rem;">
                            <thead style="background:#0d1117;">
                                <tr>
                                    <th class="px-3 py-2 text-muted fw-semibold" style="font-size:.7rem;letter-spacing:.06em;text-transform:uppercase;">Type</th>
                                    <th class="px-3 py-2 text-muted fw-semibold" style="font-size:.7rem;letter-spacing:.06em;text-transform:uppercase;">Value</th>
                                    <th class="px-3 py-2 text-muted fw-semibold" style="font-size:.7rem;letter-spacing:.06em;text-transform:uppercase;">Reason</th>
                                    <th class="px-3 py-2 text-muted fw-semibold" style="font-size:.7rem;letter-spacing:.06em;text-transform:uppercase;">Expires</th>
                                    <th class="px-3 py-2 text-muted fw-semibold" style="font-size:.7rem;letter-spacing:.06em;text-transform:uppercase;">Added</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($blacklists as $item)
                                <tr>
                                    <td class="px-3 py-2">
                                        <span class="badge {{ $item->type==='ip' ? 'bg-warning text-dark' : 'bg-danger' }}">
                                            {{ strtoupper($item->type) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2" style="font-family:monospace;">
                                        {{ $item->value }}
                                        @if($item->type==='user' && isset($users[$item->value]))
                                            <div style="font-family:sans-serif;font-size:.72rem;color:#7a8599;">
                                                {{ $users[$item->value]->name }} &bull; {{ $users[$item->value]->email }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2" style="color:#7a8599;">{{ $item->reason ?? '—' }}</td>
                                    <td class="px-3 py-2">
                                        @if($item->expires_at)
                                            <span class="{{ $item->expires_at->isPast() ? 'text-muted' : 'text-warning' }}">
                                                {{ $item->expires_at->format('d M Y H:i') }}
                                                @if($item->expires_at->isPast()) <small>(expired)</small> @endif
                                            </span>
                                        @else
                                            <span class="text-danger">Permanent</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2" style="color:#7a8599;">{{ $item->created_at->format('d M Y') }}</td>
                                    <td class="px-3 py-2">
                                        <form method="POST" action="{{ route('admin.blacklist.destroy', $item->id) }}" class="d-inline"
                                              onsubmit="return confirm('Remove from blacklist?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-success py-0 px-2" style="font-size:.72rem;">
                                                <i class="bi bi-check-lg"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No blacklisted entries.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($blacklists->hasPages())
                    <div class="p-3">
                        {{ $blacklists->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
