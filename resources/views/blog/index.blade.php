@extends('selllayout')

@push('styles')
<style>
:root {
    --blog-green: #00cc00;
    --blog-dark: #070d07;
    --blog-card: #0f1a0f;
    --blog-card2: #162016;
    --blog-border: rgba(0,204,0,0.12);
    --blog-muted: #7a9a7a;
    --blog-text: #e0ece0;
}

.bl-hero {
    background: linear-gradient(135deg, #060e06 0%, #0a1f0a 50%, #06120a 100%);
    border-bottom: 1px solid var(--blog-border);
    padding: 2.5rem 1rem 2rem;
    text-align: center;
    margin-bottom: 2rem;
}
.bl-hero h1 { font-size: 2rem; font-weight: 800; color: #fff; margin: 0 0 .4rem; }
.bl-hero p  { color: var(--blog-muted); font-size: .9rem; max-width: 480px; margin: 0 auto; }

/* Category filter pills */
.bl-cats { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.75rem; }
.bl-cat-pill {
    padding: .35rem .85rem;
    border-radius: 20px;
    border: 1px solid var(--blog-border);
    color: var(--blog-muted);
    font-size: .78rem;
    font-weight: 600;
    text-decoration: none;
    transition: all .2s;
    background: var(--blog-card);
}
.bl-cat-pill:hover, .bl-cat-pill.active {
    background: rgba(0,204,0,.1);
    border-color: var(--blog-green);
    color: var(--blog-green);
}

/* Post cards grid */
.bl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem; }
.bl-grid-carousel { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }

@media (max-width: 768px) {
    .bl-grid-carousel { grid-template-columns: 1fr; }
}
@media (min-width: 769px) and (max-width: 992px) {
    .bl-grid-carousel { grid-template-columns: repeat(2, 1fr); }
}

.bl-card {
    background: var(--blog-card);
    border: 1px solid var(--blog-border);
    border-radius: 16px;
    overflow: hidden;
    text-decoration: none !important;
    color: inherit;
    display: flex;
    flex-direction: column;
    transition: border-color .2s, transform .2s;
}
.bl-card:hover { border-color: rgba(0,204,0,.35); transform: translateY(-2px); }

.bl-card-cover {
    height: 160px;
    background: linear-gradient(135deg, #0a1f0a, #0d2a12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: rgba(0,204,0,.25);
    flex-shrink: 0;
}
.bl-card-cover img { width: 100%; height: 100%; object-fit: cover; }

.bl-card-body { padding: 1rem 1.1rem 1.25rem; flex: 1; display: flex; flex-direction: column; }
.bl-card-cat {
    display: inline-block;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--blog-green);
    background: rgba(0,204,0,.1);
    border-radius: 4px;
    padding: .18rem .55rem;
    margin-bottom: .6rem;
}
.bl-card-title { font-size: .95rem; font-weight: 700; color: #fff; margin-bottom: .4rem; line-height: 1.4; }
.bl-card-excerpt { font-size: .78rem; color: var(--blog-muted); line-height: 1.55; flex: 1; }
.bl-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: .85rem;
    font-size: .72rem;
    color: var(--blog-muted);
}

/* Empty state */
.bl-empty { text-align: center; padding: 4rem 1rem; color: var(--blog-muted); }
.bl-empty i { font-size: 3rem; color: rgba(0,204,0,.2); display: block; margin-bottom: .75rem; }
</style>
@endpush

@section('content')
<div class="bl-hero">
    <h1><i class="bi bi-journal-richtext me-2" style="color:var(--blog-green);"></i>Blog &amp; Education</h1>
    <p>Learn about crypto trading, market insights, and tips to help you make smarter decisions.</p>
</div>

<div class="container-fluid px-3 pb-5">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11">

            {{-- Category filter --}}
            @if($categories->count())
            <div class="bl-cats">
                <a href="{{ url('/blog') }}" class="bl-cat-pill @if(!$category) active @endif">All</a>
                @foreach($categories as $cat)
                    <a href="{{ url('/blog?category='.urlencode($cat)) }}"
                       class="bl-cat-pill @if($category === $cat) active @endif">{{ $cat }}</a>
                @endforeach
            </div>
            @endif

            @if($posts->count())
                <div id="blogCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @php
                            $chunks = $posts->chunk(3); // 3 posts per slide
                        @endphp
                        @foreach($chunks as $index => $chunk)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="bl-grid-carousel">
                                @foreach($chunk as $post)
                                <a href="{{ url('/blog/'.$post->slug) }}" class="bl-card">
                                    <div class="bl-card-cover">
                                        @if($post->cover_image)
                                            <img src="{{ asset('storage/'.$post->cover_image) }}" alt="{{ $post->title }}">
                                        @else
                                            <i class="bi bi-journal-text"></i>
                                        @endif
                                    </div>
                                    <div class="bl-card-body">
                                        <span class="bl-card-cat">{{ $post->category }}</span>
                                        <div class="bl-card-title">{{ $post->title }}</div>
                                        @if($post->excerpt)
                                            <div class="bl-card-excerpt">{{ $post->excerpt }}</div>
                                        @endif
                                        <div class="bl-card-footer">
                                            <span><i class="bi bi-calendar3 me-1"></i>{{ $post->published_at?->format('M d, Y') }}</span>
                                            <span><i class="bi bi-clock me-1"></i>{{ $post->readingTime() }} min read</span>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#blogCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#blogCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>

                <div class="mt-4">{{ $posts->links() }}</div>
            @else
                <div class="bl-empty">
                    <i class="bi bi-journal-x"></i>
                    <p>No posts yet. Check back soon!</p>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
