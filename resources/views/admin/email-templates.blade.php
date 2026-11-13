@extends('adminnavlayout')

@section('title', 'Email templates')

@push('styles')
<style>
.em-page{--em-bg:#0d1410;--em-card:#131d17;--em-border:rgba(220,242,226,.09);--em-muted:#99aaa0;max-width:1320px;margin:0 auto;padding:26px 16px 64px;color:#eef5ef}.em-hero{position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:24px;padding:30px;border:1px solid var(--em-border);border-radius:24px;background:radial-gradient(ellipse at 90% 5%,rgba(0,204,0,.17),transparent 35%),linear-gradient(130deg,#142019,#101a16 60%,#111b20);margin-bottom:18px}.em-kicker{color:#78e795;font-size:.72rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.em-hero h1{margin:9px 0 6px;color:#fff;font-size:clamp(1.6rem,3vw,2.25rem);font-weight:800;letter-spacing:-.04em}.em-hero p{max-width:610px;margin:0;color:#a7b5aa;font-size:.9rem;line-height:1.65}.em-actions{display:flex;gap:9px;flex-wrap:wrap}.em-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:10px 15px;border:1px solid var(--em-border);border-radius:11px;background:#19251d;color:#e9f3eb;text-decoration:none;font-size:.81rem;font-weight:700;transition:.18s}.em-btn:hover{background:#223329;color:#fff;transform:translateY(-1px)}.em-btn.primary{border:0;background:linear-gradient(135deg,#90f0a4,#48d56f);color:#06180b}.em-btn.primary:hover{background:#a4f5b5;color:#06180b}.em-flash{padding:13px 16px;border:1px solid rgba(69,211,112,.18);border-radius:12px;background:rgba(38,173,81,.09);color:#a3efb4;margin-bottom:16px}.em-overview{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:17px}.em-stat,.em-studio,.em-library{border:1px solid var(--em-border);border-radius:19px;background:linear-gradient(145deg,#141f18,#101813)}.em-stat{display:flex;align-items:center;gap:12px;padding:15px}.em-stat i{width:40px;height:40px;display:grid;place-items:center;border-radius:12px;background:rgba(120,231,149,.1);color:#82e89b;font-size:1.05rem}.em-stat span{display:block;color:var(--em-muted);font-size:.72rem}.em-stat strong{display:block;margin-top:2px;color:#f1f7f2;font-size:1.05rem}.em-studio{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:20px;padding:21px 23px;margin-bottom:24px;background:radial-gradient(ellipse at 100% 0%,rgba(31,153,83,.12),transparent 40%),#111a15}.em-studio h2{margin:0 0 5px;color:#f5faf6;font-size:1.02rem;font-weight:750}.em-studio p{margin:0;color:var(--em-muted);font-size:.8rem;line-height:1.6}.em-studio-mark{display:inline-flex;align-items:center;gap:8px;margin-bottom:8px;color:#8be9a0;font-size:.69rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.em-library{overflow:hidden}.em-library-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:19px 21px;border-bottom:1px solid var(--em-border)}.em-library-head h2{margin:0;color:#f5f8f5;font-size:1rem;font-weight:750}.em-library-head p{margin:4px 0 0;color:var(--em-muted);font-size:.74rem}.em-search{width:min(290px,100%);padding:9px 12px;border:1px solid var(--em-border);border-radius:10px;background:#0b120e;color:#eef6ef;font-size:.78rem;outline:none}.em-search:focus{border-color:rgba(120,231,149,.55);box-shadow:0 0 0 3px rgba(120,231,149,.08)}.em-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;padding:16px}.em-template{display:flex;flex-direction:column;min-width:0;padding:17px;border:1px solid rgba(222,244,228,.08);border-radius:15px;background:rgba(255,255,255,.02);transition:border-color .18s,transform .18s,background .18s}.em-template:hover{border-color:rgba(120,231,149,.28);background:rgba(120,231,149,.025);transform:translateY(-1px)}.em-template-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}.em-key{display:inline-flex;max-width:100%;padding:5px 8px;border-radius:8px;background:rgba(120,231,149,.08);color:#8be9a0;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:.7rem;overflow-wrap:anywhere}.em-edit{display:inline-flex;align-items:center;gap:6px;flex:none;color:#8ce79f!important;text-decoration:none;font-size:.75rem;font-weight:750}.em-edit:hover{color:#c0f7ca!important}.em-subject{margin:16px 0 6px;color:#f4f8f4;font-size:.92rem;font-weight:700;overflow-wrap:anywhere}.em-desc{min-height:36px;margin:0;color:var(--em-muted);font-size:.75rem;line-height:1.55}.em-template-foot{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:auto;padding-top:15px}.em-foot-hint{color:#718176;font-size:.68rem}.em-empty{padding:48px 18px;text-align:center;color:var(--em-muted)}.em-empty i{font-size:1.6rem;color:#82e89b}.em-empty h3{margin:12px 0 5px;color:#eff6f0;font-size:.95rem}
body.kx-light .em-page{color:#17251b}body.kx-light .em-hero{background:radial-gradient(ellipse at 90% 5%,rgba(0,170,45,.12),transparent 35%),linear-gradient(130deg,#f4fff6,#edf8f0 60%,#f3faf4)}body.kx-light .em-hero h1,body.kx-light .em-studio h2,body.kx-light .em-library-head h2,body.kx-light .em-subject,body.kx-light .em-stat strong,body.kx-light .em-empty h3{color:#17251b}body.kx-light .em-hero p,body.kx-light .em-studio p,body.kx-light .em-library-head p,body.kx-light .em-desc{color:#63756a}body.kx-light .em-stat,body.kx-light .em-studio,body.kx-light .em-library,body.kx-light .em-template{background:#fff}body.kx-light .em-search{background:#fff;color:#17251b}
@media(max-width:760px){.em-page{padding:17px 12px 56px}.em-hero{align-items:flex-start;flex-direction:column;padding:22px}.em-actions{width:100%}.em-actions .em-btn{flex:1}.em-overview{gap:8px}.em-stat{align-items:flex-start;flex-direction:column;gap:8px;padding:12px}.em-stat i{width:34px;height:34px}.em-stat strong{font-size:.95rem}.em-studio{grid-template-columns:1fr;padding:18px}.em-studio .em-btn{width:100%}.em-library-head{align-items:flex-start;flex-direction:column}.em-search{width:100%}.em-grid{grid-template-columns:1fr;padding:11px}.em-template{padding:15px}}
</style>
@endpush

@section('content')
<main class="em-page">
    <header class="em-hero">
        <div><div class="em-kicker"><i class="bi bi-envelope-paper me-1"></i> Messaging workspace</div><h1>Email templates</h1><p>Manage the transactional messages your customers receive, keep every subject and layout on-brand, and open the campaign studio when you’re ready to write a promotion.</p></div>
        <div class="em-actions"><a class="em-btn" href="{{ route('admin.email-settings.index') }}"><i class="bi bi-gear"></i>Email settings</a><a class="em-btn primary" href="{{ route('admin.campaigns.index') }}"><i class="bi bi-stars"></i>Open campaign studio</a></div>
    </header>

    @if(session('success'))<div class="em-flash" role="status"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <section class="em-overview" aria-label="Email template overview">
        <article class="em-stat"><i class="bi bi-files"></i><div><span>Available templates</span><strong>{{ number_format($templates->count()) }}</strong></div></article>
        <article class="em-stat"><i class="bi bi-braces"></i><div><span>Reusable placeholders</span><strong>Personalized content</strong></div></article>
        <article class="em-stat"><i class="bi bi-send-check"></i><div><span>Promotional email audience</span><strong>Opted-in subscribers</strong></div></article>
    </section>

    <section class="em-studio">
        <div><div class="em-studio-mark"><i class="bi bi-magic"></i> AI email campaign studio</div><h2>Generate a promotional email draft and campaign ideas</h2><p>Describe your goal, offer and call to action. The AI fills an editable draft in Campaigns; you review it before any email is sent. Email campaigns go only to active newsletter subscribers who opted in.</p></div>
        <a class="em-btn primary" href="{{ route('admin.campaigns.index') }}"><i class="bi bi-pencil-square"></i>Write a campaign</a>
    </section>

    <section class="em-library">
        <header class="em-library-head"><div><h2>Transactional template library</h2><p>Edits apply to future emails sent by the matching workflow.</p></div><input type="search" id="template-search" class="em-search" placeholder="Search templates by event or subject" aria-label="Search email templates"></header>
        @if($templates->isNotEmpty())
            <div class="em-grid" id="template-grid">
                @foreach($templates as $tpl)
                    <article class="em-template" data-search="{{ Str::lower($tpl->key.' '.$tpl->subject.' '.$tpl->description) }}">
                        <div class="em-template-top"><span class="em-key">{{ $tpl->key }}</span><a class="em-edit" href="{{ route('admin.email-templates.edit', $tpl->key) }}">Edit <i class="bi bi-arrow-up-right"></i></a></div>
                        <h3 class="em-subject">{{ $tpl->subject }}</h3>
                        <p class="em-desc">{{ $tpl->description ?: 'Transactional message template for this account event.' }}</p>
                        <footer class="em-template-foot"><span class="em-foot-hint"><i class="bi bi-person-check me-1"></i>Token-enabled</span><a class="em-edit" href="{{ route('admin.email-templates.edit', $tpl->key) }}">Open editor <i class="bi bi-chevron-right"></i></a></footer>
                    </article>
                @endforeach
            </div>
            <div class="em-empty" id="template-no-results" hidden><i class="bi bi-search"></i><h3>No matching templates</h3><p>Try a different event or subject search.</p></div>
        @else
            <div class="em-empty"><i class="bi bi-envelope-x"></i><h3>No email templates yet</h3><p>Transactional email templates will appear here when configured.</p></div>
        @endif
    </section>
</main>
<script>
document.addEventListener('DOMContentLoaded',function(){const search=document.getElementById('template-search'),cards=[...document.querySelectorAll('.em-template')],empty=document.getElementById('template-no-results');search?.addEventListener('input',function(){const q=this.value.trim().toLowerCase();let visible=0;cards.forEach(card=>{const match=(card.dataset.search||'').includes(q);card.hidden=!match;if(match)visible++});if(empty)empty.hidden=visible!==0})});
</script>
@endsection
