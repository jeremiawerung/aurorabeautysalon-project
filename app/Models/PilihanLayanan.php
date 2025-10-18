<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilihanLayanan extends Model
{
    use HasFactory;

    protected $table = 'pilihan_layanan';
    protected $primaryKey = 'id_pilihan';
    public $timestamps = false;

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }

    public function slotJadwal()
    {
        return $this->belongsTo(Slot_Jadwal::class, 'id_slot');
    }
}
