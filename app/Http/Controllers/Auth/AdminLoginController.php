<?php

namespace App\Http\Controllers\Auth;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.admin-login');  // Halaman login admin
    }

    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Proses login menggunakan guard 'admin'
        if (Auth::guard('admin')->attempt([
            'email' => $request->email,
            'password' => $request->password,  // Verifikasi password menggunakan bcrypt
        ], $request->remember)) {
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    public function logout()
    {
        Auth::guard('admin')->logout();  // Logout admin
        return redirect('/admin/login');
    }
}
