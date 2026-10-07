<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin in users table
        $user = User::create([
            'name' => 'Admin Aurora',
            'email' => 'admin@aurorabeauty.com',
            'password' => Hash::make(env('ADMIN_DEFAULT_PASSWORD', 'AuroraBeauty2026!')),
            'email_verified_at' => now(), // Admin langsung terverifikasi
            
        ]);
        $user->role = 'admin';
        $user->save();

        // Create admin in admin table
        Admin::create([
            'user_id' => $user->id,
            'nama' => 'Admin Aurora',
            'last_login' => now(),
            'status_admin' => 'aktif',
        ]);
    }
}
