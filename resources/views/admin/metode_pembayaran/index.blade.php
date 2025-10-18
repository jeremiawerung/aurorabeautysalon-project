@extends('layouts.app')

@section('content')
    <h1>Daftar Metode Pembayaran</h1>

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

    <a href="{{ route('metode-pembayaran.create') }}" class="btn btn-primary mb-3">Tambah Metode Pembayaran</a>

    <table class="table">
        <thead>
            <tr>
                <th>Nama Metode Pembayaran</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($metodePembayaran as $metode)
                <tr>
                    <td>{{ $metode->nama }}</td>
                    <td>{{ $metode->status }}</td>
                    <td>{{ $metode->keterangan }}</td>
                    <td>
                        <a href="{{ route('metode-pembayaran.edit', $metode->id_metodePembayaran) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('metode-pembayaran.destroy', $metode->id_metodePembayaran) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus metode pembayaran ini?')">Hapus</button>
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
