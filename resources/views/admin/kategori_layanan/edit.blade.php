@extends('layouts.app')

@section('content')
    <h1>Edit Kategori Layanan</h1>

    <form action="{{ route('kategori-layanan.update', $kategoriLayanan->id_kategoriLayanan) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nama" class="form-label">Nama Kategori</label>
            <input type="text" class="form-control" id="nama" name="nama" value="{{ $kategoriLayanan->nama }}" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="aktif" {{ $kategoriLayanan->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ $kategoriLayanan->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan">{{ $kategoriLayanan->keterangan }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
@endsection
