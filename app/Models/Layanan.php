<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'layanan';
    protected $primaryKey = 'id_layanan';
    public $timestamps = false;
    protected $fillable = [
        'id_admin', 'id_kategoriLayanan', 'nama_layanan', 'harga', 'deskripsi', 'durasi', 'status_layanan', 'id_diskon'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin');
    }

    public function kategoriLayanan()
    {
        return $this->belongsTo(KategoriLayanan::class, 'id_kategoriLayanan');
    }

    public function slotJadwal()
    {
        return $this->hasMany(Slot_Jadwal::class, 'id_layanan');
    }
    // Relasi ke diskon (N:1)
    public function diskon()
    {
        return $this->belongsTo(Diskon::class, 'id_diskon');
    }
}
