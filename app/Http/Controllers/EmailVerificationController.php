<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        // 1. Ambil fresh instance dari DB untuk menghindari caching model saat login
        $user = Auth::user() ? Auth::user()->fresh() : null;

        \Illuminate\Support\Facades\Log::info('EmailVerificationController::notice', [
            'id' => $user->id ?? 'null',
            'email_verified_at' => $user->email_verified_at ?? 'null',
            'hasVerifiedEmail' => $user ? $user->hasVerifiedEmail() : 'false'
        ]);

        // 2. Cek STRICTLY null. Jangan andalkan hasVerifiedEmail() saja jika ragu.
        if ($user && !is_null($user->email_verified_at)) {
            return redirect($this->redirectPath());
        }

        return view('auth.verify-email');
    }

    public function verify(Request $request, $id) // Removed EmailVerificationRequest strict type
    {
        $userId = $id; 
        
        \Illuminate\Support\Facades\Log::info('EmailVerificationController: Hit verify route.', [
            'route_id' => $userId, 
            'auth_id' => Auth::id() ?? 'guest',
        ]);

        // Force Login for UX if user is not logged in but ID exists
        if (!Auth::check()) {
             $user = \App\Models\User::find($userId);
             if ($user) {
                 Auth::login($user);
             } else {
                 return redirect('/login')->with('error', 'User tidak ditemukan.');
             }
        } else {
             $user = Auth::user();
        }

        // Security & Session Fix: Ensure logged in user matches URL ID
        if ($user && $user->id != $userId) {
             \Illuminate\Support\Facades\Log::warning('EmailVerificationController: ID Mismatch. switching user.', ['current_auth' => $user->id, 'url_target' => $userId]);
             Auth::logout(); // Logout user A
             
             $targetUser = \App\Models\User::find($userId);
             if ($targetUser) {
                 Auth::login($targetUser); // Login user B
                 $user = $targetUser;
             } else {
                 return redirect('/login')->with('error', 'Akun tidak ditemukan.');
             }
        }

        // Double check user object after potential switch
        if (!$user) {
             return redirect('/login')->with('error', 'Silakan login ulang.');
        }

        // Validasi hash terhadap email user saat ini (persis seperti Illuminate\Foundation\Auth\EmailVerificationRequest).
        // Middleware 'signed' pada route ini sudah menjamin URL (termasuk id & hash) belum
        // diubah dan belum kedaluwarsa, tapi pengecekan hash tetap dipertahankan sebagai lapis
        // kedua — mis. supaya link lama otomatis tidak valid lagi kalau email user berubah.
        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
             \Illuminate\Support\Facades\Log::warning('EmailVerificationController: Hash tidak cocok.', ['user_id' => $user->id]);

             return redirect('/login')->with('error', 'Link verifikasi tidak valid.');
        }

        // 1. Check if already verified
        if ($user->hasVerifiedEmail()) {
            return redirect($this->redirectPath())->with('verified', 'Email Anda sudah diverifikasi sebelumnya.');
        }

        // 2. Mark as verified (DIRECT DB UPDATE to be 100% sure)
        $timestamp = now();
        
        // Method 1: Standard Laravel
        if ($user->markEmailAsVerified()) {
             event(new Verified($user));
             \Illuminate\Support\Facades\Log::info('EmailVerificationController: Standard verification success.');
        } 
        
        // Method 2: Failsafe Force Update
        if (!$user->hasVerifiedEmail()) {
             \Illuminate\Support\Facades\Log::warning('EmailVerificationController: Standard failed, forcing DB update.');
             \App\Models\User::where('id', $user->id)->update(['email_verified_at' => $timestamp]);
//             $user->refresh(); // Reload to sync
        }

        // 3. Sync to Pelanggan Table (Dynamic Requirement)
        if ($user->role === 'pelanggan') {
            try {
                $updated = \Illuminate\Support\Facades\DB::table('pelanggan')
                    ->where('user_id', $user->id)
                    ->update(['email_verified_at' => $timestamp]);
                
                \Illuminate\Support\Facades\Log::info('EmailVerificationController: Synced to pelanggan table.', [
                   'user_id' => $user->id,
                   'updated_rows' => $updated
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('EmailVerificationController: Sync failed.', ['error' => $e->getMessage()]);
            }
        }

        return redirect($this->redirectPath())
            ->with('verified', 'Email Anda telah berhasil diverifikasi! Silakan login kembali jika diperlukan.');
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($this->redirectPath())
                ->with('message', 'Email sudah terverifikasi.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('resent', 'Link verifikasi telah dikirim ulang ke email Anda!');
    }

    protected function redirectPath()
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            return '/admin';
        } elseif ($user && $user->role === 'pelanggan') {
            return '/';
        }

        return '/';
    }
}
