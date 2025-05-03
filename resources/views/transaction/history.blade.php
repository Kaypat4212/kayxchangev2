@extends('layout')

@section('content')
<div class="container my-5">
    <h3 class="text-success">Your Transactions</h3>

    <h5 class="mt-4">Sell Trades</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Coin</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Method</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sellTrades as $trade)
                <tr>
                    <td>{{ $trade->coin }}</td>
                    <td>${{ $trade->amount }}</td>
                    <td>{{ $trade->status }}</td>
                    <td>{{ $trade->payment_method }}</td>
                    <td>{{ $trade->created_at->format('d M, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">No sell trades found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h5 class="mt-4">Buy Trades</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Coin</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Method</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($buyTrades as $trade)
                <tr>
                    <td>{{ $trade->coin }}</td>
                    <td>${{ $trade->amount }}</td>
                    <td>{{ $trade->status }}</td>
                    <td>{{ $trade->payment_method }}</td>
                    <td>{{ $trade->created_at->format('d M, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">No buy trades found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
