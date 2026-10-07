@extends('layouts.pelanggan')

@section('title', 'Konfirmasi & Pembayaran')

@section('content')
<style>
/* 🎨 CSS GLOBAL VARIABLES */
:root {
    --primary: #e91e63;
    --primary-dark: #c2185b;
    --primary-light: #fbe5f1;
    --text: #333;
    --muted: #777;
    --bg: #f5f6fb;
    --border-light: #f1e4ed;
    --border-primary: #f1b8d6;
    --radius-lg: 16px;
    --radius-md: 12px;
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
    --shadow-md: 0 10px 25px rgba(0,0,0,0.12);
}
/* 🧱 LAYOUT & CONTAINER */
html, body {
    background: var(--bg);
    color: var(--text);
}
.container-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px;
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 24px;
}
@media (max-width: 1024px) {
    .container-page {
        grid-template-columns: 1fr;
    }
}
.card {
    background: #fff;
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
    box-shadow: var(--shadow-sm);
}
.section-title {
    margin: 0 0 8px;
    font-weight: 700;
    font-size: 22px;
    color: var(--text);
}
.section-subtitle {
    margin: 0 0 16px;
    font-size: 13px;
    color: var(--muted);
}
/* 📝 RESERVASI LIST (LEFT) */
.reservasi-list-header {
    margin-bottom: 12px;
}
.reservasi-card {
    border: 1px solid #f2d6e7;
    border-radius: 14px;
    padding: 12px 14px;
    margin-bottom: 12px;
    background: #fff;
    transition: box-shadow 0.2s, transform 0.15s, border-color 0.2s;
}
.reservasi-card:hover {
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
    border-color: var(--primary);
    transform: translateY(-2px);
}
.reservasi-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.reservasi-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.reservasi-title {
    font-weight: 700;
    font-size: 14px;
    color: var(--text);
}
.reservasi-meta {
    font-size: 12px;
    color: var(--muted);
    margin-top: 2px;
}
.reservasi-checkbox {
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
}
.chip-status {
    border-radius: 999px;
    padding: 3px 10px;
    background: var(--primary-light);
    color: var(--primary-dark);
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
.reservasi-items-wrapper {
    margin-top: 10px;
    border-top: 1px dashed #f2d6e7;
    padding-top: 8px;
}
.item {
    display: grid;
    grid-template-columns: 54px 1fr auto;
    gap: 10px;
    align-items: center;
    padding: 6px 0;
}
.item img {
    width: 54px;
    height: 54px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid var(--border-light);
}
.item-title {
    font-weight: 600;
    font-size: 13px;
    color: var(--text);
}
.item-sub {
    color: var(--muted);
    font-size: 11px;
}
.price {
    color: var(--primary);
    font-weight: 700;
    font-size: 13px;
    white-space: nowrap;
}
.reservasi-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 6px;
    font-size: 12px;
    gap: 10px;
    flex-wrap: wrap;
}
.reservasi-footer strong {
    font-size: 13px;
}
.reservasi-summary-label {
    color: var(--muted);
}
/* 💳 PAYMENT OPTIONS (RIGHT) */
.select-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin: 12px 0 8px;
}
.radio {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    background: #fff;
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    padding: 10px 12px;
    cursor: pointer;
    flex: 1 1 47%;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s, opacity 0.2s;
    font-size: 13px;
}
.radio.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.radio:hover:not(.disabled) {
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    background: #fff7fc;
}
.radio input {
    margin-top: 2px;
    accent-color: var(--primary);
}
.radio-title {
    font-weight: 600;
    margin-bottom: 2px;
}
.radio-desc {
    font-size: 12px;
    color: var(--muted);
}
.help {
    color: var(--muted);
    font-size: 12px;
    margin-top: 6px;
}
/* 🔘 BUTTONS */
.btn-primary {
    width: 100%;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    padding: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s;
    font-size: 15px;
    box-shadow: var(--shadow-md);
}
.btn-primary:hover:not(:disabled) {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 12px 24px rgba(233, 30, 99, 0.4);
}
.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}
.btn-outline {
    width: 100%;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: var(--radius-md);
    padding: 12px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 8px;
    font-size: 13px;
    color: var(--muted);
    transition: 0.2s;
    text-align: center;
    text-decoration: none;
    display: inline-block;
}
.btn-outline:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: #fff7fc;
}
/* 💰 TOTAL DISPLAY */
.total-pay-box {
    margin-top: 16px;
    padding: 10px 12px;
    border-radius: var(--radius-md);
    background: #fff7fc;
    border: 1px dashed var(--border-primary);
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 12px;
}
.total-pay-label {
    font-size: 13px;
    color: var(--muted);
}
.total-pay-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-dark);
}
/* 🔄 OVERLAY */
.loading-overlay {
    position: fixed;
    inset: 0;
    background: rgba(245, 246, 251, 0.86);
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
    z-index: 1000;
    text-align: center;
    padding: 20px;
}

.total-pay-value {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}
/* Responsive tweaks */
@media (max-width: 768px) {
    .container-page {
        padding: 16px;
    }
    .section-title {
        font-size: 18px;
    }
    .reservasi-card {
        padding: 10px 12px;
    }
}
</style>
<div class="container-page">
    {{-- LEFT: LIST RESERVASI --}}
    <div class="card">
        <div class="reservasi-list-header">
            <div>
                <h3 class="section-title">{{ __('booking_step3.select_reservation') }}</h3>
                <p class="section-subtitle">{{ __('booking_step3.select_reservation_desc') }}</p>
            </div>
        </div>

        @php
            $dpTipe = $dpDisplay['tipe'] ?? 'persen';
            $dpValue = $dpDisplay['value'] ?? 30;
        @endphp

        @foreach($cards as $card)
            @php
                $reservasiModel = $card['reservasi'];
                $sDate = \Carbon\Carbon::parse($reservasiModel->tanggal_reservasi ?? now())->format('d M Y');
                $sTime = substr($reservasiModel->waktu_reservasi ?? '00:00:00', 0, 5);
                $total = (float) $card['finalAmount'];
                $dpAmount = (float) $card['dpAmount'];
                $totalPaid = (float) $card['totalPaid'];
                $remaining = (float) $card['remaining'];
                $statusText = $card['statusText']; 
            @endphp

            <div class="reservasi-card"
                data-id="{{ $reservasiModel->id_reservasi }}"
                data-final="{{ $total }}"
                data-dp="{{ $dpAmount }}"
                data-paid="{{ $totalPaid }}"
                data-remaining="{{ $remaining }}">
                <div class="reservasi-header">
                    <div class="reservasi-title-wrap">
                        <input type="checkbox" class="reservasi-checkbox" value="{{ $reservasiModel->id_reservasi }}" checked>
                        <div>
                            <div class="reservasi-title">{{ __('booking_step3.reservation') }} #{{ $reservasiModel->id_reservasi }}</div>
                            <div class="reservasi-meta">
                                {{ $sDate }} • {{ $sTime }}<br>
                                {{ __('booking_step3.total_bill') }}: Rp {{ number_format($total,0,',','.') }}
                            </div>
                        </div>
                    </div>
                    <span class="chip-status">{{ $statusText }}</span>
                </div>

                <div class="reservasi-items-wrapper">
                    @foreach($card['items'] as $svc)
                        @php $hargaBayar = (float)$svc['price']; @endphp
                        <div class="item">
                            <img src="{{ $svc['image'] }}" alt="{{ $svc['title'] }}"
                                onerror="this.onerror=null;this.src='{{ asset('img/defaults/layanan.png') }}';">
                            <div>
                                <div class="item-title">{{ $svc['title'] }}</div>
                                <div class="item-sub">{{ $sDate }} • {{ $sTime }}</div>
                            </div>
                            <div class="price">Rp {{ number_format($hargaBayar, 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="reservasi-footer">
                    <div>
                        <div class="reservasi-summary-label">
                            {{ __('booking_step3.already_paid') }}:
                            <strong>Rp {{ number_format($totalPaid,0,',','.') }}</strong>
                        </div>
                        <div class="reservasi-summary-label">
                            {{ __('booking_step3.remaining') }}:
                            <strong>Rp {{ number_format($remaining,0,',','.') }}</strong>
                        </div>
                    </div>
                    <div style="text-align:right;font-size:11px;color:var(--muted);">
                        {{ __('booking_step3.standard_dp') }}:
                        @if ($dpTipe === 'persen')
                            {{ $dpValue }}%
                        @else
                            Rp {{ number_format($dpValue, 0, ',', '.') }} ({{ __('booking_step3.nominal') }})
                        @endif
                        <br>(± Rp {{ number_format($dpAmount,0,',','.') }})
                    </div>
                </div>
            </div>
        @endforeach

        @if(empty($cards))
            <p class="help">{{ __('booking_step3.no_reservation') }}</p>
        @endif

        <div class="help">
            <strong>Catatan & Kebijakan:</strong><br>
            {!! nl2br(e($kebijakan)) !!}
        </div>
    </div>

    {{-- RIGHT: KONFIRMASI & METODE --}}
    @php
        $firstCard = collect($cards)->first(); 
        $defaultDpAmount = $firstCard ? $firstCard['dpAmount'] : 0;
    @endphp

    <div class="card">
        <h3 class="section-title">{{ __('booking_step3.payment_confirmation') }}</h3>
        <p class="section-subtitle">{{ __('booking_step3.payment_confirmation_desc') }}</p>

        <div class="select-row">
            <label class="radio" id="radioDpWrapper">
                <input type="radio" name="paytype" value="dp" checked>
                <div>
                    <div class="radio-title">{{ __('booking_step3.pay_dp') }}</div>
                    <div class="radio-desc">{{ __('booking_step3.pay_dp_desc') }}</div>
                </div>
            </label>
            <label class="radio" id="radioFullWrapper">
                <input type="radio" name="paytype" value="full">
                <div>
                    <div class="radio-title">{{ __('booking_step3.pay_full') }}</div>
                    <div class="radio-desc">{{ __('booking_step3.pay_full_desc') }}</div>
                </div>
            </label>
        </div>

        <p class="help" id="dpDisabledNote" style="display:none;">{{ __('booking_step3.dp_disabled_note') }}</p>

        {{-- SECTION VOUCHER DISKON (BARU) --}}
        <div class="card" style="padding:12px 14px;margin-top:12px;">
            <div style="font-weight:700;margin-bottom:4px;font-size:14px;">{{ __('booking_step3.use_voucher') }}</div>
            <p class="help" style="margin-top:4px;">{{ __('booking_step3.use_voucher_desc') }}</p>

            <div class="select-row" style="margin-top:10px;">
                <label class="radio" style="flex-basis:48%;">
                    <input type="radio" name="usevoucher" value="no" checked>
                    <div>
                        <div class="radio-title">{{ __('booking_step3.no_voucher') }}</div>
                        <div class="radio-desc">{{ __('booking_step3.no_voucher_desc') }}</div>
                    </div>
                </label>
                <label class="radio" style="flex-basis:48%;">
                    <input type="radio" name="usevoucher" value="yes">
                    <div>
                        <div class="radio-title">{{ __('booking_step3.yes_voucher') }}</div>
                        <div class="radio-desc">{{ __('booking_step3.yes_voucher_desc') }}</div>
                    </div>
                </label>
            </div>

            <div id="voucherInputSection" style="display:none;margin-top:12px;">
                <label style="display:block;font-weight:600;font-size:13px;margin-bottom:6px;">
                    {{ __('booking_step3.voucher_code') }}
                </label>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="voucherCode" 
                           style="flex:1;padding:10px 12px;border:1px solid var(--border-light);border-radius:var(--radius-md);font-size:14px;text-transform:uppercase;"
                           placeholder="{{ __('booking_step3.voucher_placeholder') }}">
                    <button type="button" id="applyVoucherBtn" class="btn-primary" 
                            style="width:auto;padding:10px 24px;">
                        {{ __('booking_step3.apply') }}
                    </button>
                </div>
                <div id="voucherSuccess" style="display:none;margin-top:8px;padding:10px;background:#d4edda;border:1px solid #c3e6cb;border-radius:8px;color:#155724;font-size:13px;">
                    <strong id="voucherSuccessText"></strong>
                    <button type="button" id="removeVoucherBtn" style="float:right;background:none;border:none;color:#155724;cursor:pointer;font-weight:bold;font-size:16px;padding:0;margin-left:10px;" title="Hapus voucher">×</button>
                </div>
            </div>
        </div>

        {{-- SECTION METODE PEMBAYARAN --}}
        <div class="card" style="padding:12px 14px;margin-top:12px;">
            <div style="font-weight:700;margin-bottom:4px;font-size:14px;">{{ __('booking_step3.payment_method') }}</div>
            <p class="help" style="margin-top:4px;">{{ __('booking_step3.payment_method_desc') }}</p>

            @php $firstMethod = $methods->first(); @endphp

            @if($firstMethod)
                <label class="radio" style="margin-top:10px;flex-basis:100%;justify-content:space-between;">
                    <span>
                        <input type="radio" name="paymethod" value="{{ $firstMethod->id_metodePembayaran }}" checked>
                        <span style="font-weight:600;">{{ __('booking_step3.online_payment') }}</span>
                        <span style="display:block;font-size:12px;color:var(--muted);">{{ __('booking_step3.online_payment_desc') }}</span>
                    </span>
                    <span style="color:var(--muted);font-size:11px;white-space:nowrap;">Midtrans Snap</span>
                </label>
            @else
                <div class="help" style="margin-top:8px;">{{ __('booking_step3.no_payment_method') }}</div>
            @endif
        </div>

        {{-- TOTAL BAYAR --}}
        <div class="total-pay-box">
            <div class="total-pay-label">{{ __('booking_step3.total_to_pay') }}</div>
            <div class="total-pay-value" id="displayAmount">Rp {{ number_format($defaultDpAmount,0,',','.') }}</div>
        </div>

        <p class="help">{{ __('booking_step3.total_calculated_note') }}</p>

        <button class="btn-primary" id="payButton" type="button" style="margin-top:10px;">{{ __('booking_step3.continue_payment') }}</button>
        <a class="btn-outline" href="{{ route('booking.history') }}" style="margin-top:6px;">{{ __('booking_step3.back_to_history') }}</a>
    </div>
</div>

<div class="loading-overlay" id="loadingOverlay">{{ __('booking_step3.processing') }}</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

<script type="text/javascript"
    src="https://app.{{ config('midtrans.isProduction') ? 'midtrans' : 'sandbox.midtrans' }}.com/snap/snap.js"
    data-client-key="{{ config('midtrans.clientKey') }}">
</script>

<script>
const TRANS = {
    processing: "{{ __('booking_step3.processing') }}",
    processingReservations: "{{ __('booking_step3.processing_reservations') }}",
    paymentSuccessSaving: "{{ __('booking_step3.payment_success_saving') }}",
    paymentPendingSaving: "{{ __('booking_step3.payment_pending_saving') }}",
    noBillTitle: "{{ __('booking_step3.no_bill_title') }}",
    noBillText: "{{ __('booking_step3.no_bill_text') }}",
    selectMethodTitle: "{{ __('booking_step3.select_method_title') }}",
    selectMethodText: "{{ __('booking_step3.select_method_text') }}",
    failed: "{{ __('booking_step3.failed') }}",
    failedToken: "{{ __('booking_step3.failed_token') }}",
    saveFailedTitle: "{{ __('booking_step3.save_failed_title') }}",
    saveFailedText: "{{ __('booking_step3.save_failed_text') }}",
    pendingTitle: "{{ __('booking_step3.pending_title') }}",
    pendingText: "{{ __('booking_step3.pending_text') }}",
    savePendingFailed: "{{ __('booking_step3.save_pending_failed') }}",
    errorTitle: "{{ __('booking_step3.error_title') }}",
    errorText: "{{ __('booking_step3.error_text') }}",
    cancelledTitle: "{{ __('booking_step3.cancelled_title') }}",
    cancelledText: "{{ __('booking_step3.cancelled_text') }}",
    serverErrorTitle: "{{ __('booking_step3.server_error_title') }}",
    serverErrorText: "{{ __('booking_step3.server_error_text') }}",
    continuePayment: "{{ __('booking_step3.continue_payment') }}",
    selectReservationBtn: "{{ __('booking_step3.select_reservation_btn') }}"
};

const reservasiCards = document.querySelectorAll('.reservasi-card');
const displayAmountEl = document.getElementById('displayAmount');
const loading = document.getElementById('loadingOverlay');
const dpRadio = document.querySelector('input[name="paytype"][value="dp"]');
const fullRadio = document.querySelector('input[name="paytype"][value="full"]');
const dpDisabledNote = document.getElementById('dpDisabledNote');
const radioDpWrapper = document.getElementById('radioDpWrapper');
const payButton = document.getElementById('payButton');
const voucherSection = document.getElementById('voucherInputSection');
const voucherNoRadio = document.querySelector('input[name="usevoucher"][value="no"]');
const voucherYesRadio = document.querySelector('input[name="usevoucher"][value="yes"]');
const voucherSuccessDiv = document.getElementById('voucherSuccess');
const voucherCodeInput = document.getElementById('voucherCode');
const applyVoucherBtn = document.getElementById('applyVoucherBtn');

const successUrlMulti = "{{ route('booking.successMulti', ['ids' => ':ids']) }}"; 
const processUrl = "{{ route('booking.midtrans.proses') }}";
const callbackUrl = "{{ route('booking.midtrans.callback') }}";
const validateVoucherUrl = "{{ route('booking.voucher.validate') }}";
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// State voucher
let appliedVoucher = null;

function formatRupiah(num) {
    const number = Number(num);
    if (isNaN(number) || number < 0) return 'Rp 0';
    return 'Rp ' + number.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

function getDiskonForReservasi(reservasiId) {
    if (!appliedVoucher || !appliedVoucher.applicable_reservasi) {
        return 0;
    }
    
    const discountData = appliedVoucher.applicable_reservasi.find(
        item => item.id_reservasi === reservasiId
    );
    
    return discountData ? parseFloat(discountData.diskon) : 0;
}

// FUNGSI: Reset voucher secara menyeluruh
function resetVoucher(showMessage = false) {
    // Reset state
    appliedVoucher = null;
    
    // Reset UI elements
    if (voucherSuccessDiv) voucherSuccessDiv.style.display = 'none';
    if (voucherCodeInput) {
        voucherCodeInput.value = '';
        voucherCodeInput.disabled = false;
    }
    if (applyVoucherBtn) applyVoucherBtn.disabled = false;
    
    // Reset radio button ke "Tidak"
    if (voucherNoRadio) voucherNoRadio.checked = true;
    
    // Hide input section
    if (voucherSection) voucherSection.style.display = 'none';
    
    // Recalculate
    calculateTotalAndAmounts();
    
    // Show message if needed
    if (showMessage) {
        Swal.fire({
            icon: 'info',
            title: 'Voucher Dihapus',
            text: 'Voucher diskon telah dihapus karena perubahan tipe pembayaran.',
            timer: 1500,
            showConfirmButton: false
        });
    }
}

// FUNGSI: Update voucher availability berdasarkan pay type
function updateVoucherAvailability() {
    const payType = document.querySelector('input[name="paytype"]:checked')?.value || 'dp';
    const voucherYesWrapper = voucherYesRadio ? voucherYesRadio.closest('.radio') : null;
    
    if (payType === 'dp') {
        // Disable voucher untuk DP
        if (voucherYesRadio) {
            voucherYesRadio.disabled = true;
        }
        
        if (voucherYesWrapper) {
            voucherYesWrapper.classList.add('disabled');
            voucherYesWrapper.style.opacity = '0.5';
            voucherYesWrapper.style.cursor = 'not-allowed';
        }
        
        // PENTING: Reset voucher jika sedang aktif atau form sedang terbuka
        if (appliedVoucher || (voucherSection && voucherSection.style.display === 'block')) {
            resetVoucher(appliedVoucher !== null); // Show message hanya jika ada voucher aktif
        }
    } else {
        // Enable voucher untuk Full Payment
        if (voucherYesRadio) {
            voucherYesRadio.disabled = false;
        }
        
        if (voucherYesWrapper) {
            voucherYesWrapper.classList.remove('disabled');
            voucherYesWrapper.style.opacity = '1';
            voucherYesWrapper.style.cursor = 'pointer';
        }
    }
}

function calculateTotalAndAmounts() {
    let totalAmount = 0;
    let totalDiskon = 0;
    let amounts = [];
    const payType = document.querySelector('input[name="paytype"]:checked')?.value || 'dp';

    reservasiCards.forEach(card => {
        const checkbox = card.querySelector('.reservasi-checkbox');
        if (checkbox && checkbox.checked) {
            const reservasiId = parseInt(checkbox.value);
            const remaining = parseFloat(card.dataset.remaining || 0);
            const totalPaid = parseFloat(card.dataset.paid || 0);
            const dpAmount = parseFloat(card.dataset.dp || 0);
            let amountToPay = 0;

            if (remaining > 0) {
                if (totalPaid > 0) {
                    // Pelunasan
                    amountToPay = remaining;
                } else if (payType === 'dp') { 
                    // DP - TIDAK ADA DISKON
                    amountToPay = Math.min(dpAmount, remaining);
                    amountToPay = Math.max(1000, amountToPay);
                } else if (payType === 'full') {
                    // Full Payment - BISA PAKAI DISKON
                    amountToPay = remaining;
                }
            }

            // Apply diskon HANYA jika payType === 'full'
            let diskonAmount = 0;
            if (payType === 'full') {
                diskonAmount = getDiskonForReservasi(reservasiId);
                // PENTING: Pastikan diskon tidak melebihi amount yang harus dibayar
                diskonAmount = Math.min(diskonAmount, amountToPay);
            }
            
            // Kurangi dengan diskon
            amountToPay = Math.max(0, amountToPay - diskonAmount);
            amountToPay = Math.round(amountToPay);

            if (amountToPay > 0 || diskonAmount > 0) {
                totalAmount += amountToPay;
                totalDiskon += diskonAmount;
                amounts.push({ 
                    reservasi_id: reservasiId, 
                    amount: amountToPay,
                    diskon: diskonAmount
                });
            }
        }
    });

    // Tampilkan total dengan detail diskon jika ada
    if (totalDiskon > 0) {
        displayAmountEl.innerHTML = `
            <div style="text-decoration: line-through; font-size: 14px; color: #999; font-weight: normal;">
                ${formatRupiah(totalAmount + totalDiskon)}
            </div>
            <div style="font-size: 20px; font-weight: 700; color: var(--primary-dark);">
                ${formatRupiah(totalAmount)}
            </div>
            <div style="font-size: 12px; color: #28a745; font-weight: 600; margin-top: 2px;">
                Hemat ${formatRupiah(totalDiskon)}
            </div>
        `;
    } else {
        displayAmountEl.textContent = formatRupiah(totalAmount);
    }

    payButton.disabled = totalAmount <= 0;
    payButton.textContent = payButton.disabled 
        ? TRANS.selectReservationBtn 
        : `${TRANS.continuePayment} ${formatRupiah(totalAmount)}`;

    return { totalAmount, amounts, totalDiskon };
}

function updatePaytypeAvailability() {
    let hasDownPayment = false;
    
    reservasiCards.forEach(card => {
        const checkbox = card.querySelector('.reservasi-checkbox');
        if (checkbox && checkbox.checked) {
            const totalPaid = parseFloat(card.dataset.paid || 0);
            const remaining = parseFloat(card.dataset.remaining || 0);
            if (totalPaid > 0 && remaining > 0) hasDownPayment = true;
        }
    });

    if (hasDownPayment) {
        dpRadio.disabled = true;
        radioDpWrapper.classList.add('disabled');
        dpDisabledNote.style.display = 'block';
        if (dpRadio.checked) fullRadio.checked = true;
    } else {
        dpRadio.disabled = false;
        radioDpWrapper.classList.remove('disabled');
        dpDisabledNote.style.display = 'none';
    }

    // Update voucher availability setelah paytype berubah
    updateVoucherAvailability();
    calculateTotalAndAmounts();
}

async function saveToDatabaseMulti(midtransResult, tokenData) {
    try {
        const response = await fetch(callbackUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                reservasi_ids: tokenData.reservasi_ids,
                order_id: tokenData.order_id,
                transaction_status: midtransResult.transaction_status || midtransResult.status_code, 
                payment_type: midtransResult.payment_type || 'unknown',
                metode_id: tokenData.metode_id,
                amounts: tokenData.amounts,
                pay_type_selected: tokenData.pay_type_selected,
                diskon_data: tokenData.diskon_data || null
            })
        });
        return await response.json();
    } catch (error) {
        console.error('Callback Save DB Error:', error);
        return { success: false, message: error.message || TRANS.saveFailedText };
    }
}

// EVENT: Toggle voucher input section
document.querySelectorAll('input[name="usevoucher"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const payType = document.querySelector('input[name="paytype"]:checked')?.value || 'dp';
        
        if (this.value === 'yes') {
            // Cek apakah pay type adalah full
            if (payType !== 'full') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Bisa Menggunakan Voucher',
                    text: 'Voucher hanya tersedia untuk Pembayaran Penuh (Full Payment).',
                    timer: 2000,
                    showConfirmButton: false
                });
                // Kembalikan ke "Tidak"
                voucherNoRadio.checked = true;
                return;
            }
            
            voucherSection.style.display = 'block';
        } else {
            voucherSection.style.display = 'none';
            
            // Reset voucher tanpa message jika user manual uncheck
            if (appliedVoucher) {
                appliedVoucher = null;
                voucherSuccessDiv.style.display = 'none';
                voucherCodeInput.value = '';
                voucherCodeInput.disabled = false;
                applyVoucherBtn.disabled = false;
                calculateTotalAndAmounts();
            }
        }
    });
});

// EVENT: Apply voucher button
document.getElementById('applyVoucherBtn').addEventListener('click', async function() {
    const voucherCode = voucherCodeInput.value.trim().toUpperCase();
    
    if (!voucherCode) {
        Swal.fire({
            icon: 'warning',
            title: 'Kode Voucher Kosong',
            text: 'Silakan masukkan kode voucher terlebih dahulu.'
        });
        return;
    }

    const payType = document.querySelector('input[name="paytype"]:checked')?.value || 'dp';
    
    // Validasi: voucher hanya untuk full payment
    if (payType !== 'full') {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Bisa Menggunakan Voucher',
            text: 'Voucher diskon hanya berlaku untuk Pembayaran Penuh (Full Payment), tidak berlaku untuk Down Payment (DP).'
        });
        return;
    }

    const selectedIds = [];
    reservasiCards.forEach(card => {
        const checkbox = card.querySelector('.reservasi-checkbox');
        if (checkbox && checkbox.checked) {
            selectedIds.push(parseInt(checkbox.value));
        }
    });

    if (selectedIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Ada Reservasi',
            text: 'Pilih minimal satu reservasi untuk menggunakan voucher.'
        });
        return;
    }

    loading.style.display = 'flex';
    loading.innerHTML = 'Memvalidasi voucher...';

    try {
        const response = await fetch(validateVoucherUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                kode_diskon: voucherCode,
                reservasi_ids: selectedIds,
                pay_type: payType
            })
        });

        const result = await response.json();
        loading.style.display = 'none';

        if (result.success) {
            appliedVoucher = result.data;
            
            let alertText = result.message;
            if (result.warning && result.data.detail.reservasi_without_diskon.length > 0) {
                // Tampilkan warning khusus
                Swal.fire({
                    icon: 'warning',
                    title: 'Voucher Berhasil (Sebagian)',
                    html: alertText,
                    timer: 3000,
                    showConfirmButton: true
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Voucher Berhasil!',
                    text: result.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            }

            const successText = document.getElementById('voucherSuccessText');
            const voucherType = result.data.is_global ? 'Diskon Global' : 'Diskon Layanan Tertentu';
            successText.textContent = `✓ ${result.data.nama_diskon} (${result.data.persentase}%) - ${voucherType} - Hemat ${formatRupiah(result.data.total_diskon)}`;
            voucherSuccessDiv.style.display = 'block';

            voucherCodeInput.disabled = true;
            applyVoucherBtn.disabled = true;

            const { totalAmount, totalDiskon } = calculateTotalAndAmounts();
            console.log('After Apply - Total:', totalAmount, 'Diskon:', totalDiskon);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Voucher Tidak Valid',
                text: result.message
            });
        }
    } catch (error) {
        loading.style.display = 'none';
        console.error('Voucher validation error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan',
            text: 'Terjadi kesalahan saat memvalidasi voucher.'
        });
    }
});

// EVENT: Remove voucher button
document.getElementById('removeVoucherBtn').addEventListener('click', function() {
    appliedVoucher = null;
    voucherSuccessDiv.style.display = 'none';
    voucherCodeInput.value = '';
    voucherCodeInput.disabled = false;
    applyVoucherBtn.disabled = false;
    
    calculateTotalAndAmounts();
    
    Swal.fire({
        icon: 'info',
        title: 'Voucher Dihapus',
        text: 'Voucher diskon telah dihapus dari transaksi.',
        timer: 1500,
        showConfirmButton: false
    });
});

// EVENT: Paytype change - AUTO RESET VOUCHER DAN HIDE FORM
document.querySelectorAll('input[name="paytype"]').forEach(r => r.addEventListener('change', function() {
    // PENTING: Update voucher availability dulu (ini akan auto-reset jika perlu)
    updateVoucherAvailability();
    
    // Lalu update paytype
    updatePaytypeAvailability();
}));

// EVENT: Checkbox change - AUTO RESET VOUCHER
document.querySelectorAll('.reservasi-checkbox').forEach(cb => cb.addEventListener('change', function() {
    // Reset voucher jika ada perubahan checkbox dan voucher sedang aktif
    if (appliedVoucher) {
        resetVoucher(true);
    }
    updatePaytypeAvailability();
}));

// EVENT: Pay button handler
document.getElementById('payButton').addEventListener('click', async function() {
    const payType = document.querySelector('input[name="paytype"]:checked')?.value || 'dp';
    const currentPayMethodId = document.querySelector('input[name="paymethod"]:checked')?.value;
    const { totalAmount, amounts: amountsPaid } = calculateTotalAndAmounts();
    let selectedIds = amountsPaid.map(item => item.reservasi_id.toString());

    if (selectedIds.length === 0 || totalAmount <= 0) {
        Swal.fire({ icon: 'warning', title: TRANS.noBillTitle, text: TRANS.noBillText });
        return;
    }

    if (!currentPayMethodId) {
        Swal.fire({ icon: 'warning', title: TRANS.selectMethodTitle, text: TRANS.selectMethodText });
        return;
    }

    loading.style.display = 'flex';
    loading.innerHTML = TRANS.processingReservations.replace(':count', selectedIds.length).replace(':amount', formatRupiah(totalAmount));

    try {
        const response = await fetch(processUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ 
                reservasi_ids: selectedIds, 
                pay_type: payType, 
                metode_id: currentPayMethodId,
                diskon_data: appliedVoucher
            })
        });

        const data = await response.json();

        if (!data.success || !data.token || !data.token.snap_token) {
            Swal.fire({ icon: 'error', title: TRANS.failed, text: data.message || TRANS.failedToken });
            loading.style.display = 'none';
            return;
        }

        const tokenData = data.token;
        tokenData.amounts = amountsPaid; 
        tokenData.pay_type_selected = payType;
        tokenData.metode_id = currentPayMethodId;
        tokenData.diskon_data = appliedVoucher;

        loading.style.display = 'none';

        window.snap.pay(tokenData.snap_token, {
            onSuccess: async function(result) {
                loading.style.display = 'flex';
                loading.innerHTML = TRANS.paymentSuccessSaving.replace(':count', tokenData.reservasi_ids.length);

                const dbResult = await saveToDatabaseMulti(result, tokenData);
                loading.style.display = 'none';

                if (dbResult.success) {
                    const idsParam = dbResult.reservasi_ids.join(',');
                    window.location.href = successUrlMulti.replace(':ids', idsParam);
                } else {
                    Swal.fire({ icon: 'error', title: TRANS.saveFailedTitle, text: dbResult.message || TRANS.saveFailedText });
                }
            },
            onPending: async function(result) {
                loading.style.display = 'flex';
                loading.innerHTML = TRANS.paymentPendingSaving.replace(':count', tokenData.reservasi_ids.length);

                const dbResult = await saveToDatabaseMulti(result, tokenData);
                loading.style.display = 'none';

                if (dbResult.success) {
                    const idsParam = dbResult.reservasi_ids.join(',');
                    Swal.fire({ icon: 'info', title: TRANS.pendingTitle, text: TRANS.pendingText.replace(':status', result.transaction_status) })
                        .then(() => { window.location.href = successUrlMulti.replace(':ids', idsParam); });
                } else {
                    Swal.fire({ icon: 'error', title: TRANS.saveFailedTitle, text: dbResult.message || TRANS.savePendingFailed });
                }
            },
            onError: function(result) {
                loading.style.display = 'none';
                Swal.fire({ icon: 'error', title: TRANS.errorTitle, text: result.status_message || TRANS.errorText });
            },
            onClose: function() {
                loading.style.display = 'none';
                Swal.fire({ icon: 'warning', title: TRANS.cancelledTitle, text: TRANS.cancelledText });
            }
        });

    } catch (error) {
        console.error('AJAX Error:', error);
        Swal.fire({ icon: 'error', title: TRANS.serverErrorTitle, text: TRANS.serverErrorText });
        loading.style.display = 'none';
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => { 
    updatePaytypeAvailability();
    updateVoucherAvailability(); // Init voucher state saat load
});
</script>
@endsection