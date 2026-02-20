<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diskon extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'diskon';

    // Kolom yang dapat diisi secara massal
    protected $fillable = [
        'nama_diskon',
        'kode_diskon',
        'persentase_diskon',
        'tanggal_mulai',
        'tanggal_berakhir',
        'status_diskon',
        'status_voucher',
        'keterangan',
    ];

    // Cast kolom tanggal
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
    ];

    /**
     * Relasi One-to-Many ke Layanan
     * Satu Diskon dapat memiliki banyak Layanan
     */
    public function layanan()
    {
        return $this->hasMany(Layanan::class, 'id_diskon', 'id');
    }

    /**
     * Scope untuk filter diskon aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status_diskon', 'aktif');
    }

    /**
     * Accessor untuk format persentase
     */
    public function getPersentaseFormatAttribute()
    {
        return number_format($this->persentase_diskon, 2).'%';
    }

    /**
     * Cek apakah diskon sudah kedaluwarsa
     */
    public function isExpired()
    {
        if ($this->tanggal_berakhir) {
            return now()->isAfter($this->tanggal_berakhir);
        }

        return false;
    }

    /**
     * Cek apakah diskon sedang aktif (dalam periode waktu)
     */
    public function isActive()
    {
        $now = now();
        $started = $now->isAfter($this->tanggal_mulai) || $now->isSameDay($this->tanggal_mulai);

        if ($this->tanggal_berakhir) {
            $notExpired = $now->isBefore($this->tanggal_berakhir) || $now->isSameDay($this->tanggal_berakhir);

            return $started && $notExpired && $this->status_diskon === 'aktif';
        }

        return $started && $this->status_diskon === 'aktif';
    }
}
