@extends('adminnavlayout')

@section('title', 'User Campaigns')

@section('content')
<style>
.campaign-wrap{max-width:1400px;margin:0 auto;padding:1.5rem;color:var(--kx-text,#e5e7eb)}
.campaign-card{background:var(--kx-card,#111827);border:1px solid rgba(255,255,255,.09);border-radius:14px;padding:1.25rem;height:100%}
.campaign-muted{color:var(--kx-muted,#9ca3af);font-size:.86rem}
.campaign-field{background:#0b1220!important;color:#e5e7eb!important;border-color:rgba(255,255,255,.15)!important}
.campaign-field option{background:#0b1220;color:#e5e7eb}
.campaign-wrap{max-width:1480px;padding-top:1.75rem}.campaign-card{border-radius:19px;padding:1.4rem;box-shadow:0 18px 45px rgba(0,0,0,.13)}.campaign-hero{padding:23px 26px;border-radius:22px;border:1px solid rgba(255,255,255,.09);background:radial-gradient(ellipse at 90% 0%,rgba(0,204,0,.15),transparent 36%),linear-gradient(130deg,#142019,#101a16 60%,#111b20)}.campaign-hero h1{color:#f5faf6;font-weight:800;letter-spacing:-.035em}.campaign-ai{border:1px solid rgba(120,231,149,.18);border-radius:15px;padding:15px;margin:0 0 20px;background:radial-gradient(ellipse at 95% 0%,rgba(25,146,73,.13),transparent 38%),#0e1712}.campaign-ai h3{color:#f2f8f3;font-size:.9rem;font-weight:750}.campaign-ai-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px}.campaign-ai .form-control,.campaign-ai .form-select{background:#09110c;color:#e8f2e9;border-color:rgba(255,255,255,.12);font-size:.78rem}.campaign-ai .form-select option{background:#09110c;color:#e8f2e9}.campaign-ai-help{color:#98a89d;font-size:.72rem;line-height:1.55}.campaign-ai-result{margin-top:11px;padding:11px;border:1px solid rgba(255,255,255,.08);border-radius:11px;background:rgba(255,255,255,.025)}.campaign-ai-result button{border:1px solid rgba(120,231,149,.18);border-radius:8px;background:#142019;color:#b7efc2;padding:6px 8px;font-size:.7rem;text-align:left}.campaign-ai-result button:hover{background:#1d3022}.campaign-ai-ideas{display:flex;flex-wrap:wrap;gap:6px}.campaign-ai-error{color:#ffaaaa;font-size:.76rem}.campaign-muted{line-height:1.6}.campaign-field{border-radius:10px;padding:.65rem .8rem}.campaign-wrap .table{--bs-table-bg:transparent;--bs-table-color:#e5e7eb;--bs-table-hover-bg:rgba(255,255,255,.025);--bs-table-border-color:rgba(255,255,255,.07)}
@media(max-width:520px){.campaign-ai-grid{grid-template-columns:1fr}.campaign-hero{padding:19px}.campaign-wrap{padding-left:12px;padding-right:12px}}
</style>
<div class="campaign-wrap">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div class="campaign-hero flex-grow-1"><div class="text-success small fw-bold text-uppercase mb-2">Customer communications</div><h1 class="h3 mb-1">Campaign studio</h1><div class="campaign-muted">Compose, review and send updates. Email campaigns are restricted to active newsletter subscribers who opted in.</div></div>
        <a href="{{ route('admin.newsletter.index') }}" class="btn btn-outline-secondary"><i class="bi bi-envelope me-1"></i>Newsletter subscribers</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="campaign-card">
                <h2 class="h5 mb-1"><i class="bi bi-send me-2"></i>Create campaign</h2>
                <p class="campaign-muted mb-4">In-app campaigns appear in the recipients’ notification bell. Email is limited to active newsletter subscribers.</p>
                <section class="campaign-ai" id="email-ai-studio" aria-labelledby="email-ai-title">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3"><div><div class="text-success small fw-bold text-uppercase mb-1"><i class="bi bi-stars me-1"></i>KayXchange AI</div><h3 id="email-ai-title" class="mb-1">Draft an email campaign</h3><p class="campaign-ai-help mb-0">Generate editable subject, message and campaign angles. Nothing is sent until you review and confirm below.</p></div><i class="bi bi-envelope-paper text-success fs-4"></i></div>
                    <div class="campaign-ai-grid">
                        <div><label class="form-label small mb-1" for="ai-email-goal">Campaign goal</label><input class="form-control" id="ai-email-goal" maxlength="500" placeholder="e.g. Introduce our new wallet page to subscribers"></div>
                        <div><label class="form-label small mb-1" for="ai-email-offer">Offer or confirmed details <span class="text-secondary">(optional)</span></label><input class="form-control" id="ai-email-offer" maxlength="300" placeholder="Only include terms you have approved"></div>
                        <div><label class="form-label small mb-1" for="ai-email-cta">Call to action <span class="text-secondary">(optional)</span></label><input class="form-control" id="ai-email-cta" maxlength="120" placeholder="e.g. Explore your wallet"></div>
                        <div><label class="form-label small mb-1" for="ai-email-tone">Writing tone</label><select class="form-select" id="ai-email-tone"><option value="friendly">Friendly</option><option value="professional">Professional</option><option value="celebratory">Celebratory</option><option value="educational">Educational</option><option value="urgent">Urgent (only for real deadlines)</option></select></div>
                    </div>
                    <button class="btn btn-sm btn-outline-success mt-3" type="button" id="ai-email-generate"><i class="bi bi-magic me-1"></i>Generate campaign draft</button>
                    <div id="ai-email-error" class="campaign-ai-error mt-2" role="status" aria-live="polite"></div>
                    <div id="ai-email-result" class="campaign-ai-result" hidden><div class="campaign-ai-help mb-2">Alternative subject lines · click to use</div><div id="ai-email-subjects" class="d-grid gap-1 mb-3"></div><div class="campaign-ai-help mb-2">Other campaign angles</div><div id="ai-email-ideas" class="campaign-ai-ideas"></div></div>
                </section>
                <form method="POST" action="{{ route('admin.campaigns.send') }}" id="campaign-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Delivery channel</label>
                        <select class="form-select campaign-field" name="channel" id="campaign-channel" required>
                            <option value="in_app" @selected(old('channel') === 'in_app')>In-app notification</option>
                            <option value="email" @selected(old('channel','email') === 'email')>Email (opted-in newsletter only)</option>
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
    const emailStudio=document.getElementById('email-ai-studio');
    const aiButton=document.getElementById('ai-email-generate');
    function syncAudience(){
        const allowed=[...audience.options].filter(o=>o.dataset.channel===channel.value);
        [...audience.options].forEach(o=>o.hidden=o.dataset.channel!==channel.value);
        if(!allowed.some(o=>o.value===audience.value)) audience.value=allowed[0]?.value||'';
        const selected=audience.selectedOptions[0];
        count.textContent=selected ? `${Number(selected.dataset.count).toLocaleString()} eligible recipient(s)` : '';
        if(emailStudio) emailStudio.hidden=channel.value!=='email';
    }
    channel.addEventListener('change',syncAudience); audience.addEventListener('change',syncAudience); syncAudience();
    aiButton?.addEventListener('click',async function(){
        const goal=document.getElementById('ai-email-goal').value.trim(),error=document.getElementById('ai-email-error'),result=document.getElementById('ai-email-result');
        if(!goal){error.textContent='Describe the email campaign goal first.';document.getElementById('ai-email-goal').focus();return}
        error.textContent='';result.hidden=true;aiButton.disabled=true;aiButton.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Drafting…';
        try{
            const response=await fetch(@json(route('ai.email-campaign')),{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'Accept':'application/json'},credentials:'same-origin',body:JSON.stringify({goal,offer:document.getElementById('ai-email-offer').value.trim(),call_to_action:document.getElementById('ai-email-cta').value.trim(),tone:document.getElementById('ai-email-tone').value})});
            const data=await response.json();if(!response.ok||data.error)throw new Error(data.error||'The AI could not draft this campaign.');
            document.querySelector('#campaign-form [name="title"]').value=data.subject||'';document.querySelector('#campaign-form [name="body"]').value=data.body||'';channel.value='email';syncAudience();
            const subjects=document.getElementById('ai-email-subjects'),ideas=document.getElementById('ai-email-ideas');subjects.replaceChildren();ideas.replaceChildren();
            (data.subject_options||[]).forEach(text=>{const choice=document.createElement('button');choice.type='button';choice.textContent=text;choice.addEventListener('click',()=>document.querySelector('#campaign-form [name="title"]').value=text);subjects.appendChild(choice)});
            (data.campaign_ideas||[]).forEach(text=>{const chip=document.createElement('span');chip.className='badge rounded-pill text-bg-dark border border-secondary-subtle p-2';chip.textContent=text;ideas.appendChild(chip)});
            result.hidden=false;document.querySelector('#campaign-form [name="title"]').focus();
        }catch(ex){error.textContent=ex.message||'AI drafting failed. Check the AI settings and try again.'}
        finally{aiButton.disabled=false;aiButton.innerHTML='<i class="bi bi-magic me-1"></i>Generate campaign draft'}
    });
    form.addEventListener('submit',function(e){
        const selected=audience.selectedOptions[0];
        const number=Number(selected?.dataset.count||0);
        if(number<1){e.preventDefault();alert('This audience has no eligible recipients.');return;}
        if(!confirm(`Send this ${channel.value==='email'?'email':'in-app'} campaign to ${number.toLocaleString()} recipient(s)?`)) e.preventDefault();
    });
})();
</script>
@endsection
