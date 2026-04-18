
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
            <h4><i class="bi bi-patch-check-fill me-2" style="color:var(--kx-blue)"></i>KYC Verification</h4>
            <small>Review and approve identity documents</small>
        </div>
    </div>

    <?php if(session('success')): ?><div class="kx-alert-success"><i class="bi bi-check-circle-fill"></i><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if(session('error')): ?><div class="kx-alert-error"><i class="bi bi-exclamation-circle-fill"></i><?php echo e(session('error')); ?></div><?php endif; ?>

    <?php
        $pendingKyc  = $kycRecords->where('status','pending')->count();
        $approvedKyc = $kycRecords->where('status','approved')->count();
        $rejectedKyc = $kycRecords->where('status','rejected')->count();
    ?>
    <div class="kx-stat-row">
        <div class="kx-stat"><div class="kx-stat-icon icon-yellow"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="kx-stat-label">Pending</div><div class="kx-stat-value"><?php echo e($pendingKyc); ?></div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-green"><i class="bi bi-patch-check-fill"></i></div>
            <div><div class="kx-stat-label">Approved</div><div class="kx-stat-value"><?php echo e($approvedKyc); ?></div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-red"><i class="bi bi-x-circle-fill"></i></div>
            <div><div class="kx-stat-label">Rejected</div><div class="kx-stat-value"><?php echo e($rejectedKyc); ?></div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-blue"><i class="bi bi-files"></i></div>
            <div><div class="kx-stat-label">Total</div><div class="kx-stat-value"><?php echo e($kycRecords->total()); ?></div></div></div>
    </div>

    <div class="kx-panel">
        <div class="kx-panel-header"><span class="kx-panel-title"><i class="bi bi-person-badge me-2"></i>KYC Submissions</span></div>
        <div class="kx-table-wrap">
            <table class="kx-table">
                <thead><tr>
                    <th>#</th><th>User</th><th>ID Document</th><th>Selfie</th>
                    <th>Status</th><th>Submitted</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $kycRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kyc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><span style="color:var(--kx-muted)">#<?php echo e($kyc->id); ?></span></td>
                    <td>
                        <span style="font-weight:600"><?php echo e($kyc->user->name ?? 'N/A'); ?></span><br>
                        <span style="font-size:.72rem;color:var(--kx-muted)"><?php echo e($kyc->user->email ?? ''); ?></span>
                    </td>
                    <td>
                        <?php if($kyc->id_document_path): ?>
                            <?php
                                $idDocPath = ltrim((string) $kyc->id_document_path, '/');
                                $idDocUrl = preg_match('/^https?:\/\//i', $idDocPath)
                                    ? $idDocPath
                                    : asset('storage/' . preg_replace('/^storage\//', '', $idDocPath));
                            ?>
                            <a href="<?php echo e($idDocUrl); ?>" target="_blank" class="btn-kx-edit" style="font-size:.72rem;padding:.25rem .55rem">
                                <i class="bi bi-file-earmark-person"></i> View ID
                            </a>
                        <?php else: ?> <span style="color:var(--kx-muted);font-size:.75rem">None</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($kyc->selfie_path): ?>
                            <?php
                                $selfiePath = ltrim((string) $kyc->selfie_path, '/');
                                $selfieUrl = preg_match('/^https?:\/\//i', $selfiePath)
                                    ? $selfiePath
                                    : asset('storage/' . preg_replace('/^storage\//', '', $selfiePath));
                            ?>
                            <a href="<?php echo e($selfieUrl); ?>" target="_blank" class="btn-kx-edit" style="font-size:.72rem;padding:.25rem .55rem">
                                <i class="bi bi-person-badge"></i> View
                            </a>
                        <?php else: ?> <span style="color:var(--kx-muted);font-size:.75rem">None</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($kyc->status === 'approved'): ?>
                            <span class="kx-badge kx-badge-green"><i class="bi bi-check me-1"></i>Approved</span>
                        <?php elseif($kyc->status === 'rejected'): ?>
                            <span class="kx-badge kx-badge-red"><i class="bi bi-x me-1"></i>Rejected</span>
                        <?php else: ?>
                            <span class="kx-badge kx-badge-yellow"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.75rem;color:var(--kx-muted)"><?php echo e($kyc->created_at ? $kyc->created_at->format('d M Y') : '—'); ?></td>
                    <td>
                        <?php if($kyc->status === 'pending'): ?>
                        <div class="d-flex gap-1 flex-wrap">
                            <form action="<?php echo e(route('kyc.verify', $kyc->id)); ?>" method="POST" style="display:inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn-kx-approve" onclick="return confirm('Approve KYC?')"><i class="bi bi-check-lg me-1"></i>Approve</button>
                            </form>
                            <form action="<?php echo e(route('kyc.verify', $kyc->id)); ?>" method="POST" style="display:inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn-kx-danger" onclick="return confirm('Reject KYC?')"><i class="bi bi-x-lg me-1"></i>Reject</button>
                            </form>
                            <button type="button" class="btn-kx-icon" title="AI Compliance Check"
                                onclick="aiKycAnalyze(<?php echo e($kyc->id); ?>, '<?php echo e(addslashes($kyc->user->name ?? 'User')); ?>')"
                                style="color:#a855f7;border-color:rgba(168,85,247,.35);">
                                <i class="bi bi-robot"></i>
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="d-flex gap-1">
                            <span style="font-size:.72rem;color:var(--kx-muted)">Reviewed</span>
                            <button type="button" class="btn-kx-icon" title="AI Compliance Check"
                                onclick="aiKycAnalyze(<?php echo e($kyc->id); ?>, '<?php echo e(addslashes($kyc->user->name ?? 'User')); ?>')"
                                style="color:#a855f7;border-color:rgba(168,85,247,.35);">
                                <i class="bi bi-robot"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--kx-muted);padding:2.5rem">No KYC records found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($kycRecords->hasPages()): ?>
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--kx-border)">
            <?php echo e($kycRecords->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>


<div class="modal fade" id="aiKycModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="bi bi-robot me-2" style="color:#a855f7"></i>AI Compliance Assessment — <span id="ai-kyc-name"></span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="ai-kyc-loading" style="text-align:center;padding:1.5rem;display:none;">
                    <div class="spinner-border spinner-border-sm" style="color:#a855f7"></div>
                    <p style="font-size:.8rem;color:var(--kx-muted);margin-top:.75rem">Analysing KYC data…</p>
                </div>
                <div id="ai-kyc-content" style="font-size:.84rem;color:var(--kx-text);line-height:1.7;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-kx-outline" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
async function aiKycAnalyze(kycId, userName){
    document.getElementById('ai-kyc-name').textContent = userName;
    document.getElementById('ai-kyc-content').innerHTML = '';
    document.getElementById('ai-kyc-loading').style.display = 'block';
    new bootstrap.Modal(document.getElementById('aiKycModal')).show();
    try {
        const res = await fetch('<?php echo e(route("ai.kyc-analyze")); ?>', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'},
            body: JSON.stringify({kyc_id: kycId})
        });
        const data = await res.json();
        document.getElementById('ai-kyc-loading').style.display = 'none';
        document.getElementById('ai-kyc-content').innerHTML = data.analysis ||
            '<span style="color:var(--kx-red)">'+(data.error||'Error')+'</span>';
    } catch(e) {
        document.getElementById('ai-kyc-loading').style.display = 'none';
        document.getElementById('ai-kyc-content').innerHTML = '<span style="color:var(--kx-red)">Request failed: '+e.message+'</span>';
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminnavlayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\admin\kyc.blade.php ENDPATH**/ ?>