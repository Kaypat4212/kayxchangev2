@extends('layout')

@section('content')
<style>
:root {
    --kx-dark: #0d1117; --kx-card: #161b27; --kx-card2: #1e2535;
    --kx-border: rgba(255,255,255,0.07); --kx-text: #e4e8f0; --kx-muted: #7a8599;
    --kx-orange: #f97316; --kx-orange-dim: rgba(249,115,22,0.12);
}
.fh-wrap { max-width: 800px; margin: 0 auto; padding: 28px 16px 80px; }
.fh-hdr { display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px; }
.fh-hdr-left { display:flex;align-items:center;gap:12px; }
.fh-icon { width:44px;height:44px;border-radius:12px;background:var(--kx-orange-dim);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0; }
.fh-title { font-size:1.25rem;font-weight:700;color:var(--kx-text);margin:0; }
.fh-sub { font-size:0.8rem;color:var(--kx-muted);margin:2px 0 0; }
.fh-new-btn {
    display:inline-flex;align-items:center;gap:6px;padding:9px 16px;
    background:var(--kx-orange);color:#fff;border-radius:9px;
    font-size:0.82rem;font-weight:600;text-decoration:none;transition:opacity .2s;
}
.fh-new-btn:hover{opacity:.85;color:#fff;}
.fh-card { background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;overflow:hidden;margin-bottom:14px; }
.fh-row {
    padding:16px 20px;border-bottom:1px solid var(--kx-border);
    display:grid;grid-template-columns:1fr auto;gap:12px;align-items:start;
}
.fh-row:last-child{border-bottom:none;}
.fh-row-title{font-weight:600;color:var(--kx-text);font-size:0.92rem;margin-bottom:4px;}
.fh-row-meta{font-size:0.78rem;color:var(--kx-muted);display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.fh-row-desc{font-size:0.82rem;color:var(--kx-muted);margin-top:6px;line-height:1.5;}
.fh-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:600;text-transform:capitalize;}
.badge-pending{background:rgba(148,163,184,0.12);color:#94a3b8;}
.badge-in_review{background:rgba(251,191,36,0.12);color:#fbbf24;}
.badge-planned{background:rgba(56,189,248,0.12);color:#38bdf8;}
.badge-completed{background:rgba(0,204,0,0.12);color:#4ade80;}
.badge-rejected{background:rgba(239,68,68,0.12);color:#f87171;}
.fh-pri-low{color:#64748b;font-weight:600;}
.fh-pri-medium{color:#f59e0b;font-weight:600;}
.fh-pri-high{color:#f97316;font-weight:600;}
.fh-empty{text-align:center;padding:48px 20px;color:var(--kx-muted);}
.fh-empty i{font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4;}
.fh-admin-note{margin-top:8px;padding:8px 12px;background:rgba(56,189,248,0.07);border-left:3px solid #38bdf8;border-radius:4px;font-size:0.8rem;color:#7dd3fc;}
</style>

<div class="fh-wrap">
    <div class="fh-hdr">
        <div class="fh-hdr-left">
            <div class="fh-icon">💡</div>
            <div>
                <p class="fh-title">My Feature Requests</p>
                <p class="fh-sub">Track the status of ideas you've submitted</p>
            </div>
        </div>
        <a href="{{ route('feature.request.form') }}" class="fh-new-btn">
            <i class="bi bi-plus-lg"></i> New Request
        </a>
    </div>

    @if($requests->isEmpty())
        <div class="fh-card">
            <div class="fh-empty">
                <i class="bi bi-lightbulb"></i>
                <p style="font-size:0.95rem;margin:0 0 8px">No feature requests yet</p>
                <a href="{{ route('feature.request.form') }}" style="color:var(--kx-orange);font-size:0.85rem;text-decoration:none;">Submit your first idea →</a>
            </div>
        </div>
    @else
        <div class="fh-card">
            @foreach($requests as $req)
            <div class="fh-row">
                <div>
                    <p class="fh-row-title">{{ $req->title }}</p>
                    <div class="fh-row-meta">
                        <span class="fh-pri-{{ $req->priority }}">● {{ ucfirst($req->priority) }} priority</span>
                        <span>{{ ucfirst($req->category) }}</span>
                        <span>{{ $req->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="fh-row-desc">{{ Str::limit($req->description, 120) }}</p>
                    @if($req->admin_notes)
                        <div class="fh-admin-note">
                            <strong>Admin note:</strong> {{ $req->admin_notes }}
                        </div>
                    @endif
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <span class="fh-badge badge-{{ str_replace(' ','-',strtolower($req->status)) }}">{{ $req->status }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div style="margin-top:16px;">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
