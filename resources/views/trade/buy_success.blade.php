@extends('buylayout')

@section('content')
<div class="container text-center my-5">
    <h3 class="text-success">Buy Trade Placed Successfully</h3>
    <p>Thank you for your transaction. Here are the trade details:</p>
    <p><strong>Coin:</strong> {{ $trade->coin }}</p>
    <p><strong>Amount:</strong> ${{ $trade->amount }}</p>
    <p><strong>Status:</strong> {{ $trade->status }}</p>
    <p><strong>Payment Method:</strong> {{ $trade->payment_method }}</p>
    <a href="{{ route('transaction.history') }}" class="btn btn-success">View Transaction History</a>
</div>
@endsection
