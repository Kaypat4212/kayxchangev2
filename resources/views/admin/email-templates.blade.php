@extends('adminnavlayout')

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}
body{background:var(--kx-dark);color:var(--kx-text);}
.kx-page-header{background:linear-gradient(135deg,#0a1628,#0d1f1a);border:1px solid var(--kx-border);border-radius:16px;padding:24px 28px;margin-bottom:24px;}
.kx-page-header h1{font-size:1.35rem;font-weight:700;color:#fff;margin:0 0 4px;}
.kx-page-header p{color:var(--kx-muted);font-size:.875rem;margin:0;}
.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1.25rem;}
.kx-card-title{font-size:.95rem;font-weight:700;color:var(--kx-text);margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;}
.kx-card-title i{color:var(--kx-green);}
.tpl-row{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:14px 18px;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.tpl-row:hover{border-color:rgba(0,204,0,.3);}
.tpl-key{font-family:monospace;font-size:.8rem;color:var(--kx-green);background:rgba(0,204,0,.08);padding:3px 8px;border-radius:6px;}
.tpl-desc{font-size:.82rem;color:var(--kx-muted);margin-top:4px;}
.tpl-subject{font-size:.875rem;color:var(--kx-text);font-weight:600;}
.btn-edit{background:var(--kx-green);border:none;color:#000;font-weight:700;font-size:.8rem;padding:6px 14px;border-radius:8px;text-decoration:none;white-space:nowrap;}
.btn-edit:hover{background:#00e600;color:#000;}
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    <div class="kx-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1><i class="fas fa-file-alt me-2" style="color:#00cc00;"></i>Email Templates</h1>
            <p>Customise the email messages sent to users for each event.</p>
        </div>
        <a href="{{ route('admin.email-settings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-cog me-1"></i>Email Settings
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success rounded-3 border-0 mb-3" style="background:rgba(0,204,0,.12);color:#00cc00;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    </div>
    @endif

    <div class="kx-card">
        <div class="kx-card-title"><i class="fas fa-envelope"></i> All Templates</div>

        <p style="font-size:.82rem;color:var(--kx-muted);margin-bottom:1rem;">
            Use <code style="background:rgba(0,204,0,.08);color:#00cc00;padding:2px 6px;border-radius:4px;">&#123;&#123;token&#125;&#125;</code>
            placeholders in subject &amp; body — they are replaced with real values when emails are sent.
        </p>

        @forelse($templates as $tpl)
        <div class="tpl-row">
            <div style="flex:1;min-width:0;">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <span class="tpl-key">{{ $tpl->key }}</span>
                    <span class="tpl-subject">{{ $tpl->subject }}</span>
                </div>
                @if($tpl->description)
                <div class="tpl-desc">{{ $tpl->description }}</div>
                @endif
            </div>
            <a href="{{ route('admin.email-templates.edit', $tpl->key) }}" class="btn-edit">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
        </div>
        @empty
        <p class="text-center" style="color:var(--kx-muted);">No templates found.</p>
        @endforelse
    </div>
</div>
@endsection
