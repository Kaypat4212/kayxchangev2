@extends('layout')

@section('content')
<style>
.kx-forum{--f-bg:#0a120d;--f-card:rgba(255,255,255,.035);--f-line:rgba(255,255,255,.09);--f-muted:#8b9b8f;color:#eaf5ec;padding:22px 0 60px}.kx-forum a{text-decoration:none}.kx-forum-hero{position:relative;overflow:hidden;padding:clamp(22px,4vw,38px);border:1px solid rgba(0,204,0,.18);border-radius:24px;background:radial-gradient(ellipse at 85% 0,rgba(0,204,0,.14),transparent 40%),linear-gradient(135deg,#132019,#0b120d 68%);margin-bottom:22px}.kx-forum-kicker{display:inline-flex;gap:7px;align-items:center;color:#75e987;font-size:.7rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase}.kx-forum-hero h1{font-size:clamp(1.65rem,4vw,2.6rem);letter-spacing:-.045em;font-weight:800;margin:10px 0}.kx-forum-hero p{max-width:660px;color:#a9b9ac;font-size:.9rem;line-height:1.7;margin:0}.kx-forum-hero-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}.kx-forum-btn{display:inline-flex;justify-content:center;align-items:center;gap:8px;padding:11px 16px;border-radius:12px;background:#00c844;color:#041207!important;font-size:.8rem;font-weight:700;border:1px solid transparent;transition:.2s}.kx-forum-btn:hover{transform:translateY(-1px);box-shadow:0 8px 24px #00c8442b}.kx-forum-btn.secondary{color:#dff7e4!important;background:#ffffff08;border-color:var(--f-line)}.kx-forum-stats{display:flex;gap:10px;flex-wrap:wrap;margin-top:24px}.kx-forum-stat{border:1px solid var(--f-line);background:#ffffff06;border-radius:11px;padding:8px 12px;color:#c2d0c5;font-size:.72rem}.kx-forum-stat strong{color:#fff;margin-right:4px}.kx-forum-layout{display:grid;grid-template-columns:250px minmax(0,1fr);gap:20px;align-items:start}.kx-forum-side,.kx-forum-panel{border:1px solid var(--f-line);border-radius:18px;background:var(--f-card);overflow:hidden}.kx-forum-side-head,.kx-forum-panel-head{padding:16px 18px;border-bottom:1px solid var(--f-line);display:flex;justify-content:space-between;align-items:center;gap:12px}.kx-forum-side-head strong,.kx-forum-panel-head strong{font-size:.86rem}.kx-forum-category{display:flex;gap:10px;align-items:center;padding:12px 15px;color:#d4e3d7;border-bottom:1px solid rgba(255,255,255,.045);font-size:.77rem;transition:background .18s}.kx-forum-category:hover,.kx-forum-category.active{background:rgba(0,204,0,.08);color:#a3ffb3}.kx-forum-category i{font-size:1rem}.kx-forum-category small{margin-left:auto;color:var(--f-muted)}.kx-forum-side-note{padding:15px;color:var(--f-muted);font-size:.72rem;line-height:1.6}.kx-forum-toolbar{display:flex;gap:9px;flex-wrap:wrap;padding:14px;border-bottom:1px solid var(--f-line)}.kx-forum-search{display:flex;align-items:center;gap:8px;flex:1;min-width:190px;padding:0 12px;border:1px solid var(--f-line);border-radius:11px;background:#0002}.kx-forum-search input{width:100%;background:transparent;border:0;outline:0;color:#fff;padding:10px 0;font-size:.76rem}.kx-forum-select{border:1px solid var(--f-line);border-radius:11px;background:#101a13;color:#dce9df;padding:9px 11px;font-size:.74rem}.kx-forum-topic{display:grid;grid-template-columns:44px minmax(0,1fr) auto;gap:13px;padding:16px 18px;border-bottom:1px solid rgba(255,255,255,.05);transition:background .18s}.kx-forum-topic:last-child{border-bottom:0}.kx-forum-topic:hover{background:rgba(255,255,255,.025)}.kx-forum-avatar{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:linear-gradient(135deg,#1d3825,#112017);border:1px solid #ffffff12;color:#90f59d;font-weight:800}.kx-forum-topic h2{font-size:.91rem;margin:0 0 6px;line-height:1.4;font-weight:700}.kx-forum-topic h2 a{color:#f2faf3}.kx-forum-topic p{margin:0 0 9px;color:#9bac9f;font-size:.76rem;line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}.kx-forum-meta{display:flex;align-items:center;gap:8px 13px;flex-wrap:wrap;color:#76877b;font-size:.67rem}.kx-forum-meta .category{color:#8df59d}.kx-forum-badges{display:flex;gap:5px;flex-wrap:wrap;margin-bottom:5px}.kx-forum-badge{font-size:.6rem;font-weight:700;padding:3px 7px;border-radius:6px;color:#b8d7be;background:#ffffff0c}.kx-forum-badge.pin{color:#ffe18a;background:#fbbf2415}.kx-forum-badge.blog{color:#9ed5ff;background:#38bdf815}.kx-forum-numbers{display:flex;gap:12px;align-items:center;color:#7f9184;font-size:.68rem;white-space:nowrap}.kx-forum-empty{text-align:center;padding:50px 24px;color:var(--f-muted)}.kx-forum-empty i{font-size:2rem;color:#5d8064}.kx-forum-pagination{padding:15px 18px;border-top:1px solid var(--f-line)}.kx-forum-disclaimer{margin-top:15px;padding:13px 15px;border-radius:12px;background:rgba(251,191,36,.055);border:1px solid rgba(251,191,36,.14);color:#b9aa7f;font-size:.7rem;line-height:1.6}.kx-forum-disclaimer i{color:#fbbf24}@media(max-width:850px){.kx-forum-layout{grid-template-columns:1fr}.kx-forum-side{order:2}.kx-forum-category-list{display:flex;overflow:auto;padding:8px;gap:6px}.kx-forum-category{border:1px solid var(--f-line);border-radius:999px;white-space:nowrap;padding:9px 12px}.kx-forum-category small{display:none}.kx-forum-side-note{display:none}}@media(max-width:560px){.kx-forum{padding-top:12px}.kx-forum-hero{border-radius:18px}.kx-forum-topic{grid-template-columns:36px minmax(0,1fr);gap:10px;padding:14px 12px}.kx-forum-avatar{width:36px;height:36px;border-radius:12px}.kx-forum-numbers{grid-column:2}.kx-forum-topic h2{font-size:.84rem}.kx-forum-stats{gap:6px}.kx-forum-stat{font-size:.66rem;padding:7px 9px}}
body.light-mode .kx-forum{color:#18321e}body.light-mode .kx-forum-hero{background:radial-gradient(ellipse at 85% 0,rgba(0,170,30,.13),transparent 40%),linear-gradient(135deg,#f5fff6,#eaf7ed);border-color:#cde9d2}body.light-mode .kx-forum-hero p,body.light-mode .kx-forum-topic p{color:#596b5e}body.light-mode .kx-forum-stat strong,body.light-mode .kx-forum-topic h2 a{color:#17331d}body.light-mode .kx-forum-side,body.light-mode .kx-forum-panel{background:#fff;border-color:#dfe9e0}body.light-mode .kx-forum-side-head,body.light-mode .kx-forum-panel-head,body.light-mode .kx-forum-topic{border-color:#edf1ed}body.light-mode .kx-forum-category{color:#344a39;border-color:#edf1ed}body.light-mode .kx-forum-search input{color:#1c3221}body.light-mode .kx-forum-select{background:#fff;color:#24412a;border-color:#dfe9e0}body.light-mode .kx-forum-topic:hover{background:#f8fcf8}
</style>

<main class="kx-forum container-xl">
    <header class="kx-forum-hero">
        <span class="kx-forum-kicker"><i class="bi bi-stars"></i> KayXchange Community</span>
        <h1>Learn together. Trade thoughtfully.</h1>
        <p>Ask questions, share research and discuss crypto news with other members. Keep ideas evidence-based, respectful and safe.</p>
        <div class="kx-forum-hero-actions">
            <a href="{{ route('forum.create') }}" class="kx-forum-btn"><i class="bi bi-plus-lg"></i>Start a discussion</a>
            <a href="{{ route('blog.index') }}" class="kx-forum-btn secondary"><i class="bi bi-journal-richtext"></i>Read blog news</a>
        </div>
        <div class="kx-forum-stats">
            <span class="kx-forum-stat"><strong>{{ number_format($stats['members']) }}</strong> members</span>
            <span class="kx-forum-stat"><strong>{{ number_format($stats['topics']) }}</strong> discussions</span>
            <span class="kx-forum-stat"><strong>{{ number_format($stats['replies']) }}</strong> replies</span>
        </div>
    </header>

    @if(session('success') || session('status'))<div class="alert alert-success py-2">{{ session('success') ?? session('status') }}</div>@endif
    @if(session('error'))<div class="alert alert-warning py-2">{{ session('error') }}</div>@endif

    <div class="kx-forum-layout">
        <aside class="kx-forum-side">
            <div class="kx-forum-side-head"><strong><i class="bi bi-grid me-2 text-success"></i>Browse topics</strong><a href="{{ route('blog.index') }}" class="small text-success">Blog</a></div>
            <nav class="kx-forum-category-list" aria-label="Forum categories">
                <a class="kx-forum-category {{ !$category ? 'active' : '' }}" href="{{ route('forum.index') }}"><i class="bi bi-grid-fill text-success"></i>All discussions<small>{{ number_format($stats['topics']) }}</small></a>
                @foreach($categories as $item)
                    <a class="kx-forum-category {{ $category?->id === $item->id ? 'active' : '' }}" href="{{ route('forum.index', ['category' => $item->slug]) }}"><i class="bi {{ $item->icon }}" style="color:{{ $item->color }}"></i>{{ $item->name }}<small>{{ number_format($item->visible_topics_count) }}</small></a>
                @endforeach
            </nav>
            <div class="kx-forum-side-note"><i class="bi bi-shield-check text-success me-1"></i>Never share passwords, PINs, recovery phrases or private keys. Report suspicious posts to moderators.</div>
        </aside>

        <section class="kx-forum-panel">
            <div class="kx-forum-panel-head"><strong>{{ $category?->name ?? 'Community discussions' }}</strong><span class="text-secondary small">{{ $sort === 'trending' ? 'Trending' : 'Latest activity' }}</span></div>
            <form class="kx-forum-toolbar" method="GET" action="{{ route('forum.index') }}">
                @if($category)<input type="hidden" name="category" value="{{ $category->slug }}">@endif
                <label class="kx-forum-search"><i class="bi bi-search text-secondary"></i><input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Search discussions" aria-label="Search discussions"></label>
                <select class="kx-forum-select" name="sort" aria-label="Sort discussions"><option value="recent" @selected($sort === 'recent')>Latest</option><option value="trending" @selected($sort === 'trending')>Trending</option></select>
                <button class="kx-forum-btn" type="submit" style="padding:9px 13px">Filter</button>
            </form>

            @forelse($topics as $topic)
                <article class="kx-forum-topic">
                    <div class="kx-forum-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($topic->user->name ?? 'K', 0, 1)) }}</div>
                    <div>
                        <div class="kx-forum-badges">
                            @if($topic->is_pinned)<span class="kx-forum-badge pin"><i class="bi bi-pin-angle-fill me-1"></i>Pinned</span>@endif
                            @if($topic->is_locked)<span class="kx-forum-badge"><i class="bi bi-lock-fill me-1"></i>Closed</span>@endif
                            @if($topic->blogPost)<span class="kx-forum-badge blog"><i class="bi bi-journal-text me-1"></i>Blog discussion</span>@endif
                        </div>
                        <h2><a href="{{ route('forum.show', $topic->slug) }}">{{ $topic->title }}</a></h2>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($topic->body), 190) }}</p>
                        <div class="kx-forum-meta"><span class="category"><i class="bi {{ $topic->category->icon }} me-1"></i>{{ $topic->category->name }}</span><span>by {{ $topic->user->name ?? 'Member' }}@if($topic->user?->is_admin) <span class="text-info" title="Verified KayXchange official account" aria-label="Verified KayXchange official account"><i class="bi bi-patch-check-fill"></i> Official</span>@endif</span><span>{{ $topic->last_activity_at?->diffForHumans() ?? $topic->created_at->diffForHumans() }}</span>@if($topic->blogPost)<a href="{{ route('blog.show', $topic->blogPost->slug) }}" class="text-info">Read linked article</a>@endif</div>
                    </div>
                    <div class="kx-forum-numbers"><span title="Replies"><i class="bi bi-chat-left-text me-1"></i>{{ $topic->visible_replies_count }}</span><span title="Views"><i class="bi bi-eye me-1"></i>{{ number_format($topic->views_count) }}</span></div>
                </article>
            @empty
                <div class="kx-forum-empty"><i class="bi bi-chat-square-dots d-block mb-3"></i><strong>No discussions yet</strong><p class="small mb-3 mt-2">Start a useful conversation or browse the latest crypto articles.</p><a href="{{ route('forum.create', $category ? ['category' => $category->id] : []) }}" class="kx-forum-btn">Start the first discussion</a></div>
            @endforelse

            @if($topics->hasPages())<div class="kx-forum-pagination">{{ $topics->links() }}</div>@endif
        </section>
    </div>
    <div class="kx-forum-disclaimer"><i class="bi bi-exclamation-triangle-fill me-1"></i>Community posts are member opinions, not financial advice. Crypto is volatile. Never trust guaranteed-return claims or send funds to someone based on a forum post.</div>
</main>
@endsection
