@extends('layouts.app')

@push('styles')
<style>
:root {
    --pa-green: #00cc00;
    --pa-bg: #081108;
    --pa-panel: rgba(255,255,255,0.04);
    --pa-border: rgba(255,255,255,0.08);
    --pa-text: #e9f5ea;
    --pa-muted: rgba(233,245,234,0.58);
}
body { background: var(--pa-bg); color: var(--pa-text); }
.pa-wrap { max-width: 900px; margin: 0 auto; padding: 1.5rem 1rem 4rem; }
.pa-card { background: var(--pa-panel); border: 1px solid var(--pa-border); border-radius: 14px; padding: 1.5rem; }
.pa-title { font-size: 1.4rem; font-weight: 700; color: var(--pa-green); }
.pa-badge { font-size: .72rem; padding: .25em .65em; border-radius: 20px; font-weight: 600; }
.badge-active   { background: rgba(0,204,0,.18); color: #4ade80; }
.badge-paused   { background: rgba(250,204,21,.15); color: #fbbf24; }
.badge-fired    { background: rgba(99,102,241,.18); color: #a5b4fc; }
.pa-table { width:100%; border-collapse:collapse; font-size:.9rem; }
.pa-table th { color: var(--pa-muted); font-weight:600; padding:.6rem .8rem; border-bottom:1px solid var(--pa-border); text-align:left; }
.pa-table td { padding:.65rem .8rem; border-bottom:1px solid rgba(255,255,255,.05); vertical-align:middle; }
.pa-table tr:last-child td { border-bottom: none; }
.form-section { background: var(--pa-panel); border: 1px solid var(--pa-border); border-radius: 14px; padding: 1.4rem; }
.form-label { color: var(--pa-muted); font-size:.85rem; margin-bottom:.3rem; }
.form-control, .form-select { background: rgba(0,0,0,.35); border: 1px solid var(--pa-border); color: var(--pa-text); border-radius:8px; }
.form-control:focus, .form-select:focus { border-color: var(--pa-green); box-shadow: 0 0 0 3px rgba(0,204,0,.15); background: rgba(0,0,0,.4); color:#fff; }
.form-select option { background: #0d1f0d; }
.btn-pa-create { background: var(--pa-green); color: #081108; font-weight:700; border-radius:8px; padding:.5rem 1.4rem; border:none; }
.btn-pa-create:hover { background: #00e500; }
.btn-pa-del { background: rgba(239,68,68,.15); color: #f87171; border: 1px solid rgba(239,68,68,.25); border-radius:6px; padding:.25rem .7rem; font-size:.8rem; }
.btn-pa-toggle { background: rgba(250,204,21,.12); color: #fbbf24; border: 1px solid rgba(250,204,21,.25); border-radius:6px; padding:.25rem .7rem; font-size:.8rem; }
.hint-text { font-size:.78rem; color: var(--pa-muted); }
.type-toggle .btn { font-size:.85rem; }
.type-toggle .btn.active { background: var(--pa-green); color:#081108; font-weight:700; }
.type-toggle .btn:not(.active) { background: transparent; color: var(--pa-muted); border:1px solid var(--pa-border); }
</style>
@endpush

@section('content')
<div class="pa-wrap">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="pa-title mb-1"><i class="bi bi-bell-fill me-2"></i>Price Alerts</h1>
            <p class="hint-text mb-0">Get notified via Telegram, email, or in-app when crypto prices hit your target.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 rounded-3" role="alert">
        {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger rounded-3 mb-4">
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
    @endif

    <div class="row g-4">

        {{-- Create Alert Form --}}
        <div class="col-lg-5">
            <div class="form-section">
                <h5 class="fw-bold mb-3" style="color:var(--pa-green)">Set New Alert</h5>

                <form method="POST" action="{{ route('price-alerts.store') }}">
                    @csrf

                    {{-- Alert Type --}}
                    <div class="mb-3">
                        <label class="form-label">Alert Type</label>
                        <div class="btn-group type-toggle w-100" id="typeToggle">
                            <button type="button" class="btn active" data-type="platform">Platform Rate <span class="hint-text">(NGN)</span></button>
                            <button type="button" class="btn"        data-type="market">Market Price <span class="hint-text">(USD)</span></button>
                        </div>
                        <input type="hidden" name="type" id="typeInput" value="platform">
                        <div class="hint-text mt-1" id="typeHint">Platform rate = what KayXchange offers for buy/sell in NGN.</div>
                    </div>

                    {{-- Coin --}}
                    <div class="mb-3">
                        <label class="form-label">Coin</label>
                        <select name="coin" class="form-select" required>
                            @foreach(['BTC','ETH','USDT','SOL','BNB'] as $c)
                            <option value="{{ $c }}" {{ old('coin') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Direction + Price --}}
                    <div class="row g-2 mb-3">
                        <div class="col-5">
                            <label class="form-label">When price is</label>
                            <select name="direction" class="form-select" required>
                                <option value="above" {{ old('direction') === 'above' ? 'selected' : '' }}>Above ↑</option>
                                <option value="below" {{ old('direction') === 'below' ? 'selected' : '' }}>Below ↓</option>
                            </select>
                        </div>
                        <div class="col-7">
                            <label class="form-label">Target price (<span id="currencyLabel">₦</span>)</label>
                            <input type="number" name="target_price" class="form-control" step="0.01" min="0.01"
                                   value="{{ old('target_price') }}" placeholder="e.g. 85000000" required>
                        </div>
                    </div>

                    {{-- Notification Channels --}}
                    <div class="mb-4">
                        <label class="form-label">Notify via</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="notify_app" value="1" id="chkApp" checked>
                                <label class="form-check-label hint-text" for="chkApp">In-App</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="notify_telegram" value="1" id="chkTg" checked>
                                <label class="form-check-label hint-text" for="chkTg">Telegram</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="notify_email" value="1" id="chkEmail" checked>
                                <label class="form-check-label hint-text" for="chkEmail">Email</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-pa-create w-100">
                        <i class="bi bi-bell-plus me-1"></i> Create Alert
                    </button>
                </form>
            </div>
        </div>

        {{-- Alerts List --}}
        <div class="col-lg-7">
            <div class="pa-card">
                <h5 class="fw-bold mb-3" style="color:var(--pa-green)">Your Alerts</h5>

                @if($alerts->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash display-5" style="color:var(--pa-muted)"></i>
                    <p class="mt-2" style="color:var(--pa-muted)">No alerts yet. Create your first one on the left.</p>
                </div>
                @else
                <div class="table-responsive">
                    <table class="pa-table">
                        <thead>
                            <tr>
                                <th>Coin</th>
                                <th>Type</th>
                                <th>Condition</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($alerts as $alert)
                        <tr>
                            <td><strong>{{ $alert->coin }}</strong></td>
                            <td>
                                <span class="hint-text">{{ $alert->type === 'platform' ? 'Platform' : 'Market' }}</span>
                            </td>
                            <td>
                                {{ $alert->direction === 'above' ? '↑ Above' : '↓ Below' }}
                                <strong>{{ $alert->type === 'platform' ? '₦' : '$' }}{{ number_format($alert->target_price, 2) }}</strong>
                                <br>
                                <span class="hint-text">
                                    @if($alert->notify_app)<i class="bi bi-bell-fill" title="In-app"></i>@endif
                                    @if($alert->notify_telegram)<i class="bi bi-telegram ms-1" title="Telegram"></i>@endif
                                    @if($alert->notify_email)<i class="bi bi-envelope-fill ms-1" title="Email"></i>@endif
                                </span>
                            </td>
                            <td>
                                @if($alert->triggered_at)
                                    <span class="pa-badge badge-fired">Fired {{ $alert->triggered_at->diffForHumans() }}</span>
                                @elseif($alert->is_active)
                                    <span class="pa-badge badge-active">Active</span>
                                @else
                                    <span class="pa-badge badge-paused">Paused</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form method="POST" action="{{ route('price-alerts.toggle', $alert) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-pa-toggle" title="{{ $alert->is_active ? 'Pause' : 'Reactivate' }}">
                                            <i class="bi bi-{{ $alert->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('price-alerts.destroy', $alert) }}"
                                          onsubmit="return confirm('Delete this alert?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-pa-del"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $alerts->links() }}
                @endif
            </div>
        </div>

    </div>{{-- /row --}}
</div>
@endsection

@push('scripts')
<script>
(function() {
    const btns   = document.querySelectorAll('#typeToggle .btn');
    const input  = document.getElementById('typeInput');
    const hint   = document.getElementById('typeHint');
    const label  = document.getElementById('currencyLabel');

    btns.forEach(btn => btn.addEventListener('click', () => {
        btns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const type = btn.dataset.type;
        input.value = type;
        if (type === 'platform') {
            hint.textContent  = 'Platform rate = what KayXchange offers for buy/sell in NGN.';
            label.textContent = '₦';
        } else {
            hint.textContent  = 'Market price = live CoinGecko USD price.';
            label.textContent = '$';
        }
    }));
})();
</script>
@endpush
