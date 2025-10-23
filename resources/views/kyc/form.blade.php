@extends('layout')

@section('content')
<style>
    body {
        background-color: #1a1a1a;
        color: #ffffff;
    }
    .kyc-form-container {
        background-color: #2c2c2c;
        border-radius: 15px;
        padding: 2rem;
        max-width: 500px;
        margin: 2rem auto;
    }
    .text-green {
        color: #28a745 !important;
    }
    .form-control {
        background-color: #3a3a3a;
        color: #ffffff;
        border-color: #4a4a4a;
    }
    .form-control-file {
        color: #ffffff;
    }
    .btn-green {
        background-color: #28a745;
        color: #ffffff;
        transition: transform 0.3s ease;
    }
    .btn-green:hover {
        transform: scale(1.05);
    }
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }
</style>

<div class="kyc-form-container">
    <h2 class="text-center text-green mb-4">KYC Verification</h2>
    <p class="text-center mb-4">Please upload the required documents to verify your identity before you can buy crypto.</p>

    <form method="POST" action="{{ route('kyc.submit') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label for="id_document" class="block text-sm font-medium">Government-Issued ID (PDF, JPEG, PNG)</label>
            <input type="file" name="id_document" id="id_document" class="form-control-file w-full mt-1" required>
            @error('id_document')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="selfie" class="block text-sm font-medium">Selfie with ID (JPEG, PNG)</label>
            <input type="file" name="selfie" id="selfie" class="form-control-file w-full mt-1" required>
            @error('selfie')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="w-full btn-green p-3 rounded font-semibold">Submit KYC</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

<script>
    @if (session('success'))
        Toastify({
            text: "{{ session('success') }}",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "#28a745",
        }).showToast();
    @endif

    @if (session('error'))
        Toastify({
            text: "{{ session('error') }}",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "#dc3545",
        }).showToast();
    @endif
</script>
@endsection