@extends('adminnavlayout')

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}
body{background:var(--kx-dark)!important;color:var(--kx-text)!important;}
.an-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:20px;height:100%;}
.an-card-title{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--kx-muted);margin-bottom:16px;display:flex;align-items:center;gap:6px;}
.an-stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px;}
.an-stat{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;padding:16px 18px;}
.an-stat-label{font-size:.72rem;color:var(--kx-muted);margin-bottom:4px;}
.an-stat-val{font-size:1.4rem;font-weight:800;color:#fff;}
.an-stat-sub{font-size:.72rem;color:var(--kx-muted);margin-top:2px;}
.an-stat-val.green{color:var(--kx-green);}
.an-range{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px;}
.an-range-btn{background:var(--kx-card2);border:1px solid var(--kx-border);color:var(--kx-muted);border-radius:8px;padding:5px 12px;font-size:.77rem;font-weight:600;cursor:pointer;transition:all .15s;}
.an-range-btn.active,.an-range-btn:hover{background:rgba(0,204,0,.12);border-color:rgba(0,204,0,.3);color:var(--kx-green);}
.loading-spin{display:inline-block;width:18px;height:18px;border:2px solid rgba(0,204,0,.3);border-top-color:#00cc00;border-radius:50%;animation:spin .7s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0" style="color:#e4e8f0;"><i class="bi bi-bar-chart-line-fill me-2" style="color:#00cc00"></i>Trade Analytics</h4>
            <p class="text-muted small mb-0">Visual charts for volume, revenue, and coin distribution</p>
        </div>
        <div class="an-range" id="rangeBar">
            <button class="an-range-btn active" data-days="7">7D</button>
            <button class="an-range-btn" data-days="14">14D</button>
            <button class="an-range-btn" data-days="30">30D</button>
            <button class="an-range-btn" data-days="90">90D</button>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="an-stat-grid" id="statCards">
        <div class="an-stat"><div class="an-stat-label">Total Volume (NGN)</div><div class="an-stat-val green" id="s-volume">—</div></div>
        <div class="an-stat"><div class="an-stat-label">Total Trades</div><div class="an-stat-val" id="s-trades">—</div></div>
        <div class="an-stat"><div class="an-stat-label">Sell Trades</div><div class="an-stat-val" id="s-sell">—</div></div>
        <div class="an-stat"><div class="an-stat-label">Buy Trades</div><div class="an-stat-val" id="s-buy">—</div></div>
        <div class="an-stat"><div class="an-stat-label">Completed</div><div class="an-stat-val green" id="s-completed">—</div></div>
        <div class="an-stat"><div class="an-stat-label">Pending</div><div class="an-stat-val" id="s-pending">—</div></div>
    </div>

    <div class="row g-4">
        {{-- Daily Volume Line Chart --}}
        <div class="col-xl-8 col-lg-7">
            <div class="an-card">
                <div class="an-card-title"><i class="bi bi-graph-up-arrow"></i> Daily NGN Volume</div>
                <canvas id="volumeChart" height="100"></canvas>
            </div>
        </div>

        {{-- Coin Distribution Doughnut --}}
        <div class="col-xl-4 col-lg-5">
            <div class="an-card">
                <div class="an-card-title"><i class="bi bi-pie-chart-fill"></i> Coin Distribution</div>
                <canvas id="coinChart" height="180"></canvas>
            </div>
        </div>

        {{-- Buy vs Sell Bar --}}
        <div class="col-xl-6">
            <div class="an-card">
                <div class="an-card-title"><i class="bi bi-bar-chart-fill"></i> Buy vs Sell (Daily Count)</div>
                <canvas id="buyVsSellChart" height="120"></canvas>
            </div>
        </div>

        {{-- Revenue trend --}}
        <div class="col-xl-6">
            <div class="an-card">
                <div class="an-card-title"><i class="bi bi-currency-dollar"></i> Revenue Trend (NGN)</div>
                <canvas id="revenueChart" height="120"></canvas>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#7a8599';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';

let volumeChart, coinChart, bvChart, revenueChart;
let currentDays = 7;

function initCharts(data) {
    const charts = data.charts || {};
    const trading = data.trading || {};

    // stat cards
    const vol = charts.trading_volume || {};
    const totalNgn = (vol.labels||[]).length ? vol.datasets?.reduce((s,d) => s + (d.data||[]).reduce((a,b)=>a+b,0), 0) : 0;
    document.getElementById('s-volume').textContent = '₦' + totalNgn.toLocaleString('en-NG', {maximumFractionDigits:0});
    document.getElementById('s-trades').textContent = ((trading.total_sell||0) + (trading.total_buy||0));
    document.getElementById('s-sell').textContent = trading.total_sell || 0;
    document.getElementById('s-buy').textContent = trading.total_buy || 0;
    document.getElementById('s-completed').textContent = (trading.completed_sell||0) + (trading.approved_buy||0);
    document.getElementById('s-pending').textContent = (trading.pending_sell||0) + (trading.pending_buy||0);

    const ctxV = document.getElementById('volumeChart').getContext('2d');
    const ctxC = document.getElementById('coinChart').getContext('2d');
    const ctxB = document.getElementById('buyVsSellChart').getContext('2d');
    const ctxR = document.getElementById('revenueChart').getContext('2d');

    if (volumeChart) volumeChart.destroy();
    if (coinChart) coinChart.destroy();
    if (bvChart) bvChart.destroy();
    if (revenueChart) revenueChart.destroy();

    // Volume line chart
    const tvol = charts.trading_volume || {labels:[], datasets:[]};
    volumeChart = new Chart(ctxV, {
        type: 'line',
        data: {
            labels: tvol.labels || [],
            datasets: (tvol.datasets || []).map((ds,i) => ({
                ...ds,
                borderColor: i===0 ? '#00cc00' : '#3b82f6',
                backgroundColor: i===0 ? 'rgba(0,204,0,.08)' : 'rgba(59,130,246,.08)',
                tension: 0.4, fill: true, pointRadius: 3,
            }))
        },
        options: { responsive: true, plugins: { legend: { labels: { color:'#7a8599', boxWidth:12 } } }, scales: { y: { beginAtZero: true } } }
    });

    // Coin doughnut
    const coin = charts.coin_distribution || {labels:[], datasets:[]};
    coinChart = new Chart(ctxC, {
        type: 'doughnut',
        data: {
            labels: coin.labels || [],
            datasets: [{
                data: coin.datasets?.[0]?.data || [],
                backgroundColor: ['#f7931a','#627eea','#26a17b','#a855f7','#38bdf8'],
                borderWidth: 2, borderColor: '#161b27',
            }]
        },
        options: { responsive: true, plugins: { legend: { position:'bottom', labels:{color:'#7a8599',boxWidth:12,padding:12} } }, cutout:'65%' }
    });

    // Buy vs Sell bar
    const bvs = charts.user_growth || {labels:[], datasets:[]};
    bvChart = new Chart(ctxB, {
        type: 'bar',
        data: {
            labels: tvol.labels || [],
            datasets: [
                { label:'Sell', data: tvol.datasets?.[0]?.data || [], backgroundColor:'rgba(0,204,0,.5)', borderColor:'#00cc00', borderWidth:1, borderRadius:4 },
                { label:'Buy',  data: tvol.datasets?.[1]?.data || [], backgroundColor:'rgba(59,130,246,.5)', borderColor:'#3b82f6', borderWidth:1, borderRadius:4 }
            ]
        },
        options: { responsive: true, plugins: { legend: { labels: { color:'#7a8599', boxWidth:12 } } }, scales: { y: { beginAtZero: true } } }
    });

    // Revenue trend
    const rev = charts.revenue_trend || {labels:[], datasets:[]};
    revenueChart = new Chart(ctxR, {
        type: 'line',
        data: {
            labels: rev.labels || [],
            datasets: (rev.datasets || []).map(ds => ({
                ...ds,
                borderColor: '#fbbf24',
                backgroundColor: 'rgba(251,191,36,.07)',
                tension: 0.4, fill: true, pointRadius: 3,
            }))
        },
        options: { responsive: true, plugins: { legend: { labels: { color:'#7a8599', boxWidth:12 } } }, scales: { y: { beginAtZero: true } } }
    });
}

async function loadAnalytics() {
    try {
        const res = await fetch('/admin/analytics/dashboard-data', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        initCharts(data);
    } catch(e) {
        console.error('Analytics fetch error', e);
    }
}

// Range buttons (visual only for now — reloads with same API)
document.querySelectorAll('.an-range-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.an-range-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        loadAnalytics();
    });
});

loadAnalytics();
</script>
@endsection
