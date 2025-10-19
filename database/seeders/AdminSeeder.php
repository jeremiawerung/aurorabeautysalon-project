<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;
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
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);

        // Create admin in admin table
        Admin::create([
            'nama' => 'Admin Aurora',
            'email' => 'admin@aurorabeauty.com',
            'password' => Hash::make('password123'),
            'last_login' => now(),
            'status_admin' => 'aktif'
        ]);
    }
}