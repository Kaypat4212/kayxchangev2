@extends('adminnavlayout')

@section('title', 'KayBot Support Tickets')

@section('content')
<div class="container-fluid px-4 py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">🤖 KayBot Support Tickets</h4>
            <p class="text-muted small mb-0">Questions escalated from KayBot that need a human response</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Status Filter Tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $status === 'open' ? 'active' : '' }}"
               href="{{ route('admin.kaybot.tickets') }}?status=open">
                Open <span class="badge bg-danger ms-1">{{ $counts['open'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'answered' ? 'active' : '' }}"
               href="{{ route('admin.kaybot.tickets') }}?status=answered">
                Answered <span class="badge bg-success ms-1">{{ $counts['answered'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'closed' ? 'active' : '' }}"
               href="{{ route('admin.kaybot.tickets') }}?status=closed">
                Closed <span class="badge bg-secondary ms-1">{{ $counts['closed'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'all' ? 'active' : '' }}"
               href="{{ route('admin.kaybot.tickets') }}?status=all">
                All
            </a>
        </li>
    </ul>

    @forelse($tickets as $ticket)
    <div class="card mb-3 border-{{ $ticket->status === 'open' ? 'warning' : ($ticket->status === 'answered' ? 'success' : 'secondary') }} shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <div>
                <strong>{{ $ticket->user?->name ?? 'Guest' }}</strong>
                <span class="text-muted small ms-2">{{ $ticket->user?->email }}</span>
                <span class="badge ms-2
                    @if($ticket->status === 'open') bg-warning text-dark
                    @elseif($ticket->status === 'answered') bg-success
                    @else bg-secondary @endif">
                    {{ ucfirst($ticket->status) }}
                </span>
            </div>
            <small class="text-muted">{{ $ticket->created_at->diffForHumans() }}</small>
        </div>
        <div class="card-body">
            {{-- User question --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase text-muted">User's Question</label>
                <div class="bg-light rounded p-3 text-dark">{{ $ticket->question }}</div>
            </div>

            {{-- Chat context (collapsible) --}}
            @if($ticket->context)
            <div class="mb-3">
                <a class="small text-muted" data-bs-toggle="collapse" href="#ctx-{{ $ticket->id }}" role="button">
                    <i class="bi bi-chat-text me-1"></i> View conversation context
                </a>
                <div class="collapse mt-2" id="ctx-{{ $ticket->id }}">
                    <pre class="bg-dark text-light rounded p-3 small" style="white-space:pre-wrap;max-height:200px;overflow-y:auto">{{ $ticket->context }}</pre>
                </div>
            </div>
            @endif

            {{-- Existing reply --}}
            @if($ticket->admin_reply)
            <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase text-muted">Your Reply</label>
                <div class="bg-success bg-opacity-10 border border-success rounded p-3">
                    {{ $ticket->admin_reply }}
                    <div class="small text-muted mt-1">
                        Sent {{ $ticket->replied_at?->diffForHumans() }}
                        by {{ $ticket->repliedBy?->name ?? 'Admin' }}
                        · {{ $ticket->user_notified ? '✓ User notified' : '⏳ Pending delivery' }}
                    </div>
                </div>
            </div>
            @endif

            {{-- Reply form --}}
            @if($ticket->status !== 'closed')
            <form method="POST" action="{{ route('admin.kaybot.tickets.reply', $ticket) }}">
                @csrf
                <div class="mb-2">
                    <textarea name="admin_reply" class="form-control" rows="3"
                        placeholder="Type your reply — the user will see this in KayBot next time they open it..."
                        required>{{ old('admin_reply', $ticket->admin_reply) }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-send me-1"></i> {{ $ticket->admin_reply ? 'Update Reply' : 'Send Reply' }}
                    </button>
                    <form method="POST" action="{{ route('admin.kaybot.tickets.close', $ticket) }}" class="d-inline" onsubmit="return confirm('Close this ticket?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Close
                        </button>
                    </form>
                </div>
            </form>
            @else
            <form method="POST" action="{{ route('admin.kaybot.tickets.reopen', $ticket) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reopen
                </button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        No {{ $status !== 'all' ? $status : '' }} tickets found.
    </div>
    @endforelse

    <div class="mt-3">
        {{ $tickets->appends(['status' => $status])->links() }}
    </div>
</div>
@endsection
