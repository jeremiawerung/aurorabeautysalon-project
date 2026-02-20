<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PelangganAuthController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nomor_telepon' => 'required|numeric|digits_between:10,15',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan',
            'email_verified_at' => null, // Force null
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $user->id,
            'nama' => $request->nama,
            'nomor_telepon' => $request->nomor_telepon,
            'status_pelanggan' => 'aktif',
            'tanggal_daftar' => now(),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice')->with('message', 'Pendaftaran berhasil! Silakan cek email Anda untuk verifikasi.');
    }

    public function showLoginForm()
    {
        return redirect()->route('login');
    }
}
