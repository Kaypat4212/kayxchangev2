

<?php $__env->startSection('content'); ?>
<style>
    :root { --primary-green:#00cc00; }
    body { background:#060e06; color:#e8f5e8; font-family:'Poppins',sans-serif; min-height:100vh; }
    body.light-mode { background:#f2f7f2; color:#1a2e1a; }

    .kx-sup { padding:28px 0 70px; }
    .kx-card {
        background:rgba(255,255,255,0.03);
        border:1px solid rgba(255,255,255,0.07);
        border-radius:20px;
    }
    .kx-page-title { font-size:1.35rem;font-weight:700;color:#e8f5e8;margin-bottom:4px; }
    .kx-page-sub   { font-size:0.8rem;color:rgba(255,255,255,0.4); }

    /* Chat window */
    .kx-chat-wrap   { display:flex;flex-direction:column;height:calc(100vh - 240px);min-height:420px;max-height:700px; }
    .kx-chat-header { padding:.85rem 1.2rem;border-bottom:1px solid rgba(255,255,255,0.07);display:flex;align-items:center;gap:.75rem; }
    .kx-chat-avatar { width:36px;height:36px;border-radius:50%;background:rgba(0,204,0,0.15);border:1.5px solid rgba(0,204,0,0.3);display:flex;align-items:center;justify-content:center;color:#00cc00;font-size:.95rem;flex-shrink:0; }
    .kx-chat-name   { font-size:.88rem;font-weight:600;color:#e8f5e8; }
    .kx-chat-status { font-size:.72rem;color:rgba(255,255,255,0.4);display:flex;align-items:center;gap:4px; }
    .kx-online-dot  { width:7px;height:7px;border-radius:50%;background:#00cc00; }

    .kx-chat-body   { flex:1;overflow-y:auto;padding:.9rem 1.1rem;display:flex;flex-direction:column;gap:.6rem; }
    .kx-chat-body::-webkit-scrollbar { width:3px; }
    .kx-chat-body::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.12);border-radius:2px; }

    .kx-msg          { max-width:72%;display:flex;flex-direction:column;gap:2px; }
    .kx-msg-user     { align-self:flex-end;align-items:flex-end; }
    .kx-msg-admin    { align-self:flex-start;align-items:flex-start; }
    .kx-msg-bubble   { padding:.55rem .9rem;border-radius:16px;font-size:.82rem;line-height:1.5;word-wrap:break-word;white-space:pre-wrap; }
    .kx-msg-user .kx-msg-bubble  { background:rgba(0,204,0,0.18);border:1px solid rgba(0,204,0,0.25);color:#d4f5d4;border-bottom-right-radius:4px; }
    .kx-msg-admin .kx-msg-bubble { background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#e8f5e8;border-bottom-left-radius:4px; }
    .kx-msg-time     { font-size:.66rem;color:rgba(255,255,255,0.25); }

    .kx-chat-footer  { padding:.75rem 1.1rem;border-top:1px solid rgba(255,255,255,0.07);display:flex;gap:.6rem;align-items:flex-end; }
    .kx-chat-input   { flex:1;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:12px;color:#e8f5e8;padding:.6rem .9rem;font-size:.83rem;resize:none;font-family:inherit;min-height:42px;max-height:120px;outline:none;transition:border-color .2s; }
    .kx-chat-input:focus { border-color:rgba(0,204,0,0.4); }
    .kx-chat-input::placeholder { color:rgba(255,255,255,0.25); }
    .kx-send-btn     { width:42px;height:42px;border-radius:12px;background:rgba(0,204,0,0.18);border:1px solid rgba(0,204,0,0.3);color:#00cc00;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;flex-shrink:0; }
    .kx-send-btn:hover:not(:disabled) { background:rgba(0,204,0,0.3);border-color:rgba(0,204,0,0.6); }
    .kx-send-btn:disabled { opacity:.4;cursor:not-allowed; }

    .kx-chat-empty   { flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.75rem;color:rgba(255,255,255,0.25); }
    .kx-chat-empty i { font-size:2.5rem;color:rgba(0,204,0,0.3); }
    .kx-typing       { display:none;font-size:.72rem;color:rgba(255,255,255,0.3);padding:0 1.1rem .4rem; }

    /* Info sidebar */
    .kx-sup-info     { padding:1.2rem; }
    .kx-info-item    { display:flex;align-items:flex-start;gap:.75rem;padding:.75rem 0;border-bottom:1px solid rgba(255,255,255,0.05); }
    .kx-info-item:last-child { border-bottom:none; }
    .kx-info-icon    { width:32px;height:32px;border-radius:9px;background:rgba(0,204,0,0.1);display:flex;align-items:center;justify-content:center;color:#00cc00;font-size:.85rem;flex-shrink:0; }
    .kx-info-label   { font-size:.72rem;color:rgba(255,255,255,0.35);margin-bottom:2px; }
    .kx-info-val     { font-size:.82rem;color:#e8f5e8;font-weight:500; }
</style>

<div class="kx-sup">
<div class="container-xl">

    <!-- Header -->
    <div class="kx-db-head mb-4">
        <div class="kx-page-title"><i class="bi bi-headset me-2" style="color:#00cc00"></i>Support Chat</div>
        <div class="kx-page-sub">Chat directly with our support team. We typically respond within a few minutes.</div>
    </div>

    <div class="row g-4">
        <!-- Chat Panel -->
        <div class="col-12 col-lg-8">
            <div class="kx-card kx-chat-wrap">
                <!-- Header -->
                <div class="kx-chat-header">
                    <div class="kx-chat-avatar"><i class="bi bi-headset"></i></div>
                    <div>
                        <div class="kx-chat-name">KayXchange Support</div>
                        <div class="kx-chat-status">
                            <span class="kx-online-dot"></span>
                            Online — ready to help
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="kx-chat-body" id="kx-chat-body">
                    <div class="kx-chat-empty" id="kx-empty-state">
                        <i class="bi bi-chat-dots"></i>
                        <span>No messages yet.<br>Send a message to start the conversation.</span>
                    </div>
                </div>
                <div class="kx-typing" id="kx-typing">Support is typing…</div>

                <!-- Input -->
                <div class="kx-chat-footer">
                    <textarea id="kx-chat-input" class="kx-chat-input" rows="1"
                        placeholder="Type your message…" maxlength="2000"></textarea>
                    <button class="kx-send-btn" id="kx-send-btn" disabled>
                        <i class="bi bi-send-fill" style="font-size:.85rem"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-12 col-lg-4">
            <div class="kx-card kx-sup-info">
                <div style="font-size:.8rem;font-weight:600;color:rgba(255,255,255,0.5);letter-spacing:.06em;margin-bottom:.5rem">SUPPORT INFO</div>
                <div class="kx-info-item">
                    <div class="kx-info-icon"><i class="bi bi-clock-fill"></i></div>
                    <div>
                        <div class="kx-info-label">Response Time</div>
                        <div class="kx-info-val">Usually within minutes</div>
                    </div>
                </div>
                <div class="kx-info-item">
                    <div class="kx-info-icon"><i class="bi bi-calendar-check-fill"></i></div>
                    <div>
                        <div class="kx-info-label">Available</div>
                        <div class="kx-info-val">Mon – Sat, 9am – 9pm WAT</div>
                    </div>
                </div>
                <div class="kx-info-item">
                    <div class="kx-info-icon"><i class="bi bi-shield-check-fill"></i></div>
                    <div>
                        <div class="kx-info-label">Secure</div>
                        <div class="kx-info-val">All chats are private &amp; encrypted</div>
                    </div>
                </div>
                <div class="kx-info-item">
                    <div class="kx-info-icon"><i class="bi bi-telegram"></i></div>
                    <div>
                        <div class="kx-info-label">Also on Telegram</div>
                        <div class="kx-info-val">
                            <a href="https://t.me/TradewithkayxchangeBOT" target="_blank"
                               style="color:#00cc00;text-decoration:none">@TradewithkayxchangeBOT</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Topics -->
            <div class="kx-card kx-sup-info mt-3">
                <div style="font-size:.8rem;font-weight:600;color:rgba(255,255,255,0.5);letter-spacing:.06em;margin-bottom:.75rem">QUICK TOPICS</div>
                <?php $__currentLoopData = [
                    ['icon'=>'bi-arrow-repeat','text'=>'Trade status inquiry'],
                    ['icon'=>'bi-bank2','text'=>'Withdrawal not received'],
                    ['icon'=>'bi-camera-fill','text'=>'KYC document help'],
                    ['icon'=>'bi-wallet2','text'=>'Wallet / balance issue'],
                    ['icon'=>'bi-question-circle-fill','text'=>'General question'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button class="kx-topic-btn"
                    onclick="prefillTopic('<?php echo e($topic['text']); ?>')">
                    <i class="bi <?php echo e($topic['icon']); ?> me-2" style="color:#00cc00"></i><?php echo e($topic['text']); ?>

                </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

</div>
</div>

<style>
    .kx-topic-btn { width:100%;text-align:left;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);color:rgba(255,255,255,0.65);font-size:.78rem;padding:.5rem .75rem;border-radius:9px;margin-bottom:.4rem;cursor:pointer;transition:all .2s;display:block; }
    .kx-topic-btn:hover { background:rgba(0,204,0,0.08);border-color:rgba(0,204,0,0.2);color:#e8f5e8; }
</style>

<script>
(function () {
    const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const body    = document.getElementById('kx-chat-body');
    const input   = document.getElementById('kx-chat-input');
    const sendBtn = document.getElementById('kx-send-btn');
    const empty   = document.getElementById('kx-empty-state');
    const historyUrl = <?php echo json_encode(route('chat.history'), 15, 512) ?>;
    const pollUrl = <?php echo json_encode(route('chat.poll'), 15, 512) ?>;
    const sendUrl = <?php echo json_encode(route('chat.send'), 15, 512) ?>;
    let lastId = 0;
    let pollTimer;

    // ── Helpers ──────────────────────────────────────────────────────────
    function timeAgo(str) {
        if (!str) return '';
        const d = new Date(str), now = new Date();
        const diff = Math.floor((now - d) / 1000);
        if (diff < 60) return 'just now';
        if (diff < 3600) return Math.floor(diff/60) + 'm ago';
        if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
        return d.toLocaleDateString();
    }

    function buildBubble(msg) {
        const isUser = <?php echo e(Auth::id()); ?> === msg.sender_id && msg.receiver_id === null || <?php echo e(Auth::id()); ?> === msg.sender_id;
        const cls = isUser ? 'kx-msg-user' : 'kx-msg-admin';
        const label = isUser ? 'You' : (msg.sender?.name || 'Support');
        const el = document.createElement('div');
        el.className = `kx-msg ${cls}`;
        el.dataset.id = msg.id;
        el.innerHTML = `<div class="kx-msg-bubble">${escHtml(msg.content)}</div>
            <div class="kx-msg-time">${label} · ${timeAgo(msg.created_at)}</div>`;
        return el;
    }

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function scrollBottom() {
        body.scrollTop = body.scrollHeight;
    }

    function appendMessages(msgs) {
        if (!msgs.length) return;
        if (empty && empty.parentNode) empty.remove();
        msgs.forEach(m => {
            if (!document.querySelector(`.kx-msg[data-id="${m.id}"]`)) {
                body.appendChild(buildBubble(m));
                if (parseInt(m.id) > lastId) lastId = parseInt(m.id);
            }
        });
        scrollBottom();
    }

    // ── Load history ─────────────────────────────────────────────────────
    function loadHistory() {
        fetch(historyUrl, { headers: {'Accept':'application/json'} })
            .then(r => r.ok ? r.json() : [])
            .then(msgs => { appendMessages(msgs); startPoll(); })
            .catch(() => startPoll());
    }

    // ── Polling for new messages ──────────────────────────────────────────
    function poll() {
        fetch(`${pollUrl}?last_id=${lastId}`, { headers: {'Accept':'application/json'} })
            .then(r => r.ok ? r.json() : [])
            .then(msgs => { appendMessages(msgs); })
            .catch(() => {});
    }

    function startPoll() {
        clearInterval(pollTimer);
        pollTimer = setInterval(poll, 6000);
    }

    // ── Send ──────────────────────────────────────────────────────────────
    function sendMessage() {
        const text = input.value.trim();
        if (!text) return;
        sendBtn.disabled = true;
        fetch(sendUrl, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body: JSON.stringify({ message: text })
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (data?.message) appendMessages([data.message]);
            input.value = '';
            input.style.height = '';
            sendBtn.disabled = true;
        })
        .catch(() => {})
        .finally(() => { if (input.value.trim()) sendBtn.disabled = false; });
    }

    // ── Input events ──────────────────────────────────────────────────────
    input.addEventListener('input', function() {
        sendBtn.disabled = !this.value.trim();
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); if (!sendBtn.disabled) sendMessage(); }
    });
    sendBtn.addEventListener('click', sendMessage);

    // ── Init ──────────────────────────────────────────────────────────────
    function prefillTopic(text) {
        input.value = `Hi, I need help with: ${text}`;
        input.dispatchEvent(new Event('input'));
        input.focus();
    }
    window.prefillTopic = prefillTopic;

    loadHistory();
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/support/chat.blade.php ENDPATH**/ ?>