<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot_Jadwal extends Model
{
    use HasFactory;

    protected $table = 'slot_jadwal';
    protected $primaryKey = 'id_slot';
    public $timestamps = false;
    protected $fillable = [
        'id_admin', 'id_layanan', 'waktu', 'status_slot', 'is_default'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin');
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }

    public function reservasi()
    {
        return $this->hasManyThrough(Reservasi::class, Reservasi_Slot_Jadwal::class, 'id_slot', 'id_reservasi');
    }
}
