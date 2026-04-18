@extends('layout')

@section('content')
<style>
:root {
    --kx-dark: #0d1117; --kx-card: #161b27; --kx-card2: #1e2535;
    --kx-border: rgba(255,255,255,0.07); --kx-text: #e4e8f0; --kx-muted: #7a8599;
    --kx-red: #ef4444; --kx-red-dim: rgba(239,68,68,0.12);
}
.bh-wrap { max-width: 800px; margin: 0 auto; padding: 28px 16px 80px; }
.bh-hdr { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.bh-hdr-left { display:flex; align-items:center; gap:12px; }
.bh-icon { width:44px;height:44px;border-radius:12px;background:var(--kx-red-dim);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0; }
.bh-title { font-size:1.25rem;font-weight:700;color:var(--kx-text);margin:0; }
.bh-sub { font-size:0.8rem;color:var(--kx-muted);margin:2px 0 0; }
.bh-new-btn {
    display:inline-flex;align-items:center;gap:6px;padding:9px 16px;
    background:var(--kx-red);color:#fff;border-radius:9px;
    font-size:0.82rem;font-weight:600;text-decoration:none;
    transition:opacity .2s;
}
.bh-new-btn:hover{opacity:.85;color:#fff;}
.bh-card { background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;overflow:hidden;margin-bottom:14px; }
.bh-row {
    padding:16px 20px;border-bottom:1px solid var(--kx-border);
    display:grid;grid-template-columns:1fr auto;gap:12px;align-items:start;
}
.bh-row:last-child{border-bottom:none;}
.bh-row-title{font-weight:600;color:var(--kx-text);font-size:0.92rem;margin-bottom:4px;}
.bh-row-meta{font-size:0.78rem;color:var(--kx-muted);display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.bh-row-desc{font-size:0.82rem;color:var(--kx-muted);margin-top:6px;line-height:1.5;}
.bh-badge {
    display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:600;
    text-transform:capitalize;white-space:nowrap;
}
.badge-open{background:rgba(148,163,184,0.12);color:#94a3b8;}
.badge-investigating{background:rgba(251,191,36,0.12);color:#fbbf24;}
.badge-resolved{background:rgba(0,204,0,0.12);color:#4ade80;}
.badge-closed{background:rgba(107,114,128,0.12);color:#6b7280;}
.bh-sev-low{color:#64748b;font-weight:600;}
.bh-sev-medium{color:#f59e0b;font-weight:600;}
.bh-sev-high{color:#f97316;font-weight:600;}
.bh-sev-critical{color:#ef4444;font-weight:600;}
.bh-empty{text-align:center;padding:48px 20px;color:var(--kx-muted);}
.bh-empty i{font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4;}
.bh-admin-note{margin-top:8px;padding:8px 12px;background:rgba(249,115,22,0.07);border-left:3px solid #f97316;border-radius:4px;font-size:0.8rem;color:#fcd34d;}
</style>

<div class="bh-wrap">
    <div class="bh-hdr">
        <div class="bh-hdr-left">
            <div class="bh-icon">🐛</div>
            <div>
                <p class="bh-title">My Bug Reports</p>
                <p class="bh-sub">Track the status of bugs you've reported</p>
            </div>
        </div>
        <a href="{{ route('bug.report.form') }}" class="bh-new-btn">
            <i class="bi bi-plus-lg"></i> New Report
        </a>
    </div>

    @if($reports->isEmpty())
        <div class="bh-card">
            <div class="bh-empty">
                <i class="bi bi-bug"></i>
                <p style="font-size:0.95rem;margin:0 0 8px">No bug reports yet</p>
                <a href="{{ route('bug.report.form') }}" style="color:var(--kx-red);font-size:0.85rem;text-decoration:none;">Report your first bug →</a>
            </div>
        </div>
    @else
        <div class="bh-card">
            @foreach($reports as $report)
            <div class="bh-row">
                <div>
                    <p class="bh-row-title">{{ $report->title }}</p>
                    <div class="bh-row-meta">
                        <span class="bh-sev-{{ $report->severity }}">● {{ ucfirst($report->severity) }}</span>
                        <span>{{ ucfirst($report->category) }}</span>
                        <span>{{ $report->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="bh-row-desc">{{ Str::limit($report->description, 120) }}</p>
                    @if($report->admin_notes)
                        <div class="bh-admin-note">
                            <strong>Admin note:</strong> {{ $report->admin_notes }}
                        </div>
                    @endif
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <span class="bh-badge badge-{{ $report->status }}">{{ $report->status }}</span>
                    @if($report->page_url)
                        <br><a href="{{ $report->page_url }}" target="_blank" rel="noopener noreferrer"
                            style="font-size:0.72rem;color:var(--kx-muted);margin-top:6px;display:inline-block;">
                            <i class="bi bi-link-45deg"></i> Page
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div style="margin-top:16px;">{{ $reports->links() }}</div>
    @endif
</div>
@endsection
