@extends('layouts.pelanggan')

@section('content')
    <style>
        :root{
            --pink:#ff6b9e;
            --pink-200:#ffe3ec;
            --ink:#232222;
            --muted:#6f6c6c;
            --bg:#fff7fa;
            --ring:rgba(255,107,158,.35);
            --card:#ffffff;

            --shadow-sm:0 2px 8px rgba(45,42,42,.06);
            --shadow-md:0 6px 18px rgba(45,42,42,.10);
        }

        /* Base */
        *,*::before,*::after{box-sizing:border-box}
        html{-webkit-text-size-adjust:100%}
        body{background:var(--bg);color:var(--ink);margin:0}
        img{max-width:100%;height:auto;display:block}

        /* Container & Card */
        .success-container{max-width:min(960px,92vw);margin:clamp(16px,3vw,40px) auto;padding:0 clamp(12px,2vw,24px)}
        .success-card{
            background:var(--card);border-radius:20px;padding:clamp(20px,4vw,40px);
            box-shadow:0 6px 28px rgba(255,107,158,.14);outline:1px solid rgba(0,0,0,.04)
        }

        /* Header */
        .success-head{text-align:center}
        .success-icon{
            inline-size:84px;block-size:84px;background:var(--pink-200);border-radius:999px;display:grid;place-items:center;
            margin:0 auto clamp(12px,2.2vw,24px);font-size:40px;font-weight:700;color:var(--pink);
            box-shadow:inset 0 0 0 6px #fff, 0 8px 20px var(--ring)
        }
        .success-title{font-size:clamp(22px,3.2vw,30px);font-weight:800;margin:0 0 6px;letter-spacing:-.015em}
        .success-subtitle{font-size:clamp(14px,1.8vw,16px);color:var(--muted);margin:0}

        /* Section (Booking summary) */
        .booking-summary{
            background:var(--bg);border-radius:16px;padding:clamp(14px,2.4vw,24px);
            margin-top:clamp(16px,2.4vw,24px);border:1px solid #f3dbe3
        }
        .summary-header{
            font-size:clamp(16px,2vw,18px);font-weight:800;color:var(--ink);
            margin:0 0 14px;padding-bottom:10px;border-bottom:2px solid var(--pink-200)
        }

        /* Key/Value Grid */
        .summary-rows{display:grid;gap:10px}
        .summary-row{
            display:grid;grid-template-columns:1fr auto;gap:10px;align-items:center;
            padding:10px 0;border-bottom:1px dashed #f0d5de
        }
        .summary-row:last-child{border-bottom:0}
        .summary-label{color:var(--muted);font-size:14px}
        .summary-value{font-weight:600;color:var(--ink)}

        /* Status badge (seragam) */
        .status-badge{
            display:inline-block;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:700;
            letter-spacing:.02em;border:1px solid transparent
        }
        .status-pending{background:#fff3cd;color:#856404;border-color:#ffe69c}
        .status-dikonfirmasi{background:#d1ecf1;color:#0c5460;border-color:#a5d8de}
        .status-selesai{background:#d4edda;color:#155724;border-color:#a3d9b1}
        .status-dibatalkan{background:#f8d7da;color:#721c24;border-color:#f1b0b7}

        /* Services (grid jadwal per layanan) */
        .services-grid{display:grid;gap:12px;grid-template-columns:1fr}
        @media (min-width:560px){ .services-grid{grid-template-columns:repeat(2,minmax(0,1fr))} }
        @media (min-width:920px){ .services-grid{grid-template-columns:repeat(3,minmax(0,1fr))} }

        .service-item{
            background:#fff;border-radius:14px;padding:14px 16px;border:1px solid #f0d5de;display:grid;gap:8px;
            transition:transform .12s ease, box-shadow .12s ease;box-shadow:var(--shadow-sm);overflow:hidden
        }
        .service-item:hover{transform:translateY(-1px);box-shadow:var(--shadow-md)}
        .service-name{font-weight:800;margin:0;font-size:15px;overflow-wrap:anywhere}

        .service-details{
            display:flex;flex-wrap:wrap;gap:8px;color:var(--muted);font-size:13px
        }
        .kv{display:inline-flex;gap:6px;align-items:center}
        .k{color:var(--muted)}
        .v{color:var(--ink);font-weight:600}

        /* Services list (detail layanan yang dipesan) */
        .services-list{list-style:none;margin:0;padding:0;display:grid;gap:10px}
        .service-row{
            display:grid;gap:6px;padding:12px 14px;border:1px solid #f0d5de;border-radius:12px;background:#fff;
            transition:transform .12s ease, box-shadow .12s ease;box-shadow:var(--shadow-sm);overflow:hidden
        }
        .service-row:hover{transform:translateY(-1px);box-shadow:var(--shadow-md)}
        .service-row .service-name{font-size:15px}
        .service-row .service-details{display:flex;flex-wrap:wrap;gap:10px;color:var(--muted);font-size:13px}

        /* Total */
        .total-row{
            display:grid;grid-template-columns:1fr auto;align-items:center;gap:12px;padding-top:16px;margin-top:8px;
            border-top:2px solid var(--pink);font-size:clamp(18px,2.4vw,20px);font-weight:800
        }
        .total-price{color:var(--pink)}

        /* Actions (seragam tombol) */
        .action-buttons{display:flex;flex-wrap:wrap;gap:12px;margin-top:clamp(14px,2.2vw,24px)}
        .btn{
            --_pad-x:clamp(16px,3vw,28px);--_pad-y:12px;border-radius:12px;font-weight:700;text-decoration:none;
            display:inline-flex;align-items:center;justify-content:center;gap:8px;border:2px solid transparent;
            padding:var(--_pad-y) var(--_pad-x);flex:1 1 240px;transition:filter .2s ease, transform .04s ease, border-color .2s ease
        }
        .btn:active{transform:translateY(1px)}
        .btn-primary{background:var(--pink);color:#fff}
        .btn-primary:hover{filter:brightness(.96);color:#fff}
        .btn-secondary{background:#fff;color:var(--pink);border-color:var(--pink)}
        .btn-secondary:hover{background:var(--pink-200)}

        /* Info (tanpa emoji) */
        .alert-info{
            background:#e8f6f8;border:1px solid #cceef4;color:#0c5460;padding:clamp(12px,2vw,16px);
            border-radius:12px;margin-top:clamp(12px,2vw,24px);font-size:14px
        }

        /* Mobile: stack labels above values */
        @media (max-width:520px){
            .summary-row{grid-template-columns:1fr;align-items:start}
            .summary-value{justify-self:start}
        }
    </style>

<div class="success-container">
    <div class="success-card">
        <header class="success-head" aria-labelledby="success-title">
            <div class="success-icon" aria-hidden="true">✓</div>
            <h1 id="success-title" class="success-title">{{ __('booking_success.reservation_success') }}</h1>
            <p class="success-subtitle">
                {{ __('booking_success.reservation_number') }}: <strong>#{{ str_pad($reservasi->id_reservasi, 6, '0', STR_PAD_LEFT) }}</strong>
            </p>
        </header>

        <section class="booking-summary" aria-labelledby="detail-reservasi">
            <h2 id="detail-reservasi" class="summary-header">{{ __('booking_success.reservation_details') }}</h2>

            <div class="summary-rows" role="list">
                <div class="summary-row" role="listitem">
                    <span class="summary-label">{{ __('booking_success.date') }}</span>
                    <span class="summary-value">{{ $reservasi->tanggal_format }}</span>
                </div>
                <div class="summary-row" role="listitem">
                    <span class="summary-label">{{ __('booking_success.time') }}</span>
                    <span class="summary-value">{{ $reservasi->waktu_reservasi }}</span>
                </div>
                <div class="summary-row" role="listitem">
                    <span class="summary-label">{{ __('booking_success.status') }}</span>
                    <span class="summary-value">
                        <span class="status-badge status-{{ $reservasi->status_reservasi }}">
                            {{ ucfirst($reservasi->status_reservasi) }}
                        </span>
                    </span>
                </div>
            </div>

            @if (data_get($reservasi, 'catatan.schedules'))
                <div style="margin-top:clamp(12px,2vw,24px);">
                    <div class="summary-header" style="font-size:clamp(15px,1.8vw,16px);margin-bottom:12px;">
                        {{ __('booking_success.schedule_per_service') }}
                    </div>
                    @php
                        $namaById = $reservasi->reservasiLayanan->pluck('layanan.nama_layanan', 'id_layanan');
                    @endphp
                    <div class="services-grid">
                        @foreach ($reservasi->catatan['schedules'] as $idLayanan => $jadwal)
                            <article class="service-item" aria-label="{{ __('booking_success.schedule_per_service') }}">
                                <h3 class="service-name">{{ $namaById[$idLayanan] ?? 'Layanan #' . $idLayanan }}</h3>
                                <div class="service-details">
                                    <span class="kv"><span class="k">{{ __('booking_success.date') }}:</span><span class="v">{{ $jadwal['date'] ?? '-' }}</span></span>
                                    <span class="kv"><span class="k">{{ __('booking_success.time') }}:</span><span class="v">{{ $jadwal['time'] ?? '-' }}</span></span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

            <div style="margin-top:clamp(12px,2vw,24px);">
                <ul class="services-list">
                    @foreach ($reservasi->reservasiLayanan as $detail)
                        <li class="service-row">
                            <div class="service-name">{{ $detail->layanan->nama_layanan }}</div>
                            <div class="service-details">
                                <span>{{ __('booking_success.duration') }}: <strong>{{ $detail->layanan->durasi }} {{ __('booking_success.minutes') }}</strong></span>
                                <span>{{ __('booking_success.price') }}: <strong>{{ $detail->layanan->harga_format }}</strong></span>
                                <span>{{ __('booking_success.category') }}: <strong>{{ $detail->layanan->kategoriLayanan->nama ?? '-' }}</strong></span>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="total-row" aria-live="polite">
                    <span>{{ __('booking_success.total_payment') }}</span>
                    <span class="total-price">{{ $reservasi->harga_format }}</span>
                </div>
            </div>
        </section>

        <nav class="action-buttons" aria-label="Action buttons">
            <a href="{{ route('booking.history') }}" class="btn btn-primary">{{ __('booking_success.view_booking_history') }}</a>
            <a href="{{ route('daftar-layanan.index') }}" class="btn btn-secondary">{{ __('booking_success.book_again') }}</a>
        </nav>

        <aside class="alert-info" role="note">
            <strong>{{ __('booking_success.important_info') }}</strong><br>
            • {{ __('booking_success.info_pending') }}<br>
            • {{ __('booking_success.info_notification') }}<br>
            • {{ __('booking_success.info_arrival') }}
        </aside>
    </div>
</div>
@endsection