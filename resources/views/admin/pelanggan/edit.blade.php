@extends('layouts.app')

@section('title', 'Edit Pelanggan - Aurora')

@section('content')
<style>
    /* CARD & WRAPPER */
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
    .content-wrapper { padding: 20px; }

    /* LABEL & INPUT */
    .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
    }
    .form-control {
        border: 1px solid #f1b8d6;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }
    .form-control:focus {
        border-color: #e673ae;
        box-shadow: 0 0 0 0.25rem rgba(241,184,214,0.5);
    }

    /* TOMBOL */
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

    /* STATUS TOGGLE */
    .status-toggle-group {
        display: flex;
        border-radius: 0.375rem;
        overflow: hidden;
        border: 1px solid #ced4da;
    }
    .status-toggle-group input[type="radio"] {
        display: none;
    }
    .status-btn {
        flex: 1;
        text-align: center;
        padding: 0.5rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        background-color: #f8f9fa;
        color: #6c757d;
        border-left: 1px solid #ced4da;
        transition: all 0.25s ease;
    }
    .status-btn:first-of-type {
        border-left: none;
    }
    .status-btn.active-aktif {
        background-color: #198754 !important;
        color: #fff !important;
        border-color: #198754 !important;
    }
    .status-btn.active-nonaktif {
        background-color: #6c757d !important;
        color: #fff !important;
        border-color: #6c757d !important;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">

        {{-- PESAN ERROR --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi Kesalahan!</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORM --}}
        <form action="{{ route('pelanggan.update', $pelanggan->id_pelanggan) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Data Pelanggan</h5>
                </div>

                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nama" class="form-label">Nama Pelanggan</label>
                            <input type="text" name="nama" id="nama" class="form-control"
                                value="{{ old('nama', $pelanggan->nama) }}" placeholder="Nama pelanggan..." required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-block">Status</label>
                            <div class="status-toggle-group">
                                <input type="radio" name="status" id="status_aktif" value="aktif"
                                    {{ old('status', $pelanggan->status_pelanggan) == 'aktif' ? 'checked' : '' }}>
                                <label for="status_aktif" class="status-btn" id="btnAktif">Aktif</label>

                                <input type="radio" name="status" id="status_nonaktif" value="non-aktif"
                                    {{ old('status', $pelanggan->status_pelanggan) == 'non-aktif' ? 'checked' : '' }}>
                                <label for="status_nonaktif" class="status-btn" id="btnNonaktif">Non-Aktif</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="telepon" class="form-label">Nomor Telepon</label>
                            <input type="tel" name="telepon" id="telepon" class="form-control"
                                value="{{ old('telepon', $pelanggan->nomor_telepon) }}" placeholder="+62 8000-0000-000" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                value="{{ old('email', $pelanggan->email) }}" placeholder="contoh@email.com" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password Baru (opsional)</label>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Isi jika ingin mengganti password">
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-simpan-pink">Update</button>
                        <a href="{{ route('pelanggan.data_pelanggan') }}" class="btn btn-batal-outline ms-2">Kembali</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // fungsi untuk update warna status
    function updateStatusColor() {
        const aktif = document.getElementById('status_aktif');
        const nonaktif = document.getElementById('status_nonaktif');
        const btnAktif = document.getElementById('btnAktif');
        const btnNonaktif = document.getElementById('btnNonaktif');

        btnAktif.classList.remove('active-aktif', 'active-nonaktif');
        btnNonaktif.classList.remove('active-aktif', 'active-nonaktif');

        if (aktif.checked) {
            btnAktif.classList.add('active-aktif');
        } else if (nonaktif.checked) {
            btnNonaktif.classList.add('active-nonaktif');
        }
    }

    // jalankan saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        updateStatusColor();

        document.querySelectorAll('input[name="status"]').forEach(radio => {
            radio.addEventListener('change', updateStatusColor);
        });
    });
</script>

@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Sukses!',
        text: '{{ session('success') }}',
        timer: 2500,
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
