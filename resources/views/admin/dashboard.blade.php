@extends('layouts.app')

@section('title', 'Dashboard - Aurora')

@section('content')
<style>
    .stat-card {
        border: 1px solid #f1b8d6;
        border-radius: 10px;
        text-align: center;
        padding: 20px 10px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .stat-card .value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
    }
    .stat-card .label {
        font-size: 0.9rem;
        color: #777;
        margin-top: 5px;
    }
    .stat-card .value-small {
        font-size: 1.3rem;
    }

    .card {
        border: 1px solid #f1b8d6 !important;
        border-radius: 10px;
        height: 100%;
        overflow: hidden;
    }
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f1b8d6 !important;
        font-weight: 600;
    }

    table th,
    table td {
        font-size: 0.8rem;
        vertical-align: middle;
        white-space: nowrap;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    .content-wrapper {
        padding: 20px;
    }

    @media (max-width: 992px) {
        .stat-card { margin-bottom: 10px; }
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">

        {{-- Baris untuk Statistik dan List Jadwal --}}
        <div class="row g-4 mb-4">

            {{-- Kolom Statistik --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">Dasbor</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4 col-6">
                                <div class="stat-card">
                                    <div class="value">{{ $stats['total_pelanggan'] }}</div>
                                    <div class="label">Pelanggan</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="stat-card">
                                    <div class="value">{{ $stats['total_transaksi'] }}</div>
                                    <div class="label">Transaksi (Paid)</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="stat-card">
                                    <div class="value">{{ $stats['pemasukan_hari_ini'] }}</div>
                                    <div class="label">Pemasukan Hari Ini</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="stat-card">
                                    <div class="value">{{ $stats['layanan_aktif'] }}</div>
                                    <div class="label">Layanan Aktif</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="stat-card">
                                    <div class="value">{{ $stats['rerata_transaksi'] }}</div>
                                    <div class="label">Rerata Transaksi</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="stat-card">
                                    <div class="value value-small">{{ $stats['pemasukan_bulan_ini'] }}</div>
                                    <div class="label">Pemasukan Bulan Ini</div>
                                </div>
                            </div>
                        </div>
                        {{-- Baris Stat Tambahan --}}
                        <div class="row g-3 mt-1">
                            <div class="col-md-4 col-6">
                                <div class="stat-card">
                                    <div class="value">{{ $stats['layanan_nonaktif'] }}</div>
                                    <div class="label">Layanan Non-aktif</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="stat-card">
                                    <div class="value">{{ $stats['menunggu_pembayaran'] }}</div>
                                    <div class="label">Menunggu Pembayaran</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="stat-card">
                                    <div class="value">{{ $stats['pelanggan_baru_bulan_ini'] }}</div>
                                    <div class="label">Pelanggan Baru (Bulan Ini)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom List Jadwal Pelanggan --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">List Jadwal Pelanggan</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Pelanggan</th>
                                        <th>Layanan</th>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Slot Jadwal</th>
                                        <th>Status Pembayaran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jadwal as $j)
                                        <tr>
                                            <td>{{ $j['nama'] }}</td>
                                            <td>{{ $j['layanan'] }}</td>
                                            <td>{{ $j['tanggal'] }}</td>
                                            <td>{{ $j['waktu'] }}</td>
                                            <td>{{ $j['waktu'] }}</td>
                                            <td>
                                                <span class="badge {{ $j['status_cls'] }}">
                                                    {{ $j['status_txt'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada jadwal.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Baris untuk Grafik --}}
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">Jumlah Transaksi per Hari (7 hari)</div>
                    <div class="card-body">
                        <canvas id="chartHarian" height="150"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">Rerata Transaksi per Jam (30 hari)</div>
                    <div class="card-body">
                        <canvas id="chartJam" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // === Grafik Transaksi per Hari (7 hari) ===
    const harianLabels = @json($chartHarianLabels);
    const harianData   = @json($chartHarianData);

    const ctxHarian = document.getElementById('chartHarian').getContext('2d');
    new Chart(ctxHarian, {
        type: 'bar',
        data: {
            labels: harianLabels,
            datasets: [{
                label: 'Transaksi',
                data: harianData,
                backgroundColor: '#f8d4e9',
                borderColor: '#d13a8a',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    // === Grafik Rerata Transaksi per Jam (30 hari) ===
    const jamLabels = @json($chartJamLabels);
    const jamData   = @json($chartJamData);

    const ctxJam = document.getElementById('chartJam').getContext('2d');
    new Chart(ctxJam, {
        type: 'line',
        data: {
            labels: jamLabels,
            datasets: [{
                label: 'Rerata / Jam',
                data: jamData,
                fill: true,
                backgroundColor: 'rgba(248, 212, 233, 0.4)',
                borderColor: '#d13a8a',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
@endsection
