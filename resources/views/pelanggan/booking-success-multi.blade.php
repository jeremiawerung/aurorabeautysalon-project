@extends('layouts.pelanggan')

@section('title', 'Pembayaran Berhasil')

@section('content')
<style>
/* ... (Gunakan CSS yang sudah ada di step3.blade.php untuk konsistensi) ... */
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
    --success: #4CAF50;
    --success-light: #e8f5e9;
}
html, body { background: var(--bg); color: var(--text); }
.container-success {
    max-width: 800px;
    margin: 40px auto;
    padding: 24px;
    text-align: center;
}
.card-success {
    background: #fff;
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: 30px;
    box-shadow: var(--shadow-md);
    text-align: left;
}
.icon-success {
    color: var(--success);
    font-size: 60px;
    margin-bottom: 15px;
}
.header-success {
    font-size: 28px;
    font-weight: 700;
    color: var(--success);
    margin-bottom: 8px;
}
.subtitle-success {
    font-size: 16px;
    color: var(--muted);
    margin-bottom: 30px;
}
.btn-primary {
    display: inline-block;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    padding: 12px 25px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s;
    font-size: 15px;
    margin-top: 20px;
    text-decoration: none;
}
.btn-primary:hover {
    background: var(--primary-dark);
}
.rincian-card {
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    padding: 15px;
    margin-bottom: 15px;
    background: var(--success-light);
}
.rincian-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px dashed var(--success);
    padding-bottom: 8px;
    margin-bottom: 10px;
}
.rincian-title {
    font-weight: 700;
    font-size: 16px;
    color: var(--primary-dark);
}
.rincian-type {
    font-size: 12px;
    font-weight: 600;
    color: var(--success);
    background: #c8e6c9;
    padding: 3px 8px;
    border-radius: 6px;
}
.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    font-size: 14px;
}
.detail-row .label {
    color: var(--muted);
}
.detail-row .value {
    font-weight: 600;
    color: var(--text);
}
.total-bayar {
    font-size: 18px;
    font-weight: 700;
    color: var(--success);
    padding-top: 10px;
    border-top: 1px solid var(--border-light);
    margin-top: 10px;
}
</style>
<div class="container-success">
    <div class="card-success">
        <div style="text-align: center;">
            <i class="icon-success fa fa-check-circle"></i> 
            <h1 class="header-success">{{ __('booking_success.payment_success') }}</h1>
            <p class="subtitle-success">
                {{ __('booking_success.payment_thank_you', ['count' => count($cards)]) }}
            </p>
        </div>

        {{-- Loop Rincian Pembayaran --}}
        @foreach($cards as $card)
            @php
                $reservasi = $card['reservasi'];
                $sDate = \Carbon\Carbon::parse($reservasi->tanggal_reservasi ?? now())->format('d M Y');
                $sTime = substr($reservasi->waktu_reservasi ?? '00:00:00', 0, 5);
            @endphp
            <div class="rincian-card">
                <div class="rincian-header">
                    <div class="rincian-title">{{ __('booking_success.reservation') }} #{{ $reservasi->id_reservasi }}</div>
                    <span class="rincian-type">{{ $card['tipe_pembayaran'] }}</span>
                </div>

                {{-- Detail Transaksi --}}
                <div class="detail-row">
                    <span class="label">{{ __('booking_success.date_time') }}:</span>
                    <span class="value">{{ $sDate }} • {{ $sTime }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">{{ __('booking_success.total_bill') }}:</span>
                    <span class="value">Rp {{ number_format($card['total_tagihan'], 0, ',', '.') }}</span>
                </div>
                
                @if(($card['biaya_tambahan'] ?? 0) > 0)
                <div class="detail-row">
                    <span class="label">Biaya Tambahan:</span>
                    <span class="value text-danger">+ Rp {{ number_format($card['biaya_tambahan'], 0, ',', '.') }}</span>
                </div>
                <div class="detail-row">
                    <span class="label" style="font-size:12px; font-style:italic;">(Termasuk dalam Total Tagihan)</span>
                </div>
                @endif
                
                @if($card['sudah_dibayar'] > 0)
                <div class="detail-row">
                    <span class="label">{{ __('booking_success.previously_paid') }}:</span>
                    <span class="value">Rp {{ number_format($card['sudah_dibayar'], 0, ',', '.') }}</span>
                </div>
                @endif
                
                <div class="detail-row">
                    <span class="label">{{ __('booking_success.paid_now') }}:</span>
                    <span class="value total-bayar" style="color:var(--primary-dark);">Rp {{ number_format($card['real_paid'] ?? $card['transaksi_ini'], 0, ',', '.') }}</span>
                </div>
                
                @if(isset($card['diskon_ini']) && $card['diskon_ini'] > 0)
                <div class="detail-row">
                    <span class="label">Potongan / Diskon:</span>
                    <span class="value" style="color:var(--success); font-weight:bold;">- Rp {{ number_format($card['diskon_ini'], 0, ',', '.') }}</span>
                </div>
                @endif
                
                @php
                    $totalSudahBayar = $card['sudah_dibayar'] + $card['transaksi_ini'];
                    $sisaTagihan = max($card['total_tagihan'] - $totalSudahBayar, 0);
                    $isLunas = $sisaTagihan <= 100; // Toleransi pembulatan
                @endphp

                <div class="detail-row" style="margin-top:10px;">
                    <span class="label">{{ __('booking_success.final_payment_status') }}:</span>
                    <span class="value">
                        @if($isLunas)
                            <span class="text-success fw-bold">LUNAS</span>
                        @else
                            <span class="text-warning fw-bold">BELUM LUNAS</span>
                            <br>
                            <small class="text-muted">(Sisa: Rp {{ number_format($sisaTagihan, 0, ',', '.') }})</small>
                        @endif
                    </span>
                </div>
                <div class="detail-row">
                    <span class="label">{{ __('booking_success.payment_method') }}:</span>
                    <span class="value">{{ $card['metode_midtrans'] }}</span>
                </div>

                <p class="help" style="margin-top:10px; font-size:12px; color:var(--text);">
                    {{ __('booking_success.description') }}: {{ $card['keterangan'] }}
                </p>
            </div>
        @endforeach

        <div style="text-align: center;">
            <a href="{{ route('booking.history') }}" class="btn-primary">
                {{ __('booking_success.view_all_reservations') }}
            </a>
            <a href="{{ route('daftar-layanan.index') }}" class="btn-primary">
                {{ __('booking_success.create_new_reservation') }}
            </a>
        </div>
    </div>
</div>
@endsection