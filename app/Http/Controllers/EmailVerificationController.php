<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification notice.
     */
    public function notice()
    {
        $user = Auth::user();
        
        // Jika user sudah verified, redirect ke dashboard
        if ($user && $user->email_verified_at) {
            return redirect($this->redirectPath());
        }
        
        return view('auth.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(EmailVerificationRequest $request)
    {
        $user = $request->user();
        
        if ($user->email_verified_at) {
            return redirect()->intended($this->redirectPath());
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->save();
        
        event(new Verified($user));

        return redirect()->intended($this->redirectPath())->with('verified', 'Email Anda telah berhasil diverifikasi!');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request)
    {
        $user = $request->user();
        
        if ($user->email_verified_at) {
            return redirect()->intended($this->redirectPath());
        }

        $user->sendEmailVerificationNotification();

        return back()->with('resent', 'Email verifikasi telah dikirim ulang!');
    }

    /**
     * Get the redirect path based on user role.
     */
    protected function redirectPath()
    {
        $user = Auth::user();
        
        if ($user && $user->role === 'admin') {
            return '/admin/dashboard';
        } elseif ($user && $user->role === 'pelanggan') {
            return '/dashboard';
        }
        
        return '/dashboard';
    }
}