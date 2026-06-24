<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - TIL Performance Appraisal System</title>
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

        .reset-card {
            max-width: 500px;
            width: 100%;
            background: rgba(255, 255, 255, 0.94);
            border-radius: 18px;
            border: 1px solid var(--theme-border);
            box-shadow: 0 22px 60px rgba(26, 107, 59, 0.16);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .reset-header {
            background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-secondary) 100%);
            color: white;
            padding: 32px 30px;
            text-align: center;
        }

        .reset-body {
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

        .alert-info {
            background-color: var(--theme-accent);
            border-color: rgba(26, 107, 59, 0.15);
            color: #315743;
        }

        .text-link-theme {
            color: var(--theme-primary);
            font-weight: 600;
        }

        .text-link-theme:hover {
            color: var(--theme-secondary);
        }
    </style>
</head>

<body>
    <div class="reset-card">
        <div class="reset-header">
            <h3 class="mb-0">Reset Password</h3>
            <p class="mb-0 mt-2">TIL Performance Appraisal System</p>
        </div>
        <div class="reset-body">
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

            <div class="alert alert-info mb-3">
                <small>Forgot your password? No problem. Enter your email address and we will send you a password reset
                    link.</small>
            </div>

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                        required autofocus>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-outline-primary btn-lg">Send Password Reset Link</button>
                </div>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-decoration-none text-link-theme">← Back to Login</a>
                </div>
            </form>

            <hr class="my-4">
            <p class="text-center text-muted mb-0">
                <small>If you don't receive an email, please contact HR Admin for assistance.</small>
            </p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
