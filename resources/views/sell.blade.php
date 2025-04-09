@extends('layout')


@section('content')
<div class="container my-5">
    <h3 class="text-center text-success mb-4">Sell Crypto</h3>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success text-center">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="sellForm" action="/sell" method="POST" enctype="multipart/form-data" 
          class="form-glow p-5 bg-gradient rounded-4">
        @csrf

        <!-- Step 1 -->
        <div id="step1">
            <div class="mb-4">
                <label class="form-label custom-label" for="name">Your Name</label>
                <input type="text" id="name" class="form-control glowing-input" value="{{ auth()->user()->name }}" readonly />
            </div>

            <div class="mb-4">
                <label class="form-label custom-label" for="coin">Select Coin</label>
                <select name="coin" id="coin" class="form-control glowing-input" required>
                    <option value="">Choose a coin</option>
                    <option value="BTC">Bitcoin (BTC)</option>
                    <option value="ETH">Ethereum (ETH)</option>
                    <option value="USDT">Tether (USDT)</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label custom-label" for="amount">Amount</label>
                <input 
                    type="text" 
                    name="amount" 
                    id="amount" 
                    class="form-control glowing-input" 
                    required 
                    oninput="validateAmount(this)"
                    placeholder="Enter amount e.g. 0.5 or 100"
                />
                <span id="amountWarning" class="text-warning small d-none">Only numeric values are allowed.</span>
            </div>

            <button type="button" class="btn btn-light w-100 rounded-pill fw-bold shadow" onclick="goToStep2()">Next</button>
        </div>

        <!-- Step 2 -->
        <div id="step2" style="display: none;">
            <div class="mb-4">
                <label class="form-label custom-label">Send Crypto To:</label>
                <p id="walletAddress" class="fw-bold bg-white text-dark p-3 rounded-3 mb-0"></p>
            </div>

            <div class="mb-4">
                <label for="proof" class="form-label custom-label">Upload Payment Proof (screenshot)</label>
                <input type="file" name="proof" id="proof" class="form-control glowing-input" accept="image/*" required />
            </div>

            <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold shadow">Submit</button>
        </div>
    </form>
</div>

<script>
    const walletMap = {
        BTC: 'bc1qexamplebtcaddress',
        ETH: '0xexampleethaddress',
        USDT: 'TXexampleusdtaddress'
    };

    function goToStep2() {
        const coin = document.getElementById('coin').value;
        const amount = document.getElementById('amount').value;

        if (!coin) return alert("Please select the asset you want to sell");
        if (!amount) return alert("Please enter an amount");

        document.getElementById('walletAddress').textContent = walletMap[coin] || "Wallet not found";
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
    }

    function validateAmount(input) {
        const warning = document.getElementById('amountWarning');
        const valid = /^[0-9]*\.?[0-9]*$/.test(input.value);

        if (!valid && input.value !== "") {
            warning.classList.remove('d-none');
            input.classList.add('is-invalid');
        } else {
            warning.classList.add('d-none');
            input.classList.remove('is-invalid');
        }

        input.value = input.value.replace(/[^0-9.]/g, '');
    }
</script>

<style>
    .bg-gradient {
        background: linear-gradient(135deg, #28a745, #218838,rgb(17, 85, 33));
    }

    .form-glow {
        width: 100%;
        max-width: 400px;
        margin: 20px auto;
        box-shadow: 0 0 30px rgba(40, 167, 69, 0.6);
        animation: pulseGlow 3s infinite alternate;
    }

    @keyframes pulseGlow {
        from {
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.4);
        }
        to {
            box-shadow: 0 0 40px rgba(40, 167, 69, 0.8);
        }
    }

    .glowing-input {
        border-radius: 15px;
        border: none;
        padding: 12px;
        transition: all 0.3s ease;
    }

    .glowing-input:focus {
        outline: none;
        box-shadow: 0 0 12px rgba(67, 66, 66, 0.7);
        background-color: #f8fff8;
    }

    .btn {
        transition: all 0.3s ease-in-out;
    }

    .btn:hover {
        transform: scale(1.03);
    }

    #step2 {
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .custom-label {
        color: #000;
        font-weight: 600;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    
</style>
@endsection
