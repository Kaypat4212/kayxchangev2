@extends('adminnavlayout')

@push('styles')
<style>
:root{
    --kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
    --kx-danger:#ef4444;--kx-success:#4ade80;
}
body{background:var(--kx-dark);color:var(--kx-text);}
.kx-page-header{background:linear-gradient(135deg,#0a1628,#0d1f1a);border:1px solid var(--kx-border);border-radius:16px;padding:24px 28px;margin-bottom:24px;}
.kx-page-header h1{font-size:1.35rem;font-weight:700;color:#fff;margin:0 0 4px;}
.kx-page-header p{color:var(--kx-muted);font-size:.875rem;margin:0;}
.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1.25rem;}
.kx-card-title{font-size:.95rem;font-weight:700;color:var(--kx-text);margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;}
.stat-box{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:1.1rem 1.4rem;text-align:center;}
.stat-box .val{font-size:1.8rem;font-weight:800;color:var(--kx-green);}
.stat-box .lbl{font-size:.78rem;color:var(--kx-muted);margin-top:2px;}
.kx-table{width:100%;border-collapse:collapse;font-size:.875rem;}
.kx-table th{color:var(--kx-muted);font-weight:600;padding:.6rem .8rem;border-bottom:1px solid var(--kx-border);text-align:left;white-space:nowrap;}
.kx-table td{padding:.65rem .8rem;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle;}
.kx-table tr:last-child td{border-bottom:none;}
.badge-active{background:rgba(0,204,0,.15);color:#4ade80;font-size:.72rem;padding:.25em .65em;border-radius:20px;font-weight:600;}
.badge-unsub{background:rgba(239,68,68,.12);color:#f87171;font-size:.72rem;padding:.25em .65em;border-radius:20px;font-weight:600;}
.btn-del{background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.2);border-radius:6px;padding:.25rem .7rem;font-size:.8rem;cursor:pointer;}
.btn-del:hover{background:rgba(239,68,68,.25);}
.form-control,.form-select{background:rgba(0,0,0,.35);border:1px solid var(--kx-border);color:var(--kx-text);border-radius:8px;}
.form-control:focus,.form-select:focus{border-color:var(--kx-green);box-shadow:0 0 0 3px rgba(0,204,0,.15);background:rgba(0,0,0,.4);color:#fff;}
.form-select option{background:#0d1f0d;}
textarea.form-control{min-height:120px;}
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width:1100px">

    <div class="kx-page-header">
        <h1><i class="bi bi-envelope-heart-fill me-2" style="color:var(--kx-green)"></i>Newsletter Subscribers</h1>
        <p>Manage subscribers and send email campaigns to your audience.</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->has('campaign'))
    <div class="alert alert-danger rounded-3 mb-4">{{ $errors->first('campaign') }}</div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-4 col-md-2">
            <div class="stat-box">
                <div class="val">{{ $stats['total'] }}</div>
                <div class="lbl">Total</div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-box">
                <div class="val">{{ $stats['active'] }}</div>
                <div class="lbl">Active</div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-box">
                <div class="val">{{ $stats['today'] }}</div>
                <div class="lbl">Today</div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Subscriber list --}}
        <div class="col-lg-8">
            <div class="kx-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="kx-card-title mb-0"><i class="bi bi-people-fill" style="color:var(--kx-green)"></i> Subscribers</div>
                    <a href="{{ route('admin.newsletter.export') }}" class="btn btn-sm"
                       style="background:rgba(0,204,0,.15);color:#4ade80;border:1px solid rgba(0,204,0,.25);border-radius:8px;font-size:.82rem;font-weight:600;">
                        <i class="bi bi-download me-1"></i>Export CSV
                    </a>
                </div>

                {{-- Filters --}}
                <form method="GET" action="{{ route('admin.newsletter.index') }}" class="row g-2 mb-3">
                    <div class="col-7">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Search email or name…" value="{{ request('search') }}">
                    </div>
                    <div class="col-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="unsubscribed" {{ request('status') === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <button class="btn btn-sm w-100" style="background:var(--kx-green);color:#081108;font-weight:700;border-radius:8px;">Go</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="kx-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Subscribed</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($subscribers as $sub)
                        <tr>
                            <td style="color:var(--kx-green);font-weight:500;">{{ $sub->email }}</td>
                            <td style="color:var(--kx-muted)">{{ $sub->name ?: '—' }}</td>
                            <td>
                                @if($sub->is_active)
                                    <span class="badge-active">Active</span>
                                @else
                                    <span class="badge-unsub">Unsubscribed</span>
                                @endif
                            </td>
                            <td style="color:var(--kx-muted);font-size:.8rem;">{{ $sub->subscribed_at?->format('d M Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.newsletter.destroy', $sub) }}"
                                      onsubmit="return confirm('Delete {{ $sub->email }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-del"><i class="bi bi-trash3"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4" style="color:var(--kx-muted)">No subscribers found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $subscribers->links() }}</div>
            </div>
        </div>

        {{-- Campaign sender --}}
        <div class="col-lg-4">
            <div class="kx-card">
                <div class="kx-card-title"><i class="bi bi-send-fill" style="color:var(--kx-green)"></i> Send Campaign</div>
                <p style="color:var(--kx-muted);font-size:.82rem;margin-bottom:1rem;">
                    Sends to all <strong style="color:var(--kx-success)">{{ $stats['active'] }}</strong> active subscribers.
                </p>
                <form method="POST" action="{{ route('admin.newsletter.campaign') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="color:var(--kx-muted);font-size:.82rem;">Subject</label>
                        <input type="text" name="subject" class="form-control form-control-sm"
                               placeholder="e.g. BTC rates just changed!" value="{{ old('subject') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:var(--kx-muted);font-size:.82rem;">Message body</label>
                        <textarea name="body" class="form-control form-control-sm"
                                  placeholder="Write your message here… plain text or simple HTML." required>{{ old('body') }}</textarea>
                        <div style="font-size:.73rem;color:var(--kx-muted);margin-top:.3rem;">Plain text or basic HTML supported.</div>
                    </div>
                    <button type="submit" class="btn w-100"
                            style="background:var(--kx-green);color:#081108;font-weight:700;border-radius:8px;"
                            onclick="return confirm('Send this campaign to all {{ $stats['active'] }} active subscribers?')">
                        <i class="bi bi-send me-1"></i>Send to All
                    </button>
                </form>
            </div>
        </div>

    </div>{{-- /row --}}
</div>
@endsection
