@extends('layouts.app')

@section('content')
    <h1>Daftar Kategori Layanan</h1>

    <!-- Menampilkan Pesan Sukses jika ada -->
    @if (session('message'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: '{{ session('message') }}'
            });
        </script>
    @endif

    <a href="{{ route('kategori-layanan.create') }}" class="btn btn-primary mb-3">Tambah Kategori Layanan</a>

    <table class="table">
        <thead>
            <tr>
                <th>Nama Kategori</th>
                <th>Jumlah Layanan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kategoriLayanan as $kategori)
                <tr>
                    <td>{{ $kategori->nama }}</td>
                    <td>{{ $kategori->layanan_count }}</td> <!-- Menampilkan jumlah layanan dalam kategori -->
                    <td>
                        <a href="{{ route('kategori-layanan.edit', $kategori->id_kategoriLayanan) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('kategori-layanan.destroy', $kategori->id_kategoriLayanan) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori layanan ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
