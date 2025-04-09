@extends('layouts.rates')

@section('content')
<div class="container mt-5">
    <h2 class="text-center">Crypto Rates</h2>
    <table class="table table-bordered text-center mt-3">
        <thead class="table-dark">
            <tr>
                <th>Icon</th>
                <th>Name</th>
                <th>Rate</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><img src="/exchangeratesassetimages/bitcoin.svg" width="25px" alt="Bitcoin"></td>
                <td><b>Bitcoin</b></td>
                <td>1520/$</td>
            </tr>
            <tr>
                <td><img src="/exchangeratesassetimages/ethereum.svg" width="25px" alt="Ethereum"></td>
                <td><b>Ethereum</b></td>
                <td>1520/$</td>
            </tr>
            <tr>
                <td><img src="/exchangeratesassetimages/usdt.svg" width="25px" alt="USDT"></td>
                <td><b>USDT</b></td>
                <td>1520/$</td>
            </tr>
            <!-- Add more cryptos here -->
        </tbody>
    </table>
</div>
@endsection
