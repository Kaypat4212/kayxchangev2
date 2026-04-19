<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            margin: 0;
            background: radial-gradient(1000px 500px at 0% 0%, rgba(0, 204, 0, 0.12), transparent), #0b1210;
            color: #e7f0ec;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 18px;
        }
        .card-wrap {
            width: 100%;
            max-width: 520px;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 18px;
            padding: 26px;
            background: #131d1a;
            box-shadow: 0 24px 60px rgba(0,0,0,0.35);
        }
        .form-label {
            color: #90a099;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .form-control {
            background: #0f1714;
            border: 1px solid rgba(255,255,255,0.09);
            color: #e7f0ec;
            border-radius: 10px;
            padding: .75rem .85rem;
        }
        .form-control:focus {
            background: #0f1714;
            color: #e7f0ec;
            border-color: rgba(0, 204, 0, 0.45);
            box-shadow: 0 0 0 0.2rem rgba(0, 204, 0, 0.14);
        }
        .btn-reset {
            background: #00cc00;
            color: #031803;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            padding: .78rem;
        }
        .btn-reset:hover { background: #00dd00; }
        .muted { color: #90a099; font-size: .86rem; }
    </style>
</head>
<body>
    <div class="card-wrap">
        <h4 class="mb-1">Admin Password Reset</h4>
        <p class="muted mb-3">Reset an admin account password using the secret key.</p>

        @if ($errors->any())
            <div class="alert alert-danger" style="background:rgba(255,107,107,.12);border:1px solid rgba(255,107,107,.35);color:#ffbaba;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.reset.secret') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">Admin Email</label>
                <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="secret_key">Secret Key</label>
                <input class="form-control" type="password" id="secret_key" name="secret_key" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">New Password</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password_confirmation">Confirm New Password</label>
                <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn btn-reset w-100">Reset Admin Password</button>
        </form>

        <div class="mt-3 d-flex justify-content-between flex-wrap gap-2 muted">
            <a href="{{ route('admin.login') }}" style="color:#a6ffaf;text-decoration:none;"><i class="bi bi-arrow-left"></i> Back to login</a>
            <a href="mailto:{{ config('app.security_email') }}" style="color:#a6ffaf;text-decoration:none;">Need help? {{ config('app.security_email') }}</a>
        </div>
    </div>
</body>
</html>
