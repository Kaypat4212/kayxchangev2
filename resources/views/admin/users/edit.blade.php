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
            <h4><i class="bi bi-pencil-square me-2" style="color:var(--kx-green)"></i>Edit User</h4>
            <small>Editing: {{ $user->name }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.show', $user->id) }}" class="btn-kx-outline"><i class="bi bi-eye me-1"></i>View</a>
            <a href="{{ route('admin.users.index') }}" class="btn-kx-outline"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    @if(session('success'))<div class="kx-alert-success"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="kx-alert-error"><i class="bi bi-exclamation-circle-fill"></i>{{ session('error') }}</div>@endif

    <div class="row g-3">
        <div class="col-lg-7">
            {{-- Basic Info --}}
            <div class="kx-panel">
                <div class="kx-panel-header"><span class="kx-panel-title"><i class="bi bi-person me-2"></i>User Information</span></div>
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" style="padding:1.25rem">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="kx-label">Name</label>
                            <input type="text" name="name" class="form-control kx-input" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div style="color:var(--kx-red);font-size:.75rem;margin-top:.3rem">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="kx-label">Email</label>
                            <input type="email" name="email" class="form-control kx-input" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div style="color:var(--kx-red);font-size:.75rem;margin-top:.3rem">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="kx-label">Balance (₦)</label>
                            <input type="number" name="balance" class="form-control kx-input" step="0.01" min="0" value="{{ old('balance', $user->balance ?? 0) }}" required>
                            @error('balance')<div style="color:var(--kx-red);font-size:.75rem;margin-top:.3rem">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="kx-label">Role</label>
                            @php($activeRole = old('role', $user->role ?: ($user->is_admin ? 'admin' : 'user')))
                            <select name="role" class="form-control kx-input" required>
                                <option value="user" {{ $activeRole === 'user' ? 'selected' : '' }}>User</option>
                                <option value="support" {{ $activeRole === 'support' ? 'selected' : '' }}>Support</option>
                                <option value="manager" {{ $activeRole === 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="finance" {{ $activeRole === 'finance' ? 'selected' : '' }}>Finance</option>
                                <option value="compliance" {{ $activeRole === 'compliance' ? 'selected' : '' }}>Compliance</option>
                                <option value="admin" {{ $activeRole === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')<div style="color:var(--kx-red);font-size:.75rem;margin-top:.3rem">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <div style="padding:.75rem .85rem;border:1px solid var(--kx-border);border-radius:8px;background:var(--kx-card2);">
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" value="1" id="is_admin" name="is_admin" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_admin" style="color:var(--kx-text);font-size:.85rem;">
                                        Grant admin access for this user
                                    </label>
                                </div>
                            </div>
                            @error('is_admin')<div style="color:var(--kx-red);font-size:.75rem;margin-top:.3rem">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn-kx-green"><i class="bi bi-save me-1"></i>Update User</button>
                        <a href="{{ route('admin.users.index') }}" class="btn-kx-outline">Cancel</a>
                    </div>
                </form>
            </div>

            {{-- Balance Adjustment --}}
            <div class="kx-panel">
                <div class="kx-panel-header"><span class="kx-panel-title"><i class="bi bi-wallet2 me-2" style="color:var(--kx-yellow)"></i>Adjust Balance</span>
                    <span style="font-size:.8rem;color:var(--kx-green);font-weight:600">Current: ₦{{ number_format($user->balance ?? 0, 2) }}</span>
                </div>
                <form action="{{ route('admin.users.balance.adjust', $user->id) }}" method="POST" style="padding:1.25rem">
                    @csrf @method('PATCH')
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="kx-label">Amount (₦)</label>
                            <input type="number" name="amount" class="form-control kx-input" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6 d-flex gap-2">
                            <button type="submit" name="action" value="add" class="btn-kx-approve" style="flex:1;justify-content:center">
                                <i class="bi bi-plus-circle me-1"></i>Add
                            </button>
                            <button type="submit" name="action" value="subtract" class="btn-kx-danger" style="flex:1;justify-content:center">
                                <i class="bi bi-dash-circle me-1"></i>Subtract
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            {{-- Profile Card --}}
            <div class="kx-panel">
                <div class="kx-panel-header"><span class="kx-panel-title">Profile Summary</span></div>
                <div style="padding:1.25rem">
                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
                        <div style="width:48px;height:48px;border-radius:50%;background:var(--kx-gdim);border:1px solid var(--kx-green);display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;color:var(--kx-green);flex-shrink:0">
                            {{ strtoupper(substr($user->name,0,2)) }}
                        </div>
                        <div>
                            <div style="font-weight:600;color:#fff">{{ $user->name }}</div>
                            <div style="font-size:.75rem;color:var(--kx-muted)">ID #{{ $user->id }}</div>
                        </div>
                    </div>
                    <div style="font-size:.8rem;color:var(--kx-muted);display:flex;flex-direction:column;gap:.5rem">
                        <div><i class="bi bi-envelope me-2"></i>{{ $user->email }}</div>
                        <div><i class="bi bi-calendar me-2"></i>Joined {{ $user->created_at->format('d M Y') }}</div>
                        <div><i class="bi bi-clock me-2"></i>Updated {{ $user->updated_at->diffForHumans() }}</div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.users.backdoor', $user->id) }}"
                           class="btn-kx-danger" style="width:100%;justify-content:center;padding:.5rem"
                           onclick="return confirm('Log in as this user?')">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Access User Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
