@extends('adminnavlayout')

@section('content')
<div class="container mt-5">
    <h2>User Details</h2>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $user->name }}</h5>
            <p class="card-text"><strong>Email:</strong> {{ $user->email }}</p>
            <p class="card-text"><strong>Balance:</strong> ${{ number_format($user->balance, 2) }}</p>
            <p class="card-text"><strong>Joined:</strong> {{ $user->created_at->format('d M Y') }}</p>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back</a>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>
</div>
@endsection