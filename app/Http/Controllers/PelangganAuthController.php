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
            'email' => 'required|email|unique:pelanggan,email',
            'nomor_telepon' => 'required|numeric|digits_between:10,15|unique:pelanggan,nomor_telepon',
            'password' => 'required|min:8|confirmed',  // Menyertakan konfirmasi password
        ]);

        // Membuat pelanggan baru setelah validasi
        $pelanggan = Pelanggan::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'nomor_telepon' => $request->nomor_telepon,
            'password' => Hash::make($request->password), // Mengenkripsi password
            'status_pelanggan' => 'inactive', // Status pelanggan masih inactive setelah registrasi
            'tanggal_daftar' => now(),
        ]);

        // Membuat pengguna baru untuk autentikasi (User)
        $user = User::create([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan', // Set role sebagai pelanggan
        ]);

        // Men-trigger event registrasi (untuk mengirim email verifikasi)
        event(new Registered($user));
        
        // Redirect ke halaman login setelah registrasi berhasil
        return redirect()->route('login')->with('message', 'Pendaftaran berhasil! Cek email untuk verifikasi.');
    }

    // Menampilkan form login pelanggan
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Proses login pelanggan
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('pelanggan')->attempt($credentials)) {
            $pelanggan = Auth::guard('pelanggan')->user();

            // Cek status pelanggan jika aktif
            if ($pelanggan->status_pelanggan !== 'aktif') {
                Auth::guard('pelanggan')->logout();
                return back()->withErrors(['email' => 'Akun Anda tidak aktif.']);
            }

            // Redirect ke halaman index setelah login berhasil
            return redirect()->route('pelanggan.index');
        }

        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    // Logout pelanggan
    public function logout()
    {
        Auth::guard('pelanggan')->logout();
        return redirect()->route('login');
    }
}
