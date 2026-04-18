@extends('layout')

@section('content')
<style>
:root {
    --kx-dark: #0d1117;
    --kx-card: #161b27;
    --kx-card2: #1e2535;
    --kx-border: rgba(255,255,255,0.07);
    --kx-text: #e4e8f0;
    --kx-muted: #7a8599;
    --kx-red: #ef4444;
    --kx-red-dim: rgba(239,68,68,0.12);
}
.br-wrap { max-width: 680px; margin: 0 auto; padding: 28px 16px 80px; }
.br-header { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
.br-header-icon {
    width: 48px; height: 48px; border-radius: 14px;
    background: var(--kx-red-dim);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
}
.br-title { font-size: 1.35rem; font-weight: 700; color: var(--kx-text); margin: 0; }
.br-subtitle { font-size: 0.82rem; color: var(--kx-muted); margin: 2px 0 0; }
.br-card {
    background: var(--kx-card);
    border: 1px solid var(--kx-border);
    border-radius: 18px;
    padding: 28px 26px;
    margin-bottom: 18px;
}
.br-label {
    display: block; font-size: 0.8rem; font-weight: 600;
    color: var(--kx-muted); text-transform: uppercase; letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.br-input, .br-select, .br-textarea {
    width: 100%; background: var(--kx-dark);
    border: 1.5px solid var(--kx-border);
    border-radius: 10px; color: var(--kx-text);
    font-size: 0.9rem; padding: 0.65rem 0.9rem;
    outline: none; transition: border-color .2s;
    font-family: inherit;
}
.br-input:focus, .br-select:focus, .br-textarea:focus {
    border-color: var(--kx-red);
    box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
}
.br-select option { background: #1e2535; }
.br-textarea { resize: vertical; min-height: 120px; }
.br-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media(max-width:520px){ .br-row { grid-template-columns: 1fr; } }
.br-file-label {
    display: flex; align-items: center; gap: 10px;
    background: var(--kx-dark); border: 1.5px dashed var(--kx-border);
    border-radius: 10px; padding: 14px 16px; cursor: pointer;
    transition: border-color .2s; color: var(--kx-muted); font-size: 0.85rem;
}
.br-file-label:hover { border-color: var(--kx-red); color: var(--kx-text); }
.br-file-label i { font-size: 1.1rem; color: var(--kx-red); }
.br-btn {
    width: 100%; padding: 13px; background: var(--kx-red);
    color: #fff; border: none; border-radius: 10px;
    font-size: 0.95rem; font-weight: 600; cursor: pointer;
    transition: opacity .2s, transform .15s; display: flex;
    align-items: center; justify-content: center; gap: 8px;
}
.br-btn:hover { opacity: 0.9; transform: translateY(-1px); }
.br-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.br-alert { border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; font-size: 0.87rem; }
.br-alert-success { background: rgba(0,204,0,0.1); border: 1px solid rgba(0,204,0,0.25); color: #4ade80; }
.br-alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); color: #f87171; }
.br-alert ul { margin: 6px 0 0 18px; }
.br-history-link { text-align: center; margin-top: 18px; font-size: 0.85rem; color: var(--kx-muted); }
.br-history-link a { color: var(--kx-red); text-decoration: none; }
.br-history-link a:hover { text-decoration: underline; }
.br-spin { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: br-spin .7s linear infinite; }
@keyframes br-spin { to { transform: rotate(360deg); } }
.br-sev-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px; }
.br-sev-badge {
    padding: 5px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 600;
    border: 1.5px solid transparent; cursor: pointer; transition: all .15s;
    user-select: none;
}
.br-sev-badge.low    { border-color: #64748b; color: #94a3b8; }
.br-sev-badge.medium { border-color: #f59e0b; color: #fbbf24; }
.br-sev-badge.high   { border-color: #f97316; color: #fb923c; }
.br-sev-badge.critical { border-color: #ef4444; color: #f87171; }
.br-sev-badge.selected { background: currentColor; }
.br-sev-badge.selected span { color: #0d1117; }
</style>

<div class="br-wrap">
    <div class="br-header">
        <div class="br-header-icon">🐛</div>
        <div>
            <p class="br-title">Report a Bug</p>
            <p class="br-subtitle">Found something broken? Let us know so we can fix it.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="br-alert br-alert-success">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="br-alert br-alert-error">
            <strong>Please fix the following:</strong>
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="br-card">
        <form method="POST" action="{{ route('bug.report.submit') }}" enctype="multipart/form-data" id="brForm">
            @csrf

            <div style="margin-bottom:18px">
                <label class="br-label" for="br_title">Bug Title</label>
                <input class="br-input" type="text" name="title" id="br_title"
                    value="{{ old('title') }}" required
                    placeholder="e.g. Deposit button not responding on mobile">
            </div>

            <div class="br-row" style="margin-bottom:18px">
                <div>
                    <label class="br-label" for="br_category">Category</label>
                    <select class="br-select" name="category" id="br_category" required>
                        <option value="">— Select —</option>
                        <option value="general"  {{ old('category')=='general'?'selected':'' }}>General</option>
                        <option value="ui"       {{ old('category')=='ui'?'selected':'' }}>UI / Display</option>
                        <option value="payment"  {{ old('category')=='payment'?'selected':'' }}>Payment</option>
                        <option value="trade"    {{ old('category')=='trade'?'selected':'' }}>Trading</option>
                        <option value="account"  {{ old('category')=='account'?'selected':'' }}>Account</option>
                        <option value="other"    {{ old('category')=='other'?'selected':'' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="br-label">Severity</label>
                    <input type="hidden" name="severity" id="severityInput" value="{{ old('severity', 'medium') }}" required>
                    <div class="br-sev-badges" id="sevBadges">
                        @foreach(['low','medium','high','critical'] as $s)
                            <label class="br-sev-badge {{ $s }} {{ old('severity','medium')===$s ? 'selected' : '' }}"
                                data-val="{{ $s }}">
                                <span>{{ ucfirst($s) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div style="margin-bottom:18px">
                <label class="br-label" for="br_page">Page / URL where bug occurred <span style="text-transform:none;font-weight:400">(optional)</span></label>
                <input class="br-input" type="url" name="page_url" id="br_page"
                    value="{{ old('page_url') }}"
                    placeholder="https://...">
            </div>

            <div style="margin-bottom:18px">
                <label class="br-label" for="br_desc">Description</label>
                <textarea class="br-textarea" name="description" id="br_desc" required
                    placeholder="Describe the bug — what you did, what you expected, and what happened instead.">{{ old('description') }}</textarea>
            </div>

            <div style="margin-bottom:22px">
                <label class="br-label">Screenshot <span style="text-transform:none;font-weight:400">(optional)</span></label>
                <label class="br-file-label" for="br_attachment">
                    <i class="bi bi-image"></i>
                    <span id="brFileLabel">Click to attach a screenshot (JPG, PNG, PDF · max 5MB)</span>
                </label>
                <input type="file" name="attachment" id="br_attachment" accept=".jpg,.jpeg,.png,.pdf"
                    style="display:none"
                    onchange="document.getElementById('brFileLabel').textContent=this.files[0]?.name||'Click to attach a screenshot'">
            </div>

            <button type="submit" class="br-btn" id="brBtn">
                <i class="bi bi-bug"></i> Submit Bug Report
            </button>
        </form>
    </div>

    <p class="br-history-link">
        Already submitted? <a href="{{ route('bug.report.history') }}">View your reports →</a>
    </p>
</div>

<script>
// Severity badge toggle
document.querySelectorAll('.br-sev-badge').forEach(badge => {
    badge.addEventListener('click', function() {
        document.querySelectorAll('.br-sev-badge').forEach(b => b.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('severityInput').value = this.dataset.val;
    });
});

document.getElementById('brForm').addEventListener('submit', function() {
    const btn = document.getElementById('brBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="br-spin"></span> Submitting...';
});
</script>
@endsection
