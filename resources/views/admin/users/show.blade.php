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
            <h4><i class="bi bi-person-circle me-2" style="color:var(--kx-green)"></i>User Profile</h4>
            <small>Viewing details for {{ $user->name }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-kx-green"><i class="bi bi-pencil me-1"></i>Edit</a>
            <a href="{{ route('admin.users.index') }}" class="btn-kx-outline"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="kx-panel">
                <div class="kx-panel-header"><span class="kx-panel-title">Profile</span></div>
                <div style="padding:1.5rem;text-align:center">
                    <div style="width:72px;height:72px;border-radius:50%;background:var(--kx-gdim);border:2px solid var(--kx-green);display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:700;color:var(--kx-green);margin:0 auto 1rem">
                        {{ strtoupper(substr($user->name,0,2)) }}
                    </div>
                    <div style="font-size:1.1rem;font-weight:700;color:#fff">{{ $user->name }}</div>
                    <div style="font-size:.8rem;color:var(--kx-muted);margin:.3rem 0 1rem">{{ $user->email }}</div>
                    @if($user->is_admin)
                        <span class="kx-badge kx-badge-purple"><i class="bi bi-shield-fill me-1"></i>Admin</span>
                    @else
                        <span class="kx-badge kx-badge-gray">User</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="kx-panel">
                <div class="kx-panel-header"><span class="kx-panel-title">Account Details</span></div>
                <div style="padding:1.25rem">
                    <div class="row g-3">
                        <div class="col-6">
                            <div style="font-size:.72rem;color:var(--kx-muted);text-transform:uppercase">Balance</div>
                            <div style="font-size:1.3rem;font-weight:700;color:var(--kx-green)">₦{{ number_format($user->balance ?? 0, 2) }}</div>
                        </div>
                        <div class="col-6">
                            <div style="font-size:.72rem;color:var(--kx-muted);text-transform:uppercase">KYC Status</div>
                            <div class="mt-1">
                                @if($user->kyc_verified == 1)
                                    <span class="kx-badge kx-badge-green"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                                @elseif(isset($user->kyc_verified) && $user->kyc_verified === 0)
                                    <span class="kx-badge kx-badge-yellow">Pending</span>
                                @else
                                    <span class="kx-badge kx-badge-gray">Not submitted</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="font-size:.72rem;color:var(--kx-muted);text-transform:uppercase">Joined</div>
                            <div style="font-size:.85rem;color:var(--kx-text)">{{ $user->created_at->format('d M Y, H:i') }}</div>
                        </div>
                        <div class="col-6">
                            <div style="font-size:.72rem;color:var(--kx-muted);text-transform:uppercase">Last Updated</div>
                            <div style="font-size:.85rem;color:var(--kx-text)">{{ $user->updated_at->diffForHumans() }}</div>
                        </div>
                        @if($user->referral_code)
                        <div class="col-6">
                            <div style="font-size:.72rem;color:var(--kx-muted);text-transform:uppercase">Referral Code</div>
                            <div style="font-size:.85rem;font-family:monospace;color:var(--kx-green)">{{ $user->referral_code }}</div>
                        </div>
                        @endif
                    </div>
                    <div class="mt-3 d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.users.backdoor', $user->id) }}"
                           class="btn-kx-outline" style="font-size:.78rem"
                           onclick="return confirm('Log in as {{ addslashes($user->name) }}?')">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Access Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== Badges Section ===== -->
@php
    $allBadges = \App\Models\Badge::orderBy('sort_order')->get();
    $userBadgeIds = $user->badges()->pluck('badges.id')->toArray();
@endphp
<div class="container-fluid" style="max-width:960px;padding:0 1rem 3rem;">
    <div class="kx-panel">
        <div class="kx-panel-header">
            <span class="kx-panel-title"><i class="bi bi-award-fill" style="color:#fbbf24"></i> Badges & Achievements</span>
            <span style="font-size:.75rem;color:var(--kx-muted)">{{ count($userBadgeIds) }} earned</span>
        </div>
        <div style="padding:1.25rem">

            {{-- Current badges --}}
            @if(count($userBadgeIds))
            <div style="margin-bottom:1.5rem">
                <div style="font-size:.7rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem">Earned Badges</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($user->badges()->with('pivot')->get() as $badge)
                    <div class="d-flex align-items-center gap-2" style="background:rgba(0,204,0,.06);border:1px solid rgba(0,204,0,.15);border-radius:10px;padding:.4rem .8rem;">
                        <span style="font-size:1.2rem">{{ $badge->emoji }}</span>
                        <div>
                            <div style="font-size:.75rem;font-weight:600;color:{{ $badge->color ?? '#00cc00' }}">{{ $badge->name }}</div>
                            <div style="font-size:.62rem;color:var(--kx-muted)">{{ ucfirst($badge->rarity) }}</div>
                        </div>
                        {{-- Revoke button --}}
                        <form method="POST" action="{{ route('admin.badges.revoke', [$user, $badge]) }}" style="margin-left:.5rem" onsubmit="return confirm('Revoke badge {{ addslashes($badge->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="font-size:.6rem;background:rgba(239,68,68,.15);color:#f87171;border:none;border-radius:6px;padding:.15rem .45rem;cursor:pointer;" title="Revoke badge">✕</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Award a badge --}}
            <form method="POST" action="{{ route('admin.badges.award', $user) }}" class="d-flex align-items-end gap-3 flex-wrap">
                @csrf
                <div style="flex:1;min-width:200px">
                    <label style="font-size:.7rem;color:var(--kx-muted);text-transform:uppercase;display:block;margin-bottom:.4rem">Award Badge</label>
                    <select name="badge_id" class="form-select form-select-sm" style="background:#1a2e1a;color:#e8f5e8;border-color:rgba(255,255,255,.12)">
                        <option value="">-- Select a badge --</option>
                        @foreach($allBadges as $badge)
                        <option value="{{ $badge->id }}" {{ in_array($badge->id, $userBadgeIds) ? 'disabled' : '' }}>
                            {{ $badge->emoji }} {{ $badge->name }} ({{ ucfirst($badge->rarity) }}){{ in_array($badge->id, $userBadgeIds) ? ' ✓' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-kx-outline" style="font-size:.78rem;padding:.4rem 1rem">
                    <i class="bi bi-award me-1"></i> Award
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
