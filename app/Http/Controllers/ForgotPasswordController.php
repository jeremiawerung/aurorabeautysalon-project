<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $token = Str::random(60);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            $resetUrl = url('/reset-password/'.$token.'?email='.urlencode($request->email));

            try {
                Mail::send('emails.password-reset', ['resetUrl' => $resetUrl], function ($message) use ($request) {
                    $message->to($request->email)
                        ->subject('Reset Password - Aurora Beauty Salon');
                });
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send reset email: ' . $e->getMessage());
            }
        }

        return back()->with([
            'status' => 'Jika email Anda terdaftar, link reset password telah dikirim. Silakan cek email (termasuk folder spam/junk).',
        ]);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

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

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $tokenRecord || ! Hash::check($request->token, $tokenRecord->token)) {
            return back()->withErrors(['token' => 'Token reset tidak valid atau sudah kadaluarsa.']);
        }

        if (\Carbon\Carbon::parse($tokenRecord->created_at)->addMinutes(60)->isPast()) {
            // Delete expired token
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()->withErrors(['token' => 'Token reset sudah kadaluarsa. Silakan minta reset password baru.']);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'User tidak ditemukan.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', '✅ Password berhasil direset! Silakan login dengan password baru Anda.');
    }
}
