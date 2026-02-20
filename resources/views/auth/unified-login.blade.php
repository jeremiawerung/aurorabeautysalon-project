<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('img/favicon.svg') }}" type="image/x-icon">
    <title>Login - Aurora Beauty Salon</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            overflow: hidden;
        }

        .login-wrapper {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 20px;
        }

        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }

        .logo-overlay {
            position: absolute;
            top: 30px;
            left: 30px;
            z-index: 2;
            width: 120px;
            height: auto;
        }

        .login-card {
            position: relative;
            z-index: 2;
            background: white;
            border-radius: 20px;
            padding: 50px 45px;
            max-width: 450px;
            width: 100%;
            margin-right: 80px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.15);
        }

        .header-section {
            margin-bottom: 40px;
        }

        .welcome-text {
            font-size: 13px;
            color: #666;
            margin-bottom: 3px;
        }

        .salon-name {
            font-size: 13px;
            color: #ff6b9d;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
        }

        .signin-title {
            font-size: 36px;
            font-weight: 700;
            color: #000;
            margin: 0;
        }

        .register-link-top {
            position: absolute;
            top: 50px;
            right: 45px;
            text-align: right;
        }

        .register-link-top .text-muted {
            font-size: 12px;l
            color: #999;
            display: block;
            margin-bottom: 2px;
        }

        .register-link-top a {
            font-size: 12px;
            color: #ff6b9d;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link-top a:hover {
            text-decoration: underline;
        }

        .form-label {
            font-size: 13px;
            color: #333;
            font-weight: 500;
            margin-bottom: 10px;
            display: block;
        }

        .form-control {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 14px;
            transition: all 0.3s;
            width: 100%;
        }

        .form-control:focus {
            border-color: #ff6b9d;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 157, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #bbb;
            font-size: 13px;
        }

        .mb-3 {
            margin-bottom: 20px;
        }

        .forgot-password {
            text-align: right;
            margin-top: 12px;
            margin-bottom: 30px;
        }

        .forgot-password a {
            color: #4a9eff;
            text-decoration: none;
            font-size: 13px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .btn-signin {
            width: 100%;
            background: linear-gradient(135deg, #ff7a94 0%, #ff9bac 100%);
            border: none;
            border-radius: 8px;
            padding: 15px;
            color: white;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 122, 148, 0.3);
        }

        .btn-signin:hover {
            background: linear-gradient(135deg, #ff6b85 0%, #ff8a9b 100%);
            box-shadow: 0 6px 20px rgba(255, 122, 148, 0.4);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        @media (max-width: 992px) {
            .login-card {
                margin-right: 0;
                max-width: 500px;
            }
        }

        @media (max-width: 768px) {
            .login-wrapper {
                justify-content: center;
                padding: 20px;
            }

            .login-card {
                padding: 40px 30px;
                margin-right: 0;
            }

            .logo-overlay {
                width: 80px;
                top: 20px;
                left: 20px;
            }

            .register-link-top {
                position: static;
                text-align: center;
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <!-- Background Image -->
        <img src="{{ asset('img/Wallpaper_login.png') }}" alt="Salon Background" class="background-image">

        <!-- Logo Overlay -->
        <img src="{{ asset('img/LOGO.png') }}" alt="Aurora Logo" class="logo-overlay">

        <!-- Login Card -->
        <div class="login-card">
            <!-- Register Link Top Right -->
            <div class="register-link-top">
                <span class="text-muted">Belum punya akun?</span>
                <a href="{{ route('register') }}">Daftar di sini</a>
            </div>

            <!-- Header -->
            <div class="header-section">
                <div class="welcome-text">Welcome to</div>
                <div class="salon-name">AURORA BEAUTY SALON</div>
                <h1 class="signin-title">Sign in</h1>
            </div>

            <!-- Alerts -->
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Masukan Email Terdaftar</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                        placeholder="@gmail.com" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Masukan Password Anda</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                        required>
                </div>

                <div class="forgot-password">
                    <a href="{{ route('password.request') }}">Lupa Password?</a>
                </div>

                <button type="submit" class="btn-signin">Sign In</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
