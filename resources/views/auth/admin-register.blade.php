@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Registrasi Admin</h1>
        <form id="registerForm" action="{{ route('admin.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" name="nama" id="nama" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>

            <button type="button" id="submitBtn" class="btn btn-primary">Registrasi</button>
        </form>

        <!-- Menampilkan pesan sukses setelah registrasi berhasil -->
        @if(session('success'))
            <div class="alert alert-success mt-3">
                {{ session('success') }}
            </div>
        @endif

        <!-- Menampilkan pesan error jika ada kesalahan validasi -->
        @if($errors->any())
            <ul class="alert alert-danger mt-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- SweetAlert2 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Ambil tombol submit
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('registerForm');

        // Tambahkan event listener untuk tombol submit
        submitBtn.addEventListener('click', function () {
            // Tampilkan popup konfirmasi dengan SweetAlert2
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan membuat admin baru.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Lanjut',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika memilih "Lanjut", submit form
                    form.submit();
                } else {
                    // Jika memilih "Batal", tidak terjadi apa-apa
                    Swal.fire(
                        'Batal',
                        'Proses registrasi dibatalkan.',
                        'error'
                    );
                }
            });
        });
    </script>
@endsection
