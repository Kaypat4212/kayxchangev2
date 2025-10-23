@extends('layout')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    :root {
        --dark-bg: #1a1a1a;
        --dark-card-bg: #2c2c2c;
        --dark-text: #00871b;
        --dark-text-muted: #b0b0b0;
        --dark-gradient-bg: linear-gradient(135deg, #2c2c2c, #3a3a3a);
        --dark-border: #4a4a4a;
        --dark-table-hover: #3a3a3a;
        --light-bg: #f5f5f5;
        --light-card-bg: #ffffff;
        --light-text: #1a1a1a;
        --light-text-muted: #6c757d;
        --light-gradient-bg: linear-gradient(135deg, #ffffff, #e9ecef);
        --light-border: #dee2e6;
        --light-table-hover: #e9ecef;
        --primary-green: #28a745;
        --primary-red: #dc3545;
        --glow-color: rgba(40, 167, 69, 0.5);
        --glow-color-red: rgba(220, 53, 69, 0.5);
        --transition: all 0.3s ease;
    }

    body {
        background: var(--dark-bg);
        color: var(--dark-text);
        font-family: 'Poppins', sans-serif;
        transition: var(--transition);
    }

    body.light-mode {
        background: var(--light-bg);
        color: var(--light-text);
    }

    .container {
        padding: 3rem 1rem;
        min-height: calc(100vh - 200px);
    }

    .card {
        background-color: var(--dark-card-bg) !important;
        color: var(--dark-text);
        border: none !important;
        border-radius: 1rem;
        box-shadow: 0 4px 15px var(--glow-color);
        transition: var(--transition);
    }

    .light-mode .card {
        background-color: var(--light-card-bg) !important;
        color: var(--light-text);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px var(--glow-color);
    }

    .light-mode .card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .text-green-dark {
        color: var(--primary-green) !important;
    }

    .text-muted {
        color: var(--dark-text-muted) !important;
    }

    .light-mode .text-muted {
        color: var(--light-text-muted) !important;
    }

    .form-control {
        background-color: var(--dark-table-hover);
        color: var(--dark-text);
        border-color: var(--dark-border);
        border-radius: 0.5rem;
        transition: var(--transition);
    }

    .light-mode .form-control {
        background-color: #ffffff;
        color: var(--light-text);
        border-color: var(--light-border);
    }

    .form-control:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 8px var(--glow-color);
    }

    .btn-green {
        background-color: var(--primary-green);
        color: #ffffff;
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        transition: var(--transition);
        box-shadow: 0 2px 10px var(--glow-color);
    }

    .btn-green:hover {
        background-color: #2ecc71;
        transform: scale(1.05);
        box-shadow: 0 4px 15px var(--glow-color);
    }

    .table {
        color: var(--dark-text);
        border-collapse: separate;
        border-spacing: 0;
    }

    .light-mode .table {
        color: var(--light-text);
    }

    .table th, .table td {
        border: none;
        padding: 1rem;
        border-bottom: 1px solid var(--dark-border);
    }

    .light-mode .table th, .light-mode .table td {
        border-bottom: 1px solid var(--light-border);
    }

    .table-hover tbody tr:hover {
        background-color: var(--dark-table-hover);
        box-shadow: 0 2px 10px var(--glow-color);
    }

    .light-mode .table-hover tbody tr:hover {
        background-color: var(--light-table-hover);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .fade-in {
        animation: fadeIn 0.8s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .pagination .page-link {
        background-color: var(--dark-card-bg);
        color: var(--dark-text);
        border-color: var(--dark-border);
        transition: var(--transition);
    }

    .light-mode .pagination .page-link {
        background-color: var(--light-card-bg);
        color: var(--light-text);
        border-color: var(--light-border);
    }

    .pagination .page-link:hover {
        background-color: var(--primary-green);
        color: #ffffff;
        border-color: var(--primary-green);
    }

    .pagination .active .page-link {
        background-color: var(--primary-green);
        border-color: var(--primary-green);
        color: #ffffff;
    }
</style>

<div class="container py-5">
    <h1 class="text-3xl font-bold text-green-dark mb-6 fade-in text-center">Referral Program</h1>

    <!-- Referral Details Section -->
    <div class="card mb-5 p-4">
        <h3 class="text-xl font-semibold text-green-dark mb-4">Your Referral Details</h3>
        <div class="mb-4">
            <label class="block text-sm text-muted mb-2">Referral Code</label>
            <div class="input-group">
                <input type="text" id="referralCode" readonly value="{{ $user->code ?? $user->referral_code }}"
                       class="form-control rounded-start" />
                <button type="button" onclick="copyToClipboard('referralCode')"
                        class="btn btn-green rounded-end">Copy</button>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm text-muted mb-2">Referral Link</label>
            <div class="input-group">
                <input type="text" id="referralLink" readonly value="{{ $referral_link ?? $referralLink }}"
                       class="form-control rounded-start" />
                <button type="button" onclick="copyToClipboard('referralLink')"
                        class="btn btn-green rounded-end">Copy</button>
            </div>
        </div>
        <p class="text-muted mt-2">Share your referral code or link to earn 500 NGN per new user!</p>
    </div>

    <!-- Referral Stats Section -->
    <div class="card p-4">
        <h3 class="text-xl font-semibold text-green-dark mb-4">Referral Stats</h3>
        <p class="mb-2">Total Rewards Earned: ₦{{ number_format($total_rewards ?? $totalRewards, 2) }}</p>
        <p class="mb-4">Referred Users: {{ $referrals->total() }}</p>

        @if($referrals->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="py-3 text-muted">Referred User</th>
                            <th class="py-3 text-muted text-center">Reward Amount</th>
                            <th class="py-3 text-muted text-center">Status</th>
                            <th class="py-3 text-muted text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($referrals as $referral)
                            <tr>
                                <td class="py-3">{{ $referral->nickname ?? ($referral->referred ? $referral->referred->email : 'Unknown-') }}</td>
                                <td class="py-3 text-center">₦{{ number_format($referral->reward_amount ?? 0, 2) }}</td>
                                <td class="py-3 text-center">{{ ucfirst($referral->status) }}</td>
                                <td class="py-3 text-right">{{ $referral->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $referrals->links() }}
            </div>
        @else
            <p class="text-center text-muted mt-4">No referrals yet. Start sharing your referral code or link!</p>
        @endif
    </div>
</div>

<script>
    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        const text = element.value;

        navigator.clipboard.writeText(text)
            .then(() => {
                alert('Copied to clipboard!');
            })
            .catch(() => {
                alert('Error: Failed to copy. Please copy manually.');
            });
    }
</script>

@include('footer')

@endsection