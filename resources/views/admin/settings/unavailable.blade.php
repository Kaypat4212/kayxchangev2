@extends('adminnavlayout')

@section('title', 'Settings unavailable')

@section('content')
<style>
.settings-recovery{max-width:980px;margin:clamp(1rem,4vw,3rem) auto;padding:0 1rem;color:#e7edf4}
.settings-hero{position:relative;overflow:hidden;border:1px solid rgba(148,163,184,.17);border-radius:24px;padding:clamp(1.4rem,4vw,2.5rem);background:radial-gradient(ellipse at 93% 0,rgba(251,191,36,.15),transparent 36%),linear-gradient(135deg,#101824,#111d1a);box-shadow:0 22px 60px rgba(0,0,0,.22)}
.settings-badge{display:inline-flex;align-items:center;gap:.45rem;padding:.38rem .7rem;border-radius:999px;background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.25);color:#fcd34d;font-size:.72rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase}
.settings-hero h1{font-size:clamp(1.55rem,4vw,2.1rem);font-weight:800;letter-spacing:-.035em;color:#fff;margin:1rem 0 .55rem}
.settings-hero p{max-width:690px;color:#a8b5c3;line-height:1.7;margin:0}
.settings-recovery-card{margin-top:1.1rem;padding:clamp(1.1rem,3vw,1.7rem);border:1px solid rgba(148,163,184,.16);border-radius:18px;background:#121a24}
.settings-recovery-card h2{font-size:1.02rem;font-weight:750;color:#f1f5f9;margin:0 0 .7rem}
.settings-recovery-card p,.settings-recovery-card li{color:#aab6c4;line-height:1.65;font-size:.92rem}
.settings-command{display:block;overflow:auto;padding:.9rem 1rem;border-radius:11px;background:#080d13;border:1px solid rgba(148,163,184,.17);color:#8ce5a7;font:600 .88rem/1.5 ui-monospace,SFMono-Regular,Consolas,monospace;user-select:all}
.settings-recovery-actions{display:flex;gap:.7rem;flex-wrap:wrap;margin-top:1.25rem}
.settings-recovery-actions a{display:inline-flex;align-items:center;justify-content:center;min-height:43px;padding:.6rem 1rem;border-radius:10px;text-decoration:none;font-weight:700}
.settings-primary{background:#22c55e;color:#07130b}.settings-secondary{background:rgba(255,255,255,.05);border:1px solid rgba(148,163,184,.2);color:#dce5ed}
</style>

<main class="settings-recovery">
    <section class="settings-hero">
        <span class="settings-badge"><i class="bi bi-database-exclamation"></i> Settings service unavailable</span>
        <h1>Your settings are safe</h1>
        <p>The settings panel could not reach its configuration storage. This can happen when the settings migration has not run or when the database is temporarily unavailable. No credentials are shown on this page.</p>
    </section>

    <section class="settings-recovery-card">
        <h2>Restore the settings table</h2>
        <p>From the project directory in your hosting terminal, run the Laravel migrations:</p>
        <code class="settings-command">php artisan migrate --force</code>
        <p class="mt-3 mb-0">If migrations have already run, check the database connection in your production environment and confirm the <code>admin_settings</code> table exists. Then reload this page.</p>
        <div class="settings-recovery-actions">
            <a class="settings-primary" href="{{ route('admin.settings.index') }}"><i class="bi bi-arrow-clockwise me-2"></i>Retry settings</a>
            <a class="settings-secondary" href="{{ route('admin.terminal') }}"><i class="bi bi-terminal me-2"></i>Open admin terminal</a>
        </div>
    </section>
</main>
@endsection
