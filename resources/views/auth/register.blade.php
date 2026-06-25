<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - University Energy Monitoring Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background:
                linear-gradient(rgba(7, 63, 143, 0.72), rgba(2, 29, 73, 0.78)),
                url("{{ asset('assets/adzu-campus-login-bg.jpg') }}") center / cover no-repeat fixed;
            font-family: "Segoe UI", Arial, sans-serif;
            padding: 36px 16px;
        }

        .auth-seal {
            display: block;
            width: 128px;
            max-width: 38vw;
            margin: 0 auto 18px;
            filter: drop-shadow(0 10px 22px rgba(2, 29, 73, 0.34));
        }

        .login-card {
            max-width: 460px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #d7dde4;
            border-top: 3px solid #19bceb;
            border-radius: 2px;
            box-shadow: 0 18px 45px rgba(2, 29, 73, 0.28);
        }

        .login-card .card-header {
            background: rgba(255, 255, 255, 0.96);
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
    <img class="auth-seal" src="{{ asset('assets/adzu-seal.png') }}" alt="Ateneo de Zamboanga University seal">

    <div class="login-card">
        <div class="card-header">
            <i class="bi bi-person-plus me-2"></i> Responder Registration
        </div>
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please review the form.</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Full name</label>
                    <input class="form-control" id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="office_name">Office name</label>
                    <input class="form-control" id="office_name" name="office_name" type="text" value="{{ old('office_name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email address</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <input class="form-control" id="password" name="password" type="password" required>
                        <button class="btn btn-outline-secondary" type="button" data-password-toggle="password" aria-label="Show password" title="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password_confirmation">Confirm password</label>
                    <div class="input-group">
                        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required>
                        <button class="btn btn-outline-secondary" type="button" data-password-toggle="password_confirmation" aria-label="Show password" title="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reports you will submit</label>
                    <div class="border rounded-1 p-3">
                        @foreach (\App\Models\User::REPORT_TYPES as $key => $label)
                            <div class="form-check">
                                <input class="form-check-input" id="report_type_{{ $key }}" name="report_types[]" type="checkbox" value="{{ $key }}" @checked(in_array($key, old('report_types', []), true))>
                                <label class="form-check-label" for="report_type_{{ $key }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <button class="btn btn-primary w-100" type="submit">Submit registration</button>
            </form>
            <div class="small text-center mt-3">
                <a href="{{ route('login') }}">Back to login</a>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);
                const icon = button.querySelector('i');
                const isHidden = input.type === 'password';

                input.type = isHidden ? 'text' : 'password';
                button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                button.setAttribute('title', isHidden ? 'Hide password' : 'Show password');
                icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });
    </script>
</body>
</html>
