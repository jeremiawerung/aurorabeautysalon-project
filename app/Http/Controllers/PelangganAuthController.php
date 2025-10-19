<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class PelangganAuthController extends Controller
{
    // Menampilkan form registrasi pelanggan
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Menyimpan data pelanggan baru
    public function register(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nomor_telepon' => 'required|numeric|digits_between:10,15',
            'password' => 'required|min:8|confirmed',
        ]);

        // Membuat pengguna baru untuk autentikasi (User)
        $user = User::create([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan', // Set role sebagai pelanggan
        ]);

        // Membuat pelanggan baru setelah validasi (untuk data detail)
        $pelanggan = Pelanggan::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'nomor_telepon' => $request->nomor_telepon,
            'password' => Hash::make($request->password),
            'status_pelanggan' => 'aktif', // Langsung aktif untuk sekarang
            'tanggal_daftar' => now(),
        ]);

        // Men-trigger event registrasi (untuk mengirim email verifikasi)
        event(new Registered($user));
        
        // Login user setelah registrasi
        Auth::login($user);
        
        // Redirect ke halaman verifikasi email
        return redirect()->route('verification.notice')->with('message', 'Pendaftaran berhasil! Silakan cek email Anda untuk verifikasi.');
    }

    // Menampilkan form login pelanggan (tidak digunakan lagi, login terpadu di AuthController)
    public function showLoginForm()
    {
        return redirect()->route('login');
    }

    // Login dan logout sudah ditangani di AuthController
}
