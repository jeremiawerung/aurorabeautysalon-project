<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriLayanan extends Model
{
    use HasFactory;

    protected $table = 'kategori_layanan';
    protected $primaryKey = 'id_kategoriLayanan';
    public $timestamps = false;
    protected $fillable = [
        'id_admin', 'nama', 'status', 'keterangan', 'tanggal_update'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin');
    }

    public function layanan()
    {
        return $this->hasMany(Layanan::class, 'id_kategoriLayanan');
    }
}
