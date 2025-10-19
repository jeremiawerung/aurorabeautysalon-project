@extends('layouts.pelanggan')

@section('content')
    <div class="container">
        <div class="mb-4">
            <h2>Dashboard Pelanggan</h2>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Selamat Datang, {{ Auth::user()->name }}!</h5>
                        <p class="card-text">Anda masuk sebagai pelanggan di Aurora Beauty Salon.</p>
                        <p class="text-muted">Email: {{ Auth::user()->email }}</p>
                        <div class="mt-3">
                            <a href="#" class="btn btn-primary">Buat Reservasi</a>
                            <a href="#" class="btn btn-secondary">Lihat Riwayat</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Reservasi Mendatang</h5>
                        <p class="card-text">Tidak ada reservasi mendatang.</p>
                        <small class="text-muted">Silakan buat reservasi baru untuk layanan kecantikan.</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Layanan Favorit</h5>
                        <p class="card-text">Belum ada layanan favorit.</p>
                        <small class="text-muted">Layanan yang sering Anda gunakan akan muncul di sini.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Informasi Kontak</h5>
                        <p class="card-text">Hubungi kami untuk pertanyaan atau bantuan:</p>
                        <ul class="list-unstyled">
                            <li><strong>Telepon:</strong> (021) 1234-5678</li>
                            <li><strong>WhatsApp:</strong> 0812-3456-7890</li>
                            <li><strong>Alamat:</strong> Jl. Kecantikan No. 123, Jakarta</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection