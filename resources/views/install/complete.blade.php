@extends('adminnavlayout')

@section('content')
<style>
.install-status-page{max-width:1000px;margin:0 auto;padding:1.5rem;color:#e5e7eb}
.install-status-hero{padding:1.5rem;border-radius:18px;margin-bottom:1.25rem;background:linear-gradient(130deg,#102a1b,#142338);border:1px solid rgba(148,163,184,.2)}
.install-status-hero h1{color:#fff;font-size:clamp(1.4rem,3vw,2rem);font-weight:800;margin:.4rem 0}
.install-status-hero p{color:#aab6c6;margin:0}
.install-status-card{border-radius:16px;background:#111827;border:1px solid rgba(148,163,184,.16);overflow:hidden}
.install-check{display:flex;gap:1rem;align-items:flex-start;padding:1rem 1.2rem;border-bottom:1px solid rgba(148,163,184,.12)}
.install-check:last-child{border-bottom:0}.install-check-icon{width:2rem;height:2rem;flex:0 0 2rem;border-radius:50%;display:grid;place-items:center;font-weight:800}
.install-check.pass .install-check-icon{background:rgba(34,197,94,.15);color:#4ade80}.install-check.fail .install-check-icon{background:rgba(239,68,68,.15);color:#f87171}.install-check.optional .install-check-icon{background:rgba(251,191,36,.15);color:#fbbf24}
.install-check strong{display:block;color:#f8fafc;margin:.05rem 0 .2rem}.install-check small{color:#94a3b8;line-height:1.5}
.install-status-actions{display:flex;flex-wrap:wrap;gap:.7rem;margin-top:1.2rem}
</style>
<main class="install-status-page">
    <section class="install-status-hero">
        <div style="font-size:.76rem;letter-spacing:.12em;text-transform:uppercase;color:{{ $installationComplete ? '#4ade80' : '#fbbf24' }};font-weight:800">KayXchange setup status</div>
        <h1>{{ $installationComplete ? 'Site installed successfully' : 'Installation needs attention' }}</h1>
        <p>{{ $installationComplete ? 'All required checks passed. The installer is locked and the platform is ready for admin configuration.' : 'Some checks are not passing yet. Fix the items below before treating the site as fully installed.' }}</p>
    </section>

    <section class="install-status-card" aria-label="Installation checks">
        @foreach($checks as $check)
            @php($checkState = $check['state'] ?? ($check['passed'] ? 'ready' : 'fail'))
            <div class="install-check {{ $checkState === 'optional' ? 'optional' : ($check['passed'] ? 'pass' : 'fail') }}">
                <div class="install-check-icon" aria-hidden="true">{{ $checkState === 'optional' ? '–' : ($check['passed'] ? '✓' : '!') }}</div>
                <div><strong>{{ $check['label'] }}</strong><small>{{ $check['detail'] }}</small></div>
            </div>
        @endforeach
    </section>

    <div class="install-status-actions">
        <a href="{{ route('admin.enhanced-dashboard') }}" class="btn btn-primary"><i class="bi bi-speedometer2 me-1"></i>Open admin dashboard</a>
        <a href="{{ route('home') }}" class="btn btn-outline-light"><i class="bi bi-house me-1"></i>View site</a>
        <a href="{{ route('install.status') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-code-slash me-1"></i>View status JSON</a>
    </div>
    <p class="text-muted small mt-3 mb-0">This checklist is only visible to authenticated administrators. Optional integrations such as payment providers, AI, and Telegram can be configured separately.</p>
</main>
@endsection
