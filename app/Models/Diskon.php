<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diskon extends Model
{
    use HasFactory;

    protected $table = 'diskon';

    protected $fillable = [
        'nama_diskon', 'persentase_diskon', 'tanggal_mulai', 'tanggal_berakhir', 'status', 'keterangan'
    ];

    // Relasi dengan Layanan (1:N)
    public function layanan()
    {
        return $this->hasMany(Layanan::class, 'id_diskon');
    }
}
