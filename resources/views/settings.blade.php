@extends('layout')

@section('content')
<div class="container my-5">
    <h3>Settings</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <ul class="list-group mt-4">
        <li class="list-group-item">
            <a href="{{ route('edit.bank') }}">Edit Bank Account Information</a>
        </li>
        <li class="list-group-item">
            <a href="{{ route('change.password.form') }}">Change Password</a>
        </li>
    </ul>
</div>
@endsection
