@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Edit Admin</h1>

        <form action="{{ route('admin.update', $admin->id_admin) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" name="nama" id="nama" class="form-control" value="{{ old('nama', $admin->nama) }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password (Kosongkan jika tidak ingin diubah)</label>
                <input type="password" name="password" id="password" class="form-control">
            </div>

            <div class="mb-3">
                <label for="status_admin" class="form-label">Status Admin</label>
                <select name="status_admin" id="status_admin" class="form-control">
                    <option value="aktif" {{ old('status_admin', $admin->status_admin) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="non-aktif" {{ old('status_admin', $admin->status_admin) == 'non-aktif' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Update Admin</button>
        </form>
    </div>
@endsection
