@extends('layout')

@section('content')
<style>
.kx-thread{max-width:950px;margin:0 auto;padding:22px 0 60px;color:#edf7ef}.kx-thread a{text-decoration:none}.kx-thread-back{display:inline-flex;align-items:center;gap:7px;color:#91a395;font-size:.76rem;margin:0 0 14px}.kx-thread-card,.kx-reply-card{border:1px solid rgba(255,255,255,.09);border-radius:19px;background:rgba(255,255,255,.035);overflow:hidden}.kx-thread-main{padding:clamp(18px,3vw,28px)}.kx-thread-cat{display:inline-flex;gap:7px;align-items:center;color:#78e889;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.kx-thread-title{font-size:clamp(1.35rem,3.4vw,2.1rem);font-weight:800;letter-spacing:-.04em;line-height:1.3;margin:10px 0}.kx-thread-meta{display:flex;flex-wrap:wrap;gap:8px 15px;color:#829386;font-size:.7rem}.kx-thread-body{white-space:pre-wrap;overflow-wrap:anywhere;color:#cbd8ce;font-size:.88rem;line-height:1.8;margin-top:21px}.kx-linked-article{display:flex;gap:11px;align-items:center;padding:12px 14px;border-radius:12px;background:rgba(56,189,248,.065);border:1px solid rgba(56,189,248,.15);color:#cceaff;font-size:.77rem}.kx-thread-actions{display:flex;align-items:center;gap:9px;flex-wrap:wrap;padding-top:17px;margin-top:20px;border-top:1px solid rgba(255,255,255,.07)}.kx-thread-action{display:inline-flex;gap:7px;align-items:center;padding:8px 11px;border:1px solid rgba(255,255,255,.11);border-radius:10px;background:#ffffff07;color:#b7c8bc;font-size:.72rem}.kx-thread-action:hover,.kx-thread-action.active{color:#98f6a6;border-color:rgba(0,204,0,.3);background:rgba(0,204,0,.08)}.kx-report details{position:relative}.kx-report summary{list-style:none;cursor:pointer;color:#9aaba0;font-size:.72rem;padding:8px}.kx-report summary::-webkit-details-marker{display:none}.kx-report-form{position:absolute;z-index:4;top:36px;right:0;width:min(320px,calc(100vw - 42px));padding:14px;border:1px solid rgba(255,255,255,.14);border-radius:14px;background:#111b14;box-shadow:0 18px 44px rgba(0,0,0,.35)}.kx-report-form select,.kx-report-form textarea{width:100%;margin-bottom:8px;padding:9px 10px;border:1px solid #ffffff18;border-radius:9px;background:#0a120d;color:#eaf5ec;font-size:.72rem}.kx-replies{margin-top:22px}.kx-replies-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px}.kx-replies-head h2{font-size:1rem;font-weight:800}.kx-reply-card{padding:16px;margin-bottom:10px}.kx-reply-top{display:flex;align-items:center;gap:9px;color:#91a395;font-size:.7rem}.kx-reply-author{font-weight:700;color:#e5f1e7}.kx-reply-body{white-space:pre-wrap;overflow-wrap:anywhere;color:#c4d2c7;font-size:.81rem;line-height:1.75;margin:13px 0}.kx-reply-footer{display:flex;align-items:center;gap:8px}.kx-reply-form{padding:17px;border:1px solid rgba(0,204,0,.18);border-radius:17px;background:rgba(0,204,0,.035);margin-top:18px}.kx-reply-form textarea{width:100%;min-height:130px;resize:vertical;padding:12px;border:1px solid rgba(255,255,255,.12);border-radius:11px;background:#0b140e;color:#eff8f0;outline:0;font-size:.8rem;line-height:1.65}.kx-reply-form textarea:focus{border-color:#00c844;box-shadow:0 0 0 3px rgba(0,200,68,.1)}.kx-thread-locked{padding:14px 16px;border:1px solid rgba(251,191,36,.16);border-radius:12px;background:#fbbf2409;color:#d5c486;font-size:.76rem}.kx-thread-disclaimer{color:#8a9a8e;font-size:.71rem;line-height:1.6;margin-top:13px}@media(max-width:600px){.kx-thread{padding:12px 0 45px}.kx-thread-main{padding:18px}.kx-thread-meta{font-size:.66rem}.kx-reply-card{padding:13px}}
body.light-mode .kx-thread{color:#17321d}body.light-mode .kx-thread-card,body.light-mode .kx-reply-card{background:#fff;border-color:#dfe9e0}body.light-mode .kx-thread-body,body.light-mode .kx-reply-body{color:#405544}body.light-mode .kx-thread-title,body.light-mode .kx-reply-author{color:#18341e}body.light-mode .kx-report-form{background:#fff;border-color:#dfe9e0}body.light-mode .kx-report-form select,body.light-mode .kx-report-form textarea,body.light-mode .kx-reply-form textarea{background:#f8fcf8;color:#17321d;border-color:#dfe9e0}
</style>
<main class="kx-thread container-xl">
    <a href="{{ route('forum.index') }}" class="kx-thread-back"><i class="bi bi-arrow-left"></i>Back to community</a>
    @if(session('success') || session('status'))<div class="alert alert-success py-2">{{ session('success') ?? session('status') }}</div>@endif
    @if(session('error'))<div class="alert alert-warning py-2">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif
    <article class="kx-thread-card">
        <div class="kx-thread-main">
            <div class="kx-thread-cat"><i class="bi {{ $topic->category->icon }}"></i>{{ $topic->category->name }} @if($topic->is_pinned)<span class="badge text-bg-warning ms-1">Pinned</span>@endif @if($topic->is_locked)<span class="badge text-bg-secondary ms-1">Closed</span>@endif</div>
            <h1 class="kx-thread-title">{{ $topic->title }}</h1>
            <div class="kx-thread-meta"><span>Posted by <strong class="text-light">{{ $topic->user->name ?? 'Member' }}</strong>@if($topic->user?->is_admin) <span class="text-info" title="Verified KayXchange official account" aria-label="Verified KayXchange official account"><i class="bi bi-patch-check-fill"></i> Official</span>@endif</span><span>{{ $topic->created_at->format('M d, Y · g:i A') }}</span><span><i class="bi bi-eye me-1"></i>{{ number_format($topic->views_count) }} views</span><span><i class="bi bi-chat-left-text me-1"></i>{{ $topic->visible_replies_count ?? $topic->replies_count }} replies</span></div>
            @if($topic->blogPost)<a class="kx-linked-article mt-4" href="{{ route('blog.show', $topic->blogPost->slug) }}"><i class="bi bi-journal-richtext"></i><span><strong>From the KayXchange Blog</strong><br>{{ $topic->blogPost->title }}</span><i class="bi bi-arrow-up-right ms-auto"></i></a>@endif
            <div class="kx-thread-body">{{ $topic->body }}</div>
            <div class="kx-thread-actions">
                <form method="POST" action="{{ route('forum.react', $topic) }}">@csrf<button class="kx-thread-action {{ $userReacted ? 'active' : '' }}" type="submit"><i class="bi bi-hand-thumbs-up"></i>{{ $userReacted ? 'Helpful' : 'Mark helpful' }} · {{ $topic->reactions_count }}</button></form>
                <a class="kx-thread-action" href="#replyForm"><i class="bi bi-reply"></i>Reply</a>
                <div class="kx-report"><details><summary><i class="bi bi-flag me-1"></i>Report</summary><form class="kx-report-form" method="POST" action="{{ route('forum.report') }}">@csrf<input type="hidden" name="topic_id" value="{{ $topic->id }}"><label class="small mb-1">Why are you reporting this?</label><select name="reason" required><option value="">Choose a reason</option><option value="scam">Scam or suspicious offer</option><option value="spam">Spam or promotion</option><option value="abuse">Harassment or abuse</option><option value="misinformation">Misleading information</option><option value="other">Other concern</option></select><textarea name="details" rows="2" maxlength="1000" placeholder="Optional context"></textarea><button class="btn btn-sm btn-warning w-100" type="submit">Send report</button></form></details></div>
            </div>
        </div>
    </article>

    <section class="kx-replies" id="replies">
        <div class="kx-replies-head"><h2 class="mb-0">Replies <span class="text-secondary">({{ $topic->visible_replies_count ?? $topic->replies_count }})</span></h2><a class="text-success small" href="{{ route('blog.index') }}"><i class="bi bi-journal-text me-1"></i>Explore blog</a></div>
        @forelse($replies as $reply)
            @php $replyReacted = in_array($reply->id, $reactedReplies, true); @endphp
            <article class="kx-reply-card">
                <div class="kx-reply-top"><span class="kx-reply-author">{{ $reply->user->name ?? 'Member' }}@if($reply->user?->is_admin) <span class="text-info" title="Verified KayXchange official account" aria-label="Verified KayXchange official account"><i class="bi bi-patch-check-fill"></i> Official</span>@endif</span><span>·</span><time>{{ $reply->created_at->diffForHumans() }}</time></div>
                <div class="kx-reply-body">{{ $reply->body }}</div>
                <div class="kx-reply-footer">
                    <form method="POST" action="{{ route('forum.reply.react', $reply) }}">@csrf<button class="kx-thread-action {{ $replyReacted ? 'active' : '' }}" type="submit"><i class="bi bi-hand-thumbs-up"></i>Helpful · {{ $reply->reactions_count }}</button></form>
                    <div class="kx-report"><details><summary><i class="bi bi-flag me-1"></i>Report</summary><form class="kx-report-form" method="POST" action="{{ route('forum.report') }}">@csrf<input type="hidden" name="reply_id" value="{{ $reply->id }}"><label class="small mb-1">Why are you reporting this?</label><select name="reason" required><option value="">Choose a reason</option><option value="scam">Scam or suspicious offer</option><option value="spam">Spam or promotion</option><option value="abuse">Harassment or abuse</option><option value="misinformation">Misleading information</option><option value="other">Other concern</option></select><textarea name="details" rows="2" maxlength="1000" placeholder="Optional context"></textarea><button class="btn btn-sm btn-warning w-100" type="submit">Send report</button></form></details></div>
                </div>
            </article>
        @empty
            <div class="kx-reply-card text-secondary small">No replies yet. Add the first helpful response below.</div>
        @endforelse
        @if($replies->hasPages())<div class="py-2">{{ $replies->fragment('replies')->links() }}</div>@endif
    </section>

    @if(!$topic->is_locked)
        <form class="kx-reply-form" id="replyForm" method="POST" action="{{ route('forum.reply', $topic) }}">
            @csrf
            <h2 class="h6 fw-bold mb-2">Add a reply</h2>
            <p class="small text-secondary">Share useful context. Keep account credentials and private wallet information to yourself.</p>
            @error('body')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
            <textarea name="body" minlength="2" maxlength="8000" required placeholder="Write a thoughtful response…">{{ old('body') }}</textarea>
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mt-2"><span class="kx-thread-disclaimer mb-0">Posts are community opinions and are not financial advice.</span><button class="btn btn-success btn-sm px-3 fw-bold" type="submit"><i class="bi bi-send me-1"></i>Post reply</button></div>
        </form>
    @else
        <div class="kx-thread-locked mt-3"><i class="bi bi-lock-fill me-2"></i>This discussion has been closed by a moderator.</div>
    @endif
</main>
@endsection
