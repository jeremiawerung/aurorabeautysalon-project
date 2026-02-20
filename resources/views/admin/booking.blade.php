@extends('layouts.app')

@section('title', 'Pengaturan Booking - Aurora')

@push('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    {{-- CSS Khusus Halaman Booking --}}
    <style>
        .content-wrapper { padding: 20px; }

        .card { border: 1px solid #f1b8d6 !important; border-radius: 10px; height: 100%; overflow: hidden; }
        .card-header { background-color: #fff; border-bottom: 1px solid #f1b8d6 !important; font-weight: 600; }
        .card-footer { background-color: #fff; border-top: 1px solid #f1b8d6 !important; padding: 1rem 1.5rem; }

        .table-responsive table th, .table-responsive table td {
            font-size: 0.8rem; vertical-align: middle; white-space: nowrap; padding: 0.4rem;
        }

        .calendar-wrapper { padding: 20px; }
        .nav-tabs .nav-link { color: #777; border: none; border-bottom: 3px solid transparent; padding-top: 0.75rem; padding-bottom: 0.75rem; }
        .nav-tabs .nav-link.active { color: #d13a8a; font-weight: 600; border-bottom-color: #d13a8a; background-color: transparent; }

        .form-label { font-size: 0.9rem; font-weight: 600; color: #555; }
        .form-text { font-size: 0.8rem; }
        .btn-pink { background-color: #fff; border: 1px solid #f1b8d6; color: #d13a8a; font-weight: 600; }
        .btn-pink:hover { background-color: #f1b8d6; border-color: #f1b8d6; color: #d13a8a; }

        .calendar-header { display: flex; justify-content: space-between; align-items: center; padding: 15px 0px; border-bottom: 1px solid #eee; }
        .calendar-header h2 { font-size: 1.25rem; font-weight: 500; color: #333; margin: 0; text-transform: uppercase; }
        .calendar-header .nav-btn { background: none; border: 1px solid #ccc; border-radius: 5px; cursor: pointer; font-size: 1.2rem; width: 30px; height: 30px; color: #555; transition: background-color 0.2s; }
        .calendar-header .nav-btn:hover { background-color: #e9ecef; }

        .calendar-body { padding: 10px 0px; }
        .calendar-weekdays, .calendar-days { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; }
        .calendar-weekdays div { padding: 10px 0; font-weight: 700; font-size: 0.75rem; color: #333; border-bottom: 2px solid #cde6ff; }
        .calendar-weekdays div:first-child { color: #e74c3c; }
        .calendar-days { gap: 2px; }
        .calendar-days div { display: flex; justify-content: center; align-items: center; height: 40px; font-size: 0.9rem; color: #444; cursor: pointer; border-radius: 5px; transition: background-color 0.2s, color 0.2s; position: relative; }
        .calendar-days .empty-cell { background-color: #f8f9fa; opacity: 0.5; cursor: not-allowed; pointer-events: none; }
        .calendar-days .selected { background-color: #007bff; color: #ffffff; font-weight: 700; }
        .calendar-days .selected::after { content: ''; position: absolute; bottom: 10px; left: 30%; right: 30%; height: 2px; background-color: #ffffff; }
        .calendar-days div:not(.empty-cell):not(.disabled):hover { background-color: #f1f1f1; }
        .calendar-days .disabled { color: #aaa; cursor: not-allowed; pointer-events: none; position: relative; }
        .calendar-days .disabled::after { content: ''; position: absolute; bottom: 10px; left: 30%; right: 30%; height: 1px; background-color: #ccc; }

        .jam-operasional-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .jam-operasional-row .form-check { min-width: 100px; padding-top: 5px; }

        #map-lokasi { height: 400px; border-radius: 5px; border: 1px solid #ddd; z-index: 1; }

        /* Galeri Arsip */
        .gallery-image-wrapper { position: relative; width: 120px; height: 120px; border-radius: 5px; overflow: hidden; border: 1px solid #ddd; }
        .gallery-image-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .gallery-delete-btn { position: absolute; top: 5px; right: 5px; width: 24px; height: 24px; background-color: rgba(0, 0, 0, 0.6); color: white; border: none; border-radius: 50%; font-weight: bold; font-size: 14px; line-height: 22px; text-align: center; cursor: pointer; padding: 0; }
        .gallery-delete-btn:hover { background-color: rgba(255, 0, 0, 0.8); }

        .new-caption-item { border: 1px solid #eee; border-radius: 5px; padding: 10px; background-color: #fdfdfd; }
        .new-caption-item label { font-size: 0.8rem; font-weight: 500; color: #666; word-break: break-all; }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">

        {{-- Card untuk Point of Sale - Tambahkan di halaman booking admin --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card" style="border: 2px solid #d13a8a; border-radius: 12px; overflow: hidden;">
                    <div class="card-body" style="padding: 1.5rem;">
                        <div class="row align-items-center">
                            {{-- Icon & Judul --}}
                            <div class="col-lg-8 col-md-7 mb-3 mb-md-0">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h5 style="margin: 0; font-weight: 700; color: #333;">Jadwalkan Pelanggan Offline</h5>
                                        <p style="margin: 4px 0 0; font-size: 14px; color: #666;">
                                            Buat booking manual untuk pelanggan yang datang langsung atau booking via telepon
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Button --}}
                            <div class="col-lg-4 col-md-5 text-md-end">
                                <a href="{{ route('admin.pos.index') }}" class="btn" style="background: #d13a8a; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; font-size: 15px; transition: all 0.3s; box-shadow: 0 4px 12px rgba(209, 58, 138, 0.3); text-decoration: none; display: inline-block;">
                                    <i class="fas fa-store-alt me-2"></i>Buat Jadwal Offline
                                </a>
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="row mt-3 pt-3" style="border-top: 1px dashed #d13a8a;">
                            <div class="col-6 col-md-2 text-center mb-2 mb-md-0">
                                <div style="font-size: 13px; color: #888; margin-bottom: 4px;">Booking Hari Ini</div>
                                <div style="font-size: 20px; font-weight: 800; color: #d13a8a;">
                                    {{ \App\Models\Reservasi::whereDate('tanggal_reservasi', today())->count() }}
                                </div>
                            </div>
                            <div class="col-6 col-md-2 text-center mb-2 mb-md-0">
                                <div style="font-size: 13px; color: #888; margin-bottom: 4px;">Sudah Dibayar Lunas</div>
                                <div style="font-size: 20px; font-weight: 800; color: #28a745;">
                                    {{ \App\Models\Pembayaran::where('status_pembayaran', 'bayar_lunas')->count() }}
                                </div>
                            </div>
                            <div class="col-6 col-md-2 text-center">
                                <div style="font-size: 13px; color: #888; margin-bottom: 4px;">Berlangsung</div>
                                <div style="font-size: 20px; font-weight: 800; color: #17a2b8;">
                                    {{ \App\Models\Reservasi::where('status_reservasi', 'proses')->count() }}
                                </div>
                            </div>
                            <div class="col-6 col-md-2 text-center">
                                <div style="font-size: 13px; color: #888; margin-bottom: 4px;">Selesai Bulan Ini</div>
                                <div style="font-size: 20px; font-weight: 800; color: #6c757d;">
                                    {{ \App\Models\Reservasi::where('status_reservasi', 'selesai')->whereMonth('created_at', now()->month)->count() }}
                                </div>
                            </div>
                            <div class="col-6 col-md-2 text-center">
                                <div style="font-size: 13px; color: #888; margin-bottom: 4px;">Pembatalan Lunas</div>
                                <div style="font-size: 20px; font-weight: 800; color: #6c757d;">
                                    {{ \App\Models\Pembayaran::where('status_pembayaran', 'pembatalan_lunas')->whereMonth('created_at', now()->month)->count() }}
                                </div>
                            </div>
                            <div class="col-6 col-md-2 text-center">
                                <div style="font-size: 13px; color: #888; margin-bottom: 4px;">Pembatalan DP</div>
                                <div style="font-size: 20px; font-weight: 800; color: #6c757d;">
                                    {{ \App\Models\Pembayaran::where('status_pembayaran', 'pembatalan_dp')->whereMonth('created_at', now()->month)->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="mb-4" style="font-weight: 600;">Aurora Beauty Salon / Booking</h4>
        {{-- Baris untuk Kalender dan List Jadwal --}}
        <div class="row g-4 mb-4">
            {{-- Kolom Kalender (kiri) --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body p-0">
                        <div id="calendar" class="calendar-wrapper">
                            <header class="calendar-header">
                                <button id="prev-month" class="nav-btn">&lt;</button>
                                <h2 id="month-year"></h2>
                                <button id="next-month" class="nav-btn">&gt;</button>
                            </header>
                            <div class="calendar-body">
                                <div class="calendar-weekdays">
                                    <div>MINGGU</div> <div>SENIN</div> <div>SELASA</div> <div>RABU</div> <div>KAMIS</div> <div>JUMAT</div> <div>SABTU</div>
                                </div>
                                <div class="calendar-days" id="calendar-days">
                                    {{-- Diisi oleh JavaScript --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom List Jadwal Pelanggan (kanan) --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        List Jadwal Pelanggan
                        <a href="{{ route('booking.list') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Pelanggan</th> <th>Layanan</th> <th>Tanggal</th> <th>Waktu</th> <th>Slot</th> <th>Status Pembayaran</th>
                                    </tr>
                                </thead>
                                <tbody id="jadwal-pelanggan-tbody">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            Pilih tanggal di kalender untuk melihat jadwal.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Baris untuk Tabs dan Pengaturan Booking --}}
        <div class="row">
            <div class="col-12">
                <form method="POST" enctype="multipart/form-data" id="form-pengaturan">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="ringkasan-tab" data-bs-toggle="tab" data-bs-target="#ringkasan" type="button" role="tab" aria-controls="ringkasan" aria-selected="true">Ringkasan</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="arsip-tab" data-bs-toggle="tab" data-bs-target="#arsip" type="button" role="tab" aria-controls="arsip" aria-selected="false">Arsip</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tentang-tab" data-bs-toggle="tab" data-bs-target="#tentang" type="button" role="tab" aria-controls="tentang" aria-selected="false">Tentang Kami</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="lokasi-tab" data-bs-toggle="tab" data-bs-target="#lokasi" type="button" role="tab" aria-controls="lokasi" aria-selected="false">Lokasi</button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content" id="myTabContent">
                                {{-- TAB 1: RINGKASAN --}}
                                <div class="tab-pane fade show active" id="ringkasan" role="tabpanel" aria-labelledby="ringkasan-tab">
                                    <h5 class="card-title mb-4">Pengaturan Booking Mandiri:</h5>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="aktifkanBooking" class="form-label">Aktifkan Booking Mandiri:</label>
                                            <select id="aktifkanBooking" name="booking_aktif" class="form-select">
                                                <option value="" disabled selected>-- Pilih --</option>
                                                <option value="1">Ya</option>
                                                <option value="0">Tidak</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="jamMulai" class="form-label">Jam Mulai:</label>
                                            <input type="time" id="jamMulai" name="jam_mulai" class="form-control" value="">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="jamSelesai" class="form-label">Jam Selesai:</label>
                                            <input type="time" id="jamSelesai" name="jam_selesai" class="form-control" value="">
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="opsiStaff" class="form-label">Opsi Staff/Terapis:</label>
                                            <select id="opsiStaff" name="opsi_staff" class="form-select">
                                                <option value="" disabled selected>-- Pilih --</option>
                                                <option value="0">Tidak</option>
                                                <!-- aktifkan jika menambahkan fitur untuk pelanggan bisa memilih staff-->
                                                <option value="1" disabled>Ya (Pelanggan bisa memilih)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="dpValue" class="form-label">DP/Down Payment (%):</label>
                                            <div class="input-group">
                                                <input type="number" id="dpValue" name="dp_value" class="form-control" value="">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text">*Masukkan nilai persentase DP (misal: 70).</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="maksRentang" class="form-label">Maksimal Rentang Booking (hari):</label>
                                            <input type="number" id="maksRentang" name="maks_rentang_booking" class="form-control" value="">
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-8">
                                            <label for="kebijakan" class="form-label">Pengaturan Kebijakan:</label>
                                            <textarea id="kebijakan" name="kebijakan" class="form-control" rows="3" placeholder="Tambahkan Kebijakan..."></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="intervalMin" class="form-label">Interval Minimal Booking (menit):</label>
                                            <input type="number" id="intervalMin" name="interval_min_booking" class="form-control" value="">
                                        </div>
                                    </div>
                                </div>

                                {{-- TAB 2: ARSIP --}}
                                <div class="tab-pane fade" id="arsip" role="tabpanel" aria-labelledby="arsip-tab">
                                    <h5 class="card-title mb-4">Pengaturan Galeri Arsip</h5>

                                    <div class="mb-3">
                                        <label for="id_layanan_arsip" class="form-label">Pilih Layanan:</label>
                                        <select class="form-select" id="id_layanan_arsip" name="id_layanan">
                                            <option value="">-- Pilih Layanan --</option>
                                        </select>
                                        <div class="form-text">Pilih layanan untuk melihat atau menambahkan foto galeri.</div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="mb-3">
                                        <label class="form-label">Galeri Saat Ini (untuk layanan yang dipilih):</label>
                                        <div id="gallery-preview-container" class="d-flex flex-wrap gap-2 border p-3 rounded" style="min-height: 100px;">
                                            <span id="gallery-loading-spinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                                            <span id="gallery-empty-text" class="text-muted">Pilih layanan untuk melihat galeri.</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="new-images-input" class="form-label">Tambah Foto Baru:</label>
                                        <input class="form-control" type="file" id="new-images-input" name="new_images[]" multiple accept="image/png, image/jpeg, image/webp" disabled>
                                        <div class="form-text" id="new-images-help-text">Pilih layanan terlebih dahulu sebelum menambah foto.</div>
                                    </div>

                                    <div id="new-captions-container" class="d-grid gap-3" style="grid-template-columns: 1fr 1fr;">
                                        {{-- Input keterangan akan digenerate oleh JS di sini --}}
                                    </div>
                                </div>

                                {{-- TAB 3: TENTANG KAMI --}}
                                <div class="tab-pane fade" id="tentang" role="tabpanel" aria-labelledby="tentang-tab">
                                    <h5 class="card-title mb-4">Tentang Kami</h5>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label for="deskripsiBooking" class="form-label">Deskripsi Booking Mandiri:</label>
                                                <textarea class="form-control" id="deskripsiBooking" name="deskripsi_booking" rows="10" placeholder="Jelaskan tentang salon Anda, kebijakan, atau info lainnya..."></textarea>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <label class="form-label">Jam Operasional:</label>
                                            <div id="jam-operasional-wrapper">
                                                <div class="jam-operasional-row">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="jam[senin][status]" id="jam_senin_status"><label class="form-check-label" for="jam_senin_status">Senin</label></div>
                                                    <input type="time" class="form-control" name="jam[senin][mulai]" value="09:00">
                                                    <input type="time" class="form-control" name="jam[senin][selesai]" value="19:00">
                                                </div>
                                                <div class="jam-operasional-row">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="jam[selasa][status]" id="jam_selasa_status"><label class="form-check-label" for="jam_selasa_status">Selasa</label></div>
                                                    <input type="time" class="form-control" name="jam[selasa][mulai]" value="09:00">
                                                    <input type="time" class="form-control" name="jam[selasa][selesai]" value="19:00">
                                                </div>
                                                <div class="jam-operasional-row">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="jam[rabu][status]" id="jam_rabu_status"><label class="form-check-label" for="jam_rabu_status">Rabu</label></div>
                                                    <input type="time" class="form-control" name="jam[rabu][mulai]" value="09:00">
                                                    <input type="time" class="form-control" name="jam[rabu][selesai]" value="19:00">
                                                </div>
                                                <div class="jam-operasional-row">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="jam[kamis][status]" id="jam_kamis_status"><label class="form-check-label" for="jam_kamis_status">Kamis</label></div>
                                                    <input type="time" class="form-control" name="jam[kamis][mulai]" value="09:00">
                                                    <input type="time" class="form-control" name="jam[kamis][selesai]" value="19:00">
                                                </div>
                                                <div class="jam-operasional-row">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="jam[jumat][status]" id="jam_jumat_status"><label class="form-check-label" for="jam_jumat_status">Jumat</label></div>
                                                    <input type="time" class="form-control" name="jam[jumat][mulai]" value="09:00">
                                                    <input type="time" class="form-control" name="jam[jumat][selesai]" value="19:00">
                                                </div>
                                                <div class="jam-operasional-row">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="jam[sabtu][status]" id="jam_sabtu_status"><label class="form-check-label" for="jam_sabtu_status">Sabtu</label></div>
                                                    <input type="time" class="form-control" name="jam[sabtu][mulai]" value="09:00">
                                                    <input type="time" class="form-control" name="jam[sabtu][selesai]" value="19:00">
                                                </div>
                                                <div class="jam-operasional-row">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="jam[minggu][status]" id="jam_minggu_status"><label class="form-check-label" for="jam_minggu_status">Minggu</label></div>
                                                    <input type="time" class="form-control" name="jam[minggu][mulai]" value="09:00">
                                                    <input type="time" class="form-control" name="jam[minggu][selesai]" value="19:00">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- TAB 4: LOKASI --}}
                                <div class="tab-pane fade" id="lokasi" role="tabpanel" aria-labelledby="lokasi-tab">
                                    <h5 class="card-title mb-4">Informasi Lokasi</h5>
                                    <div class="mb-3">
                                        <label for="koordinat-input" class="form-label">Koordinat (Latitude, Longitude):</label>
                                        <input type="text" class="form-control" id="koordinat-input" name="koordinat" value="0.750602, 124.322494">
                                        <div class="form-text">Klik pada peta, geser penanda, atau masukkan koordinat manual.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="alamat-input" class="form-label">Alamat:</label>
                                        <textarea class="form-control" id="alamat-input" name="alamat" rows="3" placeholder="Alamat akan terisi otomatis..."></textarea>
                                    </div>
                                    <div id="map-lokasi"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Simpan Utama --}}
                    <div class="d-flex justify-content-start mt-3 mb-4">
                        <button type="button" class="btn btn-pink" id="btn-simpan-pengaturan">
                            Simpan
                            <span class="spinner-border spinner-border-sm ms-1 d-none" role="status" aria-hidden="true" id="simpan-spinner"></span>
                        </button>
                    </div>
                    <br><br>
                </form>
            </div>
        </div>

    </div>

    {{-- MODAL UPDATE STATUS --}}
    <div class="modal fade" id="modalUpdateStatus" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status Reservasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formUpdateStatus">
                        <input type="hidden" id="update_id_reservasi" name="id_reservasi">
                        <div class="mb-3">
                            <label class="form-label">Pilih Status Baru:</label>
                            <select class="form-select" name="status" id="select_status_reservasi">
                                <option value="pending">Pending</option>
                                <option value="proses">Sedang Berjalan (Proses)</option>
                                <option value="selesai">Selesai</option>
                                <option value="dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="alert alert-info py-2" style="font-size: 0.9rem;">
                            <i class="fas fa-info-circle"></i> Pastikan pembayaran lunas sebelum ubah ke Selesai.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="submitUpdateStatus()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL MARK AS PAID --}}
    <div class="modal fade" id="modalMarkPaid" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success"><i class="fas fa-check-circle"></i> Tandai Sudah Bayar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formMarkPaid">
                        <input type="hidden" id="paid_id_reservasi" name="id_reservasi">
                        <div class="mb-3">
                            <label class="form-label">Jumlah Pembayaran (Rp):</label>
                            <input type="number" class="form-control" name="jumlah" id="input_jumlah_bayar" required min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran:</label>
                            <select class="form-select" name="metode" id="input_metode_bayar">
                                <option value="Cash">Cash / Tunai</option>
                                <option value="Transfer Manual">Transfer Manual</option>
                                <option value="EDC">EDC / Kartu</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="submitMarkPaid()">Simpan Pembayaran</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL ADD ADDITIONAL COST --}}
    <div class="modal fade" id="modalAddCost" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle text-primary"></i> Tambah Biaya (Add-on)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAddCost">
                        <input type="hidden" id="cost_id_reservasi" name="id_reservasi">
                        <div class="mb-3">
                            <label class="form-label">Nominal Tambahan (Rp):</label>
                            <input type="number" class="form-control" name="nominal" id="input_cost_nominal" required min="1" placeholder="Contoh: 50000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan Tambahan:</label>
                            <input type="text" class="form-control" name="catatan" id="input_cost_catatan" placeholder="Contoh: Vitamin Rambut, Extra Service">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="submitAddCost()">Simpan Tambahan</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- Aman dari null: ambil CSRF dari input ATAU meta ---
        const CSRF_TOKEN =
            document.querySelector('input[name="_token"]')?.value
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '';

        const formPengaturan   = document.getElementById('form-pengaturan');
        const btnSimpan        = document.getElementById('btn-simpan-pengaturan');
        const spinnerSimpan    = document.getElementById('simpan-spinner');

        // Elemen Arsip
        const selectLayananArsip      = document.getElementById('id_layanan_arsip');
        const galleryPreviewContainer = document.getElementById('gallery-preview-container');
        const galleryLoadingSpinner   = document.getElementById('gallery-loading-spinner');
        const galleryEmptyText        = document.getElementById('gallery-empty-text');
        const newImagesInput          = document.getElementById('new-images-input');
        const newImagesHelpText       = document.getElementById('new-images-help-text');
        const newCaptionsContainer    = document.getElementById('new-captions-container');

        let allGalleryImages = [];
        const imagesToDelete = new Set();

        // URL dari route
        const JADWAL_URL          = '{{ route("pengaturan.reservasibooking") }}';
        const LOAD_BOOKING_URL    = '{{ route("pengaturan.booking") }}';
        const SAVE_BOOKING_URL    = '{{ route("pengaturan.booking.simpan") }}';
        const LOAD_ARSIP_URL      = '{{ route("pengaturan.arsip") }}';
        const SAVE_ARSIP_URL      = '{{ route("pengaturan.arsip.simpan") }}';
        const LAYANAN_AJAX_URL    = '{{ route("layanan.ajax") }}';
        const LOAD_TENTANG_URL    = '{{ route("pengaturan.tentangkami") }}';
        const SAVE_TENTANG_URL    = '{{ route("pengaturan.tentangkami.simpan") }}';
        const LOAD_LOKASI_URL     = '{{ route("pengaturan.lokasi") }}';
        const SAVE_LOKASI_URL     = '{{ route("pengaturan.lokasi.simpan") }}';

        // --- Helper tabel jadwal ---
        const tbodyJadwal = document.getElementById('jadwal-pelanggan-tbody');

        function formatTanggalKeYMD(date){
            const y = date.getFullYear();
            const m = String(date.getMonth()+1).padStart(2,'0');
            const d = String(date.getDate()).padStart(2,'0');
            return `${y}-${m}-${d}`;
        }

        function formatTanggal(ymd){
            const p = (ymd||'').split('-'); return p.length===3 ? `${p[2]}-${p[1]}-${p[0]}` : ymd;
        }

        // STATUS BADGE – disamakan dengan $mapStatus
        function getStatusBadge(s) {
            if (!s) return '';

            const status = String(s).toLowerCase();

            switch (status) {
                case 'pending':
                    return `<span class="badge bg-warning text-dark">Menunggu Pembayaran</span>`;

                case 'proses':
                    return `<span class="badge bg-info text-white">Dibooking (Sudah Dibayar)</span>`;

                case 'sudah bayar':
                    return `<span class="badge bg-success">Sudah Dibayar Lunas</span>`;

                case 'dibatalkan':
                    return `<span class="badge bg-danger">Dibatalkan</span>`;

                case 'selesai':
                    return `<span class="badge bg-success">selesai</span>`;

                default:
                    const label = status.charAt(0).toUpperCase() + status.slice(1);
                    return `<span class="badge bg-secondary">${label}</span>`;
            }
        }

        function fetchJadwal(date){
            if(!tbodyJadwal) return;
            const formatted = formatTanggalKeYMD(date);
            const url = `${JADWAL_URL}?date=${formatted}`;
            tbodyJadwal.innerHTML = `<tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Memuat data...</td></tr>`;
            fetch(url)
                .then(r => { if(!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
                .then(res => renderTabelJadwal(res.data, formatted))
                .catch(err => {
                    console.error(err);
                    tbodyJadwal.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Gagal memuat data. (${err.message})</td></tr>`;
                });
        }

        function renderTabelJadwal(data, ymd){
            if(!data || !data.length){
                tbodyJadwal.innerHTML = `<tr><td colspan="6" class="text-center py-4">Tidak ada jadwal pada tanggal ${formatTanggal(ymd)}.</td></tr>`;
                return;
            }

            tbodyJadwal.innerHTML = data.map(item=>{
                const jm = item.jam_mulai ? item.jam_mulai.substring(0,5) : '-';
                const js = item.jam_mulai ? item.jam_mulai.substring(0,5) : '-';
                const tg = item.tanggal ? formatTanggal(item.tanggal.split(' ')[0]) : '-';

                return `
                <tr>
                    <td>${item.nama || '-'}</td>
                    <td>${item.nama_layanan || '-'}</td>
                    <td>${tg}</td>
                    <td>${jm}</td>
                    <td>${js}</td>
                    <td>
                        <span class="badge ${item.status_cls}">
                            ${item.status_txt}
                        </span>
                    </td>
                </tr>`;
            }).join('');
        }

        // --- LOGIKA KALENDER (robust) ---
        (function initCalendar(){
            const monthYearEl   = document.getElementById('month-year');
            const calendarDays  = document.getElementById('calendar-days');
            const prevBtn       = document.getElementById('prev-month');
            const nextBtn       = document.getElementById('next-month');

            if(!monthYearEl || !calendarDays || !prevBtn || !nextBtn){
                console.warn('Elemen kalender tidak lengkap (#month-year / #calendar-days / #prev-month / #next-month).');
                return;
            }

            let selectedDate = (() => {
                const saved = localStorage.getItem('auroraSelectedDate');
                const d = saved ? new Date(saved + 'T00:00:00') : new Date();
                return isNaN(d) ? new Date() : d;
            })();
            selectedDate.setHours(0,0,0,0);

            let currentMonth = selectedDate.getMonth();
            let currentYear  = selectedDate.getFullYear();

            function setHeader(y, m){
                try {
                    monthYearEl.textContent =
                        new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' })
                        .format(new Date(y, m, 1))
                        .toUpperCase();
                } catch (e) {
                    const namaBulan = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
                    monthYearEl.textContent = `${namaBulan[m]} ${y}`.toUpperCase();
                }
            }

            function renderCalendar(){
                setHeader(currentYear, currentMonth);
                calendarDays.innerHTML = '';

                const firstDay  = new Date(currentYear, currentMonth, 1).getDay();
                const lastDate  = new Date(currentYear, currentMonth+1, 0).getDate();
                const today     = new Date(); today.setHours(0,0,0,0);

                for(let i=0;i<firstDay;i++){
                    const gap = document.createElement('div');
                    gap.className = 'empty-cell';
                    gap.innerHTML = '&nbsp;';
                    calendarDays.appendChild(gap);
                }

                for(let d=1; d<=lastDate; d++){
                    const el = document.createElement('div');
                    el.textContent = d;
                    const dayDate = new Date(currentYear, currentMonth, d); dayDate.setHours(0,0,0,0);

                    if(+dayDate === +selectedDate) el.classList.add('selected');
                    if(dayDate < today) el.classList.add('disabled');

                    if(!el.classList.contains('disabled')){
                        el.addEventListener('click', () => {
                            document.querySelector('.calendar-days .selected')?.classList.remove('selected');
                            el.classList.add('selected');
                            selectedDate = new Date(currentYear, currentMonth, d); selectedDate.setHours(0,0,0,0);
                            localStorage.setItem('auroraSelectedDate', formatTanggalKeYMD(selectedDate));
                            fetchJadwal(selectedDate);
                        });
                    }
                    calendarDays.appendChild(el);
                }

                const total = calendarDays.children.length;
                const target = total <= 35 ? 35 : 42;
                for(let i=0;i<target-total;i++){
                    const gap = document.createElement('div');
                    gap.className = 'empty-cell';
                    gap.innerHTML = '&nbsp;';
                    calendarDays.appendChild(gap);
                }
            }

            prevBtn.addEventListener('click', () => {
                currentMonth--; if(currentMonth < 0){ currentMonth = 11; currentYear--; }
                renderCalendar();
            });
            nextBtn.addEventListener('click', () => {
                currentMonth++; if(currentMonth > 11){ currentMonth = 0; currentYear++; }
                renderCalendar();
            });

            renderCalendar();
            fetchJadwal(selectedDate);
        })();

        // --- 2. LOGIKA PETA LOKASI (LEAFLET) ---
        const mapElement = document.getElementById('map-lokasi');
        const koordinatInput = document.getElementById('koordinat-input');
        const alamatInput = document.getElementById('alamat-input');

        if (mapElement && koordinatInput && alamatInput) {
            let map = null;
            let marker = null;
            if (typeof L === 'undefined') {
                console.error("LEAFLET (L) TIDAK TERDEFINISI.");
            } else {
                function reverseGeocode(latlng) {
                    alamatInput.value = "Mencari alamat...";
                    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latlng.lat}&lon=${latlng.lng}`;
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            alamatInput.value = (data && data.display_name) ? data.display_name : "Alamat tidak ditemukan.";
                        })
                        .catch(error => {
                            console.error('Error reverse geocoding:', error);
                            alamatInput.value = "Gagal mengambil data alamat.";
                        });
                }
                function updateLocation(latlng) {
                    koordinatInput.value = `${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`;
                    marker.setLatLng(latlng);
                    reverseGeocode(latlng);
                }
                function initMap() {
                    const coords = koordinatInput.value.split(',').map(Number);
                    const lat = coords[0] || 0.750602;
                    const lon = coords[1] || 124.322494;
                    const indonesiaBounds = L.latLngBounds(L.latLng(-11, 95), L.latLng(6, 141));
                    map = L.map('map-lokasi', {
                        attributionControl: false, minZoom: 5, maxBounds: indonesiaBounds, maxBoundsViscosity: 1.0
                    }).setView([lat, lon], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                    marker = L.marker([lat, lon], { draggable: true }).addTo(map);
                    marker.on('dragend', (e) => updateLocation(e.target.getLatLng()));
                    map.on('click', (e) => updateLocation(e.latlng));
                    if (koordinatInput.value && (lat !== 0 || lon !== 0)) {
                        reverseGeocode(L.latLng(lat, lon));
                    } else {
                        alamatInput.value = '';
                    }
                }
                koordinatInput.addEventListener('change', function() {
                    const coords = this.value.split(',').map(Number);
                    if (map && marker && coords.length === 2 && !isNaN(coords[0]) && !isNaN(coords[1])) {
                        const newLatLng = L.latLng(coords[0], coords[1]);
                        map.setView(newLatLng, 13);
                        marker.setLatLng(newLatLng);
                        reverseGeocode(newLatLng);
                    }
                });
                const lokasiTabButton = document.getElementById('lokasi-tab');
                if (lokasiTabButton) {
                    lokasiTabButton.addEventListener('shown.bs.tab', function() {
                        if (!map) { initMap(); } else { map.invalidateSize(); }
                    });
                }
            }
        }

        // =======================================================
        // TAB 1: RINGKASAN (LOAD & SAVE)
        // =======================================================
        function loadPengaturanBooking() {
            fetch(LOAD_BOOKING_URL)
                .then(response => {
                    if (!response.ok) { throw new Error('Gagal mengambil data pengaturan.'); }
                    return response.json();
                })
                .then(result => {
                    if (result.data) {
                        const data = result.data;
                        if (formPengaturan) {
                            formPengaturan.querySelector('[name="booking_aktif"]').value = (data.booking_aktif === 1 || data.booking_aktif === 0) ? data.booking_aktif : '';
                            formPengaturan.querySelector('[name="jam_mulai"]').value = data.jam_mulai || '';
                            formPengaturan.querySelector('[name="jam_selesai"]').value = data.jam_selesai || '';
                            formPengaturan.querySelector('[name="opsi_staff"]').value = (data.opsi_staff === 1 || data.opsi_staff === 0) ? data.opsi_staff : '';
                            formPengaturan.querySelector('[name="dp_value"]').value = data.dp_value || '';
                            formPengaturan.querySelector('[name="maks_rentang_booking"]').value = data.maks_rentang_booking || '';
                            formPengaturan.querySelector('[name="interval_min_booking"]').value = data.interval_min_booking || '';
                            formPengaturan.querySelector('[name="kebijakan"]').value = data.kebijakan || '';
                        }
                    }
                })
                .catch(error => console.error('Error [loadPengaturanBooking]:', error));
        }

        function saveTabRingkasan() {
            if (!formPengaturan || !btnSimpan || !spinnerSimpan) return;
            const formData = new FormData(formPengaturan);
            spinnerSimpan.classList.remove('d-none');
            btnSimpan.disabled = true;

            fetch(SAVE_BOOKING_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.success
                    });
                } else if (data.error) {
                    let errorMsg = 'Gagal menyimpan. Periksa error berikut:\n';
                    if (typeof data.error === 'object') {
                        const tab1Fields = ['booking_aktif', 'jam_mulai', 'jam_selesai', 'opsi_staff', 'dp_value', 'maks_rentang_booking', 'interval_min_booking', 'kebijakan'];
                        for (const key in data.error) {
                            if (tab1Fields.includes(key)) {
                                errorMsg += `- ${data.error[key].join(', ')}\n`;
                            }
                        }
                    } else { errorMsg = 'Terjadi kesalahan: ' + data.error; }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorMsg
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan yang tidak diketahui.'
                    });
                }
            })
            .catch(error => {
                console.error('Error [saveTabRingkasan]:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan. Gagal menghubungi server.'
                });
            })
            .finally(() => {
                spinnerSimpan.classList.add('d-none');
                btnSimpan.disabled = false;
            });
        }

        // =======================================================
        // TAB 2: ARSIP (LOAD & SAVE)
        // =======================================================
        function loadLayananOptions() {
            if (!selectLayananArsip) return;
            fetch(LAYANAN_AJAX_URL)
                .then(response => response.json())
                .then(result => {
                    if (result.success && result.data) {
                        selectLayananArsip.innerHTML = '<option value="">-- Pilih Layanan --</option>';
                        result.data.forEach(layanan => {
                            selectLayananArsip.innerHTML += `<option value="${layanan.id_layanan}">${layanan.nama_layanan}</option>`;
                        });
                    }
                })
                .catch(error => console.error('Error [loadLayananOptions]:', error));
        }

        function loadAllGalleryImages() {
            if (!galleryPreviewContainer) return;
            galleryPreviewContainer.innerHTML = '';
            galleryLoadingSpinner.style.display = 'inline-block';
            galleryPreviewContainer.appendChild(galleryLoadingSpinner);

            fetch(LOAD_ARSIP_URL)
                .then(response => response.json())
                .then(result => {
                    galleryLoadingSpinner.style.display = 'none';
                    if (result.data && result.data.galeri) {
                        allGalleryImages = result.data.galeri;
                        renderGalleryForService(selectLayananArsip.value);
                    } else {
                        allGalleryImages = [];
                        renderGalleryForService(selectLayananArsip.value);
                    }
                })
                .catch(error => {
                    console.error('Error [loadAllGalleryImages]:', error);
                    allGalleryImages = [];
                    galleryLoadingSpinner.style.display = 'none';
                    galleryPreviewContainer.innerHTML = '<span class="text-danger">Gagal memuat galeri.</span>';
                });
        }

        function renderGalleryForService(serviceId) {
            if (!galleryPreviewContainer) return;
            galleryPreviewContainer.innerHTML = '';
            imagesToDelete.clear();
            galleryEmptyText.style.display = 'none';

            const id = parseInt(serviceId, 10);
            const isServiceSelected = !isNaN(id) && !!serviceId;
            let imagesToRender = [];

            if (isServiceSelected) {
                imagesToRender = allGalleryImages.filter(image => image.id_layanan === id);
                newImagesInput.disabled = false;
                newImagesHelpText.innerText = 'Pilih gambar baru untuk di-upload (Maks 1MB per file).';
            } else {
                imagesToRender = allGalleryImages;
                newImagesInput.disabled = true;
                newImagesHelpText.innerText = 'Pilih layanan terlebih dahulu untuk menambah/menghapus foto.';
            }

            if (imagesToRender.length > 0) {
                imagesToRender.forEach(image => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'gallery-image-wrapper';
                    wrapper.setAttribute('data-id', image.id);

                    const img = document.createElement('img');
                    img.src = image.path_url;
                    wrapper.appendChild(img);

                    if (image.keterangan) {
                        const caption = document.createElement('span');
                        caption.style.cssText = 'position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.6); color:white; font-size:10px; padding: 4px; text-align:center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;';
                        caption.innerText = image.keterangan;
                        caption.title = image.keterangan;
                        wrapper.appendChild(caption);
                    }

                    if (isServiceSelected) {
                        const deleteBtn = document.createElement('button');
                        deleteBtn.type = 'button';
                        deleteBtn.className = 'gallery-delete-btn';
                        deleteBtn.innerHTML = '&times;';
                        deleteBtn.addEventListener('click', () => {
                            imagesToDelete.add(image.id);
                            wrapper.style.display = 'none';
                        });
                        wrapper.appendChild(deleteBtn);
                    }

                    galleryPreviewContainer.appendChild(wrapper);
                });
            } else {
                galleryEmptyText.innerText = isServiceSelected
                    ? 'Belum ada foto galeri untuk layanan ini.'
                    : 'Pilih layanan untuk melihat galeri atau belum ada foto sama sekali.';
                galleryEmptyText.style.display = 'inline';
                galleryPreviewContainer.appendChild(galleryEmptyText);
            }
        }

        if (newImagesInput && newCaptionsContainer) {
            newImagesInput.addEventListener('change', () => {
                newCaptionsContainer.innerHTML = '';
                const files = newImagesInput.files;
                if (files.length > 0) {
                    Array.from(files).forEach((file) => {
                        const captionItem = document.createElement('div');
                        captionItem.className = 'new-caption-item';

                        const imgPreview = document.createElement('img');
                        imgPreview.src = URL.createObjectURL(file);
                        imgPreview.style.cssText = 'width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px;';

                        const textWrapper = document.createElement('div');
                        textWrapper.style.flex = '1';

                        const label = document.createElement('label');
                        label.className = 'form-label mb-1';
                        label.innerText = `Keterangan untuk: ${file.name}`;

                        const input = document.createElement('input');
                        input.type = 'text';
                        input.className = 'form-control form-control-sm';
                        input.name = 'captions[]';
                        input.placeholder = 'Keterangan (opsional)';

                        textWrapper.appendChild(label);
                        textWrapper.appendChild(input);
                        captionItem.style.display = 'flex';
                        captionItem.style.alignItems = 'center';
                        captionItem.appendChild(imgPreview);
                        captionItem.appendChild(textWrapper);
                        newCaptionsContainer.appendChild(captionItem);
                    });
                }
            });
        }

        if (selectLayananArsip) {
            selectLayananArsip.addEventListener('change', () => {
                renderGalleryForService(selectLayananArsip.value);
                newImagesInput.value = '';
                newCaptionsContainer.innerHTML = '';
            });
        }

        function saveTabArsip() {
            if (!formPengaturan || !btnSimpan || !spinnerSimpan || !selectLayananArsip) return;

            const selectedLayananId = selectLayananArsip.value;
            if (!selectedLayananId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silakan pilih layanan terlebih dahulu untuk menyimpan perubahan galeri.'
                });
                return;
            }

            spinnerSimpan.classList.remove('d-none');
            btnSimpan.disabled = true;

            const formData = new FormData();
            formData.append('_token', CSRF_TOKEN);
            formData.append('id_layanan', selectedLayananId);

            imagesToDelete.forEach(id => formData.append('deleted_images[]', id));

            if (newImagesInput && newImagesInput.files.length > 0) {
                Array.from(newImagesInput.files).forEach(file => formData.append('new_images[]', file));
            }

            const captionInputs = newCaptionsContainer.querySelectorAll('input[name="captions[]"]');
            captionInputs.forEach(input => formData.append('captions[]', input.value));

            fetch(SAVE_ARSIP_URL, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.success
                    });
                    loadAllGalleryImages();
                    newImagesInput.value = '';
                    newCaptionsContainer.innerHTML = '';
                    imagesToDelete.clear();
                } else if (data.error) {
                    let errorMsg = 'Gagal menyimpan. Periksa error berikut:\n';
                    if (typeof data.error === 'object') {
                        for (const key in data.error) {
                            if (key.startsWith('new_images') || key === 'id_layanan' || key.startsWith('deleted_images') || key.startsWith('captions')) {
                                errorMsg += `- ${data.error[key].join(', ')}\n`;
                            }
                        }
                    } else { errorMsg = 'Terjadi kesalahan: ' + data.error; }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorMsg
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan yang tidak diketahui.'
                    });
                }
            })
            .catch(error => {
                console.error('Error [saveTabArsip]:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan. Gagal menghubungi server.'
                });
            })
            .finally(() => {
                spinnerSimpan.classList.add('d-none');
                btnSimpan.disabled = false;
            });
        }

        // =======================================================
        // TAB 3: TENTANG KAMI (LOAD & SAVE)
        // =======================================================
        function loadTabTentang() {
            fetch(LOAD_TENTANG_URL)
                .then(response => response.json())
                .then(result => {
                    if (result.data) {
                        const data = result.data;
                        formPengaturan.querySelector('[name="deskripsi_booking"]').value = data.deskripsi || '';

                        let jamOperasional = {};
                        const hariArray = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

                        if (typeof data.hari_operasional === 'string' && data.hari_operasional) {
                            try { jamOperasional = JSON.parse(data.hari_operasional); }
                            catch { jamOperasional = {}; }
                        }

                        hariArray.forEach(hari => {
                            const dataHari = jamOperasional[hari];
                            const statusCheckbox = formPengaturan.querySelector(`[name="jam[${hari}][status]"]`);
                            const mulaiInput = formPengaturan.querySelector(`[name="jam[${hari}][mulai]"]`);
                            const selesaiInput = formPengaturan.querySelector(`[name="jam[${hari}][selesai]"]`);

                            if (dataHari && statusCheckbox && mulaiInput && selesaiInput) {
                                statusCheckbox.checked = true;
                                mulaiInput.value = dataHari.mulai || '09:00';
                                selesaiInput.value = dataHari.selesai || '19:00';
                            } else if (statusCheckbox) {
                                statusCheckbox.checked = false;
                            }
                        });
                    }
                })
                .catch(error => console.error('Error [loadTabTentang]:', error));
        }

        function saveTabTentang() {
            if (!formPengaturan || !btnSimpan || !spinnerSimpan) return;

            const formData = new FormData(formPengaturan);
            formPengaturan.querySelectorAll('input[type="checkbox"][name*="[status]"]').forEach(cb => {
                if (cb.checked) formData.set(cb.name, 'on');
            });

            spinnerSimpan.classList.remove('d-none');
            btnSimpan.disabled = true;

            fetch(SAVE_TENTANG_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.success
                    });
                } else if (data.error) {
                    let errorMsg = 'Gagal menyimpan. Periksa error berikut:\n';
                    if (typeof data.error === 'object') {
                        for (const key in data.error) {
                            if (key === 'deskripsi_booking' || key.startsWith('jam.') || ['deskripsi','hari_operasional','jam_buka','jam_tutup'].includes(key)) {
                                errorMsg += `- ${data.error[key].join(', ')}\n`;
                            }
                        }
                    } else { errorMsg = 'Terjadi kesalahan: ' + data.error; }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorMsg
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan yang tidak diketahui.'
                    });
                }
            })
            .catch(error => {
                console.error('Error [saveTabTentang]:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan. Gagal menghubungi server.'
                });
            })
            .finally(() => {
                spinnerSimpan.classList.add('d-none');
                btnSimpan.disabled = false;
            });
        }

        // =======================================================
        // TAB 4: LOKASI (LOAD & SAVE)
        // =======================================================
        function loadTabLokasi() {
            fetch(LOAD_LOKASI_URL)
                .then(response => response.json())
                .then(result => {
                    if (result.data) {
                        const data = result.data;
                        const koordinatField = formPengaturan.querySelector('[name="koordinat"]');
                        const alamatField = formPengaturan.querySelector('[name="alamat"]');

                        if (koordinatField) {
                            koordinatField.value = data.koordinat || '0.750602, 124.322494';
                            koordinatField.dispatchEvent(new Event('change'));
                        }
                        if (alamatField) {
                            alamatField.value = data.alamat || '';
                        }
                    }
                })
                .catch(error => console.error('Error [loadTabLokasi]:', error));
        }

        function saveTabLokasi() {
            if (!formPengaturan || !btnSimpan || !spinnerSimpan) return;
            const formData = new FormData(formPengaturan);
            spinnerSimpan.classList.remove('d-none');
            btnSimpan.disabled = true;

            fetch(SAVE_LOKASI_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.success
                    });
                } else if (data.error) {
                    let errorMsg = 'Gagal menyimpan. Periksa error berikut:\n';
                    if (typeof data.error === 'object') {
                        for (const key in data.error) {
                            if (key === 'koordinat' || key === 'alamat') {
                                errorMsg += `- ${data.error[key].join(', ')}\n`;
                            }
                        }
                    } else { errorMsg = 'Terjadi kesalahan: ' + data.error; }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorMsg
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan yang tidak diketahui.'
                    });
                }
            })
            .catch(error => {
                console.error('Error [saveTabLokasi]:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan. Gagal menghubungi server.'
                });
            })
            .finally(() => {
                spinnerSimpan.classList.add('d-none');
                btnSimpan.disabled = false;
            });
        }

        // =======================================================
        // LOGIKA SIMPAN UTAMA (BUTTON)
        // =======================================================
        if (btnSimpan) {
            btnSimpan.addEventListener('click', () => {
                const activeTabButton = document.querySelector('.nav-tabs .nav-link.active');
                if (!activeTabButton) return;
                const activeTabId = activeTabButton.id;

                switch (activeTabId) {
                    case 'ringkasan-tab':  saveTabRingkasan(); break;
                    case 'arsip-tab':      saveTabArsip(); break;
                    case 'tentang-tab':    saveTabTentang(); break;
                    case 'lokasi-tab':     saveTabLokasi(); break;
                    default: console.warn('Tab tidak dikenal:', activeTabId);
                }
            });
        }

        // =======================================================
        // PANGGIL FUNGSI LOAD AWAL
        // =======================================================
        function loadAllSettings() {
            loadPengaturanBooking();
            loadLayananOptions();
            loadAllGalleryImages();
            loadTabTentang();
            loadTabLokasi();
        }

        loadAllSettings();

        // =======================================================
        // MANUAL UPDATE & MARK PAID LOGIC (GLOBAL)
        // =======================================================
        
        // Expose functions to window so onclick works
        window.openUpdateStatusModal = function(id, currentStatus) {
            document.getElementById('update_id_reservasi').value = id;
            
            // Set select option (mapping text to value if needed, or just select by value)
            const select = document.getElementById('select_status_reservasi');
            // Reset
            select.selectedIndex = 0; 
            
            // Try to match value
            const statusMap = {
                'Menunggu Pembayaran': 'pending',
                'Sedang Berjalan': 'proses',
                'Selesai': 'selesai',
                'Dibatalkan': 'dibatalkan',
                'Menunggu Konfirmasi Pembatalan': 'menunggu_konfirmasi_pembatalan'
            };
            
            // Try to find key that matches currentStatus text
            let val = currentStatus.toLowerCase();
            // Simple mapping attempts
            if (val.includes('berjalan')) val = 'proses';
            else if (val.includes('selesai')) val = 'selesai';
            else if (val.includes('dibatalkan')) val = 'dibatalkan';
            else if (val.includes('pending') || val.includes('menunggu')) val = 'pending';
            
            if (statusMap[currentStatus]) val = statusMap[currentStatus];
            
            select.value = val;
            
            const modal = new bootstrap.Modal(document.getElementById('modalUpdateStatus'));
            modal.show();
        };

        window.submitUpdateStatus = function() {
            const id = document.getElementById('update_id_reservasi').value;
            const status = document.getElementById('select_status_reservasi').value;
            
            fetch('{{ route("reservasi.update_status") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id_reservasi: id, status: status })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Berhasil', data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('modalUpdateStatus')).hide();
                    refreshJadwalGlobal();
                } else {
                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                }
            })
            .catch(err => Swal.fire('Error', 'Gagal menghubungi server', 'error'));
        };

        window.openMarkPaidModal = function(id) {
            document.getElementById('paid_id_reservasi').value = id;
            document.getElementById('input_jumlah_bayar').value = '';
            document.getElementById('input_metode_bayar').selectedIndex = 0;
            
            const modal = new bootstrap.Modal(document.getElementById('modalMarkPaid'));
            modal.show();
        };

        window.submitMarkPaid = function() {
            const id = document.getElementById('paid_id_reservasi').value;
            const jumlah = document.getElementById('input_jumlah_bayar').value;
            const metode = document.getElementById('input_metode_bayar').value;
            
            if (!jumlah || jumlah <= 0) {
                Swal.fire('Warning', 'Masukkan jumlah pembayaran yang valid', 'warning');
                return;
            }
            
            fetch('{{ route("reservasi.mark_paid") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    id_reservasi: id, 
                    jumlah_bayar: jumlah,
                    metode_pembayaran: metode
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Berhasil', data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('modalMarkPaid')).hide();
                    refreshJadwalGlobal();
                } else {
                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                }
            })
            .catch(err => Swal.fire('Error', 'Gagal menghubungi server', 'error'));
        };

        window.openAddCostModal = function(id) {
            document.getElementById('cost_id_reservasi').value = id;
            document.getElementById('input_cost_nominal').value = '';
            document.getElementById('input_cost_catatan').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalAddCost'));
            modal.show();
        };

        window.submitAddCost = function() {
            const id = document.getElementById('cost_id_reservasi').value;
            const nominal = document.getElementById('input_cost_nominal').value;
            const catatan = document.getElementById('input_cost_catatan').value;
            
            if (!nominal || nominal <= 0) {
                Swal.fire('Warning', 'Masukkan nominal tambahan yang valid', 'warning');
                return;
            }
            
            fetch('{{ route("reservasi.add_cost") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    id_reservasi: id, 
                    nominal: nominal,
                    catatan: catatan
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Berhasil', data.message + '\nTotal Baru: Rp ' + new Intl.NumberFormat('id-ID').format(data.total_baru), 'success');
                    bootstrap.Modal.getInstance(document.getElementById('modalAddCost')).hide();
                    refreshJadwalGlobal();
                } else {
                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                }
            })
            .catch(err => Swal.fire('Error', 'Gagal menghubungi server', 'error'));
        };

        function refreshJadwalGlobal() {
            const selectedEl = document.querySelector('.calendar-days .selected');
            if(selectedEl) {
                selectedEl.click();
            } else {
                // If no date selected, maybe fetch today's date? 
                // Alternatively, just do nothing or reload
                location.reload(); 
            }
        }
    });
    </script>
@endpush
