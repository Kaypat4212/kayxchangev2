@extends('adminlayout')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-success fw-bold mb-2">Operations · Diagnostics</div>
            <h1 class="h3 text-white mb-1">Laravel error logs</h1>
            <p class="text-secondary mb-0">Review a recent log excerpt and ask AI for likely causes and practical fixes.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-light" href="{{ route('admin.logs.index', ['file' => $selected]) }}"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
            @if($selected)
            <a class="btn btn-outline-info" href="{{ route('admin.logs.download', ['file' => $selected]) }}"><i class="bi bi-download me-1"></i>Download sanitized log</a>
            <form method="POST" action="{{ route('admin.logs.clear') }}" onsubmit="return confirm('Clear the selected Laravel log? This removes its current contents.');">
                @csrf
                @method('DELETE')
                <input type="hidden" name="file" value="{{ $selected }}">
                <button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash3 me-1"></i>Clear log</button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif
    @if($logError)<div class="alert alert-info">{{ $logError }} Check that Laravel can read <code>storage/logs</code> on the server.</div>@endif

    <div class="card bg-dark border-secondary mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.logs.index') }}" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="logFile" class="form-label text-light">Log file</label>
                    <select id="logFile" name="file" class="form-select bg-black text-light border-secondary" onchange="this.form.submit()">
                        @forelse($files as $file)<option value="{{ $file }}" @selected($selected === $file)>{{ $file }}</option>@empty<option value="">No Laravel logs found</option>@endforelse
                    </select>
                </div>
                <div class="col-md-4">
                    @if($selected)
                    <button form="analyzeLogForm" class="btn btn-success w-100"><i class="bi bi-stars me-1"></i>Diagnose recent errors with AI</button>
                    @endif
                </div>
            </form>
            @if($selected)
            <form id="analyzeLogForm" method="POST" action="{{ route('admin.logs.analyze') }}" class="d-none">@csrf<input type="hidden" name="file" value="{{ $selected }}"></form>
            @endif
            <div class="small text-secondary mt-3">Only the last 180 lines (up to 18 KB) are sent for analysis. Common credentials and personal contact details are redacted first.</div>
        </div>
    </div>

    @if($analysis)
    <section class="card border-success mb-4">
        <div class="card-header bg-success bg-opacity-10 text-white"><i class="bi bi-lightbulb me-2"></i>AI diagnosis and suggested fixes</div>
        <div class="card-body text-light" style="white-space:pre-wrap">{{ $analysis }}</div>
    </section>
    @endif

    <section class="card bg-dark border-danger border-opacity-25 mb-4">
        <div class="card-header border-danger border-opacity-25 d-flex justify-content-between align-items-center flex-wrap gap-2 text-light"><span><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Error and exception entries</span><div class="d-flex align-items-center gap-2"><span class="badge text-bg-danger">{{ $errorCount ?? 0 }} in latest excerpt</span>@if(!empty($errorLines))<button class="btn btn-sm btn-outline-light" type="button" id="copyErrorsButton"><i class="bi bi-clipboard me-1"></i>Copy errors</button>@endif</div></div>
        <div class="card-body p-0"><pre class="m-0 p-3 text-light" style="max-height:38vh;overflow:auto;white-space:pre-wrap;overflow-wrap:anywhere;font-size:.8rem">{{ !empty($errorLines) ? implode("\n", $errorLines) : 'No error-level entries found in the latest excerpt.' }}</pre></div>
        <div class="card-footer border-secondary small text-secondary">This section filters the sanitized excerpt shown below. New application errors are written to the selected Laravel log automatically.</div>
    </section>

    <section class="card bg-dark border-secondary">
        <div class="card-header border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2 text-light">
            <span><i class="bi bi-file-earmark-text me-2"></i>Latest log excerpt</span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-secondary">Redacted · latest 220 lines</span>
                @if($content !== '')<button class="btn btn-sm btn-outline-light" type="button" id="copyLogButton"><i class="bi bi-clipboard me-1"></i>Copy error</button>@endif
            </div>
        </div>
        <div class="card-body p-0"><pre id="logContent" class="m-0 p-3 text-light" style="max-height:65vh;overflow:auto;white-space:pre-wrap;overflow-wrap:anywhere;font-size:.8rem">{{ $content !== '' ? $content : 'No log contents to display.' }}</pre></div>
    </section>
    <div class="small text-secondary mt-2" id="copyLogStatus" role="status" aria-live="polite"></div>
</div>
@if($content !== '')
<script>
document.getElementById('copyLogButton')?.addEventListener('click', async function () {
    const text = document.getElementById('logContent')?.innerText || '';
    const status = document.getElementById('copyLogStatus');
    try {
        await navigator.clipboard.writeText(text);
        if (status) status.textContent = 'Sanitized log excerpt copied.';
    } catch (error) {
        const temp = document.createElement('textarea');
        temp.value = text;
        temp.setAttribute('readonly', '');
        temp.style.position = 'fixed'; temp.style.opacity = '0';
        document.body.appendChild(temp); temp.select();
        const copied = document.execCommand('copy'); temp.remove();
        if (status) status.textContent = copied ? 'Sanitized log excerpt copied.' : 'Copy failed. Select and copy the log text manually.';
    }
});
document.getElementById('copyErrorsButton')?.addEventListener('click', async function () {
    const text = @json(implode("\n", $errorLines ?? []));
    const status = document.getElementById('copyLogStatus');
    try {
        await navigator.clipboard.writeText(text);
        if (status) status.textContent = 'Sanitized error entries copied.';
    } catch (error) {
        const temp = document.createElement('textarea');
        temp.value = text;
        temp.setAttribute('readonly', '');
        temp.style.position = 'fixed'; temp.style.opacity = '0';
        document.body.appendChild(temp); temp.select();
        const copied = document.execCommand('copy'); temp.remove();
        if (status) status.textContent = copied ? 'Sanitized error entries copied.' : 'Copy failed. Select and copy the error text manually.';
    }
});
</script>
@endif
@endsection
