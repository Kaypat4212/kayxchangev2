@extends('adminnavlayout')

@push('styles')
<style>
:root{
    --kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
    --kx-danger:#ef4444;
}
body{background:var(--kx-dark);color:var(--kx-text);}
.kx-page-header{background:linear-gradient(135deg,#0a1628,#0d1f1a);border:1px solid var(--kx-border);border-radius:16px;padding:24px 28px;margin-bottom:24px;}
.kx-page-header h1{font-size:1.35rem;font-weight:700;color:#fff;margin:0 0 4px;}
.kx-page-header p{color:var(--kx-muted);font-size:.875rem;margin:0;}
.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1.25rem;}
.stat-box{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:1.1rem 1.4rem;text-align:center;}
.stat-box .val{font-size:1.8rem;font-weight:800;color:var(--kx-green);}
.stat-box .lbl{font-size:.78rem;color:var(--kx-muted);margin-top:2px;}
.kx-table{width:100%;border-collapse:collapse;font-size:.82rem;}
.kx-table th{color:var(--kx-muted);font-weight:600;padding:.55rem .7rem;border-bottom:1px solid var(--kx-border);white-space:nowrap;}
.kx-table td{padding:.55rem .7rem;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle;}
.kx-table tr:last-child td{border-bottom:none;}
.badge-bot{background:rgba(239,68,68,.15);color:#f87171;font-size:.7rem;padding:.2em .55em;border-radius:12px;}
.badge-human{background:rgba(0,204,0,.12);color:#4ade80;font-size:.7rem;padding:.2em .55em;border-radius:12px;}
.badge-mobile{background:rgba(99,102,241,.15);color:#a5b4fc;font-size:.7rem;padding:.2em .55em;border-radius:12px;}
.badge-notified{background:rgba(250,204,21,.12);color:#fbbf24;font-size:.7rem;padding:.2em .55em;border-radius:12px;}
.ip-chip{font-family:monospace;background:rgba(0,0,0,.4);border:1px solid var(--kx-border);border-radius:5px;padding:.15em .5em;font-size:.78rem;color:var(--kx-green);}
.form-control,.form-select{background:rgba(0,0,0,.35);border:1px solid var(--kx-border);color:var(--kx-text);border-radius:8px;font-size:.82rem;}
.form-control:focus,.form-select:focus{border-color:var(--kx-green);box-shadow:0 0 0 3px rgba(0,204,0,.12);background:rgba(0,0,0,.4);color:#fff;}
.form-select option{background:#0d1f0d;}
.tg-icon{color:#fbbf24;font-size:.85rem;}
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width:1200px">

    <div class="kx-page-header">
        <h1><i class="bi bi-eye-fill me-2" style="color:var(--kx-green)"></i>Visitor Logs</h1>
        <p>Real-time tracking of all visitors — IP, location, device, and Telegram alerts for homepage visits.</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4">
        {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-box">
                <div class="val">{{ number_format($stats['total_today']) }}</div>
                <div class="lbl">Visits Today</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-box">
                <div class="val">{{ number_format($stats['unique_ips']) }}</div>
                <div class="lbl">Unique IPs Today</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-box">
                <div class="val" style="color:#f87171">{{ number_format($stats['bots_today']) }}</div>
                <div class="lbl">Bots Today</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-box">
                @if($stats['top_countries']->isNotEmpty())
                    <div class="val" style="font-size:1.1rem;">{{ $stats['top_countries']->keys()->first() }}</div>
                    <div class="lbl">Top Country Today</div>
                @else
                    <div class="val">—</div>
                    <div class="lbl">Top Country Today</div>
                @endif
            </div>
        </div>
    </div>

    <div class="kx-card">
        {{-- Toolbar --}}
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <form method="GET" action="{{ route('admin.visitor-logs.index') }}" class="d-flex flex-wrap gap-2 flex-grow-1">
                <input type="text" name="ip_filter" class="form-control" style="max-width:150px"
                       placeholder="Filter IP…" value="{{ request('ip_filter') }}">
                <input type="text" name="country" class="form-control" style="max-width:140px"
                       placeholder="Country…" value="{{ request('country') }}">
                <select name="bot" class="form-select" style="max-width:110px">
                    <option value="">All</option>
                    <option value="no"  {{ request('bot') === 'no'  ? 'selected' : '' }}>Humans</option>
                    <option value="yes" {{ request('bot') === 'yes' ? 'selected' : '' }}>Bots</option>
                </select>
                <button class="btn btn-sm" style="background:var(--kx-green);color:#081108;font-weight:700;border-radius:8px;padding:.35rem 1rem;">Filter</button>
                <a href="{{ route('admin.visitor-logs.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,.07);color:var(--kx-muted);border-radius:8px;padding:.35rem .9rem;">Clear</a>
            </form>
            <a href="{{ route('admin.visitor-logs.export') }}" class="btn btn-sm"
               style="background:rgba(0,204,0,.12);color:#4ade80;border:1px solid rgba(0,204,0,.2);border-radius:8px;font-size:.82rem;font-weight:600;white-space:nowrap;">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
            <form method="POST" action="{{ route('admin.visitor-logs.clear') }}"
                  onsubmit="return confirm('Delete logs older than 30 days?')">
                @csrf @method('DELETE')
                <input type="hidden" name="days" value="30">
                <button type="submit" class="btn btn-sm"
                        style="background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.2);border-radius:8px;font-size:.82rem;white-space:nowrap;">
                    <i class="bi bi-trash3 me-1"></i>Clear old (30d)
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="kx-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>IP</th>
                        <th>Location</th>
                        <th>Device</th>
                        <th>Browser</th>
                        <th>Route/URL</th>
                        <th>Referrer</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="color:var(--kx-muted);white-space:nowrap;">
                        {{ $log->created_at->format('d M, H:i') }}
                    </td>
                    <td>
                        <span class="ip-chip">{{ $log->ip }}</span>
                        @if($log->telegram_notified)
                            <i class="bi bi-telegram tg-icon ms-1" title="Telegram notified"></i>
                        @endif
                        @if($log->user_id)
                            <br><span style="font-size:.72rem;color:#a5b4fc">👤 #{{ $log->user_id }}</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap;">
                        @if($log->country)
                            <span style="font-size:.85rem;">{{ $log->country }}</span>
                            @if($log->city)<br><span style="color:var(--kx-muted);font-size:.75rem;">{{ $log->city }}</span>@endif
                        @else
                            <span style="color:var(--kx-muted)">—</span>
                        @endif
                    </td>
                    <td>
                        {{ $log->platform ?? '—' }}
                        @if($log->is_mobile) <span class="badge-mobile">📱</span> @endif
                    </td>
                    <td style="color:var(--kx-muted)">{{ $log->browser ?? '—' }}</td>
                    <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $log->url }}">
                        @if($log->route_name)
                            <span style="color:var(--kx-green);font-size:.78rem;">{{ $log->route_name }}</span><br>
                        @endif
                        <span style="color:var(--kx-muted);font-size:.75rem;">{{ $log->method }} {{ Str::limit(parse_url($log->url, PHP_URL_PATH) ?: $log->url, 40) }}</span>
                    </td>
                    <td style="color:var(--kx-muted);font-size:.75rem;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $log->referer }}">
                        {{ $log->referer ? (parse_url($log->referer, PHP_URL_HOST) ?: Str::limit($log->referer, 30)) : '—' }}
                    </td>
                    <td>
                        @if($log->is_bot)
                            <span class="badge-bot">🤖 Bot</span>
                        @else
                            <span class="badge-human">👤 Human</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4" style="color:var(--kx-muted)">No visitor logs yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
