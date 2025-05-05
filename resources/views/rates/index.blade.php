<div class="container mt-5">
    <h2 class="mb-4">Current Crypto Rates</h2>

    @if($rates->isEmpty())
        <p class="text-muted">No rates set yet.</p>
    @else
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Coin</th>
                    <th>Buy Rate (₦)</th>
                    <th>Sell Rate (₦)</th>
                    <th>Updated At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rates as $rate)
                    <tr>
                        <td>{{ $rate->coin }}</td>
                        <td>₦{{ number_format($rate->buy_rate, 2) }}</td>
                        <td>₦{{ number_format($rate->sell_rate, 2) }}</td>
                        <td>{{ $rate->updated_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
