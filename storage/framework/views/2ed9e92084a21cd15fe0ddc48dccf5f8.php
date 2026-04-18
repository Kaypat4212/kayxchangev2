

<?php $__env->startPush('styles'); ?>
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}
body{background:var(--kx-dark);color:var(--kx-text);}
.kx-page-header{background:linear-gradient(135deg,#0a1628,#0d1f1a);border:1px solid var(--kx-border);border-radius:16px;padding:24px 28px;margin-bottom:24px;}
.kx-page-header h1{font-size:1.35rem;font-weight:700;color:#fff;margin:0 0 4px;}
.kx-page-header p{color:var(--kx-muted);font-size:.875rem;margin:0;}
.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1.25rem;}
.kx-label{font-size:.75rem;font-weight:600;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;display:block;}
.kx-input{background:var(--kx-card2)!important;border:1px solid var(--kx-border)!important;color:var(--kx-text)!important;border-radius:10px;padding:.7rem 1rem;font-size:.875rem;width:100%;outline:none;transition:border-color .2s;}
.kx-input:focus{border-color:var(--kx-green)!important;box-shadow:0 0 0 3px rgba(0,204,0,.1)!important;}
textarea.kx-input{font-family:monospace;font-size:.82rem;min-height:320px;resize:vertical;}
.btn-kx{background:var(--kx-green);border:none;color:#000;font-weight:700;border-radius:10px;padding:.7rem 1.4rem;font-size:.875rem;cursor:pointer;transition:background .15s;}
.btn-kx:hover{background:#00e600;}
.btn-outline{background:transparent;border:1px solid var(--kx-border);color:var(--kx-text);border-radius:10px;padding:.7rem 1.4rem;font-size:.875rem;cursor:pointer;text-decoration:none;font-weight:600;}
.badge-key{font-family:monospace;font-size:.8rem;color:var(--kx-green);background:rgba(0,204,0,.08);padding:3px 10px;border-radius:6px;display:inline-block;margin-bottom:.75rem;}
.token-pill{display:inline-block;background:rgba(0,204,0,.08);color:var(--kx-green);border:1px solid rgba(0,204,0,.2);font-size:.75rem;font-family:monospace;padding:3px 8px;border-radius:6px;margin:2px;cursor:pointer;}
.token-pill:hover{background:rgba(0,204,0,.18);}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3 px-md-4 py-4">

    <div class="kx-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1><i class="fas fa-edit me-2" style="color:#00cc00;"></i>Edit Email Template</h1>
            <p>Changes take effect immediately for all future emails of this type.</p>
        </div>
        <a href="<?php echo e(route('admin.email-templates')); ?>" class="btn-outline d-inline-flex align-items-center gap-1">
            <i class="fas fa-arrow-left"></i> Back to Templates
        </a>
    </div>

    <?php if($errors->any()): ?>
    <div class="alert alert-danger rounded-3 border-0 mb-3" style="background:rgba(239,68,68,.12);color:#ef4444;">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($error); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('admin.email-templates.update', $template->key)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="kx-card">
            <div class="badge-key"><?php echo e($template->key); ?></div>
            <?php if($template->description): ?>
            <p style="color:var(--kx-muted);font-size:.82rem;margin-bottom:1.25rem;"><?php echo e($template->description); ?></p>
            <?php endif; ?>

            <div class="mb-3">
                <label class="kx-label">Subject Line</label>
                <input id="email-subject-input" type="text" name="subject" class="kx-input <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       value="<?php echo e(old('subject', $template->subject)); ?>">
                <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1" style="font-size:.8rem;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                
                <div style="margin-top:.5rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
                    <button type="button" id="ai-subject-btn" onclick="aiOptimizeSubject()" class="btn-kx-outline" style="font-size:.75rem;padding:.3rem .75rem">
                        <i class="bi bi-robot me-1"></i>AI A/B Suggestions
                    </button>
                    <span style="font-size:.72rem;color:var(--kx-muted)">Generate 5 subject line variants</span>
                </div>
                <div id="ai-subject-out" style="display:none;margin-top:.6rem;display:none">
                    <div id="ai-subject-variants" style="display:flex;flex-direction:column;gap:.35rem"></div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="kx-label mb-0">Body (HTML supported)</label>
                </div>

                
                <div class="mb-2">
                    <?php
                        $tokens = [
                            'user_name','app_name','amount','currency','naira_amount',
                            'wallet_address','reference','payment_method','account_details','reason'
                        ];
                    ?>
                    <?php $__currentLoopData = $tokens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tok): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="token-pill" onclick="insertToken('&#123;&#123;<?php echo e($tok); ?>&#125;&#125;')">&#123;&#123;<?php echo e($tok); ?>&#125;&#125;</span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <textarea id="bodyEditor" name="body" class="kx-input <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('body', $template->body)); ?></textarea>
                <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1" style="font-size:.8rem;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="d-flex gap-2 mt-1">
                <button type="submit" class="btn-kx">
                    <i class="fas fa-save me-1"></i> Save Template
                </button>
                <a href="<?php echo e(route('admin.email-templates')); ?>" class="btn-outline">Cancel</a>
            </div>
        </div>
    </form>

    
    <div class="kx-card mt-3">
        <div style="font-size:.9rem;font-weight:700;color:var(--kx-text);margin-bottom:.75rem;">
            <i class="fas fa-eye me-1" style="color:#00cc00;"></i> Preview (tokens shown as-is)
        </div>
        <div id="bodyPreview"
             style="background:#1e2535;border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:1rem;font-size:.875rem;line-height:1.7;color:#e4e8f0;min-height:80px;">
        </div>
    </div>
</div>

<script>
async function aiOptimizeSubject(){
    const subjectEl = document.getElementById('email-subject-input');
    const btn       = document.getElementById('ai-subject-btn');
    const out       = document.getElementById('ai-subject-out');
    const variants  = document.getElementById('ai-subject-variants');

    const currentSubject = subjectEl.value.trim();
    const templateKey    = '<?php echo e($template->key ?? $template->name ?? "email template"); ?>';
    const context = 'Template: ' + templateKey + (currentSubject ? '. Current subject: ' + currentSubject : '');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating…';
    out.style.display = 'none';
    try {
        const res  = await fetch('<?php echo e(route("ai.email-subject")); ?>', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'},
            body: JSON.stringify({template_context: context})
        });
        const data = await res.json();
        if (data.error) { alert('AI Error: '+data.error); return; }
        const s = data.subjects || [];
        variants.innerHTML = s.map((t,i) =>
            `<button type="button" class="btn-kx-outline" style="text-align:left;justify-content:flex-start;font-size:.8rem;padding:.35rem .7rem"
                onclick="document.getElementById('email-subject-input').value = this.dataset.t"
                data-t="${t.replace(/"/g,'&quot;')}">
                <span style="color:var(--kx-muted);font-size:.7rem;margin-right:.4rem">${i+1}.</span>${t}
            </button>`
        ).join('');
        out.style.display = 'block';
    } catch(e) {
        alert('Request failed: '+e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-robot me-1"></i>AI A/B Suggestions';
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function insertToken(token) {
    const ta = document.getElementById('bodyEditor');
    const start = ta.selectionStart;
    const end   = ta.selectionEnd;
    ta.value    = ta.value.slice(0, start) + token + ta.value.slice(end);
    ta.selectionStart = ta.selectionEnd = start + token.length;
    ta.focus();
    updatePreview();
}

function updatePreview() {
    document.getElementById('bodyPreview').innerHTML =
        document.getElementById('bodyEditor').value;
}

document.getElementById('bodyEditor').addEventListener('input', updatePreview);
updatePreview();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminnavlayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\admin\email-template-edit.blade.php ENDPATH**/ ?>