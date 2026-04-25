<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Payment Proof — KayXchange</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a0f;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            background: #12131a;
            border: 1px solid #1e2130;
            border-radius: 1rem;
            padding: 2rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .logo { font-size: 2rem; margin-bottom: 0.5rem; }
        h1 { font-size: 1.25rem; color: #a8b4c8; margin-bottom: 1.5rem; }
        .icon-big { font-size: 3rem; margin-bottom: 1rem; }
        .msg { font-size: 1rem; line-height: 1.6; color: #94a3b8; margin-bottom: 1.5rem; }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-align: left;
        }
        .alert-error   { background: #2d1515; border: 1px solid #6b2020; color: #f87171; }
        .alert-success { background: #0f2d1a; border: 1px solid #1a6b3a; color: #4ade80; }
        label { display: block; text-align: left; font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.4rem; }
        .file-input-wrapper {
            border: 2px dashed #2a3040;
            border-radius: 0.75rem;
            padding: 2rem 1rem;
            cursor: pointer;
            transition: border-color 0.2s;
            margin-bottom: 1.25rem;
            position: relative;
        }
        .file-input-wrapper:hover { border-color: #3b82f6; }
        .file-input-wrapper input[type="file"] {
            position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
        }
        .file-input-wrapper .hint { font-size: 0.85rem; color: #64748b; margin-top: 0.4rem; }
        .file-name { font-size: 0.85rem; color: #60a5fa; margin-top: 0.5rem; }
        .btn {
            display: block; width: 100%;
            padding: 0.85rem;
            background: #3b82f6;
            color: #fff;
            border: none;
            border-radius: 0.6rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover { background: #2563eb; }
        .btn:disabled { background: #334155; cursor: not-allowed; }
        .powered { margin-top: 1.5rem; font-size: 0.75rem; color: #334155; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">💱</div>
    <h1>KayXchange — Payment Proof Upload</h1>

    @if($expired ?? false)
        <div class="icon-big">⏰</div>
        <p class="msg">This upload link has <strong>expired</strong> or is invalid.<br>Please go back to the Telegram bot and start your sell trade again to get a new link.</p>

    @elseif($success ?? false)
        <div class="icon-big">✅</div>
        <p class="msg" style="color:#4ade80;font-size:1.1rem;"><strong>Upload successful!</strong></p>
        <p class="msg">Your payment proof has been received. Go back to the Telegram bot — you should see the next step waiting for you.</p>

    @else
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <p class="msg">Upload a screenshot or photo of your <strong>crypto transfer</strong> as proof of payment.</p>

        <form method="POST" action="{{ route('tg.upload-proof.store') }}" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label for="proof">Select proof image (JPG, PNG, PDF — max 5 MB)</label>
            <div class="file-input-wrapper" id="dropZone">
                <div class="icon-big" style="font-size:2rem">📷</div>
                <div>Tap to choose a photo</div>
                <div class="hint">JPG · PNG · WEBP · PDF</div>
                <input type="file" name="proof" id="proof" accept="image/*,.pdf" required>
            </div>
            <div class="file-name" id="fileName"></div>

            <button type="submit" class="btn" id="submitBtn">Upload Proof</button>
        </form>
    @endif

    <div class="powered">Secured by KayXchange · tradewithkay.com</div>
</div>

<script>
    const input    = document.getElementById('proof');
    const fileName = document.getElementById('fileName');
    const btn      = document.getElementById('submitBtn');

    if (input) {
        input.addEventListener('change', () => {
            if (input.files[0]) {
                fileName.textContent = '📎 ' + input.files[0].name;
            }
        });
    }

    const form = document.getElementById('uploadForm');
    if (form) {
        form.addEventListener('submit', () => {
            if (btn) { btn.disabled = true; btn.textContent = 'Uploading…'; }
        });
    }
</script>
</body>
</html>
