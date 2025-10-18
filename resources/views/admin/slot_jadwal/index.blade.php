@extends('layouts.app')

@section('content')
    <h1>Daftar Slot Jadwal</h1>

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

    <a href="{{ route('slot-jadwal.create') }}" class="btn btn-primary mb-3">Tambah Slot Jadwal</a>

    <table class="table">
        <thead>
            <tr>
                <th>Nama Layanan</th>
                <th>Waktu</th>
                <th>Status Slot</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($slotJadwal as $slot)
                <tr>
                    <td>{{ $slot->layanan->nama_layanan }}</td>
                    <td>{{ $slot->waktu }}</td>
                    <td>{{ ucfirst($slot->status_slot) }}</td>
                    <td>
                        <a href="{{ route('slot-jadwal.edit', $slot->id_slot) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('slot-jadwal.destroy', $slot->id_slot) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus slot jadwal ini?')">Hapus</button>
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
