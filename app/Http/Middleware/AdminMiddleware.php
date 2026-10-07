<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah user sudah login DAN merupakan admin
        if (Auth::check() && Auth::user()->role === 'admin') {
            $admin = \App\Models\Admin::where('user_id', Auth::id())->first();
            if ($admin && $admin->status_admin === 'aktif') {
                return $next($request);
            }
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akun admin Anda tidak aktif.');
        }

        // Jika bukan admin, redirect ke dashboard atau halaman lain
        return redirect()->route('home')->with('error', 'Akses ditolak.');
    }
}
