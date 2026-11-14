@extends('layout')

@section('content')
<style>
.kx-forum-form{max-width:880px;margin:0 auto;padding:26px 0 60px;color:#edf7ef}.kx-forum-form-shell{border:1px solid rgba(255,255,255,.1);border-radius:22px;background:linear-gradient(145deg,#111c15,#0a110d);overflow:hidden;box-shadow:0 22px 65px rgba(0,0,0,.24)}.kx-forum-form-head{padding:25px;border-bottom:1px solid rgba(255,255,255,.08);background:radial-gradient(ellipse at 90% 0,rgba(0,204,0,.1),transparent 48%)}.kx-forum-form-head a{color:#75e987;text-decoration:none;font-size:.75rem}.kx-forum-form-head h1{font-size:1.55rem;font-weight:800;margin:12px 0 6px;letter-spacing:-.04em}.kx-forum-form-head p{font-size:.82rem;color:#9eafa2;margin:0}.kx-forum-form-body{padding:25px}.kx-forum-label{display:block;margin-bottom:7px;font-size:.75rem;font-weight:700;color:#cde3d1}.kx-forum-input,.kx-forum-textarea,.kx-forum-select{width:100%;padding:12px 14px;border:1px solid rgba(255,255,255,.12);border-radius:12px;background:#0b140e;color:#eff8f0;outline:0;font:500 .82rem 'Poppins',sans-serif;transition:border .2s,box-shadow .2s}.kx-forum-input:focus,.kx-forum-textarea:focus,.kx-forum-select:focus{border-color:#00c844;box-shadow:0 0 0 3px rgba(0,200,68,.1)}.kx-forum-textarea{min-height:230px;resize:vertical;line-height:1.7}.kx-forum-help{display:flex;justify-content:space-between;gap:10px;margin-top:6px;color:#718276;font-size:.67rem}.kx-forum-note{padding:13px 15px;border:1px solid rgba(251,191,36,.16);border-radius:12px;background:rgba(251,191,36,.055);color:#c3b783;font-size:.72rem;line-height:1.6}.kx-forum-note i{color:#fbbf24}.kx-forum-blog-link{display:flex;align-items:center;gap:11px;padding:12px;border-radius:12px;background:rgba(56,189,248,.07);border:1px solid rgba(56,189,248,.17);color:#cceaff;font-size:.75rem}.kx-forum-blog-link i{font-size:1.1rem;color:#5ecbff}@media(max-width:600px){.kx-forum-form{padding-top:12px}.kx-forum-form-head,.kx-forum-form-body{padding:19px}.kx-forum-form-head h1{font-size:1.3rem}}
body.light-mode .kx-forum-form{color:#17321d}body.light-mode .kx-forum-form-shell{background:#fff;border-color:#dce9de}body.light-mode .kx-forum-form-head{border-color:#edf1ed}body.light-mode .kx-forum-form-head p{color:#647267}body.light-mode .kx-forum-label{color:#304d35}body.light-mode .kx-forum-input,body.light-mode .kx-forum-textarea,body.light-mode .kx-forum-select{background:#f8fcf8;color:#16341d;border-color:#dce8de}
</style>
<main class="kx-forum-form container-xl">
    <div class="kx-forum-form-shell">
        <header class="kx-forum-form-head"><a href="{{ route('forum.index') }}"><i class="bi bi-arrow-left me-1"></i>Back to community</a><h1>Start a discussion</h1><p>Good questions and clear context help the community give better answers.</p></header>
        <div class="kx-forum-form-body">
            @if($errors->any())<div class="alert alert-danger"><strong>Please review your post.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="POST" action="{{ route('forum.store') }}">
                @csrf
                @if($blogPost)
                    <input type="hidden" name="blog_post_id" value="{{ $blogPost->id }}">
                    <div class="kx-forum-blog-link mb-4"><i class="bi bi-journal-bookmark-fill"></i><span><strong>Linked blog article</strong><br>{{ $blogPost->title }}</span><a class="ms-auto text-info" href="{{ route('blog.show', $blogPost->slug) }}" target="_blank" rel="noopener">Preview</a></div>
                @endif
                <div class="mb-3"><label class="kx-forum-label" for="forumCategory">Category</label><select class="kx-forum-select" id="forumCategory" name="category_id" required><option value="">Choose a category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)old('category_id', $selectedCategoryId ?? '') === (string)$category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div class="mb-3"><label class="kx-forum-label" for="forumTitle">Discussion title</label><input class="kx-forum-input" id="forumTitle" name="title" value="{{ old('title') }}" maxlength="180" minlength="8" required placeholder="Example: What should beginners know before using a crypto wallet?"><div class="kx-forum-help"><span>Be specific and avoid promotional headlines.</span><span id="forumTitleCount">0 / 180</span></div></div>
                <div class="mb-4"><label class="kx-forum-label" for="forumBody">Your post</label><textarea class="kx-forum-textarea" id="forumBody" name="body" minlength="20" maxlength="12000" required placeholder="Share the details, sources or context that will help others understand your question or idea.">{{ old('body') }}</textarea><div class="kx-forum-help"><span>Plain text only. Never include passwords, PINs, recovery phrases or private keys.</span><span id="forumBodyCount">0 / 12000</span></div></div>
                <div class="kx-forum-note mb-4"><i class="bi bi-shield-exclamation me-1"></i>Crypto discussions are for education and community exchange, not personalized financial advice. Do not advertise guaranteed returns, request direct payments or share suspicious links. Posts can be reported to moderators.</div>
                <div class="d-flex flex-wrap gap-2 justify-content-end"><a class="btn btn-outline-secondary rounded-3" href="{{ route('forum.index') }}">Cancel</a><button class="btn btn-success rounded-3 px-4 fw-bold" type="submit"><i class="bi bi-send-fill me-2"></i>Publish discussion</button></div>
            </form>
        </div>
    </div>
</main>
<script>
(function(){const title=document.getElementById('forumTitle'),body=document.getElementById('forumBody');function count(input,target,max){const el=document.getElementById(target);if(el)el.textContent=(input.value||'').length+' / '+max;}title?.addEventListener('input',()=>count(title,'forumTitleCount',180));body?.addEventListener('input',()=>count(body,'forumBodyCount',12000));if(title)count(title,'forumTitleCount',180);if(body)count(body,'forumBodyCount',12000);})();
</script>
@endsection
