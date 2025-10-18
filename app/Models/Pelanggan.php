<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pelanggan extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    // Tentukan nama tabel dan primary key jika berbeda dari default
    protected $table = 'pelanggan'; // Nama tabel sesuai dengan yang ada di database
    protected $primaryKey = 'id_pelanggan'; // Sesuaikan dengan primary key tabel
    public $timestamps = true; // Karena kita menggunakan created_at dan updated_at

    // Tentukan kolom yang dapat diisi (mass assignment)
    protected $fillable = [
        'nama', 'nomor_telepon', 'email', 'password', 'tanggal_daftar'
    ];

    // Hash password sebelum disimpan ke database
    protected static function booted()
    {
        static::creating(function ($pelanggan) {
            $pelanggan->password = bcrypt($pelanggan->password); // Mengenkripsi password saat menyimpan
        });
    }

    // Relasi dengan tabel reservasi dan pilihan layanan
    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'id_pelanggan');
    }

    public function pilihanLayanan()
    {
        return $this->hasMany(PilihanLayanan::class, 'id_pelanggan');
    }

    // Relasi dengan model User (One-to-One) berdasarkan email
    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
