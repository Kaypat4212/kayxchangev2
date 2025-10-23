@extends('layout')

@section('content')
<div class="container my-5">
    <h3>Settings</h3>

    <style>
        a{
            text-decoration: none;
            color: black;
            font-size: 23px;
        }
    </style>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <ul class="list-group mt-4">
        <li class="list-group-item">
            <a href="{{ route('edit-bank') }}">
                <i class="fas fa-university me-2"></i>
                Edit Bank Account Information
            </a>
        </li>
        <li class="list-group-item">
            <a href="{{ route('change.password.form') }}">
                <i class="fas fa-lock me-2"></i>
                Change Password
            </a>
        </li>
        <li class="list-group-item">
            <a href="{{ route('settings.telegram') }}">
                <i class="fab fa-telegram-plane me-2"></i>
                Telegram Notifications
            </a>
        </li>
    </ul>
</div>
@endsection
