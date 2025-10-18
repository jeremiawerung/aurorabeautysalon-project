@extends('layouts.app')

@section('content')
    <h1>Edit Diskon</h1>

    <form action="{{ route('diskon.update', $diskon->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nama_diskon" class="form-label">Nama Diskon</label>
            <input type="text" class="form-control" id="nama_diskon" name="nama_diskon" value="{{ $diskon->nama_diskon }}" required>
        </div>

        <div class="mb-3">
            <label for="persentase_diskon" class="form-label">Persentase Diskon</label>
            <input type="number" class="form-control" id="persentase_diskon" name="persentase_diskon" value="{{ $diskon->persentase_diskon }}" min="0" max="100" required>
        </div>

        <div class="mb-3">
            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
            <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="{{ $diskon->tanggal_mulai }}" required>
        </div>

        <div class="mb-3">
            <label for="tanggal_berakhir" class="form-label">Tanggal Berakhir</label>
            <input type="date" class="form-control" id="tanggal_berakhir" name="tanggal_berakhir" value="{{ $diskon->tanggal_berakhir }}" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="aktif" {{ $diskon->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ $diskon->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan">{{ $diskon->keterangan }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Diskon</button>
    </form>
@endsection
