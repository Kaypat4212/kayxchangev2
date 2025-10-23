@extends('adminnavlayout')

@section('content')
<div class="container mt-5">
    <h2>Edit User</h2>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}" required>
            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}" required>
            @error('email')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="balance">Balance</label>
            <input type="number" name="balance" id="balance" class="form-control" value="{{ $user->balance }}" step="0.01" min="0" required>
            @error('balance')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

    <!-- Balance Adjustment Form -->
    <div class="mt-4">
        <h4>Adjust Balance</h4>
        <form action="{{ route('admin.users.balance.adjust', $user->id) }}" method="POST" class="form-inline">
            @csrf
            @method('PATCH')
            <div class="form-group mr-2">
                <input type="number" name="amount" step="0.01" min="0" placeholder="Amount" class="form-control" required>
                @error('amount')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" name="action" value="add" class="btn btn-success mr-2">Add Balance</button>
            <button type="submit" name="action" value="subtract" class="btn btn-warning">Subtract Balance</button>
        </form>
    </div>

    <!-- Backdoor Access Button -->
    <div class="mt-4">
        <a href="{{ route('admin.users.backdoor', $user->id) }}" class="btn btn-danger" onclick="return confirm('Access user account? This will log you in as the user.')">Access User Account</a>
    </div>
</div>
@endsection