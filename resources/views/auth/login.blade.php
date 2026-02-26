<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dakoss Global POS</title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/icons/favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/icons/apple-touch-icon.png') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --dakoss-primary: #1a472a;
            --dakoss-secondary: #f39200;
        }

        body {
            background: linear-gradient(135deg, var(--dakoss-primary) 0%, #0f3a1f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, var(--dakoss-primary) 0%, #0f3a1f 100%);
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .login-logo {
            max-height: 60px;
            margin-bottom: 1rem;
        }

        .login-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, #fff 0%, var(--dakoss-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-subtitle {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
            margin-top: 0.5rem;
        }

        .login-body {
            padding: 2rem 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--dakoss-primary);
            margin-bottom: 0.6rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--dakoss-primary);
            box-shadow: 0 0 0 0.2rem rgba(26, 71, 42, 0.25);
        }

        .form-control::placeholder {
            color: #999;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--dakoss-primary) 0%, #0f3a1f 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.85rem;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(26, 71, 42, 0.2);
            color: white;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .login-footer {
            text-align: center;
            padding: 1.5rem;
            color: #666;
            font-size: 0.9rem;
        }

        .login-footer a {
            color: var(--dakoss-secondary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .demo-credentials {
            background: #f8f9fa;
            border-left: 4px solid var(--dakoss-secondary);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            font-size: 0.85rem;
        }

        .demo-credentials strong {
            display: block;
            color: var(--dakoss-primary);
            margin-bottom: 0.5rem;
        }

        .demo-credentials div {
            margin: 0.3rem 0;
            color: #666;
        }

        .loading {
            display: none;
        }

        .btn-login.is-loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-login.is-loading .spinner-border {
            display: inline-block;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <img src="{{ asset('assets/images/logo.svg') }}" alt="Dakoss Global" class="login-logo">

            </div>

            <!-- Body -->
            <div class="login-body">
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-circle"></i>
                        <strong>Login Failed!</strong>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @if (session('message'))
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i> {{ session('message') }}
                    </div>
                @endif

                <div id="errorAlert" class="alert alert-danger" role="alert" style="display: none;">
                    <i class="bi bi-exclamation-circle"></i>
                    <strong>Login Failed!</strong>
                    <div id="errorMessage"></div>
                </div>

                <form id="loginForm" method="POST" action="/login">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-person"></i> Username
                        </label>
                        <input type="text" name="username" class="form-control" placeholder="Enter your username" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-lock"></i> Password
                        </label>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn-login">
                        <span class="loading">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Logging in...
                        </span>
                        <span class="btn-text"><i class="bi bi-box-arrow-in-right"></i> Login</span>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="login-footer">
                <p class="mb-0">© {{ date('Y') }} <strong>Dakoss Global Nigeria Limited</strong></p>
                <small>Dakoss Global POS v1.0</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.querySelector('.btn-login');
            const loading = btn.querySelector('.loading');
            const text = btn.querySelector('.btn-text');

            btn.classList.add('is-loading');
            loading.style.display = 'inline';
            text.style.display = 'none';
            btn.disabled = true;
        });
    </script>
</body>
</html>
