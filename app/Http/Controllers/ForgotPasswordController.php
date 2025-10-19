<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak terdaftar di sistem kami.',
        ]);

        // Generate custom reset token
        $token = Str::random(60);
        
        // Store token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Generate reset URL
        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($request->email));

        // Send custom email
        try {
            Mail::send('emails.password-reset', ['resetUrl' => $resetUrl], function($message) use ($request) {
                $message->to($request->email)
                       ->subject('Reset Password - Aurora Beauty Salon');
            });

            return back()->with([
                'status' => '✅ Link reset password telah dikirim ke email Anda! Silakan cek email (termasuk folder spam/junk).'
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Gagal mengirim email. Silakan coba lagi atau hubungi admin.'
            ]);
        }
    }

    /**
     * Display the password reset view for the given token.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ], [
            'token.required' => 'Token reset tidak valid.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Verify token
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenRecord || !Hash::check($request->token, $tokenRecord->token)) {
            return back()->withErrors(['token' => 'Token reset tidak valid atau sudah kadaluarsa.']);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($tokenRecord->created_at) > 60) {
            // Delete expired token
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['token' => 'Token reset sudah kadaluarsa. Silakan minta reset password baru.']);
        }

        // Find user and update password
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'User tidak ditemukan.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ]);

        // Delete the used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Fire password reset event
        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', '✅ Password berhasil direset! Silakan login dengan password baru Anda.');
    }
}