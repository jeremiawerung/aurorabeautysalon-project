{{-- resources/views/pelanggan/partials/reservation-cards.blade.php --}}

{{-- $reservations, $active, $currentSearch harus dilewatkan dari Controller --}}
@php
    $active = $active ?? request('status', 'semua');
    $currentSearch = $currentSearch ?? request('search', '');
@endphp

@if ($reservations->count() === 0)
    <div class="empty">
        <div class="empty-message">
            <span class="far fa-frown" style="font-size: 24px; display: block; margin-bottom: 10px;"></span>
            Belum ada reservasi pada status atau kriteria pencarian ini.
        </div>
    </div>
@else
    @foreach ($reservations as $reservasi)
        @php
            // --- Logika Penetapan Data Reservasi ---
            $first = $reservasi->reservasiLayanan->first();
            $nama = 'Layanan Tidak Dikenal';
            $kategori = 'Kategori Layanan';
            $thumb = asset('img/favicon.svg'); // Ganti dengan placeholder gambar default

            if ($first) {
                $nama = $first->layanan?->nama_layanan ?? $nama;
                $kategori = $first->layanan?->kategoriLayanan?->nama ?? $kategori;
                if ($first->layanan?->gambar) {
                    $thumb = asset('storage/' . $first->layanan->gambar);
                }
            }

            $paymentRecord = $reservasi->pembayaran->first();
            $paymentStatus = $paymentRecord->status_pembayaran ?? 'belum_bayar';

            // --- Logika Penetapan Status & Class ---
            $statusDisplay = '';
            $statusClass = '';
            $isClickable = false;
            $clickHint = '';

            if ($reservasi->status_reservasi === 'dibatalkan') {
                $statusDisplay = 'Dibatalkan';
                $statusClass = 'status-batal';
            } elseif ($reservasi->status_reservasi === 'selesai') {
                $statusDisplay = 'Selesai';
                $statusClass = 'status-lunas';
            } elseif ($paymentStatus === 'bayar_lunas') {
                $statusDisplay = 'Sudah Lunas';
                $statusClass = 'status-lunas';
            } elseif ($paymentStatus === 'bayar_dp') {
                $statusDisplay = 'DP Masuk';
                $statusClass = 'status-dp';
            } elseif ($paymentStatus === 'menunggu_verifikasi') {
                $statusDisplay = 'Menunggu Verifikasi';
                $statusClass = 'status-dp';
            } elseif ($paymentStatus === 'belum_bayar' && in_array($reservasi->status_reservasi, ['pending', 'proses'])) {
                $statusDisplay = 'Menunggu Pembayaran';
                $statusClass = '';
            } else {
                $statusDisplay = 'Diproses';
                $statusClass = '';
            }

            // --- Logika Link & Aksi Cepat ---
            $detailLink = '#'; // Asumsi ada route detail
            $cardLink = $detailLink;

            if ($paymentStatus === 'belum_bayar' && in_array($reservasi->status_reservasi, ['pending', 'proses'])) {
                $cardLink = route('booking.step2', ['reservasi_id' => $reservasi->id_reservasi]);
                $isClickable = true;
                $clickHint = 'Klik untuk melanjutkan & bayar DP/Lunas';
            } elseif ($paymentStatus === 'bayar_dp' && $reservasi->status_reservasi === 'proses') {
                $cardLink = route('booking.step3', ['reservasi_id' => $reservasi->id_reservasi]);
                $isClickable = true;
                $clickHint = 'Klik untuk melunasi pembayaran';
            }
        @endphp

        <article class="card {{ $isClickable ? 'clickable' : '' }}"
            @if($isClickable)
                onclick="window.location.href='{{ $cardLink }}'"
                style="cursor: pointer;"
            @endif>

            <img class="thumb" src="{{ $thumb }}" alt="Thumbnail">

            <div class="card-content">
                <h3 class="title">
                    {{ $nama }}
                    @if(($reservasi->reservasiLayanan->count() - 1) > 0)
                        <span class="count">(+{{ $reservasi->reservasiLayanan->count() - 1 }} Layanan)</span>
                    @endif
                </h3>

                <div class="meta">
                    <span><span class="fas fa-tag"></span> Kategori: <strong>{{ $kategori }}</strong></span>
                    <span><span class="far fa-clock"></span> Jam: <strong>{{ $reservasi->waktu_reservasi ?? '-' }}</strong></span>
                    <span>
                        <span class="far fa-calendar-alt"></span> Tanggal:
                        <strong>
                            {{ $reservasi->tanggal_reservasi
                                ? \Carbon\Carbon::parse($reservasi->tanggal_reservasi)->format('d-m-Y')
                                : '-' }}
                        </strong>
                    </span>
                </div>

                @if (($paymentStatus === 'bayar_dp' || $paymentStatus === 'menunggu_verifikasi') && $reservasi->status_reservasi !== 'selesai' && $reservasi->status_reservasi !== 'dibatalkan')
                    @php
                        $totalReservasi = (float)($reservasi->total_harga ?? 0);
                        $totalPaid = (float) $reservasi->pembayaran
                            ->whereIn('status_pembayaran', ['bayar_dp', 'bayar_lunas', 'menunggu_verifikasi'])
                            ->sum('jumlah');
                        $sisa = max($totalReservasi - $totalPaid, 0);
                    @endphp
                    @if ($sisa > 0)
                        <div class="meta" style="margin-top: 8px; border-top: 1px dotted var(--border); padding-top: 8px;">
                            <span><span class="fas fa-wallet" style="color:var(--danger);"></span> Sisa Bayar: <strong style="color:var(--danger);">Rp {{ number_format($sisa, 0, ',', '.') }}</strong></span>
                        </div>
                    @endif
                @endif

                @if ($isClickable && $clickHint)
                    <small class="card-clickable-hint">
                        <span class="fas fa-hand-pointer"></span> {{ $clickHint }}
                    </small>
                @endif
            </div>

            <div class="status-area">
                <div class="status {{ $statusClass }}">{{ $statusDisplay }}</div>

                <div class="card-actions">
                    <a href="{{ $detailLink }}" class="btn-detail">Detail</a>

                    {{-- Tombol Batalkan / Hapus --}}
                    @if($reservasi->status_reservasi === 'pending' || ($paymentStatus === 'belum_bayar' && $reservasi->status_reservasi === 'proses'))
                        {{-- Batalkan/Hapus reservasi yang belum ada pembayaran --}}
                        <form method="POST" action="{{ route('booking.cancel', $reservasi->id_reservasi) }}"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus reservasi yang belum dibayar ini?');">
                            @csrf
                            <button type="submit" class="btn-cancel">Batalkan</button>
                        </form>
                    @elseif(in_array($paymentStatus, ['bayar_dp', 'bayar_lunas', 'menunggu_verifikasi']) && $reservasi->status_reservasi === 'proses')
                        {{-- Batalkan reservasi yang sudah ada pembayaran (memicu refund) --}}
                        <form method="POST" action="{{ route('booking.cancel', $reservasi->id_reservasi) }}"
                                onsubmit="return confirm('Anda telah melakukan pembayaran. Pembatalan akan memicu proses refund (perlu diverifikasi). Lanjutkan?');">
                            @csrf
                            <button type="submit" class="btn-cancel">Batalkan</button>
                        </form>
                    @endif
                </div>
            </div>
        </article>
    @endforeach

    {{-- PAGINATION LINKS --}}
    <div class="pagination-wrap-history">
        {{ $reservations->appends(['status' => $active, 'search' => $currentSearch])->links() }}
    </div>
@endif
