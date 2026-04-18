@extends('adminnavlayout')

@section('content')
<style>
:root{
    --kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
    --kx-red:#ef4444;--kx-orange:#f97316;--kx-yellow:#fbbf24;--kx-green:#00cc00;
}
body{background:var(--kx-dark);color:var(--kx-text);font-family:'Poppins',sans-serif;}
.abr-wrap{padding:28px 20px 60px;max-width:1100px;margin:0 auto;}
.abr-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;}
.abr-hdr-title{font-size:1.3rem;font-weight:700;color:var(--kx-text);}
.abr-hdr-sub{font-size:0.8rem;color:var(--kx-muted);margin-top:2px;}
.abr-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;}
@media(max-width:640px){.abr-stats{grid-template-columns:repeat(2,1fr);}}
.abr-stat{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;padding:16px 18px;}
.abr-stat-num{font-size:1.6rem;font-weight:700;margin-bottom:2px;}
.abr-stat-lbl{font-size:0.75rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.4px;}
.abr-filters{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;padding:16px 18px;margin-bottom:18px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;}
.abr-filters label{font-size:0.75rem;color:var(--kx-muted);display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;}
.abr-sel,.abr-inp{background:var(--kx-dark);border:1.5px solid var(--kx-border);border-radius:8px;color:var(--kx-text);font-size:0.82rem;padding:7px 10px;outline:none;}
.abr-sel:focus,.abr-inp:focus{border-color:var(--kx-red);}
.abr-sel option{background:#1e2535;}
.abr-filter-btn{padding:8px 16px;background:var(--kx-red);color:#fff;border:none;border-radius:8px;font-size:0.82rem;font-weight:600;cursor:pointer;transition:opacity .2s;}
.abr-filter-btn:hover{opacity:.85;}
.abr-table-wrap{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;overflow:hidden;}
.abr-table{width:100%;border-collapse:collapse;}
.abr-table th{padding:12px 16px;font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--kx-muted);border-bottom:1px solid var(--kx-border);text-align:left;}
.abr-table td{padding:13px 16px;font-size:0.84rem;color:var(--kx-text);border-bottom:1px solid var(--kx-border);vertical-align:middle;}
.abr-table tr:last-child td{border-bottom:none;}
.abr-table tr:hover td{background:rgba(255,255,255,0.02);}
.abr-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:600;text-transform:capitalize;}
.b-open{background:rgba(148,163,184,.12);color:#94a3b8;}
.b-investigating{background:rgba(251,191,36,.12);color:#fbbf24;}
.b-resolved{background:rgba(0,204,0,.12);color:#4ade80;}
.b-closed{background:rgba(107,114,128,.12);color:#6b7280;}
.sev-low{color:#64748b;font-weight:600;}
.sev-medium{color:#f59e0b;font-weight:600;}
.sev-high{color:#f97316;font-weight:600;}
.sev-critical{color:#ef4444;font-weight:600;}
.abr-btn-edit{background:rgba(249,115,22,.15);color:#fb923c;border:1px solid rgba(249,115,22,.3);padding:5px 11px;border-radius:7px;font-size:0.78rem;cursor:pointer;transition:background .2s;}
.abr-btn-edit:hover{background:rgba(249,115,22,.25);}
.abr-empty{text-align:center;padding:40px;color:var(--kx-muted);}
/* Modal */
.abr-modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:1000;align-items:center;justify-content:center;}
.abr-modal-backdrop.open{display:flex;}
.abr-modal{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:18px;padding:28px;width:90%;max-width:480px;}
.abr-modal-title{font-size:1.1rem;font-weight:700;margin-bottom:18px;color:var(--kx-text);}
.abr-modal label{font-size:0.78rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:6px;}
.abr-modal select,.abr-modal textarea{width:100%;background:var(--kx-dark);border:1.5px solid var(--kx-border);border-radius:9px;color:var(--kx-text);font-size:.85rem;padding:.6rem .8rem;outline:none;margin-bottom:14px;font-family:inherit;}
.abr-modal select:focus,.abr-modal textarea:focus{border-color:var(--kx-red);}
.abr-modal select option{background:#1e2535;}
.abr-modal-btns{display:flex;gap:10px;justify-content:flex-end;margin-top:6px;}
.abr-modal-save{padding:9px 20px;background:var(--kx-red);color:#fff;border:none;border-radius:9px;font-size:.85rem;font-weight:600;cursor:pointer;}
.abr-modal-cancel{padding:9px 20px;background:transparent;color:var(--kx-muted);border:1px solid var(--kx-border);border-radius:9px;font-size:.85rem;cursor:pointer;}
</style>

<div class="abr-wrap">
    <div class="abr-hdr">
        <div>
            <p class="abr-hdr-title">🐛 Bug Reports</p>
            <p class="abr-hdr-sub">Manage and resolve user-submitted bug reports</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="abr-stats">
        <div class="abr-stat">
            <div class="abr-stat-num" style="color:#94a3b8;">{{ $stats['open'] }}</div>
            <div class="abr-stat-lbl">Open</div>
        </div>
        <div class="abr-stat">
            <div class="abr-stat-num" style="color:#fbbf24;">{{ $stats['investigating'] }}</div>
            <div class="abr-stat-lbl">Investigating</div>
        </div>
        <div class="abr-stat">
            <div class="abr-stat-num" style="color:#4ade80;">{{ $stats['resolved'] }}</div>
            <div class="abr-stat-lbl">Resolved</div>
        </div>
        <div class="abr-stat">
            <div class="abr-stat-num" style="color:#6b7280;">{{ $stats['closed'] }}</div>
            <div class="abr-stat-lbl">Closed</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="abr-filters">
        <div>
            <label>Status</label>
            <select name="status" class="abr-sel">
                <option value="">All</option>
                <option value="open"          {{ request('status')=='open'?'selected':'' }}>Open</option>
                <option value="investigating" {{ request('status')=='investigating'?'selected':'' }}>Investigating</option>
                <option value="resolved"      {{ request('status')=='resolved'?'selected':'' }}>Resolved</option>
                <option value="closed"        {{ request('status')=='closed'?'selected':'' }}>Closed</option>
            </select>
        </div>
        <div>
            <label>Severity</label>
            <select name="severity" class="abr-sel">
                <option value="">All</option>
                <option value="critical" {{ request('severity')=='critical'?'selected':'' }}>Critical</option>
                <option value="high"     {{ request('severity')=='high'?'selected':'' }}>High</option>
                <option value="medium"   {{ request('severity')=='medium'?'selected':'' }}>Medium</option>
                <option value="low"      {{ request('severity')=='low'?'selected':'' }}>Low</option>
            </select>
        </div>
        <div>
            <label>Category</label>
            <select name="category" class="abr-sel">
                <option value="">All</option>
                @foreach(['general','ui','payment','trade','account','other'] as $cat)
                    <option value="{{ $cat }}" {{ request('category')===$cat?'selected':'' }}>{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Search</label>
            <input type="text" name="search" class="abr-inp" value="{{ request('search') }}" placeholder="Title or user…">
        </div>
        <button type="submit" class="abr-filter-btn">Filter</button>
        <a href="{{ route('admin.bug-reports') }}" style="padding:8px 12px;color:var(--kx-muted);font-size:.82rem;text-decoration:none;align-self:flex-end;">Clear</a>
    </form>

    {{-- Table --}}
    <div class="abr-table-wrap">
        @if($reports->isEmpty())
            <div class="abr-empty"><i class="bi bi-bug" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.4;"></i>No bug reports found</div>
        @else
        <table class="abr-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Title</th>
                    <th>Severity</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $r)
                <tr>
                    <td style="color:var(--kx-muted);">{{ $r->id }}</td>
                    <td>
                        <span style="font-weight:600;">{{ $r->user->name ?? '—' }}</span>
                        <br><span style="font-size:.75rem;color:var(--kx-muted);">{{ $r->user->email ?? '' }}</span>
                    </td>
                    <td>
                        {{ Str::limit($r->title, 40) }}
                        @if($r->admin_notes)
                            <br><span style="font-size:.75rem;color:#fb923c;"><i class="bi bi-chat-dots"></i> Has note</span>
                        @endif
                    </td>
                    <td><span class="sev-{{ $r->severity }}">{{ ucfirst($r->severity) }}</span></td>
                    <td style="text-transform:capitalize;color:var(--kx-muted);">{{ $r->category }}</td>
                    <td><span class="abr-badge b-{{ $r->status }}">{{ $r->status }}</span></td>
                    <td style="color:var(--kx-muted);font-size:.78rem;">{{ $r->created_at->format('M d, Y') }}</td>
                    <td>
                        <button class="abr-btn-edit"
                            onclick="openModal({{ $r->id }}, '{{ addslashes($r->title) }}', '{{ $r->status }}', '{{ addslashes($r->admin_notes ?? '') }}', '{{ route('admin.bug-reports.update', $r->id) }}')">
                            Edit
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding:16px 16px 4px;">{{ $reports->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

{{-- Edit Modal --}}
<div class="abr-modal-backdrop" id="editModal">
    <div class="abr-modal">
        <p class="abr-modal-title" id="modalTitle">Update Bug Report</p>
        <form method="POST" id="editForm">
            @csrf
            @method('PATCH')
            <label>Status</label>
            <select name="status" id="modalStatus">
                <option value="open">Open</option>
                <option value="investigating">Investigating</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
            </select>
            <label>Admin Note <span style="text-transform:none;font-weight:400">(optional, visible to user)</span></label>
            <textarea name="admin_notes" id="modalNotes" rows="3" placeholder="Add a note for the user…"></textarea>
            <div class="abr-modal-btns">
                <button type="button" class="abr-modal-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="abr-modal-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id, title, status, notes, url) {
    document.getElementById('modalTitle').textContent = 'Update: ' + title;
    document.getElementById('modalStatus').value = status;
    document.getElementById('modalNotes').value = notes;
    document.getElementById('editForm').action = url;
    document.getElementById('editModal').classList.add('open');
}
function closeModal() {
    document.getElementById('editModal').classList.remove('open');
}
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endsection
