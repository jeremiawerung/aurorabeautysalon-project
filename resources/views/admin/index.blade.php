@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Daftar Admin</h1>

        <!-- Menampilkan pesan sukses atau error -->
        @if(session('success'))
            <div class="alert alert-success mt-3">
                {{ session('success') }}
            </div>
        @endif

        <!-- Tombol untuk registrasi admin baru -->
        <a href="{{ route('admin.register') }}" class="btn btn-primary mb-3">Tambah Admin Baru</a>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($admins as $admin)
                    <tr>
                        <td>{{ $admin->id_admin }}</td>
                        <td>{{ $admin->nama }}</td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->status_admin }}</td>
                        <td>
                            <!-- Tombol Edit dan Delete -->
                            <a href="{{ route('admin.edit', $admin->id_admin) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('admin.destroy', $admin->id_admin) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
