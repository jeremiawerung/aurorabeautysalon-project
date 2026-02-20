@extends('layouts.app')

@section('title', 'Tambah Layanan - Aurora')

@section('content')
<style>
    /* ... (Semua style CSS Anda tetap sama) ... */
    /* Style dasar (card, content) */
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

    /* Style khusus untuk Form (dari kategori/create.blade.php) */
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

    /* Style untuk Input Group (Rp. dan Menit) */
    .input-group-text {
        border: 1px solid #f1b8d6;
        background-color: #f8f9fa;
        font-size: 0.875rem;
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

    /* Style Status Toggle Button (Dilebarkan) */
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
        background-color: #198754; /* Hijau 'Aktif' */
        color: #fff;
        border-color: #198754;
    }
    .status-toggle-group input[type="radio"]#status_nonaktif:checked + .status-btn {
        background-color: #6c757d; /* Abu-abu 'Non-Aktif' */
        color: #fff;
        border-color: #6c757d;
    }

    /* =================================== */
    /* [PENAMBAHAN] Style untuk Image Preview Sederhana */
    /* =================================== */
    .img-preview-simple {
        display: none; /* Sembunyi secara default */
        width: 50%; /* Lebar 50% dari kolom */
        aspect-ratio: 1 / 1; /* KOTAK */
        margin-top: 1rem; /* Jarak dari input file */
        border-radius: 0.375rem; /* Samakan radiusnya */
        border: 1px solid #f1b8d6; /* Tetap ada border pink tipis */
        object-fit: cover; /* [PENTING] Ini yang 'memotong' gambarnya */
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">

        {{-- ============================================= --}}
        {{-- FORM TAMBAH LAYANAN --}}
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

        {{-- [PERUBAHAN] Tambahkan enctype untuk upload file --}}
        <form action="{{ route('layanan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card mb-4">
                {{-- CARD HEADER --}}
                <div class="card-header d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center">
                    <h5 class="mb-2 mb-md-0">Form Layanan</h5>
                </div>

                {{-- CARD BODY --}}
                <div class="card-body">

                    {{-- BARIS 1: Nama, Status --}}
                    <div class="row g-3 mb-3">
                        {{-- Nama Layanan --}}
                        <div class="col-md-9">
                            <label for="nama_layanan" class="form-label">Nama Layanan</label>
                            <input type="text" id="nama_layanan" name="nama_layanan" class="form-control" placeholder="Contoh: Gunting Rambut" value="{{ old('nama_layanan') }}" required>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-3">
                            <label class="form-label d-block">Status</label>
                            <div class="status-toggle-group">
                                <input type="radio" name="status_layanan" id="status_aktif" value="aktif" {{ old('status_layanan', 'aktif') == 'aktif' ? 'checked' : '' }}>
                                <label for="status_aktif" class="status-btn">Aktif</label>
                                <input type="radio" name="status_layanan" id="status_nonaktif" value="non-aktif" {{ old('status_layanan') == 'non-aktif' ? 'checked' : '' }}>
                                <label for="status_nonaktif" class="status-btn">Non-Aktif</label>
                            </div>
                        </div>
                    </div>

                    {{-- BARIS 2: Harga, Kategori, Durasi --}}
                    <div class="row g-3 mb-3">
                        {{-- Harga --}}
                        <div class="col-md-4">
                            <label for="harga_display" class="form-label">Harga</label>
                            <div class="input-group">
                                <span class="input-group-text" style="border-right: 0;">Rp.</span>
                                <input type="text"
                                       id="harga_display"
                                       class="form-control"
                                       placeholder="50.000"
                                       value="{{ old('harga') ? number_format(old('harga'), 0, ',', '.') : '' }}"
                                       required
                                       style="border-left: 0;">
                                <input type="hidden"
                                       id="harga_hidden"
                                       name="harga"
                                       value="{{ old('harga') }}">
                            </div>
                        </div>

                        {{-- Kategori --}}
                        <div class="col-md-5">
                            <label for="id_kategoriLayanan" class="form-label">Kategori</label>
                            <select id="id_kategoriLayanan" name="id_kategoriLayanan" class="form-select" required>
                                {{-- Pastikan Anda mengirim $kategoriLayanans dari Controller --}}
                                @isset($kategoriLayanans)
                                    <option value="" disabled {{ old('id_kategoriLayanan') ? '' : 'selected' }}>Pilih kategori...</option>
                                    @foreach($kategoriLayanans as $kategori)
                                        <option value="{{ $kategori->id_kategoriLayanan }}" {{ old('id_kategoriLayanan') == $kategori->id_kategoriLayanan ? 'selected' : '' }}>
                                            {{ $kategori->nama }}
                                        </option>
                                    @endforeach
                                @else
                                    <option disabled selected>Gagal memuat kategori...</option>
                                @endisset
                            </select>
                        </div>

                        {{-- Durasi --}}
                        <div class="col-md-3">
                            <label for="durasi" class="form-label">Durasi <small class="text-muted">(Per Menit)</small></label>
                            <div class="input-group">
                                <input type="number" id="durasi" name="durasi" class="form-control" placeholder="45" value="{{ old('durasi') }}" required style="border-right: 0;">
                                <span class="input-group-text" style="border-left: 0;">Menit</span>
                            </div>
                        </div>
                    </div>

                    {{-- [PERUBAHAN] BARIS 3: Deskripsi & Gambar --}}
                    <div class="row g-3">
                        {{-- Deskripsi --}}
                        <div class="col-md-6">
                            <label for="deskripsi" class="form-label">Deskripsi <small class="text-muted">(Opsional)</small></label>
                            <textarea id="deskripsi" name="deskripsi" class="form-control" rows="5" placeholder="Tulis deskripsi singkat layanan...">{{ old('deskripsi') }}</textarea>
                        </div>

                        {{-- [PENAMBAHAN] Input Gambar --}}
                        <div class="col-md-6">
                            <label for="gambar" class="form-label">Gambar Layanan <small class="text-muted">(Opsional)</small></label>
                            <input type="file" id="gambar" name="gambar" class="form-control" accept="image/png, image/jpeg, image/jpg">
                            <small class="form-text text-muted">Maks. 2MB. Format: JPG, PNG.</small>

                            @error('gambar')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            {{-- Preview Sederhana --}}
                            <img id="img-preview" class="img-preview-simple" src="" alt="Preview Gambar">
                        </div>
                    </div>
                </div>


                {{-- CARD FOOTER --}}
                <div class="card-footer d-flex justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-simpan-pink">Simpan</button>
                        <a href="{{ route('layanan.index') }}" class="btn btn-batal-outline ms-2">Batal</a>
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


{{-- Script untuk Format Rupiah & Preview Gambar --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- Script Format Rupiah ---
        const hargaDisplay = document.getElementById('harga_display');
        const hargaHidden = document.getElementById('harga_hidden');

        if(hargaDisplay && hargaHidden) {
            hargaDisplay.addEventListener('input', function(e) {
                let numericValue = e.target.value.replace(/[^0-9]/g, '');
                hargaHidden.value = numericValue;
                if (numericValue) {
                    e.target.value = new Intl.NumberFormat('id-ID').format(numericValue);
                } else {
                    e.target.value = '';
                }
            });
        }

        // --- [PENAMBAHAN] Script Preview Gambar ---
        const gambarInput = document.getElementById('gambar');
        const imgPreview = document.getElementById('img-preview');

        if (gambarInput && imgPreview) {
            gambarInput.addEventListener('change', function(event) {
                // Pastikan ada file yang dipilih
                if (event.target.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(){
                        imgPreview.src = reader.result;
                        imgPreview.style.display = 'block'; // Tampilkan gambar
                    };

                    reader.readAsDataURL(event.target.files[0]);
                } else {
                    // Jika tidak ada file (dibatalkan)
                    imgPreview.src = ''; // Kosongkan sumber
                    imgPreview.style.display = 'none'; // Sembunyikan gambar lagi
                }
            });
        }
    });
</script>

@endsection
