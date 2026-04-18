

<?php $__env->startPush('styles'); ?>
<style>
:root{
    --kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
    --kx-danger:#ef4444;--kx-warning:#f59e0b;
}
body{background:var(--kx-dark);color:var(--kx-text);}
.kx-page-header{background:linear-gradient(135deg,#0a1628,#0d1f1a);border:1px solid var(--kx-border);border-radius:16px;padding:24px 28px;margin-bottom:24px;}
.kx-page-header h1{font-size:1.35rem;font-weight:700;color:#fff;margin:0 0 4px;}
.kx-page-header p{color:var(--kx-muted);font-size:.875rem;margin:0;}
.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1.25rem;}
.kx-card-title{font-size:.95rem;font-weight:700;color:var(--kx-text);margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;}
.kx-card-title i{color:var(--kx-green);}
.kx-label{font-size:.75rem;font-weight:600;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;display:block;}
.kx-input{background:var(--kx-card2)!important;border:1px solid var(--kx-border)!important;color:var(--kx-text)!important;border-radius:10px;padding:.7rem 1rem;font-size:.875rem;width:100%;outline:none;transition:border-color .2s;}
.kx-input:focus{border-color:var(--kx-green)!important;box-shadow:0 0 0 3px rgba(0,204,0,.1)!important;}
.kx-select{background:var(--kx-card2)!important;border:1px solid var(--kx-border)!important;color:var(--kx-text)!important;border-radius:10px;padding:.7rem 1rem;font-size:.875rem;width:100%;outline:none;}
.kx-select option{background:var(--kx-card2);color:var(--kx-text);}
.btn-kx{background:var(--kx-green);border:none;color:#000;font-weight:700;border-radius:10px;padding:.7rem 1.4rem;font-size:.875rem;cursor:pointer;transition:background .15s;}
.btn-kx:hover{background:#00e600;}
.btn-kx-outline{background:var(--kx-card2);border:1px solid var(--kx-border);color:var(--kx-text);font-weight:600;border-radius:10px;padding:.7rem 1.4rem;font-size:.875rem;cursor:pointer;transition:background .15s;}
.btn-kx-outline:hover{background:rgba(255,255,255,.06);}
.btn-kx-danger{background:var(--kx-danger);border:none;color:#fff;font-weight:700;border-radius:10px;padding:.5rem 1rem;font-size:.8rem;cursor:pointer;}
/* Toggle switch */
.kx-toggle{display:flex;align-items:center;gap:.75rem;}
.kx-toggle input[type=checkbox]{display:none;}
.kx-toggle label{position:relative;display:inline-block;width:44px;height:24px;cursor:pointer;}
.kx-toggle label::before{content:'';position:absolute;inset:0;background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;transition:background .2s;}
.kx-toggle label::after{content:'';position:absolute;top:3px;left:3px;width:16px;height:16px;background:#fff;border-radius:50%;transition:transform .2s;}
.kx-toggle input:checked + label::before{background:rgba(0,204,0,.3);border-color:var(--kx-green);}
.kx-toggle input:checked + label::after{transform:translateX(20px);background:var(--kx-green);}
.kx-toggle-text{font-size:.875rem;color:var(--kx-text);}
/* Stats */
.kx-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1.5rem;}
@media(max-width:576px){.kx-stats{grid-template-columns:1fr 1fr;}}
.kx-stat{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:1rem;text-align:center;}
.s-val{font-size:1.5rem;font-weight:700;color:var(--kx-green);}
.s-val.danger{color:var(--kx-danger);}
.s-lbl{font-size:.72rem;color:var(--kx-muted);text-transform:uppercase;margin-top:.2rem;}
/* Table */
.kx-table{width:100%;border-collapse:collapse;}
.kx-table th{font-size:.72rem;font-weight:600;color:var(--kx-muted);text-transform:uppercase;padding:.6rem .75rem;border-bottom:1px solid var(--kx-border);}
.kx-table td{padding:.8rem .75rem;border-bottom:1px solid var(--kx-border);font-size:.82rem;vertical-align:middle;}
.kx-table tr:last-child td{border-bottom:none;}
.kx-table tbody tr:hover td{background:rgba(255,255,255,.02);}
.status-pill{display:inline-flex;align-items:center;gap:.3rem;border-radius:20px;padding:.2rem .65rem;font-size:.72rem;font-weight:600;}
.status-pill.success{background:rgba(0,204,0,.12);color:var(--kx-green);border:1px solid rgba(0,204,0,.2);}
.status-pill.failed{background:rgba(239,68,68,.1);color:var(--kx-danger);border:1px solid rgba(239,68,68,.2);}
/* Alert */
.kx-alert{padding:.85rem 1.25rem;border-radius:10px;margin-bottom:1rem;font-size:.875rem;}
.kx-alert.success{background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.2);color:var(--kx-green);}
.kx-alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:var(--kx-danger);}
/* Filter bar */
.filter-bar{display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;margin-bottom:1rem;}
.filter-bar .kx-input,.filter-bar .kx-select{width:auto;flex:1;min-width:120px;}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid px-3 py-4">

    <?php if(session('success')): ?>
        <div class="kx-alert success"><i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="kx-alert error"><i class="bi bi-x-circle me-2"></i><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <div class="kx-page-header">
        <h1><i class="bi bi-envelope-gear-fill me-2" style="color:var(--kx-green);"></i>Email Settings & Login Logs</h1>
        <p>Configure SMTP settings, manage email notifications, and view login activity</p>
    </div>

    <div class="row g-3">

        
        <div class="col-lg-5">

            
            <div class="kx-card">
                <div class="kx-card-title"><i class="bi bi-gear-fill"></i>SMTP Configuration</div>

                
                <div style="margin-bottom:1.25rem;">
                    <div style="font-size:.72rem;font-weight:600;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.6rem;">Quick Setup Presets</div>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem;">
                        <button type="button" class="preset-btn" onclick="applyPreset('cpanel')"
                            style="background:#1e2535;border:1px solid rgba(255,150,0,0.2);border-radius:10px;padding:.65rem .75rem;cursor:pointer;text-align:left;transition:all .15s;">
                            <div style="font-size:.8rem;font-weight:700;color:#f59e0b;">&#127968; cPanel</div>
                            <div style="font-size:.7rem;color:var(--kx-muted);margin-top:2px;">Port 465 · SSL</div>
                        </button>
                        <button type="button" class="preset-btn" onclick="applyPreset('gmail')"
                            style="background:#1e2535;border:1px solid rgba(66,133,244,0.2);border-radius:10px;padding:.65rem .75rem;cursor:pointer;text-align:left;transition:all .15s;">
                            <div style="font-size:.8rem;font-weight:700;color:#4285f4;">&#9993; Gmail</div>
                            <div style="font-size:.7rem;color:var(--kx-muted);margin-top:2px;">Port 587 · TLS</div>
                        </button>
                        <button type="button" class="preset-btn" onclick="applyPreset('mailpit')"
                            style="background:#1e2535;border:1px solid rgba(139,92,246,0.2);border-radius:10px;padding:.65rem .75rem;cursor:pointer;text-align:left;transition:all .15s;">
                            <div style="font-size:.8rem;font-weight:700;color:#8b5cf6;">&#128268; Mailpit / MailHog</div>
                            <div style="font-size:.7rem;color:var(--kx-muted);margin-top:2px;">Local dev · Port 1025</div>
                        </button>
                        <button type="button" class="preset-btn" onclick="applyPreset('mailtrap')"
                            style="background:#1e2535;border:1px solid rgba(16,185,129,0.2);border-radius:10px;padding:.65rem .75rem;cursor:pointer;text-align:left;transition:all .15s;">
                            <div style="font-size:.8rem;font-weight:700;color:#10b981;">&#128270; Mailtrap</div>
                            <div style="font-size:.7rem;color:var(--kx-muted);margin-top:2px;">Testing · Port 2525</div>
                        </button>
                    </div>
                    <div id="preset-note" style="display:none;margin-top:.6rem;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:8px;padding:.6rem .85rem;font-size:.75rem;color:#f59e0b;"></div>
                </div>

                <form method="POST" action="<?php echo e(route('admin.email-settings.update')); ?>" id="smtp-form">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="kx-label">Mail Driver</label>
                            <select name="mail_mailer" id="field_mailer" class="kx-select">
                                <?php $__currentLoopData = ['smtp','sendmail','log','array']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($m); ?>" <?php echo e($settings->mail_mailer===$m?'selected':''); ?>><?php echo e(strtoupper($m)); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-8">
                            <label class="kx-label">SMTP Host</label>
                            <input type="text" name="mail_host" id="field_host" class="kx-input" value="<?php echo e($settings->mail_host); ?>" placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-4">
                            <label class="kx-label">Port</label>
                            <input type="number" name="mail_port" id="field_port" class="kx-input" value="<?php echo e($settings->mail_port); ?>" placeholder="587">
                        </div>
                        <div class="col-12">
                            <label class="kx-label">Encryption</label>
                            <select name="mail_encryption" id="field_encryption" class="kx-select">
                                <option value="tls" <?php echo e($settings->mail_encryption==='tls'?'selected':''); ?>>TLS</option>
                                <option value="ssl" <?php echo e($settings->mail_encryption==='ssl'?'selected':''); ?>>SSL</option>
                                <option value="" <?php echo e(!$settings->mail_encryption?'selected':''); ?>>None</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="kx-label">SMTP Username</label>
                            <input type="text" name="mail_username" id="field_username" class="kx-input" value="<?php echo e($settings->mail_username); ?>" placeholder="your@email.com" autocomplete="off">
                        </div>
                        <div class="col-12">
                            <label class="kx-label">SMTP Password <span style="text-transform:none;font-weight:400;">(leave blank to keep current)</span></label>
                            <input type="password" name="mail_password" class="kx-input" placeholder="••••••••" autocomplete="new-password">
                        </div>
                        <div class="col-12">
                            <label class="kx-label">From Address</label>
                            <input type="email" name="mail_from_address" class="kx-input" value="<?php echo e($settings->mail_from_address); ?>" placeholder="noreply@kayxchange.com">
                        </div>
                        <div class="col-12">
                            <label class="kx-label">From Name</label>
                            <input type="text" name="mail_from_name" class="kx-input" value="<?php echo e($settings->mail_from_name); ?>" placeholder="KayXchange">
                        </div>
                    </div>

                    
                    <hr style="border-color:var(--kx-border);margin:1.5rem 0;">
                    <div class="kx-card-title mb-3"><i class="bi bi-toggles2"></i>Email Notifications</div>

                    <div class="d-flex flex-column gap-3">
                        <div class="kx-toggle">
                            <input type="checkbox" id="toggle_welcome" name="welcome_email_enabled" value="1"
                                <?php echo e($settings->welcome_email_enabled ? 'checked' : ''); ?>>
                            <label for="toggle_welcome"></label>
                            <div>
                                <div class="kx-toggle-text">Welcome Email</div>
                                <div style="font-size:.75rem;color:var(--kx-muted);">Sent when a new user registers</div>
                            </div>
                        </div>
                        <div class="kx-toggle">
                            <input type="checkbox" id="toggle_login_success" name="login_success_email_enabled" value="1"
                                <?php echo e($settings->login_success_email_enabled ? 'checked' : ''); ?>>
                            <label for="toggle_login_success"></label>
                            <div>
                                <div class="kx-toggle-text">Login Success Alert</div>
                                <div style="font-size:.75rem;color:var(--kx-muted);">Notify user on every successful login</div>
                            </div>
                        </div>
                        <div class="kx-toggle">
                            <input type="checkbox" id="toggle_login_failed" name="login_failed_email_enabled" value="1"
                                <?php echo e($settings->login_failed_email_enabled ? 'checked' : ''); ?>>
                            <label for="toggle_login_failed"></label>
                            <div>
                                <div class="kx-toggle-text">Failed Login Alert</div>
                                <div style="font-size:.75rem;color:var(--kx-muted);">Notify user on failed login attempts</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn-kx w-100">
                            <i class="bi bi-floppy-fill me-1"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>

            
            <div class="kx-card">
                <div class="kx-card-title"><i class="bi bi-send-fill"></i>Send Test Email</div>
                <form method="POST" action="<?php echo e(route('admin.email-settings.test')); ?>">
                    <?php echo csrf_field(); ?>
                    <label class="kx-label">Recipient Email</label>
                    <div class="d-flex gap-2">
                        <input type="email" name="test_email" class="kx-input" placeholder="test@example.com" required>
                        <button type="submit" class="btn-kx-outline" style="white-space:nowrap;">
                            <i class="bi bi-send me-1"></i>Send Test
                        </button>
                    </div>
                    <div style="font-size:.75rem;color:var(--kx-muted);margin-top:.4rem;">
                        Sends a welcome email using the current SMTP config above.
                    </div>
                </form>
            </div>

        </div>

        
        <div class="col-lg-7">

            
            <div class="kx-stats">
                <div class="kx-stat">
                    <div class="s-val"><?php echo e($totalAttempts); ?></div>
                    <div class="s-lbl">Total Attempts</div>
                </div>
                <div class="kx-stat">
                    <div class="s-val"><?php echo e($totalSuccess); ?></div>
                    <div class="s-lbl">Successful</div>
                </div>
                <div class="kx-stat">
                    <div class="s-val danger"><?php echo e($totalFailed); ?></div>
                    <div class="s-lbl">Failed</div>
                </div>
            </div>

            <div class="kx-card">
                <div class="kx-card-title"><i class="bi bi-shield-lock-fill"></i>Login Activity
                    <span style="margin-left:auto;font-size:.72rem;font-weight:400;color:var(--kx-muted);">
                        <?php echo e($logs->total()); ?> records
                    </span>
                </div>

                
                <form method="GET" action="<?php echo e(route('admin.email-settings.index')); ?>" class="filter-bar mb-3">
                    <input type="text" name="search" class="kx-input" placeholder="Search email..." value="<?php echo e(request('search')); ?>">
                    <select name="status" class="kx-select" style="width:130px;flex:none;">
                        <option value="">All</option>
                        <option value="success" <?php echo e(request('status')==='success'?'selected':''); ?>>Success</option>
                        <option value="failed"  <?php echo e(request('status')==='failed'?'selected':''); ?>>Failed</option>
                    </select>
                    <button type="submit" class="btn-kx" style="padding:.6rem 1rem;font-size:.82rem;">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if(request('search') || request('status')): ?>
                    <a href="<?php echo e(route('admin.email-settings.index')); ?>" class="btn-kx-outline" style="padding:.6rem 1rem;font-size:.82rem;text-decoration:none;">
                        <i class="bi bi-x"></i>
                    </a>
                    <?php endif; ?>
                </form>

                <div class="table-responsive">
                    <table class="kx-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>IP Address</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600;color:var(--kx-text);"><?php echo e($log->email); ?></div>
                                    <?php if($log->user): ?>
                                    <div style="font-size:.72rem;color:var(--kx-muted);"><?php echo e($log->user->name); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-family:monospace;color:var(--kx-muted);">
                                    <?php echo e($log->ip_address ?? '—'); ?>

                                </td>
                                <td class="text-center">
                                    <span class="status-pill <?php echo e($log->status); ?>">
                                        <?php if($log->status === 'success'): ?>
                                            <i class="bi bi-check-circle-fill"></i>Success
                                        <?php else: ?>
                                            <i class="bi bi-x-circle-fill"></i>Failed
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="text-end" style="color:var(--kx-muted);white-space:nowrap;font-size:.75rem;">
                                    <?php echo e($log->created_at->format('d M Y')); ?><br>
                                    <span style="font-size:.7rem;"><?php echo e($log->created_at->format('g:i A')); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center" style="color:var(--kx-muted);padding:2rem;">
                                    No login logs yet.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($logs->hasPages()): ?>
                <div class="d-flex justify-content-center mt-3">
                    <?php echo e($logs->links()); ?>

                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const presets = {
    cpanel: {
        mailer: 'smtp',
        host: 'mail.' + window.location.hostname.replace(/^(localhost|127\.0\.0\.1)$/, 'yourdomain.com'),
        port: 465,
        encryption: 'ssl',
        username: '',
        note: '&#127968; cPanel SMTP: Replace the host with your actual domain (e.g. mail.yourdomain.com). Use your full cPanel email address as the username.'
    },
    gmail: {
        mailer: 'smtp',
        host: 'smtp.gmail.com',
        port: 587,
        encryption: 'tls',
        username: '',
        note: '&#9993; Gmail SMTP: Use your Gmail address as username and an App Password (not your regular password). Enable 2FA first at myaccount.google.com/security.'
    },
    mailpit: {
        mailer: 'smtp',
        host: '127.0.0.1',
        port: 1025,
        encryption: '',
        username: '',
        note: '&#128268; Mailpit / MailHog: Local development only — emails are captured and NOT actually sent. Run Mailpit at http://localhost:8025 to view them.'
    },
    mailtrap: {
        mailer: 'smtp',
        host: 'sandbox.smtp.mailtrap.io',
        port: 2525,
        encryption: 'tls',
        username: '',
        note: '&#128270; Mailtrap: Staging/testing inbox. Get your credentials from mailtrap.io dashboard — emails are captured and not delivered to real addresses.'
    }
};

function applyPreset(key) {
    const p = presets[key];
    if (!p) return;

    document.getElementById('field_mailer').value = p.mailer;
    document.getElementById('field_host').value = p.host;
    document.getElementById('field_port').value = p.port;

    const encSelect = document.getElementById('field_encryption');
    for (let i = 0; i < encSelect.options.length; i++) {
        if (encSelect.options[i].value === p.encryption) {
            encSelect.selectedIndex = i;
            break;
        }
    }

    if (p.username !== undefined && p.username !== '') {
        document.getElementById('field_username').value = p.username;
    }

    const noteEl = document.getElementById('preset-note');
    noteEl.innerHTML = p.note;
    noteEl.style.display = 'block';

    // Highlight the selected preset button
    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.style.opacity = '0.6';
    });
    event.currentTarget.style.opacity = '1';
    event.currentTarget.style.boxShadow = '0 0 0 2px rgba(0,204,0,0.3)';
}
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('adminnavlayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\admin\email-settings.blade.php ENDPATH**/ ?>