<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.unified-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (! $user->hasVerifiedEmail()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Silakan verifikasi email Anda terlebih dahulu.'])
                    ->with('email_unverified', $request->email);
            }

            $request->session()->forget('url.intended');

            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'pelanggan') {
                return redirect()->route('welcome');
            }

            Auth::logout();

            return back()->withErrors(['email' => 'Role user tidak valid.']);
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function redirectAfterLogin()
    {
        $user = Auth::user();
        
        \Illuminate\Support\Facades\Log::info('RedirectAfterLogin Hit', [
            'user_id' => $user ? $user->id : 'null',
            'role' => $user ? $user->role : 'null'
        ]);

        if ($user) {
            if ($user->role === 'admin') {
                return redirect('/admin');
            } elseif ($user->role === 'pelanggan') {
                return redirect('/');
            }
            
            // Fallback for unknown role (assume pelanggan default or show error)
            \Illuminate\Support\Facades\Log::warning('RedirectAfterLogin: Unknown Role', ['role' => $user->role]);
            return redirect('/'); 
        }

        // Should not happen if middleware 'auth' is working
        \Illuminate\Support\Facades\Log::error('RedirectAfterLogin: User is null despite auth middleware.');
        return redirect('/login');
    }
}
