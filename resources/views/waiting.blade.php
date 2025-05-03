<!-- resources/views/waiting.blade.php -->
@extends('layout')

@section('content')
    <div class="container text-center my-5">
        <h3 class="text-success">Processing Your Trade...</h3>
        <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Please wait while we process your transaction.</p>
    </div>
@endsection
