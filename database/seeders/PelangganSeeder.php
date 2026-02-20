<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create customer in users table (tanpa verifikasi untuk testing)
        $user = User::create([
            'name' => 'Maria Sari',
            'email' => 'maria.sari@gmail.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now()->toDateString(),
            'role' => 'pelanggan',
        ]);

        // Create customer in pelanggan table
        Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Maria Sari',
            'nomor_telepon' => '081234567890',
            'tanggal_daftar' => now()->toDateString(),
            'status_pelanggan' => 'aktif',
        ]);
    }
}
