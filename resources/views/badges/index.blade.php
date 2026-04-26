@extends('layout')

@section('content')
<style>
    :root {
        --primary-green: #00cc00;
        --text-muted: #7a9a7a;
    }
    body { background: #060e06; color: #e8f5e8; font-family: 'Poppins', sans-serif; }

    .badges-container { max-width: 960px; margin: 0 auto; padding: 28px 16px 80px; }
    .badges-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: .75rem; }
    .badges-header h1 { font-size: 1.35rem; font-weight: 700; color: #e8f5e8; margin: 0; }
    .badges-header h1 i { color: #fbbf24; }

    .category-section { margin-bottom: 2.5rem; }
    .category-title {
        font-size: .72rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase;
        color: var(--text-muted); border-bottom: 1px solid rgba(255,255,255,.06);
        padding-bottom: .5rem; margin-bottom: 1.1rem;
    }
    .badge-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(175px, 1fr)); gap: 1rem; }

    .badge-card {
        background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08);
        border-radius: 14px; padding: 1.1rem; text-align: center;
        transition: border-color .25s, transform .2s, box-shadow .25s;
        position: relative;
    }
    .badge-card.earned { border-color: rgba(0,204,0,.25); }
    .badge-card.earned:hover { border-color: rgba(0,204,0,.5); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.3); }
    .badge-card.locked { opacity: .4; filter: grayscale(1); }
    .badge-card.pinned { outline: 2px solid #fbbf24; outline-offset: 2px; }

    .badge-emoji { font-size: 2.2rem; line-height: 1; margin-bottom: .5rem; display: block; }
    .badge-name { font-size: .8rem; font-weight: 700; margin-bottom: .15rem; }
    .badge-desc { font-size: .68rem; color: var(--text-muted); line-height: 1.4; margin-bottom: .6rem; }
    .badge-rarity {
        display: inline-block; font-size: .62rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; padding: .15rem .5rem; border-radius: 20px;
        background: rgba(255,255,255,.06); color: var(--text-muted);
    }
    .badge-rarity.common   { color: #94a3b8; background: rgba(148,163,184,.1); }
    .badge-rarity.rare     { color: #60a5fa; background: rgba(96,165,250,.1); }
    .badge-rarity.legendary{ color: #fbbf24; background: rgba(251,191,36,.1); }

    .pin-earned-at { font-size: .62rem; color: var(--text-muted); margin-bottom: .55rem; }

    .btn-pin, .btn-unpin {
        font-size: .7rem; font-weight: 600; border-radius: 8px;
        padding: .3rem .75rem; border: none; cursor: pointer; transition: opacity .2s;
        width: 100%;
    }
    .btn-pin { background: rgba(0,204,0,.15); color: #00cc00; }
    .btn-pin:hover { background: rgba(0,204,0,.25); }
    .btn-unpin { background: rgba(251,191,36,.12); color: #fbbf24; }
    .btn-unpin:hover { background: rgba(251,191,36,.22); }

    .locked-overlay {
        font-size: .65rem; color: var(--text-muted);
        margin-top: .4rem; display: flex; align-items: center; justify-content: center; gap: .3rem;
    }

    .pin-slot-bar { display: flex; gap: .75rem; margin-bottom: 2rem; flex-wrap: wrap; }
    .pin-slot {
        flex: 1; min-width: 130px; background: rgba(255,255,255,.03);
        border: 1px dashed rgba(255,255,255,.12); border-radius: 12px;
        padding: .8rem 1rem; text-align: center; font-size: .75rem; color: var(--text-muted);
    }
    .pin-slot.filled { border-style: solid; border-color: rgba(251,191,36,.3); background: rgba(251,191,36,.04); }
    .pin-slot .slot-emoji { font-size: 1.5rem; display: block; margin-bottom: .3rem; }
    .pin-slot .slot-name { font-size: .72rem; font-weight: 600; color: #e8f5e8; }
    .pin-slot .slot-label { font-size: .6rem; color: var(--text-muted); }

    .toast-notification {
        position: fixed; bottom: 80px; right: 20px; z-index: 9999;
        background: #1a2e1a; border: 1px solid rgba(0,204,0,.3); color: #e8f5e8;
        border-radius: 10px; padding: .6rem 1rem; font-size: .78rem;
        opacity: 0; transition: opacity .3s; pointer-events: none; min-width: 220px;
    }
    .toast-notification.show { opacity: 1; }
    .toast-notification.error { border-color: rgba(239,68,68,.3); }
</style>

<div class="badges-container">
    <div class="badges-header">
        <h1><i class="bi bi-award-fill"></i> Badges & Achievements</h1>
        <span style="font-size:.78rem;color:var(--text-muted)">{{ count($earnedIds) }} / {{ $allBadges->count() }} earned</span>
    </div>

    <!-- Pinned badge slots -->
    <div class="pin-slot-bar">
        @for($slot = 1; $slot <= 3; $slot++)
            @php $slotBadge = $pinnedBadges->firstWhere('pin_position', $slot); @endphp
            <div class="pin-slot {{ $slotBadge ? 'filled' : '' }}" id="slot-{{ $slot }}">
                @if($slotBadge)
                    <span class="slot-emoji">{{ $slotBadge->badge->emoji }}</span>
                    <div class="slot-name">{{ $slotBadge->badge->name }}</div>
                    <div class="slot-label">Slot {{ $slot }}</div>
                @else
                    <span class="slot-emoji" style="opacity:.3">✦</span>
                    <div style="font-size:.65rem">Empty slot {{ $slot }}</div>
                @endif
            </div>
        @endfor
    </div>

    <!-- Badge categories -->
    @foreach($grouped as $category => $badges)
    <div class="category-section">
        <div class="category-title">
            @if($category === 'trader_tier') 🏅 Trader Tier
            @elseif($category === 'volume_tier') 💰 Volume Tier
            @elseif($category === 'account') 🔑 Account Milestones
            @elseif($category === 'special') ⭐ Special Badges
            @else {{ ucfirst(str_replace('_', ' ', $category)) }}
            @endif
        </div>
        <div class="badge-grid">
            @foreach($badges as $badge)
            @php
                $isEarned = in_array($badge->id, $earnedIds);
                $isPinned = $pinnedBadges->contains(fn($ub) => $ub->badge_id === $badge->id);
                $earnedAt = $isEarned ? $user->userBadges()->where('badge_id', $badge->id)->value('awarded_at') : null;
            @endphp
            <div class="badge-card {{ $isEarned ? 'earned' : 'locked' }} {{ $isPinned ? 'pinned' : '' }}" id="badge-card-{{ $badge->id }}">
                <span class="badge-emoji">{{ $badge->emoji }}</span>
                <div class="badge-name" style="color:{{ $isEarned ? ($badge->color ?? '#e8f5e8') : '#7a9a7a' }}">{{ $badge->name }}</div>
                <div class="badge-desc">{{ $badge->description }}</div>
                <span class="badge-rarity {{ $badge->rarity }}">{{ ucfirst($badge->rarity) }}</span>

                @if($isEarned)
                    @if($earnedAt)
                    <div class="pin-earned-at mt-2">Earned {{ \Carbon\Carbon::parse($earnedAt)->diffForHumans() }}</div>
                    @endif
                    @if($isPinned)
                    <button class="btn-unpin mt-1" onclick="unpinBadge({{ $badge->id }}, this)">📌 Unpin</button>
                    @else
                    <button class="btn-pin mt-1" onclick="pinBadge({{ $badge->id }}, this)">📌 Pin</button>
                    @endif
                @else
                    <div class="locked-overlay mt-2">
                        <i class="bi bi-lock-fill"></i>
                        <span>Locked</span>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

<div class="toast-notification" id="toast"></div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function showToast(message, isError = false) {
    const el = document.getElementById('toast');
    el.textContent = message;
    el.className = 'toast-notification show' + (isError ? ' error' : '');
    clearTimeout(el._timer);
    el._timer = setTimeout(() => { el.classList.remove('show'); }, 3000);
}

async function pinBadge(badgeId, btn) {
    btn.disabled = true;
    try {
        const res = await fetch(`/badges/${badgeId}/pin`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message);
            // Update UI
            const card = document.getElementById(`badge-card-${badgeId}`);
            card.classList.add('pinned');
            btn.textContent = '📌 Unpin';
            btn.className = 'btn-unpin mt-1';
            btn.onclick = () => unpinBadge(badgeId, btn);
            btn.disabled = false;
        } else {
            showToast(data.message, true);
            btn.disabled = false;
        }
    } catch {
        showToast('An error occurred. Please try again.', true);
        btn.disabled = false;
    }
}

async function unpinBadge(badgeId, btn) {
    btn.disabled = true;
    try {
        const res = await fetch(`/badges/${badgeId}/unpin`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message);
            const card = document.getElementById(`badge-card-${badgeId}`);
            card.classList.remove('pinned');
            btn.textContent = '📌 Pin';
            btn.className = 'btn-pin mt-1';
            btn.onclick = () => pinBadge(badgeId, btn);
            btn.disabled = false;
        } else {
            showToast(data.message, true);
            btn.disabled = false;
        }
    } catch {
        showToast('An error occurred. Please try again.', true);
        btn.disabled = false;
    }
}
</script>
@endsection
