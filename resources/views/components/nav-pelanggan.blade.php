<!-- Navigation -->
    <nav>
        <div class="logo">
            <img src="{{ asset('img/LOGO.png') }}" alt="Aurora Beauty Salon Logo">
        </div>
        <div class="menu-toggle" onclick="toggleProfile(event)">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="nav-right" id="navMenu">
            <ul class="nav-links">
                <li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">{{ __('nav.home') }}</a></li>
                <li><a href="{{ url('/about') }}" class="{{ request()->is('about') ? 'active' : '' }}">{{ __('nav.about') }}</a></li>
                <li><a href="{{ url('/contact') }}" class="{{ request()->is('contact') ? 'active' : '' }}">{{ __('nav.contact') }}</a></li>
                <li class="language-dropdown">
                    <button class="language-btn" onclick="toggleLanguage(event)">
                        {{ strtoupper(app()->getLocale()) }} <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="{{ route('lang.switch', 'id') }}">ID</a>
                        <a href="{{ route('lang.switch', 'en') }}">ENG</a>
                    </div>
                </li>
            </ul>
            <div class="nav-buttons">
                @guest
                    <a href="{{ route('login') }}" class="btn-login">{{ __('nav.sign_in') }}</a>
                    <a href="{{ route('register') }}" class="btn-signup">{{ __('nav.sign_up') }}</a>
                @else
                    <button class="btn-signup" onclick="window.location.href='{{ route('booking.categories') }}'">
                        {{ __('nav.booking') }}
                    </button>
                    <button class="btn-login profile-btn" onclick="toggleProfile(event)">
                        <i class="fas fa-user"></i>
                    </button>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Profile Overlay -->
    <div class="profile-overlay" id="profileOverlay" onclick="closeProfile()"></div>

    <!-- Profile Panel Sidebar -->
    <div class="profile-dropdown" id="profileDropdown">
        <button class="profile-close" onclick="closeProfile()">×</button>

        @auth
            <!-- Logged In State -->
            <div class="profile-header">
                <div class="profile-header-content">
                    @if (Auth::user()->avatar)
                        <img src="{{ Auth::user()->avatar }}" alt="Profile Picture" class="profile-avatar">
                    @else
                        <i class="far fa-user-circle"></i>
                    @endif
                    <h3>{{ Auth::user()->name ?? __('nav.profile') }}</h3>
                </div>
            </div>

            <div class="profile-menu">
                <a href="{{ route('booking.history') }}" class="profile-menu-item">
                    <i class="far fa-calendar-check"></i>
                    <span>{{ __('nav.booking_list') }}</span>
                </a>
                <a href="{{ route('pelanggan.profile') }}" class="profile-menu-item">
                    <i class="far fa-user"></i>
                    <span>{{ __('nav.profile') }}</span>
                </a>
                <!-- Nav Links for Mobile -->
                <div class="profile-nav-links">
                    <div class="profile-divider"></div>
                    <a href="{{ url('/') }}" class="profile-menu-item">
                        <i class="fas fa-home"></i>
                        <span>{{ __('nav.home') }}</span>
                    </a>
                    <a href="{{ url('/about') }}" class="profile-menu-item">
                        <i class="fas fa-info-circle"></i>
                        <span>{{ __('nav.about') }}</span>
                    </a>
                    <a href="{{ url('/contact') }}" class="profile-menu-item">
                        <i class="fas fa-phone"></i>
                        <span>{{ __('nav.contact') }}</span>
                    </a>
                </div>

                <div class="profile-divider"></div>

                <button class="profile-menu-item add-booking" onclick="window.location.href='{{ route('daftar-layanan.index') }}'">
                    <span>+ {{ __('nav.cart') }}</span>
                </button>

                <button class="profile-menu-item logout" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>{{ __('nav.logout') }}</span>
                </button>
            </div>
        @else
            <!-- Guest State -->
            <div class="profile-header">
                <div class="profile-header-content">
                    <h3>{{ __('nav.menu') }}</h3>
                </div>
            </div>

            <div class="profile-menu">
                <!-- Nav Links for Mobile (Guest) -->
                <a href="{{ route('welcome') }}" class="profile-menu-item">
                    <i class="fas fa-home"></i>
                    <span>{{ __('nav.home') }}</span>
                </a>
                <a href="{{ route('tentang') }}" class="profile-menu-item">
                    <i class="fas fa-info-circle"></i>
                    <span>{{ __('nav.about') }}</span>
                </a>
                <a href="{{ route('kontak') }}" class="profile-menu-item">
                    <i class="fas fa-phone"></i>
                    <span>{{ __('nav.contact') }}</span>
                </a>

                <div class="profile-divider"></div>

                <a href="{{ route('login') }}" class="profile-menu-item add-booking" style="margin-bottom: 12px;">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>{{ __('nav.sign_in') }}</span>
                </a>

                <a href="{{ route('register') }}" class="profile-menu-item logout">
                    <i class="fas fa-user-plus"></i>
                    <span>{{ __('nav.sign_up') }}</span>
                </a>
            </div>
        @endauth
    </div>