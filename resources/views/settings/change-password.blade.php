@extends('layout')

@section('content')
<div class="container my-5">
    <h4>Change Password</h4>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('change.password') }}">
        @csrf
        <div class="form-group mb-3">
            <label for="current_password">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label for="new_password_confirmation">Confirm New Password</label>
            <input type="password" name="new_password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Change Password</button>
    </form>
</div>
@endsection
