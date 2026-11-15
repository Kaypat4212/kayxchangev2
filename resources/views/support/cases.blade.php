@extends('layouts.app')
@section('title', 'Support cases')
@section('content')
<div class="container py-4" style="max-width:920px">
    <div class="mb-4"><p class="text-uppercase small text-primary fw-bold mb-1">KayXchange support</p><h1 class="h3 fw-bold">Your support cases</h1><p class="text-muted">Get help with a transaction or ask our team a question.</p></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="card shadow-sm border-0 mb-4"><div class="card-body p-4"><h2 class="h5 fw-bold">Open a case</h2><form method="post" action="{{ route('support.cases.store') }}">@csrf
        <div class="row g-3"><div class="col-md-4"><label class="form-label">Topic</label><select name="category" class="form-select"><option value="general">General help</option><option value="complaint" @selected(old('category')==='complaint')>Complaint</option><option value="deposit">Deposit</option><option value="withdrawal">Withdrawal</option><option value="swap">Swap</option><option value="bill">Bill payment</option></select></div><div class="col-md-8"><label class="form-label">Subject</label><input name="subject" maxlength="160" class="form-control" value="{{ old('subject') }}" required></div><div class="col-12"><label class="form-label">{{ old('category')==='complaint' ? 'Describe your complaint' : 'How can we help?' }}</label><textarea name="message" class="form-control" rows="4" minlength="10" maxlength="4000" required>{{ old('message') }}</textarea><div class="form-text">Your message goes to KayXchange support and the admin team. You’ll receive an in-app confirmation and can follow replies below.</div></div></div>
        <button class="btn btn-primary mt-3">Submit support case</button></form></div></div>
    <h2 class="h5 fw-bold mb-3">Previous cases</h2>
    @forelse($cases as $case)<article class="card border-0 shadow-sm mb-3"><div class="card-body"><div class="d-flex justify-content-between gap-3"><div><span class="badge text-bg-light">#{{ $case->id }} · {{ ucfirst(str_replace('_',' ',$case->category)) }}</span><h3 class="h6 mt-2 mb-1">{{ $case->subject }}</h3><p class="mb-2 text-muted">{{ \Illuminate\Support\Str::limit($case->message,170) }}</p></div><span class="badge {{ $case->status === 'resolved' ? 'text-bg-success' : 'text-bg-warning' }} align-self-start">{{ ucfirst(str_replace('_',' ',$case->status)) }}</span></div><div class="text-muted small mt-2">{{ $case->created_at->format('M j, Y · H:i') }}@if($case->crypto_deposit_id) · Deposit #{{ $case->crypto_deposit_id }}@endif</div><a href="{{ route('support.cases.show',$case) }}" class="btn btn-sm btn-outline-primary mt-3"><i class="bi bi-chat-dots me-1"></i>Open case · view replies</a></div></article>@empty<div class="text-muted py-4">No support cases yet.</div>@endforelse
    {{ $cases->links() }}
</div>
@endsection
