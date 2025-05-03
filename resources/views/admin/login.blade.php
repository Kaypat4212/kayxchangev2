@extends('layout')

@section('content')
<style>

    .login-container {
        background-color: #ffffff;
        border: 2px solid #28a745;
        border-radius: 15px;
        padding: 40px;
        width: 100%;
        max-width: 400px;
        margin-top: 150px;
        margin-bottom: 200px;
        box-shadow: 0 0 10px rgba(0, 128, 0, 0.2);
        text-align: center;
        
    }

    .login-container h2 {
        font-size: 24px;
        color: #28a745;
        margin-bottom: 20px;
        animation: slideIn 2s infinite alternate;
    }

    @keyframes slideIn {
        0% {
            transform: translateX(-10px);
        }
        100% {
            transform: translateX(10px);
        }
    }

    .login-container label {
        display: block;
        text-align: left;
        margin-bottom: 5px;
        color: #28a745;
        font-weight: bold;
    }

    .login-container input[type="email"],
    .login-container input[type="password"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
        box-sizing: border-box;
    }

    .login-container button {
        width: 100%;
        padding: 10px;
        background-color: #28a745;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
    }

    .login-container button:hover {
        background-color: #218838;
    }

    .error-message {
        color: red;
        margin-bottom: 15px;
    }
</style>

<div class="login-container">
    <h2>Admin Login</h2>
    @if ($errors->any())
        <div class="error-message">
            <strong>{{ $errors->first() }}</strong>
        </div>
    @endif
    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Login</button>
    </form>
</div>
@endsection
