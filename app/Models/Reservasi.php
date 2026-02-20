<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model
{
    use HasFactory;

    protected $table = 'reservasi';

    protected $primaryKey = 'id_reservasi';

    protected $guarded = ['id_reservasi'];

    protected $casts = [
        'tanggal_reservasi' => 'date',
        'total_harga' => 'decimal:2',
        'catatan' => 'array',
    ];

    // Relasi ke Pelanggan
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_reservasi', 'id_reservasi');
    }

    // Relasi Many-to-Many ke Layanan melalui tabel pivot reservasi_layanan
    public function layanan()
    {
        // Pastikan nama tabel pivot dan kunci sesuai
        return $this->belongsToMany(Layanan::class, 'reservasi_layanan', 'id_reservasi', 'id_layanan')
                    ->withPivot(['harga_deal', 'nama_layanan_snapshot'])
                    ->withTimestamps();
    }

    // Relasi ke ReservasiLayanan (pivot table)
    public function reservasiLayanan()
    {
        return $this->hasMany(ReservasiLayanan::class, 'id_reservasi', 'id_reservasi');
    }

    // Accessor untuk format tanggal Indonesia
    public function getTanggalFormatAttribute()
    {
        return \Carbon\Carbon::parse($this->tanggal_reservasi)
            ->locale('id')
            ->isoFormat('dddd, D MMMM YYYY');
    }

    // Accessor untuk format harga
    public function getHargaFormatAttribute()
    {
        return 'Rp '.number_format($this->total_harga, 0, ',', '.');
    }

    // Scope untuk filter berdasarkan status
    public function scopePending($query)
    {
        return $query->where('status_reservasi', 'pending');
    }

    public function scopeDikonfirmasi($query)
    {
        return $query->where('status_reservasi', 'dikonfirmasi');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status_reservasi', 'selesai');
    }

    public function scopeDibatalkan($query)
    {
        return $query->where('status_reservasi', 'dibatalkan');
    }
}
