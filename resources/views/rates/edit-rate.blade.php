@extends('adminnavlayout')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-white">
            <h2 class="mb-0">Edit Rate for {{ $rate->coin }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('rates.update-rate', $rate->id) }}" method="POST">
                @csrf
                @method('POST')

                <div class="mb-3">
                    <label for="buy_rate" class="form-label">Buy Rate</label>
                    <input type="number" step="0.01" name="buy_rate" value="{{ old('buy_rate', $rate->buy_rate) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="sell_rate" class="form-label">Sell Rate</label>
                    <input type="number" step="0.01" name="sell_rate" value="{{ old('sell_rate', $rate->sell_rate) }}" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">Update Rate</button>
            </form>
        </div>
    </div>
</div>
@endsection
