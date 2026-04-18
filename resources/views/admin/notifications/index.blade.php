@extends('adminnavlayout')
@section('content')
<style>
:root{--kx-green:#00cc00;--kx-gdim:rgba(0,204,0,.12);--kx-glow:rgba(0,204,0,.22);
--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,.07);
--kx-text:#e4e8f0;--kx-muted:#7a8599;--kx-red:#ef4444;--kx-yellow:#f59e0b;
--kx-blue:#38bdf8;--kx-purple:#a855f7;}
body{background:var(--kx-dark)!important;color:var(--kx-text)!important;font-family:'Poppins',sans-serif;}
.kx-page-header{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:12px;
padding:1rem 1.4rem;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;}
.kx-page-header h4{margin:0;font-size:1rem;font-weight:700;color:#fff;}
.kx-page-header small{font-size:.75rem;color:var(--kx-muted);}
.kx-panel{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:12px;margin-bottom:1.25rem;overflow:hidden;}
.kx-panel-header{padding:.875rem 1.25rem;border-bottom:1px solid var(--kx-border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;background:var(--kx-card2);}
.kx-panel-title{font-size:.9rem;font-weight:600;color:#fff;margin:0;}
.kx-table{width:100%;border-collapse:collapse;}
.kx-table thead th{background:var(--kx-card2);color:var(--kx-muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;
padding:.7rem 1rem;border-bottom:1px solid var(--kx-border);white-space:nowrap;font-weight:600;}
.kx-table tbody tr{border-bottom:1px solid var(--kx-border);transition:background .15s;}
.kx-table tbody tr:hover{background:rgba(255,255,255,.02);}
.kx-table tbody tr:last-child{border-bottom:none;}
.kx-table td{padding:.75rem 1rem;font-size:.83rem;color:var(--kx-text);vertical-align:middle;}
.kx-table-wrap{overflow-x:auto;}
.kx-badge{display:inline-flex;align-items:center;padding:.2rem .6rem;border-radius:20px;font-size:.7rem;font-weight:600;}
.kx-badge-green{background:rgba(0,204,0,.12);color:var(--kx-green);}
.kx-badge-red{background:rgba(239,68,68,.12);color:var(--kx-red);}
.kx-badge-yellow{background:rgba(245,158,11,.12);color:var(--kx-yellow);}
.kx-badge-blue{background:rgba(56,189,248,.12);color:var(--kx-blue);}
.kx-badge-purple{background:rgba(168,85,247,.12);color:var(--kx-purple);}
.kx-badge-gray{background:rgba(255,255,255,.06);color:var(--kx-muted);}
.btn-kx-green{background:var(--kx-green);color:#000;border:none;border-radius:8px;font-weight:600;font-size:.8rem;padding:.45rem 1rem;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;}
.btn-kx-green:hover{background:#00e600;color:#000;}
.btn-kx-outline{background:transparent;color:var(--kx-text);border:1px solid var(--kx-border);font-size:.8rem;padding:.45rem 1rem;border-radius:8px;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;cursor:pointer;}
.btn-kx-outline:hover{background:var(--kx-card2);color:#fff;border-color:rgba(255,255,255,.2);}
.btn-kx-danger{background:transparent;color:var(--kx-red);border:1px solid rgba(239,68,68,.3);font-size:.75rem;padding:.3rem .7rem;border-radius:7px;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;text-decoration:none;}
.btn-kx-danger:hover{background:rgba(239,68,68,.1);color:var(--kx-red);}
.btn-kx-edit{background:transparent;color:var(--kx-blue);border:1px solid rgba(56,189,248,.3);font-size:.75rem;padding:.3rem .7rem;border-radius:7px;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;text-decoration:none;}
.btn-kx-edit:hover{background:rgba(56,189,248,.1);color:var(--kx-blue);}
.btn-kx-approve{background:transparent;color:var(--kx-green);border:1px solid rgba(0,204,0,.3);font-size:.75rem;padding:.3rem .7rem;border-radius:7px;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;}
.btn-kx-approve:hover{background:var(--kx-gdim);color:var(--kx-green);}
.kx-input{background:var(--kx-card2)!important;border:1px solid var(--kx-border)!important;color:var(--kx-text)!important;border-radius:8px!important;padding:.5rem .85rem!important;font-size:.83rem!important;}
.kx-input:focus{border-color:rgba(0,204,0,.4)!important;box-shadow:0 0 0 2px rgba(0,204,0,.1)!important;color:#fff!important;outline:none!important;}
.kx-input::placeholder{color:var(--kx-muted)!important;}
select.kx-input option{background:var(--kx-card2);color:var(--kx-text);}
.kx-label{font-size:.75rem;color:var(--kx-muted);display:block;margin-bottom:.35rem;text-transform:uppercase;letter-spacing:.04em;}
.kx-alert-success{background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);color:var(--kx-green);border-radius:8px;padding:.75rem 1rem;font-size:.84rem;display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;}
.kx-alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:var(--kx-red);border-radius:8px;padding:.75rem 1rem;font-size:.84rem;display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;}
.modal-content{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;color:var(--kx-text);}
.modal-header{border-bottom:1px solid var(--kx-border);}
.modal-footer{border-top:1px solid var(--kx-border);}
.kx-stat-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.875rem;margin-bottom:1.25rem;}
.kx-stat{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:12px;padding:1rem 1.25rem;display:flex;align-items:center;gap:1rem;}
.kx-stat-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.icon-green{background:var(--kx-gdim);color:var(--kx-green);}
.icon-yellow{background:rgba(245,158,11,.15);color:var(--kx-yellow);}
.icon-blue{background:rgba(56,189,248,.12);color:var(--kx-blue);}
.icon-red{background:rgba(239,68,68,.12);color:var(--kx-red);}
.icon-purple{background:rgba(168,85,247,.12);color:var(--kx-purple);}
.kx-stat-label{font-size:.7rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;}
.kx-stat-value{font-size:1.4rem;font-weight:700;color:#fff;line-height:1.1;}
.kx-search{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;display:flex;align-items:center;padding:.45rem .75rem;gap:.5rem;}
.kx-search-input{background:transparent;border:none;outline:none;color:var(--kx-text);font-size:.83rem;flex:1;}
.kx-search-input::placeholder{color:var(--kx-muted);}
</style>
<div class="container-fluid py-3 px-3 px-md-4">
    <div class="kx-page-header">
        <div>
            <h4><i class="bi bi-bell-fill me-2" style="color:var(--kx-green)"></i>Notifications</h4>
            <small>Send and manage system notifications to users</small>
        </div>
        <button class="btn-kx-green" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-lg me-1"></i>New Notification
        </button>
    </div>

    @if(session('success'))<div class="kx-alert-success"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="kx-alert-error"><i class="bi bi-exclamation-circle-fill"></i>{{ session('error') }}</div>@endif

    <div class="kx-stat-row">
        <div class="kx-stat"><div class="kx-stat-icon icon-blue"><i class="bi bi-bell"></i></div>
            <div><div class="kx-stat-label">Total</div><div class="kx-stat-value">{{ $stats['total'] }}</div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-yellow"><i class="bi bi-envelope-fill"></i></div>
            <div><div class="kx-stat-label">Unread</div><div class="kx-stat-value">{{ $stats['unread'] }}</div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-purple"><i class="bi bi-broadcast"></i></div>
            <div><div class="kx-stat-label">Broadcast</div><div class="kx-stat-value">{{ $stats['broadcast'] }}</div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-green"><i class="bi bi-calendar-check"></i></div>
            <div><div class="kx-stat-label">Today</div><div class="kx-stat-value">{{ $stats['today'] }}</div></div></div>
    </div>

    <div class="kx-panel">
        <div class="kx-panel-header">
            <span class="kx-panel-title"><i class="bi bi-list-ul me-2"></i>All Notifications</span>
        </div>
        <div class="kx-table-wrap">
            <table class="kx-table">
                <thead><tr>
                    <th>#</th><th>Title</th><th>Message</th><th>Type</th><th>Target</th><th>Date</th><th>Actions</th>
                </tr></thead>
                <tbody>
                @php $list = $notifications->items() ?? []; @endphp
                @forelse($list as $n)
                <tr>
                    <td style="color:var(--kx-muted)">#{{ $n->id }}</td>
                    <td>
                        <span style="font-weight:600">{{ $n->title }}</span>
                        @if(!($n->is_read ?? true))
                            <span class="kx-badge kx-badge-yellow ms-1" style="font-size:.62rem">Unread</span>
                        @endif
                    </td>
                    <td style="max-width:240px;font-size:.78rem;color:var(--kx-muted)">{{ Str::limit($n->message, 70) }}</td>
                    <td>
                        @if($n->is_broadcast ?? false)
                            <span class="kx-badge kx-badge-blue"><i class="bi bi-broadcast me-1"></i>Broadcast</span>
                        @else
                            <span class="kx-badge kx-badge-gray">Personal</span>
                        @endif
                    </td>
                    <td style="font-size:.78rem">{{ isset($n->user) ? $n->user->name : 'All Users' }}</td>
                    <td style="font-size:.75rem;color:var(--kx-muted)">{{ $n->created_at ? $n->created_at->diffForHumans() : '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <form action="{{ route('admin.notifications.delete', $n->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this notification?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-kx-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:var(--kx-muted);padding:2.5rem">No notifications yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($notifications,'hasPages') && $notifications->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--kx-border)">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>

{{-- CREATE MODAL --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:.95rem;font-weight:600;color:#fff"><i class="bi bi-bell-plus me-2" style="color:var(--kx-green)"></i>New Notification</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.notifications.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:1.25rem">
                    <div class="mb-3">
                        <label class="kx-label">Title *</label>
                        <input type="text" name="title" class="form-control kx-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="kx-label">Message *</label>
                        <textarea name="message" class="form-control kx-input" rows="4" style="height:auto;resize:vertical" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="kx-label">Type</label>
                            <select name="type" class="form-control kx-input">
                                <option value="info">Info</option>
                                <option value="success">Success</option>
                                <option value="warning">Warning</option>
                                <option value="error">Error</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="kx-label">Target User (leave blank = broadcast)</label>
                            <select name="user_id" class="form-control kx-input">
                                <option value="">All Users (Broadcast)</option>
                                @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="kx-label">Icon (Bootstrap Icons class)</label>
                            <input type="text" name="icon" class="form-control kx-input" placeholder="bi-bell" value="bi-bell">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-kx-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-kx-green"><i class="bi bi-send me-1"></i>Send Notification</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── AI Notification Copy Generator ─────────────────────────────────────── --}}
<div class="kx-panel">
    <div class="kx-panel-header"><span class="kx-panel-title"><i class="bi bi-robot me-2" style="color:var(--kx-green)"></i>AI Notification Copy Generator</span></div>
    <div style="padding:1.25rem">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="kx-label">User Segment</label>
                <input id="ai-notif-segment" type="text" class="kx-input" placeholder="e.g. Users with pending KYC">
            </div>
            <div class="col-md-5">
                <label class="kx-label">Goal / Context</label>
                <input id="ai-notif-context" type="text" class="kx-input" placeholder="e.g. Remind them to complete verification to enable trading">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button id="ai-notif-btn" onclick="aiGenerateNotifCopy()" class="btn-kx-green w-100">
                    <i class="bi bi-robot me-1"></i>Generate Copy
                </button>
            </div>
        </div>
        <div id="ai-notif-out" style="margin-top:1rem;display:none">
            <p style="font-size:.75rem;color:var(--kx-muted);margin-bottom:.5rem">Click a variant to use it as the notification title:</p>
            <div id="ai-notif-variants" style="display:flex;flex-direction:column;gap:.5rem"></div>
        </div>
    </div>
</div>

<script>
async function aiGenerateNotifCopy(){
    const segment  = document.getElementById('ai-notif-segment').value.trim();
    const context  = document.getElementById('ai-notif-context').value.trim();
    const btn      = document.getElementById('ai-notif-btn');
    const out      = document.getElementById('ai-notif-out');
    const variants = document.getElementById('ai-notif-variants');
    if (!segment || !context) { alert('Please fill in both Segment and Goal fields.'); return; }
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating…';
    out.style.display = 'none';
    try {
        const res  = await fetch('{{ route("ai.notification-copy") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({segment, context})
        });
        const data = await res.json();
        if (data.error) { alert('AI Error: '+data.error); return; }
        const v = data.variants || [];
        variants.innerHTML = v.map((t,i) =>
            `<button type="button" class="btn-kx-outline" style="text-align:left;justify-content:flex-start;font-size:.82rem;padding:.55rem .875rem"
                onclick="document.getElementById('notifModal').querySelector('[name=title]').value = this.dataset.text;
                         bootstrap.Modal.getInstance(document.getElementById('notifModal'))?.show();"
                data-text="${t.replace(/"/g,'&quot;')}">
                <span style="color:var(--kx-muted);font-size:.72rem;margin-right:.5rem">${i+1}.</span>${t}
            </button>`
        ).join('');
        out.style.display = 'block';
    } catch(e) {
        alert('Request failed: '+e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-robot me-1"></i>Generate Copy';
    }
}
</script>
@endsection
