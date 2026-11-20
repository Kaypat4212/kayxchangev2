@extends('adminnavlayout')

@section('title', 'Edit email template')

@push('styles')
<style>
.ee-page{--ee-border:rgba(222,244,228,.09);--ee-muted:#98a99e;max-width:1440px;margin:0 auto;padding:25px 16px 75px;color:#eef5ef}.ee-hero{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:23px 26px;margin-bottom:17px;border:1px solid var(--ee-border);border-radius:21px;background:radial-gradient(ellipse at 90% 5%,rgba(0,204,0,.14),transparent 36%),linear-gradient(130deg,#142019,#101a16 60%,#111b20)}.ee-kicker{color:#7fe696;font-size:.69rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.ee-hero h1{margin:7px 0 4px;color:#fff;font-size:1.55rem;font-weight:800;letter-spacing:-.03em}.ee-hero p{margin:0;color:#a5b4a9;font-size:.81rem}.ee-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 13px;border:1px solid var(--ee-border);border-radius:10px;background:#19251d;color:#eaf3ec;text-decoration:none;font-size:.78rem;font-weight:700;white-space:nowrap}.ee-btn:hover{background:#25372a;color:#fff}.ee-btn.primary{background:linear-gradient(135deg,#91efa5,#4bd572);border:0;color:#07190b}.ee-card{border:1px solid var(--ee-border);border-radius:18px;background:linear-gradient(155deg,#141e18,#101712);padding:20px}.ee-layout{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(360px,.9fr);gap:16px;align-items:start}.ee-label{display:block;margin-bottom:7px;color:#a6b4aa;font-size:.71rem;font-weight:750;letter-spacing:.07em;text-transform:uppercase}.ee-input{width:100%;border:1px solid rgba(221,241,226,.11);border-radius:10px;background:#0b120e;color:#f0f6f1;padding:11px 13px;font-size:.84rem;outline:none}.ee-input:focus{border-color:rgba(120,231,149,.6);box-shadow:0 0 0 3px rgba(120,231,149,.08)}.ee-subject-row{display:flex;gap:9px}.ee-subject-row .ee-input{flex:1;min-width:0}.ee-ai-btn{flex:none;border:1px solid rgba(120,231,149,.24);border-radius:10px;background:rgba(120,231,149,.08);color:#91eda5;padding:0 12px;font-size:.75rem;font-weight:750}.ee-ai-btn:hover{background:rgba(120,231,149,.14)}.ee-ai-btn:disabled{opacity:.6;cursor:wait}.ee-note{color:var(--ee-muted);font-size:.72rem;line-height:1.6}.ee-template-id{display:inline-flex;align-items:center;gap:7px;margin-bottom:15px;padding:6px 9px;border-radius:8px;background:rgba(120,231,149,.07);color:#8be9a0;font-family:ui-monospace,Consolas,monospace;font-size:.74rem}.ee-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:19px 0 8px}.ee-token-list{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px}.ee-token{border:1px solid rgba(120,231,149,.2);border-radius:7px;background:rgba(120,231,149,.06);color:#9aeeaa;padding:5px 8px;font-family:ui-monospace,Consolas,monospace;font-size:.68rem;cursor:pointer}.ee-token:hover{background:rgba(120,231,149,.14)}.ee-editor{min-height:480px;resize:vertical;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:.78rem;line-height:1.6;tab-size:2}.ee-ai-results{display:grid;gap:6px;margin-top:10px}.ee-subject-choice{width:100%;padding:8px 10px;border:1px solid var(--ee-border);border-radius:9px;background:#101913;color:#e5eee7;text-align:left;font-size:.75rem;cursor:pointer}.ee-subject-choice:hover{border-color:rgba(120,231,149,.35)}.ee-preview-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}.ee-preview-head h2{margin:0;color:#f0f6f1;font-size:.93rem;font-weight:750}.ee-toggle{display:flex;gap:4px;padding:4px;border:1px solid var(--ee-border);border-radius:9px;background:#0b120e}.ee-toggle button{border:0;border-radius:6px;background:transparent;color:#91a096;padding:5px 8px;font-size:.66rem}.ee-toggle button.active{background:#1a2a1e;color:#a3efb3}.ee-preview-shell{display:flex;justify-content:center;min-height:590px;padding:13px;border:1px solid rgba(255,255,255,.07);border-radius:14px;background:repeating-linear-gradient(135deg,#0e1510,#0e1510 12px,#101812 12px,#101812 24px)}.ee-preview-frame{width:100%;max-width:600px;height:560px;border:0;border-radius:9px;background:#fff;box-shadow:0 14px 40px rgba(0,0,0,.28);transition:max-width .2s}.ee-preview-frame.mobile{max-width:375px}.ee-savebar{position:sticky;bottom:8px;z-index:10;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:16px;padding:12px 14px;border:1px solid var(--ee-border);border-radius:13px;background:rgba(15,23,17,.94);backdrop-filter:blur(14px)}.ee-savebar .ee-note{margin:0}.ee-save{border:0;border-radius:9px;background:linear-gradient(135deg,#91efa5,#4bd572);color:#07190b;padding:10px 15px;font-weight:800;font-size:.8rem}.ee-alert{padding:11px 13px;margin-bottom:13px;border:1px solid rgba(239,68,68,.2);border-radius:11px;background:rgba(239,68,68,.08);color:#ffaaaa;font-size:.8rem}
body.kx-light .ee-page{color:#1a2b1e}body.kx-light .ee-hero{background:linear-gradient(130deg,#f4fff6,#edf8f0)}body.kx-light .ee-hero h1,body.kx-light .ee-preview-head h2{color:#19291e}body.kx-light .ee-hero p,body.kx-light .ee-note{color:#64766a}body.kx-light .ee-card{background:#fff}body.kx-light .ee-input{background:#f7fbf8;color:#19291e}body.kx-light .ee-savebar{background:rgba(255,255,255,.94)}body.kx-light .ee-toggle{background:#f3f8f4}body.kx-light .ee-toggle button{color:#64766a}body.kx-light .ee-toggle button.active{background:#e0f4e5;color:#236c35}
@media(max-width:900px){.ee-layout{grid-template-columns:1fr}.ee-preview-shell{min-height:520px}.ee-preview-frame{height:490px}.ee-hero{align-items:flex-start;flex-direction:column;padding:20px}.ee-hero .ee-btn{width:100%}.ee-editor{min-height:360px}}
@media(max-width:520px){.ee-page{padding:16px 11px 60px}.ee-card{padding:15px}.ee-subject-row{flex-direction:column}.ee-ai-btn{min-height:38px}.ee-savebar{align-items:flex-start;flex-direction:column}.ee-savebar .ee-save{width:100%}.ee-preview-head{align-items:flex-start;flex-direction:column}.ee-preview-shell{padding:8px}}
</style>
@endpush

@section('content')
@php
    $tokens = ['user_name','app_name','amount','currency','naira_amount','wallet_address','reference','payment_method','account_details','reason'];
@endphp
<main class="ee-page">
    <header class="ee-hero"><div><div class="ee-kicker"><i class="bi bi-envelope-paper me-1"></i> Template editor</div><h1>Edit email template</h1><p>Changes apply to future emails triggered by this workflow.</p></div><a href="{{ route('admin.email-templates') }}" class="ee-btn"><i class="bi bi-arrow-left"></i>Template library</a></header>

    @if($errors->any())<div class="ee-alert" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <form id="template-form" method="POST" action="{{ route('admin.email-templates.update', $template->key) }}">
        @csrf @method('PUT')
        <div class="ee-layout">
            <section class="ee-card">
                <div class="ee-template-id"><i class="bi bi-braces"></i>{{ $template->key }}</div>
                @if($template->description)<p class="ee-note mb-4">{{ $template->description }}</p>@endif
                <div class="mb-4"><label class="ee-label" for="email-subject-input">Subject line</label><div class="ee-subject-row"><input id="email-subject-input" type="text" name="subject" maxlength="255" class="ee-input" value="{{ old('subject', $template->subject) }}" required><button type="button" id="ai-subject-btn" class="ee-ai-btn"><i class="bi bi-stars me-1"></i>AI subject ideas</button></div><div id="subject-error" class="ee-note mt-2" role="status" aria-live="polite"></div><div id="ai-subject-out" class="ee-ai-results" hidden></div></div>
                <div class="ee-toolbar"><label class="ee-label mb-0" for="bodyEditor">HTML email body</label><span class="ee-note">Insert tokens or edit the HTML</span></div>
                <div class="ee-token-list" aria-label="Insert template token">
                    @foreach($tokens as $token)<button type="button" class="ee-token" data-token="{{ $token }}">&#123;&#123;{{ $token }}&#125;&#125;</button>@endforeach
                </div>
                <textarea id="bodyEditor" name="body" class="ee-input ee-editor" spellcheck="false" required>{{ old('body', $template->body) }}</textarea>
                <div class="ee-note mt-2">Preview values are examples only. Tokens are replaced with customer data when the message is sent.</div>
            </section>

            <section class="ee-card">
                <div class="ee-preview-head"><div><h2><i class="bi bi-eye me-2 text-success"></i>Live email preview</h2><div class="ee-note mt-1">Scripts are disabled inside this isolated preview.</div></div><div class="ee-toggle" role="group" aria-label="Preview size"><button type="button" class="active" data-size="desktop">Desktop</button><button type="button" data-size="mobile">Mobile</button></div></div>
                <div class="ee-preview-shell"><iframe id="bodyPreview" class="ee-preview-frame" title="Email template preview" sandbox=""></iframe></div>
            </section>
        </div>
        <div class="ee-savebar"><p class="ee-note"><i class="bi bi-shield-check me-1 text-success"></i>Review the subject and preview before saving this live template.</p><div class="d-flex gap-2 w-100 w-sm-auto"><a href="{{ route('admin.email-templates') }}" class="ee-btn">Cancel</a><button type="submit" class="ee-save"><i class="bi bi-check2-circle me-1"></i>Save template</button></div></div>
    </form>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
    const body=document.getElementById('bodyEditor'),preview=document.getElementById('bodyPreview'),subject=document.getElementById('email-subject-input');
    const subjectBtn=document.getElementById('ai-subject-btn'),subjectOut=document.getElementById('ai-subject-out'),subjectError=document.getElementById('subject-error');
    const templateKey=@json($template->key);
    const tokenOpen=String.fromCharCode(123,123),tokenClose=String.fromCharCode(125,125);
    function updatePreview(){
        const examples={user_name:'Alex',app_name:'KayXchange',amount:'25.00',currency:'USDT',naira_amount:'₦38,500.00',wallet_address:'Your wallet address',reference:'KX-EXAMPLE-1042',payment_method:'Crypto wallet',account_details:'Account details',reason:'Your request was reviewed'};
        let content=body.value;
        Object.entries(examples).forEach(([key,value])=>{content=content.replaceAll(tokenOpen+key+tokenClose,value)});
        preview.srcdoc='<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><meta charset="utf-8"><style>body{margin:0;padding:20px;background:#fff;color:#202620;font:15px/1.65 Arial,Helvetica,sans-serif;overflow-wrap:anywhere}img{max-width:100%;height:auto}table{max-width:100%}a{color:#168c3b}</style></head><body>'+content+'</body></html>';
    }
    body.addEventListener('input',updatePreview);updatePreview();
    document.querySelectorAll('.ee-token').forEach(button=>button.addEventListener('click',()=>{const token=tokenOpen+button.dataset.token+tokenClose,start=body.selectionStart,end=body.selectionEnd;body.setRangeText(token,start,end,'end');body.focus();updatePreview()}));
    document.querySelectorAll('.ee-toggle button').forEach(button=>button.addEventListener('click',()=>{document.querySelectorAll('.ee-toggle button').forEach(other=>other.classList.toggle('active',other===button));preview.classList.toggle('mobile',button.dataset.size==='mobile')}));
    subjectBtn.addEventListener('click',async()=>{
        subjectBtn.disabled=true;subjectBtn.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Thinking';subjectError.textContent='';subjectOut.replaceChildren();subjectOut.hidden=true;
        try{
            const response=await fetch(@json(route('ai.email-subject')),{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'Accept':'application/json'},credentials:'same-origin',body:JSON.stringify({template_context:'Event: '+templateKey+'. Description: '+@json($template->description).'. Current subject: '+subject.value.trim()})});
            const data=await response.json();if(!response.ok||data.error)throw new Error(data.error||'The AI service could not create subject ideas.');
            (data.subjects||[]).slice(0,5).forEach((text,index)=>{const choice=document.createElement('button');choice.type='button';choice.className='ee-subject-choice';choice.textContent=(index+1)+'. '+text;choice.addEventListener('click',()=>{subject.value=text;subject.dispatchEvent(new Event('input',{bubbles:true}));subject.focus()});subjectOut.appendChild(choice)});
            subjectOut.hidden=subjectOut.childElementCount===0;if(subjectOut.hidden)throw new Error('The AI returned no subject ideas. Try again.');
        }catch(error){subjectError.textContent=error.message||'Could not generate subject ideas. Check the AI settings and retry.'}
        finally{subjectBtn.disabled=false;subjectBtn.innerHTML='<i class="bi bi-stars me-1"></i>AI subject ideas'}
    });
});
</script>
@endpush
