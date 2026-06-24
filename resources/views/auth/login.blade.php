<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - TIL Performance Appraisal System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --theme-primary: #1a6b3b;
            --theme-secondary: #2d9a56;
            --theme-accent: #e9f5ee;
            --theme-surface: #f7fbf8;
            --theme-border: rgba(26, 107, 59, 0.15);
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(45, 154, 86, 0.22), transparent 30%),
                radial-gradient(circle at bottom right, rgba(26, 107, 59, 0.18), transparent 28%),
                linear-gradient(135deg, #eff8f2 0%, #dff1e6 48%, #cbe7d5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-card {
            max-width: 450px;
            width: 100%;
            background: rgba(255, 255, 255, 0.94);
            border-radius: 18px;
            border: 1px solid var(--theme-border);
            box-shadow: 0 22px 60px rgba(26, 107, 59, 0.16);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-secondary) 100%);
            color: white;
            padding: 32px 30px;
            text-align: center;
        }

        .login-body {
            padding: 32px 30px;
        }

        .form-control:focus {
            border-color: var(--theme-primary);
            box-shadow: 0 0 0 0.2rem rgba(26, 107, 59, 0.18);
        }

        .btn-outline-primary {
            background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-secondary) 100%);
            color: #fff;
            border: 0;
            box-shadow: 0 10px 24px rgba(26, 107, 59, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #166036 0%, #238a4a 100%);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(26, 107, 59, 0.26);
        }

        .text-link-theme {
            color: var(--theme-primary);
            font-weight: 600;
        }

        .text-link-theme:hover {
            color: var(--theme-secondary);
        }

        .form-check-input:checked {
            background-color: var(--theme-primary);
            border-color: var(--theme-primary);
        }

        .alert-success {
            background-color: var(--theme-accent);
            border-color: rgba(26, 107, 59, 0.15);
            color: var(--theme-primary);
        }

        .card-note {
            color: #5b6e61;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <h3 class="mb-0">TIL Performance Appraisal</h3>
            <p class="mb-0 mt-2">Tosrifa Industries Limited</p>
        </div>
        <div class="login-body">
            @if (session('status'))
                <div class="alert alert-success mb-3">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                        required autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required
                        autocomplete="current-password">
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-outline-primary btn-lg">Log In</button>
                </div>

                <div class="text-center">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none text-link-theme">Forgot
                            your password?</a>
                    @endif
                </div>
            </form>

            <hr class="my-4">
            <p class="text-center text-muted mb-0 card-note">
                <small>For new employees, please contact HR Admin to create your account.</small>
            </p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
