@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto p-6 bg-white shadow rounded-xl mt-10">
    <h2 class="text-2xl font-bold mb-6 text-center">Step 3: Select Payout Method</h2>

    <form action="{{ route('sell.finalize') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block font-semibold mb-1">Choose a Payout Method</label>
            <select name="payout_method" id="payout_method" class="w-full border rounded p-2" required>
                <option value="">-- Select Method --</option>
                <option value="default_bank">My Default Bank</option>
                <option value="external_bank">Another Bank</option>
                <option value="wallet_balance">Add to Wallet Balance</option>
            </select>
        </div>

        <div id="external-fields" class="hidden">
            <div class="mb-4">
                <label class="block font-semibold mb-1">Bank Name</label>
                <input type="text" name="alt_bank" class="w-full border rounded p-2">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Account Number</label>
                <input type="text" name="alt_account_number" class="w-full border rounded p-2">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Account Name</label>
                <input type="text" name="alt_account_name" class="w-full border rounded p-2">
            </div>
        </div>

        <button type="submit" class="w-full bg-green-600 text-white p-3 rounded hover:bg-green-700">
            Submit Trade
        </button>
    </form>
</div>

<script>
    const methodSelect = document.getElementById('payout_method');
    const externalFields = document.getElementById('external-fields');

    methodSelect.addEventListener('change', function () {
        externalFields.classList.toggle('hidden', this.value !== 'external_bank');
    });
</script>
@endsection
