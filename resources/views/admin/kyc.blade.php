@extends('kyclayout')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

<style>
    body {
        background-color: #1a1a1a;
        color: #ffffff;
        font-family: Arial, sans-serif;
    }
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        background-color: #3a3a3a;
        color: #ffffff;
        width: 100%;
        border-collapse: collapse;
    }
    .table th, .table td {
        padding: 0.75rem;
        text-align: left;
        border: 1px solid #4a4a4a;
    }
    .table th {
        background-color: #2c2c2c;
        color: white;
    }
    .btn-approve, .btn-reject {
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        transition: transform 0.2s ease;
        font-size: 0.875rem;
        width: 100%;
        text-align: center;
        margin: 0.25rem 0;
    }
    .btn-approve {
        background-color: #28a745;
        color: #ffffff;
    }
    .btn-reject {
        background-color: #dc3545;
        color: #ffffff;
    }
    .btn-approve:hover, .btn-reject:hover {
        transform: scale(1.05);
    }
    .toast {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 1000;
        max-width: 90%;
    }
    .pagination {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    .pagination a, .pagination span {
        padding: 0.5rem 1rem;
        background-color: #3a3a3a;
        border-radius: 0.375rem;
        color: #ffffff;
        text-decoration: none;
    }
    .pagination a:hover {
        background-color: #28a745;
    }
    .pagination .current {
        background-color: #28a745;
    }

    @media (max-width: 768px) {
        .table th, .table td {
            font-size: 0.75rem;
            padding: 0.5rem;
        }
        .btn-approve, .btn-reject {
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
        }
        .admin-container {
            padding: 1rem;
            margin: 1rem;
        }
        .table th:not(:first-child),
        .table td:not(:first-child) {
            min-width: 100px;
        }
    }

    @media (max-width: 640px) {
        .table thead {
            display: none;
        }
        .table, .table tbody, .table tr, .table td {
            display: block;
            width: 100%;
        }
        .table tr {
            margin-bottom: 1rem;
            border: 1px solid #4a4a4a;
            border-radius: 0.375rem;
        }
        .table td {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem;
            border: none;
            border-bottom: 1px solid #4a4a4a;
            position: relative;
            text-align: right;
        }
        .table td:before {
            content: attr(data-label);
            font-weight: bold;
            text-align: left;
            flex: 1;
            color: #28a745;
        }
        .table td:last-child {
            border-bottom: none;
        }
        .btn-approve, .btn-reject {
            display: inline-block;
            width: auto;
            margin: 0.25rem;
        }
    }
</style>

<div class="admin-container mx-auto bg-gray-800 rounded-xl p-6 sm:p-4 max-w-7xl sm:mx-2">
    <h2 class="text-center text-green-500 mb-6 text-2xl sm:text-xl">KYC Verification Dashboard</h2>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>ID Document</th>
                    <th>Selfie</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kycRecords as $kyc)
                    <tr>
                        <td data-label="User">{{ $kyc->user->name }}</td>
                        <td data-label="Email">{{ $kyc->user->email }}</td>
                        <td data-label="ID Document"><a href="{{ Storage::url($kyc->id_document_path) }}" target="_blank" class="text-green-500 hover:underline">View</a></td>
                        <td data-label="Selfie"><a href="{{ Storage::url($kyc->selfie_path) }}" target="_blank" class="text-green-500 hover:underline">View</a></td>
                        <td data-label="Status">{{ ucfirst($kyc->status) }}</td>
                        <td data-label="Actions">
                            @if ($kyc->status === 'pending')
                                <form action="{{ route('kyc.verify', $kyc) }}" method="POST" class="inline-block sm:w-full">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn-approve sm:w-full">Approve</button>
                                </form>
                                <form action="{{ route('kyc.verify', $kyc) }}" method="POST" class="inline-block sm:w-full">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn-reject sm:w-full">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $kycRecords->links() }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
    @if (session('success'))
        Toastify({
            text: "{{ session('success') }}",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "#28a745",
            className: "toast",
        }).showToast();
    @endif

    @if (session('error'))
        Toastify({
            text: "{{ session('error') }}",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "#dc3545",
            className: "toast",
        }).showToast();
    @endif
</script>
@endsection