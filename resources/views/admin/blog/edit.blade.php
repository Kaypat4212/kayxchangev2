@extends('adminlayout')

@push('styles')
@include('admin.blog._editor-styles')
@endpush

@section('content')

{{-- ── Top bar ── --}}
<div class="editor-topbar d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.blog.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i>Posts
    </a>
    <div class="flex-grow-1">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Post</h5>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="{{ url('/blog/'.$post->slug) }}" target="_blank"
           class="btn btn-sm btn-outline-info rounded-pill px-3">
            <i class="bi bi-eye me-1"></i>Preview
        </a>
        <button type="submit" form="blog-form" name="_action" value="draft"
                class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-floppy me-1"></i>Save Draft
        </button>
        <button type="submit" form="blog-form" name="_action" value="publish"
                class="btn btn-warning btn-sm rounded-pill px-3">
            <i class="bi bi-check-lg me-1"></i>Update Post
        </button>
    </div>
</div>

<form id="blog-form" method="POST" action="{{ route('admin.blog.update', $post) }}">
    @csrf @method('PUT')
    @include('admin.blog._form')
</form>

@endsection

@push('scripts')
@include('admin.blog._editor-scripts')
@endpush
