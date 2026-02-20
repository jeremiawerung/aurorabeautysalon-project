<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodePembayaran extends Model
{
    use HasFactory;

    protected $table = 'metodepembayaran';

    protected $primaryKey = 'id_metodePembayaran';

    public $timestamps = true; // tabel metodepembayaran juga punya timestamps

    protected $fillable = [
        'id_admin',
        'nama',
        'status',
        'keterangan',
        'tanggal_dibuat',
        'tanggal_update',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_metodePembayaran', 'id_metodePembayaran');
    }
}
