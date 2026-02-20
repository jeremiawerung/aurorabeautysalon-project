@extends('layouts.app')

@section('title', 'Edit Admin - Aurora')

@section('content')
<style>
    .card {
        border: 1px solid #f1b8d6 !important;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f1b8d6 !important;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
    .card-footer {
        background-color: #fff;
        border-top: 1px solid #f1b8d6 !important;
        padding: 1rem 1.25rem;
    }
    .content-wrapper { padding: 20px; }

    .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
    }
    .form-control, .form-select {
        border: 1px solid #f1b8d6;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #e673ae;
        box-shadow: 0 0 0 0.25rem rgba(241, 184, 214, 0.5);
    }

    .btn-simpan-pink {
        background-color: #e91e63;
        border-color: #e91e63;
        color: #fff;
        font-weight: 600;
        border-radius: 0.375rem;
        padding: 0.5rem 1.25rem;
    }
    .btn-simpan-pink:hover {
        background-color: #d81b60;
        border-color: #d81b60;
    }
    .btn-hapus-outline {
        background-color: #fff;
        border: 1px solid #6c757d;
        color: #6c757d;
        font-weight: 600;
        border-radius: 0.375rem;
        padding: 0.5rem 1.25rem;
    }
    .btn-hapus-outline:hover {
        background-color: #6c757d;
        color: #fff;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Admin</h5>
                <a href="{{ route('admin.index') }}" class="text-decoration-none text-dark">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <form action="{{ route('admin.update', $admin->id_admin) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-12">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text"
                                   name="nama"
                                   id="nama"
                                   class="form-control"
                                   value="{{ old('nama', $admin->nama) }}"
                                   required>
                        </div>

                        <div class="col-md-6 col-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   class="form-control"
                                   value="{{ old('email', $admin->email) }}"
                                   required>
                        </div>

                        <div class="col-md-6 col-12">
                            <label for="password" class="form-label">
                                Password
                                <small class="text-muted">(kosongkan jika tidak diubah)</small>
                            </label>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control">
                        </div>

                        <div class="col-md-6 col-12">
                            <label for="password_confirmation" class="form-label">
                                Konfirmasi Password
                            </label>
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   class="form-control">
                        </div>

                        <div class="col-md-6 col-12">
                            <label for="status_admin" class="form-label">Status Admin</label>
                            <select name="status_admin" id="status_admin" class="form-select">
                                <option value="aktif" {{ old('status_admin', $admin->status_admin) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="non-aktif" {{ old('status_admin', $admin->status_admin) == 'non-aktif' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger mt-3 mb-0">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success mt-3 mb-0">
                            {{ session('success') }}
                        </div>
                    @endif
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-simpan-pink">
                            Simpan
                        </button>
                        <a href="{{ route('admin.index') }}" class="btn btn-hapus-outline ms-2">
                            Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
