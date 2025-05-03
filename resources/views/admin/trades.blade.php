@extends('adminnavlayout')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Trades Section</h2>

    <ul class="nav nav-tabs" id="tradesTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="buy-trades-tab" data-bs-toggle="tab" href="#buy-trades" role="tab">Buy Trades</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="sell-trades-tab" data-bs-toggle="tab" href="#sell-trades" role="tab">Sell Trades</a>
        </li>
    </ul>

    <div class="tab-content mt-4">
        <!-- Buy Trades -->
        <div class="tab-pane fade show active" id="buy-trades" role="tabpanel">
            <div class="table-responsive">
                <table id="buyTradesTable" class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>You'll Pay ($)</th>
                            <th>Amount Sent (₦)</th>
                            <th>Wallet Address</th>
                            <th>Proof of Payment</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                            <th>Update Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($buyTrades as $trade)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>${{ $trade->usd_amount }}</td>
                            <td>₦{{ number_format($trade->naira_amount) }}</td>
                            <td>{{ $trade->wallet_address ?? '-' }}</td>
                            <td>
                                @if($trade->payment_proof)
                                    <a href="{{ asset('storage/' . $trade->payment_proof) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $trade->payment_proof) }}" alt="Proof" style="width: 70px; border-radius: 5px;">
                                    </a>
                                @else
                                    <span class="text-muted">No proof</span>
                                @endif
                            </td>
                            <td>{{ $trade->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                @if($trade->status == 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($trade->status == 'successful')
                                    <span class="badge bg-success">Successful</span>
                                @elseif($trade->status == 'canceled')
                                    <span class="badge bg-danger">Canceled</span>
                                @else
                                    <span class="badge bg-secondary">Unknown</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('buy.updateStatus', $trade->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                        <option value="pending" {{ $trade->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="successful" {{ $trade->status == 'successful' ? 'selected' : '' }}>Successful</option>
                                        <option value="canceled" {{ $trade->status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($buyTrades, 'links'))
                <div class="mt-3">{{ $buyTrades->links() }}</div>
            @endif
        </div>

        <!-- Sell Trades -->
        <div class="tab-pane fade" id="sell-trades" role="tabpanel">
            <div class="table-responsive">
                <table id="sellTradesTable" class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Coin</th>
                            <th>Amount (₦)</th>
                            <th>Proof</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                            <th>Update Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sellTrades as $sell)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $sell->name }}</td>
                            <td>{{ strtoupper($sell->coin) }}</td>
                            <td>₦{{ number_format($sell->amount) }}</td>
                            <td>
                                @if($sell->proof)
                                    <a href="{{ asset('storage/' . $sell->proof) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $sell->proof) }}" alt="Proof" style="width: 70px; border-radius: 5px;">
                                    </a>
                                @else
                                    <span class="text-muted">No proof</span>
                                @endif
                            </td>
                            <td>{{ $sell->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                @if($sell->status == 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($sell->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($sell->status == 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @else
                                    <span class="badge bg-secondary">Unknown</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.sells.updateStatus', $sell->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                        <option value="pending" {{ $sell->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="completed" {{ $sell->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $sell->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($sellTrades, 'links'))
                <div class="mt-3">{{ $sellTrades->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- DataTables CDN -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#buyTradesTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[5, 'desc']],
        });

        $('#sellTradesTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[5, 'desc']],
        });
    });
</script>
@endsection
