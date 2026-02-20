<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pelanggan extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'pelanggan';

    protected $primaryKey = 'id_pelanggan';

    public $timestamps = true;

    protected $fillable = [
        'nama', 'nomor_telepon', 'status_pelanggan', 'tanggal_daftar', 'user_id'
    ];

    // Relasi dengan model User (One-to-One)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Accessor untuk nama lengkap
    public function getNamaLengkapAttribute()
    {
        return $this->nama;
    }

    // Accessor untuk email (dari tabel users)
    public function getEmailAttribute()
    {
        return $this->user ? $this->user->email : null;
    }

    // Scope untuk filter berdasarkan email
    public function scopeByEmail($query, $email)
    {
        return $query->whereHas('user', function ($q) use ($email) {
            $q->where('email', $email);
        });
    }
}
