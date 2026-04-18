
<?php
    $balance     = auth()->user()?->balance ?? 0;
    $selectedCoin = session('sell.coin', '');
    $inputType   = session('sell.input_type', 'usd');
    $amount      = $inputType === 'usd' ? session('sell.usd_amount', '') : session('sell.naira_amount', '');
?>

<?php $__env->startPush('styles'); ?>
<style>
:root {
    --kx-green:  #00cc00;
    --kx-dark:   #0d1117;
    --kx-card:   #161b27;
    --kx-card2:  #1e2535;
    --kx-border: rgba(255,255,255,0.07);
    --kx-text:   #e4e8f0;
    --kx-muted:  #7a8599;
}

/* ── Page hero ── */
.sell-hero {
    background: linear-gradient(135deg, rgba(0,204,0,0.08) 0%, rgba(0,80,0,0.04) 100%);
    border: 1px solid rgba(0,204,0,0.14);
    border-radius: 20px;
    padding: 2rem 1.75rem;
    text-align: center;
    margin-bottom: 1.75rem;
    position: relative; overflow: hidden;
}
.sell-hero::before {
    content:''; position:absolute; top:-50px; left:-50px;
    width:180px; height:180px;
    background:radial-gradient(circle,rgba(0,204,0,0.07) 0%,transparent 70%);
    pointer-events:none;
}
.sell-hero-icon {
    width:64px; height:64px; border-radius:50%;
    background:linear-gradient(135deg,#00cc00,#007a0c);
    display:flex; align-items:center; justify-content:center;
    margin:0 auto 1rem; font-size:1.75rem; color:#fff;
    box-shadow:0 6px 28px rgba(0,204,0,0.28);
}
.sell-hero h1 { font-size:1.6rem; font-weight:700; color:#fff; margin-bottom:.4rem; }
.sell-hero p  { color:var(--kx-muted); font-size:.9rem; margin:0; }

/* ── Steps bar ── */
.kx-steps { display:flex; margin-bottom:1.75rem; }
.kx-step  { flex:1; display:flex; flex-direction:column; align-items:center; position:relative; padding:0 .5rem; }
.kx-step:not(:last-child)::after {
    content:''; position:absolute; top:19px;
    left:calc(50% + 22px); right:calc(-50% + 22px);
    height:2px; background:var(--kx-border); z-index:0;
}
.kx-step.active:not(:last-child)::after { background:rgba(0,204,0,0.3); }
.kx-step-num {
    width:38px; height:38px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:.82rem; font-weight:700;
    background:var(--kx-card2); border:2px solid var(--kx-border);
    color:var(--kx-muted); position:relative; z-index:1; transition:all .3s;
}
.kx-step.active .kx-step-num {
    background:linear-gradient(135deg,#00cc00,#007a0c);
    border-color:#00cc00; color:#fff;
    box-shadow:0 3px 14px rgba(0,204,0,0.35);
}
.kx-step-lbl { font-size:.68rem; color:var(--kx-muted); margin-top:.45rem; text-align:center; }
.kx-step.active .kx-step-lbl { color:var(--kx-green); font-weight:600; }

/* ── Cards ── */
.kx-card {
    background:var(--kx-card); border:1px solid var(--kx-border);
    border-radius:16px; overflow:hidden; margin-bottom:1.25rem;
}
.kx-card-hd {
    padding:1.1rem 1.4rem; border-bottom:1px solid var(--kx-border);
    display:flex; align-items:center; gap:.7rem;
}
.kx-card-hd .hico {
    width:36px; height:36px; border-radius:9px;
    background:rgba(0,204,0,0.1);
    display:flex; align-items:center; justify-content:center;
    color:var(--kx-green); font-size:1rem; flex-shrink:0;
}
.kx-card-hd h5 { font-size:.95rem; font-weight:600; color:#fff; margin:0; }
.kx-card-hd p  { font-size:.76rem; color:var(--kx-muted); margin:0; }
.kx-card-bd { padding:1.4rem; }

/* ── Balance chip ── */
.kx-balance-chip {
    display:inline-flex; align-items:center; gap:.5rem;
    background:rgba(0,204,0,0.08); border:1px solid rgba(0,204,0,0.18);
    border-radius:30px; padding:.4rem 1rem;
    font-size:.82rem; font-weight:600; color:#00cc00;
}

/* ── Coin selector ── */
.kx-coin-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.65rem; }
.kx-coin-btn {
    background:var(--kx-card2); border:2px solid var(--kx-border);
    border-radius:12px; padding:.75rem .5rem;
    text-align:center; cursor:pointer;
    transition:all .2s ease; user-select:none;
}
.kx-coin-btn:hover { border-color:rgba(0,204,0,0.35); background:rgba(0,204,0,0.05); }
.kx-coin-btn.selected {
    border-color:#00cc00; background:rgba(0,204,0,0.08);
    box-shadow:0 0 0 3px rgba(0,204,0,0.12);
}
.kx-coin-btn .coin-icon {
    width:36px; height:36px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    margin:0 auto .45rem; font-size:1.1rem; font-weight:700;
}
.coin-icon.btc  { background:rgba(247,147,26,0.15);  color:#f7931a; }
.coin-icon.eth  { background:rgba(98,126,234,0.15);  color:#627eea; }
.coin-icon.sol  { background:rgba(153,69,255,0.15); color:#9945ff; }
.kx-network-card {
    display:none;
    background:var(--kx-card2); border:1px solid var(--kx-border);
    border-radius:14px; padding:1.1rem 1.25rem; margin-top:1rem;
}
.kx-network-card.visible { display:block; }
.kx-network-label { font-size:.75rem; font-weight:600; color:var(--kx-muted); margin-bottom:.7rem; text-transform:uppercase; letter-spacing:.5px; }
.kx-net-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.6rem; }
.kx-net-btn {
    background:var(--kx-card); border:2px solid var(--kx-border);
    border-radius:10px; padding:.65rem .4rem; text-align:center;
    cursor:pointer; transition:all .2s;
    font-size:.75rem; font-weight:700; color:var(--kx-muted);
}
.kx-net-btn:hover { border-color:rgba(0,204,0,.3); color:#fff; }
.kx-net-btn.selected { border-color:#00cc00; color:#fff; background:rgba(0,204,0,.07); box-shadow:0 0 0 2px rgba(0,204,0,.1); }
.kx-net-dot { width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:4px;vertical-align:middle; }
.kx-coin-btn .coin-name  { font-size:.82rem; font-weight:600; color:#fff; }
.kx-coin-btn .coin-rate  { font-size:.7rem; color:var(--kx-muted); margin-top:.15rem; }
.kx-coin-btn.selected .coin-rate { color:#00cc00; }

/* ── Amount toggle ── */
.kx-toggle-row {
    display:flex; background:var(--kx-card2); border-radius:10px;
    border:1px solid var(--kx-border); overflow:hidden; margin-bottom:1rem;
}
.kx-toggle-opt {
    flex:1; padding:.55rem; text-align:center;
    font-size:.82rem; font-weight:600; color:var(--kx-muted);
    cursor:pointer; transition:all .2s; border:none; background:none;
}
.kx-toggle-opt.active {
    background:rgba(0,204,0,0.1); color:#00cc00;
    border-bottom:2px solid #00cc00;
}

/* ── Input ── */
.kx-input-group { position:relative; }
.kx-input-prefix {
    position:absolute; left:14px; top:50%; transform:translateY(-50%);
    font-size:1rem; font-weight:700; color:var(--kx-green); pointer-events:none;
}
.kx-input {
    width:100%; background:var(--kx-card2);
    border:1.5px solid var(--kx-border); border-radius:12px;
    color:#fff; font-size:1.25rem; font-weight:600;
    padding:.8rem 1rem .8rem 2.2rem;
    transition:border-color .2s, box-shadow .2s;
    outline:none;
}
.kx-input:focus { border-color:#00cc00; box-shadow:0 0 0 3px rgba(0,204,0,0.12); }
.kx-input::placeholder { color:var(--kx-muted); font-weight:400; font-size:1rem; }

/* ── Conversion display ── */
.kx-conversion-box {
    background:rgba(0,204,0,0.04); border:1px solid rgba(0,204,0,0.12);
    border-radius:12px; padding:1rem 1.25rem;
    display:flex; align-items:center; justify-content:space-between; gap:.75rem;
    margin-top:.85rem;
}
.kx-conv-side { text-align:center; }
.kx-conv-label { font-size:.7rem; color:var(--kx-muted); margin-bottom:.25rem; }
.kx-conv-value { font-size:1.05rem; font-weight:700; color:#fff; }
.kx-conv-value.green { color:#00cc00; }
.kx-conv-arrow { font-size:1.1rem; color:var(--kx-muted); }
.kx-rate-pill {
    display:inline-flex; align-items:center; gap:.4rem;
    font-size:.73rem; color:var(--kx-muted);
    background:var(--kx-card2); border:1px solid var(--kx-border);
    border-radius:20px; padding:.25rem .75rem; margin-top:.85rem;
}
.kx-rate-pill span { color:#00cc00; font-weight:600; }

/* ── Submit button ── */
.kx-sell-btn {
    width:100%; background:linear-gradient(135deg,#00cc00,#007a0c);
    border:none; border-radius:12px; color:#fff;
    font-size:1rem; font-weight:600; padding:.9rem;
    cursor:pointer; transition:all .22s ease;
    display:flex; align-items:center; justify-content:center; gap:.6rem;
    box-shadow:0 4px 20px rgba(0,204,0,0.22);
}
.kx-sell-btn:hover:not(:disabled) {
    transform:translateY(-2px); box-shadow:0 8px 28px rgba(0,204,0,0.35);
}
.kx-sell-btn:disabled { opacity:.5; cursor:not-allowed; }

/* ── Alerts ── */
.kx-alert {
    display:flex; align-items:flex-start; gap:.7rem;
    padding:.9rem 1.1rem; border-radius:10px;
    font-size:.85rem; margin-bottom:1.25rem;
}
.kx-alert-err { background:rgba(220,53,69,0.1); border:1px solid rgba(220,53,69,0.25); color:#ffb3b3; }
.kx-alert-info { background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.25); color:#b8e9ff; }

/* ── Toast ── */
#kxToast {
    position:fixed; bottom:84px; left:50%; transform:translateX(-50%) translateY(20px);
    background:var(--kx-card); border:1px solid var(--kx-border); color:var(--kx-text);
    border-radius:12px; padding:.7rem 1.4rem;
    font-size:.82rem; font-weight:500; z-index:9999;
    opacity:0; pointer-events:none; transition:all .3s ease;
    white-space:nowrap; box-shadow:0 8px 28px rgba(0,0,0,0.4);
}
#kxToast.show { opacity:1; transform:translateX(-50%) translateY(0); }
#kxToast.err  { border-color:rgba(220,53,69,0.4); color:#ffb3b3; }
#kxToast.ok   { border-color:rgba(0,204,0,0.4);    color:#b3ffb3; }

@media(max-width:576px) {
    .sell-hero h1 { font-size:1.3rem; }
    .kx-conv-value { font-size:.9rem; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
<div class="col-xl-6 col-lg-7">

    
    <div class="sell-hero">
        <div class="sell-hero-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
        <h1>Sell Crypto</h1>
        <p>Convert your crypto to Naira — fast, secure, and at competitive rates.</p>
    </div>

    
    <div class="kx-steps">
        <div class="kx-step active">
            <div class="kx-step-num">1</div>
            <span class="kx-step-lbl">Amount</span>
        </div>
        <div class="kx-step">
            <div class="kx-step-num">2</div>
            <span class="kx-step-lbl">Bank Details</span>
        </div>
        <div class="kx-step">
            <div class="kx-step-num">3</div>
            <span class="kx-step-lbl">Confirm</span>
        </div>
    </div>

    
    <?php if($errors->any()): ?>
    <div class="kx-alert kx-alert-err">
        <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:2px"></i>
        <div><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($e); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
    </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <div class="kx-alert kx-alert-err" id="sessionErrBox">
        <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:2px"></i>
        <span><?php echo e(session('error')); ?></span>
    </div>
    <?php endif; ?>
    <?php if(!(auth()->user()?->kyc_verified)): ?>
    <div class="kx-alert kx-alert-info">
        <i class="bi bi-info-circle-fill" style="flex-shrink:0;margin-top:2px"></i>
        <span>Unverified users can sell up to <strong>₦500,000</strong> per trade. Complete KYC to unlock higher limits.</span>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('sell.postStep1')); ?>" id="sellForm">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="input_type"   id="inputTypeHidden" value="<?php echo e($inputType); ?>">
        <input type="hidden" name="usd_amount"   id="usdAmountHidden"   value="<?php echo e(session('sell.usd_amount','')); ?>">
        <input type="hidden" name="naira_amount" id="nairaAmountHidden" value="<?php echo e(session('sell.naira_amount','')); ?>">

        
        <div class="kx-card">
            <div class="kx-card-hd">
                <div class="hico"><i class="bi bi-currency-bitcoin"></i></div>
                <div>
                    <h5>Select Coin</h5>
                    <p>Choose the cryptocurrency you want to sell</p>
                </div>
            </div>
            <div class="kx-card-bd">
                
                <select name="coin" id="coinSelect" style="display:none" required>
                    <option value="">Choose a coin</option>
                    <?php $__currentLoopData = ['BTC'=>'Bitcoin','ETH'=>'Ethereum','USDT'=>'USDT','SOL'=>'Solana']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $symbol => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($symbol); ?>" <?php echo e($selectedCoin === $symbol ? 'selected' : ''); ?>><?php echo e($name); ?> (<?php echo e($symbol); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <div class="kx-coin-grid">
                    <?php
                        $coinMeta = [
                            'BTC'  => ['icon'=>'btc',  'label'=>'Bitcoin',  'sym'=>'BTC',  'ico'=>'₿'],
                            'ETH'  => ['icon'=>'eth',  'label'=>'Ethereum', 'sym'=>'ETH',  'ico'=>'Ξ'],
                            'USDT' => ['icon'=>'usdt', 'label'=>'USDT',     'sym'=>'USDT', 'ico'=>'₮'],
                            'SOL'  => ['icon'=>'sol',  'label'=>'Solana',   'sym'=>'SOL',  'ico'=>'◎'],
                        ];
                    ?>
                    <?php $__currentLoopData = $coinMeta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sym => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="kx-coin-btn <?php echo e($selectedCoin === $sym ? 'selected' : ''); ?>"
                         data-coin="<?php echo e($sym); ?>"
                         onclick="selectCoin('<?php echo e($sym); ?>')">
                        <div class="coin-icon <?php echo e($meta['icon']); ?>"><?php echo e($meta['ico']); ?></div>
                        <div class="coin-name"><?php echo e($meta['sym']); ?></div>
                        <div class="coin-rate" id="rate_<?php echo e($sym); ?>">
                            <?php if(isset($rates[$sym])): ?>
                                ₦<?php echo e(number_format($rates[$sym],0)); ?>/USD
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div class="kx-network-card <?php echo e(in_array($selectedCoin,['USDT']) ? 'visible' : ''); ?>" id="networkPicker">
                    <div class="kx-network-label"><i class="bi bi-diagram-3-fill me-1"></i>Select USDT Network</div>
                    <input type="hidden" name="network" id="networkInput" value="<?php echo e(session('sell.network','TRC20')); ?>">
                    <div class="kx-net-grid">
                        <?php $__currentLoopData = ['ERC20'=>['#627eea','Ethereum'],'TRC20'=>['#e84142','Tron'],'BEP20'=>['#f0b90b','BNB Chain']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $net=>[$color,$label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="kx-net-btn <?php echo e(session('sell.network','TRC20')===$net ? 'selected':''); ?>"
                             data-net="<?php echo e($net); ?>" onclick="selectNetwork('<?php echo e($net); ?>')">
                            <span class="kx-net-dot" style="background:<?php echo e($color); ?>"></span>
                            <?php echo e($net); ?><br><span style="font-size:.65rem;font-weight:400;opacity:.6"><?php echo e($label); ?></span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="kx-card">
            <div class="kx-card-hd">
                <div class="hico"><i class="bi bi-calculator-fill"></i></div>
                <div>
                    <h5>Enter Amount</h5>
                    <p>Wallet balance: <span class="kx-balance-chip"><i class="bi bi-wallet2"></i> ₦<?php echo e(number_format($balance,2)); ?></span></p>
                </div>
            </div>
            <div class="kx-card-bd">
                
                <div class="kx-toggle-row mb-3">
                    <button type="button" id="btnUSD"   class="kx-toggle-opt <?php echo e($inputType==='usd'   ? 'active' : ''); ?>" onclick="setMode('usd')">
                        <i class="bi bi-currency-dollar me-1"></i>USD
                    </button>
                    <button type="button" id="btnNaira" class="kx-toggle-opt <?php echo e($inputType==='naira' ? 'active' : ''); ?>" onclick="setMode('naira')">
                        <i class="bi bi-currency-exchange me-1"></i>Naira (₦)
                    </button>
                </div>

                
                <div class="kx-input-group">
                    <span class="kx-input-prefix" id="inputPrefix"><?php echo e($inputType==='usd' ? '$' : '₦'); ?></span>
                    <input type="number" name="amount" id="amountInput"
                           class="kx-input" step="0.01" min="0.01"
                           placeholder="0.00" value="<?php echo e($amount); ?>" required
                           oninput="calculateConversion()">
                </div>

                
                <div class="text-center mt-2">
                    <span class="kx-rate-pill" id="ratePill" style="display:none">
                        <i class="bi bi-graph-up-arrow"></i>
                        1 USD = <span id="rateDisplay">—</span>
                    </span>
                </div>

                
                <div class="kx-conversion-box" id="conversionBox" style="opacity:.4">
                    <div class="kx-conv-side">
                        <div class="kx-conv-label">You send (USD)</div>
                        <div class="kx-conv-value" id="usdDisplay">$0.00</div>
                    </div>
                    <div class="kx-conv-arrow"><i class="bi bi-arrow-right"></i></div>
                    <div class="kx-conv-side">
                        <div class="kx-conv-label">You receive (₦)</div>
                        <div class="kx-conv-value green" id="nairaDisplay">₦0.00</div>
                    </div>
                </div>
            </div>
        </div>

        
        <button type="submit" class="kx-sell-btn" id="submitBtn">
            <i class="bi bi-arrow-up-circle-fill"></i>
            <span>Continue to Bank Details</span>
        </button>
    </form>

</div>
</div>


<div id="kxToast"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const rates    = <?php echo json_encode($rates ?? []); ?>;
let isUSD      = <?php echo e($inputType === 'usd' ? 'true' : 'false'); ?>;
let activeCoin = '<?php echo e($selectedCoin); ?>';

/* ── Coin selection ── */
function selectCoin(sym) {
    activeCoin = sym;
    document.getElementById('coinSelect').value = sym;
    document.querySelectorAll('.kx-coin-btn').forEach(b => b.classList.remove('selected'));
    document.querySelector(`.kx-coin-btn[data-coin="${sym}"]`).classList.add('selected');

    // Show/hide network picker for USDT
    const picker = document.getElementById('networkPicker');
    if (picker) picker.classList.toggle('visible', sym === 'USDT');

    updateRatePill();
    calculateConversion();
}

/* ── Network selection (USDT) ── */
function selectNetwork(net) {
    document.getElementById('networkInput').value = net;
    document.querySelectorAll('.kx-net-btn').forEach(b => b.classList.toggle('selected', b.dataset.net === net));
}

function updateRatePill() {
    const pill = document.getElementById('ratePill');
    const rateEl = document.getElementById('rateDisplay');
    if (activeCoin && rates[activeCoin]) {
        rateEl.textContent = '₦' + parseFloat(rates[activeCoin]).toLocaleString('en-NG');
        pill.style.display = 'inline-flex';
    } else {
        pill.style.display = 'none';
    }
}

/* ── Currency mode toggle ── */
function setMode(mode) {
    const prev = isUSD ? 'usd' : 'naira';
    if (prev === mode) return;

    const amt = parseFloat(document.getElementById('amountInput').value) || 0;
    isUSD = mode === 'usd';
    document.getElementById('inputTypeHidden').value = mode;
    document.getElementById('inputPrefix').textContent = isUSD ? '$' : '₦';
    document.getElementById('btnUSD').classList.toggle('active', isUSD);
    document.getElementById('btnNaira').classList.toggle('active', !isUSD);

    // Convert existing value
    if (amt > 0 && activeCoin && rates[activeCoin]) {
        const rate = rates[activeCoin];
        document.getElementById('amountInput').value = isUSD
            ? (amt / rate).toFixed(2)
            : (amt * rate).toFixed(2);
    }
    calculateConversion();
}

/* ── Conversion calculation ── */
function calculateConversion() {
    const raw = parseFloat(document.getElementById('amountInput').value) || 0;
    const box = document.getElementById('conversionBox');

    if (!activeCoin || !rates[activeCoin] || raw <= 0) {
        document.getElementById('usdDisplay').textContent   = '$0.00';
        document.getElementById('nairaDisplay').textContent = '₦0.00';
        document.getElementById('usdAmountHidden').value    = '';
        document.getElementById('nairaAmountHidden').value  = '';
        box.style.opacity = '0.4';
        return;
    }

    const rate = rates[activeCoin];
    let usd, naira;
    if (isUSD) {
        usd   = raw;
        naira = raw * rate;
    } else {
        naira = raw;
        usd   = raw / rate;
    }

    document.getElementById('usdAmountHidden').value    = usd.toFixed(2);
    document.getElementById('nairaAmountHidden').value  = naira.toFixed(2);
    document.getElementById('usdDisplay').textContent   = '$' + usd.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById('nairaDisplay').textContent = '₦' + naira.toLocaleString('en-NG', {minimumFractionDigits:2, maximumFractionDigits:2});
    box.style.opacity = '1';
}

/* ── Toast ── */
function showToast(msg, type='err') {
    const t = document.getElementById('kxToast');
    t.textContent = msg;
    t.className   = `show ${type}`;
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), 3500);
}

/* ── Form validation ── */
document.getElementById('sellForm').addEventListener('submit', function(e) {
    const coin = document.getElementById('coinSelect').value;
    const amt  = parseFloat(document.getElementById('amountInput').value);
    const usd  = document.getElementById('usdAmountHidden').value;
    if (!coin) {
        e.preventDefault(); showToast('Please select a coin first.'); return;
    }
    if (!rates[coin]) {
        e.preventDefault(); showToast('Rate unavailable for ' + coin + '.'); return;
    }
    if (!amt || amt <= 0) {
        e.preventDefault(); showToast('Please enter a valid amount.'); return;
    }
    if (!usd) {
        e.preventDefault(); showToast('Conversion error — please try again.'); return;
    }
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
    // Reset after 30 s if server never responds
    setTimeout(() => {
        if (btn.disabled) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-up-circle-fill"></i><span>Continue to Bank Details</span>';
            showToast('Request timed out. Please try again.', 'err');
        }
    }, 30000);;
});

/* ── Init ── */
document.addEventListener('DOMContentLoaded', () => {
    if (activeCoin) updateRatePill();
    calculateConversion();
    const errs = <?php echo json_encode($errors->all(), 15, 512) ?>;
    if (errs.length) errs.forEach(e => showToast(e));
});

// Reset spinner if user presses Back
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        const btn = document.getElementById('submitBtn');
        if (btn && btn.disabled) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-up-circle-fill"></i><span>Continue to Bank Details</span>';
        }
    }
});
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('selllayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\sell\step1.blade.php ENDPATH**/ ?>