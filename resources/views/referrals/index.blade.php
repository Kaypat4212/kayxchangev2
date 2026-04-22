@extends('layout')

@push('styles')
<style>
:root{
    --kx-green:#00cc00;
    --kx-dark:#0d1117;
    --kx-card:#161b27;
    --kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);
    --kx-text:#e4e8f0;
    --kx-muted:#7a8599;
}
body{background:var(--kx-dark);color:var(--kx-text);}

/* Hero */
.kx-hero{
    background:linear-gradient(135deg,#0a1628 0%,#0d1f1a 100%);
    border-bottom:1px solid var(--kx-border);
    padding:2rem 1rem 1.5rem;
    text-align:center;
    margin-bottom:2rem;
}
.kx-hero h1{font-size:1.6rem;font-weight:700;color:#fff;margin:0 0 .3rem;}
.kx-hero p{color:var(--kx-muted);font-size:.9rem;margin:0;}
.kx-hero-badge{display:inline-flex;align-items:center;gap:.4rem;background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);border-radius:20px;padding:.3rem .9rem;font-size:.8rem;color:var(--kx-green);margin-bottom:.75rem;}

/* Cards */
.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1.25rem;}
.kx-card-title{font-size:.95rem;font-weight:700;color:var(--kx-text);margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;}
.kx-card-title i{color:var(--kx-green);}

/* Stat tiles */
.kx-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1.5rem;}
@media(max-width:576px){.kx-stats{grid-template-columns:1fr 1fr;}}
.kx-stat{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:1rem;text-align:center;}
.kx-stat .s-val{font-size:1.4rem;font-weight:700;color:var(--kx-green);}
.kx-stat .s-lbl{font-size:.72rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-top:.2rem;}

/* Copy input */
.kx-copy-group{display:flex;gap:0;margin-bottom:1rem;}
.kx-copy-input{background:var(--kx-card2)!important;border:1px solid var(--kx-border)!important;color:var(--kx-text)!important;border-radius:10px 0 0 10px!important;padding:.75rem 1rem!important;font-size:.82rem!important;flex:1;outline:none;}
.kx-copy-btn{background:var(--kx-green);border:none;color:#000;font-weight:700;padding:.75rem 1.1rem;border-radius:0 10px 10px 0;font-size:.82rem;white-space:nowrap;cursor:pointer;transition:background .15s;}
.kx-copy-btn:hover{background:#00e600;}
.kx-copy-btn.copied{background:#0099ff;color:#fff;}
.kx-label{font-size:.75rem;font-weight:600;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;}

/* Share buttons */
.share-row{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1rem;}
.share-btn{display:inline-flex;align-items:center;gap:.4rem;border-radius:8px;padding:.45rem .9rem;font-size:.8rem;font-weight:600;text-decoration:none;border:1px solid var(--kx-border);color:var(--kx-text);background:var(--kx-card2);transition:all .15s;}
.share-btn:hover{border-color:var(--kx-green);color:var(--kx-green);}
.share-btn.wa{border-color:rgba(37,211,102,.3);color:#25d366;}
.share-btn.wa:hover{background:rgba(37,211,102,.1);}
.share-btn.tg{border-color:rgba(0,136,204,.3);color:#0088cc;}
.share-btn.tg:hover{background:rgba(0,136,204,.1);}
.share-btn.tw{border-color:rgba(29,161,242,.3);color:#1da1f2;}
.share-btn.tw:hover{background:rgba(29,161,242,.1);}

/* How it works */
.kx-steps-how{display:flex;gap:1rem;margin-top:.5rem;}
@media(max-width:576px){.kx-steps-how{flex-direction:column;}}
.kx-step-how{flex:1;background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:1rem;text-align:center;}
.kx-step-how .how-num{width:32px;height:32px;border-radius:50%;background:rgba(0,204,0,.15);border:1px solid rgba(0,204,0,.3);color:var(--kx-green);font-weight:700;font-size:.85rem;display:flex;align-items:center;justify-content:center;margin:0 auto .6rem;}
.kx-step-how .how-title{font-size:.82rem;font-weight:600;color:var(--kx-text);margin-bottom:.25rem;}
.kx-step-how .how-desc{font-size:.75rem;color:var(--kx-muted);}

/* Table */
.kx-table{width:100%;border-collapse:collapse;}
.kx-table th{font-size:.72rem;font-weight:600;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;padding:.6rem .75rem;border-bottom:1px solid var(--kx-border);}
.kx-table td{padding:.85rem .75rem;border-bottom:1px solid var(--kx-border);font-size:.875rem;vertical-align:middle;}
.kx-table tr:last-child td{border-bottom:none;}
.kx-table tbody tr:hover td{background:rgba(255,255,255,.025);}
.avatar-pill{width:32px;height:32px;border-radius:50%;background:rgba(0,204,0,.15);border:1px solid rgba(0,204,0,.2);color:var(--kx-green);display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;}
.status-pill{display:inline-flex;align-items:center;gap:.3rem;border-radius:20px;padding:.2rem .65rem;font-size:.72rem;font-weight:600;}
.status-pill.completed{background:rgba(0,204,0,.12);color:var(--kx-green);border:1px solid rgba(0,204,0,.2);}
.status-pill.pending{background:rgba(255,193,7,.1);color:#ffc107;border:1px solid rgba(255,193,7,.2);}
.status-pill.cancelled{background:rgba(255,68,68,.1);color:#ff4444;border:1px solid rgba(255,68,68,.2);}

/* Empty state */
.kx-empty{text-align:center;padding:2.5rem 1rem;}
.kx-empty-icon{width:64px;height:64px;border-radius:50%;background:rgba(0,204,0,.08);border:1px solid rgba(0,204,0,.15);color:var(--kx-green);font-size:1.6rem;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;}
.kx-empty h5{color:var(--kx-text);font-size:.95rem;margin-bottom:.4rem;}
.kx-empty p{color:var(--kx-muted);font-size:.82rem;margin:0;}

/* Pagination override */
.pagination .page-link{background:var(--kx-card2);border-color:var(--kx-border);color:var(--kx-text);}
.pagination .page-link:hover,.pagination .active .page-link{background:var(--kx-green);border-color:var(--kx-green);color:#000;}

/* Toast */
.kx-toast{position:fixed;top:1rem;right:1rem;z-index:9999;min-width:200px;padding:.7rem 1rem;border-radius:10px;font-size:.85rem;font-weight:500;display:none;}
.kx-toast.success{background:#0d2d0d;border:1px solid var(--kx-green);color:var(--kx-green);}
.kx-toast.error{background:#2d0d0d;border:1px solid #ff4444;color:#ff4444;}
</style>
@endpush

@section('content')

<div class="kx-toast" id="kxToast"></div>

{{-- Hero --}}
<div class="kx-hero">
    <div class="kx-hero-badge"><i class="bi bi-gift-fill"></i>Referral Program</div>
    <h1>Invite Friends &amp; Earn</h1>
    <p>Get <strong style="color:var(--kx-green);">&#8358;500</strong> for every friend who signs up and completes their first trade</p>
</div>

<div class="container-fluid px-3 pb-5">
<div class="row justify-content-center">
<div class="col-xl-7 col-lg-8 col-md-10">

    {{-- ── Fraud policy notice ──────────────────────────────────────────────── --}}
    <div style="background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.22);border-radius:14px;padding:1rem 1.2rem;margin-bottom:1.25rem;">
        <div style="display:flex;align-items:flex-start;gap:.75rem;">
            <i class="bi bi-shield-exclamation" style="color:#ef4444;font-size:1.3rem;margin-top:.1rem;flex-shrink:0;"></i>
            <div>
                <div style="font-weight:700;color:#ef4444;font-size:.88rem;margin-bottom:.35rem;">Important — Fair Play Policy</div>
                <ul style="margin:0;padding-left:1.1rem;color:rgba(255,255,255,.65);font-size:.78rem;line-height:1.8;" class="kx-policy-list">
                    <li>Each person may only receive a referral bonus <strong style="color:#fff" class="kx-policy-strong">once</strong>. Duplicate accounts are automatically detected.</li>
                    <li>Referring yourself (creating a second account) will result in <strong style="color:#ef4444">permanent account ban</strong> and forfeiture of all rewards.</li>
                    <li>Rewards are only paid after the referred person deposits ₦10,000 and completes KYC — not on signup.</li>
                    <li>We check device, IP, phone, bank account and timing patterns. Suspicious referrals are held for manual review.</li>
                    <li>If you are an ambassador or influencer, your account will be revoked if fraud is traced to codes you distribute.</li>
                </ul>
                <style>
                    body.light-mode .kx-policy-list { color: #374151 !important; }
                    body.light-mode .kx-policy-strong { color: #111827 !important; }
                </style>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="kx-stats">
        <div class="kx-stat">
            <div class="s-val">{{ $totalCount }}</div>
            <div class="s-lbl">Total Referred</div>
        </div>
        <div class="kx-stat">
            <div class="s-val">{{ $completedCount }}</div>
            <div class="s-lbl">Completed</div>
        </div>
        <div class="kx-stat">
            <div class="s-val">&#8358;{{ number_format($totalRewards, 0) }}</div>
            <div class="s-lbl">Total Earned</div>
        </div>
    </div>

    {{-- Your referral details --}}
    <div class="kx-card">
        <div class="kx-card-title"><i class="bi bi-link-45deg"></i>Your Referral Details</div>

        <div class="mb-3">
            <div class="kx-label">Referral Code</div>
            <div class="kx-copy-group">
                <input type="text" id="codeInput" class="kx-copy-input" readonly
                    value="{{ $user->code ?? $user->referral_code }}">
                <button class="kx-copy-btn" onclick="copyField('codeInput', this)">
                    <i class="bi bi-copy me-1"></i>Copy
                </button>
            </div>
        </div>

        <div class="mb-2">
            <div class="kx-label">Referral Link</div>
            <div class="kx-copy-group">
                <input type="text" id="linkInput" class="kx-copy-input" readonly
                    value="{{ $referral_link ?? $referralLink }}">
                <button class="kx-copy-btn" onclick="copyField('linkInput', this)">
                    <i class="bi bi-copy me-1"></i>Copy
                </button>
            </div>
        </div>

        {{-- Share buttons --}}
        <div class="share-row">
            <a class="share-btn wa" id="shareWa" href="#" target="_blank" rel="noopener">
                <i class="bi bi-whatsapp"></i>WhatsApp
            </a>
            <a class="share-btn tg" id="shareTg" href="#" target="_blank" rel="noopener">
                <i class="bi bi-telegram"></i>Telegram
            </a>
            <a class="share-btn tw" id="shareTw" href="#" target="_blank" rel="noopener">
                <i class="bi bi-twitter-x"></i>X / Twitter
            </a>
        </div>
    </div>

    {{-- How it works --}}
    <div class="kx-card">
        <div class="kx-card-title"><i class="bi bi-info-circle-fill"></i>How It Works</div>
        <div class="kx-steps-how">
            <div class="kx-step-how">
                <div class="how-num">1</div>
                <div class="how-title">Share Your Link</div>
                <div class="how-desc">Send your unique referral link or code to a friend</div>
            </div>
            <div class="kx-step-how">
                <div class="how-num">2</div>
                <div class="how-title">Friend Signs Up</div>
                <div class="how-desc">They register on KayXchange using your link</div>
            </div>
            <div class="kx-step-how">
                <div class="how-num">3</div>
                <div class="how-title">Earn &#8358;500</div>
                <div class="how-desc">Reward is credited after their first completed trade</div>
            </div>
        </div>
    </div>

    {{-- Referral list --}}
    <div class="kx-card">
        <div class="kx-card-title"><i class="bi bi-people-fill"></i>Your Referrals
            <span style="margin-left:auto;font-size:.72rem;font-weight:400;color:var(--kx-muted);">
                {{ $totalCount }} total &bull; {{ $pendingCount }} pending
            </span>
        </div>

        @if($referrals->isNotEmpty())
        <div class="table-responsive">
            <table class="kx-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th class="text-center">Reward</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($referrals as $referral)
                    @php
                        $email = $referral->referred?->email ?? 'Unknown';
                        $initials = strtoupper(substr($email, 0, 2));
                        $status = strtolower($referral->status ?? 'pending');
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-pill">{{ $initials }}</div>
                                <div>
                                    <div style="font-size:.82rem;font-weight:600;color:var(--kx-text);">
                                        {{ $referral->nickname ?? $email }}
                                    </div>
                                    @if($referral->nickname)
                                    <div style="font-size:.7rem;color:var(--kx-muted);">{{ $email }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center" style="color:var(--kx-green);font-weight:600;">
                            &#8358;{{ number_format($referral->reward_amount ?? 0, 0) }}
                        </td>
                        <td class="text-center">
                            <span class="status-pill {{ $status }}">
                                @if($status === 'completed')
                                    <i class="bi bi-check-circle-fill"></i>
                                @elseif($status === 'pending')
                                    <i class="bi bi-clock-fill"></i>
                                @else
                                    <i class="bi bi-x-circle-fill"></i>
                                @endif
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        <td class="text-end" style="color:var(--kx-muted);font-size:.8rem;white-space:nowrap;">
                            {{ $referral->created_at->format('d M Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($referrals->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $referrals->links() }}
        </div>
        @endif
        @else
        <div class="kx-empty">
            <div class="kx-empty-icon"><i class="bi bi-people"></i></div>
            <h5>No referrals yet</h5>
            <p>Share your referral link above and start earning &#8358;500 per sign-up!</p>
        </div>
        @endif
    </div>

</div>
</div>
</div>

<script>
(function(){
    function showToast(msg, type) {
        var t = document.getElementById('kxToast');
        t.textContent = msg; t.className = 'kx-toast ' + type;
        t.style.display = 'block';
        setTimeout(function(){ t.style.display = 'none'; }, 2800);
    }

    window.copyField = function(id, btn) {
        var val = document.getElementById(id).value;
        navigator.clipboard.writeText(val).then(function(){
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copied!';
            btn.classList.add('copied');
            showToast('Copied to clipboard!', 'success');
            setTimeout(function(){ btn.innerHTML = orig; btn.classList.remove('copied'); }, 2000);
        }).catch(function(){
            showToast('Copy failed — please copy manually.', 'error');
        });
    };

    // Build share URLs
    var link = document.getElementById('linkInput').value;
    var msg  = encodeURIComponent('Join KayXchange \u2014 the best crypto exchange platform! Sign up with my link: ' + link);
    document.getElementById('shareWa').href = 'https://wa.me/?text=' + msg;
    document.getElementById('shareTg').href = 'https://t.me/share/url?url=' + encodeURIComponent(link) + '&text=' + encodeURIComponent('Join KayXchange using my referral link!');
    document.getElementById('shareTw').href = 'https://twitter.com/intent/tweet?text=' + msg;

    @if(session('success'))
        showToast(@json(session('success')), 'success');
    @elseif(session('error'))
        showToast(@json(session('error')), 'error');
    @endif
})();
</script>

@endsection
