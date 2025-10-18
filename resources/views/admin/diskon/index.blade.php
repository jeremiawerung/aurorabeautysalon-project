@extends('layouts.app')

@section('content')
    <h1>Daftar Diskon</h1>

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

    <a href="{{ route('diskon.create') }}" class="btn btn-primary mb-3">Tambah Diskon</a>

    <table class="table">
        <thead>
            <tr>
                <th>Nama Diskon</th>
                <th>Persentase Diskon</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Berakhir</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($diskons as $diskon)
            <tr>
                <td>{{ $diskon->nama_diskon }}</td>
                <td>{{ $diskon->persentase_diskon }}%</td>
                <td>{{ $diskon->tanggal_mulai }}</td>
                <td>{{ $diskon->tanggal_berakhir }}</td>
                <td>{{ ucfirst($diskon->status) }}</td>
                <td>
                    <!-- Pastikan menggunakan $diskon->id -->
                    <a href="{{ route('diskon.edit', $diskon->id) }}" class="btn btn-warning">Edit</a>
                    <form action="{{ route('diskon.destroy', $diskon->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus diskon ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Menambahkan SweetAlert2 -->
@endpush
