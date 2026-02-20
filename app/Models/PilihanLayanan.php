<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilihanLayanan extends Model
{
    use HasFactory;

    protected $table = 'pilihan_layanan';

    protected $primaryKey = 'id_pilihan';

    protected $fillable = [
        'id_pelanggan',
        'id_slot',
        'tanggal_dipilih',
        'status_pilihan',
    ];

    protected $casts = [
        'tanggal_dipilih' => 'date',
    ];

    // Relasi ke Pelanggan
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    // Relasi ke Slot Jadwal
    public function slotJadwal()
    {
        return $this->belongsTo(SlotJadwal::class, 'id_slot', 'id_slot');
    }
}
