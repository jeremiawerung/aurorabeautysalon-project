@extends('layouts.app')

@section('content')
    <h1>Edit Slot Jadwal</h1>

    <form action="{{ route('slot-jadwal.update', $slotJadwal->id_slot) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="id_layanan" class="form-label">Layanan</label>
            <select class="form-select" id="id_layanan" name="id_layanan" required>
                @foreach($layanan as $l)
                    <option value="{{ $l->id_layanan }}" {{ $slotJadwal->id_layanan == $l->id_layanan ? 'selected' : '' }}>{{ $l->nama_layanan }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="waktu" class="form-label">Waktu</label>
            <input type="time" class="form-control" id="waktu" name="waktu" value="{{ $slotJadwal->waktu }}" required>
        </div>

        <div class="mb-3">
            <label for="status_slot" class="form-label">Status Slot</label>
            <select class="form-select" id="status_slot" name="status_slot" required>
                <option value="aktif" {{ $slotJadwal->status_slot == 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ $slotJadwal->status_slot == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="is_default" class="form-label">Is Default</label>
            <select class="form-select" id="is_default" name="is_default" required>
                <option value="1" {{ $slotJadwal->is_default == 1 ? 'selected' : '' }}>Ya</option>
                <option value="0" {{ $slotJadwal->is_default == 0 ? 'selected' : '' }}>Tidak</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
@endsection
