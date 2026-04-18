@extends('selllayout')

@push('styles')
<style>
.setup-page { max-width: 400px; margin: 0 auto; }

.setup-card {
    background: rgba(10,18,10,.88);
    border: 1px solid rgba(0,204,0,.18);
    border-radius: 22px;
    padding: 2.25rem 1.75rem;
    backdrop-filter: blur(16px);
}

.setup-title { font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: .3rem;
               display:flex; align-items:center; gap:.5rem; }
.setup-sub { font-size: .82rem; color: rgba(255,255,255,.4); margin-bottom: 1.75rem; line-height: 1.55; }

.sp-label { display: block; font-size: .72rem; font-weight: 600; color: rgba(255,255,255,.4);
            text-align: center; margin-bottom: .35rem; letter-spacing: .06em; text-transform: uppercase; }

.sp-dots { display: flex; justify-content: center; gap: 1rem; margin-bottom: .5rem; }
.sp-dot {
    width: 14px; height: 14px; border-radius: 50%;
    background: rgba(255,255,255,.12); border: 2px solid rgba(255,255,255,.2);
    transition: all .2s cubic-bezier(.34,1.56,.64,1);
}
.sp-dot.filled { background: #00cc00; border-color: #00cc00; transform: scale(1.15); box-shadow: 0 0 8px rgba(0,204,0,.45); }
.sp-dot.error  { background: #ef4444; border-color: #ef4444; animation: spShake .4s; }
@keyframes spShake{0%,100%{transform:translateX(0)}25%,75%{transform:translateX(-4px)}50%{transform:translateX(4px)}}

.sp-err { font-size: .78rem; color: #f87171; text-align: center; margin: .5rem 0; min-height: 1rem; }

.sp-sep { border: none; border-top: 1px solid rgba(255,255,255,.06); margin: 1.25rem 0; }

.sp-pad { display: grid; grid-template-columns: repeat(3,1fr); gap: .6rem; }
.sp-btn {
    padding: .82rem .4rem; border-radius: 13px; border: 1px solid rgba(0,204,0,.13);
    background: rgba(0,204,0,.05); color: #fff; font-size: 1.18rem; font-weight: 600;
    cursor: pointer; font-family: inherit; transition: all .15s;
    display: flex; flex-direction: column; align-items: center; gap: 2px; line-height: 1;
}
.sp-btn:active, .sp-btn.pressed { background: rgba(0,204,0,.18); transform: scale(.93); }
.sp-btn .sp-sub { font-size: .42rem; color: rgba(255,255,255,.28); letter-spacing: .08em; }
.sp-btn.zero { grid-column: 2; }
.sp-btn.del  { background: rgba(255,255,255,.03); border-color: rgba(255,255,255,.08); grid-column: 3; }

.sp-submit {
    width: 100%; margin-top: 1.25rem; padding: .82rem;
    border-radius: 13px; border: none;
    background: linear-gradient(135deg,#00cc00,#007a0c);
    color: #fff; font-weight: 700; font-size: .93rem; font-family: inherit;
    cursor: pointer; transition: opacity .2s, transform .1s;
    display: none; align-items: center; justify-content: center; gap: .45rem;
}
.sp-submit.show { display: flex; }
.sp-submit:hover { opacity: .9; transform: translateY(-1px); }

.sp-notice {
    background: rgba(0,204,0,.07); border: 1px solid rgba(0,204,0,.13);
    border-radius: 10px; padding: .65rem .9rem; font-size: .78rem;
    color: rgba(255,255,255,.5); margin-bottom: 1.5rem; line-height: 1.5;
}
</style>
@endpush

@section('content')
<div class="setup-page">
    <div class="mb-4">
        <h1 style="font-size:1.3rem;font-weight:700;color:#fff;margin-bottom:.2rem">Set Up PIN</h1>
        <p style="font-size:.82rem;color:rgba(255,255,255,.4)">Create a 4-digit PIN to authorise transactions.</p>
    </div>

    @if(session('info'))
    <div class="sp-notice">&#x2139;&#xFE0F; {{ session('info') }}</div>
    @endif

    <div class="setup-card">

        @if($errors->any())
        <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:.65rem .9rem;font-size:.82rem;color:#f87171;margin-bottom:1.25rem">
            @foreach($errors->all() as $e){{ $e }}<br>@endforeach
        </div>
        @endif

        <span class="sp-label" id="spLabel">Choose your PIN</span>
        <div class="sp-dots" id="spDots">
            <div class="sp-dot"></div><div class="sp-dot"></div>
            <div class="sp-dot"></div><div class="sp-dot"></div>
        </div>
        <div class="sp-err" id="spErr"></div>

        <hr class="sp-sep">

        <div class="sp-pad" id="spPad">
            <button type="button" class="sp-btn" data-n="1">1</button>
            <button type="button" class="sp-btn" data-n="2">2<span class="sp-sub">ABC</span></button>
            <button type="button" class="sp-btn" data-n="3">3<span class="sp-sub">DEF</span></button>
            <button type="button" class="sp-btn" data-n="4">4<span class="sp-sub">GHI</span></button>
            <button type="button" class="sp-btn" data-n="5">5<span class="sp-sub">JKL</span></button>
            <button type="button" class="sp-btn" data-n="6">6<span class="sp-sub">MNO</span></button>
            <button type="button" class="sp-btn" data-n="7">7<span class="sp-sub">PQRS</span></button>
            <button type="button" class="sp-btn" data-n="8">8<span class="sp-sub">TUV</span></button>
            <button type="button" class="sp-btn" data-n="9">9<span class="sp-sub">WXYZ</span></button>
            <button type="button" class="sp-btn zero" data-n="0">0</button>
            <button type="button" class="sp-btn del" id="spDel">&#x232B;</button>
        </div>

        <form method="POST" action="{{ route('pin.setup') }}" id="spForm">
            @csrf
            <input type="hidden" name="pin" id="spPinIn">
            <input type="hidden" name="pin_confirm" id="spConfIn">
            <button type="submit" class="sp-submit" id="spSubmit">
                <i class="bi bi-shield-lock-fill"></i> Set PIN
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    let p1='', p2='', confirm=false;
    const dots = Array.from(document.querySelectorAll('#spDots .sp-dot'));
    const label = document.getElementById('spLabel');
    const errEl = document.getElementById('spErr');

    function render(v){ dots.forEach((d,i)=>{ d.classList.toggle('filled',i<v.length); d.classList.remove('error'); }); }

    function flash(msg){
        errEl.textContent=msg;
        dots.forEach(d=>{d.classList.remove('filled');d.classList.add('error');});
        setTimeout(()=>{dots.forEach(d=>d.classList.remove('error'));errEl.textContent='';},700);
    }

    document.getElementById('spPad').addEventListener('click',function(e){
        const btn=e.target.closest('[data-n]'), d=e.target.closest('#spDel');
        if(btn){btn.classList.add('pressed');setTimeout(()=>btn.classList.remove('pressed'),150);add(btn.dataset.n);}
        if(d) del();
    });

    document.addEventListener('keydown',function(e){
        if(e.key>='0'&&e.key<='9') add(e.key);
        if(e.key==='Backspace') del();
    });

    function add(d){
        if(!confirm){
            if(p1.length<4){p1+=d;render(p1);}
            if(p1.length===4) setTimeout(()=>{confirm=true;label.textContent='Confirm your PIN';render('');},200);
        } else {
            if(p2.length<4){p2+=d;render(p2);}
            if(p2.length===4) setTimeout(()=>{
                if(p1!==p2){flash('PINs do not match.');p1='';p2='';confirm=false;label.textContent='Choose your PIN';render('');}
                else{
                    document.getElementById('spPinIn').value=p1;
                    document.getElementById('spConfIn').value=p2;
                    document.getElementById('spSubmit').classList.add('show');
                    document.getElementById('spPad').style.opacity='.4';
                    document.getElementById('spPad').style.pointerEvents='none';
                }
            },200);
        }
    }
    function del(){
        if(!confirm&&p1.length){p1=p1.slice(0,-1);render(p1);}
        else if(confirm&&p2.length){p2=p2.slice(0,-1);render(p2);}
    }
}());
</script>
@endpush
