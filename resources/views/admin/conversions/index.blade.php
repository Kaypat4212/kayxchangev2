@extends('adminnavlayout')

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}
body{background:var(--kx-dark);color:var(--kx-text);}
.kx-page-header{background:linear-gradient(135deg,#0a1628,#0d1f1a);border:1px solid var(--kx-border);border-radius:16px;padding:24px 28px;margin-bottom:24px;}
.kx-page-header h1{font-size:1.35rem;font-weight:700;color:#fff;margin:0 0 4px;}
.kx-page-header p{color:var(--kx-muted);font-size:.875rem;margin:0;}
.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1.25rem;}
.stat-mini{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:10px;padding:.8rem 1rem;text-align:center;}
.stat-mini .v{font-size:1.4rem;font-weight:800;color:var(--kx-green);}
.stat-mini .l{font-size:.72rem;color:var(--kx-muted);}
.stat-mini.warn .v{color:#fbbf24;}
.stat-mini.bad .v{color:#f87171;}
.stat-mini.info .v{color:#38bdf8;}
.filter-pill{display:inline-block;padding:.35rem .9rem;border-radius:999px;font-size:.75rem;font-weight:600;text-decoration:none;border:1px solid var(--kx-border);color:var(--kx-muted);background:var(--kx-card2);margin-right:.4rem;transition:.15s;}
.filter-pill:hover{color:var(--kx-text);}
.filter-pill.active{background:var(--kx-green);color:#081108;border-color:var(--kx-green);}
.conv-table{width:100%;border-collapse:collapse;font-size:.82rem;}
.conv-table th{color:var(--kx-muted);font-weight:600;text-align:left;padding:.6rem .5rem;border-bottom:1px solid var(--kx-border);font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;}
.conv-table td{padding:.7rem .5rem;border-bottom:1px solid var(--kx-border);color:var(--kx-text);vertical-align:middle;}
.conv-table tr:hover td{background:rgba(255,255,255,.02);}
.status-pill{font-size:.68rem;font-weight:700;padding:.2rem .6rem;border-radius:999px;text-transform:uppercase;letter-spacing:.03em;}
.status-pill.completed{background:rgba(16,185,129,.12);color:#34d399;}
.status-pill.pending{background:rgba(245,158,11,.12);color:#fbbf24;}
.status-pill.processing{background:rgba(56,189,248,.12);color:#38bdf8;}
.status-pill.failed{background:rgba(239,68,68,.12);color:#f87171;}
.mode-tag{font-size:.68rem;font-weight:700;padding:.2rem .6rem;border-radius:6px;}
.mode-tag.manual{background:rgba(167,139,250,.12);color:#a78bfa;}
.mode-tag.cryptomus{background:rgba(0,204,0,.1);color:#4ade80;}
.btn-view{font-size:.75rem;padding:.3rem .8rem;border-radius:8px;background:var(--kx-card2);border:1px solid var(--kx-border);color:var(--kx-text);text-decoration:none;font-weight:600;}
.btn-view:hover{border-color:var(--kx-green);color:var(--kx-green);}
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width:1100px">

    <div class="kx-page-header">
        <h1><i class="bi bi-arrow-left-right me-2" style="color:var(--kx-green)"></i>Conversions</h1>
        <p>Review and process crypto conversion requests. Manual conversions require you to send the target crypto to the user's wallet.</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3">
        <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stats --}}
    <div class="row g-2 mb-3">
        <div class="col-3"><div class="stat-mini warn"><div class="v">{{ $counts['pending'] }}</div><div class="l">Pending</div></div></div>
        <div class="col-3"><div class="stat-mini info"><div class="v">{{ $counts['processing'] }}</div><div class="l">Processing</div></div></div>
        <div class="col-3"><div class="stat-mini"><div class="v">{{ $counts['completed'] }}</div><div class="l">Completed</div></div></div>
        <div class="col-3"><div class="stat-mini bad"><div class="v">{{ $counts['failed'] }}</div><div class="l">Failed</div></div></div>
    </div>

    <div class="kx-card">
        {{-- Filters --}}
        <div class="mb-3">
            @foreach(['' => 'All', 'pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed', 'failed' => 'Failed'] as $val => $label)
            <a href="{{ route('admin.conversions.index', array_filter(['status' => $val, 'mode' => $mode])) }}"
               class="filter-pill {{ ($status ?? '') === $val ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
            <span style="border-left:1px solid var(--kx-border);margin:0 .5rem;"></span>
            @foreach(['' => 'All Modes', 'manual' => 'Manual', 'cryptomus' => 'Cryptomus'] as $val => $label)
            <a href="{{ route('admin.conversions.index', array_filter(['status' => $status, 'mode' => $val])) }}"
               class="filter-pill {{ ($mode ?? '') === $val ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="table-responsive">
            <table class="conv-table">
                <thead>
                    <tr>
                        <th>#</th><th>User</th><th>Pair</th><th>Amount</th><th>Receive</th><th>Mode</th><th>Status</th><th>Date</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversions as $conv)
                    <tr>
                        <td style="color:var(--kx-muted)">{{ $conv->id }}</td>
                        <td>
                            <div style="font-weight:600">{{ $conv->user->name ?? 'User #'.$conv->user_id }}</div>
                            <div style="font-size:.7rem;color:var(--kx-muted)">{{ $conv->user->email ?? '' }}</div>
                        </td>
                        <td style="font-weight:700">{{ $conv->from_coin }} → {{ $conv->to_coin }}</td>
                        <td>{{ rtrim(rtrim(number_format((float) $conv->from_amount, 8), '0'), '.') }} {{ $conv->from_coin }}</td>
                        <td style="color:var(--kx-green);font-weight:600">{{ rtrim(rtrim(number_format((float) $conv->to_amount, 8), '0'), '.') }} {{ $conv->to_coin }}</td>
                        <td><span class="mode-tag {{ $conv->mode }}">{{ ucfirst($conv->mode) }}</span></td>
                        <td><span class="status-pill {{ $conv->status }}">{{ $conv->status }}</span></td>
                        <td style="color:var(--kx-muted);font-size:.75rem">{{ $conv->created_at->format('M d, H:i') }}</td>
                        <td><a href="{{ route('admin.conversions.show', $conv->id) }}" class="btn-view">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="text-align:center;color:var(--kx-muted);padding:2rem;">No conversions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $conversions->links() }}</div>
    </div>
</div>
@endsection
