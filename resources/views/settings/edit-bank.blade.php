@extends('layout')

@section('content')
<div class="container my-5">
    <h4>Edit Bank Information</h4>

    <form method="POST" action="{{ route('update.bank') }}">
        @csrf
        <div class="form-group mb-3">
            <label for="bank_name">Bank Name</label>
            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $user->bank_name) }}" required>
        </div>
        <div class="form-group mb-3">
            <label for="account_number">Account Number</label>
            <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $user->account_number) }}" required>
        </div>
        <div class="form-group mb-3">
            <label for="account_name">Account Name</label>
            <input type="text" name="account_name" class="form-control" value="{{ old('account_name', $user->account_name) }}" required>
        </div>
        <button type="submit" class="btn btn-success">Update Bank Info</button>
    </form>
</div>
@endsection
