@extends('layouts.pelanggan')

@section('content')
<style>
.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 8px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 14px 35px rgba(15, 23, 42, 0.18);
    max-height: 350px;
    overflow-y: auto;
    z-index: 100000;
}
.search-suggestion-item {
    padding: 12px 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s;
}
.search-suggestion-item:last-child { border-bottom: none; }
.search-suggestion-item:hover { background: #fef2f7; }
.search-suggestion-item img {
    width: 48px; height: 48px; object-fit: cover; border-radius: 8px;
}
.search-suggestion-content { flex: 1; }
.search-suggestion-title { font-weight: 600; color: #333; margin-bottom: 2px; }
.search-suggestion-meta { font-size: 12px; color: #6b7280; }
.search-suggestion-price { font-weight: 700; color: #e91e63; }
.search-suggestions-empty,
.search-suggestions-loading { padding: 16px; text-align: center; font-size: 14px; }
.search-suggestions-empty { color: #9ca3af; }
.search-suggestions-loading { color: #6b7280; }
</style>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <p class="hero-subtitle">
            <img src="{{ asset('img/LOGO.png') }}" alt="Logo">
            <span>{{ __('home.hero.subtitle') }}</span>
        </p>

        <h1 class="hero-title">{!! __('home.hero.title_html') !!}</h1>
        <p class="hero-description">{{ __('home.hero.description') }}</p>

        <div class="search-container">
            <div class="search-section">
                <i class="fas fa-clipboard-list search-icon"></i>
                <span class="search-label">{{ __('home.search.label') }}</span>
            </div>
            <div class="search-divider"></div>
            <div style="position: relative; flex: 1;">
                <input
                    type="text"
                    id="searchLayanan"
                    class="search-input"
                    placeholder="{{ __('home.search.placeholder') }}"
                    autocomplete="off"
                >
                <div id="searchSuggestions" class="search-suggestions" style="display: none;"></div>
            </div>
            <button class="search-btn" onclick="goToLayananPage()">
                {{ __('home.search.button') }}
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services">
    <div class="services-grid">
        <p style="text-align: center; color: #e91e63; grid-column: 1 / -1;">
            {{ __('home.services.loading') }}
        </p>
    </div>

@php
    $i18n = [
        'search' => [
            'loading' => __('home.search.loading'),
            'empty'   => __('home.search.empty'),
            'error'   => __('home.search.error'),
            'minutes' => __('home.common.minutes'),
            'general' => __('home.common.general'),
        ],
        'services' => [
            'none'  => __('home.services.none'),
            'error' => __('home.services.error'),
        ],
        'experience' => [
            'loading' => __('home.experience.loading'),
            'none'    => __('home.experience.none'),
            'error'   => __('home.experience.error'),
        ],
        'reco' => [
            'subtitle'     => __('home.reco.subtitle'),
            'title'        => __('home.reco.title'),
            'loading'      => __('home.reco.loading'),
            'none'         => __('home.reco.none'),
            'error'        => __('home.reco.error'),
            'cta'          => __('home.reco.cta'),
            'price_prefix' => __('home.reco.price_prefix'),
        ],
    ];
@endphp
<script>
    const I18N = @json($i18n);
</script>


<script>
// Debounce
function debounce(func, wait = 400) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => { clearTimeout(timeout); func(...args); };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search layanan
async function searchLayanan(keyword) {
    const suggestionsBox = document.getElementById('searchSuggestions');

    if (!keyword || keyword.length < 2) {
        suggestionsBox.style.display = 'none';
        return;
    }

    // Loading
    suggestionsBox.style.display = 'block';
    suggestionsBox.innerHTML = `<div class="search-suggestions-loading">${I18N.search.loading}</div>`;

    try {
        const response = await fetch(`{{ route('layanan.search') }}?q=${encodeURIComponent(keyword)}`);
        const result = await response.json();

        if (!result.success || !result.data || result.data.length === 0) {
            suggestionsBox.innerHTML = `<div class="search-suggestions-empty">${I18N.search.empty}</div>`;
            return;
        }

        // Build suggestions
        let html = '';
        result.data.forEach(layanan => {
            const imageUrl = layanan.image_url || '{{ asset("img/defaults/layanan.png") }}';
            const price = new Intl.NumberFormat('id-ID').format(layanan.harga);
            const kategori = (layanan.kategori_layanan && layanan.kategori_layanan.nama) ? layanan.kategori_layanan.nama : I18N.search.general;
            const durasi = `${layanan.durasi} ${I18N.search.minutes}`;

            html += `
                <div class="search-suggestion-item" onclick="goToLayananDetail(${layanan.id_layanan})">
                    <img src="${imageUrl}" alt="${layanan.nama_layanan}">
                    <div class="search-suggestion-content">
                        <div class="search-suggestion-title">${layanan.nama_layanan}</div>
                        <div class="search-suggestion-meta">${kategori} • ${durasi}</div>
                    </div>
                    <div class="search-suggestion-price">Rp ${price}</div>
                </div>
            `;
        });

        suggestionsBox.innerHTML = html;

    } catch (error) {
        console.error('Error searching layanan:', error);
        suggestionsBox.innerHTML = `<div class="search-suggestions-empty">${I18N.search.error}</div>`;
    }
}

// Debounced search
const debouncedSearch = debounce(searchLayanan, 400);

// Event listener
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchLayanan');
    const suggestionsBox = document.getElementById('searchSuggestions');

    searchInput.addEventListener('input', (e) => {
        debouncedSearch(e.target.value.trim());
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-container')) {
            suggestionsBox.style.display = 'none';
        }
    });

    // Enter key
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') goToLayananPage();
    });
});

function goToLayananPage() {
    const keyword = document.getElementById('searchLayanan').value.trim();
    if (keyword) window.location.href = `{{ route('daftar-layanan.index') }}?search=${encodeURIComponent(keyword)}`;
    else window.location.href = `{{ route('daftar-layanan.index') }}`;
}

function goToLayananDetail(idLayanan) {
    window.location.href = `{{ route('daftar-layanan.index') }}?highlight=${idLayanan}`;
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const servicesGrid = document.querySelector('.services-grid');
    const defaultCategoryImg = '{{ asset("img/defaults/kategori.png") }}';
    const bookingUrlTemplate = '{{ route("booking.step1", ["kategori" => "_PLACEHOLDER_"]) }}';

    async function fetchAndRenderServices() {
        try {
            const response = await fetch('{{ route("kategorilayananpelanggan.ajax") }}');
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                servicesGrid.innerHTML = '';

                result.data.forEach(item => {
                    if (!item || !item.nama) return;

                    const bookingUrl = bookingUrlTemplate.replace('_PLACEHOLDER_', item.nama);

                    const link = document.createElement('a');
                    link.href = bookingUrl;
                    link.style.textDecoration = 'none';

                    const serviceItem = document.createElement('div');
                    serviceItem.className = 'service-item';
                    serviceItem.style.textAlign = 'center';

                    const img = document.createElement('img');
                    img.src = item.image_url || defaultCategoryImg;
                    img.alt = item.nama;
                    img.style.width = '100%';
                    img.style.borderRadius = '8px';

                    const nameText = document.createElement('p');
                    nameText.textContent = item.nama;
                    nameText.style.marginTop = '8px';
                    nameText.style.fontWeight = '600';
                    nameText.style.color = '#333';

                    serviceItem.appendChild(img);
                    serviceItem.appendChild(nameText);
                    link.appendChild(serviceItem);
                    servicesGrid.appendChild(link);
                });

            } else {
                servicesGrid.innerHTML =
                    `<p style="text-align: center; color: #6c757d; grid-column: 1 / -1;">${I18N.services.none}</p>`;
            }

        } catch (error) {
            console.error('Error fetching services:', error);
            servicesGrid.innerHTML =
                `<p style="text-align: center; color: #dc3545; grid-column: 1 / -1;">${I18N.services.error}</p>`;
        }
    }

    fetchAndRenderServices();
});
</script>
</section>

<!-- Experience Section -->
<section class="experience">
    <div class="experience-header">
        <h2 class="experience-title">
            {!! __('home.experience.title_html') !!}
        </h2>
    </div>

    <div class="experience-container">
        <div class="experience-grid" id="experienceGrid">
            <div style="text-align:center; color:#888; padding:30px">{{ __('home.experience.loading') }}</div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('experienceGrid');

    fetch("{{ route('galeripelanggan.ajax') }}")
        .then(res => res.json())
        .then(response => {
            if (!response.success || !response.data?.galeri?.length) {
                grid.innerHTML = `<div style="text-align:center;color:#888;padding:30px">${I18N.experience.none}</div>`;
                return;
            }

            const galeri = response.data.galeri;
            let html = '';

            galeri.forEach(item => {
                html += `
                    <div class="experience-item">
                        <img src="${item.path_url}" alt="${item.keterangan ?? 'Galeri'}">
                    </div>
                `;
            });

            grid.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            grid.innerHTML = `<div style="text-align:center;color:#c33;padding:30px">${I18N.experience.error}</div>`;
        });
});
</script>

<section class="recommendations">
    <div class="recommendations-header">
        <p class="recommendations-subtitle">{{ __('home.reco.subtitle') }}</p>
        <h2 class="recommendations-title">{{ __('home.reco.title') }}</h2>
    </div>
    <div class="recommendations-container">
        <button class="scroll-btn scroll-btn-left" onclick="scrollRecommendations(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>

        <div class="recommendations-grid" id="recommendationsGrid">
            <p style="text-align: center; color: #e91e63; grid-column: 1 / -1; min-width: 300px;">
                {{ __('home.reco.loading') }}
            </p>
        </div>

        <button class="scroll-btn scroll-btn-right" onclick="scrollRecommendations(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('recommendationsGrid');
    const defaultServiceImg = '{{ asset("img/defaults/layanan.png") }}';
    const ajaxUrl = '{{ route("layananpelanggan.ajax") }}';
    const bookingUrlTemplate = '{{ route("booking.step1", ["kategori" => "_PLACEHOLDER_"]) }}';

    async function loadRecommendations() {
        try {
            const response = await fetch(ajaxUrl);
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            const result = await response.json();

            if (result.success && Array.isArray(result.data) && result.data.length > 0) {
                grid.innerHTML = '';

                result.data.forEach(item => {
                    if (!item || !item.kategori_layanan || !item.kategori_layanan.nama) return;

                    const imageUrl = item.image_url || defaultServiceImg;
                    const rawPrice = parseInt(item.harga, 10) || 0;
                    const formattedPrice = new Intl.NumberFormat('id-ID').format(rawPrice);
                    const price = `${I18N.reco.price_prefix}${formattedPrice}`;

                    const kategoriNama = item.kategori_layanan.nama;
                    const bookingUrl = bookingUrlTemplate.replace('_PLACEHOLDER_', kategoriNama);

                    const cardHtml = `
                        <div class="recommendation-card">
                            <div class="recommendation-image">
                                <img src="${imageUrl}" alt="${item.nama_layanan || 'Layanan'}">
                            </div>
                            <div class="recommendation-content">
                                <h3 class="recommendation-name">${item.nama_layanan || '-'}</h3>
                                <p class="recommendation-price">${price}</p>
                                <a href="${bookingUrl}" class="recommendation-btn" style="text-decoration: none;">${I18N.reco.cta}</a>
                            </div>
                        </div>
                    `;

                    grid.insertAdjacentHTML('beforeend', cardHtml);
                });

            } else {
                grid.innerHTML = `<p style="text-align: center; color: #6c757d; grid-column: 1 / -1;">${I18N.reco.none}</p>`;
            }

        } catch (error) {
            console.error('Gagal memuat rekomendasi:', error);
            grid.innerHTML = `<p style="text-align: center; color: #dc3545; grid-column: 1 / -1;">${I18N.reco.error}</p>`;
        }
    }

    loadRecommendations();
});

// Simple horizontal scroll (optional; implement sesuai kebutuhan)
function scrollRecommendations(dir) {
    const container = document.querySelector('.recommendations-container .recommendations-grid');
    container.scrollBy({ left: dir * 300, behavior: 'smooth' });
}
</script>
</section>
@endsection
