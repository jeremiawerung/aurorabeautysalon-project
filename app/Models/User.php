<?php

namespace App\Models;

use App\Notifications\QueuedVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
    protected $guarded = ['id'];

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
     * Override notifikasi verifikasi email bawaan supaya dikirim lewat queue.
     *
     * Tanpa ini, notifikasi VerifyEmail default Laravel dikirim SINKRON (SMTP asli ke Gmail,
     * lihat .env MAIL_MAILER=smtp) di tengah request registrasi, membuat halaman tergantung
     * beberapa detik sebelum redirect. QueuedVerifyEmail identik tapi ShouldQueue.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new QueuedVerifyEmail);
    }
}
