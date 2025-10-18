@extends('layouts.app')

@section('content')
    <h1>Daftar Pelanggan</h1>

    <!-- Link ke Form Tambah Pelanggan -->
    <a href="{{ route('pelanggan.create') }}" class="btn btn-primary mb-3">Tambah Pelanggan</a>

    <!-- Form Pencarian -->
    <form method="GET" action="{{ route('pelanggan.index') }}">
        <input type="text" name="search" placeholder="Cari pelanggan..." value="{{ request()->search }}">
        <button type="submit">Cari</button>
    </form>

    <!-- Menampilkan Pesan Konfirmasi dengan SweetAlert -->
    @if(session('message'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: '{{ session('message') }}'
            });
        </script>
    @endif

    <!-- Daftar Pelanggan -->
    <table class="table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pelanggan as $p)
                <tr>
                    <td>{{ $p->nama }}</td>
                    <td>{{ $p->email }}</td>
                    <td>{{ $p->nomor_telepon }}</td>
                    <td>
                        <a href="{{ route('pelanggan.edit', $p->id_pelanggan) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('pelanggan.destroy', $p->id_pelanggan) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    {{ $pelanggan->links() }}
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Menambahkan SweetAlert2 -->
@endpush
