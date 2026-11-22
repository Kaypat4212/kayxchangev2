@extends('adminnavlayout')

@push('styles')
<style>
.md-shell{max-width:1440px;margin:auto;padding:24px clamp(12px,2vw,28px);color:#e8edf5}.md-hero,.md-card{border:1px solid rgba(255,255,255,.08);border-radius:18px;background:linear-gradient(145deg,#111a27,#10151f);box-shadow:0 14px 36px rgba(0,0,0,.16)}.md-hero{padding:24px;margin-bottom:18px;display:flex;justify-content:space-between;gap:16px;align-items:center;flex-wrap:wrap}.md-hero h1{font-size:1.4rem;margin:0 0 5px;color:#fff}.md-muted{color:#94a3b8;font-size:.84rem}.md-actions{display:flex;gap:8px;flex-wrap:wrap}.md-actions a,.md-btn{border:1px solid rgba(255,255,255,.12);border-radius:10px;background:#1d2838;color:#e8edf5;padding:9px 13px;text-decoration:none;font-size:.82rem;font-weight:650}.md-btn-primary{background:#19d163;color:#07140c;border-color:#19d163}.md-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}.md-stat{background:#131d2b;border:1px solid rgba(255,255,255,.07);border-radius:14px;padding:15px}.md-stat b{display:block;font-size:1.35rem;color:#fff}.md-stat span{font-size:.75rem;color:#94a3b8}.md-card{padding:18px;margin-bottom:18px}.md-card h2{font-size:1rem;color:#fff;margin:0 0 12px}.md-health{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px}.md-health div{padding:11px;border:1px solid rgba(255,255,255,.07);border-radius:11px;background:rgba(255,255,255,.025);font-size:.78rem}.md-pill{display:inline-flex;padding:4px 9px;border-radius:99px;font-size:.7rem;font-weight:750;text-transform:uppercase;letter-spacing:.04em}.md-pill.accepted{color:#62e99b;background:rgba(34,197,94,.12)}.md-pill.failed{color:#ff9797;background:rgba(239,68,68,.13)}.md-pill.sending,.md-pill.retrying{color:#f8c86b;background:rgba(245,158,11,.12)}.md-pill.retried{color:#a5b4fc;background:rgba(99,102,241,.15)}.md-filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}.md-filters input,.md-filters select{min-height:42px;border:1px solid rgba(255,255,255,.1);border-radius:9px;background:#101825;color:#e8edf5;padding:8px 11px}.md-table{width:100%;border-collapse:collapse;min-width:800px}.md-table th{font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;text-align:left;padding:10px;border-bottom:1px solid rgba(255,255,255,.1)}.md-table td{padding:12px 10px;border-bottom:1px solid rgba(255,255,255,.06);font-size:.8rem;vertical-align:top}.md-table tr:last-child td{border:0}.md-subject{font-weight:700;color:#f1f5f9;max-width:300px;overflow-wrap:anywhere}.md-error{color:#ffaaaa;font-size:.73rem;margin-top:5px;max-width:390px;overflow-wrap:anywhere}.md-small{color:#94a3b8;font-size:.72rem}.md-row-actions{display:flex;gap:6px;flex-wrap:wrap}.md-row-actions form{margin:0}.md-row-actions button{font-size:.72rem;padding:6px 9px}.md-ai-result{margin-top:10px;padding:12px;border-radius:10px;border:1px solid rgba(34,197,94,.2);background:rgba(34,197,94,.05);white-space:pre-wrap;font-size:.8rem;line-height:1.55}.md-guide{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.md-guide div{border:1px solid rgba(255,255,255,.07);border-radius:11px;padding:12px;background:rgba(255,255,255,.025);font-size:.8rem}.md-guide b{display:block;color:#e9f5ec;margin-bottom:4px}.md-warning{color:#f5d58a}.md-empty{text-align:center;padding:32px;color:#94a3b8}
@media(max-width:800px){.md-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.md-health{grid-template-columns:repeat(2,minmax(0,1fr))}.md-guide{grid-template-columns:1fr}.md-hero{padding:18px}.md-card{padding:14px}}
</style>
@endpush

@section('content')
<main class="md-shell">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <header class="md-hero">
        <div><div class="text-success small fw-bold text-uppercase mb-1">Email operations</div><h1><i class="bi bi-send-check me-2 text-success"></i>Mail delivery</h1><p class="md-muted mb-0">SMTP attempts recorded by the app. “Accepted” means the SMTP server accepted the message; it does not guarantee inbox delivery.</p></div>
        <div class="md-actions"><a href="{{ route('admin.email-settings.index') }}"><i class="bi bi-gear me-1"></i>SMTP settings</a><a href="{{ route('admin.logs.index') }}"><i class="bi bi-journal-text me-1"></i>Laravel logs</a><a href="{{ route('admin.email-settings.test') }}" onclick="event.preventDefault();document.getElementById('quick-test').scrollIntoView({behavior:'smooth'});"><i class="bi bi-envelope-check me-1"></i>Send test</a></div>
    </header>

    <section class="md-stats" aria-label="Mail delivery totals">
        <div class="md-stat"><b>{{ number_format($counts['total']) }}</b><span>Recorded attempts · last 90 days</span></div>
        <div class="md-stat"><b>{{ number_format($counts['accepted']) }}</b><span>Accepted by SMTP</span></div>
        <div class="md-stat"><b>{{ number_format($counts['failed']) }}</b><span>Failed / unresolved</span></div>
        <div class="md-stat"><b>{{ number_format($counts['sending']) }}</b><span>Currently sending</span></div>
    </section>

    <section class="md-card" aria-labelledby="smtp-health-title">
        <h2 id="smtp-health-title"><i class="bi bi-shield-check text-success me-2"></i>Configuration snapshot</h2>
        <div class="md-health">
            <div><span class="md-small d-block">Mailer</span><strong>{{ $settings?->mail_mailer ?: config('mail.default', 'not configured') }}</strong></div>
            <div><span class="md-small d-block">SMTP host</span><strong>{{ $settings?->mail_host ?: config('mail.mailers.smtp.host', 'not configured') }}</strong></div>
            <div><span class="md-small d-block">Port / encryption</span><strong>{{ $settings?->mail_port ?: config('mail.mailers.smtp.port', '—') }} · {{ strtoupper($settings?->mail_encryption ?: config('mail.mailers.smtp.encryption', 'none')) }}</strong></div>
            <div><span class="md-small d-block">Sender</span><strong>{{ $settings?->mail_from_address ?: config('mail.from.address', 'not configured') }}</strong></div>
        </div>
        <p class="md-small mt-3 mb-0">Credentials are never displayed or stored in this log. Email metadata is retained for 90 days. SMTP acceptance is not the same as recipient delivery or inbox placement.</p>
    </section>

    <section class="md-card" id="quick-test" aria-labelledby="smtp-guide-title">
        <h2 id="smtp-guide-title"><i class="bi bi-wrench-adjustable-circle text-warning me-2"></i>Common SMTP fixes</h2>
        <div class="md-guide">
            <div><b>550 spam / message discarded</b>Verify SPF, DKIM and DMARC for the sending domain. Make the From address match an authenticated sender and ask the SMTP provider to inspect its rejection event.</div>
            <div><b>Connection timeout / refused</b>Confirm host and port with the mail provider; use port 465 with implicit SSL or 587 with STARTTLS. Ask hosting support whether outbound SMTP is allowed.</div>
            <div><b>535 authentication failed</b>Re-enter the SMTP username and password. For Gmail, use an app password; some hosts require the full email address as username.</div>
            <div><b>Accepted but missing</b>Check spam/quarantine and provider message trace. Confirm DNS authentication and sender reputation. A 250 response means the SMTP relay accepted it, not that the inbox delivered it.</div>
        </div>
        <p class="md-muted mt-3 mb-0">The test form is on <a href="{{ route('admin.email-settings.index') }}" class="link-success">Email Settings</a>. Retry is deliberately limited to a safe SMTP test or admin digest; customer mail bodies and one-time links are not retained for replay.</p>
    </section>

    <section class="md-card" aria-labelledby="delivery-table-title">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3"><h2 id="delivery-table-title" class="mb-0"><i class="bi bi-clock-history text-success me-2"></i>Recent attempts</h2><span class="md-small">Most recent first · metadata only</span></div>
        <form method="GET" action="{{ route('admin.mail-delivery.index') }}" class="md-filters">
            <select name="status" aria-label="Filter by status"><option value="all">All statuses</option>@foreach(['sending','accepted','failed','retrying','retried'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? 'all') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="160" placeholder="Search address, subject, or error" aria-label="Search mail logs">
            <button class="md-btn md-btn-primary" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
            @if(!empty($filters['q']) || (!empty($filters['status']) && $filters['status'] !== 'all'))<a class="md-btn" href="{{ route('admin.mail-delivery.index') }}">Clear</a>@endif
        </form>

        <div class="table-responsive">
            <table class="md-table"><thead><tr><th>Time</th><th>Recipient / subject</th><th>Transport</th><th>Status</th><th>Actions</th></tr></thead><tbody>
            @forelse($logs as $log)
                <tr>
                    <td class="text-nowrap">{{ $log->created_at->format('d M Y, H:i:s') }}<div class="md-small">Attempt {{ $log->attempts }}</div></td>
                    <td><div>{{ $log->recipient ?: 'No recipient captured' }}</div><div class="md-subject">{{ $log->subject }}</div>@if($log->message_id)<div class="md-small">ID {{ $log->message_id }}</div>@endif @if($log->last_error)<div class="md-error">{{ $log->last_error }}</div>@endif</td>
                    <td>{{ $log->mailer ?: 'unknown' }}<div class="md-small">{{ $log->accepted_at ? 'SMTP accepted' : ($log->retry_type ? 'Safe retry available' : 'No message content stored') }}</div></td>
                    <td><span class="md-pill {{ $log->status }}">{{ $log->status }}</span></td>
                    <td>
                        <div class="md-row-actions">
                            @if($log->status === 'failed' && in_array($log->retry_type, ['smtp_test','daily_digest'], true) && $log->attempts < 3)
                                <form method="POST" action="{{ route('admin.mail-delivery.retry', $log) }}" onsubmit="return confirm('Send a new {{ $log->retry_type === 'smtp_test' ? 'SMTP test' : 'daily digest' }} to this recipient? This creates a fresh attempt.');">@csrf<button class="md-btn md-btn-primary" type="submit"><i class="bi bi-arrow-repeat me-1"></i>Retry safe email</button></form>
                            @elseif($log->status === 'failed' && $log->retry_type)<span class="md-small">Retry limit reached</span>
                            @elseif($log->status === 'failed')<span class="md-small" title="Content is not retained">Use the original app action to send again</span>@endif
                            @if(in_array($log->status, ['failed','sending'], true))<button class="md-btn" type="button" data-diagnose-url="{{ route('admin.mail-delivery.diagnose', $log) }}" data-result-id="mail-ai-{{ $log->id }}"><i class="bi bi-stars me-1"></i>AI diagnose</button>@endif
                        </div>
                        <div id="mail-ai-{{ $log->id }}" class="md-ai-result" hidden></div>
                    </td>
                </tr>
            @empty<tr><td class="md-empty" colspan="5">No mail attempts have been recorded yet. New outgoing messages will appear here after the mail log migration is applied.</td></tr>@endforelse
            </tbody></table>
        </div>
        <div class="mt-3">{{ $logs->links() }}</div>
    </section>
</main>

<script>
document.querySelectorAll('[data-diagnose-url]').forEach(button => button.addEventListener('click', async () => {
    const box = document.getElementById(button.dataset.resultId);
    button.disabled = true; box.hidden = false; box.textContent = 'Checking the redacted SMTP status with AI…';
    try {
        const response = await fetch(button.dataset.diagnoseUrl, {method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept':'application/json'}});
        const data = await response.json().catch(()=>({}));
        if (!response.ok) throw new Error(data.message || `Diagnosis failed (${response.status}).`);
        box.textContent = data.diagnosis + '\n\nAI provides suggestions only. Review each change in your mail provider and hosting settings.';
    } catch (error) { box.textContent = error.message || 'Diagnosis is unavailable. Use the troubleshooting guide above.'; }
    finally { button.disabled = false; }
}));
</script>
@endsection
