@extends('layouts.pelanggan')

@section('content')
<style>
:root {
    --primary: #e91e63;
    --primary-dark: #c2185b;
    --primary-light: #fbe5f1;
    --border: #f1b8d6;
    --text: #333;
    --muted: #777;
    --bg: #f5f6fb;
}

.layanan-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px;
    min-height: 100vh;
    background: var(--bg);
}

.layanan-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.layanan-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--text);
}

.cart-badge-wrapper {
    position: relative;
}

.btn-cart {
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 999px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(233, 30, 99, 0.3);
}

.btn-cart:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(233, 30, 99, 0.4);
}

.cart-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ff4444;
    color: #fff;
    border-radius: 999px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    border: 2px solid #fff;
}

.layanan-controls {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 250px;
}

.search-box input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    font-size: 14px;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
}

.category-filter {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.category-btn {
    padding: 10px 18px;
    border: 1px solid var(--border);
    border-radius: 999px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 500;
    font-size: 13px;
    color: var(--muted);
}

.category-btn:hover {
    background: var(--primary-light);
    border-color: var(--primary);
}

.category-btn.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.service-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
}

.service-card.highlight {
    animation: highlightPulse 2s ease-in-out;
    border-color: var(--primary);
}

@keyframes highlightPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(233, 30, 99, 0.7); }
    50% { box-shadow: 0 0 0 10px rgba(233, 30, 99, 0); }
}

.service-card.in-cart::before {
    content: '✓';
    position: absolute;
    top: 12px;
    right: 12px;
    background: #10b981;
    color: #fff;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    z-index: 10;
    font-size: 14px;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
}

.service-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    flex-shrink: 0;
}

.service-content {
    padding: 16px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.service-category {
    font-size: 11px;
    color: var(--primary);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.service-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 8px;
    min-height: 44px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
}

.service-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.service-duration {
    font-size: 12px;
    color: var(--muted);
}

.service-price {
    font-size: 16px;
    font-weight: 700;
    color: var(--primary);
    white-space: nowrap;
}

.service-description {
    font-size: 13px;
    color: var(--muted);
    line-height: 1.5;
    margin-bottom: 12px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 42px;
    flex-grow: 1;
}

.service-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
}

.btn-add-cart, .btn-remove-cart {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
}

.btn-add-cart {
    background: var(--primary);
    color: #fff;
}

.btn-add-cart:hover {
    background: var(--primary-dark);
}

.btn-remove-cart {
    background: #fee2e2;
    color: #dc2626;
}

.btn-remove-cart:hover {
    background: #fecaca;
}

/* Cart Modal/Sidebar */
.cart-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 998;
    display: none;
    opacity: 0;
    transition: opacity 0.3s;
}

.cart-overlay.show {
    display: block;
    opacity: 1;
}

.cart-sidebar {
    position: fixed;
    right: -100%;
    top: 0;
    width: 450px;
    max-width: 90vw;
    height: 100vh;
    background: #fff;
    z-index: 999;
    box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
    transition: right 0.3s ease-in-out;
    display: flex;
    flex-direction: column;
}

.cart-sidebar.show {
    right: 0;
}

.cart-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-header h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.btn-close-cart {
    background: transparent;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--muted);
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s;
}

.btn-close-cart:hover {
    background: #f3f4f6;
    color: var(--text);
}

.cart-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px 24px;
}

.cart-empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--muted);
}

.cart-empty i {
    font-size: 64px;
    color: #e5e7eb;
    margin-bottom: 16px;
}

.cart-item {
    display: flex;
    gap: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    margin-bottom: 12px;
    background: #fff;
    transition: all 0.2s;
}

.cart-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.cart-item-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.cart-item-info {
    flex: 1;
}

.cart-item-title {
    font-weight: 700;
    font-size: 14px;
    color: var(--text);
    margin-bottom: 4px;
}

.cart-item-meta {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 6px;
}

.cart-item-price {
    font-size: 15px;
    font-weight: 700;
    color: var(--primary);
}

.cart-item-remove {
    background: #fee2e2;
    color: #dc2626;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.cart-item-remove:hover {
    background: #fecaca;
}

.cart-footer {
    padding: 20px 24px;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
}

.cart-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    font-size: 18px;
}

.cart-total-label {
    font-weight: 600;
    color: var(--text);
}

.cart-total-amount {
    font-weight: 700;
    color: var(--primary);
    font-size: 22px;
}

.btn-checkout {
    width: 100%;
    padding: 14px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-checkout:hover {
    background: var(--primary-dark);
}

.btn-checkout:disabled {
    background: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--muted);
}

.empty-state i {
    font-size: 64px;
    color: #e5e7eb;
    margin-bottom: 16px;
}

.empty-state p {
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .layanan-container {
        padding: 16px;
    }
    
    .services-grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
    }
    
    .cart-sidebar {
        width: 100%;
    }
}
</style>
@if (session('error'))
    <div style="padding: 12px; background: #fee2e2; color: #dc2626; border-radius: 8px; margin-bottom: 16px;">
        {{ session('error') }}
    </div>
@endif
<div class="layanan-container">
    <!-- Header -->
    <div class="layanan-header">
        <div>
            <h1 class="layanan-title">{{ __('layanan.page_title') }}</h1>
            <p style="color: var(--muted); font-size: 14px; margin-top: 4px;">
                {{ __('layanan.page_subtitle') }}
            </p>
        </div>
        <div class="cart-badge-wrapper">
            <button class="btn-cart" onclick="toggleCart()">
                <i class="fas fa-shopping-cart"></i>
                <span>{{ __('layanan.cart_button') }}</span>
            </button>
            <span class="cart-badge" id="cartBadge" style="display: none;">0</span>
        </div>
    </div>

    <!-- Controls -->
    <div class="layanan-controls">
        <div class="search-box">
            <input 
                type="text" 
                id="searchInput" 
                placeholder="{{ __('layanan.search_placeholder') }}" 
                value="{{ $search }}"
            >
        </div>
    </div>

    <!-- Category Filter -->
    <div class="category-filter">
        <button 
            class="category-btn {{ $kategori === 'all' ? 'active' : '' }}" 
            onclick="filterByCategory('all')"
        >
            {{ __('layanan.category_all') }}
        </button>
        @foreach($categories as $cat)
        <button 
            class="category-btn {{ $kategori == $cat->id_kategoriLayanan ? 'active' : '' }}" 
            onclick="filterByCategory({{ $cat->id_kategoriLayanan }})"
        >
            {{ $cat->nama }} ({{ $cat->layanan_count }})
        </button>
        @endforeach
    </div>

    <!-- Services Grid -->
    @if($services->isEmpty())
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <p>{{ __('layanan.empty_title') }}</p>
            <button class="btn-cart" onclick="window.location.href='{{ route('layanan.index') }}'">
                {{ __('layanan.empty_button') }}
            </button>
        </div>
    @else
        <div class="services-grid" id="servicesGrid">
            @foreach($services as $service)
            @php
                $image = $service->gambar 
                    ? asset('storage/' . $service->gambar) 
                    : asset('img/favicon.svg');
            @endphp
            <div 
                class="service-card {{ $highlight == $service->id_layanan ? 'highlight' : '' }}" 
                data-id="{{ $service->id_layanan }}"
                data-title="{{ $service->nama_layanan }}"
                data-price="{{ $service->harga }}"
                data-duration="{{ $service->durasi }}"
                data-image="{{ $image }}"
                data-category="{{ $service->kategoriLayanan->nama ?? __('layanan.category_default') }}"
            >
                <img src="{{ $image }}" alt="{{ $service->nama_layanan }}" class="service-image">
                <div class="service-content">
                    <div class="service-category">{{ $service->kategoriLayanan->nama ?? __('layanan.category_default') }}</div>
                    <div class="service-title">{{ $service->nama_layanan }}</div>
                    <div class="service-meta">
                        <span class="service-duration">
                            <i class="fas fa-clock"></i> {{ $service->durasi }} {{ __('layanan.duration_label') }}
                        </span>
                    </div>
                    @if($service->deskripsi)
                    <div class="service-description">{{ $service->deskripsi }}</div>
                    @endif
                    <div class="service-price">Rp {{ number_format($service->harga, 0, ',', '.') }}</div>
                    <div class="service-actions">
                        <button class="btn-add-cart" onclick="addToCart({{ $service->id_layanan }})">
                            <i class="fas fa-plus"></i> {{ __('layanan.btn_add') }}
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Cart Sidebar -->
<div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-header">
        <h3>{{ __('layanan.cart_title') }}</h3>
        <button class="btn-close-cart" onclick="toggleCart()">×</button>
    </div>
    <div class="cart-body" id="cartBody">
        <div class="cart-empty">
            <i class="fas fa-shopping-cart"></i>
            <p>{{ __('layanan.cart_empty') }}</p>
        </div>
    </div>
    <div class="cart-footer" id="cartFooter" style="display: none;">
        <div class="cart-total">
            <span class="cart-total-label">{{ __('layanan.cart_total') }}</span>
            <span class="cart-total-amount" id="cartTotalAmount">Rp 0</span>
        </div>
        <button class="btn-checkout" id="btnCheckout" onclick="goToCheckout()">
            {{ __('layanan.cart_checkout') }}
        </button>
    </div>
</div>

<!-- Add SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ================== AUTO SHOW SESSION ALERTS ==================
document.addEventListener('DOMContentLoaded', function() {
    // Session Error
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            confirmButtonText: 'OK',
            confirmButtonColor: '#ef4444',
            timer: 5000,
            timerProgressBar: true
        });
    @endif

    // Session Success
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            confirmButtonText: 'OK',
            confirmButtonColor: '#10b981',
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    // Session Warning
    @if(session('warning'))
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: '{{ session('warning') }}',
            confirmButtonText: 'OK',
            confirmButtonColor: '#f59e0b',
            timer: 4000,
            timerProgressBar: true
        });
    @endif

    // Session Info
    @if(session('info'))
        Swal.fire({
            icon: 'info',
            title: 'Informasi',
            text: '{{ session('info') }}',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3b82f6',
            timer: 4000,
            timerProgressBar: true
        });
    @endif
});
</script>


<script>
var defaultImageUrl = "{{ asset('img/favicon.svg') }}";
const CSRF = '{{ csrf_token() }}';
let cartData = [];

// Translations
const TRANS = {
    successAdded: "{{ __('layanan.success_added') }}",
    successRemoved: "{{ __('layanan.success_removed') }}",
    errorLoadCart: "{{ __('layanan.error_load_cart') }}",
    errorAddCart: "{{ __('layanan.error_add_cart') }}",
    errorRemoveCart: "{{ __('layanan.error_remove_cart') }}",
    errorOccurred: "{{ __('layanan.error_occurred') }}",
    confirmRemoveTitle: "{{ __('layanan.confirm_remove_title') }}",
    confirmRemoveText: "{{ __('layanan.confirm_remove_text') }}",
    confirmYes: "{{ __('layanan.confirm_yes') }}",
    confirmNo: "{{ __('layanan.confirm_no') }}",
    removedTitle: "{{ __('layanan.removed_title') }}",
    cartEmpty: "{{ __('layanan.cart_empty') }}",
    cartEmptyWarning: "{{ __('layanan.cart_empty_warning') }}",
    cartEmptyMessage: "{{ __('layanan.cart_empty_message') }}",
    okButton: "{{ __('layanan.ok_button') }}",
    btnAdd: "{{ __('layanan.btn_add') }}",
    btnAdded: "{{ __('layanan.btn_added') }}",
    durationLabel: "{{ __('layanan.duration_label') }}"
};

// Format Rupiah
function formatRupiah(num) {
    return 'Rp ' + parseInt(num).toLocaleString('id-ID');
}

// Load cart dari server
async function loadCart() {
    try {
        const response = await fetch('{{ route("booking.cart.get") }}', {
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success && result.cart) {
            cartData = result.cart;
            renderCart();
            updateCartUI();
        }
    } catch (error) {
        console.error('Error loading cart:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: TRANS.errorLoadCart,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
}

// Add to cart
async function addToCart(idLayanan) {
    try {
        const response = await fetch('{{ route("booking.cart.manage") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id_layanan: idLayanan,
                action: 'add'
            })
        });

        const result = await response.json();

        if (result.success) {
            cartData = result.cart || [];
            renderCart();
            updateCartUI();
            
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: TRANS.successAdded,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || TRANS.errorAddCart,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: TRANS.errorOccurred,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
}

// Remove from cart
async function removeFromCart(idLayanan, idReservasi) {
    const result = await Swal.fire({
        title: TRANS.confirmRemoveTitle,
        text: TRANS.confirmRemoveText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: TRANS.confirmYes,
        cancelButtonText: TRANS.confirmNo,
        reverseButtons: true
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch('{{ route("booking.cart.manage") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id_layanan: idLayanan,
                id_reservasi: idReservasi,
                action: 'remove'
            })
        });

        const data = await response.json();

        if (data.success) {
            cartData = data.cart || [];
            renderCart();
            updateCartUI();
            
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: TRANS.successRemoved,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || TRANS.errorRemoveCart,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    } catch (error) {
        console.error('Error removing from cart:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: TRANS.errorOccurred,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
}

// Render cart
function renderCart() {
    const cartBody = document.getElementById('cartBody');
    const cartFooter = document.getElementById('cartFooter');
    const cartTotalAmount = document.getElementById('cartTotalAmount');

    if (!cartData || cartData.length === 0) {
        cartBody.innerHTML = `
            <div class="cart-empty">
                <i class="fas fa-shopping-cart"></i>
                <p>${TRANS.cartEmpty}</p>
            </div>
        `;
        cartFooter.style.display = 'none';
        return;
    }

    let html = '';
    let total = 0;

    cartData.forEach(item => {
        total += parseFloat(item.harga);
        const imageUrl = item.gambar || defaultImageUrl;
        
        html += `
            <div class="cart-item">
                <img src="${imageUrl}" alt="${item.nama_layanan}" class="cart-item-image">
                <div class="cart-item-info">
                    <div class="cart-item-title">${item.nama_layanan}</div>
                    <div class="cart-item-meta">${item.durasi} ${TRANS.durationLabel}</div>
                    <div class="cart-item-price">${formatRupiah(item.harga)}</div>
                </div>
                <button class="cart-item-remove" onclick="removeFromCart(${item.id_layanan}, ${item.reservasi.id_reservasi})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    });

    cartBody.innerHTML = html;
    cartTotalAmount.textContent = formatRupiah(total);
    cartFooter.style.display = 'block';
}

// Update cart UI (badge, card styling)
function updateCartUI() {
    const badge = document.getElementById('cartBadge');
    const count = cartData.length;

    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }

    // Update service cards
    document.querySelectorAll('.service-card').forEach(card => {
        const id = parseInt(card.dataset.id);
        const inCart = cartData.some(item => item.id_layanan === id);

        if (inCart) {
            card.classList.add('in-cart');
            const btn = card.querySelector('.btn-add-cart');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-check"></i> ' + TRANS.btnAdded;
                btn.disabled = true;
                btn.style.opacity = '0.6';
            }
        } else {
            card.classList.remove('in-cart');
            const btn = card.querySelector('.btn-add-cart');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-plus"></i> ' + TRANS.btnAdd;
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        }
    });
}

// Toggle cart sidebar
function toggleCart() {
    const overlay = document.getElementById('cartOverlay');
    const sidebar = document.getElementById('cartSidebar');

    overlay.classList.toggle('show');
    sidebar.classList.toggle('show');

    if (sidebar.classList.contains('show')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

// Go to checkout
function goToCheckout() {
    if (!cartData || cartData.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: TRANS.cartEmptyWarning,
            text: TRANS.cartEmptyMessage,
            confirmButtonText: TRANS.okButton,
            confirmButtonColor: '#3b82f6'
        });
        return;
    }

    window.location.href = '{{ route("booking.step2") }}';
}

// Filter by category
function filterByCategory(categoryId) {
    const url = new URL(window.location.href);
    url.searchParams.set('kategori', categoryId);
    window.location.href = url.toString();
}

// Search with debounce
let searchTimeout;
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const url = new URL(window.location.href);
        const value = e.target.value.trim();
        
        if (value) {
            url.searchParams.set('search', value);
        } else {
            url.searchParams.delete('search');
        }
        
        window.location.href = url.toString();
    }, 600);
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadCart();

    // Auto-scroll to highlighted item
    const highlighted = document.querySelector('.service-card.highlight');
    if (highlighted) {
        setTimeout(() => {
            highlighted.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 500);
    }
});

// Close cart with ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const sidebar = document.getElementById('cartSidebar');
        if (sidebar.classList.contains('show')) {
            toggleCart();
        }
    }
});
</script>
@endsection