@extends('adminnavlayout')

@section('content')
<style>
:root{
    --kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
    --kx-orange:#f97316;--kx-blue:#38bdf8;--kx-green:#00cc00;
}
body{background:var(--kx-dark);color:var(--kx-text);font-family:'Poppins',sans-serif;}
.afr-wrap{padding:28px 20px 60px;max-width:1100px;margin:0 auto;}
.afr-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;}
.afr-hdr-title{font-size:1.3rem;font-weight:700;}
.afr-hdr-sub{font-size:0.8rem;color:var(--kx-muted);margin-top:2px;}
.afr-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px;}
@media(max-width:700px){.afr-stats{grid-template-columns:repeat(3,1fr);}}
@media(max-width:480px){.afr-stats{grid-template-columns:repeat(2,1fr);}}
.afr-stat{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;padding:14px 16px;}
.afr-stat-num{font-size:1.5rem;font-weight:700;margin-bottom:2px;}
.afr-stat-lbl{font-size:.72rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.4px;}
.afr-filters{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;padding:16px 18px;margin-bottom:18px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;}
.afr-filters label{font-size:.75rem;color:var(--kx-muted);display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;}
.afr-sel,.afr-inp{background:var(--kx-dark);border:1.5px solid var(--kx-border);border-radius:8px;color:var(--kx-text);font-size:.82rem;padding:7px 10px;outline:none;}
.afr-sel:focus,.afr-inp:focus{border-color:var(--kx-orange);}
.afr-sel option{background:#1e2535;}
.afr-filter-btn{padding:8px 16px;background:var(--kx-orange);color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;transition:opacity .2s;}
.afr-filter-btn:hover{opacity:.85;}
.afr-table-wrap{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;overflow:hidden;}
.afr-table{width:100%;border-collapse:collapse;}
.afr-table th{padding:12px 16px;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--kx-muted);border-bottom:1px solid var(--kx-border);text-align:left;}
.afr-table td{padding:13px 16px;font-size:.84rem;color:var(--kx-text);border-bottom:1px solid var(--kx-border);vertical-align:middle;}
.afr-table tr:last-child td{border-bottom:none;}
.afr-table tr:hover td{background:rgba(255,255,255,.02);}
.afr-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600;text-transform:capitalize;}
.b-pending{background:rgba(148,163,184,.12);color:#94a3b8;}
.b-in_review{background:rgba(251,191,36,.12);color:#fbbf24;}
.b-planned{background:rgba(56,189,248,.12);color:#38bdf8;}
.b-completed{background:rgba(0,204,0,.12);color:#4ade80;}
.b-rejected{background:rgba(239,68,68,.12);color:#f87171;}
.pri-low{color:#64748b;font-weight:600;}
.pri-medium{color:#f59e0b;font-weight:600;}
.pri-high{color:#f97316;font-weight:600;}
.afr-btn-edit{background:rgba(249,115,22,.15);color:#fb923c;border:1px solid rgba(249,115,22,.3);padding:5px 11px;border-radius:7px;font-size:.78rem;cursor:pointer;transition:background .2s;}
.afr-btn-edit:hover{background:rgba(249,115,22,.25);}
.afr-empty{text-align:center;padding:40px;color:var(--kx-muted);}
.afr-modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:1000;align-items:center;justify-content:center;}
.afr-modal-backdrop.open{display:flex;}
.afr-modal{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:18px;padding:28px;width:90%;max-width:480px;}
.afr-modal-title{font-size:1.1rem;font-weight:700;margin-bottom:18px;}
.afr-modal label{font-size:.78rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:6px;}
.afr-modal select,.afr-modal textarea{width:100%;background:var(--kx-dark);border:1.5px solid var(--kx-border);border-radius:9px;color:var(--kx-text);font-size:.85rem;padding:.6rem .8rem;outline:none;margin-bottom:14px;font-family:inherit;}
.afr-modal select:focus,.afr-modal textarea:focus{border-color:var(--kx-orange);}
.afr-modal select option{background:#1e2535;}
.afr-modal-btns{display:flex;gap:10px;justify-content:flex-end;margin-top:6px;}
.afr-modal-save{padding:9px 20px;background:var(--kx-orange);color:#fff;border:none;border-radius:9px;font-size:.85rem;font-weight:600;cursor:pointer;}
.afr-modal-cancel{padding:9px 20px;background:transparent;color:var(--kx-muted);border:1px solid var(--kx-border);border-radius:9px;font-size:.85rem;cursor:pointer;}
</style>

<div class="afr-wrap">
    <div class="afr-hdr">
        <div>
            <p class="afr-hdr-title">💡 Feature Requests</p>
            <p class="afr-hdr-sub">Review and manage feature ideas from users</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="afr-stats">
        <div class="afr-stat">
            <div class="afr-stat-num" style="color:#94a3b8;">{{ $stats['pending'] }}</div>
            <div class="afr-stat-lbl">Pending</div>
        </div>
        <div class="afr-stat">
            <div class="afr-stat-num" style="color:#fbbf24;">{{ $stats['in_review'] }}</div>
            <div class="afr-stat-lbl">Under Review</div>
        </div>
        <div class="afr-stat">
            <div class="afr-stat-num" style="color:#38bdf8;">{{ $stats['planned'] }}</div>
            <div class="afr-stat-lbl">Planned</div>
        </div>
        <div class="afr-stat">
            <div class="afr-stat-num" style="color:#4ade80;">{{ $stats['completed'] }}</div>
            <div class="afr-stat-lbl">Completed</div>
        </div>
        <div class="afr-stat">
            <div class="afr-stat-num" style="color:#f87171;">{{ $stats['rejected'] }}</div>
            <div class="afr-stat-lbl">Rejected</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="afr-filters">
        <div>
            <label>Status</label>
            <select name="status" class="afr-sel">
                <option value="">All</option>
                <option value="pending"      {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                <option value="in_review" {{ request('status')=='in_review'?'selected':'' }}>In Review</option>
                <option value="planned"      {{ request('status')=='planned'?'selected':'' }}>Planned</option>
                <option value="completed"    {{ request('status')=='completed'?'selected':'' }}>Completed</option>
                <option value="rejected"     {{ request('status')=='rejected'?'selected':'' }}>Rejected</option>
            </select>
        </div>
        <div>
            <label>Priority</label>
            <select name="priority" class="afr-sel">
                <option value="">All</option>
                <option value="high"   {{ request('priority')=='high'?'selected':'' }}>High</option>
                <option value="medium" {{ request('priority')=='medium'?'selected':'' }}>Medium</option>
                <option value="low"    {{ request('priority')=='low'?'selected':'' }}>Low</option>
            </select>
        </div>
        <div>
            <label>Category</label>
            <select name="category" class="afr-sel">
                <option value="">All</option>
                @foreach(['trading','payments','ui','security','notification','other'] as $cat)
                    <option value="{{ $cat }}" {{ request('category')===$cat?'selected':'' }}>{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Search</label>
            <input type="text" name="search" class="afr-inp" value="{{ request('search') }}" placeholder="Title or user…">
        </div>
        <button type="submit" class="afr-filter-btn">Filter</button>
        <a href="{{ route('admin.feature-requests') }}" style="padding:8px 12px;color:var(--kx-muted);font-size:.82rem;text-decoration:none;align-self:flex-end;">Clear</a>
    </form>

    {{-- Table --}}
    <div class="afr-table-wrap">
        @if($requests->isEmpty())
            <div class="afr-empty"><i class="bi bi-lightbulb" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.4;"></i>No feature requests found</div>
        @else
        <table class="afr-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $r)
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
                    <td style="text-transform:capitalize;color:var(--kx-muted);">{{ $r->category ?? '—' }}</td>
                    <td><span class="pri-{{ $r->priority }}">{{ ucfirst($r->priority ?? '—') }}</span></td>
                    <td>
                        <span class="afr-badge b-{{ str_replace(' ','-',strtolower($r->status)) }}">{{ $r->status }}</span>
                    </td>
                    <td style="color:var(--kx-muted);font-size:.78rem;">{{ $r->created_at->format('M d, Y') }}</td>
                    <td>
                        <button class="afr-btn-edit"
                            onclick="openModal({{ $r->id }}, '{{ addslashes($r->title) }}', '{{ addslashes($r->status) }}', '{{ addslashes($r->admin_notes ?? '') }}', '{{ route('admin.feature-requests.update', $r->id) }}')">
                            Edit
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding:16px 16px 4px;">{{ $requests->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

{{-- Edit Modal --}}
<div class="afr-modal-backdrop" id="editModal">
    <div class="afr-modal">
        <p class="afr-modal-title" id="modalTitle">Update Feature Request</p>
        <form method="POST" id="editForm">
            @csrf
            @method('PATCH')
            <label>Status</label>
            <select name="status" id="modalStatus">
                <option value="pending">Pending</option>
                <option value="in_review">In Review</option>
                <option value="planned">Planned</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
            </select>
            <label>Admin Note <span style="text-transform:none;font-weight:400">(optional, visible to user)</span></label>
            <textarea name="admin_notes" id="modalNotes" rows="3" placeholder="Leave a note for the user…"></textarea>
            <div class="afr-modal-btns">
                <button type="button" class="afr-modal-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="afr-modal-save">Save Changes</button>
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
