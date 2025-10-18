@extends('layouts.app')

@section('content')
    <h1>Tambah Slot Jadwal</h1>

    <form action="{{ route('slot-jadwal.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="id_layanan" class="form-label">Layanan</label>
            <select class="form-select" id="id_layanan" name="id_layanan" required>
                @foreach($layanan as $l)
                    <option value="{{ $l->id_layanan }}">{{ $l->nama_layanan }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="waktu" class="form-label">Waktu</label>
            <input type="time" class="form-control" id="waktu" name="waktu" required>
        </div>

        <div class="mb-3">
            <label for="status_slot" class="form-label">Status Slot</label>
            <select class="form-select" id="status_slot" name="status_slot" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="is_default" class="form-label">Is Default</label>
            <select class="form-select" id="is_default" name="is_default" required>
                <option value="1">Ya</option>
                <option value="0">Tidak</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
@endsection
