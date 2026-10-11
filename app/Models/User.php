<?php

namespace App\Models;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Menambahkan relasi untuk pelanggan
     * Seorang pengguna dapat memiliki satu pelanggan (One-to-One).
     */
    /**
     * Menambahkan relasi untuk pelanggan
     * Seorang pengguna dapat memiliki satu pelanggan (One-to-One).
     */
    public function pelanggan()
    {
        return $this->hasOne(Pelanggan::class, 'user_id');
    }

    /**
     * Menambahkan relasi untuk admin
     * Seorang pengguna dapat memiliki satu admin (One-to-One).
     */
    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_id');
    }

    /**
     * Mendapatkan status peran pengguna (admin atau pelanggan)
     *
     * @return string
     */
    public function getRoleAttribute()
    {
        return $this->attributes['role'] ?? 'pelanggan';  // Default role adalah pelanggan
    }

    /**
     * Email verifikasi dikirim LANGSUNG (sinkron), sama kayak email reset password.
     *
     * Dulu lewat queue (QueuedVerifyEmail), tapi di hosting cPanel gak ada queue worker,
     * jadi emailnya nyangkut di tabel jobs dan gak pernah kekirim. Sengaja gak pakai cron
     * queue:work karena satu akun hosting dipakai bareng beberapa domain.
     * Konsekuensinya halaman daftar nunggu beberapa detik selama kirim ke SMTP Gmail.
     *
     * Kalau SMTP gagal, error-nya cuma dicatat ke log biar registrasi tetep lanjut ke
     * halaman verifikasi (di sana ada tombol Kirim Ulang).
     */
    public function sendEmailVerificationNotification()
    {
        try {
            $this->notify(new VerifyEmail);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim email verifikasi', ['user_id' => $this->id, 'error' => $e->getMessage()]);
        }
    }
}
