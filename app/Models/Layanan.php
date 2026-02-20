<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'layanan';

    protected $primaryKey = 'id_layanan';

    protected $fillable = [
        'id_admin',
        'id_kategoriLayanan',
        'id_diskon',
        'gambar',
        'nama_layanan',
        'harga',
        'deskripsi',
        'durasi',
        'status_layanan',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin');
    }

    public function kategoriLayanan()
    {
        return $this->belongsTo(KategoriLayanan::class, 'id_kategoriLayanan');
    }

    public function diskon()
    {
        return $this->belongsTo(Diskon::class, 'id_diskon', 'id');
    }

    public function slotJadwal()
    {
        return $this->hasMany(Slot_jadwal::class, 'id_layanan');
    }

    // Relasi ke ReservasiLayanan (pivot table)
    public function reservasiLayanan()
    {
        return $this->hasMany(ReservasiLayanan::class, 'id_layanan', 'id_layanan');
    }

    // Accessor untuk format harga
    public function getHargaFormatAttribute()
    {
        return 'Rp '.number_format($this->harga, 0, ',', '.');
    }

    // Scope untuk layanan aktif
    public function scopeAktif($query)
    {
        return $query->where('status_layanan', 'aktif');
    }

    // app/Models/Layanan.php
    public function reservasi()
    {
        // Pastikan nama tabel pivot dan kunci sesuai
        return $this->belongsToMany(Reservasi::class, 'reservasi_layanan', 'id_layanan', 'id_reservasi');
    }
}
