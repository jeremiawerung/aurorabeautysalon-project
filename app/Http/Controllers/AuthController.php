<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Menampilkan form login terpadu
    public function showLoginForm()
    {
        return view('auth.unified-login');
    }

    // Proses login terpadu
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $password = $request->password;

        // Cari user berdasarkan email saja
        $user = User::where('email', $email)->first();

        if ($user && Hash::check($password, $user->password)) {
            // Login user dengan guard default
            Auth::login($user, $request->remember);
            
            // Cek apakah email sudah diverifikasi
            if (!$user->email_verified_at) {
                return redirect()->route('verification.notice')->with('message', 'Silakan verifikasi email Anda terlebih dahulu.');
            }
            
            // Redirect berdasarkan role yang ada di database
            if ($user->role === 'admin') {
                return redirect('/admin/dashboard');
            } elseif ($user->role === 'pelanggan') {
                return redirect('/dashboard');
            } else {
                // Jika role tidak dikenal
                Auth::logout();
                return back()->withErrors(['email' => 'Role user tidak valid.']);
            }
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->except('password'));
    }

    // Logout terpadu
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // Redirect setelah login berdasarkan role
    public function redirectAfterLogin()
    {
        $user = Auth::user();
        
        if ($user) {
            if ($user->role === 'admin') {
                return redirect('/admin/dashboard');
            } elseif ($user->role === 'pelanggan') {
                return redirect('/dashboard');
            }
        }

        return redirect('/login');
    }
}