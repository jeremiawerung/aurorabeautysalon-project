<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    public $timestamps = false;
    protected $fillable = [
        'id_reservasi', 'id_metodePembayaran', 'jumlah', 'status', 'diskon_applied'
    ];
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin');
    }

    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'id_reservasi');
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class, 'id_metodePembayaran');
    }
}
