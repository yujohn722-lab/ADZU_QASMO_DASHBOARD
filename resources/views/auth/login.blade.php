<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Energy Crisis Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: #e8edf3;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .login-top {
            height: 58px;
            background: #073f8f;
            color: #fff;
            display: flex;
            align-items: center;
            padding: 0 28px;
            font-size: 21px;
            font-weight: 700;
        }

        .login-card {
            max-width: 420px;
            margin: 70px auto;
            background: #fff;
            border: 1px solid #d7dde4;
            border-top: 3px solid #19bceb;
            border-radius: 2px;
        }

        .login-card .card-header {
            background: #fff;
            border-bottom: 1px solid #d7dde4;
            font-size: 18px;
        }

        .form-control,
        .btn {
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <div class="login-top">MyADZU</div>

    <div class="login-card">
        <div class="card-header">
            <i class="bi bi-lightning-charge me-2"></i> Energy Crisis Dashboard Login
        </div>
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email address</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" id="password" name="password" type="password" required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button class="btn btn-primary w-100" type="submit">Login</button>
            </form>
            <div class="small text-muted mt-3">
                Seeded admin: admin@example.edu / password
            </div>
        </div>
    </div>
</body>
</html>
