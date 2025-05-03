@extends('layout')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center mb-4">
        <div class="col-lg-6 col-md-8">
            <div class="card dashboard-card shadow-sm text-center animate-fade p-4">
                <h5 class="text-success fw-bold mb-2">Welcome, {{ Auth::user()->name }}</h5>
                <p class="fs-5 text-dark">Balance: <span class="text-success">${{ Auth::user()->balance }}</span></p>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-auto d-flex gap-3 flex-wrap justify-content-center">
            <button class="btn btn-success px-4 py-2" onclick="navigateTo('buy')">Buy Crypto</button>
            <button class="btn btn-outline-success px-4 py-2" onclick="navigateTo('sell')">Sell Crypto</button>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card dashboard-card shadow-sm animate-slide-up p-4">
                <h5 class="text-success mb-3 text-center">Current Crypto Prices</h5>
                <div id="prices-container" class="text-center">
                    <p>Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function navigateTo(page) {
        window.location.href = `/${page}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetch('https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=bitcoin,ethereum,tether,litecoin,binancecoin')
            .then(response => response.json())
            .then(data => {
                const pricesContainer = document.getElementById('prices-container');
                pricesContainer.innerHTML = '';
                data.forEach(coin => {
                    pricesContainer.innerHTML += `
                        <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                            <div class="d-flex align-items-center gap-2">
                                <img src="${coin.image}" width="28" alt="${coin.name}" />
                                <strong class="text-dark">${coin.name}</strong>
                            </div>
                            <span class="text-success fw-semibold">$${coin.current_price.toLocaleString()}</span>
                        </div>`;
                });
            })
            .catch(() => {
                document.getElementById('prices-container').innerHTML = '<p class="text-danger">Error loading prices</p>';
            });
    });
</script>
@endsection
