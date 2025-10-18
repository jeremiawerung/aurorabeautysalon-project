@extends('layouts.app')

@section('content')
    <h1>Edit Layanan</h1>

    <form action="{{ route('layanan.update', $layanan->id_layanan) }}" method="POST">
        @csrf
        @method('PUT')  <!-- Menggunakan PUT untuk update -->

        <!-- Nama Layanan -->
        <div class="mb-3">
            <label for="nama_layanan" class="form-label">Nama Layanan</label>
            <input type="text" class="form-control" id="nama_layanan" name="nama_layanan" value="{{ old('nama_layanan', $layanan->nama_layanan) }}" required>
        </div>

        <!-- Kategori Layanan -->
        <div class="mb-3">
            <label for="id_kategoriLayanan" class="form-label">Kategori Layanan</label>
            <select class="form-select" id="id_kategoriLayanan" name="id_kategoriLayanan" required>
                @foreach($kategoriLayanan as $kategori)
                    <option value="{{ $kategori->id_kategoriLayanan }}" 
                        {{ $layanan->id_kategoriLayanan == $kategori->id_kategoriLayanan ? 'selected' : '' }}>
                        {{ $kategori->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Harga Layanan -->
        <div class="mb-3">
            <label for="harga" class="form-label">Harga</label>
            <input type="number" class="form-control" id="harga" name="harga" value="{{ old('harga', $layanan->harga) }}" required>
        </div>

        <!-- Deskripsi Layanan -->
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi">{{ old('deskripsi', $layanan->deskripsi) }}</textarea>
        </div>

        <!-- Durasi Layanan -->
        <div class="mb-3">
            <label for="durasi" class="form-label">Durasi (menit)</label>
            <input type="number" class="form-control" id="durasi" name="durasi" value="{{ old('durasi', $layanan->durasi) }}" required>
        </div>

        <!-- Status Layanan -->
        <div class="mb-3">
            <label for="status_layanan" class="form-label">Status Layanan</label>
            <select class="form-select" id="status_layanan" name="status_layanan" required>
                <option value="aktif" {{ $layanan->status_layanan == 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ $layanan->status_layanan == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        <!-- Tombol Update -->
        <button type="submit" class="btn btn-primary">Update Layanan</button>
    </form>
@endsection
