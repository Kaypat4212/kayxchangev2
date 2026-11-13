@extends('adminnavlayout')

@section('content')
<div class="container-fluid py-4 px-3 px-lg-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h2 class="mb-1">User onboarding editor</h2><p class="text-muted mb-0">Edit the welcome, security PIN, bank details, and completion copy shown to new users.</p></div>
        <a href="{{ route('onboard', ['preview' => 1]) }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-box-arrow-up-right me-1"></i>Preview onboarding</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.onboarding-content.update') }}" class="card border-0 shadow-sm">
        @csrf @method('PUT')
        <div class="card-body p-3 p-lg-4">
            <div class="row g-3">
                @foreach($content as $item)
                    <div class="col-12 {{ str_contains($item->key, 'subtitle') || str_contains($item->key, 'description') || str_contains($item->key, 'feature_') ? '' : 'col-lg-6' }}">
                        <label class="form-label fw-semibold" for="content_{{ $item->key }}">{{ $item->label }}</label>
                        @if(str_contains($item->key, 'subtitle') || str_contains($item->key, 'description') || str_contains($item->key, 'feature_'))
                            <textarea id="content_{{ $item->key }}" name="content[{{ $item->key }}]" rows="2" maxlength="500" class="form-control">{{ old('content.'.$item->key, $item->value) }}</textarea>
                        @else
                            <input id="content_{{ $item->key }}" name="content[{{ $item->key }}]" value="{{ old('content.'.$item->key, $item->value) }}" maxlength="500" class="form-control">
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer bg-transparent d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <small class="text-muted">Changes appear in the onboarding flow for new or unfinished accounts.</small>
            <button type="submit" class="btn btn-primary"><i class="bi bi-floppy me-1"></i>Save onboarding page</button>
        </div>
    </form>
</div>
@endsection
