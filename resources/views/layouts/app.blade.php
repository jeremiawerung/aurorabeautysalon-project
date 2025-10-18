<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <!-- Add Bootstrap CSS link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom Styles for Navbar */
        .navbar {
            background-color: #f8c8e6;  /* Pink background */
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
        }

        .navbar-nav .nav-link {
            color: #000000 !important;
        }

        .navbar-nav .nav-link:hover {
            color: #5a5a5a !important;
        }

        .profile-btn {
            background-color: #fff;
            color: #000;
            border: 1px solid #ccc;
            padding: 8px 15px;
            border-radius: 20px;
        }

        .profile-btn:hover {
            background-color: #e0e0e0;
        }

        .logout-btn {
            background-color: #dc3545;
            color: #fff;
            border: 1px solid #dc3545;
            padding: 8px 15px;
            border-radius: 20px;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="logo.png" alt="Logo"> <!-- Ganti logo.png dengan logo Anda -->
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="/admin/dashboard">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Booking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Customer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Reports</a>
                    </li>

                    <!-- Dropdown for Data Master -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Data Master
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="{{ route('layanan.index') }}">Layanan</a></li>  
                            <li><a class="dropdown-item" href="{{ route('slot-jadwal.index') }}">Slot Jadwal Layanan</a></li>
                            <li><a class="dropdown-item" href="{{ route('kategori-layanan.index') }}">Kategori Layanan</a></li>
                            <li><a class="dropdown-item" href="{{ route('pelanggan.index') }}">Pelanggan</a></li> <!-- Update link untuk Master Data Pelanggan -->
                            <li><a class="dropdown-item" href="{{ route('metode-pembayaran.index') }}">Metode Pembayaran</a></li> <!-- Link ke Master Data Metode Pembayaran -->
                            <li><a class="dropdown-item" href="{{ route('diskon.index') }}">Diskon</a></li> <!-- Link ke Master Data Diskon -->
                            <li><a class="dropdown-item" href="/admin">Admin Management</a></li> <!-- Link ke halaman daftar admin -->
                        </ul>
                    </li>
                </ul>
                <div class="d-flex">
                    <!-- Tombol Logout yang mengarah ke route admin.logout -->
                    <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn logout-btn me-2">Logout</button>
                    </form>

                    <button class="btn profile-btn" type="button">Profile</button>
                </div>
            </div>
        </div>
    </nav>


    <!-- Konten Halaman -->
    <div class="container mt-4">
        @yield('content')
    </div>

    <!-- Add Bootstrap JS link -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
