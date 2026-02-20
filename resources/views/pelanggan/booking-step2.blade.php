@extends('layouts.pelanggan')

@section('content')
    <style>
        /* ========================================= */
        /* VARIABEL & BASE STYLE */
        /* ========================================= */
        :root {
            --pink: #ff6b9e;
            --pink-200: #fff0f6;
            --pink-dark: #e55588;
            --ink: #2d2a2a;
            --muted: #7a7a7a;
            --bg: #fff7fa;
            --border: #f2d8e2;
            --border-light: #f7e8ee;
            --shadow-sm: 0 2px 8px rgba(45, 42, 42, .04);
            --shadow-md: 0 6px 16px rgba(45, 42, 42, .08);
        }

        html,
        body {
            background: var(--bg);
            color: var(--ink)
        }

        /* ========================================= */
        /* LAYOUT & RESPONSIVITAS */
        /* ========================================= */
        .container-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: clamp(16px, 4vw, 24px);
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 24px
        }

        @media(max-width:1024px) {
            .container-page {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                margin-top: 16px;
            }
        }

        /* ========================================= */
        /* LAYANAN (MAIN CONTENT) */
        /* ========================================= */
        .main-content-title {
            font-size: 24px;
            font-weight: 800;
            margin: 8px 0 16px
        }

        .stepper {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 12px
        }

        .step {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--muted);
            font-weight: 700
        }

        .step .dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--pink-200);
            background: #fff;
            color: var(--muted);
            font-weight: 800
        }

        .step.active {
            color: var(--ink)
        }

        .step.active .dot {
            border-color: var(--pink);
            color: var(--pink);
            background: #fff
        }

        .step.done .dot {
            background: var(--pink);
            border-color: var(--pink);
            color: #fff
        }

        .step-line {
            height: 2px;
            background: var(--pink-200);
            flex: 1;
            transition: background 0.3s
        }

        .step.done+.step-line {
            background: var(--pink);
        }

        .service-row {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 16px;
            align-items: center;
            box-shadow: var(--shadow-sm);
            transition: .2s;
            margin-bottom: 14px
        }

        .service-row:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px)
        }

        .service-info-thumb {
            display: grid;
            grid-template-columns: 60px 1fr;
            gap: 12px;
            align-items: center
        }

        .service-info-thumb img {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid var(--border-light)
        }

        .service-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 2px
        }

        .service-meta {
            color: var(--muted);
            font-size: 14px
        }

        .btn-schedule {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: .2s;
            min-width: 200px
        }

        .btn-schedule:hover {
            background: var(--pink-200);
            border-color: var(--pink)
        }

        .btn-schedule.scheduled {
            background: var(--pink-200);
            border-color: var(--pink)
        }

        .btn-schedule i {
            color: var(--pink)
        }

        .schedule-text {
            flex: 1;
            text-align: left;
            color: var(--muted)
        }

        .btn-primary {
            width: 100%;
            background: var(--pink);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: .2s
        }

        .btn-primary:hover {
            background: var(--pink-dark);
            transform: translateY(-1px)
        }

        .btn-primary[disabled] {
            opacity: .5;
            cursor: not-allowed;
        }


        /* ========================================= */
        /* SIDEBAR (RINGKASAN KERANJANG) - Mirip Step 1 */
        /* ========================================= */
        .sidebar {
            position: sticky;
            top: 20px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px;
            box-shadow: var(--shadow-sm);
            width: 100%;
        }

        .sidebar-title {
            font-weight: 800;
            font-size: 20px;
            margin: 0 0 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-light)
        }

        /* Booking Card Style */
        .booking-card {
            background: #fff;
            border: 1px solid #f5e3ec;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 10px
        }

        .booking-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .booking-card-title {
            margin: 0;
            font-weight: 700;
            font-size: 14px;
            line-height: 1.2;
        }

        .booking-card-detail {
            color: #777;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .booking-card-price {
            color: var(--pink);
            font-weight: 800;
            font-size: 14px;
        }

        .btn-remove {
            background: none;
            border: 1px solid #efd6df;
            border-radius: 8px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #c33;
            cursor: pointer;
            font-size: 18px;
            line-height: 1;
            transition: .2s;
            padding: 0;
        }

        .btn-remove:hover {
            border-color: var(--pink-dark);
            color: var(--pink-dark);
            background: var(--pink-200);
        }

        #scheduleList {
            max-height: unset;
            padding-right: 0;
            margin-bottom: 12px;
        }

        /* Total Section */
        .booking-total {
            border-top: 2px solid var(--border);
            padding-top: 12px;
            margin-top: 0;
        }

        .booking-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            color: var(--ink);
            font-weight: 700;
        }


        /* ========================================= */
        /* MODAL (PILIH WAKTU) */
        /* ========================================= */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.506);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 10px;
            overflow-y: auto;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal {
            background: #ffffff;
            border-radius: 20px;
            max-width: min(860px, 95vw);
            width: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-height: 90vh;
            margin: auto;
            position: relative;
            overflow: hidden;
            border: 2px solid var(--border);
        }

        .modal-body {
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #ffffff;
        }

        .modal-header {
            padding: 18px 20px 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            background: #ffffff;
        }

        .modal-footer {
            padding: 18px 20px;
            flex-shrink: 0;
            border-top: 1px solid var(--border);
            background: #ffffff;
        }

        .modal-title-wrap {
            flex: 1;
        }

        .modal-title-wrap .modal-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--ink);
        }

        .btn-close-modal {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            font-size: 18px;
            color: var(--muted);
        }

        .btn-close-modal:hover {
            color: var(--ink);
        }

        .day-strip-wrap {
            overflow-x: auto;
            padding-bottom: 8px;
            margin-top: 12px;
            flex-shrink: 0;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .day-strip-wrap::-webkit-scrollbar {
            display: none;
        }

        .day-strip {
            display: flex;
            gap: 10px;
            padding-bottom: 5px;
        }

        .day-tab {
            min-width: 80px;
            flex-shrink: 0;
            background: #ffffff;
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 10px 8px;
            text-align: center;
            cursor: pointer;
            transition: .2s;
        }

        .day-tab:hover {
            border-color: var(--pink)
        }

        .day-tab.active {
            background: var(--pink);
            border-color: var(--pink)
        }

        .day-tab .dw {
            font-size: 13px;
            color: var(--muted);
            font-weight: 700
        }

        .day-tab .dd {
            font-weight: 800;
            font-size: 14px;
            margin-top: 4px;
            color: var(--ink)
        }

        .day-tab.active .dw,
        .day-tab.active .dd {
            color: #fff
        }

        .time-list-container {
            overflow-y: auto;
            flex-grow: 1;
            padding-top: 10px;
            max-height: 100%;
        }

        .time-list-wrap {
            max-width: 520px;
            margin: 0 auto
        }

        .time-list {
            display: flex;
            flex-direction: column;
            gap: 10px
        }

        .time-slot-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 14px;
            border: 2px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            transition: .2s;
            background: #ffffff;
        }

        .time-slot-row .time-label-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .time-slot-row:hover {
            border-color: var(--pink);
            background: var(--pink-200)
        }

        .time-slot-row.active {
            border-color: var(--pink);
            background: var(--pink-200)
        }

        .time-slot-row.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #f6f6f6;
            border-style: dashed;
        }

        .time-slot-row.disabled:hover {
            border-color: var(--border);
            background: #f6f6f6;
        }

        .time-label {
            font-weight: 700;
            font-size: 16px;
            color: var(--ink);
        }

        .time-meta {
            font-size: 12px;
            color: var(--muted);
            font-weight: 500;
        }

        .time-add-btn {
            font-size: 14px;
            font-weight: 800;
            color: var(--pink);
        }

        .week-range {
            margin-top: 4px;
            font-size: 14px;
            font-weight: 400;
            color: var(--muted);
        }
    </style>

    {{-- Font Awesome harus dipanggil. Ganti dengan CDN yang sama dengan yang Anda gunakan. --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

@php
    $pengaturan = DB::table('pengaturan_booking')->first();
    $bookingAktif = $pengaturan ? $pengaturan->booking_aktif : true;
@endphp

<div class="container-page">
    <div class="main-content">
        <div class="stepper" aria-label="{{ __('booking_step2.step2') }}">
            <div class="step done">
                <div class="dot">1</div>
                <div>{{ __('booking_step2.step1') }}</div>
            </div>
            <div class="step-line"></div>
            <div class="step active">
                <div class="dot">2</div>
                <div>{{ __('booking_step2.step2') }}</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="dot">3</div>
                <div>{{ __('booking_step2.step3') }}</div>
            </div>
        </div>

        <h1 class="main-content-title">{{ __('booking_step2.page_title') }}</h1>

        {{-- Menampilkan pesan error dari session (jika ada) --}}
        @if (session('error'))
            <div style="padding: 12px; background: #fee2e2; color: #dc2626; border-radius: 8px; margin-bottom: 16px;">
                {{ session('error') }}
            </div>
        @endif

        <div id="serviceListContainer">
            @forelse ($cart as $item)
                {{-- Menggunakan null coalescing untuk fallback ID --}}
                @php
                    $reservasiId = $item['reservasi']['id_reservasi'] ?? $item['id_reservasi'];
                    $isScheduled = (
                        isset($item['reservasi']['tanggal_reservasi']) && $item['reservasi']['tanggal_reservasi'] && 
                        isset($item['reservasi']['waktu_reservasi']) && $item['reservasi']['waktu_reservasi']
                    );
                @endphp

                <div class="service-row" data-reservasi-id="{{ $reservasiId }}">
                    <div class="service-info-thumb">
                        <img src="{{ $item['image'] ?? asset('img/favicon.svg') }}" alt="{{ $item['title'] }}"
                            onerror="this.onerror=null;this.src='{{ asset('img/favicon.svg') }}';">
                        <div>
                            <div class="service-title">{{ $item['title'] }}</div>
                            <div class="service-meta">{{ $item['duration'] }} {{ __('booking_step2.minutes') }} | Rp
                                {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="service-actions">
                        <button
                            {{-- Menggunakan variabel $isScheduled untuk menghindari error Undefined Array Key --}}
                            class="btn-schedule {{ $isScheduled ? 'scheduled' : '' }}"
                            type="button" 
                            onclick="openModal({{ $reservasiId }}, {{ $item['id'] }})"
                            data-duration="{{ $item['duration'] }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="schedule-text" id="schedule-text-{{ $reservasiId }}">
                                @if ($isScheduled)
                                    {{ __('booking_step2.loading_schedule') }}
                                @else
                                    {{ __('booking_step2.select_schedule') }}
                                @endif
                            </span>
                            <i class="fas fa-chevron-right" style="color:#b3b3b3"></i>
                        </button>
                    </div>
                </div>
            @empty
                <p style="text-align:center; padding: 20px;">
                    {{ __('booking_step2.cart_empty') }}
                    <a href="{{ route('booking.categories') }}">{{ __('booking_step2.cart_empty_link') }}</a>.
                </p>
            @endforelse
        </div>
    </div>

    <aside class="sidebar">
        <h3 class="sidebar-title">{{ __('booking_step2.sidebar_title') }}</h3>
        <div id="scheduleList" class="sidebar-item-list">
            <p style="color:var(--muted); text-align:center;">{{ __('booking_step2.sidebar_loading') }}</p>
        </div>

        <div class="booking-total">
            <div class="booking-total-row">
                <span>{{ __('booking_step2.total_payment') }}</span>
                <span id="subtotal-sidebar">Rp 0</span>
            </div>
            <button id="payBtn" class="btn-primary" type="button" onclick="goPay()" disabled>
                {{ __('booking_step2.next_button') }}
            </button>
        </div>
    </aside>
</div>

{{-- MODAL JADWAL (Markup statis) --}}
<div id="modalBackdrop" class="modal-backdrop" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-header">
            <div class="modal-title-wrap">
                <div class="modal-title" id="modalTitle">{{ __('booking_step2.modal_select_date') }}</div>
                <div id="weekRange" class="week-range"></div>
            </div>

            <button class="btn-close-modal" type="button" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="day-strip-wrap">
                <div id="dayStrip" class="day-strip"></div>
            </div>

            <div class="time-list-container">
                <div class="time-list-wrap">
                    <div id="timeList" class="time-list"></div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn-primary" type="button" onclick="applySchedule()">{{ __('booking_step2.done_button') }}</button>
        </div>
    </div>
</div>

<!-- Add SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ================== TRANSLATIONS ==================
const TRANS = {
    step1: "{{ __('booking_step2.step1') }}",
    step2: "{{ __('booking_step2.step2') }}",
    step3: "{{ __('booking_step2.step3') }}",
    pageTitle: "{{ __('booking_step2.page_title') }}",
    loadingSchedule: "{{ __('booking_step2.loading_schedule') }}",
    selectSchedule: "{{ __('booking_step2.select_schedule') }}",
    minutes: "{{ __('booking_step2.minutes') }}",
    duration: "{{ __('booking_step2.duration') }}",
    schedule: "{{ __('booking_step2.schedule') }}",
    scheduleNotSelected: "{{ __('booking_step2.schedule_not_selected') }}",
    sidebarTitle: "{{ __('booking_step2.sidebar_title') }}",
    cartEmpty: "{{ __('booking_step2.cart_empty') }}",
    totalPayment: "{{ __('booking_step2.total_payment') }}",
    weekRange: "{{ __('booking_step2.week_range') }}",
    noDatesAvailable: "{{ __('booking_step2.no_dates_available') }}",
    loadingDates: "{{ __('booking_step2.loading_dates') }}",
    loadingSlots: "{{ __('booking_step2.loading_slots') }}",
    selectDateFirst: "{{ __('booking_step2.select_date_first') }}",
    noSlotsAvailable: "{{ __('booking_step2.no_slots_available') }}",
    statusBooked: "{{ __('booking_step2.status_booked') }}",
    statusConflict: "{{ __('booking_step2.status_conflict') }}",
    bookedByOthers: "{{ __('booking_step2.booked_by_others') }}",
    conflictWithCart: "{{ __('booking_step2.conflict_with_cart') }}",
    slotUnavailable: "{{ __('booking_step2.slot_unavailable') }}",
    scheduleIncomplete: "{{ __('booking_step2.schedule_incomplete') }}",
    selectDateTime: "{{ __('booking_step2.select_date_time') }}",
    saving: "{{ __('booking_step2.saving') }}",
    saveFailed: "{{ __('booking_step2.save_failed') }}",
    saveError: "{{ __('booking_step2.save_error') }}",
    saveSuccess: "{{ __('booking_step2.save_success') }}",
    scheduleSaved: "{{ __('booking_step2.schedule_saved') }}",
    serverError: "{{ __('booking_step2.server_error') }}",
    removeService: "{{ __('booking_step2.remove_service') }}",
    removeConfirm: "{{ __('booking_step2.remove_confirm') }}",
    removeReloadInfo: "{{ __('booking_step2.remove_reload_info') }}",
    confirmYes: "{{ __('booking_step2.confirm_yes') }}",
    confirmNo: "{{ __('booking_step2.confirm_no') }}",
    removing: "{{ __('booking_step2.removing') }}",
    removeSuccess: "{{ __('booking_step2.remove_success') }}",
    removeFailed: "{{ __('booking_step2.remove_failed') }}",
    removeError: "{{ __('booking_step2.remove_error') }}",
    allScheduleRequired: "{{ __('booking_step2.all_schedule_required') }}",
    noScheduledItems: "{{ __('booking_step2.no_scheduled_items') }}",
    noCompleteSchedule: "{{ __('booking_step2.no_complete_schedule') }}",
    failedLoadSchedule: "{{ __('booking_step2.failed_load_schedule') }}",
    failedLoadServices: "{{ __('booking_step2.failed_load_services') }}",
    failedLoadCart: "{{ __('booking_step2.failed_load_cart') }}",
    okButton: "{{ __('booking_step2.ok_button') }}",
    error: "{{ __('booking_step2.error') }}"
};

// ================== GLOBAL VARIABLES ==================
let PHP_CART_ITEMS = @json($cart); 
let SCHEDULES = {};
let LIVE_CART_DATA = [];
let ALL_SLOT_DATA = null;
let DATES = [];
let activeDuration = 0;
let activeReservasiId = null;
let activeServiceId = null;
let tempSelected = { date: null, time: null };

const SLOT_JADWAL_ROUTE = '{{ route('booking.getRentangSlotDinamis') }}'; 
const UPDATE_SCHEDULE_ROUTE = '{{ route('booking.schedule.update') }}'; 
const STEP3_ROUTE = '{{ route('booking.step3') }}';
const GET_CART_ROUTE = '{{ route('booking.cart.get') }}'; 
const MANAGE_CART_ROUTE = '{{ route('booking.cart.manage') }}'; 
const ASSET_DEFAULT_IMAGE = '{{ asset('img/favicon.svg') }}';
const CSRF_TOKEN = '{{ csrf_token() }}';

const nf = n => new Intl.NumberFormat('id-ID').format(n);
const DAY_NAMES = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
const MONTH_NAMES = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

function formatDateLabel(dateString) {
    if (!dateString) return 'Tanggal tidak valid';
    const [year, month, day] = dateString.split('-').map(Number);
    const date = new Date(year, month - 1, day, 12); 
    const dw = DAY_NAMES[date.getDay()];
    const mon = MONTH_NAMES[date.getMonth()].substring(0, 3);
    const dd = date.getDate().toString().padStart(2, '0');
    return `${dw}, ${dd} ${mon}`;
}

function createDateTime(date, time) {
    const [year, month, day] = date.split('-').map(Number);
    const [hour, minute] = time.split(':').map(Number);
    return new Date(year, month - 1, day, hour, minute, 0, 0);
}

function calculateEndTime(date, time, duration) {
    const start = createDateTime(date, time);
    return new Date(start.getTime() + duration * 60000);
}

// ================== FETCH DATA ==================
async function fetchCartData() {
    try {
        const res = await fetch(GET_CART_ROUTE);
        const data = await res.json();
        if (!data.success) {
            console.error(TRANS.failedLoadCart, data.message);
            return false;
        }
        LIVE_CART_DATA = data.cart;
        SCHEDULES = {};
        LIVE_CART_DATA.forEach(item => {
            const r = item.reservasi;
            const reservasiId = r.id_reservasi || item.id_reservasi; 
            if (r.tanggal_reservasi && r.waktu_reservasi) {
                SCHEDULES[reservasiId] = {
                    date: r.tanggal_reservasi,
                    time: r.waktu_reservasi,
                    duration: item.durasi
                };
            }
        });
        PHP_CART_ITEMS = LIVE_CART_DATA.map(item => {
            const originalItem = PHP_CART_ITEMS.find(i => (i.id_reservasi || i.reservasi.id_reservasi) === (item.reservasi.id_reservasi || item.id_reservasi));
            const originalImage = originalItem?.image || ASSET_DEFAULT_IMAGE;
            const reservasiId = item.reservasi.id_reservasi || item.id_reservasi;
            return {
                id: item.id_layanan,
                id_reservasi: reservasiId,
                title: item.nama_layanan,
                price: item.harga,
                duration: item.durasi,
                image: originalImage,
                reservasi: item.reservasi
            };
        });
        return true;
    } catch (e) {
        console.error('AJAX Error:', e);
        return false;
    }
}

async function fetchAndProcessAllSlots(serviceId) {
    try {
        const res = await fetch(SLOT_JADWAL_ROUTE, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_layanan: serviceId })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || TRANS.failedLoadSchedule);
        ALL_SLOT_DATA = data.data_slot_per_tanggal;
        DATES = ALL_SLOT_DATA.map(day => {
            const yyyyMmDd = day.date_ymd;
            if (!yyyyMmDd) return null;
            const dateParts = yyyyMmDd.split('-').map(Number);
            if (dateParts.length < 3) return null;
            const [year, month, dayNum] = dateParts;
            const dateObj = new Date(year, month - 1, dayNum);
            return {
                date: yyyyMmDd,
                dw: DAY_NAMES[dateObj.getDay()],
                mon: MONTH_NAMES[dateObj.getMonth()].substring(0, 3),
                dd: String(dateObj.getDate()).padStart(2, '0'),
                slots: day.slots
            };
        }).filter(d => d !== null);
        return true;
    } catch (e) {
        console.error("Error fetching:", e);
        ALL_SLOT_DATA = [];
        DATES = [];
        return false;
    }
}

// ================== MODAL FUNCTIONS ==================
async function openModal(reservasiId, serviceId) {
    activeReservasiId = reservasiId;
    activeServiceId = serviceId;
    const svc = PHP_CART_ITEMS.find(i => i.id == serviceId);
    activeDuration = svc?.duration || 0;

    document.getElementById('dayStrip').innerHTML = `<p style="text-align:center; padding: 10px 0;">${TRANS.loadingDates}</p>`;
    document.getElementById('timeList').innerHTML = `<p style="color:var(--muted);text-align:center"><i class="fas fa-spinner fa-spin"></i> ${TRANS.loadingSlots}</p>`;

    const backdrop = document.getElementById('modalBackdrop');
    backdrop.classList.add('show');
    backdrop.style.display = 'flex';
    backdrop.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    await fetchCartData();
    const fetchSuccess = await fetchAndProcessAllSlots(serviceId);

    if (!fetchSuccess) {
        document.getElementById('timeList').innerHTML = `<p style="color:#dc2626;text-align:center">${TRANS.failedLoadSchedule}</p>`;
        return;
    }

    const existingSchedule = SCHEDULES[reservasiId] ? { ...SCHEDULES[reservasiId] } : { date: null, time: null };
    document.getElementById('modalTitle').textContent = `${TRANS.schedule}: ${svc?.title || ''}`;

    let defaultDate = DATES.length > 0 ? DATES[0].date : null;
    if (existingSchedule.date && DATES.some(d => d.date === existingSchedule.date)) {
        defaultDate = existingSchedule.date;
    } else if (existingSchedule.date && !DATES.some(d => d.date === existingSchedule.date)) {
        existingSchedule.time = null;
    }

    tempSelected = { date: defaultDate, time: existingSchedule.time };
    renderWeekNav();
    renderDayStrip();
    renderTimeList();
}

function closeModal() {
    const b = document.getElementById('modalBackdrop');
    b.classList.remove('show');
    b.style.display = 'none';
    b.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    activeReservasiId = null;
    activeServiceId = null;
    activeDuration = 0;
    tempSelected = { date: null, time: null };
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

function renderWeekNav() {
    const range = document.getElementById('weekRange');
    if (DATES.length > 0) {
        const start = formatDateLabel(DATES[0].date);
        const end = formatDateLabel(DATES[DATES.length - 1].date);
        range.textContent = `${TRANS.weekRange}: ${start} - ${end}`;
    } else {
        range.textContent = TRANS.noDatesAvailable;
    }
}

function renderDayStrip() {
    const wrap = document.getElementById('dayStrip');
    wrap.innerHTML = '';
    DATES.forEach(d => {
        if (!d || !d.dw || !d.mon) return;
        const tab = document.createElement('button');
        tab.type = 'button';
        tab.className = 'day-tab' + (tempSelected.date === d.date ? ' active' : '');
        tab.innerHTML = `<div class="dw">${d.dw}</div><div class="dd">${d.dd} ${d.mon}</div>`;
        tab.onclick = () => {
            if (tempSelected.date === d.date) return;
            tempSelected.date = d.date;
            tempSelected.time = null;
            renderDayStrip();
            renderTimeList();
            tab.scrollIntoView({ behavior: "smooth", block: "nearest", inline: "center" });
        };
        wrap.appendChild(tab);
    });
    if (tempSelected.date) {
        const activeTab = wrap.querySelector('.day-tab.active');
        if (activeTab) activeTab.scrollIntoView({ behavior: "smooth", block: "nearest", inline: "center" });
    }
}

async function renderTimeList() {
    const list = document.getElementById('timeList');
    list.innerHTML = '';
    if (!tempSelected.date) {
        list.innerHTML = `<p style="color:var(--muted);text-align:center">${TRANS.selectDateFirst}</p>`;
        return;
    }

    const selectedDayData = DATES.find(d => d.date === tempSelected.date);
    const availableSlots = selectedDayData ? selectedDayData.slots : [];
    const disabledBySelf = {};

    if (availableSlots.length === 0) {
        list.innerHTML = `<p style="color:var(--muted);text-align:center">${TRANS.noSlotsAvailable}</p>`;
        return;
    }

    availableSlots.forEach(s => {
        const t = s.time;
        const isReservedByOthers = s.disabled;
        const isConflictBySelf = disabledBySelf[t];
        const isActive = tempSelected.time === t;
        const isDisabled = isReservedByOthers || isConflictBySelf;
        const reservasiDetail = s.reservasi;

        let statusText = '';
        let metaText = '';

        if (reservasiDetail) {
            statusText = ` (${TRANS.statusBooked})`;
            metaText = TRANS.bookedByOthers;
        } else if (isConflictBySelf) {
            statusText = ` (${TRANS.statusConflict})`;
            metaText = TRANS.conflictWithCart;
        } else if (isReservedByOthers) {
            statusText = ` (${TRANS.slotUnavailable})`;
            metaText = reservasiDetail ? TRANS.bookedByOthers : TRANS.slotUnavailable;
        }

        const row = document.createElement('div');
        row.className = 'time-slot-row' + (isActive ? ' active' : '') + (isDisabled ? ' disabled' : '');

        let innerHTML = `
        <div class="time-label-wrap">
            <div class="time-label">${t + statusText}</div>
            <div class="time-add-btn">${isActive ? '✓' : '+'}</div>
        </div>`;
        if (metaText) innerHTML += `<div class="time-meta">${metaText}</div>`;
        row.innerHTML = innerHTML;

        if (!isDisabled) {
            row.onclick = () => {
                tempSelected.time = (tempSelected.time === t) ? null : t;
                renderTimeList();
            };
        }
        list.appendChild(row);
    });
}

async function applySchedule() {
    if (!tempSelected.date || !tempSelected.time) {
        Swal.fire({
            icon: 'warning',
            title: TRANS.scheduleIncomplete,
            text: TRANS.selectDateTime,
            confirmButtonText: TRANS.okButton,
            confirmButtonColor: '#3b82f6'
        });
        return;
    }

    const date = tempSelected.date;
    const time = tempSelected.time;
    const reservasiId = activeReservasiId;
    const updateData = { id_reservasi: reservasiId, tanggal: date, time: time };

    const applyBtn = document.querySelector('.modal-footer .btn-primary');
    const originalBtnHtml = applyBtn.innerHTML;
    applyBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${TRANS.saving}`;
    applyBtn.disabled = true;

    try {
        const res = await fetch(UPDATE_SCHEDULE_ROUTE, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json' },
            body: JSON.stringify(updateData)
        });
        const result = await res.json();
        applyBtn.innerHTML = originalBtnHtml;
        applyBtn.disabled = false;

        if (!result.success) {
            Swal.fire({
                icon: 'error',
                title: TRANS.saveFailed,
                text: result.message || TRANS.saveError,
                confirmButtonText: TRANS.okButton,
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        const item = PHP_CART_ITEMS.find(i => i.id_reservasi === reservasiId);
        SCHEDULES[reservasiId] = { date: date, time: time, duration: item?.duration || 0 };
        await fetchCartData();
        updateBadges();
        renderSidebar();
        
        Swal.fire({
            icon: 'success',
            title: TRANS.saveSuccess,
            text: TRANS.scheduleSaved,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        closeModal();

    } catch (e) {
        console.error('AJAX Error:', e);
        applyBtn.innerHTML = originalBtnHtml;
        applyBtn.disabled = false;
        Swal.fire({
            icon: 'error',
            title: TRANS.error,
            text: TRANS.serverError,
            confirmButtonText: TRANS.okButton,
            confirmButtonColor: '#ef4444'
        });
    }
}

// ================== REMOVE & RENDER ==================
async function removeCartItemStep2(reservasiId, layananId) {
    const result = await Swal.fire({
        title: TRANS.removeService,
        html: `${TRANS.removeConfirm}<br><small style="color: #6b7280;">${TRANS.removeReloadInfo}</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: TRANS.confirmYes,
        cancelButtonText: TRANS.confirmNo,
        reverseButtons: true
    });

    if (!result.isConfirmed) return;

    const data = { id_layanan: layananId, action: 'remove', id_reservasi: reservasiId };
    const listEl = document.getElementById('scheduleList');
    const originalHtml = listEl.innerHTML;
    listEl.innerHTML = `<p style="color:var(--muted); text-align:center;"><i class="fas fa-spinner fa-spin"></i> ${TRANS.removing}</p>`;

    try {
        const res = await fetch(MANAGE_CART_ROUTE, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();

        if (res.ok && result.success) {
            await Swal.fire({
                icon: 'success',
                title: TRANS.saveSuccess,
                text: result.message || TRANS.removeSuccess,
                timer: 2000,
                showConfirmButton: false,
                timerProgressBar: true
            });
            window.location.href = result.cart.length === 0 ? '{{ route('booking.categories') }}' : '{{ route('booking.step2') }}';
        } else {
            listEl.innerHTML = originalHtml;
            Swal.fire({
                icon: 'error',
                title: TRANS.removeFailed,
                text: result.message || TRANS.removeError,
                confirmButtonText: TRANS.okButton,
                confirmButtonColor: '#ef4444'
            });
        }
    } catch (e) {
        listEl.innerHTML = originalHtml;
        Swal.fire({
            icon: 'error',
            title: TRANS.error,
            text: TRANS.serverError,
            confirmButtonText: TRANS.okButton,
            confirmButtonColor: '#ef4444'
        });
    }
}

function updateBadges() {
    let allSet = true;
    let totalItems = 0;

    PHP_CART_ITEMS.forEach(s => {
        totalItems++;
        const reservasiId = s.id_reservasi;
        const el = document.getElementById('schedule-text-' + reservasiId);
        const svcRow = document.querySelector(`.service-row[data-reservasi-id="${reservasiId}"]`);
        if (!el || !svcRow) return;
        const btn = el.parentElement;
        const schedule = SCHEDULES[reservasiId];

        if (schedule && schedule.date && schedule.time) {
            const dateLabel = formatDateLabel(schedule.date);
            el.textContent = `${dateLabel} @ ${schedule.time}`;
            btn.classList.add('scheduled');
        } else {
            el.textContent = TRANS.selectSchedule;
            btn.classList.remove('scheduled');
            allSet = false;
        }
    });

    const payBtn = document.getElementById('payBtn');
    if (payBtn) payBtn.disabled = !allSet || totalItems === 0; 
}

function renderSidebar() {
    const list = document.getElementById('scheduleList');
    const subtotalSidebarEl = document.getElementById('subtotal-sidebar');
    const payBtn = document.getElementById('payBtn');
    let html = '';
    let subtotal = 0;

    if (PHP_CART_ITEMS.length === 0) {
        list.innerHTML = `<p style="color:var(--muted); text-align:center;">${TRANS.cartEmpty}</p>`;
        if (subtotalSidebarEl) subtotalSidebarEl.textContent = 'Rp 0';
        if (payBtn) payBtn.disabled = true;
        return;
    }

    PHP_CART_ITEMS.sort((a, b) => a.id_reservasi - b.id_reservasi);

    PHP_CART_ITEMS.forEach(item => {
        const reservasiId = item.id_reservasi;
        const sc = SCHEDULES[reservasiId];
        const itemPrice = item.price || 0;
        subtotal += itemPrice;

        let metaText = `${TRANS.duration}: ${item.duration} ${TRANS.minutes}`;

        if (sc && sc.date && sc.time) {
            const dateLabel = formatDateLabel(sc.date);
            metaText = `${TRANS.schedule}: ${dateLabel} @ ${sc.time}`;
        } else {
            metaText = `<span style="color:#e55588; font-weight:700;">${TRANS.scheduleNotSelected}</span>`;
        }

        html += `
        <div class="booking-card" data-reservasi-id="${reservasiId}">
            <div class="booking-card-header">
                <h4 class="booking-card-title">${item.title}</h4>
                <button class="btn-remove" type="button" onclick="removeCartItemStep2(${reservasiId}, ${item.id})">×</button>
            </div>
            <div class="booking-card-detail">${metaText}</div>
            <div class="booking-card-price">Rp ${nf(itemPrice)}</div>
        </div>`;
    });

    list.innerHTML = html;
    if (subtotalSidebarEl) subtotalSidebarEl.textContent = `Rp ${nf(subtotal)}`;
    updateBadges();
}

// ================== FINAL SUBMIT ==================
function goPay() {
    const payBtn = document.getElementById('payBtn');
    if (payBtn.disabled) {
        Swal.fire({
            icon: 'warning',
            title: TRANS.scheduleIncomplete,
            text: TRANS.allScheduleRequired,
            confirmButtonText: TRANS.okButton,
            confirmButtonColor: '#3b82f6'
        });
        return;
    }

    let scheduledIds = [];
    PHP_CART_ITEMS.forEach(item => {
        const reservasiId = item.id_reservasi;
        const schedule = SCHEDULES[reservasiId];
        if (schedule && schedule.date && schedule.time) scheduledIds.push(reservasiId);
    });

    if (scheduledIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: TRANS.noScheduledItems,
            text: TRANS.noCompleteSchedule,
            confirmButtonText: TRANS.okButton,
            confirmButtonColor: '#3b82f6'
        });
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = STEP3_ROUTE; 

    let csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_token';
    csrfField.value = CSRF_TOKEN;
    form.appendChild(csrfField);

    scheduledIds.forEach(id => {
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'reservasi_ids[]'; 
        input.value = id;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

// ================== INIT ==================
document.addEventListener('DOMContentLoaded', async () => {
    const modal = document.getElementById('modalBackdrop');
    if (modal) document.body.appendChild(modal);

    const success = await fetchCartData();

    if (success) {
        LIVE_CART_DATA.forEach(item => {
            const r = item.reservasi;
            const reservasiId = item.reservasi.id_reservasi || item.id_reservasi; 
            if (r.tanggal_reservasi && r.waktu_reservasi) {
                SCHEDULES[reservasiId] = {
                    date: r.tanggal_reservasi,
                    time: r.waktu_reservasi,
                    duration: item.durasi
                };
            }
        });
        renderSidebar();
        updateBadges();
    } else {
        document.getElementById('serviceListContainer').innerHTML = `<p style="color:#dc2626; text-align:center;">${TRANS.failedLoadServices}</p>`;
        document.getElementById('scheduleList').innerHTML = `<p style="color:#dc2626; text-align:center;">${TRANS.failedLoadCart}</p>`;
        const payBtn = document.getElementById('payBtn');
        if (payBtn) payBtn.disabled = true;
    }
});

window.removeCartItemStep2 = removeCartItemStep2;
window.openModal = openModal; 
window.goPay = goPay;
</script>
@endsection