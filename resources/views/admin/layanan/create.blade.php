@extends('layouts.app')

@section('content')
    <h1>Tambah Layanan</h1>

    <form action="{{ route('layanan.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nama_layanan" class="form-label">Nama Layanan</label>
            <input type="text" class="form-control" id="nama_layanan" name="nama_layanan" required>
        </div>

        <div class="mb-3">
            <label for="id_kategoriLayanan" class="form-label">Kategori Layanan</label>
            <select class="form-select" id="id_kategoriLayanan" name="id_kategoriLayanan" required>
                @foreach($kategoriLayanan as $kategori)
                    <option value="{{ $kategori->id_kategoriLayanan }}">{{ $kategori->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="harga" class="form-label">Harga</label>
            <input type="number" class="form-control" id="harga" name="harga" required>
        </div>

        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi"></textarea>
        </div>

        <div class="mb-3">
            <label for="durasi" class="form-label">Durasi (menit)</label>
            <input type="number" class="form-control" id="durasi" name="durasi" required>
        </div>

        <div class="mb-3">
            <label for="status_layanan" class="form-label">Status Layanan</label>
            <select class="form-select" id="status_layanan" name="status_layanan" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Layanan</button>
    </form>
@endsection
