<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservasi_Slot_Jadwal extends Model
{
    use HasFactory;

    // Nama tabel sesuai migration
    protected $table = 'reservasi_slot_jadwal';

    // Karena pakai composite primary key, kita nonaktifkan auto-increment & timestamps opsional
    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'id_reservasi',
        'id_slot',
    ];

    /**
     * Relasi ke model Reservasi
     */
    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'id_reservasi', 'id_reservasi');
    }

    /**
     * Relasi ke model Slot_jadwal
     */
    public function slot()
    {
        return $this->belongsTo(Slot_jadwal::class, 'id_slot', 'id_slot');
    }
}
