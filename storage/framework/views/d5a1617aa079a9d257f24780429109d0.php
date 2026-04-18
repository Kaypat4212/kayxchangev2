

<?php $__env->startSection('content'); ?>
<style>
    :root {
        --kx-green: #00cc00; --kx-green-dim: rgba(0,204,0,0.12);
        --kx-dark: #0d1117;  --kx-card: #161b27; --kx-card2: #1e2535;
        --kx-border: rgba(255,255,255,0.07); --kx-text: #e4e8f0;
        --kx-muted: #7a8599; --kx-danger: #ef4444; --kx-warning: #f59e0b;
        --kx-info: #38bdf8;  --kx-tg: #2aabee;
    }
    body { background: var(--kx-dark); color: var(--kx-text); font-family: 'Poppins', sans-serif; }

    .kx-panel { background: var(--kx-card); border: 1px solid var(--kx-border);
        border-radius: 12px; margin-bottom: 20px; overflow: hidden; }
    .kx-panel-header { padding: 14px 20px; border-bottom: 1px solid var(--kx-border);
        display: flex; align-items: center; justify-content: space-between; }
    .kx-panel-header h5 { margin: 0; font-size: 0.95rem; font-weight: 600; }
    .kx-panel-body { padding: 20px; }

    .kx-stat-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(150px,1fr)); gap: 14px; margin-bottom: 24px; }
    .kx-stat { background: var(--kx-card2); border: 1px solid var(--kx-border);
        border-radius: 10px; padding: 16px; text-align: center; }
    .kx-stat-val { font-size: 1.6rem; font-weight: 700; color: var(--kx-tg); }
    .kx-stat-label { font-size: 0.76rem; color: var(--kx-muted); margin-top: 4px; }

    .kx-badge { display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 9px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; white-space: nowrap; }
    .kx-badge-blue   { background: rgba(42,171,238,0.12); color: #2aabee; }
    .kx-badge-green  { background: var(--kx-green-dim);   color: var(--kx-green); }
    .kx-badge-amber  { background: rgba(245,158,11,0.12);  color: #f59e0b; }
    .kx-badge-purple { background: rgba(167,139,250,0.12); color: #a78bfa; }
    .kx-badge-red    { background: rgba(239,68,68,0.12);   color: #ef4444; }

    .kx-input { background: var(--kx-card2); border: 1px solid var(--kx-border);
        color: var(--kx-text); border-radius: 8px; padding: 8px 14px; font-size: 0.85rem; }
    .kx-input:focus { outline: none; border-color: var(--kx-tg); }
    .kx-btn { padding: 8px 18px; border-radius: 8px; font-size: 0.85rem;
        font-weight: 600; border: none; cursor: pointer; }
    .kx-btn-tg  { background: var(--kx-tg); color: #fff; }
    .kx-btn-muted { background: var(--kx-card2); color: var(--kx-muted); border:1px solid var(--kx-border); }

    .msg-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
    .msg-table th { color: var(--kx-muted); font-weight: 600; font-size: 0.75rem;
        text-transform: uppercase; letter-spacing: .04em; padding: 10px 14px;
        border-bottom: 1px solid var(--kx-border); text-align: left; }
    .msg-table td { padding: 11px 14px; border-bottom: 1px solid var(--kx-border);
        vertical-align: middle; }
    .msg-table tr:last-child td { border-bottom: none; }
    .msg-table tr:hover td { background: rgba(255,255,255,0.02); }

    .avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--kx-tg);
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.85rem; color: #fff; flex-shrink: 0; }
    .msg-text { max-width: 340px; overflow: hidden; text-overflow: ellipsis;
        white-space: nowrap; color: var(--kx-text); }
    .msg-meta { font-size: 0.75rem; color: var(--kx-muted); }

    /* Media cells */
    .msg-thumb { width: 72px; height: 72px; object-fit: cover; border-radius: 8px;
        cursor: zoom-in; border: 1px solid var(--kx-border); display: block; }
    .msg-thumb:hover { opacity: .85; }
    .msg-file { display: flex; align-items: center; gap: 8px; }
    .msg-file-icon { font-size: 1.6rem; flex-shrink: 0; }
    .msg-file-name { font-size: 0.8rem; color: var(--kx-text); max-width:200px;
        overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .msg-sticker { font-size: 2rem; line-height: 1; }
    .msg-emoji-text { font-size: 0.9rem; line-height: 1.45; max-width:320px;
        word-break: break-word; white-space: pre-wrap; }

    .pagination { display: flex; gap: 6px; justify-content: center; margin-top: 16px; }
    .pagination .page-link { background: var(--kx-card2); border: 1px solid var(--kx-border);
        color: var(--kx-text); padding: 6px 12px; border-radius: 6px; font-size: 0.82rem; text-decoration: none; }
    .pagination .page-item.active .page-link { background: var(--kx-tg); border-color: var(--kx-tg); color: #fff; }
    .pagination .page-item.disabled .page-link { opacity: .4; pointer-events: none; }
</style>

<div class="container-fluid py-4 px-4">

    
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold">💬 Bot Message Inbox</h4>
            <span class="kx-badge kx-badge-blue">All user messages received by the Telegram bot</span>
        </div>
        <a href="<?php echo e(route('admin.telegram.index')); ?>" class="kx-btn kx-btn-muted">← Bot Settings</a>
    </div>

    
    <div class="kx-stat-grid">
        <div class="kx-stat">
            <div class="kx-stat-val"><?php echo e(number_format($totalMessages)); ?></div>
            <div class="kx-stat-label">Total Messages</div>
        </div>
        <div class="kx-stat">
            <div class="kx-stat-val"><?php echo e(number_format($uniqueSenders)); ?></div>
            <div class="kx-stat-label">Unique Users</div>
        </div>
        <div class="kx-stat">
            <div class="kx-stat-val"><?php echo e(number_format($todayMessages)); ?></div>
            <div class="kx-stat-label">Today</div>
        </div>
    </div>

    
    <div class="kx-panel mb-4">
        <div class="kx-panel-body">
            <form method="GET" class="d-flex gap-3 flex-wrap align-items-end">
                <div>
                    <label class="msg-meta mb-1 d-block">Search</label>
                    <input name="search" value="<?php echo e(request('search')); ?>" placeholder="Name, username, chat ID, message…"
                        class="kx-input" style="width:280px;">
                </div>
                <div>
                    <label class="msg-meta mb-1 d-block">Type</label>
                    <select name="type" class="kx-input">
                        <option value="">All types</option>
                        <option value="text"     <?php echo e(request('type')=='text'     ? 'selected':''); ?>>💬 Text</option>
                        <option value="command"  <?php echo e(request('type')=='command'  ? 'selected':''); ?>>⌨️ Commands</option>
                        <option value="photo"    <?php echo e(request('type')=='photo'    ? 'selected':''); ?>>📸 Photos</option>
                        <option value="document" <?php echo e(request('type')=='document' ? 'selected':''); ?>>📄 Documents</option>
                        <option value="sticker"  <?php echo e(request('type')=='sticker'  ? 'selected':''); ?>>🎭 Stickers</option>
                        <option value="video"    <?php echo e(request('type')=='video'    ? 'selected':''); ?>>🎥 Videos</option>
                        <option value="audio"    <?php echo e(request('type')=='audio'    ? 'selected':''); ?>>🎵 Audio</option>
                        <option value="voice"    <?php echo e(request('type')=='voice'    ? 'selected':''); ?>>🎤 Voice</option>
                    </select>
                </div>
                <button type="submit" class="kx-btn kx-btn-tg">🔍 Filter</button>
                <?php if(request()->hasAny(['search','type'])): ?>
                    <a href="<?php echo e(route('admin.telegram.messages')); ?>" class="kx-btn kx-btn-muted">✕ Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    
    <div class="kx-panel">
        <div class="kx-panel-header">
            <h5>📨 Messages <span class="kx-badge kx-badge-blue ms-2"><?php echo e($messages->total()); ?> results</span></h5>
        </div>
        <div class="kx-panel-body p-0">
            <?php if($messages->isEmpty()): ?>
                <div class="text-center py-5" style="color:var(--kx-muted)">
                    <div style="font-size:2.5rem">💬</div>
                    <div class="mt-2">No messages yet. They will appear here once users message the bot.</div>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="msg-table">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>State</th>
                            <th>Linked Account</th>
                            <th>Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar"><?php echo e(strtoupper(substr($msg->first_name ?: ($msg->username ?: '?'), 0, 1))); ?></div>
                                    <div>
                                        <div class="fw-semibold" style="font-size:0.85rem">
                                            <?php echo e($msg->first_name ?: ('Chat #'.$msg->chat_id)); ?>

                                        </div>
                                        <?php if($msg->username): ?>
                                            <div class="msg-meta"><?php echo e('@'.$msg->username); ?></div>
                                        <?php endif; ?>
                                        <div class="msg-meta">ID: <?php echo e($msg->chat_id); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php $mt = $msg->message_type; ?>
                                <?php if($mt === 'photo' && $msg->file_id): ?>
                                    <img src="<?php echo e(route('admin.telegram.file', $msg->file_id)); ?>"
                                         class="msg-thumb"
                                         loading="lazy"
                                         alt="photo"
                                         onclick="openImgPreview(this.src)">
                                    <?php if($msg->message_text && $msg->message_text !== '[photo]'): ?>
                                        <div class="msg-meta mt-1" style="max-width:260px; white-space:normal"><?php echo e($msg->message_text); ?></div>
                                    <?php endif; ?>
                                <?php elseif($mt === 'sticker'): ?>
                                    <span class="msg-sticker" title="Sticker"><?php echo e($msg->message_text); ?></span>
                                    <?php if($msg->file_name): ?>
                                        <div class="msg-meta"><?php echo e($msg->file_name); ?></div>
                                    <?php endif; ?>
                                <?php elseif(in_array($mt, ['document','video','audio','voice'])): ?>
                                    <div class="msg-file">
                                        <span class="msg-file-icon">
                                            <?php if($mt === 'video'): ?> 🎥
                                            <?php elseif($mt === 'audio'): ?> 🎵
                                            <?php elseif($mt === 'voice'): ?> 🎤
                                            <?php else: ?> 📄
                                            <?php endif; ?>
                                        </span>
                                        <div>
                                            <div class="msg-file-name"><?php echo e($msg->file_name ?: $msg->message_text ?: '—'); ?></div>
                                            <?php if($msg->file_id): ?>
                                                <a href="<?php echo e(route('admin.telegram.file', $msg->file_id)); ?>"
                                                   target="_blank"
                                                   style="font-size:0.72rem; color:var(--kx-tg)">⬇ Download</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="msg-emoji-text" title="<?php echo e($msg->message_text); ?>"><?php echo e($msg->message_text ?? '—'); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $typeMap = [
                                        'command'  => ['label'=>'Command',  'class'=>'kx-badge-purple','icon'=>'⌨️'],
                                        'photo'    => ['label'=>'Photo',    'class'=>'kx-badge-amber', 'icon'=>'📸'],
                                        'text'     => ['label'=>'Text',     'class'=>'kx-badge-blue',  'icon'=>'💬'],
                                        'sticker'  => ['label'=>'Sticker',  'class'=>'kx-badge-green', 'icon'=>'🎭'],
                                        'document' => ['label'=>'Document', 'class'=>'kx-badge-blue',  'icon'=>'📄'],
                                        'video'    => ['label'=>'Video',    'class'=>'kx-badge-purple','icon'=>'🎥'],
                                        'audio'    => ['label'=>'Audio',    'class'=>'kx-badge-amber', 'icon'=>'🎵'],
                                        'voice'    => ['label'=>'Voice',    'class'=>'kx-badge-amber', 'icon'=>'🎤'],
                                    ];
                                    $t = $typeMap[$msg->message_type] ?? ['label'=>ucfirst($msg->message_type),'class'=>'kx-badge-blue','icon'=>'•'];
                                ?>
                                <span class="kx-badge <?php echo e($t['class']); ?>"><?php echo e($t['icon']); ?> <?php echo e($t['label']); ?></span>
                            </td>
                            <td>
                                <?php if($msg->state_at_time): ?>
                                    <span class="kx-badge kx-badge-amber" style="font-size:0.7rem"><?php echo e($msg->state_at_time); ?></span>
                                <?php else: ?>
                                    <span class="msg-meta">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($msg->user): ?>
                                    <div style="font-size:0.82rem">
                                        <div class="fw-semibold"><?php echo e($msg->user->name); ?></div>
                                        <div class="msg-meta"><?php echo e($msg->user->email); ?></div>
                                    </div>
                                    <span class="kx-badge kx-badge-green mt-1">Linked ✓</span>
                                <?php else: ?>
                                    <span class="kx-badge kx-badge-red">Not linked</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size:0.82rem; white-space:nowrap">
                                    <?php echo e($msg->created_at->format('d M Y')); ?>

                                </div>
                                <div class="msg-meta"><?php echo e($msg->created_at->format('H:i:s')); ?></div>
                            </td>
                            <td>
                                <button
                                    onclick="openReply(<?php echo e($msg->chat_id); ?>, '<?php echo e(addslashes($msg->first_name ?? ('@'.($msg->username ?? 'Chat #'.$msg->chat_id)))); ?>', <?php echo json_encode($msg->message_text ?? '', 15, 512) ?>)"
                                    class="kx-btn kx-btn-tg"
                                    style="font-size:0.78rem; padding:5px 13px; white-space:nowrap">
                                    💬 Reply
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            
            <?php if($messages->hasPages()): ?>
            <div class="p-3">
                <?php echo e($messages->links()); ?>

            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<div id="imgPreviewModal" style="display:none; position:fixed; inset:0; z-index:10000;
     background:rgba(0,0,0,0.88); align-items:center; justify-content:center; cursor:zoom-out;"
     onclick="closeImgPreview()">
    <img id="imgPreviewSrc" src="" alt="preview"
         style="max-width:92vw; max-height:92vh; border-radius:10px; object-fit:contain; pointer-events:none;">
</div>


<div id="replyModal" style="display:none; position:fixed; inset:0; z-index:9999;
     background:rgba(0,0,0,0.7); align-items:center; justify-content:center;">
    <div style="background:var(--kx-card); border:1px solid var(--kx-border); border-radius:14px;
                width:100%; max-width:520px; padding:28px 28px 24px; position:relative; max-height:90vh; overflow-y:auto;">
        <button onclick="closeReply()" style="position:absolute; top:14px; right:16px;
            background:none; border:none; color:var(--kx-muted); font-size:1.2rem; cursor:pointer;">✕</button>

        <h5 style="margin:0 0 2px; font-weight:700;">💬 Reply to <span id="replyName"></span></h5>
        <p class="msg-meta" style="margin-bottom:16px;">Chat ID: <span id="replyChatIdDisplay"></span></p>

        
        <div id="replyContext" style="background:var(--kx-card2); border:1px solid var(--kx-border);
             border-left:3px solid var(--kx-tg); border-radius:8px; padding:10px 14px; margin-bottom:16px; display:none;">
            <div class="msg-meta" style="margin-bottom:4px;">📨 Their message:</div>
            <div id="replyContextText" style="font-size:0.84rem; color:var(--kx-text); white-space:pre-wrap; word-break:break-word; max-height:80px; overflow-y:auto;"></div>
        </div>

        
        <div style="margin-bottom:12px;">
            <div class="msg-meta" style="margin-bottom:7px;">⚡ Quick replies:</div>
            <div style="display:flex; flex-wrap:wrap; gap:6px;">
                <button onclick="setQuickReply('Hi! Thank you for reaching out to KayXchange support. How can I help you today?')"
                    class="kx-badge kx-badge-blue" style="cursor:pointer; font-size:0.72rem; padding:5px 10px; border:none;">👋 Greeting</button>
                <button onclick="setQuickReply('Great news! Your trade has been approved and is being processed. You will receive your funds shortly.')"
                    class="kx-badge kx-badge-green" style="cursor:pointer; font-size:0.72rem; padding:5px 10px; border:none;">✅ Trade Approved</button>
                <button onclick="setQuickReply('Your trade is currently under review. Our team will update you within 30 minutes. Thank you for your patience.')"
                    class="kx-badge kx-badge-amber" style="cursor:pointer; font-size:0.72rem; padding:5px 10px; border:none;">⏳ Under Review</button>
                <button onclick="setQuickReply('Could you please provide more information so we can assist you better? You can also complete your KYC verification for faster processing.')"
                    class="kx-badge kx-badge-purple" style="cursor:pointer; font-size:0.72rem; padding:5px 10px; border:none;">📋 Need More Info</button>
                <button onclick="setQuickReply('We are sorry for the inconvenience. Our technical team is looking into this and will resolve it as soon as possible.')"
                    class="kx-badge kx-badge-red" style="cursor:pointer; font-size:0.72rem; padding:5px 10px; border:none;">🛠 Investigating</button>
            </div>
        </div>

        <form id="replyForm" method="POST" action="<?php echo e(route('admin.telegram.reply')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="chat_id" id="replyChatId">

            
            <div style="display:flex; justify-content:flex-end; margin-bottom:7px;">
                <button type="button" id="aiSuggestBtn" onclick="aiSuggestReply()"
                    style="background:linear-gradient(135deg,#7c3aed,#4f46e5); color:#fff; border:none;
                           border-radius:8px; padding:6px 14px; font-size:0.78rem; font-weight:600; cursor:pointer;
                           display:flex; align-items:center; gap:6px;">
                    <span id="aiSuggestIcon">✨</span>
                    <span id="aiSuggestLabel">AI Suggest</span>
                </button>
            </div>

            <textarea name="message" id="replyMessage" rows="5" placeholder="Type your message…"
                class="kx-input" style="width:100%; resize:vertical; margin-bottom:14px;"
                required maxlength="4096"></textarea>

            
            <div id="aiSuggestionBox" style="display:none; background:rgba(79,70,229,0.08);
                 border:1px solid rgba(79,70,229,0.25); border-radius:8px; padding:12px 14px; margin-bottom:14px;">
                <div style="font-size:0.72rem; color:#a78bfa; font-weight:600; margin-bottom:7px;">✨ AI Suggestion:</div>
                <div id="aiSuggestionText" style="font-size:0.83rem; color:var(--kx-text); line-height:1.55; white-space:pre-wrap;"></div>
                <div style="margin-top:10px; display:flex; gap:8px;">
                    <button type="button" onclick="useAiSuggestion()"
                        style="background:#4f46e5; color:#fff; border:none; border-radius:6px;
                               padding:5px 14px; font-size:0.75rem; font-weight:600; cursor:pointer;">
                        ✓ Use this
                    </button>
                    <button type="button" onclick="document.getElementById('aiSuggestionBox').style.display='none'"
                        style="background:var(--kx-card2); color:var(--kx-muted); border:1px solid var(--kx-border);
                               border-radius:6px; padding:5px 12px; font-size:0.75rem; cursor:pointer;">
                        Dismiss
                    </button>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-end">
                <button type="button" onclick="closeReply()" class="kx-btn kx-btn-muted">Cancel</button>
                <button type="submit" class="kx-btn kx-btn-tg">📤 Send Reply</button>
            </div>
        </form>
    </div>
</div>

<script>
function openImgPreview(src) {
    document.getElementById('imgPreviewSrc').src = src;
    document.getElementById('imgPreviewModal').style.display = 'flex';
}
function closeImgPreview() {
    document.getElementById('imgPreviewModal').style.display = 'none';
    document.getElementById('imgPreviewSrc').src = '';
}

let _replyUserMessage = '';

function openReply(chatId, name, userMessage) {
    _replyUserMessage = userMessage || '';
    document.getElementById('replyChatId').value = chatId;
    document.getElementById('replyChatIdDisplay').textContent = chatId;
    document.getElementById('replyName').textContent = name;
    document.getElementById('replyMessage').value = '';
    document.getElementById('aiSuggestionBox').style.display = 'none';

    // Show original message context
    const ctx     = document.getElementById('replyContext');
    const ctxText = document.getElementById('replyContextText');
    if (userMessage && userMessage.trim()) {
        ctxText.textContent = userMessage;
        ctx.style.display = 'block';
    } else {
        ctx.style.display = 'none';
    }

    const modal = document.getElementById('replyModal');
    modal.style.display = 'flex';
    setTimeout(() => document.getElementById('replyMessage').focus(), 50);
}

function closeReply() {
    document.getElementById('replyModal').style.display = 'none';
}

function setQuickReply(text) {
    document.getElementById('replyMessage').value = text;
    document.getElementById('replyMessage').focus();
}

function useAiSuggestion() {
    document.getElementById('replyMessage').value =
        document.getElementById('aiSuggestionText').textContent;
    document.getElementById('aiSuggestionBox').style.display = 'none';
    document.getElementById('replyMessage').focus();
}

async function aiSuggestReply() {
    const chatId  = document.getElementById('replyChatId').value;
    const userMsg = _replyUserMessage || document.getElementById('replyMessage').value || 'Hello';
    const btn     = document.getElementById('aiSuggestBtn');
    const icon    = document.getElementById('aiSuggestIcon');
    const label   = document.getElementById('aiSuggestLabel');

    btn.disabled = true;
    icon.textContent  = '⏳';
    label.textContent = 'Thinking…';

    try {
        const res = await fetch('<?php echo e(route("admin.telegram.ai-suggest")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') || {}).content || '<?php echo e(csrf_token()); ?>'
            },
            body: JSON.stringify({ chat_id: chatId, user_message: userMsg })
        });
        const data = await res.json();

        if (data.error) {
            label.textContent = '✗ Failed';
            setTimeout(() => { icon.textContent = '✨'; label.textContent = 'AI Suggest'; btn.disabled = false; }, 2500);
            return;
        }

        document.getElementById('aiSuggestionText').textContent = data.suggestion || '';
        document.getElementById('aiSuggestionBox').style.display = 'block';
    } catch (e) {
        label.textContent = '✗ Error';
        setTimeout(() => { icon.textContent = '✨'; label.textContent = 'AI Suggest'; btn.disabled = false; }, 2000);
        return;
    }

    icon.textContent  = '✨';
    label.textContent = 'AI Suggest';
    btn.disabled = false;
}

document.getElementById('replyModal').addEventListener('click', function(e) {
    if (e.target === this) closeReply();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeReply(); closeImgPreview(); }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminnavlayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/admin/telegram-messages.blade.php ENDPATH**/ ?>