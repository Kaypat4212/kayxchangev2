@extends('adminlayout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-journal-richtext me-2 text-warning"></i>Blog Posts</h2>
        <small class="text-muted">Manage educational articles for your users</small>
    </div>
    <a href="{{ route('admin.blog.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>New Post
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($posts->count())
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th style="width:170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posts as $post)
                    <tr>
                        <td>
                            <div class="fw-semibold" style="max-width:340px;">{{ $post->title }}</div>
                            @if($post->excerpt)
                                <small class="text-muted" style="font-size:.76rem;">{{ Str::limit($post->excerpt, 80) }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary">{{ $post->category }}</span></td>
                        <td>
                            @if($post->is_published)
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-warning text-dark">Draft</span>
                            @endif
                        </td>
                        <td style="font-size:.82rem;white-space:nowrap;">
                            {{ $post->published_at ? $post->published_at->format('M d, Y') : '—' }}
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ url('/blog/'.$post->slug) }}" target="_blank"
                                   class="btn btn-sm btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.blog.edit', $post) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.blog.toggle', $post) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $post->is_published ? 'warning' : 'success' }}"
                                            title="{{ $post->is_published ? 'Unpublish' : 'Publish' }}">
                                        <i class="bi bi-{{ $post->is_published ? 'eye-slash' : 'check-circle' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.blog.destroy', $post) }}"
                                      onsubmit="return confirm('Delete this post permanently?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $posts->links() }}</div>
        @else
            <div class="text-center text-muted py-5">
                <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-25"></i>
                No posts yet. <a href="{{ route('admin.blog.create') }}">Create your first post</a>.
            </div>
        @endif
    </div>
</div>
@endsection
