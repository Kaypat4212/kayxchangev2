@extends('adminnavlayout')
@section('title', 'Notification details')
@section('content')
<style>
.an-show{max-width:1040px;margin:0 auto;padding:24px 16px 60px;color:var(--kx-text,#e4e8f0)}
.an-back{display:inline-flex;align-items:center;gap:8px;color:#9aa7b7;text-decoration:none;font-size:.82rem;margin-bottom:16px}.an-back:hover{color:#fff}
.an-card{border:1px solid rgba(255,255,255,.08);border-radius:20px;background:linear-gradient(145deg,#171e2a,#111722);box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden}
.an-head{padding:clamp(20px,4vw,32px);border-bottom:1px solid rgba(255,255,255,.07);background:radial-gradient(ellipse at 100% 0,rgba(0,204,0,.11),transparent 45%)}
.an-kicker{font-size:.67rem;text-transform:uppercase;letter-spacing:.12em;font-weight:800;color:#71df89}.an-title{font-size:clamp(1.35rem,3vw,1.9rem);font-weight:800;color:#fff;margin:8px 0}.an-meta{display:flex;gap:8px;flex-wrap:wrap;color:#8997a9;font-size:.73rem}
.an-body{padding:clamp(20px,4vw,32px)}.an-message{white-space:pre-wrap;overflow-wrap:anywhere;color:#dbe3ea;font-size:.92rem;line-height:1.75}.an-context{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px;margin-top:22px}.an-fact{padding:12px 14px;border:1px solid rgba(255,255,255,.07);border-radius:12px;background:rgba(255,255,255,.025)}.an-label{display:block;color:#8493a6;text-transform:uppercase;font-size:.63rem;letter-spacing:.08em;margin-bottom:5px}.an-value{color:#e8edf2;font-size:.78rem;font-weight:650;overflow-wrap:anywhere}
.an-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:24px}.an-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 16px;border-radius:11px;text-decoration:none;font-size:.78rem;font-weight:750;border:1px solid transparent}.an-btn.primary{background:#00cc56;color:#041108}.an-btn.secondary{border-color:rgba(255,255,255,.12);color:#d7e0e8;background:rgba(255,255,255,.035)}.an-btn:hover{filter:brightness(1.08);color:inherit}.an-empty{padding:15px;border:1px dashed rgba(255,255,255,.14);border-radius:12px;color:#9aa7b7;font-size:.8rem;margin-top:18px}
@media(max-width:560px){.an-show{padding:16px 12px 44px}.an-actions .an-btn{width:100%}}
</style>
<main class="an-show">
    <a class="an-back" href="{{ route('admin.notifications.index') }}"><i class="bi bi-arrow-left"></i>All notifications</a>
    <article class="an-card">
        <header class="an-head">
            <div class="an-kicker"><i class="bi bi-bell-fill me-1"></i>Notification details</div>
            <h1 class="an-title">{{ $notification->title }}</h1>
            <div class="an-meta">
                <span>{{ $notification->created_at?->format('M j, Y · H:i') ?? '—' }}</span>
                <span>·</span><span>{{ $notification->is_read ? 'Read' : 'Unread' }}</span>
                @if($notification->is_broadcast)<span>·</span><span>Broadcast</span>@endif
            </div>
        </header>
        <div class="an-body">
            <div class="an-message">{{ $notification->message }}</div>
            <div class="an-context">
                <div class="an-fact"><span class="an-label">Recipient</span><span class="an-value">{{ $notification->user?->name ?? ($notification->is_broadcast ? 'All users' : 'Admin alert') }}</span></div>
                <div class="an-fact"><span class="an-label">Category</span><span class="an-value">{{ ucfirst(str_replace('_', ' ', $notification->type)) }}</span></div>
                @if($notification->admin)<div class="an-fact"><span class="an-label">Created by</span><span class="an-value">{{ $notification->admin->name }}</span></div>@endif
                @foreach(['support_case_id' => 'Support case', 'deposit_id' => 'Deposit', 'withdrawal_id' => 'Withdrawal', 'reference' => 'Reference'] as $key => $label)
                    @if(!empty($data[$key]))<div class="an-fact"><span class="an-label">{{ $label }}</span><span class="an-value">{{ $data[$key] }}</span></div>@endif
                @endforeach
            </div>
            @if($actionUrl)
                <div class="an-actions"><a class="an-btn primary" href="{{ $actionUrl }}"><i class="bi bi-arrow-up-right-circle"></i>{{ $actionLabel }}</a><a class="an-btn secondary" href="{{ route('admin.notifications.index') }}"><i class="bi bi-bell"></i>Back to notifications</a></div>
            @else
                <div class="an-empty"><i class="bi bi-info-circle me-1"></i>This notification has no linked admin action. Use the full message above to identify the relevant module.</div>
            @endif
        </div>
    </article>
</main>
@endsection
