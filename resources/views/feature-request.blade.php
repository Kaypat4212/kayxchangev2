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
    --kx-orange: #f97316;
    --kx-orange-dim: rgba(249,115,22,0.12);
}
.fr-wrap { max-width: 680px; margin: 0 auto; padding: 28px 16px 80px; }
.fr-header { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
.fr-header-icon {
    width: 48px; height: 48px; border-radius: 14px;
    background: var(--kx-orange-dim);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
}
.fr-title { font-size: 1.35rem; font-weight: 700; color: var(--kx-text); margin: 0; }
.fr-subtitle { font-size: 0.82rem; color: var(--kx-muted); margin: 2px 0 0; }
.fr-card {
    background: var(--kx-card);
    border: 1px solid var(--kx-border);
    border-radius: 18px;
    padding: 28px 26px;
    margin-bottom: 18px;
}
.fr-label {
    display: block; font-size: 0.8rem; font-weight: 600;
    color: var(--kx-muted); text-transform: uppercase; letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.fr-input, .fr-select, .fr-textarea {
    width: 100%; background: var(--kx-dark);
    border: 1.5px solid var(--kx-border);
    border-radius: 10px; color: var(--kx-text);
    font-size: 0.9rem; padding: 0.65rem 0.9rem;
    outline: none; transition: border-color .2s;
    font-family: inherit;
}
.fr-input:focus, .fr-select:focus, .fr-textarea:focus {
    border-color: var(--kx-orange);
    box-shadow: 0 0 0 3px rgba(249,115,22,0.1);
}
.fr-select option { background: #1e2535; }
.fr-textarea { resize: vertical; min-height: 120px; }
.fr-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media(max-width:520px){ .fr-row { grid-template-columns: 1fr; } }
.fr-file-label {
    display: flex; align-items: center; gap: 10px;
    background: var(--kx-dark); border: 1.5px dashed var(--kx-border);
    border-radius: 10px; padding: 14px 16px; cursor: pointer;
    transition: border-color .2s; color: var(--kx-muted); font-size: 0.85rem;
}
.fr-file-label:hover { border-color: var(--kx-orange); color: var(--kx-text); }
.fr-file-label i { font-size: 1.1rem; color: var(--kx-orange); }
.fr-btn {
    width: 100%; padding: 13px; background: var(--kx-orange);
    color: #fff; border: none; border-radius: 10px;
    font-size: 0.95rem; font-weight: 600; cursor: pointer;
    transition: opacity .2s, transform .15s; display: flex;
    align-items: center; justify-content: center; gap: 8px;
}
.fr-btn:hover { opacity: 0.9; transform: translateY(-1px); }
.fr-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.fr-alert { border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; font-size: 0.87rem; }
.fr-alert-success { background: rgba(0,204,0,0.1); border: 1px solid rgba(0,204,0,0.25); color: #4ade80; }
.fr-alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); color: #f87171; }
.fr-alert ul { margin: 6px 0 0 18px; }
.fr-history-link { text-align: center; margin-top: 18px; font-size: 0.85rem; color: var(--kx-muted); }
.fr-history-link a { color: var(--kx-orange); text-decoration: none; }
.fr-history-link a:hover { text-decoration: underline; }
.fr-spin { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: fr-spin .7s linear infinite; }
@keyframes fr-spin { to { transform: rotate(360deg); } }
</style>

<div class="fr-wrap">
    <div class="fr-header">
        <div class="fr-header-icon">💡</div>
        <div>
            <p class="fr-title">Request a Feature</p>
            <p class="fr-subtitle">Have an idea? We'd love to hear it!</p>
        </div>
    </div>

    @if(session('success'))
        <div class="fr-alert fr-alert-success">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="fr-alert fr-alert-error">
            <strong>Please fix the following:</strong>
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="fr-card">
        <form method="POST" action="{{ route('feature.request.submit') }}" enctype="multipart/form-data" id="frForm">
            @csrf
            <div style="margin-bottom:18px">
                <label class="fr-label" for="title">Feature Title</label>
                <input class="fr-input" type="text" name="title" id="title"
                    value="{{ old('title') }}" required
                    placeholder="e.g. Add support for USDT deposits">
            </div>

            <div class="fr-row" style="margin-bottom:18px">
                <div>
                    <label class="fr-label" for="category">Category</label>
                    <select class="fr-select" name="category" id="category" required>
                        <option value="">— Select —</option>
                        <option value="trading" {{ old('category')=='trading'?'selected':'' }}>Trading</option>
                        <option value="payments" {{ old('category')=='payments'?'selected':'' }}>Payments</option>
                        <option value="ui" {{ old('category')=='ui'?'selected':'' }}>UI / Design</option>
                        <option value="security" {{ old('category')=='security'?'selected':'' }}>Security</option>
                        <option value="notification" {{ old('category')=='notification'?'selected':'' }}>Notifications</option>
                        <option value="other" {{ old('category')=='other'?'selected':'' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="fr-label" for="priority">Priority</label>
                    <select class="fr-select" name="priority" id="priority" required>
                        <option value="">— Select —</option>
                        <option value="low" {{ old('priority')=='low'?'selected':'' }}>Low</option>
                        <option value="medium" {{ old('priority')=='medium'?'selected':'' }}>Medium</option>
                        <option value="high" {{ old('priority')=='high'?'selected':'' }}>High</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:18px">
                <label class="fr-label" for="description">Description</label>
                <textarea class="fr-textarea" name="description" id="description" required
                    placeholder="Describe the feature in detail — what problem it solves, how it should work, etc.">{{ old('description') }}</textarea>
            </div>

            <div style="margin-bottom:22px">
                <label class="fr-label">Attachment <span style="text-transform:none;font-weight:400">(optional — screenshot / mockup)</span></label>
                <label class="fr-file-label" for="attachment">
                    <i class="bi bi-paperclip"></i>
                    <span id="fileNameLabel">Click to attach a file (JPG, PNG, PDF · max 5MB)</span>
                </label>
                <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.pdf"
                    style="display:none" onchange="document.getElementById('fileNameLabel').textContent=this.files[0]?.name||'Click to attach a file'">
            </div>

            <button type="submit" class="fr-btn" id="frBtn">
                <i class="bi bi-send"></i> Submit Feature Request
            </button>
        </form>
    </div>

    <p class="fr-history-link">
        Want to see your previous requests? <a href="{{ route('feature.request.history') }}">View history →</a>
    </p>
</div>

<script>
document.getElementById('frForm').addEventListener('submit', function() {
    const btn = document.getElementById('frBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="fr-spin"></span> Submitting...';
});
</script>
@endsection