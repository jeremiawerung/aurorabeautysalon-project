<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use \Illuminate\Notifications\Notifiable;

    protected $table = 'admin';

    protected $primaryKey = 'id_admin';

    public $timestamps = true;

    // Tambahkan status_admin ke $fillable agar bisa diisi
    protected $fillable = [
        'nama', 'status_admin', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
