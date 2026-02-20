@extends('layouts.app')

@section('title', 'Edit Metode Pembayaran - Aurora')

@section('content')
<style>
    /* Style card dan form (sama seperti halaman tambah) */
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
    .input-group-text {
        border: 1px solid #f1b8d6;
        background-color: #f8f9fa;
        font-size: 0.875rem;
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
    .status-toggle-group input[type="radio"]:checked + .status-btn {
        background-color: #198754;
        color: #fff;
        border-color: #198754;
    }
    .status-toggle-group input[type="radio"]#status_nonaktif:checked + .status-btn {
        background-color: #6c757d;
        color: #fff;
        border-color: #6c757d;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">

        {{-- ALERT ERROR --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Terjadi Kesalahan!</h4>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORM EDIT METODE PEMBAYARAN --}}
        <form action="{{ route('metode-pembayaran.update', $metodePembayaran->id_metodePembayaran) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card mb-4">
                <div class="card-header d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center">
                    <h5 class="mb-2 mb-md-0">Form Edit Metode Pembayaran</h5>
                </div>

                <div class="card-body">
                    <div class="row g-3 mb-3">
                        {{-- Nama Metode Pembayaran --}}
                        <div class="col-md-9">
                            <label for="nama" class="form-label">Nama Metode Pembayaran</label>
                            <input type="text" id="nama" name="nama" class="form-control"
                                   placeholder="Contoh: Tunai, Transfer Bank, QRIS..."
                                   value="{{ old('nama', $metodePembayaran->nama) }}" required>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-3">
                            <label class="form-label d-block">Status</label>
                            <div class="status-toggle-group">
                                <input type="radio" name="status" id="status_aktif" value="aktif"
                                       {{ old('status', $metodePembayaran->status) == 'aktif' ? 'checked' : '' }}>
                                <label for="status_aktif" class="status-btn">Aktif</label>

                                <input type="radio" name="status" id="status_nonaktif" value="non-aktif"
                                       {{ old('status', $metodePembayaran->status) == 'non-aktif' ? 'checked' : '' }}>
                                <label for="status_nonaktif" class="status-btn">Non-Aktif</label>
                            </div>
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="keterangan" class="form-label">Keterangan <small class="text-muted">(Opsional)</small></label>
                            <textarea id="keterangan" name="keterangan" class="form-control" rows="5"
                                      placeholder="Tulis keterangan singkat (misal: hanya untuk pembayaran di kasir)...">{{ old('keterangan', $metodePembayaran->keterangan) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-simpan-pink">Perbarui</button>
                        <a href="{{ route('metode-pembayaran.index') }}" class="btn btn-batal-outline ms-2">Batal</a>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@section('scripts')
{{-- SweetAlert feedback --}}
@if (session('message'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: '{{ session('message') }}',
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
@endsection
