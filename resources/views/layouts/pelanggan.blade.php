<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">   <!-- CSRF TOKEN -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Beauty Salon</title>

    {{-- Icons & CSS --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> --}}
    <link rel="stylesheet" href="{{ asset('css/pelanggan.css') }}">
    <link rel="shortcut icon" href="{{ asset('img/favicon.svg') }}" type="image/x-icon">

    {{-- ① Load fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
      rel="stylesheet">

    {{-- ② Global font rules --}}
    <style>
      :root{
        --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";
        --font-serif: 'Playfair Display', Georgia, "Times New Roman", Times, serif;
      }

      /* default: semua pakai Plus Jakarta Sans */
      html, body, button, input, select, textarea {
        font-family: var(--font-sans);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
      }

      /* heading h1 & h2 pakai Playfair Display */
      h1, h2, .h1, .h2 {
        font-family: var(--font-serif);
        font-weight: 700;           /* feel free: 600/700 sesuai selera */
        letter-spacing: -0.01em;
        line-height: 1.15;
      }

      /* (opsional) pastikan judul-judul kustom ikut heading look */
      .hero-title,
      .experience-title,
      .about-hero-title,
      .about-values-title,
      .about-story-title,
      .about-journey-title,
      .recommendations-title {
        font-family: var(--font-serif);
        font-weight: 700;
      }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
</head>

<body>

    <x-nav-pelanggan />

    @yield('content')

    <footer class="footer">
      <div class="footer-content">
        <div class="footer-left">
          <img src="{{ asset('img/LOGO.png') }}" alt="Aurora Logo" class="footer-logo">
        </div>

        <div class="footer-center">
          <div class="footer-column footer-hours-col">
            <h4>{{ __('footer.hours.title') }}</h4>
            <ul class="hours-list">
              <li>{{ __('footer.hours.mon_thu_fri') }}</li>
              <li>{{ __('footer.hours.sat') }}</li>
              <li>{{ __('footer.hours.sun') }}</li>
            </ul>
          </div>

          <!-- 1) Jelajahi -->
          <div class="footer-column">
            <h4>{{ __('footer.explore.title') }}</h4>
            <ul>
              <li><a href="{{ route('welcome') }}">{{ __('footer.explore.home') }}</a></li>
              <li><a href="{{ route('tentang') }}">{{ __('footer.explore.about') }}</a></li>
              <li><a href="{{ route('daftar-layanan.index') }}">{{ __('footer.explore.services') }}</a></li>
              <li><a href="{{ route('kontak') }}">{{ __('footer.explore.contact') }}</a></li>
            </ul>
          </div>

          <!-- 4) Kontak Kami -->
          <div class="footer-column">
            <h4>{{ __('footer.contact.title') }}</h4>
            <ul>
              <li><i class="fas fa-map-marker-alt"></i> Jl. Siliwangi, Kotabangon, Kec. Kotamobagu Tim., Kota Kotamobagu, 95716</li>
              <li><i class="fas fa-phone"></i> (+62)811-437-065</li>
            </ul>
          </div>
        </div>

        <div class="footer-right">
          <div class="social-icons">
            <a href="https://www.facebook.com/share/1EUCfkrCDn/?mibextid=wwXIfr"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/aurora.beauty.salon.1?igsh=MTQyeG05bzgyYmtrdA=="><i class="fab fa-instagram"></i></a>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© {{ date('Y') }}, Aurora | Beauty Salon | {{ __('footer.bottom.rights') }}</p>
      </div>
    </footer>

    @php
        // I18N untuk teks SweetAlert (logout) di layout
        $layoutI18n = [
            'logout' => [
                'title'   => __('footer.logout.title'),
                'text'    => __('footer.logout.text'),
                'confirm' => __('footer.logout.confirm'),
                'cancel'  => __('footer.logout.cancel'),
                'alt'     => __('footer.logout.alt'),
            ],
        ];
    @endphp
    <script>
        const I18N_LAYOUT = @json($layoutI18n);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <script>
        function toggleMenu() {
            const navMenu = document.getElementById('navMenu');
            navMenu.classList.toggle('active');
        }

        function toggleLanguage(event) {
            event.stopPropagation();
            const dropdown = event.target.closest('.language-dropdown');
            dropdown.classList.toggle('active');
        }

        function toggleProfile(event) {
            event.stopPropagation();
            const profileDropdown = document.getElementById('profileDropdown');
            const profileOverlay = document.getElementById('profileOverlay');
            profileDropdown.classList.toggle('active');
            profileOverlay.classList.toggle('active');

            if (profileDropdown.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        function closeProfile() {
            const profileDropdown = document.getElementById('profileDropdown');
            const profileOverlay = document.getElementById('profileOverlay');
            profileDropdown.classList.remove('active');
            profileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        function confirmLogout() {
            Swal.fire({
                title: I18N_LAYOUT.logout.title,
                text: I18N_LAYOUT.logout.text,
                imageUrl: "{{ asset('img/icon-question.png') }}",
                imageWidth: 80,
                imageHeight: 80,
                imageAlt: I18N_LAYOUT.logout.alt,
                showCancelButton: true,
                confirmButtonText: I18N_LAYOUT.logout.confirm,
                cancelButtonText: I18N_LAYOUT.logout.cancel,
                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    popup: 'swal-custom-popup',
                    title: 'swal-custom-title',
                    htmlContainer: 'swal-custom-text',
                    confirmButton: 'swal-btn-confirm',
                    cancelButton: 'swal-btn-cancel',
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }

        // Tengahkan tombol setelah swal muncul
        setTimeout(() => {
            const swalButtons = document.querySelector('.swal-footer');
            if (swalButtons) {
                swalButtons.style.textAlign = 'center';
            }
        }, 100);

        function scrollRecommendations(direction) {
            const grid = document.getElementById('recommendationsGrid');
            const scrollAmount = 320;
            if (grid) {
                grid.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
            }
        }

        // Close menus when clicking outside
        document.addEventListener('click', function(event) {
            const languageDropdown = document.querySelector('.language-dropdown');
            if (languageDropdown && !languageDropdown.contains(event.target)) {
                languageDropdown.classList.remove('active');
            }
        });
    </script>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        // Panggil hanya kalau user sudah login (halaman auth)
        fetch("{{ route('ajax.check.reservasi') }}", {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({})
        }).catch(err => console.debug('cek reservasi gagal', err));
      });
    </script>


    <!-- Hidden Logout Form -->
    @auth
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    @endauth

    @stack('scripts')
</body>
</html>
