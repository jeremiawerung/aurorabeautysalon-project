<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengaturanSeeder extends Seeder
{
    public function run()
    {
        // ============================
        // 1. TABEL pengaturan_booking
        // ============================
        DB::table('pengaturan_booking')->updateOrInsert(
            ['id' => 1],
            [
                'booking_aktif' => 1,
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '22:00:00',
                'opsi_staff' => 0,
                'dp_value' => '15',
                'dp_tipe' => 'persen',
                'maks_rentang_booking' => 8,
                'interval_min_booking' => 120,
                'kebijakan' => "Kebijakan Kami\n- Jika pembayaran DP dan mengajukan pembatalan tidak ada kompensasi\n- Jika pembayaran penuh akan ada kompensasi 50%",
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // ============================
        // 2. TABEL tentang_kami
        // ============================
        DB::table('tentang_kami')->updateOrInsert(
            ['id' => 1],
            [
                'deskripsi' => 'Pelanggan dapat memilih layanan yang akan di pesan.',
                'hari_operasional' => json_encode([
                    'senin' => ['mulai' => '09:00', 'selesai' => '19:00'],
                    'selasa' => ['mulai' => '09:00', 'selesai' => '19:00'],
                    'rabu' => ['mulai' => '09:00', 'selesai' => '19:00'],
                    'kamis' => ['mulai' => '09:00', 'selesai' => '19:00'],
                    'jumat' => ['mulai' => '09:00', 'selesai' => '19:00'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
