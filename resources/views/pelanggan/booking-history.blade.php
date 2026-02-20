@extends('layouts.pelanggan')

@section('title', 'Riwayat Reservasi')

@section('content')
    <style>
        /* --- CSS styles dari jawaban sebelumnya tetap sama --- */
        :root {
            --pink: #e91e63;
            --pink-light: #fce4ec;
            --ink: #2d2a2a;
            --muted: #757575;
            --bg: #fafafa;
            --card: #fff;
            --border: #e0e0e0;
            --shadow: 0 2px 8px rgba(0, 0, 0, .08);
            --danger: #ef4444;
            --success: #4caf50;
            --warning: #facc15;
            --info: #2196f3;
        }

        /* ------------------------------------- */
        /* --- GLOBAL LAYOUT & UTILITY --- */
        /* ------------------------------------- */
        body {
            background-color: var(--bg);
        }
        
        .history-wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 16px;
        }

        .hero-strip {
            width: 100%;
            margin: 0 auto 18px;
            background:
                linear-gradient(90deg, rgba(0, 0, 0, .35), rgba(0, 0, 0, .15)),
                url('{{ asset('img/image.png') }}') center/cover no-repeat;
            color: #fff;
        }

        .hero-strip-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            position: relative;
            min-height: 52px;
        }

        .hero-back {
            color: #fff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 500;
            z-index: 2;
        }

        .hero-title {
            color: white !important;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            margin: 0;
            font-size: 16px;
            letter-spacing: .15em;
            text-transform: uppercase;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }

        /* ------------------------------------- */
        /* --- FILTERS & SEARCH --- */
        /* ------------------------------------- */
        .filter-bar {
            background: #fff;
            border-radius: 8px;
            box-shadow: var(--shadow);
            display: flex;
            padding: 8px;
            margin-bottom: 16px;
            gap: 8px;
            overflow-x: auto;
            justify-content: flex-start;
        }

        .tab {
            background: transparent;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            color: var(--muted);
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            font-size: 14px;
            transition: all .2s;
            text-decoration: none;
        }

        .tab.active {
            color: var(--pink);
            background: var(--pink-light)
        }

        .search-bar input {
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 6px;
            width: 100%;
            transition: border-color .2s;
        }
        .search-bar input:focus {
            outline: none;
            border-color: var(--pink);
        }

        /* ------------------------------------- */
        /* --- CARD & STATUS (RESPONSIVE) --- */
        /* ------------------------------------- */
        .card {
            background: var(--card);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 16px;
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 12px;
            position: relative;
        }

        .card.clickable {
            transition: transform .2s, box-shadow .2s;
        }

        .card.clickable:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, .12);
        }

        .thumb {
            width: 100px;
            height: 75px;
            object-fit: cover;
            border-radius: 6px
        }

        .title {
            font-weight: 700;
            font-size: 15px;
            color: var(--pink);
            margin: 0 0 4px 0;
            line-height: 1.3;
        }

        .meta {
            font-size: 13px;
            color: var(--muted);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .card-actions .btn-cancel {
            background: var(--danger);
            color: white;
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .card-actions .btn-chat-admin {
            background: var(--info);
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
            white-space: nowrap;
            display: inline-block;
            margin-bottom: 8px;
        }

        /* Status Styles */
        .status.default { background: var(--border); color: var(--muted); }
        .status.pending, .status.pending_midtrans { background: #ffebee; color: var(--danger); }
        .status.proses { background: #fffde7; color: var(--warning); }
        .status.selesai { background: #e8f5e9; color: var(--success); }
        .status.dibatalkan { background: #fbecec; color: #991b1b; }
        .status.dp { background: #e3f2fd; color: var(--info); }
        .status.lunas { background: #ccff90; color: #689f38; }
        .status.menunggu_konfirmasi_pembatalan { background: #fff7ed; color: #ea580c; }
        .status.pembatalan_dp, .status.pembatalan_lunas { background: #f0f4c3; color: #558b2f; }
        
        /* ------------------------------------- */
        /* --- PAYMENT CHECKBOX & ACTIONS --- */
        /* ------------------------------------- */
        .card-checkbox {
            text-align: right;
            position: relative;
        }

        .card-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            vertical-align: middle;
        }

        .btn-confirm-date {
            background: var(--info);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 4px;
            white-space: nowrap;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        
        /* Tombol di footer */
        .btn-reservasi {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--pink);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            box-shadow: var(--shadow);
            transition: all .2s;
            justify-content: center;
            cursor: pointer;
        }
        
        .btn-reservasi.btn-checkout {
            background: var(--success); /* Warna hijau untuk Checkout */
            font-size: 15px;
        }
        .btn-reservasi.btn-checkout:disabled {
            background: var(--muted);
            cursor: not-allowed;
            opacity: 0.8;
            transform: none;
        }

        /* ------------------------------------- */
        /* --- PAGINATION STYLES --- */
        /* ------------------------------------- */
        .pagination-links .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
        }

        .pagination-links .pagination li {
            margin: 0 4px;
        }

        .pagination-links .pagination a,
        .pagination-links .pagination span {
            display: block;
            padding: 8px 12px;
            color: var(--pink);
            text-decoration: none;
            border: 1px solid var(--border);
            border-radius: 4px;
            transition: background-color .2s;
            font-size: 14px;
            font-weight: 500;
        }

        .pagination-links .pagination a:hover {
            background-color: var(--pink-light);
        }

        .pagination-links .pagination .active span {
            background-color: var(--pink);
            color: white;
            border-color: var(--pink);
        }

        .pagination-links .pagination .disabled span {
            color: var(--muted);
            cursor: default;
            background-color: #f5f5f5;
            border-color: #f5f5f5;
        }


        /* ------------------------------------- */
        /* --- FOOTER & KERANJANG PEMBAYARAN --- */
        /* ------------------------------------- */
        .payment-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--card);
            border-top: 1px solid var(--border);
            box-shadow: 0 -4px 12px rgba(0, 0, 0, .1);
            z-index: 100;
            padding: 10px 16px;
            min-height: 48px;
            display: flex; 
        }

        .payment-footer-inner {
            max-width: 1100px;
            margin: 0 auto;
            width: 100%;
        }

        .reservation-cart-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 10px;
            max-height: 80px;
            overflow-y: auto;
        }

        .cart-item {
            display: inline-flex;
            align-items: center;
            background: var(--pink-light);
            color: var(--pink);
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .cart-item-remove {
            margin-left: 6px;
            cursor: pointer;
            font-weight: 900;
            color: var(--danger);
            line-height: 1;
            transition: transform .1s;
        }
        .cart-item-remove:hover {
            transform: scale(1.1);
        }

        .payment-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-display strong {
            color: var(--pink);
            font-size: 17px;
        }
        
        /* Empty state styling */
        .empty {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
            font-style: italic;
            background: var(--card);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }
        
        .empty-loading {
            text-align: center;
            padding: 20px;
            color: var(--muted);
            font-style: italic;
        }


        /* ------------------------------------- */
        /* --- MEDIA QUERIES (RESPONSIVENESS) --- */
        /* ------------------------------------- */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 14px;
                letter-spacing: .1em;
            }
            
            .card {
                grid-template-columns: 80px 1fr;
                padding-bottom: 70px;
            }
            
            .card > div:nth-child(3) { 
                grid-column: 2 / 3; 
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                justify-content: flex-start;
                gap: 4px;
            }
            
            .card-content .card-actions {
                position: absolute;
                bottom: 12px;
                left: 16px;
                margin-top: 0;
            }
            
            .card-checkbox {
                text-align: right;
            }
            
            .thumb {
                width: 80px;
                height: 60px;
            }
            
            .title {
                font-size: 14px;
            }
            
            .meta {
                font-size: 12px;
            }

            .payment-footer {
                padding: 8px;
            }
            .total-display strong {
                font-size: 15px;
            }
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Modal Container */
        .modal-detail {
            background: white;
            border-radius: 12px;
            max-width: 700px;
            width: 100%;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Modal Header */
        .modal-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, var(--pink) 0%, #c2185b 100%);
            color: white;
        }

        .modal-detail-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .btn-close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 32px;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .btn-close-modal:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Modal Body */
        .modal-detail-body {
            padding: 24px;
            overflow-y: auto;
            max-height: calc(90vh - 80px);
        }

        /* Detail Sections */
        .detail-section {
            margin-bottom: 24px;
        }

        .detail-section:last-child {
            margin-bottom: 0;
        }

        .detail-section-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--pink);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-section-title i {
            font-size: 18px;
        }

        .detail-info-grid {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 12px;
            background: var(--bg);
            padding: 16px;
            border-radius: 8px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--muted);
            font-size: 14px;
        }

        .detail-value {
            font-size: 14px;
            color: var(--ink);
        }

        .detail-value strong {
            color: var(--pink);
            font-weight: 700;
        }

        /* Layanan Item */
        .layanan-item-modal {
            background: var(--bg);
            padding: 12px;
            border-radius: 8px;
            display: grid;
            grid-template-columns: 60px 1fr auto;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }

        .layanan-thumb-modal {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        .layanan-info-modal h4 {
            margin: 0 0 4px 0;
            font-size: 14px;
            font-weight: 700;
            color: var(--pink);
        }

        .layanan-info-modal p {
            margin: 0;
            font-size: 13px;
            color: var(--muted);
        }

        /* Status Badge dalam Modal */
        .modal-status {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
        }

        /* Responsive Modal */
        @media (max-width: 768px) {
            .modal-detail {
                max-width: 100%;
                max-height: 100vh;
                border-radius: 0;
            }
            
            .detail-info-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .detail-label {
                font-weight: 700;
                color: var(--pink);
            }
            
            .modal-detail-header h2 {
                font-size: 18px;
            }
        }
    </style>

    {{-- HERO STRIP --}}
    <div class="hero-strip">
        <div class="hero-strip-inner">
            <a href="{{ route('welcome') }}" class="hero-back">
                <span class="hero-back-icon">‹</span>
                <span>Kembali</span>
            </a>
            <h1 class="hero-title">DAFTAR RIWAYAT RESERVASI</h1>
        </div>
    </div>

    <div class="history-wrap" style="padding-bottom: 100px;"> {{-- Padding bawah lebih besar untuk footer --}}

        @php
            $status = $status ?? 'semua';
            $currentSearch = $currentSearch ?? '';
            $active = $status;

            $tabs = [
                'semua' => 'Semua',
                'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                'sedang_berjalan' => 'Sedang Berjalan',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan',
            ];
        @endphp

        {{-- TAB FILTER (AJAX) --}}
        <div class="filter-bar">
            @foreach ($tabs as $key => $label)
                <a class="tab {{ $active === $key ? 'active' : '' }}"
                    href="#"
                    data-status="{{ $key }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Form Pencarian (AJAX) --}}
        <form action="{{ route('booking.history') }}" method="GET" class="search-bar" style="margin-bottom: 16px;">
            <input type="hidden" name="status" value="{{ $active }}">
            <input type="text"
                    name="search"
                    placeholder="Cari ID, Tanggal, Layanan..."
                    value="{{ $currentSearch }}"
                    >
        </form>

        {{-- CONTAINER UNTUK DATA RIWAYAT (DIISI OLEH AJAX) --}}
        <div id="reservations-list">
            <div class="empty-loading">Memuat riwayat reservasi...</div>
        </div>

        {{-- CONTAINER UNTUK LINK PAGINASI (DIISI OLEH AJAX) --}}
        <div class="pagination-links" style="margin-top: 20px; text-align: center;"></div>

    </div>
    
    {{-- PAYMENT FOOTER (DINAMIS & TERSEMBUNYI DEFAULT) --}}
    <div class="payment-footer" id="payment-footer-id" style="display: none;"> {{-- ID dan display: none default --}}
        <div class="payment-footer-inner">
            
            {{-- Daftar Item yang Dipilih --}}
            <div id="reservation-cart-list" class="reservation-cart-list" style="display: none;">
                {{-- Daftar item akan di-render di sini oleh JavaScript --}}
            </div>

            <div class="payment-summary">
                
                {{-- Total Pembayaran --}}
                <div class="total-display" id="total-display-wrap" style="display: none;">
                    Total Pembayaran: <strong id="total-pembayaran">Rp 0</strong>
                </div>

                {{-- Kontrol Aksi (Tombol) --}}
                <div id="footer-actions" style="width: 100%; text-align: right;">
                    
                    {{-- Tombol Reservasi Baru (Tidak terlihat jika keranjang penuh) --}}
                    <a href="{{ route('booking.step2') }}" class="btn-reservasi" id="btn-footer-reservasi" style="display: none;">
                        <span>Reservasi Baru</span>
                    </a>
                    
                    {{-- Tombol Bayar Sekarang (Tampil jika Cart Terisi) --}}
                    {{-- ACTION menggunakan route Laravel yang Anda sebutkan --}}
                    <form id="payment-form" action="{{ route('booking.step3') }}" method="POST" style="display: none;">
                        @csrf
                        {{-- Input array akan diisi oleh JS, dengan name='reservasi_ids[]' agar cocok dengan controller --}}
                        <button type="submit" id="btn-bayar-sekarang" class="btn-reservasi btn-checkout">
                            Bayar Sekarang (0)
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <div id="detailModal" class="modal-backdrop" style="display: none;">
        <div class="modal-detail">
            <div class="modal-detail-header">
                <h2 id="modalDetailTitle">Detail Reservasi</h2>
                <button class="btn-close-modal" onclick="closeDetailModal()">&times;</button>
            </div>
            <div class="modal-detail-body" id="modalDetailBody">
                <!-- Konten akan diisi oleh JavaScript -->
            </div>
        </div>
    </div>


    {{-- JQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const RESERVATIONS_URL = '{{ route('booking.ajaxGetReservations') }}'; 
        const HISTORY_PAGE_URL = '{{ route('booking.history') }}';
        const CANCEL_BASE_URL = '{{ url('booking/cancel') }}';
        const DELETE_BASE_URL = '{{ url('booking') }}'; // Base for DELETE /booking/{id}
        const DETAIL_BASE_URL = '{{ url('booking/detail') }}'; 
        const BOOKING_STEP2_URL = '{{ route('booking.step2') }}'; 

        // --- VARIABEL GLOBAL UNTUK KERANJANG PERSISTEN ---
        let paymentCart = {}; 
        const WHATSAPP_ADMIN_NUMBER = '6281234567890'; 

        let currentPage = {{ request()->get('page', 1) }};
        let currentStatus = '{{ $status ?? 'semua' }}';
        let currentSearch = '{{ $currentSearch ?? '' }}';
        let reservationsData = {};

        const STATUS_LABELS = {
            'semua': 'Semua',
            'menunggu konfirmasi': 'Menunggu Konfirmasi',
            'sedang berjalan': 'Sedang Berjalan',
            'selesai': 'Selesai',
            'dibatalkan': 'Dibatalkan',
        };

        function formatRupiah(numberString) {
            let number = String(numberString).replace(/\./g, '').replace(/,/g, ''); 
            if (isNaN(parseFloat(number)) || number.trim() === '') return 'Rp 0';

            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(parseFloat(number));
        }
        
        function renderManualPaginationLinks(paginationData) {
            // ... (Fungsi Pagination tetap sama) ...
            if (paginationData.total <= paginationData.per_page) return '';

            let linksHtml = '<ul class="pagination">';
            const currentPage = paginationData.current_page;
            const lastPage = paginationData.last_page;

            const prevDisabled = currentPage === 1;
            linksHtml += `<li class="${prevDisabled ? 'disabled' : ''}">
                                <a href="#" data-page="${currentPage - 1}" class="page-link"
                                    ${prevDisabled ? 'aria-disabled="true"' : ''}>
                                    <span>&laquo; Sebelumnya</span>
                                </a>
                            </li>`;

            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(lastPage, currentPage + 2);

            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === currentPage ? 'active' : '';
                linksHtml += `<li class="${activeClass}">
                                    <a href="#" data-page="${i}" class="page-link">
                                        <span>${i}</span>
                                    </a>
                                </li>`;
            }

            const nextDisabled = currentPage === lastPage;
            linksHtml += `<li class="${nextDisabled ? 'disabled' : ''}">
                                <a href="#" data-page="${currentPage + 1}" class="page-link"
                                    ${nextDisabled ? 'aria-disabled="true"' : ''}>
                                    <span>Berikutnya &raquo;</span>
                                </a>
                            </li>`;

            linksHtml += '</ul>';
            return linksHtml;
        }


        // --- FUNGSI UTAMA UNTUK MENGELOLA KERANJANG & TAMPILAN ---
        function updatePaymentCart() {
            let total = 0;
            let count = 0;
            const selectedIds = [];
            const cartListContainer = $('#reservation-cart-list');
            const totalDisplayWrap = $('#total-display-wrap');
            const paymentFooter = $('#payment-footer-id'); 
            let cartItemsHtml = '';

            // 1. Hitung Total dan Buat HTML Cart Item
            for (const id in paymentCart) {
                const item = paymentCart[id];
                total += item.amount;
                selectedIds.push(id);
                count++;

                cartItemsHtml += `
                    <div class="cart-item" data-id="${id}">
                        #${id} - ${item.name} (${formatRupiah(item.amount)})
                        <span class="cart-item-remove" data-id="${id}">&times;</span>
                    </div>
                `;
            }
            
            // 2. Update Tampilan Footer DYNAMICALLY
            if (count > 0) {
                // KONDISI 1: CART TERISI (Tampilkan Checkout)
                
                // Tampilkan Footer: display: flex
                paymentFooter.css('display', 'flex'); 
                
                cartListContainer.css('display', 'flex').html(cartItemsHtml);
                totalDisplayWrap.css('display', 'block');
                $('#payment-form').css('display', 'block');
                
                $('#btn-footer-reservasi').css('display', 'none'); 
                $('#total-pembayaran').text(formatRupiah(total));
                $('#btn-bayar-sekarang').text(`Bayar Sekarang (${count})`).prop('disabled', false);
            } else {
                // KONDISI 2: CART KOSONG (Sembunyikan Footer Total)
                
                // Sembunyikan Footer: display: none
                paymentFooter.css('display', 'none'); 
                
                // Sembunyikan elemen Cart dan Bayar
                cartListContainer.css('display', 'none');
                totalDisplayWrap.css('display', 'none');
                $('#payment-form').css('display', 'none');
                $('#btn-footer-reservasi').css('display', 'none'); 
            }
            
            // 3. Update Form Hidden Input Array (sinkron dengan controller 'reservasi_ids[]')
            const form = $('#payment-form');
            form.find('input[name="reservasi_ids[]"]').remove(); // Hapus yang lama
            selectedIds.forEach(id => {
                // *** KRITIS: Menggunakan name="reservasi_ids[]" ***
                form.append(`<input type="hidden" name="reservasi_ids[]" value="${id}">`);
            });


            // 4. Attach Handler Hapus Item di Cart Footer
            $('.cart-item-remove').off('click').on('click', function(e) {
                e.stopPropagation();
                const idToRemove = $(this).data('id');
                
                delete paymentCart[idToRemove];
                
                $(`.payment-checkbox[data-id="${idToRemove}"]`).prop('checked', false);
                
                updatePaymentCart();
            });
        }
        
function renderCard(reservasi) {
    const id_reservasi = reservasi.id_reservasi || '-';
    const nama_layanan = reservasi.nama_layanan || 'Layanan Tidak Diketahui';
    const total_layanan_lain = reservasi.total_layanan_lain || 0;
    const kategori = reservasi.kategori || 'Umum'; // TAMBAHKAN INI (variabel yang hilang)
    const sisa_pembayaran_str = String(reservasi.sisa_pembayaran || '0');
    const status_reservasi_class = reservasi.status_reservasi_class || 'default';
    const status_reservasi_text = reservasi.status_reservasi_text || 'Status Tidak Diketahui'; // TAMBAHKAN INI
    const sisa_pembayaran_float = parseFloat(sisa_pembayaran_str.replace(/\./g, '').replace(/,/g, '')) || 0; // DEKLARASI PERTAMA (DIPERTAHANKAN)
    const is_clickable = reservasi.is_clickable ?? false;
    const show_delete = reservasi.show_delete ?? false;
    const is_lunas_flag = reservasi.is_lunas ?? false; // Ambil flag is_lunas
    const show_cancel = reservasi.show_cancel ?? false; // Restore show_cancel

    const thumb = reservasi.thumb || '{{ asset('img/favicon.svg') }}';
    
    const tanggal_reservasi_raw = reservasi.tanggal_reservasi || 'Belum Ditentukan';
    const tanggal_is_set = tanggal_reservasi_raw !== 'Belum Ditentukan' && tanggal_reservasi_raw !== '';

    // Simpan data reservasi untuk modal
    reservationsData[id_reservasi] = {
        // ... (existing data)
        id_reservasi: id_reservasi,
        nama_layanan: nama_layanan,
        total_layanan_lain: total_layanan_lain,
        kategori: kategori,
        sisa_pembayaran: sisa_pembayaran_str,
        sisa_pembayaran_raw: sisa_pembayaran_float,
        sisa_pembayaran_formatted: formatRupiah(sisa_pembayaran_float), 
        status_reservasi_class: status_reservasi_class,
        status_reservasi_text: status_reservasi_text,
        tanggal_reservasi: tanggal_reservasi_raw,
        waktu_reservasi: reservasi.waktu_reservasi || 'Belum Ditentukan',
        thumb: thumb,
        show_cancel: show_cancel,
        show_delete: show_delete,
        is_lunas: is_lunas_flag,
        total_harga: reservasi.total_harga || sisa_pembayaran_float,
        total_harga_deal: reservasi.total_harga_deal || 0, 
        total_dibayar: reservasi.total_dibayar || 0,
        metode_pembayaran: reservasi.metode_pembayaran || 'Belum dibayar',
        catatan: reservasi.catatan || '' 
    };

    let checkboxHtml = '';
    
    // Logic Pembayaran
    const canPaySisa = (status_reservasi_class === 'dp' || status_reservasi_class === 'pending_midtrans') && sisa_pembayaran_float > 0;
    const canPayTotal = status_reservasi_class === 'pending' && tanggal_is_set;
    
    // STRICT CHECK: Jika is_lunas_flag true, checkbox HARUS hidden regardless of anything else.
    const isReservable = (canPaySisa || canPayTotal) && !is_lunas_flag; 

    const isPendingNoDate = status_reservasi_class === 'pending' && !tanggal_is_set; 

    if (isReservable) {
        const amountToPay = sisa_pembayaran_float;
        const isChecked = paymentCart.hasOwnProperty(id_reservasi);

        // Checkbox hanya muncul jika BISA dan PERLU dibayar
        checkboxHtml = `
            <div class="card-checkbox" onclick="event.stopPropagation();">
                <input type="checkbox" 
                        data-id="${id_reservasi}" 
                        data-amount="${amountToPay}" 
                        data-name="${nama_layanan}" 
                        class="payment-checkbox"
                        ${isChecked ? 'checked' : ''}
                        title="Pilih untuk pembayaran">
            </div>
        `;
    } else if (isPendingNoDate) {
        // ... (existing logic for step2 link)
         checkboxHtml = `
            <div class="card-checkbox" onclick="event.stopPropagation();">
                <a href="${BOOKING_STEP2_URL}?reservation_id=${id_reservasi}" class="btn-confirm-date">
                    Konfirmasi Jadwal/Bayar Awal
                </a>
            </div>
        `;
    }

    let sisaPembayaranHtml = '';
    if (
        (sisa_pembayaran_float > 0 || isPendingNoDate) && 
        !is_lunas_flag && 
        status_reservasi_class !== 'lunas' &&
        status_reservasi_class !== 'bayar_lunas'
    ) {
        let label = (isPendingNoDate || status_reservasi_class === 'pending') 
            ? 'Total yang harus dibayar' 
            : 'Sisa yang harus dibayar';
        
        sisaPembayaranHtml = `
            <span>${label}: <strong>${formatRupiah(sisa_pembayaran_str)}</strong></span>
        `;
    }

    let actionsHtml = '';
    
    // BUTTON LOGIC: Mutually Exclusive
    if (show_delete) {
         // Hapus Button (Only for unpaid/pending logic which sets show_delete=true)
         actionsHtml = `
            <div class="card-actions" onclick="event.stopPropagation();" style="margin-top: 10px;">
                <button type="button" class="btn-cancel" style="background: var(--danger); font-size: 0.85rem; padding: 6px 12px;" onclick="handleDeleteReservation(${id_reservasi})">
                    <i class="fas fa-trash-alt"></i> Hapus
                </button>
            </div>
        `;
    } else if (show_cancel) {
        // Cancel Button (For Paid/DP/Lunas)
        if (['menunggu_konfirmasi_pembatalan', 'pembatalan_dp', 'pembatalan_lunas'].includes(status_reservasi_class)) {
            // User requested to remove Chat Admin button. Do nothing or show status text only.
             actionsHtml = `
                <div class="card-actions" onclick="event.stopPropagation();">
                    <span class="badge badge-warning" style="background: #fff7ed; color: #c05621; padding: 4px 8px; border-radius: 4px; font-size: 0.8em;">
                        <i class="fas fa-clock"></i> Menunggu Konfirmasi
                    </span>
                </div>
            `;
        } else {
             // General Cancel Button for Active Paid Booking
             actionsHtml = `
                <div class="card-actions" onclick="event.stopPropagation();" style="margin-top: 10px;">
                    <button type="button" class="btn-cancel" style="background: #e91e63; font-size: 0.85rem; padding: 6px 12px;" onclick="handleCancelReservation(${id_reservasi})">
                        <i class="fas fa-times-circle"></i> Batalkan
                    </button>
                </div>
            `;
        }
    }

    let clickEvent = is_clickable
        ? `onclick="openDetailModal(${id_reservasi})" style="cursor: pointer;"`
        : '';
    let clickableClass = is_clickable ? 'clickable' : '';

    return `
        <article class="card ${clickableClass}" ${clickEvent}>
            <img class="thumb" src="${thumb}" alt="Thumbnail" onerror="this.src='{{ asset('img/favicon.svg') }}'">

            <div class="card-content">
                <h3 class="title">Reservasi #${id_reservasi} - ${nama_layanan} ${total_layanan_lain > 0 ? `<span class="count">(+${total_layanan_lain} layanan lain)</span>` : ''}</h3>
                <div class="meta">
                    <span>Kategori: ${kategori}</span>
                    <span>Jam: ${reservasi.waktu_reservasi || 'Belum Ditentukan'}</span>
                    <span>Tanggal: ${tanggal_reservasi_raw}</span>
                    ${sisaPembayaranHtml}
                </div>
                ${actionsHtml}
            </div>

            <div>
                <div class="status ${status_reservasi_class}">${status_reservasi_text}</div>
                ${checkboxHtml}
            </div>
        </article>
    `;
}
        function loadReservations(page = 1, status = currentStatus, search = currentSearch) {
            const listContainer = $('#reservations-list');
            const paginationContainer = $('.pagination-links');

            listContainer.html('<div class="empty-loading">Memuat riwayat reservasi...</div>');
            paginationContainer.empty();

            currentPage = page;
            currentStatus = status;
            currentSearch = search;
            
            const params = new URLSearchParams({ page, status, search });
            if (!search) params.delete('search');
            if (page == 1) params.delete('page');
            history.pushState(null, '', `${HISTORY_PAGE_URL}?${params.toString()}`);

            $.ajax({
                url: RESERVATIONS_URL,
                type: 'GET',
                data: { page, status, search },
                success: function(response) {
                    listContainer.empty();
                    
                    if (response.success && response.data.length > 0) {
                        response.data.forEach(reservasi => {
                            listContainer.append(renderCard(reservasi));
                        });

                        const paginationHtml = renderManualPaginationLinks(response.pagination);
                        paginationContainer.html(paginationHtml);

                        paginationContainer.find('.page-link').off('click').on('click', function(e) {
                            e.preventDefault();
                            if ($(this).parent().hasClass('disabled') || $(this).parent().hasClass('active')) return;

                            const newPage = $(this).data('page');
                            loadReservations(newPage, currentStatus, currentSearch);
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        });

                        $('.payment-checkbox').off('change').on('change', function(e) {
                            e.stopPropagation();
                            const id = $(this).data('id');
                            const amount = parseFloat($(this).data('amount'));
                            const name = $(this).data('name');

                            if ($(this).is(':checked')) {
                                paymentCart[id] = { amount: amount, name: name };
                            } else {
                                delete paymentCart[id];
                            }
                            updatePaymentCart();
                        });

                        // Sinkronisasi status checkbox dengan paymentCart
                        $('.payment-checkbox').each(function() {
                            const id = $(this).data('id');
                            $(this).prop('checked', paymentCart.hasOwnProperty(id));
                        });
                        updatePaymentCart(); 
                        
                    } else {
                        const statusLabel = STATUS_LABELS[currentStatus] || 'Semua';
                        const searchInfo = currentSearch ? ` untuk pencarian: "<strong>${currentSearch}</strong>"` : '';
                        listContainer.html(`<div class="empty">Belum ada riwayat reservasi yang ditemukan untuk status <strong>${statusLabel}</strong>${searchInfo}.</div>`);
                        updatePaymentCart(); 
                    }
                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON?.message || 'Terjadi kesalahan server. Mohon coba lagi.';
                    listContainer.html(`<div class="empty" style="color: var(--danger);">Error ${xhr.status}: ${errorMessage}</div>`);
                    updatePaymentCart();
                }
            });
        }

        $(document).ready(function() {
            loadReservations('{{ request()->get('page', 1) }}', '{{ $status ?? 'semua' }}', '{{ $currentSearch ?? '' }}');

            // Tab Filter Handler
            $('.filter-bar .tab').on('click', function(e) {
                e.preventDefault();
                $('.filter-bar .tab').removeClass('active');
                $(this).addClass('active');
                const newStatus = $(this).data('status');
                loadReservations(1, newStatus, currentSearch);
            });

            // Search Form Handler
            $('.search-bar').on('submit', function(e) {
                e.preventDefault();
                const newSearch = $('input[name="search"]').val();
                paymentCart = {}; 
                loadReservations(1, currentStatus, newSearch);
            });
            
            // Mencegah navigasi ke detail saat mengklik checkbox/aksi
            $('#reservations-list').on('click', '.card-checkbox, .card-actions, .btn-confirm-date', function(e) {
                e.stopPropagation();
            });
            
            // Validasi Submit Form Checkout
            $('#payment-form').on('submit', async function(e) {
                e.preventDefault(); // Selalu prevent default dulu
                
                const form = $(this);
                const ids = form.find('input[name="reservasi_ids[]"]').length;
                
                if (ids === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Belum Ada Reservasi',
                        text: 'Silakan pilih minimal satu reservasi untuk dibayar.',
                        confirmButtonColor: '#e91e63'
                    });
                    return;
                }
                
                // Hitung total dari paymentCart
                let totalAmount = 0;
                let reservasiList = '';
                for (const id in paymentCart) {
                    totalAmount += paymentCart[id].amount;
                    reservasiList += `<li style="text-align:left;">#${id} - ${paymentCart[id].name}: <strong>${formatRupiah(paymentCart[id].amount)}</strong></li>`;
                }
                
                const result = await Swal.fire({
                    title: 'Konfirmasi Pembayaran',
                    html: `
                        <div style="text-align:left; margin-bottom:12px;">
                            <p style="margin-bottom:8px; color:#757575;">Anda akan melakukan pembayaran untuk <strong>${ids} reservasi</strong>:</p>
                            <ul style="margin:0; padding-left:20px; font-size:14px; color:#2d2a2a;">
                                ${reservasiList}
                            </ul>
                        </div>
                        <div style="background:#fce4ec; padding:12px; border-radius:8px; margin-top:12px;">
                            <span style="font-size:14px; color:#757575;">Total Pembayaran:</span>
                            <div style="font-size:24px; font-weight:700; color:#e91e63;">${formatRupiah(totalAmount)}</div>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4caf50',
                    cancelButtonColor: '#757575',
                    confirmButtonText: '<i class="fas fa-credit-card"></i> Lanjutkan ke Pembayaran',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'swal-wide'
                    }
                });
                
                if (result.isConfirmed) {
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Memproses...',
                        html: 'Sedang menyiapkan halaman pembayaran',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    form.off('submit').submit();
                }
            });
        });


function openDetailModal(reservasiId) {
    const modal = document.getElementById('detailModal');
    const modalBody = document.getElementById('modalDetailBody');
    const modalTitle = document.getElementById('modalDetailTitle');
    
    // Ambil data dari storage
    const data = reservationsData[reservasiId];
    
    if (!data) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Data reservasi tidak ditemukan',
            confirmButtonColor: '#ef4444'
        });
        return;
    }
    
    // Tampilkan modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    modalTitle.textContent = `Detail Reservasi #${reservasiId}`;
    
    // Render detail
    renderDetailModal(data, modalBody);
}

// Fungsi untuk render konten modal
function renderDetailModal(data, modalBody) {
    const statusClass = data.status_reservasi_class || 'default';
    const statusText = data.status_reservasi_text || 'Status Tidak Diketahui';
    
    // Parse layanan dari data (jika ada multiple layanan)
    const namaLayanan = data.nama_layanan || 'Layanan Tidak Diketahui';
    const totalLayananLain = data.total_layanan_lain || 0;
    const kategori = data.kategori || 'Umum';
    const thumb = data.thumb || '{{ asset('img/favicon.svg') }}';
    
    // Render konten lengkap
    modalBody.innerHTML = `
        <!-- Informasi Umum -->
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="fas fa-info-circle"></i>
                Informasi Reservasi
            </div>
            <div class="detail-info-grid">
                <span class="detail-label">ID Reservasi:</span>
                <span class="detail-value"><strong>#${data.id_reservasi}</strong></span>
                
                <span class="detail-label">Status:</span>
                <span class="detail-value">
                    <span class="modal-status status ${statusClass}">${statusText}</span>
                </span>
                
                <span class="detail-label">Kategori:</span>
                <span class="detail-value">${kategori}</span>
                
                <span class="detail-label">Tanggal:</span>
                <span class="detail-value">${data.tanggal_reservasi || 'Belum ditentukan'}</span>
                
                <span class="detail-label">Waktu:</span>
                <span class="detail-value">${data.waktu_reservasi || 'Belum ditentukan'}</span>
            </div>
        </div>
        
        <!-- Layanan -->
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="fas fa-list"></i>
                Layanan yang Dipesan
            </div>
            <div class="layanan-item-modal">
                <img src="${thumb}" alt="${namaLayanan}" class="layanan-thumb-modal" onerror="this.src='{{ asset('img/favicon.svg') }}'">
                <div class="layanan-info-modal">
                    <h4>${namaLayanan}</h4>
                    <p>${totalLayananLain > 0 ? `+ ${totalLayananLain} layanan lain` : 'Layanan tunggal'}</p>
                </div>
                <div style="font-weight: 700; color: var(--pink); font-size: 14px; text-align: right;">
                    ${formatRupiah(data.total_harga_deal)}
                </div>
            </div>
        </div>
        
        <!-- Catatan (Admin/User) -->
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="fas fa-sticky-note"></i>
                Catatan
            </div>
            <div style="background: #fdf2f8; padding: 12px; border-radius: 8px; font-size: 14px; color: #be185d; border: 1px dashed #f9a8d4;">
                ${data.catatan ? data.catatan : '<em>Tidak ada catatan khusus.</em>'}
            </div>
        </div>

        <!-- Status Pembayaran Details -->
        ${renderPaymentStatus(data)}
        
        <!-- Actions -->
        ${renderModalActions(data)}
    `;
}

// Fungsi untuk render status pembayaran
function renderPaymentStatus(data) {
    const statusClass = data.status_reservasi_class || 'default';
    let content = '';
    
    if (data.is_lunas || statusClass === 'lunas' || statusClass === 'bayar_lunas') { // Cek is_lunas first
        content = `
            <div class="detail-section">
                <div style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 12px; border-radius: 6px;">
                    <strong style="color: #4caf50;"><i class="fas fa-check-circle"></i> Pembayaran Lunas</strong>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #6b7280;">
                        Pembayaran telah lunas. Terima kasih!
                    </p>
                </div>
            </div>
        `;
    } else if (statusClass === 'pending') {
        content = `
            <div class="detail-section">
                <div style="background: #fff7ed; border-left: 4px solid #ea580c; padding: 12px; border-radius: 6px;">
                    <strong style="color: #ea580c;"><i class="fas fa-exclamation-triangle"></i> Menunggu Pembayaran</strong>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #6b7280;">
                        Silakan lakukan pembayaran untuk mengkonfirmasi reservasi Anda.
                    </p>
                </div>
            </div>
        `;
    } else if (statusClass === 'dp' || statusClass === 'pending_midtrans') {
        content = `
            <div class="detail-section">
                <div style="background: #dbeafe; border-left: 4px solid #2196f3; padding: 12px; border-radius: 6px;">
                    <strong style="color: #2196f3;"><i class="fas fa-info-circle"></i> Pembayaran Sebagian</strong>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #6b7280;">
                        DP telah dibayar. Sisa pembayaran dapat dilunasi sebelum atau saat layanan.
                    </p>
                    ${ (statusClass !== 'selesai' && statusClass !== 'lunas') ? 
                        `<div style="margin-top: 8px; font-weight: bold; color: #1e40af;">
                            Sisa Tagihan: ${data.sisa_pembayaran_formatted || 'Rp 0'}
                        </div>` : '' 
                    }
                </div>
            </div>
        `;
    } else if (statusClass === 'dibatalkan') {
        content = `
            <div class="detail-section">
                <div style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 12px; border-radius: 6px;">
                    <strong style="color: #ef4444;"><i class="fas fa-times-circle"></i> Reservasi Dibatalkan</strong>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #6b7280;">
                        Reservasi ini telah dibatalkan.
                    </p>
                </div>
            </div>
        `;
    }
    
    return content;
}



// Fungsi untuk handle HAPUS dengan SweetAlert
async function handleDeleteReservation(reservasiId) {
    const result = await Swal.fire({
        title: 'Hapus Reservasi?',
        html: `Apakah Anda yakin ingin menghapus reservasi <strong>#${reservasiId}</strong>?<br><small style="color: #6b7280;">Data yang dihapus tidak dapat dikembalikan.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d32f2f', // Red for delete
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus Permanen',
        cancelButtonText: 'Batal',
        reverseButtons: true
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch(`${DELETE_BASE_URL}/${reservasiId}`, {
            method: 'DELETE', // Method DELETE
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Terhapus!',
                text: data.message || 'Reservasi berhasil dihapus',
                timer: 2000,
                showConfirmButton: false
            });
            
            closeDetailModal();
            
            // Reload data
            loadReservations(currentPage, currentStatus, currentSearch);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message || 'Gagal menghapus reservasi',
                confirmButtonColor: '#ef4444'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat menghapus reservasi',
            confirmButtonColor: '#ef4444'
        });
    }
}

// Update renderModalActions to include Delete button
function renderModalActions(data) {
    const statusClass = data.status_reservasi_class || 'default';
    const id = data.id_reservasi;
    let actions = '';
    
    // Jika show_delete is true (from controller)
    if (data.show_delete) {
         actions = `
            <div class="detail-section">
                <button onclick="handleDeleteReservation(${id})" 
                        class="btn-reservasi" style="width: 100%; background: var(--danger);">
                    <i class="fas fa-trash-alt"></i>
                    Hapus Reservasi
                </button>
            </div>
        `;
    }
    // Jika pending dan belum ada tanggal (dan bukan lunas)
    if (statusClass === 'pending' && (!data.tanggal_reservasi || data.tanggal_reservasi === 'Belum Ditentukan') && !data.is_lunas) {
        actions = `
            <div class="detail-section">
                <a href="${BOOKING_STEP2_URL}?reservation_id=${id}" class="btn-reservasi" style="width: 100%; text-align: center;">
                    <i class="fas fa-calendar-check"></i>
                    Konfirmasi Jadwal & Bayar
                </a>
            </div>
        `;
    }
    // Jika bisa bayar (pending dengan tanggal atau dp/pending_midtrans) DAN bukan lunas
    else if (((statusClass === 'pending' && data.tanggal_reservasi && data.tanggal_reservasi !== 'Belum Ditentukan') || 
             statusClass === 'dp' || statusClass === 'pending_midtrans') && !data.is_lunas) {
        actions = `
            <div class="detail-section">
                <button onclick="addToPaymentCartFromModal(${id}, '${data.nama_layanan}', ${data.sisa_pembayaran_raw || 0})" 
                        class="btn-reservasi btn-checkout" style="width: 100%;">
                    <i class="fas fa-credit-card"></i>
                    Bayar Sekarang (${formatRupiah(data.sisa_pembayaran || 0)})
                </button>
            </div>
        `;
    }
    // Jika perlu chat admin - REMOVED per user request
    else if (['menunggu_konfirmasi_pembatalan', 'pembatalan_dp', 'pembatalan_lunas'].includes(statusClass)) {
        // Do nothing / Just show info in status section
    }
    // Jika bisa dibatalkan (Paid or DP)
    else if (data.show_cancel && !['dibatalkan', 'selesai'].includes(statusClass)) {
        actions = `
            <div class="detail-section">
                <button onclick="handleCancelReservation(${id})" 
                        class="btn-reservasi" style="width: 100%; background: var(--danger);">
                    <i class="fas fa-times-circle"></i>
                    Batalkan Reservasi
                </button>
            </div>
        `;
    }
    
    return actions;
}

// Fungsi untuk add to cart dari modal
function addToPaymentCartFromModal(id, name, amount) {
    // Add ke cart
    paymentCart[id] = { amount: amount, name: name };
    
    // Update checkbox di list jika ada
    $(`.payment-checkbox[data-id="${id}"]`).prop('checked', true);
    
    // Update tampilan cart
    updatePaymentCart();
    
    // Close modal
    closeDetailModal();
    
    // Notifikasi
    Swal.fire({
        icon: 'success',
        title: 'Ditambahkan!',
        text: 'Reservasi ditambahkan ke keranjang pembayaran',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });
}

// Fungsi untuk menutup modal
function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// Fungsi untuk handle pembatalan dengan SweetAlert
async function handleCancelReservation(reservasiId) {
    const result = await Swal.fire({
        title: 'Batalkan Reservasi?',
        html: `Apakah Anda yakin ingin membatalkan reservasi <strong>#${reservasiId}</strong>?<br><small style="color: #6b7280;">Tindakan ini mungkin memerlukan konfirmasi admin.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tidak',
        reverseButtons: true
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch(`${CANCEL_BASE_URL}/${reservasiId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message || 'Reservasi berhasil dibatalkan',
                timer: 2000,
                showConfirmButton: false
            });
            
            // Close modal
            closeDetailModal();
            
            // Reload data
            loadReservations(currentPage, currentStatus, currentSearch);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message || 'Gagal membatalkan reservasi',
                confirmButtonColor: '#ef4444'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat membatalkan reservasi',
            confirmButtonColor: '#ef4444'
        });
    }
}

// Close modal saat klik backdrop atau ESC
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('detailModal');
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeDetailModal();
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeDetailModal();
        }
    });
});


    </script>
@endsection