@extends('layout')

@section('content')
<div class="container text-center mt-5">
    <h2 class="text-success">Your Buy Trade Was Successful!</h2>
    <p class="mt-3">Thank you for trading with us. We will process your transaction shortly.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary mt-4">Go to Dashboard</a>
</div>
@endsection
