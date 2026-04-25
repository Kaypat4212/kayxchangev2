<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification - {{ config('app.name') }} Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --kx-green: #00cc00;
            --kx-dark: #0b1210;
            --kx-card: #131d1a;
            --kx-border: rgba(255, 255, 255, 0.08);
            --kx-text: #e7f0ec;
            --kx-muted: #90a099;
        }
        body {
            min-height: 100vh;
            margin: 0;
            background: radial-gradient(1200px 600px at 10% -10%, rgba(0, 204, 0, 0.12), transparent),
                        radial-gradient(900px 500px at 100% 100%, rgba(0, 204, 0, 0.08), transparent),
                        var(--kx-dark);
            color: var(--kx-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: grid;
            place-items: center;
            padding: 20px;
        }
        .shell {
            width: 100%;
            max-width: 420px;
            background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
            border: 1px solid var(--kx-border);
            border-radius: 20px;
            padding: 32px 28px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.4);
        }
        .icon-ring {
            width: 68px; height: 68px;
            background: rgba(0,204,0,0.1);
            border: 2px solid rgba(0,204,0,0.3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            color: var(--kx-green);
        }
        h2 { font-size: 1.35rem; font-weight: 700; text-align: center; margin-bottom: .4rem; }
        p.sub { text-align: center; color: var(--kx-muted); font-size: .85rem; margin-bottom: 1.6rem; }
        .kx-input {
            background: rgba(255,255,255,0.04);
            border: 1.5px solid var(--kx-border);
            color: var(--kx-text);
            border-radius: 10px;
            padding: 13px 16px;
            font-size: 1.5rem;
            letter-spacing: .5rem;
            text-align: center;
            width: 100%;
            transition: border-color .2s;
        }
        .kx-input:focus { outline: none; border-color: var(--kx-green); background: rgba(0,204,0,0.04); }
        .kx-btn {
            width: 100%;
            background: linear-gradient(135deg, #00cc00, #007a0c);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-weight: 700;
            font-size: .95rem;
            cursor: pointer;
            margin-top: 1rem;
            transition: opacity .2s;
        }
        .kx-btn:hover { opacity: .9; }
        .err { color: #ff6b6b; font-size: .82rem; margin-top: .4rem; }
        .back-link { text-align: center; margin-top: 1.1rem; font-size: .82rem; color: var(--kx-muted); }
        .back-link a { color: var(--kx-green); text-decoration: none; }
    </style>
</head>
<body>
<div class="shell">
    <div class="icon-ring"><i class="bi bi-shield-lock-fill"></i></div>
    <h2>Two-Factor Authentication</h2>
    <p class="sub">Open your authenticator app and enter the 6-digit code.</p>

    @if($errors->any())
        <div class="err text-center mb-3"><i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.2fa.verify') }}">
        @csrf
        <input type="text" name="code" class="kx-input" placeholder="000000"
               inputmode="numeric" maxlength="6" autocomplete="one-time-code" autofocus
               pattern="[0-9]{6}" required>
        <button type="submit" class="kx-btn"><i class="bi bi-check2-circle me-2"></i>Verify Code</button>
    </form>

    <div class="back-link">
        <a href="{{ route('admin.login') }}"><i class="bi bi-arrow-left me-1"></i>Back to login</a>
    </div>
</div>
<script>
// Auto-format: only allow digits
document.querySelector('input[name=code]').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
});
</script>
</body>
</html>
