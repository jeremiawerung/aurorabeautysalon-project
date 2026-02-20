<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'id_admin',
        'id_reservasi',
        'order_id',
        'jumlah',
        'diskon_applied',
        'status_pembayaran',
        'id_metodePembayaran',
        'tanggal_pembayaran',
        'bukti_pembayaran',
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'datetime',
        'jumlah' => 'decimal:2',
        'diskon_applied' => 'decimal:2',
    ];

    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'id_reservasi', 'id_reservasi');
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class, 'id_metodePembayaran', 'id_metodePembayaran');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}
