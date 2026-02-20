<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('img/favicon.svg') }}" type="image/x-icon">
    <title>Register - Aurora Beauty Salon</title>

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

        .register-wrapper {
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

        .register-card {
            position: relative;
            z-index: 2;
            background: white;
            border-radius: 20px;
            padding: 50px 45px;
            max-width: 450px;
            width: 100%;
            margin-right: 80px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.15);
            max-height: 90vh;
            overflow-y: auto;
        }

        .header-section {
            margin-bottom: 35px;
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

        .signup-title {
            font-size: 36px;
            font-weight: 700;
            color: #000;
            margin: 0;
        }

        .login-link-top {
            position: absolute;
            top: 50px;
            right: 45px;
            text-align: right;
        }

        .login-link-top .text-muted {
            font-size: 12px;
            color: #999;
            display: block;
            margin-bottom: 2px;
        }

        .login-link-top a {
            font-size: 12px;
            color: #ff6b9d;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link-top a:hover {
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
            margin-bottom: 18px;
        }

        .row-input {
            display: flex;
            gap: 15px;
        }

        .row-input .col-input {
            flex: 1;
        }

        .btn-signup {
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
            margin-top: 10px;
        }

        .btn-signup:hover {
            background: linear-gradient(135deg, #ff6b85 0%, #ff8a9b 100%);
            box-shadow: 0 6px 20px rgba(255, 122, 148, 0.4);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        /* Custom Scrollbar */
        .register-card::-webkit-scrollbar {
            width: 6px;
        }

        .register-card::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .register-card::-webkit-scrollbar-thumb {
            background: #ff6b9d;
            border-radius: 10px;
        }

        .register-card::-webkit-scrollbar-thumb:hover {
            background: #ff5a8c;
        }

        @media (max-width: 992px) {
            .register-card {
                margin-right: 0;
                max-width: 500px;
            }
        }

        @media (max-width: 768px) {
            .register-wrapper {
                justify-content: center;
                padding: 20px;
            }

            .register-card {
                padding: 40px 30px;
                margin-right: 0;
            }

            .logo-overlay {
                width: 80px;
                top: 20px;
                left: 20px;
            }

            .login-link-top {
                position: static;
                text-align: center;
                margin-bottom: 20px;
            }

            .row-input {
                flex-direction: column;
                gap: 18px;
            }
        }
    </style>
</head>

<body>
    <div class="register-wrapper">
        <!-- Background Image -->
        <img src="{{ asset('img/Wallpaper_login.png') }}" alt="Salon Background" class="background-image">

        <!-- Logo Overlay -->
        <img src="{{ asset('img/LOGO.png') }}" alt="Aurora Logo" class="logo-overlay">

        <!-- Register Card -->
        <div class="register-card">
            <!-- Login Link Top Right -->
            <div class="login-link-top">
                <span class="text-muted">Sudah punya akun?</span>
                <a href="{{ route('login') }}">Masuk di sini</a>
            </div>

            <!-- Header -->
            <div class="header-section">
                <div class="welcome-text">Welcome to</div>
                <div class="salon-name">AURORA BEAUTY SALON</div>
                <h1 class="signup-title">Sign up</h1>
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

            <!-- Register Form -->
            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Masukan Email Aktif Anda</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                        placeholder="Email address" required autofocus>
                </div>

                <div class="row-input mb-3">
                    <div class="col-input">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama"
                            value="{{ old('nama') }}" placeholder="User name" required>
                    </div>

                    <div class="col-input">
                        <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" id="nomor_telepon" name="nomor_telepon"
                            value="{{ old('nomor_telepon') }}" placeholder="08..." required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Masukan Password Anda</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                        required>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Masukan Ulang Password Anda</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                        placeholder="Password" required>
                </div>

                <button type="submit" class="btn-signup">Sign up</button>
            </form>
        </div>
    </div>

    <!-- Modal Email Verifikasi -->
    <div class="modal fade" id="emailVerifyModal" tabindex="-1" aria-labelledby="emailVerifyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; padding: 30px 20px; text-align: center;">

                <!-- Tombol Back -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    style="position: absolute; top: 20px; left: 20px;"></button>

                <!-- Isi Modal -->
                <div class="modal-body">
                    <h4 style="font-weight: 700; color: #000; margin-top: 30px;">Email Verifikasi</h4>
                    <p id="emailText" style="color: #555; font-size: 14px; margin-top: 10px;">
                        Kami telah mengirimkan email verifikasi ke alamat email Anda.
                    </p>

                    <button id="confirmEmailBtn" class="btn mt-3"
                        style="background-color: #ff6b9d; color: white; border: none; border-radius: 8px; padding: 10px 35px; font-weight: 600;">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Tangkap form register
            const registerForm = document.querySelector('form');

            registerForm.addEventListener('submit', function(e) {
                // e.preventDefault();

                // Ambil email dari input
                const email = document.getElementById('email').value;

                // Tampilkan modal
                const emailText = document.getElementById('emailText');
                emailText.innerHTML =
                    `Kami telah mengirimkan email verifikasi ke alamat email <strong>${email}</strong>.`;

                const modal = new bootstrap.Modal(document.getElementById('emailVerifyModal'));
                modal.show();

                // Saat tombol Confirm diklik atau modal ditutup → redirect
                const confirmBtn = document.getElementById('confirmEmailBtn');
                confirmBtn.addEventListener('click', function() {
                    modal.hide();
                    window.location.href = "{{ route('login') }}"; // arahkan ke halaman login
                });

                document.getElementById('emailVerifyModal').addEventListener('hidden.bs.modal', function() {
                    window.location.href = "{{ route('login') }}";
                });
            });
        });
    </script>

</body>

</html>
