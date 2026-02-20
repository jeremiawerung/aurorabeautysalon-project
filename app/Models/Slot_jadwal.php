<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot_jadwal extends Model
{
    use HasFactory;

    protected $table = 'slot_jadwal';

    protected $primaryKey = 'id_slot';

    public $timestamps = false;

    protected $fillable = [
        'id_admin', 'id_layanan', 'waktu', 'status_slot', 'is_default',
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
        return $this->belongsToMany(
            Reservasi::class,           // model tujuan
            'reservasi_slot_jadwal',    // nama pivot table
            'id_slot',                  // foreign key di tabel pivot untuk Slot_jadwal
            'id_reservasi'              // foreign key di tabel pivot untuk Reservasi
        );
    }
}
