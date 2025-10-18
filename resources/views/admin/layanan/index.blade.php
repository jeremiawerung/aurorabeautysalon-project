@extends('layouts.app')

@section('content')
    <h1>Daftar Layanan</h1>

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

    <a href="{{ route('layanan.create') }}" class="btn btn-primary mb-3">Tambah Layanan</a>

    <table class="table">
        <thead>
            <tr>
                <th>Nama Layanan</th>
                <th>Kategori Layanan</th>
                <th>Harga</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($layanan as $layananItem)
                <tr>
                    <td>{{ $layananItem->nama_layanan }}</td>
                    <td>{{ $layananItem->kategoriLayanan->nama }}</td>
                    <td>{{ $layananItem->harga }}</td>
                    <td>{{ $layananItem->durasi }} menit</td>
                    <td>{{ ucfirst($layananItem->status_layanan) }}</td>
                    <td>
                        <a href="{{ route('layanan.edit', $layananItem->id_layanan) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('layanan.destroy', $layananItem->id_layanan) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus layanan ini?')">Hapus</button>
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
