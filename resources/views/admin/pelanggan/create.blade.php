@extends('layouts.app')

@section('content')
    <h1>Tambah Pelanggan Baru</h1>

    <!-- Menampilkan Pesan Error jika ada -->
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ $errors->first() }}'  // Menampilkan error pertama yang ditemukan
            });
        </script>
    @endif

    <!-- Form Tambah Pelanggan -->
    <form action="{{ route('pelanggan.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama') }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="mb-3">
            <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
            <input type="text" class="form-control" id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon') }}" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3">
            <label for="status_pelanggan" class="form-label">Status Pelanggan</label>
            <select class="form-select" id="status_pelanggan" name="status_pelanggan" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Pelanggan</button>
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Menambahkan SweetAlert2 -->
@endpush

