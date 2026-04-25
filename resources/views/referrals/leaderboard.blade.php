@extends('layout')

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}
.lb-hero{background:linear-gradient(135deg,#0a1a0a 0%,#0d1f12 100%);border:1px solid rgba(0,204,0,0.15);border-radius:18px;padding:32px;margin-bottom:24px;text-align:center;}
.lb-hero h1{font-size:1.6rem;font-weight:800;color:#fff;margin-bottom:6px;}
.lb-hero p{color:var(--kx-muted);font-size:.9rem;}
.lb-my-rank{display:inline-flex;align-items:center;gap:8px;background:rgba(0,204,0,.12);border:1px solid rgba(0,204,0,.25);border-radius:99px;padding:6px 18px;font-size:.82rem;font-weight:600;color:#00cc00;margin-top:14px;}
.lb-tiers{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:24px;}
.lb-tier-chip{display:flex;align-items:center;gap:6px;padding:8px 16px;border-radius:12px;font-size:.78rem;font-weight:600;border:1px solid;}
.lb-table-wrap{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;overflow:hidden;}
.lb-table{width:100%;border-collapse:collapse;}
.lb-table th{background:#0d1117;padding:12px 16px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--kx-muted);border-bottom:1px solid var(--kx-border);}
.lb-table td{padding:13px 16px;border-bottom:1px solid rgba(255,255,255,0.04);font-size:.85rem;color:var(--kx-text);vertical-align:middle;}
.lb-table tr:last-child td{border-bottom:none;}
.lb-table tr.is-me td{background:rgba(0,204,0,.05);}
.lb-rank{font-weight:800;font-size:1rem;color:var(--kx-muted);width:50px;}
.lb-rank.top1{color:#fbbf24;font-size:1.3rem;}
.lb-rank.top2{color:#94a3b8;font-size:1.2rem;}
.lb-rank.top3{color:#cd7f32;font-size:1.15rem;}
.lb-tier-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:.72rem;font-weight:700;border:1px solid;}
.lb-reward{color:#00cc00;font-weight:700;}
.me-badge{display:inline-block;background:rgba(0,204,0,.15);color:#00cc00;border-radius:99px;font-size:.65rem;font-weight:700;padding:1px 7px;margin-left:6px;vertical-align:middle;}
@media (max-width: 600px) {
    .lb-table th:nth-child(4),.lb-table td:nth-child(4){display:none;}
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="lb-hero">
        <div style="font-size:2.5rem;margin-bottom:8px;">🏆</div>
        <h1>Referral Leaderboard</h1>
        <p>Top referrers earn bigger rewards. Refer more friends to climb the tiers!</p>
        @if($myRank)
            <div class="lb-my-rank"><i class="bi bi-person-fill"></i> Your rank: #{{ $myRank }}</div>
        @endif
    </div>

    {{-- Tier Info --}}
    <div class="lb-tiers">
        <div class="lb-tier-chip" style="background:rgba(205,127,50,.1);color:#cd7f32;border-color:rgba(205,127,50,.25);">
            <i class="bi bi-star-fill"></i> Bronze — 1–4 referrals
        </div>
        <div class="lb-tier-chip" style="background:rgba(148,163,184,.1);color:#94a3b8;border-color:rgba(148,163,184,.25);">
            <i class="bi bi-award-fill"></i> Silver — 5–9 referrals
        </div>
        <div class="lb-tier-chip" style="background:rgba(251,191,36,.1);color:#fbbf24;border-color:rgba(251,191,36,.25);">
            <i class="bi bi-trophy-fill"></i> Gold — 10–24 referrals
        </div>
        <div class="lb-tier-chip" style="background:rgba(167,139,250,.1);color:#a78bfa;border-color:rgba(167,139,250,.25);">
            <i class="bi bi-gem"></i> Platinum — 25+ referrals
        </div>
    </div>

    <div class="lb-table-wrap">
        <table class="lb-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Referrer</th>
                    <th>Tier</th>
                    <th>Referrals</th>
                    <th>Rewards Earned</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaders as $i => $row)
                <tr class="{{ $row->is_me ? 'is-me' : '' }}">
                    <td class="lb-rank {{ $i===0 ? 'top1' : ($i===1 ? 'top2' : ($i===2 ? 'top3' : '')) }}">
                        @if($i===0) 🥇
                        @elseif($i===1) 🥈
                        @elseif($i===2) 🥉
                        @else {{ $i+1 }}
                        @endif
                    </td>
                    <td>
                        {{ $row->name }}
                        @if($row->is_me)<span class="me-badge">YOU</span>@endif
                    </td>
                    <td>
                        <span class="lb-tier-badge"
                            style="background:{{ $row->tier['color'] }}1a;color:{{ $row->tier['color'] }};border-color:{{ $row->tier['color'] }}40">
                            <i class="bi {{ $row->tier['icon'] }}"></i> {{ $row->tier['label'] }}
                        </span>
                    </td>
                    <td>{{ $row->completed_referrals }} <span style="color:var(--kx-muted);font-size:.75rem">/ {{ $row->total_referrals }} total</span></td>
                    <td class="lb-reward">₦{{ number_format($row->total_rewards, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:3rem;color:var(--kx-muted);">
                        <i class="bi bi-trophy" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                        No referrals yet. Be the first!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="text-align:center;margin-top:20px;">
        <a href="{{ route('referrals') }}" style="color:var(--kx-muted);font-size:.82rem;">
            <i class="bi bi-arrow-left me-1"></i>Back to My Referrals
        </a>
    </div>
</div>
@endsection
