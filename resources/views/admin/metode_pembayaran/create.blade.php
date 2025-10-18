@extends('layouts.app')

@section('content')
    <h1>Tambah Metode Pembayaran</h1>

    <form action="{{ route('metode-pembayaran.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nama" class="form-label">Nama Metode Pembayaran</label>
            <input type="text" class="form-control" id="nama" name="nama" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Metode Pembayaran</button>
    </form>
@endsection
