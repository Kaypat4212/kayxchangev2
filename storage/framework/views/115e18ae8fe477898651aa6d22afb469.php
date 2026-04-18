
<?php $__env->startSection('content'); ?>
<style>
:root{--kx-green:#00cc00;--kx-gdim:rgba(0,204,0,.12);--kx-glow:rgba(0,204,0,.22);
--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,.07);
--kx-text:#e4e8f0;--kx-muted:#7a8599;--kx-red:#ef4444;--kx-yellow:#f59e0b;
--kx-blue:#38bdf8;--kx-purple:#a855f7;}
body{background:var(--kx-dark)!important;color:var(--kx-text)!important;font-family:'Poppins',sans-serif;}
.kx-page-header{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:12px;
padding:1rem 1.4rem;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;}
.kx-page-header h4{margin:0;font-size:1rem;font-weight:700;color:#fff;}
.kx-page-header small{font-size:.75rem;color:var(--kx-muted);}
.kx-panel{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:12px;margin-bottom:1.25rem;overflow:hidden;}
.kx-panel-header{padding:.875rem 1.25rem;border-bottom:1px solid var(--kx-border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;background:var(--kx-card2);}
.kx-panel-title{font-size:.9rem;font-weight:600;color:#fff;margin:0;}
.kx-table{width:100%;border-collapse:collapse;}
.kx-table thead th{background:var(--kx-card2);color:var(--kx-muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;
padding:.7rem 1rem;border-bottom:1px solid var(--kx-border);white-space:nowrap;font-weight:600;}
.kx-table tbody tr{border-bottom:1px solid var(--kx-border);transition:background .15s;}
.kx-table tbody tr:hover{background:rgba(255,255,255,.02);}
.kx-table tbody tr:last-child{border-bottom:none;}
.kx-table td{padding:.75rem 1rem;font-size:.83rem;color:var(--kx-text);vertical-align:middle;}
.kx-table-wrap{overflow-x:auto;}
.kx-badge{display:inline-flex;align-items:center;padding:.2rem .6rem;border-radius:20px;font-size:.7rem;font-weight:600;}
.kx-badge-green{background:rgba(0,204,0,.12);color:var(--kx-green);}
.kx-badge-red{background:rgba(239,68,68,.12);color:var(--kx-red);}
.kx-badge-yellow{background:rgba(245,158,11,.12);color:var(--kx-yellow);}
.kx-badge-blue{background:rgba(56,189,248,.12);color:var(--kx-blue);}
.kx-badge-purple{background:rgba(168,85,247,.12);color:var(--kx-purple);}
.kx-badge-gray{background:rgba(255,255,255,.06);color:var(--kx-muted);}
.btn-kx-green{background:var(--kx-green);color:#000;border:none;border-radius:8px;font-weight:600;font-size:.8rem;padding:.45rem 1rem;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;}
.btn-kx-green:hover{background:#00e600;color:#000;}
.btn-kx-outline{background:transparent;color:var(--kx-text);border:1px solid var(--kx-border);font-size:.8rem;padding:.45rem 1rem;border-radius:8px;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;cursor:pointer;}
.btn-kx-outline:hover{background:var(--kx-card2);color:#fff;border-color:rgba(255,255,255,.2);}
.btn-kx-danger{background:transparent;color:var(--kx-red);border:1px solid rgba(239,68,68,.3);font-size:.75rem;padding:.3rem .7rem;border-radius:7px;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;text-decoration:none;}
.btn-kx-danger:hover{background:rgba(239,68,68,.1);color:var(--kx-red);}
.btn-kx-edit{background:transparent;color:var(--kx-blue);border:1px solid rgba(56,189,248,.3);font-size:.75rem;padding:.3rem .7rem;border-radius:7px;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;text-decoration:none;}
.btn-kx-edit:hover{background:rgba(56,189,248,.1);color:var(--kx-blue);}
.btn-kx-approve{background:transparent;color:var(--kx-green);border:1px solid rgba(0,204,0,.3);font-size:.75rem;padding:.3rem .7rem;border-radius:7px;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;}
.btn-kx-approve:hover{background:var(--kx-gdim);color:var(--kx-green);}
.kx-input{background:var(--kx-card2)!important;border:1px solid var(--kx-border)!important;color:var(--kx-text)!important;border-radius:8px!important;padding:.5rem .85rem!important;font-size:.83rem!important;}
.kx-input:focus{border-color:rgba(0,204,0,.4)!important;box-shadow:0 0 0 2px rgba(0,204,0,.1)!important;color:#fff!important;outline:none!important;}
.kx-input::placeholder{color:var(--kx-muted)!important;}
select.kx-input option{background:var(--kx-card2);color:var(--kx-text);}
.kx-label{font-size:.75rem;color:var(--kx-muted);display:block;margin-bottom:.35rem;text-transform:uppercase;letter-spacing:.04em;}
.kx-alert-success{background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);color:var(--kx-green);border-radius:8px;padding:.75rem 1rem;font-size:.84rem;display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;}
.kx-alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:var(--kx-red);border-radius:8px;padding:.75rem 1rem;font-size:.84rem;display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;}
.modal-content{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;color:var(--kx-text);}
.modal-header{border-bottom:1px solid var(--kx-border);}
.modal-footer{border-top:1px solid var(--kx-border);}
.kx-stat-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.875rem;margin-bottom:1.25rem;}
.kx-stat{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:12px;padding:1rem 1.25rem;display:flex;align-items:center;gap:1rem;}
.kx-stat-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.icon-green{background:var(--kx-gdim);color:var(--kx-green);}
.icon-yellow{background:rgba(245,158,11,.15);color:var(--kx-yellow);}
.icon-blue{background:rgba(56,189,248,.12);color:var(--kx-blue);}
.icon-red{background:rgba(239,68,68,.12);color:var(--kx-red);}
.icon-purple{background:rgba(168,85,247,.12);color:var(--kx-purple);}
.kx-stat-label{font-size:.7rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;}
.kx-stat-value{font-size:1.4rem;font-weight:700;color:#fff;line-height:1.1;}
.kx-search{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;display:flex;align-items:center;padding:.45rem .75rem;gap:.5rem;}
.kx-search-input{background:transparent;border:none;outline:none;color:var(--kx-text);font-size:.83rem;flex:1;}
.kx-search-input::placeholder{color:var(--kx-muted);}
</style>
<div class="container-fluid py-3 px-3 px-md-4">
    <div class="kx-page-header">
        <div>
            <h4><i class="bi bi-arrow-up-circle-fill me-2" style="color:var(--kx-yellow)"></i>Withdrawal Requests</h4>
            <small>Review and process pending withdrawal requests</small>
        </div>
        <div class="kx-search"><i class="bi bi-search" style="color:var(--kx-muted)"></i>
            <input class="kx-search-input" id="wSearch" placeholder="Search…">
        </div>
    </div>

    <?php if(session('success')): ?><div class="kx-alert-success"><i class="bi bi-check-circle-fill"></i><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if(session('error')): ?><div class="kx-alert-error"><i class="bi bi-exclamation-circle-fill"></i><?php echo e(session('error')); ?></div><?php endif; ?>

    <?php
        $pending   = collect($withdrawals)->where('status','pending')->count();
        $approved  = collect($withdrawals)->where('status','approved')->count();
        $cancelled = collect($withdrawals)->where('status','cancelled')->count();
        $total     = collect($withdrawals)->sum('amount');
    ?>
    <div class="kx-stat-row">
        <div class="kx-stat"><div class="kx-stat-icon icon-yellow"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="kx-stat-label">Pending</div><div class="kx-stat-value"><?php echo e($pending); ?></div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-green"><i class="bi bi-check-circle-fill"></i></div>
            <div><div class="kx-stat-label">Approved</div><div class="kx-stat-value"><?php echo e($approved); ?></div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-red"><i class="bi bi-x-circle-fill"></i></div>
            <div><div class="kx-stat-label">Cancelled</div><div class="kx-stat-value"><?php echo e($cancelled); ?></div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-blue"><i class="bi bi-cash-stack"></i></div>
            <div><div class="kx-stat-label">Total Volume</div><div class="kx-stat-value" style="font-size:1rem">₦<?php echo e(number_format($total,0)); ?></div></div></div>
    </div>

    <div class="kx-panel">
        <div class="kx-panel-header">
            <span class="kx-panel-title"><i class="bi bi-list-ul me-2"></i>All Withdrawals</span>
        </div>
        <div class="kx-table-wrap">
            <table class="kx-table" id="wTable">
                <thead><tr>
                    <th>#ID</th><th>User</th><th>Amount</th><th>Bank</th><th>Account</th><th>Status</th><th>Date</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $bank = is_string($w->bank_account) ? json_decode($w->bank_account, true) : (array)$w->bank_account; ?>
                <tr>
                    <td><span style="color:var(--kx-muted)">#<?php echo e($w->id); ?></span></td>
                    <td><span style="font-weight:600"><?php echo e($w->user->name ?? 'N/A'); ?></span><br>
                        <span style="font-size:.72rem;color:var(--kx-muted)"><?php echo e($w->user->email ?? ''); ?></span></td>
                    <td><span style="font-weight:700;color:var(--kx-yellow)">₦<?php echo e(number_format($w->amount, 2)); ?></span></td>
                    <td style="font-size:.78rem"><?php echo e($bank['bank_name'] ?? '—'); ?></td>
                    <td style="font-size:.78rem;font-family:monospace"><?php echo e($bank['account_number'] ?? '—'); ?><br>
                        <span style="color:var(--kx-muted)"><?php echo e($bank['account_name'] ?? ''); ?></span></td>
                    <td>
                        <?php if($w->status === 'pending'): ?>
                            <span class="kx-badge kx-badge-yellow"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                        <?php elseif($w->status === 'approved'): ?>
                            <span class="kx-badge kx-badge-green"><i class="bi bi-check me-1"></i>Approved</span>
                        <?php elseif($w->status === 'cancelled'): ?>
                            <span class="kx-badge kx-badge-red"><i class="bi bi-x me-1"></i>Cancelled</span>
                        <?php else: ?>
                            <span class="kx-badge kx-badge-gray"><?php echo e($w->status); ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.75rem;color:var(--kx-muted)"><?php echo e($w->created_at ? $w->created_at->format('d M Y') : '—'); ?></td>
                    <td>
                        <?php if($w->status === 'pending'): ?>
                        <div class="d-flex gap-1">
                            <form id="wd-approve-<?php echo e($w->id); ?>" action="<?php echo e(route('withdrawals.updateStatus', $w->id)); ?>" method="POST" style="display:inline">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <input type="hidden" name="status" value="approved">
                                <button type="button" class="btn-kx-approve"
                                    onclick="openWdConfirm('approve',<?php echo e($w->id); ?>,'<?php echo e(addslashes($w->user->name ?? 'N/A')); ?>','<?php echo e(number_format($w->amount, 2)); ?>','<?php echo e(addslashes($bank['bank_name'] ?? '—')); ?>','<?php echo e(addslashes($bank['account_number'] ?? '—')); ?>','<?php echo e(addslashes($bank['account_name'] ?? '—')); ?>')"
                                    title="Approve Withdrawal">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                            </form>
                            <form id="wd-cancel-<?php echo e($w->id); ?>" action="<?php echo e(route('withdrawals.updateStatus', $w->id)); ?>" method="POST" style="display:inline">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <input type="hidden" name="status" value="cancelled">
                                <button type="button" class="btn-kx-danger"
                                    onclick="openWdConfirm('cancel',<?php echo e($w->id); ?>,'<?php echo e(addslashes($w->user->name ?? 'N/A')); ?>','<?php echo e(number_format($w->amount, 2)); ?>','<?php echo e(addslashes($bank['bank_name'] ?? '—')); ?>','<?php echo e(addslashes($bank['account_number'] ?? '—')); ?>','<?php echo e(addslashes($bank['account_name'] ?? '—')); ?>')"
                                    title="Cancel Withdrawal">
                                    <i class="bi bi-x-lg"></i> Cancel
                                </button>
                            </form>
                        </div>
                        <?php else: ?>
                        <span style="font-size:.72rem;color:var(--kx-muted)">Processed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" style="text-align:center;color:var(--kx-muted);padding:2.5rem">No withdrawals found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="wdConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="wdm-title" style="font-size:.95rem;font-weight:700;color:#fff"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <div style="background:var(--kx-card2);border:1px solid rgba(245,158,11,.3);border-radius:12px;padding:1rem 1.25rem;text-align:center;margin-bottom:1rem">
                    <div style="font-size:.72rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Amount to Process</div>
                    <div id="wdm-amount" style="font-size:1.6rem;font-weight:800;color:var(--kx-yellow)">&#8212;</div>
                </div>
                
                <div style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:10px;padding:.875rem 1rem">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">User</div><div style="font-weight:700;color:#fff" id="wdm-user"></div></div>
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">Bank</div><div style="font-weight:600;color:#fff" id="wdm-bank"></div></div>
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">Account No.</div><div style="font-weight:700;color:var(--kx-blue);font-family:monospace" id="wdm-accnum"></div></div>
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">Account Name</div><div style="font-weight:600;color:#fff" id="wdm-accname"></div></div>
                    </div>
                </div>
                <div id="wdm-action-note" style="margin-top:.85rem;font-size:.82rem;font-weight:600;padding:.6rem .875rem;border-radius:8px"></div>
            </div>
            <div class="modal-footer" style="justify-content:flex-end;gap:.5rem">
                <button type="button" class="btn-kx-outline" data-bs-dismiss="modal">Go Back</button>
                <button type="button" id="wdm-confirm-btn" class="btn-kx-green">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
let _wdPendingFormId = null;

function openWdConfirm(action, id, user, amount, bank, accNum, accName) {
    const isApprove = action === 'approve';
    _wdPendingFormId = (isApprove ? 'wd-approve-' : 'wd-cancel-') + id;
    document.getElementById('wdm-title').innerHTML =
        isApprove
        ? '<i class="bi bi-check-circle me-2" style="color:#00cc00"></i>Approve Withdrawal'
        : '<i class="bi bi-x-circle me-2" style="color:#ef4444"></i>Cancel Withdrawal';
    document.getElementById('wdm-amount').textContent  = '\u20a6' + amount;
    document.getElementById('wdm-user').textContent    = user;
    document.getElementById('wdm-bank').textContent    = bank;
    document.getElementById('wdm-accnum').textContent  = accNum;
    document.getElementById('wdm-accname').textContent = accName;
    const note = document.getElementById('wdm-action-note');
    if(isApprove) {
        note.style.background = 'rgba(0,204,0,.08)';
        note.style.border = '1px solid rgba(0,204,0,.25)';
        note.style.color = '#00cc00';
        note.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Confirm you have transferred <strong>\u20a6'+amount+'</strong> to the account above.';
    } else {
        note.style.background = 'rgba(239,68,68,.08)';
        note.style.border = '1px solid rgba(239,68,68,.25)';
        note.style.color = '#ef4444';
        note.innerHTML = '<i class="bi bi-x-circle me-1"></i>This withdrawal will be marked as cancelled.';
    }
    const btn = document.getElementById('wdm-confirm-btn');
    btn.style.background = isApprove ? '#00cc00' : '#ef4444';
    btn.style.color = isApprove ? '#000' : '#fff';
    btn.textContent = isApprove ? '\u2713 Approve & Process' : 'Cancel Withdrawal';
    new bootstrap.Modal(document.getElementById('wdConfirmModal')).show();
}

document.getElementById('wdm-confirm-btn').addEventListener('click', function() {
    if(_wdPendingFormId) {
        bootstrap.Modal.getInstance(document.getElementById('wdConfirmModal'))?.hide();
        setTimeout(() => document.getElementById(_wdPendingFormId)?.submit(), 180);
    }
});

document.getElementById('wSearch').addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('#wTable tbody tr').forEach(r => { r.style.display = r.textContent.toLowerCase().includes(q)?'':'none'; });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminnavlayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\admin\withdrawals.blade.php ENDPATH**/ ?>