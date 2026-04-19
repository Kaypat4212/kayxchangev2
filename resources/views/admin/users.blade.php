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
            <h4><i class="bi bi-people-fill me-2" style="color:var(--kx-green)"></i>Users Management</h4>
            <small>View and manage all registered users ({{ $users->count() }} total)</small>
        </div>
        <div class="d-flex gap-2">
            <div class="kx-search"><i class="bi bi-search" style="color:var(--kx-muted)"></i>
                <input class="kx-search-input" id="userSearch" placeholder="Search users…">
            </div>
        </div>
    </div>

    @if(session('success'))<div class="kx-alert-success"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="kx-alert-error"><i class="bi bi-exclamation-circle-fill"></i>{{ session('error') }}</div>@endif

    <div class="kx-panel">
        <div class="kx-panel-header">
            <span class="kx-panel-title"><i class="bi bi-people me-2"></i>All Users</span>
            <span style="font-size:.75rem;color:var(--kx-muted)">{{ $users->count() }} users</span>
        </div>
        <div class="kx-table-wrap">
            <table class="kx-table" id="usersTable">
                <thead><tr>
                    <th>#ID</th><th>Name</th><th>Email</th><th>Balance</th>
                    <th>KYC</th><th>Role</th><th>Joined</th><th>Actions</th>
                </tr></thead>
                <tbody>
                @forelse($users as $user)
                <tr>
                    <td><span style="color:var(--kx-muted)">#{{ $user->id }}</span></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.6rem">
                            <div style="width:32px;height:32px;border-radius:50%;background:var(--kx-card2);border:1px solid var(--kx-border);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:var(--kx-green);flex-shrink:0">
                                {{ strtoupper(substr($user->name,0,2)) }}
                            </div>
                            <span style="font-weight:600">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td style="color:var(--kx-muted)">{{ $user->email }}</td>
                    <td>₦{{ number_format($user->balance ?? 0, 2) }}</td>
                    <td>
                        @if($user->kyc_verified == 1)
                            <span class="kx-badge kx-badge-green"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                        @elseif(isset($user->kyc_verified) && $user->kyc_verified === 0)
                            <span class="kx-badge kx-badge-yellow"><i class="bi bi-clock me-1"></i>Pending</span>
                        @else
                            <span class="kx-badge kx-badge-gray">None</span>
                        @endif
                    </td>
                    <td>
                        @php($effectiveRole = $user->role ?: ($user->is_admin ? 'admin' : 'user'))
                        @if($effectiveRole === 'admin')
                            <span class="kx-badge kx-badge-purple"><i class="bi bi-shield-fill me-1"></i>Admin</span>
                        @elseif($effectiveRole === 'support')
                            <span class="kx-badge kx-badge-blue"><i class="bi bi-headset me-1"></i>Support</span>
                        @elseif($effectiveRole === 'manager')
                            <span class="kx-badge kx-badge-yellow"><i class="bi bi-briefcase-fill me-1"></i>Manager</span>
                        @elseif($effectiveRole === 'finance')
                            <span class="kx-badge kx-badge-green"><i class="bi bi-cash-stack me-1"></i>Finance</span>
                        @elseif($effectiveRole === 'compliance')
                            <span class="kx-badge kx-badge-red"><i class="bi bi-shield-check me-1"></i>Compliance</span>
                        @else
                            <span class="kx-badge kx-badge-gray">User</span>
                        @endif
                    </td>
                    <td style="font-size:.75rem;color:var(--kx-muted)">{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn-kx-outline" style="font-size:.72rem;padding:.25rem .6rem">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-kx-edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete {{ addslashes($user->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-kx-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;color:var(--kx-muted);padding:2.5rem">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('userSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endsection
