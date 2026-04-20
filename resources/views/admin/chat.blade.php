@extends('adminnavlayout')

@section('content')
<style>
    .adm-sup          { padding:24px 0 60px; }
    .adm-card         { background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:18px; }
    .adm-page-title   { font-size:1.25rem;font-weight:700;color:#000;margin-bottom:2px; }
    .adm-page-sub     { font-size:0.78rem;color:#333; }
    .adm-sup-layout   { display:flex;height:calc(100vh - 200px);min-height:500px;max-height:780px;gap:0; }
    .adm-user-list    { width:260px;flex-shrink:0;border-right:1px solid rgba(255,255,255,0.07);overflow-y:auto;padding:.5rem 0; }
    .adm-chat-panel   { flex:1;display:flex;flex-direction:column;min-width:0; }
    .adm-user-item    { padding:.7rem 1rem;cursor:pointer;display:flex;align-items:center;gap:.7rem;transition:background .15s;border-left:3px solid transparent; }
    .adm-user-item:hover { background:rgba(255,255,255,0.04); }
    .adm-user-item.active { background:rgba(0,204,0,0.07);border-left-color:#00cc00; }
    .adm-user-avatar  { width:36px;height:36px;border-radius:50%;background:rgba(0,204,0,0.15);border:1.5px solid rgba(0,204,0,0.25);display:flex;align-items:center;justify-content:center;color:#00cc00;font-size:.9rem;flex-shrink:0;font-weight:700; }
    .adm-user-name    { font-size:.82rem;font-weight:600;color:#000;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
    .adm-user-last    { font-size:.71rem;color:#444;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
    .adm-unread-badge { background:#00cc00;color:#000;font-size:.65rem;font-weight:700;padding:1px 6px;border-radius:10px;flex-shrink:0; }
    .adm-user-list-header { font-size:.72rem;font-weight:600;color:#555;letter-spacing:.06em;padding:.5rem 1rem .35rem; }
    .adm-chat-header  { padding:.8rem 1.1rem;border-bottom:1px solid rgba(255,255,255,0.07);display:flex;align-items:center;gap:.75rem; }
    .adm-chat-name    { font-size:.88rem;font-weight:600;color:#000; }
    .adm-chat-email   { font-size:.71rem;color:#444; }
    .adm-chat-body    { flex:1;overflow-y:auto;padding:.9rem 1.1rem;display:flex;flex-direction:column;gap:.55rem; }
    .adm-chat-body::-webkit-scrollbar { width:3px; }
    .adm-chat-body::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.12);border-radius:2px; }
    .adm-empty        { flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#666;gap:.6rem; }
    .adm-empty i      { font-size:2.2rem;color:rgba(0,204,0,0.25); }
    .adm-msg          { max-width:72%;display:flex;flex-direction:column;gap:2px; }
    .adm-msg-user     { align-self:flex-start;align-items:flex-start; }
    .adm-msg-admin    { align-self:flex-end;align-items:flex-end; }
    .adm-msg-bubble   { padding:.5rem .85rem;border-radius:14px;font-size:.81rem;line-height:1.5;word-wrap:break-word;white-space:pre-wrap; }
    .adm-msg-user .adm-msg-bubble  { background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#000;border-bottom-left-radius:4px; }
    .adm-msg-admin .adm-msg-bubble { background:rgba(0,204,0,0.16);border:1px solid rgba(0,204,0,0.25);color:#000;border-bottom-right-radius:4px; }
    .adm-msg-time     { font-size:.65rem;color:#666; }
    .adm-chat-footer  { padding:.7rem 1rem;border-top:1px solid rgba(255,255,255,0.07);display:flex;gap:.55rem;align-items:flex-end; }
    .adm-chat-input   { flex:1;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:11px;color:#000;padding:.55rem .85rem;font-size:.82rem;resize:none;font-family:inherit;min-height:40px;max-height:110px;outline:none;transition:border-color .2s; }
    .adm-chat-input:focus { border-color:rgba(0,204,0,0.4); }
    .adm-chat-input::placeholder { color:#888; }
    .adm-send-btn     { width:40px;height:40px;border-radius:11px;background:rgba(0,204,0,0.18);border:1px solid rgba(0,204,0,0.3);color:#00cc00;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;flex-shrink:0; }
    .adm-send-btn:hover:not(:disabled) { background:rgba(0,204,0,0.3); }
    .adm-send-btn:disabled { opacity:.35;cursor:not-allowed; }
    .adm-no-user      { flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#666;gap:.75rem; }
    .adm-no-user i    { font-size:3rem;color:rgba(0,204,0,0.2); }
    .adm-ai-bar       { padding:.35rem 1rem .3rem;border-top:1px solid rgba(255,255,255,0.05);display:flex;gap:.5rem;align-items:center;flex-wrap:wrap; }
    .adm-ai-lbl       { font-size:.65rem;font-weight:600;color:#555;letter-spacing:.05em;margin-right:.25rem; }
    .adm-ai-btn       { display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .7rem;font-size:.72rem;font-weight:600;border-radius:8px;border:1px solid;cursor:pointer;transition:all .2s;background:none;white-space:nowrap; }
    .adm-ai-suggest   { border-color:rgba(0,204,0,0.35);color:#00aa00; }
    .adm-ai-suggest:hover:not(:disabled) { background:rgba(0,204,0,0.1); }
    .adm-ai-rewrite   { border-color:rgba(99,102,241,0.4);color:#818cf8; }
    .adm-ai-rewrite:hover:not(:disabled) { background:rgba(99,102,241,0.1); }
    .adm-ai-btn:disabled { opacity:.4;cursor:not-allowed; }
    .adm-ai-status    { font-size:.68rem;color:#555;margin-left:.35rem;display:none; }
    @media(max-width:768px){
        .adm-user-list { width:100%;height:200px;border-right:none;border-bottom:1px solid rgba(255,255,255,0.07); }
        .adm-sup-layout { flex-direction:column;height:auto; }
        .adm-chat-panel { height:calc(100vh - 440px);min-height:360px; }
    }
</style>

<div class="adm-sup">
<div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="adm-page-title"><i class="bi bi-headset me-2" style="color:#00cc00"></i>Support Inbox</div>
            <div class="adm-page-sub">{{ $users->count() }} user{{ $users->count() !== 1 ? "s" : "" }} with open conversations</div>
        </div>
    </div>

    <div class="adm-card adm-sup-layout">
        <div class="adm-user-list">
            <div class="adm-user-list-header">CONVERSATIONS</div>
            @forelse($users as $u)
            <div class="adm-user-item {{ $loop->first ? "active" : "" }}"
                 data-uid="{{ $u->id }}"
                 data-name="{{ $u->name }}"
                 data-email="{{ $u->email }}"
                 onclick="selectUser(this)">
                <div class="adm-user-avatar">{{ strtoupper(substr($u->name,0,1)) }}</div>
                <div style="min-width:0;flex:1">
                    <div class="adm-user-name">{{ $u->name }}</div>
                    <div class="adm-user-last">{{ $u->email }}</div>
                </div>
                @if($u->unread_count > 0)
                <span class="adm-unread-badge">{{ $u->unread_count }}</span>
                @endif
            </div>
            @empty
            <div style="padding:1.5rem 1rem;font-size:.78rem;color:#555;text-align:center">
                <i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>
                No conversations yet
            </div>
            @endforelse
        </div>

        <div class="adm-chat-panel">
            @if($users->isNotEmpty())
            <div class="adm-chat-header" id="adm-chat-header">
                <div class="adm-user-avatar" id="adm-hdr-avatar">{{ strtoupper(substr($users->first()->name,0,1)) }}</div>
                <div>
                    <div class="adm-chat-name" id="adm-hdr-name">{{ $users->first()->name }}</div>
                    <div class="adm-chat-email" id="adm-hdr-email">{{ $users->first()->email }}</div>
                </div>
            </div>
            <div class="adm-chat-body" id="adm-chat-body"></div>
            <div class="adm-ai-bar" id="adm-ai-bar">
                <span class="adm-ai-lbl"><i class="bi bi-stars me-1"></i>AI</span>
                <button class="adm-ai-btn adm-ai-suggest" id="adm-ai-suggest-btn" title="Generate a reply based on the conversation">
                    <i class="bi bi-magic"></i> Suggest Reply
                </button>
                <button class="adm-ai-btn adm-ai-rewrite" id="adm-ai-rewrite-btn" title="Rewrite your draft more professionally">
                    <i class="bi bi-arrow-repeat"></i> Rewrite Draft
                </button>
                <span class="adm-ai-status" id="adm-ai-status">⏳ Thinking…</span>
            </div>
            <div class="adm-chat-footer">
                <textarea id="adm-chat-input" class="adm-chat-input" rows="1"
                    placeholder="Type a reply..." maxlength="2000"></textarea>
                <button class="adm-send-btn" id="adm-send-btn" disabled>
                    <i class="bi bi-send-fill" style="font-size:.8rem"></i>
                </button>
            </div>
            @else
            <div class="adm-no-user">
                <i class="bi bi-chat-square-dots"></i>
                <span style="font-size:.82rem">No support messages yet</span>
            </div>
            @endif
        </div>
    </div>

</div>
</div>

<script>
(function () {
    var CSRF     = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var chatBody = document.getElementById('adm-chat-body');
    var chatInput= document.getElementById('adm-chat-input');
    var sendBtn  = document.getElementById('adm-send-btn');
    var adminId  = {{ Auth::id() }};
    var activeUid= {{ $users->isNotEmpty() ? $users->first()->id : 'null' }};
    var historyBaseUrl = @json(url('/chat/history'));
    var pollUrl = @json(route('chat.poll'));
    var adminSendUrl = @json(route('chat.send.admin'));
    var aiAssistUrl  = @json(route('chat.ai.assist'));
    var lastId   = 0;
    var pollTimer;

    function escHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function timeAgo(str){
        if(!str) return '';
        var d=new Date(str),now=new Date(),diff=Math.floor((now-d)/1000);
        if(diff<60) return 'just now';
        if(diff<3600) return Math.floor(diff/60)+'m ago';
        if(diff<86400) return Math.floor(diff/3600)+'h ago';
        return d.toLocaleDateString();
    }

    function buildBubble(msg){
        var isAdmin=(msg.sender_id===adminId);
        var cls=isAdmin?'adm-msg-admin':'adm-msg-user';
        var label=isAdmin?'You (Admin)':((msg.sender&&msg.sender.name)||'User');
        var el=document.createElement('div');
        el.className='adm-msg '+cls;
        el.dataset.id=msg.id;
        el.innerHTML='<div class="adm-msg-bubble">'+escHtml(msg.content)+'</div>'
            +'<div class="adm-msg-time">'+label+' &middot; '+timeAgo(msg.created_at)+'</div>';
        return el;
    }

    function appendMessages(msgs){
        if(!msgs||!msgs.length) return;
        var ep=chatBody.querySelector('.adm-empty');
        if(ep) ep.remove();
        msgs.forEach(function(m){
            if(!chatBody.querySelector('.adm-msg[data-id="'+m.id+'"]')){
                chatBody.appendChild(buildBubble(m));
                if(parseInt(m.id)>lastId) lastId=parseInt(m.id);
            }
        });
        chatBody.scrollTop=chatBody.scrollHeight;
        var li=document.querySelector('.adm-user-item[data-uid="'+activeUid+'"]');
        if(li){var b=li.querySelector('.adm-unread-badge');if(b)b.remove();}
    }

    function loadConversation(uid){
        lastId=0;
        chatBody.innerHTML='<div class="adm-empty"><i class="bi bi-hourglass-split"></i><span style="font-size:.78rem">Loading...</span></div>';
        clearInterval(pollTimer);
        fetch(historyBaseUrl+'/'+uid,{headers:{'Accept':'application/json'}})
            .then(function(r){return r.ok?r.json():[];})
            .then(function(msgs){
                chatBody.innerHTML=msgs.length?''
                    :'<div class="adm-empty"><i class="bi bi-chat-dots"></i><span style="font-size:.78rem">No messages yet</span></div>';
                appendMessages(msgs);
                startPoll(uid);
            })
            .catch(function(){startPoll(uid);});
    }

    function startPoll(uid){
        clearInterval(pollTimer);
        pollTimer=setInterval(function(){
            if(uid!==activeUid) return;
            fetch(pollUrl+'?last_id='+lastId,{headers:{'Accept':'application/json'}})
                .then(function(r){return r.ok?r.json():[];})
                .then(function(msgs){
                    var filtered=msgs.filter(function(m){
                        return(m.sender_id===uid&&m.receiver_id===null)||m.receiver_id===uid;
                    });
                    appendMessages(filtered);
                })
                .catch(function(){});
        },5000);
    }

    window.selectUser=function(el){
        document.querySelectorAll('.adm-user-item').forEach(function(i){i.classList.remove('active');});
        el.classList.add('active');
        activeUid=parseInt(el.dataset.uid);
        document.getElementById('adm-hdr-name').textContent=el.dataset.name;
        document.getElementById('adm-hdr-email').textContent=el.dataset.email;
        document.getElementById('adm-hdr-avatar').textContent=el.dataset.name.charAt(0).toUpperCase();
        loadConversation(activeUid);
    };

    function sendReply(){
        var text=chatInput.value.trim();
        if(!text||!activeUid) return;
        sendBtn.disabled=true;
        fetch(adminSendUrl,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({message:text,receiver_id:activeUid})
        })
        .then(function(r){return r.ok?r.json():null;})
        .then(function(data){
            if(data&&data.message) appendMessages([data.message]);
            chatInput.value='';
            chatInput.style.height='';
            sendBtn.disabled=true;
        })
        .catch(function(){})
        .finally(function(){if(chatInput.value.trim()) sendBtn.disabled=false;});
    }

    if(chatInput){
        chatInput.addEventListener('input',function(){
            sendBtn.disabled=!this.value.trim();
            this.style.height='auto';
            this.style.height=Math.min(this.scrollHeight,110)+'px';
        });
        chatInput.addEventListener('keydown',function(e){
            if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();if(!sendBtn.disabled) sendReply();}
        });
        if(sendBtn) sendBtn.addEventListener('click',sendReply);
    }

    // ── AI assist ────────────────────────────────────────────────────────
    var aiSuggestBtn = document.getElementById('adm-ai-suggest-btn');
    var aiRewriteBtn = document.getElementById('adm-ai-rewrite-btn');
    var aiStatus     = document.getElementById('adm-ai-status');

    function aiRequest(mode){
        if(!activeUid) return;
        var draft = chatInput ? chatInput.value.trim() : '';
        if(mode==='rewrite' && !draft){
            alert('Type a draft reply first, then click Rewrite Draft.');
            return;
        }
        if(aiSuggestBtn) aiSuggestBtn.disabled=true;
        if(aiRewriteBtn) aiRewriteBtn.disabled=true;
        if(aiStatus){ aiStatus.style.display='inline'; aiStatus.textContent='⏳ Thinking…'; }

        fetch(aiAssistUrl,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({mode:mode, user_id:activeUid, draft:draft})
        })
        .then(function(r){return r.json();})
        .then(function(data){
            if(data.suggestion){
                if(chatInput){
                    chatInput.value=data.suggestion;
                    chatInput.dispatchEvent(new Event('input'));
                    chatInput.focus();
                }
                if(aiStatus) aiStatus.textContent='✓ Done';
            } else {
                if(aiStatus) aiStatus.textContent='⚠ '+(data.error||'AI failed');
            }
        })
        .catch(function(){
            if(aiStatus) aiStatus.textContent='⚠ Network error';
        })
        .finally(function(){
            if(aiSuggestBtn) aiSuggestBtn.disabled=false;
            if(aiRewriteBtn) aiRewriteBtn.disabled=false;
            setTimeout(function(){ if(aiStatus) aiStatus.style.display='none'; }, 3000);
        });
    }

    if(aiSuggestBtn) aiSuggestBtn.addEventListener('click', function(){ aiRequest('suggest'); });
    if(aiRewriteBtn) aiRewriteBtn.addEventListener('click', function(){ aiRequest('rewrite'); });

    if(activeUid) loadConversation(activeUid);
})();
</script>
@endsection
