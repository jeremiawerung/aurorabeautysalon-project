@extends('layouts.app')

@section('title', 'Tambah Kategori Layanan - Aurora')

@section('content')
<style>
    /* Style dasar dari index.blade.php */
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

    /* Style khusus untuk Form Create/Edit */
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
    .form-control[disabled] {
        background-color: #f8f9fa;
        border-color: #f1b8d6;
    }

    /* Style Tombol Simpan (Pink) */
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

    /* Style Tombol Batal/Hapus (Outline) */
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


    /* Style Status Toggle Button */
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
        /* [DIPERBAIKI] Tanda # ditambahkan kembali di sini */
        background-color: #198754;
        color: #fff;
        border-color: #198754;
    }
    .status-toggle-group input[type="radio"]#status_nonaktif:checked + .status-btn {
        background-color: #6c757d;
        color: #fff;
        border-color: #6c757d;
    }

    /* =================================== */
    /* Style untuk Image Preview Sederhana */
    /* =================================== */
    .img-preview-simple {
        display: none; /* Sembunyi secara default */
        width: 50%; /* [TETAP] Menjadi width 50% (mengisi kolom) */
        aspect-ratio: 1 / 1; /* <-- Tambah baris ini untuk KOTAK */
        margin-top: 1rem; /* Jarak dari input file */
        border-radius: 0.375rem; /* Samakan radiusnya */
        border: 1px solid #f1b8d6; /* Tetap ada border pink tipis */
        object-fit: cover; /* [PENTING] Ini yang 'memotong' gambarnya */
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">

        {{-- ============================================= --}}
        {{-- FORM TAMBAH KATEGORI --}}
        {{-- ============================================= --}}

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

        <form action="{{ route('kategori-layanan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card mb-4">
                {{-- CARD HEADER --}}
                <div class="card-header d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center">
                    <h5 class="mb-2 mb-md-0">Form Kategori Layanan</h5>
                </div>

                {{-- CARD BODY --}}
                <div class="card-body">

                    {{-- BARIS 1: Nama, Status --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-9">
                            <label for="nama" class="form-label">Nama Kategori</label>
                            <input type="text" id="nama" name="nama" class="form-control" placeholder="Contoh: Hair Treatment" value="{{ old('nama') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-block">Status</label>
                            <div class="status-toggle-group">
                                <input type="radio" name="status" id="status_aktif" value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'checked' : '' }}>
                                <label for="status_aktif" class="status-btn">Aktif</label>
                                <input type="radio" name="status" id="status_nonaktif" value="non-aktif" {{ old('status') == 'non-aktif' ? 'checked' : '' }}>
                                <label for="status_nonaktif" class="status-btn">Non-Aktif</label>
                            </div>
                        </div>
                    </div>

                    {{-- BARIS 2: Upload Gambar & Keterangan (Sesuai kode Anda) --}}
                    <div class="row g-3 mb-3">
                        {{-- Input Upload & Preview (Gabungan) --}}
                        <div class="col-md-6">
                            <label for="gambar" class="form-label">Gambar Kategori <small class="text-muted">(Opsional)</small></label>
                            <input type="file" id="gambar" name="gambar" class="form-control" accept="image/png, image/jpeg, image/jpg">
                            <small class="form-text text-muted">Maks. 2MB. Format: JPG, PNG.</small>

                            @error('gambar')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            {{-- Preview Sederhana (HTML tidak berubah) --}}
                            <img id="img-preview" class="img-preview-simple" src="" alt="Preview Gambar">

                        </div>

                        <div class="col-md-6">
                            <label for="keterangan" class="form-label">Keterangan <small class="text-muted">(Opsional)</small></label>
                            <textarea id="keterangan" name="keterangan" class="form-control" rows="5" placeholder="Tulis keterangan singkat...">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>
                </div>


                {{-- CARD FOOTER --}}
                <div class="card-footer d-flex justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-simpan-pink">Simpan</button>
                        <a href="{{ route('kategori-layanan.index') }}" class="btn btn-batal-outline ms-2">Batal</a>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@section('scripts')

{{-- Script SweetAlert --}}
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


{{-- Script untuk Preview Gambar (Tidak berubah) --}}
<script>
    document.getElementById('gambar').addEventListener('change', function(event) {
        var output = document.getElementById('img-preview');

        // Pastikan ada file yang dipilih
        if (event.target.files[0]) {
            var reader = new FileReader();

            reader.onload = function(){
                output.src = reader.result;
                output.style.display = 'block'; // [PENTING] Tampilkan gambar
            };

            reader.readAsDataURL(event.target.files[0]);
        } else {
            // Jika tidak ada file (misal, dibatalkan)
            output.src = ''; // Kosongkan sumber
            output.style.display = 'none'; // [PENTING] Sembunyikan gambar lagi
        }
    });
</script>

@endsection
