@extends('layout')

@section('content')
<div class="container text-center">
    <div class="card bg-white shadow-sm p-3">
        <h5>Welcome, {{ Auth::user()->name }}</h5>
        <p>Balance: ${{ Auth::user()->balance }}</p>
    </div>
   <div class="container">
   <div class="d-flex justify-content-center mb-5 gap-3 mt-4">
        <button class="btn crypto-btn" onclick="navigateTo('buy')">Buy Crypto</button>
        <button class="btn crypto-btn1" onclick="navigateTo('sell')">Sell Crypto</button>
    </div>
    <div class="d-flex justify-content-center mb-5 gap-3 mt-4">
        <button class="btn crypto-btn" onclick="navigateTo('buy')">Airtime</button>
        <button class="btn crypto-btn1" onclick="navigateTo('sell')">Data </button>
    </div>
   </div>
    <div class="col-md m-auto-6 mt-3">
        <div class="card bg-white shadow-sm p-3">
            <h5>Current Crypto Prices</h5>
            <div id="prices-container">
                <p>Loading...</p>
            </div>
        </div>
    </div>
</div>

<script>
    function navigateTo(page) {
        document.getElementById("loader").style.display = "block";
        window.location.href = `/${page}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetch('https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=bitcoin,ethereum,tether,litecoin,binancecoin')
            .then(response => response.json())
            .then(data => {
                let pricesContainer = document.getElementById('prices-container');
                pricesContainer.innerHTML = '';
                data.forEach(coin => {
                    pricesContainer.innerHTML += `<p><img src="${coin.image}" width="20"> ${coin.name}: $${coin.current_price}</p>`;
                });
            })
            .catch(() => {
                document.getElementById('prices-container').innerHTML = '<p>Error loading prices</p>';
            });
    });
</script>

@endsection
