@extends('layouts.app')

@section('title', 'Withdraw crypto')

@section('content')
@php
    $withdrawableWallets = $wallets->filter(fn ($wallet) => $wallet->isCrypto() && $wallet->is_active && (float) $wallet->available_balance > 0)->values();
    $walletNetworkOptions = $supportedNetworkOptions ?? [];
    $recentWithdrawals = \App\Models\CryptoWithdrawal::with('wallet')->where('user_id', auth()->id())->latest()->limit(4)->get();
@endphp

<style>
.kwd-page{--kwd-bg:#10171d;--kwd-card:#131d25;--kwd-card2:#19242d;--kwd-line:rgba(255,255,255,.09);--kwd-text:#f2f6f8;--kwd-muted:#96a6b2;--kwd-green:#51df86;--kwd-warn:#ffc96b;max-width:1160px;margin:0 auto;padding:16px 12px 70px;color:var(--kwd-text)}
.kwd-hero{position:relative;overflow:hidden;padding:clamp(22px,4vw,38px);border:1px solid var(--kwd-line);border-radius:26px;background:radial-gradient(ellipse at 94% 5%,rgba(81,223,134,.16),transparent 34%),radial-gradient(ellipse at 10% 100%,rgba(68,154,255,.12),transparent 35%),linear-gradient(135deg,#131d25,#101912 82%);box-shadow:0 22px 55px rgba(0,0,0,.2)}
.kwd-hero:after{content:"";position:absolute;width:210px;height:210px;right:7%;top:-120px;border:1px solid rgba(81,223,134,.15);border-radius:50%;box-shadow:0 0 0 25px rgba(81,223,134,.025),0 0 0 55px rgba(81,223,134,.02);pointer-events:none}
.kwd-kicker{color:var(--kwd-green);font-size:.7rem;font-weight:800;letter-spacing:.15em;text-transform:uppercase}.kwd-title{margin:.55rem 0 .5rem;color:#f7fafb;font-size:clamp(1.8rem,4vw,2.65rem);font-weight:850;letter-spacing:-.045em}.kwd-subtitle{max-width:660px;margin:0;color:#a7b4be;font-size:.95rem;line-height:1.7}
.kwd-hero-actions{display:flex;gap:9px;flex-wrap:wrap}.kwd-linkbtn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 14px;border:1px solid rgba(255,255,255,.14);border-radius:11px;background:rgba(255,255,255,.055);color:#e8f1f4;text-decoration:none;font-size:.83rem;font-weight:750;transition:.18s}.kwd-linkbtn:hover{border-color:rgba(81,223,134,.5);color:#fff;background:rgba(81,223,134,.09)}.kwd-linkbtn.primary{border-color:#4ad77b;background:linear-gradient(135deg,#5ce88e,#29b95e);color:#07140b;box-shadow:0 8px 22px rgba(53,201,105,.16)}
.kwd-trust-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:23px}.kwd-trust{display:inline-flex;align-items:center;gap:8px;padding:8px 11px;border:1px solid rgba(255,255,255,.09);border-radius:10px;background:rgba(2,8,5,.2);color:#c2ced4;font-size:.74rem}.kwd-trust i{color:var(--kwd-green)}
.kwd-grid{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(270px,.75fr);gap:18px;margin-top:18px}.kwd-card{border:1px solid var(--kwd-line);border-radius:19px;background:linear-gradient(155deg,rgba(25,36,45,.98),rgba(17,25,32,.98));box-shadow:0 14px 35px rgba(0,0,0,.13)}.kwd-card-head{display:flex;align-items:flex-start;gap:12px;padding:19px 20px;border-bottom:1px solid var(--kwd-line)}.kwd-head-icon{display:grid;place-items:center;flex:0 0 42px;width:42px;height:42px;border:1px solid rgba(81,223,134,.22);border-radius:13px;background:rgba(81,223,134,.1);color:var(--kwd-green);font-size:1.15rem}.kwd-card-head h2,.kwd-card-head h3{margin:1px 0 3px;color:#f4f8fa;font-size:1rem;font-weight:800}.kwd-card-head p{margin:0;color:var(--kwd-muted);font-size:.78rem}.kwd-form{padding:20px}.kwd-label{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;color:#dbe4e9;font-size:.78rem;font-weight:750}.kwd-label small{color:#84949e;font-size:.69rem;font-weight:550}.kwd-control{width:100%;min-height:48px;border:1px solid rgba(255,255,255,.12);border-radius:11px;background:#0e171d;color:#edf4f5;padding:11px 13px;font-size:.88rem;outline:none;transition:border-color .18s,box-shadow .18s}.kwd-control:focus{border-color:rgba(81,223,134,.65);box-shadow:0 0 0 3px rgba(81,223,134,.1)}.kwd-control::placeholder{color:#6f7f89}.kwd-control option{background:#10191f;color:#edf4f5}.kwd-field{margin-bottom:18px}.kwd-amount-wrap{position:relative}.kwd-amount-wrap .kwd-control{padding-right:78px}.kwd-max{position:absolute;right:8px;top:50%;transform:translateY(-50%);padding:6px 9px;border:1px solid rgba(81,223,134,.24);border-radius:7px;background:rgba(81,223,134,.1);color:#80eba5;font-size:.68rem;font-weight:850}.kwd-balance-line{display:flex;justify-content:space-between;gap:8px;margin-top:8px;color:#91a2ad;font-size:.73rem}.kwd-balance-line strong{color:#dce8e9;font-weight:750}.kwd-error{margin-top:6px;color:#ff8585;font-size:.75rem}.kwd-alert{display:flex;gap:11px;align-items:flex-start;margin:0 20px 18px;padding:13px 14px;border:1px solid rgba(255,201,107,.21);border-radius:12px;background:rgba(255,201,107,.07);color:#e7d2a7;font-size:.76rem;line-height:1.6}.kwd-alert i{margin-top:2px;color:var(--kwd-warn);font-size:1rem}.kwd-alert.success{border-color:rgba(81,223,134,.22);background:rgba(81,223,134,.07);color:#bce9cb}.kwd-alert.success i{color:var(--kwd-green)}.kwd-submit{display:flex;width:100%;min-height:51px;align-items:center;justify-content:center;gap:9px;border:0;border-radius:12px;background:linear-gradient(135deg,#55e68a,#29b95e);color:#06140b;font-size:.9rem;font-weight:850;box-shadow:0 10px 24px rgba(35,184,87,.18);transition:transform .15s,filter .15s}.kwd-submit:hover{transform:translateY(-1px);filter:brightness(1.04)}.kwd-submit:disabled{opacity:.5;cursor:not-allowed;transform:none}
.kwd-side{padding:18px}.kwd-side-title{margin:0 0 14px;color:#f4f8fa;font-size:.88rem;font-weight:800}.kwd-summary{display:grid;gap:12px}.kwd-summary-row{display:flex;justify-content:space-between;gap:10px;padding-bottom:11px;border-bottom:1px solid rgba(255,255,255,.07);color:#98a8b2;font-size:.76rem}.kwd-summary-row strong{max-width:58%;overflow-wrap:anywhere;color:#e7eef1;text-align:right;font-size:.76rem;font-weight:750}.kwd-asset-preview{display:flex;align-items:center;gap:11px;margin-bottom:17px;padding:12px;border:1px solid rgba(255,255,255,.08);border-radius:12px;background:rgba(255,255,255,.025)}.kwd-coin-icon{display:grid;place-items:center;width:40px;height:40px;border-radius:13px;background:rgba(81,223,134,.12);color:var(--kwd-green);font-size:1.15rem}.kwd-asset-preview strong{display:block;color:#eff5f6;font-size:.84rem}.kwd-asset-preview span{color:#93a2ad;font-size:.72rem}.kwd-note{margin-top:15px;padding:12px;border-radius:11px;background:rgba(68,154,255,.075);color:#9ebdd7;font-size:.72rem;line-height:1.6}.kwd-note i{color:#73b9ff}
.kwd-section{margin-top:24px}.kwd-section-head{display:flex;align-items:end;justify-content:space-between;gap:12px;margin-bottom:11px}.kwd-section-head h2{margin:2px 0 0;color:#f3f7f8;font-size:1.08rem;font-weight:820}.kwd-section-head a{color:#86e8a4;text-decoration:none;font-size:.76rem;font-weight:750}.kwd-history{overflow:hidden;border:1px solid var(--kwd-line);border-radius:17px;background:#121b22}.kwd-history-row{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(115px,.7fr) minmax(105px,.6fr) auto;align-items:center;gap:12px;padding:14px 17px;border-bottom:1px solid rgba(255,255,255,.055)}.kwd-history-row:last-child{border-bottom:0}.kwd-history-main{display:flex;align-items:center;gap:11px;min-width:0}.kwd-history-icon{display:grid;place-items:center;flex:0 0 36px;width:36px;height:36px;border-radius:11px;background:rgba(248,113,113,.1);color:#ff9999}.kwd-history-main strong{display:block;color:#eaf0f2;font-size:.78rem}.kwd-history-main span,.kwd-history-meta span{display:block;margin-top:3px;color:#82929d;font-size:.68rem}.kwd-history-amount{color:#e8eff1;font-size:.78rem;font-weight:780;text-align:right}.kwd-status{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid rgba(255,255,255,.1);border-radius:999px;background:rgba(255,255,255,.04);color:#c0cbd1;font-size:.67rem;font-weight:750;white-space:nowrap}.kwd-status.pending,.kwd-status.processing{border-color:rgba(255,201,107,.22);background:rgba(255,201,107,.07);color:#ffd78c}.kwd-status.completed{border-color:rgba(81,223,134,.22);background:rgba(81,223,134,.07);color:#8cedad}.kwd-status.rejected,.kwd-status.failed{border-color:rgba(255,112,112,.2);background:rgba(255,112,112,.07);color:#ff9b9b}.kwd-empty{padding:28px 16px;text-align:center;color:#9aa9b2}.kwd-empty i{display:block;margin-bottom:8px;color:#5fe28d;font-size:1.5rem}.kwd-empty strong{display:block;color:#eaf1f3;font-size:.85rem}.kwd-empty span{display:block;margin:5px auto 13px;max-width:380px;font-size:.73rem;line-height:1.55}.kwd-empty a{color:#85e8a3;font-size:.76rem;font-weight:750;text-decoration:none}
body.light-mode .kwd-page{--kwd-text:#14231a;--kwd-muted:#687b71;color:#17261e}body.light-mode .kwd-hero{background:radial-gradient(ellipse at 94% 5%,rgba(81,223,134,.18),transparent 34%),linear-gradient(135deg,#effaf2,#e7f5eb 82%);border-color:rgba(22,101,52,.13)}body.light-mode .kwd-title,body.light-mode .kwd-card-head h2,body.light-mode .kwd-card-head h3,body.light-mode .kwd-side-title,body.light-mode .kwd-section-head h2,body.light-mode .kwd-asset-preview strong,body.light-mode .kwd-history-main strong,body.light-mode .kwd-empty strong{color:#173322}body.light-mode .kwd-subtitle{color:#617669}body.light-mode .kwd-trust{background:rgba(255,255,255,.6);color:#53685a;border-color:rgba(22,101,52,.1)}body.light-mode .kwd-card,body.light-mode .kwd-history{background:#fff;border-color:rgba(22,101,52,.12);box-shadow:0 12px 28px rgba(25,64,37,.06)}body.light-mode .kwd-control{background:#f8fcf9;color:#183324;border-color:rgba(22,101,52,.17)}body.light-mode .kwd-control option{background:#fff;color:#173322}body.light-mode .kwd-label,body.light-mode .kwd-summary-row strong,body.light-mode .kwd-history-amount{color:#273e30}body.light-mode .kwd-asset-preview,body.light-mode .kwd-note{background:#f2faf4;border-color:rgba(22,101,52,.1)}body.light-mode .kwd-history-row{border-color:rgba(22,101,52,.08)}body.light-mode .kwd-history-main span,body.light-mode .kwd-history-meta span{color:#718276}
@media(max-width:800px){.kwd-grid{grid-template-columns:1fr}.kwd-side{order:2}.kwd-history-row{grid-template-columns:minmax(0,1fr) auto;gap:8px 12px}.kwd-history-meta{grid-column:1}.kwd-history-amount{grid-column:2;grid-row:1;text-align:right}.kwd-history-row>.kwd-status{grid-column:2;grid-row:2;justify-self:end}}
@media(max-width:480px){.kwd-page{padding:10px 4px 60px}.kwd-hero{border-radius:20px;padding:21px 17px}.kwd-hero-actions{width:100%}.kwd-linkbtn{flex:1;padding:10px 9px;font-size:.76rem}.kwd-card{border-radius:16px}.kwd-form{padding:16px}.kwd-card-head{padding:16px}.kwd-history-row{padding:13px 12px}.kwd-history-meta{font-size:.7rem}}
</style>

<main class="kwd-page">
    <header class="kwd-hero">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 position-relative" style="z-index:1">
            <div>
                <div class="kwd-kicker">Wallet · Secure transfer</div>
                <h1 class="kwd-title">Withdraw crypto</h1>
                <p class="kwd-subtitle">Send crypto to an external wallet. Choose the matching network carefully; requests are reviewed before they’re processed.</p>
            </div>
            <div class="kwd-hero-actions">
                <a class="kwd-linkbtn" href="{{ route('wallet.index') }}"><i class="bi bi-wallet2"></i>Wallet</a>
                <a class="kwd-linkbtn" href="{{ route('wallet.history') }}"><i class="bi bi-clock-history"></i>History</a>
            </div>
        </div>
        <div class="kwd-trust-row position-relative" style="z-index:1">
                    <span class="kwd-trust"><i class="bi bi-shield-check"></i>Admin approval and provider verification before payout</span>
            <span class="kwd-trust"><i class="bi bi-diagram-3"></i>Network matched to your wallet</span>
            <span class="kwd-trust"><i class="bi bi-hourglass-split"></i>Pending requests reduce available balance</span>
        </div>
    </header>

    @if(session('success'))
        <div class="kwd-alert success mt-3 mb-0" role="status"><i class="bi bi-check-circle-fill"></i><div>{{ session('success') }}</div></div>
    @endif
    @if(session('error'))
        <div class="kwd-alert mt-3 mb-0" role="alert"><i class="bi bi-exclamation-triangle-fill"></i><div>{{ session('error') }}</div></div>
    @endif

    <div class="kwd-grid">
        <section class="kwd-card" aria-labelledby="withdrawFormTitle">
            <div class="kwd-card-head">
                <div class="kwd-head-icon"><i class="bi bi-arrow-up-right"></i></div>
                <div><h2 id="withdrawFormTitle">Withdrawal details</h2><p>Enter the amount and destination wallet information.</p></div>
            </div>

            @if($withdrawableWallets->isEmpty())
                <div class="kwd-empty">
                    <i class="bi bi-wallet2"></i>
                    <strong>No crypto available to withdraw</strong>
                    <span>Fund a supported crypto wallet first. Pending withdrawal requests are excluded from available balances.</span>
                    <a href="{{ route('wallet.deposit') }}"><i class="bi bi-plus-circle me-1"></i>Go to crypto deposits</a>
                </div>
            @else
                <form class="kwd-form" action="{{ route('wallet.withdraw.create') }}" method="POST" id="walletWithdrawForm">
                    @csrf
                    <input type="hidden" name="request_key" value="{{ $withdrawalRequestKey }}">

                    <div class="kwd-field">
                        <label class="kwd-label" for="wallet_id">Pay from <small>Choose a funded crypto wallet</small></label>
                        <select class="kwd-control" name="wallet_id" id="wallet_id" required>
                            <option value="">Select an asset</option>
                            @foreach($withdrawableWallets as $wallet)
                                <option value="{{ $wallet->id }}"
                                    data-balance="{{ number_format((float) $wallet->available_balance, 8, '.', '') }}"
                                    data-currency="{{ strtoupper($wallet->currency) }}"
                                    data-network="{{ strtoupper((string) $wallet->network) }}"
                                    {{ (string) $selectedWalletId === (string) $wallet->id ? 'selected' : '' }}>
                                    {{ strtoupper($wallet->currency) }} · {{ number_format((float) $wallet->available_balance, 8, '.', '') }} available{{ $wallet->network ? ' · '.$wallet->network : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('wallet_id')<div class="kwd-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="kwd-field">
                        <label class="kwd-label" for="amount">Amount to withdraw <small id="amountCurrencyLabel">Crypto amount</small></label>
                        <div class="kwd-amount-wrap">
                            <input class="kwd-control" type="number" name="amount" id="amount" inputmode="decimal" step="0.000001" min="0.000001" value="{{ old('amount') }}" placeholder="0.000000" required>
                            <button class="kwd-max" type="button" id="useMaxButton">MAX</button>
                        </div>
                        <div class="kwd-balance-line"><span>Available balance</span><strong id="availableBalance">Select a wallet</strong></div>
                        @error('amount')<div class="kwd-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="kwd-field">
                        <label class="kwd-label" for="network">Network <small>Must match the recipient address</small></label>
                        <select class="kwd-control" name="network" id="network" required disabled>
                            <option value="">Select an asset first</option>
                        </select>
                        @error('network')<div class="kwd-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="kwd-field mb-3">
                        <label class="kwd-label" for="address">Recipient wallet address</label>
                        <input class="kwd-control" type="text" name="address" id="address" value="{{ old('address') }}" placeholder="Paste the destination address" autocomplete="off" autocapitalize="off" spellcheck="false" required>
                        <div class="kwd-balance-line"><span><i class="bi bi-lock me-1"></i>Check every character before submitting</span><span id="addressCount">0 / 255</span></div>
                        @error('address')<div class="kwd-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="kwd-alert"><i class="bi bi-exclamation-triangle-fill"></i><div><strong>Crypto transfers cannot be reversed.</strong> Only send to an address that supports the selected asset and network. Your request stays pending for admin review; the amount is held out of your available balance while pending.</div></div>

                    <button class="kwd-submit" type="submit" id="withdrawSubmit" {{ $withdrawableWallets->isEmpty() ? 'disabled' : '' }}><i class="bi bi-shield-lock-fill"></i>Submit withdrawal request</button>
                </form>
            @endif
        </section>

        <aside class="kwd-card kwd-side" aria-label="Withdrawal summary">
            <h2 class="kwd-side-title">Request preview</h2>
            <div class="kwd-asset-preview">
                <div class="kwd-coin-icon"><i class="bi bi-currency-bitcoin" id="previewCoinIcon"></i></div>
                <div><strong id="previewCoin">Choose an asset</strong><span id="previewNetwork">Network will appear here</span></div>
            </div>
            <div class="kwd-summary">
                <div class="kwd-summary-row"><span>From wallet</span><strong id="previewWallet">—</strong></div>
                <div class="kwd-summary-row"><span>Amount</span><strong id="previewAmount">—</strong></div>
                <div class="kwd-summary-row"><span>Destination</span><strong id="previewAddress">Not entered</strong></div>
                <div class="kwd-summary-row"><span>Current status</span><strong>Pending admin review</strong></div>
            </div>
            <div class="kwd-note"><i class="bi bi-info-circle-fill me-1"></i>Withdrawal requests are reviewed by the KayXchange team. You can track status and transaction details from your <a href="{{ route('wallet.history') }}" style="color:#9bd0ff;font-weight:750;text-decoration:none">wallet history</a>.</div>
        </aside>
    </div>

    <section class="kwd-section" aria-labelledby="recentWithdrawalsTitle">
        <div class="kwd-section-head">
            <div><div class="kwd-kicker">Your activity</div><h2 id="recentWithdrawalsTitle">Recent withdrawals</h2></div>
            <a href="{{ route('wallet.history') }}">View full history <i class="bi bi-arrow-up-right ms-1"></i></a>
        </div>
        <div class="kwd-history">
            @forelse($recentWithdrawals as $withdrawal)
                @php($statusClass = strtolower($withdrawal->status))
                <article class="kwd-history-row">
                    <div class="kwd-history-main">
                        <div class="kwd-history-icon"><i class="bi bi-arrow-up-right"></i></div>
                        <div class="text-truncate"><strong>{{ strtoupper($withdrawal->wallet?->currency ?? 'CRYPTO') }} withdrawal</strong><span>{{ $withdrawal->network }} · {{ $withdrawal->created_at->format('d M Y, H:i') }}</span></div>
                    </div>
                    <div class="kwd-history-meta"><span>Reference</span><span>CW-{{ $withdrawal->id }}</span></div>
                    <div class="kwd-history-amount">−{{ number_format((float) $withdrawal->amount, 8, '.', '') }} {{ strtoupper($withdrawal->wallet?->currency ?? '') }}</div>
                    <span class="kwd-status {{ $statusClass }}"><i class="bi {{ $statusClass === 'completed' ? 'bi-check-circle-fill' : (in_array($statusClass, ['rejected','failed'], true) ? 'bi-x-circle-fill' : 'bi-hourglass-split') }}"></i>{{ ucfirst($withdrawal->status) }}</span>
                </article>
            @empty
                <div class="kwd-empty"><i class="bi bi-send"></i><strong>No withdrawals yet</strong><span>Your submitted crypto withdrawal requests and their review status will appear here.</span></div>
            @endforelse
        </div>
    </section>
    @include('components.flow-resources', ['flow' => 'withdraw'])
</main>

<script>
(() => {
    const walletSelect = document.getElementById('wallet_id');
    const networkSelect = document.getElementById('network');
    const amountInput = document.getElementById('amount');
    const addressInput = document.getElementById('address');
    if (!walletSelect || !networkSelect || !amountInput || !addressInput) return;

    const networksByAsset = @json($walletNetworkOptions);
    const oldNetwork = @json($selectedNetwork ?? '');
    const numberFmt = new Intl.NumberFormat(undefined, {maximumFractionDigits: 8});

    function refreshPreview() {
        const option = walletSelect.options[walletSelect.selectedIndex];
        const currency = option?.dataset.currency || '';
        const balance = Number(option?.dataset.balance || 0);
        const suggestedNetwork = (option?.dataset.network || '').toUpperCase();
        document.getElementById('previewCoin').textContent = currency ? currency + ' wallet' : 'Choose an asset';
        document.getElementById('previewWallet').textContent = currency || '—';
        document.getElementById('availableBalance').textContent = currency ? numberFmt.format(balance) + ' ' + currency : 'Select a wallet';
        document.getElementById('amountCurrencyLabel').textContent = currency ? 'Available: ' + currency : 'Crypto amount';
        amountInput.max = currency ? String(balance) : '';
        document.getElementById('previewAmount').textContent = amountInput.value && currency ? numberFmt.format(Number(amountInput.value)) + ' ' + currency : '—';

        const map = networksByAsset[currency] || {};
        const choices = Object.entries(map);
        networkSelect.replaceChildren(new Option(choices.length ? 'Select network...' : 'No supported network found', ''));
        choices.forEach(([code, label]) => networkSelect.add(new Option(label, code, false, (oldNetwork || suggestedNetwork).toUpperCase() === code.toUpperCase())));
        networkSelect.disabled = choices.length === 0;
        document.getElementById('previewNetwork').textContent = networkSelect.value ? networkSelect.options[networkSelect.selectedIndex].text : (currency ? 'Select a network' : 'Network will appear here');
        const icon = document.getElementById('previewCoinIcon');
        icon.className = currency === 'BTC' ? 'bi bi-currency-bitcoin' : currency === 'USD' || currency === 'USDC' || currency === 'USDT' ? 'bi bi-currency-dollar' : 'bi bi-coin';
    }

    walletSelect.addEventListener('change', () => { networkSelect.value = ''; refreshPreview(); });
    amountInput.addEventListener('input', refreshPreview);
    networkSelect.addEventListener('change', refreshPreview);
    addressInput.addEventListener('input', () => {
        document.getElementById('addressCount').textContent = addressInput.value.length + ' / 255';
        document.getElementById('previewAddress').textContent = addressInput.value ? addressInput.value.slice(0, 14) + (addressInput.value.length > 22 ? '…' + addressInput.value.slice(-6) : '') : 'Not entered';
    });
    document.getElementById('useMaxButton').addEventListener('click', () => {
        const selected = walletSelect.options[walletSelect.selectedIndex];
        if (!selected?.dataset.balance) return;
        amountInput.value = Number(selected.dataset.balance).toFixed(6).replace(/0+$/, '').replace(/\.$/, '');
        refreshPreview();
    });
    document.getElementById('walletWithdrawForm')?.addEventListener('submit', event => {
        const selected = walletSelect.options[walletSelect.selectedIndex];
        const amount = Number(amountInput.value);
        if (!selected?.dataset.currency || !networkSelect.value || !addressInput.value.trim() || !Number.isFinite(amount) || amount <= 0 || amount > Number(selected.dataset.balance)) {
            event.preventDefault();
            if (amount > Number(selected?.dataset.balance || 0)) amountInput.focus();
            return;
        }
        const button = document.getElementById('withdrawSubmit');
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Submitting request…';
    });

    refreshPreview();
    addressInput.dispatchEvent(new Event('input'));
})();
</script>
@endsection
