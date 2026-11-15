@extends('adminnavlayout')

@push('styles')
<style>
:root{
    --kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
}
body{background:var(--kx-dark)!important;color:var(--kx-text)!important;}

.gc-wrap{padding:28px 0 80px;}
.gc-page-title{font-size:clamp(1.3rem,3vw,1.8rem);font-weight:800;color:#fff;margin-bottom:4px;}
.gc-page-sub{color:var(--kx-muted);font-size:.875rem;margin-bottom:28px;}

/* category accent colors */
.gc-cat-retail    {--cat:#3b82f6;}
.gc-cat-gaming    {--cat:#a78bfa;}
.gc-cat-streaming {--cat:#f472b6;}
.gc-cat-prepaid   {--cat:#fbbf24;}

/* Section card */
.gc-card{
    background:var(--kx-card);border:1px solid var(--kx-border);
    border-radius:16px;margin-bottom:24px;
    position:relative;overflow:hidden;
}
.gc-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:var(--cat,#00cc00);
}
.gc-card-header{
    padding:18px 24px;border-bottom:1px solid var(--kx-border);
    display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;
}
.gc-cat-label{
    font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;
    color:var(--cat,#00cc00);display:flex;align-items:center;gap:8px;
}
.gc-cat-label i{font-size:.9rem;}
.gc-card-body{padding:0;}

/* Table */
.gc-table{width:100%;border-collapse:collapse;font-size:.84rem;}
.gc-table thead th{
    padding:10px 16px;font-size:.7rem;font-weight:700;text-transform:uppercase;
    letter-spacing:.5px;color:var(--kx-muted);border-bottom:1px solid var(--kx-border);
    white-space:nowrap;
}
.gc-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s;}
.gc-table tbody tr:last-child{border-bottom:none;}
.gc-table tbody tr:hover{background:rgba(255,255,255,.02);}
.gc-table td{padding:10px 16px;vertical-align:middle;}

/* Badge: country */
.gc-country{
    display:inline-flex;align-items:center;gap:.3rem;
    background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
    border-radius:6px;padding:.15rem .5rem;font-size:.72rem;font-weight:700;color:var(--kx-text);
    font-family:monospace;
}
/* Rate input */
.gc-rate-input{
    background:var(--kx-card2);border:1px solid var(--kx-border);color:var(--kx-text);
    border-radius:8px;padding:.4rem .65rem;font-size:.84rem;width:110px;
    outline:none;transition:border-color .2s;font-family:monospace;
}
.gc-rate-input:focus{border-color:rgba(0,204,0,.45);box-shadow:0 0 0 3px rgba(0,204,0,.08);}

/* Toggle */
.gc-switch{position:relative;display:inline-block;width:40px;height:22px;}
.gc-switch input{opacity:0;width:0;height:0;}
.gc-slider{
    position:absolute;inset:0;background:rgba(239,68,68,.3);border:1px solid rgba(239,68,68,.4);
    border-radius:11px;cursor:pointer;transition:all .25s;
}
.gc-slider::before{
    content:'';position:absolute;height:14px;width:14px;left:3px;bottom:3px;
    background:#fff;border-radius:50%;transition:transform .25s;
}
.gc-switch input:checked+.gc-slider{background:rgba(0,204,0,.3);border-color:rgba(0,204,0,.5);}
.gc-switch input:checked+.gc-slider::before{transform:translateX(18px);background:var(--kx-green);}

/* Action btn */
.gc-delete-btn{
    background:none;border:none;color:rgba(239,68,68,.5);
    cursor:pointer;font-size:.85rem;padding:4px 8px;border-radius:6px;
    transition:color .15s,background .15s;
}
.gc-delete-btn:hover{color:#ef4444;background:rgba(239,68,68,.1);}

/* Save bar */
.gc-save-bar{
    position:sticky;bottom:0;z-index:10;
    background:rgba(13,17,23,.93);backdrop-filter:blur(12px);
    border-top:1px solid var(--kx-border);padding:14px 0;
}
.gc-save-btn{
    background:linear-gradient(135deg,#00cc00,#007a0c);color:#fff;
    border:none;border-radius:12px;padding:12px 30px;font-weight:700;
    font-size:.9rem;cursor:pointer;transition:all .22s;
    box-shadow:0 4px 18px rgba(0,204,0,.3);
}
.gc-save-btn:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,204,0,.42);}

/* Add card drawer */
.gc-add-card{
    background:var(--kx-card);border:1px solid var(--kx-border);
    border-radius:16px;padding:22px 26px;margin-bottom:24px;
}
.gc-add-title{font-size:.8rem;font-weight:700;text-transform:uppercase;
    letter-spacing:1px;color:var(--kx-green);margin-bottom:16px;
    display:flex;align-items:center;gap:8px;}
.gc-mini-input{
    background:var(--kx-card2);border:1px solid var(--kx-border);color:var(--kx-text);
    border-radius:10px;padding:.55rem .85rem;font-size:.84rem;
    outline:none;transition:border-color .2s;width:100%;
}
.gc-mini-input:focus{border-color:rgba(0,204,0,.45);box-shadow:0 0 0 3px rgba(0,204,0,.08);}
select.gc-mini-input option{background:var(--kx-card);}

/* alert */
.gc-alert{border-radius:12px;padding:12px 18px;margin-bottom:20px;font-size:.875rem;}
.gc-alert.ok{background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);color:#00cc00;}
.gc-alert.err{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#ef4444;}

/* Stat pills */
.gc-stats{display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:24px;}
.gc-stat{
    background:var(--kx-card);border:1px solid var(--kx-border);
    border-radius:12px;padding:14px 20px;display:flex;align-items:center;gap:.75rem;
    min-width:130px;
}
.gc-stat-icon{
    width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;
}
.gc-stat-val{font-size:1.25rem;font-weight:800;color:#fff;line-height:1.1;}
.gc-stat-lbl{font-size:.7rem;color:var(--kx-muted);}
</style>
@endpush

@section('content')
<div class="gc-wrap">
<div class="container-xl">

    <h1 class="gc-page-title"><i class="bi bi-gift-fill me-2" style="color:#00cc00"></i>Gift Card Rates</h1>
    <p class="gc-page-sub">Set the customer exchange rate, USD conversion, and editable per-order service fee. The customer price is card value × exchange rate + service fee. Customer sales use the buy rate and require admin review.</p>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="gc-alert ok"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="gc-alert err"><i class="bi bi-x-circle-fill me-2"></i>{{ $errors->first() }}</div>
    @endif

    <div class="gc-add-card mb-4 d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div><div class="gc-add-title mb-1"><i class="bi bi-toggles"></i> Customer gift card purchases</div><div style="font-size:.78rem;color:var(--kx-muted)">Only active USA and UK cards with a customer purchase rate are shown.</div></div>
        <div class="d-flex align-items-center gap-2"><span class="badge {{ $giftCardsEnabled ? 'bg-success' : 'bg-secondary' }}">{{ $giftCardsEnabled ? 'Purchases on' : 'Purchases off' }}</span><form method="POST" action="{{ route('admin.gift-card-rates.toggle-feature') }}">@csrf<input type="hidden" name="enabled" value="{{ $giftCardsEnabled ? 0 : 1 }}"><button class="gc-save-btn" type="submit">{{ $giftCardsEnabled ? 'Turn purchases off' : 'Turn purchases on' }}</button></form><a class="btn btn-outline-light btn-sm" href="{{ route('admin.gift-card-orders.index') }}"><i class="bi bi-inbox me-1"></i>Purchase requests</a></div>
    </div>

    <div class="gc-add-card mb-4 d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div><div class="gc-add-title mb-1"><i class="bi bi-shield-check"></i> Customer gift card sales</div><div style="font-size:.78rem;color:var(--kx-muted)">Sales are <strong>off by default</strong>. When enabled, users may submit USA and UK cards for manual verification. Naira is credited only after admin approval.</div></div>
        <div class="d-flex align-items-center gap-2 flex-wrap"><span class="badge {{ $giftCardSalesEnabled ? 'bg-success' : 'bg-secondary' }}">{{ $giftCardSalesEnabled ? 'Sales on' : 'Sales off' }}</span><form method="POST" action="{{ route('admin.gift-card-rates.toggle-sales-feature') }}">@csrf<input type="hidden" name="enabled" value="{{ $giftCardSalesEnabled ? 0 : 1 }}"><button class="gc-save-btn" type="submit">{{ $giftCardSalesEnabled ? 'Turn sales off' : 'Turn sales on' }}</button></form><a class="btn btn-outline-light btn-sm" href="{{ route('admin.gift-card-sales.index') }}"><i class="bi bi-inbox me-1"></i>Sale requests</a></div>
    </div>

    <div class="gc-add-card mb-4">
        <div class="gc-add-title mb-1"><i class="bi bi-speedometer2"></i> Gift card purchase limits</div>
        <div class="small mb-3" style="color:var(--kx-muted)">Purchases do not require KYC documents. These per order and daily Naira caps apply to every user; pending and completed purchases count toward the daily cap.</div>
        <form method="POST" action="{{ route('admin.gift-card-rates.purchase-limits') }}" class="row g-3 align-items-end">@csrf @method('PUT')
            <div class="col-sm-5"><label class="form-label small">Maximum per order (NGN)</label><input class="form-control" name="per_order_limit_ngn" type="number" min="1000" max="100000000" step="1000" value="{{ number_format($purchasePerOrderLimit, 2, '.', '') }}" required></div>
            <div class="col-sm-5"><label class="form-label small">Maximum per day (NGN)</label><input class="form-control" name="daily_limit_ngn" type="number" min="1000" max="100000000" step="1000" value="{{ number_format($purchaseDailyLimit, 2, '.', '') }}" required></div>
            <div class="col-sm-2"><button class="gc-save-btn w-100" type="submit">Save limits</button></div>
        </form>
    </div>

    {{-- Stats --}}
    @php
        $totalCards  = $rates->count();
        $activeCards = $rates->where('is_active', true)->count();
        $categories  = $rates->pluck('category')->unique()->count();
    @endphp
    <div class="gc-stats">
        <div class="gc-stat">
            <div class="gc-stat-icon" style="background:rgba(0,204,0,.12);color:#00cc00"><i class="bi bi-gift"></i></div>
            <div><div class="gc-stat-val">{{ $totalCards }}</div><div class="gc-stat-lbl">Total Cards</div></div>
        </div>
        <div class="gc-stat">
            <div class="gc-stat-icon" style="background:rgba(59,130,246,.12);color:#60a5fa"><i class="bi bi-toggle-on"></i></div>
            <div><div class="gc-stat-val">{{ $activeCards }}</div><div class="gc-stat-lbl">Active</div></div>
        </div>
        <div class="gc-stat">
            <div class="gc-stat-icon" style="background:rgba(167,139,250,.12);color:#a78bfa"><i class="bi bi-tags"></i></div>
            <div><div class="gc-stat-val">{{ $categories }}</div><div class="gc-stat-lbl">Categories</div></div>
        </div>
    </div>

    {{-- ── Add New Card ── --}}
    <div class="gc-add-card">
        <div class="gc-add-title"><i class="bi bi-plus-circle-fill"></i> Add New Gift Card</div>
        <form method="POST" action="{{ route('admin.gift-card-rates.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" style="font-size:.75rem;color:var(--kx-muted);font-weight:600;">Card Name</label>
                    <input type="text" name="name" class="gc-mini-input" placeholder="e.g. Amazon" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label" style="font-size:.75rem;color:var(--kx-muted);font-weight:600;">Country</label>
                    <select name="country" class="gc-mini-input" required>
                        <option value="US">US</option>
                        <option value="UK">UK</option>
                        <option value="CA">CA</option>
                        <option value="AU">AU</option>
                        <option value="DE">DE</option>
                        <option value="EU">EU</option>
                        <option value="NG">NG</option>
                        <option value="OTHER">Other</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label" style="font-size:.75rem;color:var(--kx-muted);font-weight:600;">Currency</label>
                    <select name="currency" class="gc-mini-input" required>
                        <option value="USD">USD</option>
                        <option value="GBP">GBP</option>
                        <option value="CAD">CAD</option>
                        <option value="AUD">AUD</option>
                        <option value="EUR">EUR</option>
                        <option value="NGN">NGN</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.75rem;color:var(--kx-muted);font-weight:600;">Category</label>
                    <select name="category" class="gc-mini-input" required>
                        <option value="retail">Retail</option>
                        <option value="gaming">Gaming</option>
                        <option value="streaming">Streaming</option>
                        <option value="prepaid">Prepaid</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label" style="font-size:.75rem;color:var(--kx-muted);font-weight:600;">Buy Rate ₦</label>
                    <input type="number" name="buy_rate" class="gc-mini-input" placeholder="0" min="0" step="0.01" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label" style="font-size:.75rem;color:var(--kx-muted);font-weight:600;">Base rate ₦</label>
                    <input type="number" name="sell_rate" class="gc-mini-input" placeholder="0" min="0" step="0.01" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label" style="font-size:.75rem;color:var(--kx-muted);font-weight:600;">USD / unit</label>
                    <input type="number" name="usd_exchange_rate" class="gc-mini-input" placeholder="USD per unit" min="0.000001" step="0.000001">
                </div>
                <div class="col-md-1">
                    <label class="form-label" style="font-size:.75rem;color:var(--kx-muted);font-weight:600;">Service fee ₦ / order</label>
                    <input type="number" name="purchase_markup_ngn" class="gc-mini-input" value="3000" min="0" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.75rem;color:var(--kx-muted);font-weight:600;">Card artwork (optional)</label>
                    <input type="file" name="image" class="gc-mini-input" accept="image/jpeg,image/png,image/webp">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="gc-save-btn w-100" style="padding:10px 0;font-size:.8rem;">
                        <i class="bi bi-plus-lg me-1"></i>Add
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Bulk Update Form ── --}}
    <form method="POST" action="{{ route('admin.gift-card-rates.bulk-update') }}" id="gcBulkForm" enctype="multipart/form-data">
        @csrf

        @php
        $catMeta = [
            'retail'    => ['label' => 'Retail',    'icon' => 'bi-bag',           'cls' => 'gc-cat-retail'],
            'gaming'    => ['label' => 'Gaming',     'icon' => 'bi-controller',    'cls' => 'gc-cat-gaming'],
            'streaming' => ['label' => 'Streaming',  'icon' => 'bi-play-circle',   'cls' => 'gc-cat-streaming'],
            'prepaid'   => ['label' => 'Prepaid',    'icon' => 'bi-credit-card',   'cls' => 'gc-cat-prepaid'],
        ];
        @endphp

        @foreach($grouped as $category => $cards)
        @php $cm = $catMeta[$category] ?? ['label' => ucfirst($category), 'icon' => 'bi-tag', 'cls' => '']; @endphp
        <div class="gc-card {{ $cm['cls'] }}">
            <div class="gc-card-header">
                <div class="gc-cat-label">
                    <i class="bi {{ $cm['icon'] }}"></i>{{ $cm['label'] }}
                    <span style="font-size:.7rem;font-weight:400;color:var(--kx-muted);margin-left:4px;">({{ $cards->count() }} cards)</span>
                </div>
            </div>
            <div class="gc-card-body">
                <div class="table-responsive">
                    <table class="gc-table">
                        <thead>
                            <tr>
                                <th>Card Name</th>
                                <th>Artwork</th>
                                <th>Country</th>
                                <th>Currency</th>
                                <th>Buy Rate (₦)</th>
                                <th>Base Sell Rate (₦ / unit)</th>
                                <th>USD / Unit</th>
                                <th>Service fee (₦ / order)</th>
                                <th>Active</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cards as $card)
                            <tr>
                                <td style="font-weight:600;color:#fff;">{{ $card->name }}</td>
                                <td><input type="file" name="rates[{{ $card->id }}][image]" class="gc-rate-input" accept="image/jpeg,image/png,image/webp" aria-label="Upload {{ $card->name }} artwork"><small class="d-block text-secondary">{{ $card->image_path ? 'Current image saved' : 'Icon fallback' }}</small></td>
                                <td><span class="gc-country">{{ $card->country }}</span></td>
                                <td style="color:var(--kx-muted);font-family:monospace;font-size:.78rem;">{{ $card->currency }}</td>
                                <td>
                                    <input type="number"
                                        name="rates[{{ $card->id }}][buy_rate]"
                                        value="{{ $card->buy_rate }}"
                                        class="gc-rate-input"
                                        min="0" step="0.01"
                                        placeholder="0">
                                </td>
                                <td>
                                    <input type="number"
                                        name="rates[{{ $card->id }}][sell_rate]"
                                        value="{{ $card->sell_rate }}"
                                        class="gc-rate-input"
                                        min="0" step="0.01"
                                        placeholder="0">
                                </td>
                                <td>
                                    <input type="number"
                                        name="rates[{{ $card->id }}][usd_exchange_rate]"
                                        value="{{ $card->currency === 'USD' ? '1' : $card->usd_exchange_rate }}"
                                        class="gc-rate-input"
                                        min="0.000001" step="0.000001"
                                        placeholder="Set USD value per unit"
                                        {{ $card->currency === 'USD' ? 'readonly title=USD-per-USD-is-always-1' : '' }}>
                                </td>
                                <td>
                                    <input type="number"
                                        name="rates[{{ $card->id }}][purchase_markup_ngn]"
                                        value="{{ number_format((float) ($card->purchase_markup_ngn ?? 3000), 2, '.', '') }}"
                                        class="gc-rate-input"
                                        min="0" step="0.01"
                                        placeholder="3000">
                                </td>
                                <td>
                                    <label class="gc-switch">
                                        <input type="checkbox"
                                            name="rates[{{ $card->id }}][is_active]"
                                            value="1"
                                            {{ $card->is_active ? 'checked' : '' }}>
                                        <span class="gc-slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <button type="button"
                                        class="gc-delete-btn"
                                        onclick="deleteCard({{ $card->id }}, '{{ addslashes($card->name) }} ({{ $card->country }})')"
                                        title="Delete">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Sticky save --}}
        <div class="gc-save-bar">
            <div class="container-xl d-flex align-items-center gap-3">
                <button type="submit" class="gc-save-btn">
                    <i class="bi bi-floppy-fill me-2"></i>Save All Rates
                </button>
                <span id="gcSaveMsg" class="text-success small" style="display:none">
                    <i class="bi bi-check-circle-fill me-1"></i>Rates saved!
                </span>
            </div>
        </div>
    </form>

</div>
</div>

{{-- Delete form (hidden) --}}
<form id="gcDeleteForm" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
(function(){
    // ── Delete card ──
    window.deleteCard = function(id, label) {
        if (!confirm('Delete "' + label + '"? This cannot be undone.')) return;
        const form = document.getElementById('gcDeleteForm');
        form.action = '/admin/gift-card-rates/' + id;
        form.submit();
    };

    // ── Auto-select input text on focus for quick edit ──
    document.querySelectorAll('.gc-rate-input').forEach(function(el) {
        el.addEventListener('focus', function() { this.select(); });
    });
})();
</script>
@endpush
