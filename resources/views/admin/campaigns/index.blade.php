@extends('adminnavlayout')

@section('title', 'User Campaigns')

@section('content')
<style>
.campaign-wrap{max-width:1400px;margin:0 auto;padding:1.5rem;color:var(--kx-text,#e5e7eb)}
.campaign-card{background:var(--kx-card,#111827);border:1px solid rgba(255,255,255,.09);border-radius:14px;padding:1.25rem;height:100%}
.campaign-muted{color:var(--kx-muted,#9ca3af);font-size:.86rem}
.campaign-field{background:#0b1220!important;color:#e5e7eb!important;border-color:rgba(255,255,255,.15)!important}
.campaign-field option{background:#0b1220;color:#e5e7eb}
</style>
<div class="campaign-wrap">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div><h1 class="h3 mb-1">User campaigns</h1><div class="campaign-muted">Send an in-app update to customers or email to people who opted into the newsletter.</div></div>
        <a href="{{ route('admin.newsletter.index') }}" class="btn btn-outline-secondary"><i class="bi bi-envelope me-1"></i>Newsletter subscribers</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="campaign-card">
                <h2 class="h5 mb-1"><i class="bi bi-send me-2"></i>Create campaign</h2>
                <p class="campaign-muted mb-4">In-app campaigns appear in the recipients’ notification bell. Email is limited to active newsletter subscribers.</p>
                <form method="POST" action="{{ route('admin.campaigns.send') }}" id="campaign-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Delivery channel</label>
                        <select class="form-select campaign-field" name="channel" id="campaign-channel" required>
                            <option value="in_app" @selected(old('channel','in_app') === 'in_app')>In-app notification</option>
                            <option value="email" @selected(old('channel') === 'email')>Email (opted-in newsletter only)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Audience</label>
                        <select class="form-select campaign-field" name="audience" id="campaign-audience" required>
                            <option value="users" data-channel="in_app" data-count="{{ $counts['users'] }}" @selected(old('audience','transacting_users') === 'users')>All registered users ({{ number_format($counts['users']) }})</option>
                            <option value="transacting_users" data-channel="in_app" data-count="{{ $counts['transacting_users'] }}" @selected(old('audience','transacting_users') === 'transacting_users')>Users with completed transactions ({{ number_format($counts['transacting_users']) }})</option>
                            <option value="newsletter_subscribers" data-channel="email" data-count="{{ $counts['newsletter_subscribers'] }}" @selected(old('audience') === 'newsletter_subscribers')>Active newsletter subscribers ({{ number_format($counts['newsletter_subscribers']) }})</option>
                        </select>
                        <div class="campaign-muted mt-1" id="audience-count"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject / title</label>
                        <input class="form-control campaign-field" name="title" value="{{ old('title') }}" maxlength="160" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control campaign-field" name="body" rows="7" maxlength="5000" required>{{ old('body') }}</textarea>
                        <div class="campaign-muted mt-1">Messages are saved to campaign history. Email is sent as plain text and includes an unsubscribe link.</div>
                    </div>
                    <button class="btn btn-success w-100" type="submit" id="campaign-submit"><i class="bi bi-send-fill me-1"></i>Send campaign</button>
                </form>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="campaign-card">
                <h2 class="h5 mb-3">Campaign history</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead><tr><th>Campaign</th><th>Channel / audience</th><th>Delivery</th><th>Sent</th></tr></thead>
                        <tbody>
                        @forelse($campaigns as $campaign)
                            <tr>
                                <td><strong>{{ $campaign->title }}</strong><div class="campaign-muted text-truncate" style="max-width:260px">{{ $campaign->body }}</div></td>
                                <td>{{ $campaign->channel === 'email' ? 'Email' : 'In-app' }}<div class="campaign-muted">{{ str_replace('_',' ',ucfirst($campaign->audience)) }}</div></td>
                                <td>{{ number_format($campaign->sent_count) }} / {{ number_format($campaign->target_count) }}<div class="campaign-muted">{{ $campaign->failed_count }} failed · {{ str_replace('_',' ', $campaign->status) }}</div></td>
                                <td>{{ $campaign->sent_at?->format('M j, Y H:i') ?? '—' }}<div class="campaign-muted">{{ $campaign->creator?->name ?? 'Admin' }}</div></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center campaign-muted py-5">No campaigns sent yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $campaigns->links() }}</div>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    const channel=document.getElementById('campaign-channel');
    const audience=document.getElementById('campaign-audience');
    const count=document.getElementById('audience-count');
    const form=document.getElementById('campaign-form');
    function syncAudience(){
        const allowed=[...audience.options].filter(o=>o.dataset.channel===channel.value);
        [...audience.options].forEach(o=>o.hidden=o.dataset.channel!==channel.value);
        if(!allowed.some(o=>o.value===audience.value)) audience.value=allowed[0]?.value||'';
        const selected=audience.selectedOptions[0];
        count.textContent=selected ? `${Number(selected.dataset.count).toLocaleString()} eligible recipient(s)` : '';
    }
    channel.addEventListener('change',syncAudience); audience.addEventListener('change',syncAudience); syncAudience();
    form.addEventListener('submit',function(e){
        const selected=audience.selectedOptions[0];
        const number=Number(selected?.dataset.count||0);
        if(number<1){e.preventDefault();alert('This audience has no eligible recipients.');return;}
        if(!confirm(`Send this ${channel.value==='email'?'email':'in-app'} campaign to ${number.toLocaleString()} recipient(s)?`)) e.preventDefault();
    });
})();
</script>
@endsection
