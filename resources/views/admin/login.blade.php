<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - {{ config('app.name') }}</title>
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
            --kx-danger: #ff6b6b;
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
        .login-shell {
            width: 100%;
            max-width: 460px;
            background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border: 1px solid var(--kx-border);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.35);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .brand-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--kx-green);
            box-shadow: 0 0 12px rgba(0, 204, 0, 0.8);
        }
        .title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .subtitle {
            color: var(--kx-muted);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        .form-label {
            color: var(--kx-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }
        .form-control {
            background: #0f1714;
            border: 1px solid var(--kx-border);
            color: var(--kx-text);
            border-radius: 10px;
            padding: 0.75rem 0.85rem;
        }
        .form-control:focus {
            background: #0f1714;
            color: var(--kx-text);
            border-color: rgba(0, 204, 0, 0.5);
            box-shadow: 0 0 0 0.2rem rgba(0, 204, 0, 0.15);
        }
        .btn-login {
            background: var(--kx-green);
            color: #041a04;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
        }
        .btn-login:hover { background: #00de00; }
        .help-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-top: 12px;
            font-size: 0.85rem;
        }
        .help-row a { color: #a6ffaf; text-decoration: none; }
        .help-row a:hover { text-decoration: underline; }
        .alert-kx {
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 14px;
        }
        .alert-danger {
            background: rgba(255, 107, 107, 0.12);
            color: #ffbaba;
            border: 1px solid rgba(255, 107, 107, 0.35);
        }
        .alert-success {
            background: rgba(0, 204, 0, 0.12);
            color: #b9ffbf;
            border: 1px solid rgba(0, 204, 0, 0.35);
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="brand"><span class="brand-dot"></span>{{ config('app.name') }} Admin</div>
        <div class="title">Secure Admin Login</div>
        <div class="subtitle">Use your admin credentials to continue.</div>

        @if (session('success'))
            <div class="alert alert-success alert-kx">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-kx">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Admin Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="mb-2">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-login w-100 mt-2">Login</button>
        </form>

        <div class="help-row">
            <a href="{{ route('admin.password.request') }}"><i class="bi bi-key me-1"></i>Forgot password (secret key)</a>
            <a href="mailto:{{ config('app.support_email') }}"><i class="bi bi-life-preserver me-1"></i>{{ config('app.support_email') }}</a>
        </div>
        <div class="help-row" style="justify-content:flex-start;color:#90a099;">
            Security contact: <a href="mailto:{{ config('app.security_email') }}">{{ config('app.security_email') }}</a>
        </div>
    </div>
</body>
</html>
