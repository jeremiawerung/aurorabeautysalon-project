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
use App\Http\Controllers\SlotJadwalController;
use App\Http\Controllers\DiskonController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PelangganAuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ForgotPasswordController;

// Halaman depan aplikasi
Route::get('/', function () {
    return view('welcome');
});

// Rute login terpadu (menggantikan Fortify)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [PelangganAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [PelangganAuthController::class, 'register'])->name('register.store');
    
    // Password reset routes
    Route::get('/forgot-password', [App\Http\Controllers\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [App\Http\Controllers\ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\ForgotPasswordController::class, 'reset'])->name('password.update');
});

// Email verification routes (tanpa middleware verified agar bisa diakses user yang belum verified)
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware(['throttle:6,1'])->name('verification.send');
});

// Logout terpadu
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Routes untuk admin (hanya bisa diakses oleh user dengan role admin)
Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/register', [AdminController::class, 'showRegistrationForm'])->name('admin.register');
    Route::post('/register', [AdminController::class, 'store'])->name('admin.store');
    Route::get('/edit/{id}', [AdminController::class, 'edit'])->name('admin.edit');
    Route::put('/update/{id}', [AdminController::class, 'update'])->name('admin.update');
    Route::delete('/delete/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
    
    // Update password admin
    Route::get('/password/edit', [AdminController::class, 'editPasswordForm'])->name('admin.password.edit');
    Route::put('/password', [AdminController::class, 'updatePassword'])->name('admin.password.update');
});

// Routes untuk pelanggan (tanpa prefix)
Route::middleware(['auth', 'verified', 'pelanggan'])->group(function () {
    Route::get('/dashboard', function () {
        return view('pelanggan.dashboard');
    })->name('pelanggan.dashboard');
});

// Routes admin untuk mengelola data (hanya admin yang bisa akses)
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('/pelanggan', PelangganController::class);
    Route::resource('/kategori-layanan', KategoriLayananController::class);
    Route::resource('/layanan', LayananController::class);
    Route::resource('/slot-jadwal', SlotJadwalController::class);
    Route::resource('/metode-pembayaran', MetodePembayaranController::class);
    Route::resource('/diskon', DiskonController::class);
    Route::resource('/pembayaran', PembayaranController::class);
    Route::resource('/reservasi', ReservasiController::class);
    Route::resource('/pilihan-layanan', Pilihan_layananController::class);
    Route::resource('/reservasi-layanan', Reservasi_layananController::class);
    Route::resource('/reservasi-slot-jadwal', Reservasi_slot_jadwalController::class);
});
