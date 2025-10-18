<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KategoriLayananController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\MetodepembayaranController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ReservasiController;
use App\Http\Controllers\Pilihan_layananController;
use App\Http\Controllers\Reservasi_layananController;
use App\Http\Controllers\Reservasi_slot_jadwalController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\SlotJadwalController;
use App\Http\Controllers\DiskonController;
use App\Http\Controllers\PelangganAuthController;
use Laravel\Fortify\Http\Controllers\VerifiedController;

use App\Http\Controllers\Controller;

// Rute login admin
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login']);
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// Rute registrasi admin
Route::get('/admin/register', [AdminController::class, 'showRegistrationForm'])->name('admin.register');
Route::post('/admin/register', [AdminController::class, 'store'])->name('admin.store');

// Rute update password admin
Route::get('/admin/password/edit', [AdminController::class, 'editPasswordForm'])->name('admin.password.edit');
Route::put('/admin/password', [AdminController::class, 'updatePassword'])->name('admin.password.update');


// Route untuk verifikasi email
Route::get('email/verify', [VerifiedController::class, 'show'])->name('verification.notice');
Route::get('email/verify/{id}/{hash}', [VerifiedController::class, 'verify'])->name('verification.verify');
Route::post('email/resend', [VerifiedController::class, 'resend'])->name('verification.resend');

// Rute dashboard admin (hanya bisa diakses oleh admin)
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');  // Ganti dengan halaman dashboard yang sesuai
})->middleware('auth:admin');

// Route untuk admin CRUD data admin
Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
Route::get('/admin/edit/{id}', [AdminController::class, 'edit'])->name('admin.edit');  // Menampilkan form edit admin
Route::put('/admin/update/{id}', [AdminController::class, 'update'])->name('admin.update');  // Mengupdate data admin
Route::delete('/admin/delete/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');  // Menghapus admin

// Routing resource untuk Pelanggan
Route::middleware(['auth:admin'])->group(function () {
    Route::resource('/pelanggan', PelangganController::class);  // Menambahkan middleware untuk admin
    Route::get('/pelanggan/create', [PelangganController::class, 'create'])->name('pelanggan.create'); // Routing untuk form tambah pelanggan
    Route::post('/pelanggan', [PelangganController::class, 'store'])->name('pelanggan.store'); // Routing untuk menyimpan pelanggan baru
});

// Routing resource untuk Kategori Layanan
Route::resource('/kategori-layanan', KategoriLayananController::class); // CRUD Kategori Layanan

// Route untuk menampilkan daftar layanan
Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');
Route::get('/layanan/create', [LayananController::class, 'create'])->name('layanan.create'); // Route untuk menampilkan form tambah layanan
Route::post('/layanan', [LayananController::class, 'store'])->name('layanan.store'); // Route untuk menyimpan layanan baru
Route::get('/layanan/{id_layanan}/edit', [LayananController::class, 'edit'])->name('layanan.edit'); // Route untuk menampilkan form edit layanan
Route::put('/layanan/{id_layanan}', [LayananController::class, 'update'])->name('layanan.update'); // Route untuk mengupdate layanan
Route::delete('/layanan/{id_layanan}', [LayananController::class, 'destroy'])->name('layanan.destroy'); // Route untuk menghapus layanan

// Route untuk slot jadwal
Route::resource('/slot-jadwal', SlotJadwalController::class); // CRUD untuk slot jadwal

// Route untuk metode pembayaran
Route::get('/metode-pembayaran', [MetodePembayaranController::class, 'index'])->name('metode-pembayaran.index');
Route::get('/metode-pembayaran/create', [MetodePembayaranController::class, 'create'])->name('metode-pembayaran.create');
Route::post('/metode-pembayaran', [MetodePembayaranController::class, 'store'])->name('metode-pembayaran.store');
Route::get('/metode-pembayaran/{id}/edit', [MetodePembayaranController::class, 'edit'])->name('metode-pembayaran.edit');
Route::put('/metode-pembayaran/{id}', [MetodePembayaranController::class, 'update'])->name('metode-pembayaran.update');
Route::delete('/metode-pembayaran/{id}', [MetodePembayaranController::class, 'destroy'])->name('metode-pembayaran.destroy');

// Route untuk diskon
Route::get('/diskon', [DiskonController::class, 'index'])->name('diskon.index');
Route::get('/diskon/create', [DiskonController::class, 'create'])->name('diskon.create');
Route::post('/diskon', [DiskonController::class, 'store'])->name('diskon.store');
Route::get('/diskon/{id}/edit', [DiskonController::class, 'edit'])->name('diskon.edit');
Route::put('/diskon/{id}', [DiskonController::class, 'update'])->name('diskon.update');
Route::delete('/diskon/{id}', [DiskonController::class, 'destroy'])->name('diskon.destroy');

// Rute untuk registrasi dan login pelanggan
Route::middleware(['guest'])->group(function () {
    Route::get('/register', [PelangganAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [PelangganAuthController::class, 'register'])->name('register.store');
    Route::get('/login', [PelangganAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [PelangganAuthController::class, 'login'])->name('login.store');
});

// Logout pelanggan
Route::post('/logout', [PelangganAuthController::class, 'logout'])->name('logout');

// Route untuk halaman dashboard pelanggan yang hanya bisa diakses jika sudah terverifikasi
Route::middleware(['auth:pelanggan', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('pelanggan.dashboard');  // Ganti dengan halaman dashboard pelanggan
    })->name('pelanggan.dashboard');
});

// Routing resource untuk Pembayaran
Route::resource('/pembayaran', PembayaranController::class);

// Routing resource untuk Reservasi
Route::resource('/reservasi', ReservasiController::class);

// Routing resource untuk Pilihan Layanan
Route::resource('/pilihan-layanan', Pilihan_layananController::class);

// Routing resource untuk Reservasi Layanan
Route::resource('/reservasi-layanan', Reservasi_layananController::class);

// Routing resource untuk Reservasi Slot Jadwal
Route::resource('/reservasi-slot-jadwal', Reservasi_slot_jadwalController::class);

// Halaman depan aplikasi
Route::get('/', function () {
    return view('welcome');  // Ganti dengan halaman depan yang sesuai
});

// Route untuk dashboard
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
