@extends('layouts.app')

@section('title', 'Tambah Diskon Baru - Aplikasi')

@section('content')
<style>
    .card {
        border: 1px solid #f1b8d6 !important;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f1b8d6 !important;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
    .card-footer {
        background-color: #fff;
        border-top: 1px solid #f1b8d6 !important;
        padding: 1rem 1.25rem;
    }
    .content-wrapper { padding: 20px; }
    .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
    }
    .form-control, .form-select {
        border: 1px solid #f1b8d6;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #e673ae;
        box-shadow: 0 0 0 0.25rem rgba(241, 184, 214, 0.5);
    }
    .btn-simpan-pink {
        background-color: #e91e63;
        border-color: #e91e63;
        color: #fff;
        font-weight: 600;
        border-radius: 0.375rem;
        padding: 0.5rem 1.25rem;
    }
    .btn-simpan-pink:hover {
        background-color: #d81b60;
        border-color: #d81b60;
        color: #fff;
    }
    .btn-batal-outline {
        background-color: #fff;
        border: 1px solid #6c757d;
        color: #6c757d;
        font-weight: 600;
        border-radius: 0.375rem;
        padding: 0.5rem 1.25rem;
    }
    .btn-batal-outline:hover {
        background-color: #6c757d;
        color: #fff;
    }
    .status-toggle-group {
        display: flex;
        width: 100%;
        border-radius: 0.375rem;
        overflow: hidden;
        border: 1px solid #ced4da;
    }
    .status-toggle-group input[type="radio"] {
        display: none;
    }
    .status-toggle-group .status-btn {
        flex: 1;
        text-align: center;
        padding: 0.5rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        background-color: #f8f9fa;
        color: #6c757d;
        border-left: 1px solid #ced4da;
        transition: all 0.2s ease-in-out;
    }
    .status-toggle-group .status-btn:first-of-type {
        border-left: none;
    }
    .status-toggle-group input[type="radio"]#status_aktif:checked + .status-btn {
        background-color: #198754; 
        color: #fff;
        border-color: #198754;
    }
    .status-toggle-group input[type="radio"]#status_tidak_aktif:checked + .status-btn {
        background-color: #6c757d; 
        color: #fff;
        border-color: #6c757d;
    }
    .select2-container .select2-selection--multiple {
        border: 1px solid #f1b8d6 !important;
        min-height: 38px;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #e673ae !important;
        box-shadow: 0 0 0 0.25rem rgba(241, 184, 214, 0.5);
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Terjadi Kesalahan Validasi!</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('diskon.store') }}" method="POST">
            @csrf
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Form Tambah Diskon</h5>
                </div>

                <div class="card-body">

                    {{-- BARIS 1: Nama, Kode --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label for="nama_diskon" class="form-label">Nama Diskon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_diskon') is-invalid @enderror" id="nama_diskon" name="nama_diskon" value="{{ old('nama_diskon') }}" placeholder="Contoh: Diskon Akhir Tahun" required>
                            @error('nama_diskon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-5">
                            <label for="kode_diskon" class="form-label">Kode Diskon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kode_diskon') is-invalid @enderror" id="kode_diskon" name="kode_diskon" value="{{ old('kode_diskon') }}" placeholder="Contoh: MERDEKA20" required>
                            @error('kode_diskon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- BARIS 2: Pilih Layanan --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label for="layanan_ids" class="form-label">Terapkan Diskon ke Layanan (Opsional)</label>
                            <select 
                                id="layanan_ids" 
                                name="layanan_ids[]"
                                class="form-select @error('layanan_ids') is-invalid @enderror"
                                multiple>
                                
                                @foreach($layanans as $layanan)
                                    <option value="{{ $layanan->id_layanan }}"
                                        {{ (is_array(old('layanan_ids')) && in_array($layanan->id_layanan, old('layanan_ids'))) ? 'selected' : '' }}
                                    >
                                        {{ $layanan->nama_layanan }} (Rp {{ number_format($layanan->harga, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Pilih layanan yang akan mendapatkan diskon ini. Biarkan kosong untuk tidak menerapkan ke layanan manapun.
                            </small>
                            @error('layanan_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('layanan_ids.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- BARIS 3: Persentase, Tanggal Mulai, Status --}}
                    <div class="row g-3 mb-3">
                        {{-- Persentase Diskon --}}
                        <div class="col-md-4">
                            <label for="persentase_diskon" class="form-label">Persentase Diskon (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('persentase_diskon') is-invalid @enderror" id="persentase_diskon" name="persentase_diskon" min="0" max="100" step="0.01" value="{{ old('persentase_diskon') }}" required style="border-right: 0;">
                                <span class="input-group-text" style="border-left: 0;">%</span>
                            </div>
                            @error('persentase_diskon')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tanggal Mulai --}}
                        <div class="col-md-4">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                            @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Status Diskon/Voucher --}}
                        <div class="col-md-4">
                            <label class="form-label d-block">Status Diskon <span class="text-danger">*</span></label>
                            <div class="status-toggle-group">
                                <input type="radio" name="status_diskon" id="status_aktif" value="aktif" {{ old('status_diskon', 'aktif') == 'aktif' ? 'checked' : '' }}>
                                <label for="status_aktif" class="status-btn">Aktif</label>
                                <input type="radio" name="status_diskon" id="status_tidak_aktif" value="tidak aktif" {{ old('status_diskon') == 'tidak aktif' ? 'checked' : '' }}>
                                <label for="status_tidak_aktif" class="status-btn">Tidak Aktif</label>
                            </div>
                            @error('status_diskon')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- BARIS 4: Tanggal Berakhir & Keterangan --}}
                    <div class="row g-3">
                         {{-- Tanggal Berakhir --}}
                        <div class="col-md-6">
                            <label for="tanggal_berakhir" class="form-label">Tanggal Berakhir (Opsional)</label>
                            <input type="date" class="form-control @error('tanggal_berakhir') is-invalid @enderror" id="tanggal_berakhir" name="tanggal_berakhir" value="{{ old('tanggal_berakhir') }}">
                            @error('tanggal_berakhir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Keterangan --}}
                        <div class="col-md-6">
                            <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="3" placeholder="Penjelasan singkat...">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-start">
                    <button type="submit" class="btn btn-simpan-pink">Simpan Diskon</button>
                    <a href="{{ route('diskon.index') }}" class="btn btn-batal-outline ms-2">Batal</a>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
@endif
@if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}'
        });
    </script>
@endif

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#layanan_ids').select2({
            placeholder: "Pilih layanan...",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection