<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard - Aurora')</title>

    {{-- CSRF (opsional untuk akses via meta) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('img/favicon.svg') }}" type="image/x-icon">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- Chart.js (opsional) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Styles stack/section halaman --}}
    @yield('styles')
    @stack('styles')

    <style>
        /* === Global Style === */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fefcff;
        }

        /* === Navbar === */
        .navbar {
            background-color: #ffffff;
            border-bottom: 3px solid #f8d4e9;
            padding: 0.8rem 1rem;
        }

        .navbar-brand img { width: 75px; }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: #333;
            margin-right: 1rem;
        }

        .navbar-nav .nav-link.active {
            color: #d13a8a !important;
            font-weight: 600;
            border-bottom: 3px solid #d13a8a;
            margin-bottom: -3px;
        }

        .navbar-nav .nav-link:hover { color: #d13a8a; }

        /* === Tombol Profile (desktop) === */
        .profile-btn {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 50px;
            padding: 0.3rem 0.5rem;
            display: inline-flex;
            align-items: center;
        }
        .profile-icon {
            width: 30px; height: 30px; background-color: #f0f0f0;
            border-radius: 50%; margin-right: 0.5rem; display: inline-block;
        }

        /* === Card === */
        .card {
            border: 1px solid #f8d4e9;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            height: 100%;
        }
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f8d4e9;
            font-weight: 600;
            color: #333;
            padding: 1rem 1.25rem;
        }
        .card-body { padding: 1.25rem; }

        /* === Statistik Card === */
        .stat-card {
            background-color: #fff; border: 1px solid #f8d4e9; border-radius: 8px;
            padding: 1rem; text-align: left; height: 100%;
        }
        .stat-card .value { font-size: 1.75rem; font-weight: 700; color: #d13a8a; }
        .stat-card .value-small { font-size: 1.5rem; }
        .stat-card .label { font-size: 0.9rem; color: #555; font-weight: 500; }

        /* === Tabel === */
        .table { font-size: 0.9rem; vertical-align: middle; }
        .table thead th { font-weight: 600; color: #555; border-bottom-width: 1px; }
        .table-hover tbody tr:hover { background-color: #fefcff; }

        /* Header user di dropdown */
        .dropdown-menu .small { opacity: .9; }

        /* Mobile profile block */
        .mobile-profile {
            border: 1px solid #eee; border-radius: 8px; background: #fff;
        }

        /* === Notification Dropdown === */
    .icon-button {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      color: #333;
      border: none;
      background: transparent;
      transition: background 0.2s;
    }
    .icon-button:hover, .icon-button.show {
      background-color: #fce4ec;
      color: #d13a8a;
    }
    .icon-button__badge {
      position: absolute;
      top: -2px;
      right: -2px;
      width: 18px;
      height: 18px;
      background: #e91e63;
      color: #ffffff;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 50%;
      font-size: 10px;
      font-weight: 700;
      border: 2px solid #fff;
    }
    .notification-dropdown {
      width: 320px;
      padding: 0;
      border: 1px solid #f8d4e9;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      border-radius: 12px;
      overflow: hidden;
    }
    .notification-header {
      padding: 12px 16px;
      border-bottom: 1px solid #f0f0f0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #fff;
    }
    .notification-header h6 { margin: 0; font-weight: 700; font-size: 14px; }
    .mark-all-read { font-size: 11px; color: #d13a8a; text-decoration: none; cursor: pointer; }
    .mark-all-read:hover { text-decoration: underline; }
    .notification-list {
      max-height: 300px;
      overflow-y: auto;
    }
    .notification-item {
      padding: 12px 16px;
      border-bottom: 1px solid #f9f9f9;
      display: flex;
      gap: 12px;
      transition: background 0.2s;
      text-decoration: none;
      color: #333;
      position: relative;
    }
    .notification-item:hover { background: #fdf2f8; color: #333; }
    .notification-item.unread { background: #fff5f9; }
    .notification-icon {
      width: 36px; height: 36px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      background: #fce4ec; color: #d13a8a; flex-shrink: 0;
    }
    .notification-content { flex: 1; }
    .notification-title { font-size: 13px; font-weight: 600; margin-bottom: 2px; display: block; }
    .notification-msg { font-size: 12px; color: #666; display: block; line-height: 1.3; }
    .notification-time { font-size: 10px; color: #999; margin-top: 4px; display: block; }
    .notification-empty { padding: 20px; text-align: center; color: #999; font-size: 13px; font-style: italic; }
    .unread-dot {
      width: 8px; height: 8px; background: #e91e63; border-radius: 50%;
      position: absolute; top: 16px; right: 12px;
    }

    /* === Responsif === */
    @media (max-width: 768px) {
        .navbar-nav { padding-top: 1rem; }
    }    }
    </style>
</head>

<body>
@php
    $isLaporan = request()->routeIs('laporan.*');
    $isDataMaster = request()->routeIs('kategori-layanan.*')
        || request()->routeIs('layanan.*')
        || request()->routeIs('metode-pembayaran.*')
        || request()->routeIs('pelanggan.data_pelanggan')
        || request()->routeIs('slot-jadwal.*');
@endphp

<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
        <img src="{{ asset('img/LOGO.png') }}" alt="Aurora Logo">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
            aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Left Navigation -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
             aria-current="page" href="{{ route('admin.dashboard') }}">Dashboard</a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('booking.*') ? 'active' : '' }}"
             href="{{ route('booking.index') }}">Booking</a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('pelanggan.index') ? 'active' : '' }}"
             href="{{ route('pelanggan.index') }}">Pelanggan</a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.konfirmasi-pembatalan.index') ? 'active' : '' }}"
             href="{{ route('admin.konfirmasi-pembatalan.index') }}">Pembatalan Booking</a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.pos.index') ? 'active' : '' }}"
             href="{{ route('admin.pos.index') }}">Booking Offline</a>
        </li>
        <!-- Dropdown: Laporan -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ $isLaporan ? 'active' : '' }}"
             href="#" id="navbarDropdownLaporan" role="button" data-bs-toggle="dropdown"
             aria-expanded="false">
            Laporan
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownLaporan">
            <li>
              <a class="dropdown-item {{ request()->routeIs('laporan.pendapatan') ? 'active' : '' }}"
                 href="{{ route('laporan.pendapatan') }}">Laporan Pendapatan</a>
            </li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('laporan.transaksi') ? 'active' : '' }}"
                 href="{{ route('laporan.transaksi') }}">Laporan Transaksi</a>
            </li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('laporan.rata_rata') ? 'active' : '' }}"
                 href="{{ route('laporan.rata_rata') }}">Laporan Rata-Rata</a>
            </li>
          </ul>
        </li>

        <!-- Dropdown: Data Master -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ $isDataMaster ? 'active' : '' }}"
             href="#" id="navbarDropdownDataMaster" role="button" data-bs-toggle="dropdown"
             aria-expanded="false">
            Data Master
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownDataMaster">
            <li><a class="dropdown-item {{ request()->routeIs('kategori-layanan.*') ? 'active' : '' }}"
                   href="{{ route('kategori-layanan.index') }}">Kategori Layanan</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('layanan.*') ? 'active' : '' }}"
                   href="{{ route('layanan.index') }}">Layanan</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('diskon.*') ? 'active' : '' }}"
                   href="{{ route('diskon.index') }}">Diskon</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('metode-pembayaran.*') ? 'active' : '' }}"
                   href="{{ route('metode-pembayaran.index') }}">Metode Pembayaran</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('pelanggan.data_pelanggan') ? 'active' : '' }}"
                   href="{{ route('pelanggan.data_pelanggan') }}">Pelanggan</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('slot-jadwal.*') ? 'active' : '' }}"
                   href="{{ route('slot-jadwal.index') }}">Slot Jadwal</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('admin.index.*') ? 'active' : '' }}"
                   href="{{ route('admin.index') }}">Daftar Admin</a></li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('contacts.index') ? 'active' : '' }}"
                href="{{ route('contacts.index') }}">Pesan & Testimoni</a>
            </li>
          </ul>
        </li>
      </ul>

      <!-- Right Navigation -->
      <div class="d-flex align-items-center gap-2">

        <!-- Notification Bell -->
        <div class="dropdown">
          <button class="icon-button" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
             <i class="fas fa-bell" style="font-size: 18px;"></i>
             <span class="icon-button__badge" id="notif-badge" style="display: none;">0</span>
          </button>
          <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notifDropdown">
             <div class="notification-header">
                <h6>Notifikasi</h6>
                <a href="#" class="mark-all-read" onclick="markAllNotificationsRead(event)">Tandai semua dibaca</a>
             </div>
             <div class="notification-list" id="notification-list">
                <div class="notification-empty">Memuat...</div>
             </div>
          </div>
        </div>

        <!-- Profile (DESKTOP): dropdown -->
        <div class="dropdown d-none d-lg-block">
          <button class="btn profile-btn dropdown-toggle"
                  type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  <img class="profile-icon" src="{{ asset('img/avatar.jpg') }}" alt="">
            <span>Profile</span>
          </button>

          <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="profileDropdown" style="min-width: 260px;">
            <li class="px-3 py-2">
              @auth
                <div class="fw-semibold text-truncate">{{ Auth::user()->name }}</div>
                <div class="small text-muted text-truncate">{{ Auth::user()->email }}</div>
              @else
                <div class="fw-semibold">Guest</div>
                <div class="small text-muted">—</div>
              @endauth
            </li>
            <li><hr class="dropdown-divider"></li>
            @auth
              <li class="px-3 pb-2">
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="btn btn-outline-danger w-100">Logout</button>
                </form>
              </li>
            @else
              <li><a class="dropdown-item" href="{{ route('login') }}">Login</a></li>
            @endauth
          </ul>
        </div>
      </div>

      <!-- Profile (MOBILE): tanpa dropdown, muncul sebagai blok saat hamburger terbuka) -->
      <div class="d-lg-none w-100 mt-3">
        <div class="mobile-profile p-3">
          @auth
            <div class="fw-semibold text-truncate">{{ Auth::user()->name }}</div>
            <div class="small text-muted text-truncate mb-2">{{ Auth::user()->email }}</div>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="btn btn-outline-danger w-100">Logout</button>
            </form>
          @else
            <a href="{{ route('login') }}" class="btn btn-primary w-100">Login</a>
          @endauth
        </div>
      </div>
    </div>
  </div>
</nav>

{{-- Konten halaman --}}
@yield('content')

{{-- JS Global --}}
<!-- Select2 JS (setelah jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // NOTIFICATION SYSTEM
    document.addEventListener('DOMContentLoaded', function() {
        fetchNotifications();
        // Poll every 60 seconds
        setInterval(fetchNotifications, 60000);
    });

    function fetchNotifications() {
        fetch('{{ route('admin.notifications.get') }}')
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    updateNotificationUI(data.unread_count, data.notifications);
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    function updateNotificationUI(count, notifications) {
        const badge = document.getElementById('notif-badge');
        const list = document.getElementById('notification-list');

        // Update Badge
        if(count > 0) {
            badge.style.display = 'flex';
            badge.textContent = count > 99 ? '99+' : count;
        } else {
            badge.style.display = 'none';
        }

        // Update List
        list.innerHTML = '';
        if(notifications.length === 0) {
            list.innerHTML = '<div class="notification-empty">Tidak ada notifikasi baru</div>';
            return;
        }

        notifications.forEach(notif => {
            const isUnread = !notif.read_at;
            const item = document.createElement('a');
            item.href = notif.data.link || '#';
            item.className = `notification-item ${isUnread ? 'unread' : ''}`;
            item.onclick = (e) => handleNotificationClick(e, notif.id, notif.data.link);

            item.innerHTML = `
                <div class="notification-icon">
                    <i class="${notif.data.icon || 'fas fa-bell'}"></i>
                </div>
                <div class="notification-content">
                    <span class="notification-title">${notif.data.title}</span>
                    <span class="notification-msg">${notif.data.message}</span>
                    <span class="notification-time">${notif.created_at_human}</span>
                </div>
                ${isUnread ? '<div class="unread-dot"></div>' : ''}
            `;
            list.appendChild(item);
        });
    }

    function handleNotificationClick(event, id, link) {
        event.preventDefault(); // Prevent immediate navigation
        
        // Mark as read
        fetch(`{{ url('/notifications/mark-read') }}/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        }).then(() => {
            // Fetch updated list (optional, or just navigate)
             window.location.href = link;
        });
    }

    function markAllNotificationsRead(event) {
        event.preventDefault();
        
        fetch('{{ route('admin.notifications.markAllRead') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                fetchNotifications(); // Refresh UI
            }
        });
    }
</script>

{{-- Scripts stack/section halaman --}}
@yield('scripts')
@stack('scripts')
</body>
</html>
