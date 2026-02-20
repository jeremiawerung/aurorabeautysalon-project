<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('img/favicon.svg') }}" type="image/x-icon">
    <title>Email Verification - Aurora Beauty Salon</title>

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

        .verify-wrapper {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
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

        .verify-card {
            position: relative;
            z-index: 2;
            background: white;
            border-radius: 20px;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-circle {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #ff7a94 0%, #ff9bac 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 4px 15px rgba(255, 122, 148, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .icon-circle svg {
            width: 36px;
            height: 36px;
            color: white;
        }

        .verify-title {
            font-size: 32px;
            font-weight: 700;
            color: #000;
            text-align: center;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .verify-subtitle {
            font-size: 14px;
            color: #666;
            text-align: center;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .email-badge {
            display: inline-block;
            background: linear-gradient(135deg, #fff0f5 0%, #ffe8f0 100%);
            color: #ff6b9d;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 20px;
            margin: 0 auto 28px;
            display: block;
            width: fit-content;
            border: 1px solid #ffcce0;
        }

        .alert {
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13px;
            border: none;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .info-card {
            background: linear-gradient(135deg, #fff5f8 0%, #ffe8f0 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid #ffcce0;
        }

        .info-card h3 {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-list li {
            font-size: 13px;
            color: #555;
            padding: 6px 0;
            padding-left: 24px;
            position: relative;
            line-height: 1.5;
        }

        .info-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #ff6b9d;
            font-weight: bold;
            font-size: 14px;
        }

        .btn-resend {
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
            margin-bottom: 12px;
        }

        .btn-resend:hover {
            background: linear-gradient(135deg, #ff6b85 0%, #ff8a9b 100%);
            box-shadow: 0 6px 20px rgba(255, 122, 148, 0.4);
            transform: translateY(-2px);
        }

        .btn-resend:active {
            transform: translateY(0);
        }

        .btn-logout {
            background: none;
            border: none;
            color: #999;
            font-size: 13px;
            text-decoration: underline;
            cursor: pointer;
            transition: color 0.2s;
            padding: 8px;
        }

        .btn-logout:hover {
            color: #666;
        }

        .divider {
            text-align: center;
            margin: 16px 0 0;
        }

        @media (max-width: 768px) {
            .verify-card {
                padding: 40px 30px;
                border-radius: 16px;
            }

            .logo-overlay {
                width: 80px;
                top: 20px;
                left: 20px;
            }

            .verify-title {
                font-size: 28px;
            }

            .icon-circle {
                width: 64px;
                height: 64px;
            }

            .icon-circle svg {
                width: 32px;
                height: 32px;
            }
        }

        @media (max-width: 380px) {
            .verify-card {
                padding: 32px 24px;
            }

            .verify-title {
                font-size: 24px;
            }

            .email-badge {
                font-size: 13px;
                padding: 6px 12px;
            }

            .info-card {
                padding: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="verify-wrapper">
        <!-- Background Image -->
        <img src="{{ asset('img/Wallpaper_login.png') }}" alt="Salon Background" class="background-image">

        <!-- Logo Overlay -->
        <img src="{{ asset('img/LOGO.png') }}" alt="Aurora Logo" class="logo-overlay">

        <!-- Verify Card -->
        <div class="verify-card">
            {{-- <!-- Email Icon -->
            <div class="icon-circle">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div> --}}

            <!-- Title -->
            <h1 class="verify-title">Verifikasi Email</h1>
            <p class="verify-subtitle">Email verifikasi telah dikirim ke</p>
            <div class="email-badge">{{ Auth::user()->email }}</div>

            <!-- Success Messages -->
            @if (session('message'))
                <div class="alert alert-success">
                    ✓ {{ session('message') }}
                </div>
            @endif

            @if (session('resent') || session('status') == 'verification-link-sent')
                <div class="alert alert-success">
                    ✓ Tautan verifikasi baru telah dikirim
                </div>
            @endif

            <!-- Info Card -->
            <div class="info-card">
                <h3>
                    <span>📬</span>
                    Langkah Selanjutnya
                </h3>
                <ul class="info-list">
                    <li>Buka inbox email Anda</li>
                    <li>Cek folder Spam jika tidak ada</li>
                    <li>Klik link verifikasi dalam email</li>
                </ul>
            </div>

            <!-- Resend Button -->
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn-resend">
                    Kirim Ulang Email
                </button>
            </form>

            <!-- Logout -->
            <div class="divider">
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-logout">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
