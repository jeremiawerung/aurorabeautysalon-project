<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterDataTransferSeeder extends Seeder
{
    public function run()
    {
        // NAMA DATABASE LAMA (SUMBER)
        $dbLama = 'db_aurora_beauty_salon_v1'; 

        DB::beginTransaction();
        try {
            $this->command->info("Mulai Transfer Data Master dari $dbLama ke Database Baru...");

            // ==========================================
            // 1. TRANSFER USERS (PONDASI UTAMA)
            // ==========================================
            // Kita pindahkan user dulu karena Admin & Pelanggan butuh user_id
            $oldUsers = DB::table("$dbLama.users")->get();
            foreach ($oldUsers as $user) {
                DB::table('users')->insertOrIgnore([
                    'id' => $user->id, // ID LAMA WAJIB DIPERTAHANKAN
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'password' => $user->password,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
            $this->command->info('1. Tabel Users SELESAI.');

            // ==========================================
            // 2. TRANSFER ADMIN
            // ==========================================
            $oldAdmins = DB::table("$dbLama.admin")->get();
            foreach ($oldAdmins as $admin) {
                // Cari User ID miliknya (berdasarkan email di DB lama atau Users yg baru dipindah)
                $user = DB::table('users')->where('email', $admin->email)->first();
                
                DB::table('admin')->insertOrIgnore([
                    'id_admin' => $admin->id_admin, // Pertahankan ID Admin
                    'user_id' => $user ? $user->id : null, 
                    'nama' => $admin->nama,
                    'status_admin' => $admin->status_admin,
                    'last_login' => $admin->last_login,
                    'created_at' => $admin->created_at,
                    'updated_at' => $admin->updated_at,
                ]);
            }
            $this->command->info('2. Tabel Admin SELESAI.');

            // ==========================================
            // 3. TRANSFER PELANGGAN
            // ==========================================
            $oldPelanggans = DB::table("$dbLama.pelanggan")->get();
            foreach ($oldPelanggans as $plg) {
                // Cek apakah pelanggan ini punya akun user (berdasarkan email)
                $user = null;
                if (!empty($plg->email)) {
                    $user = DB::table('users')->where('email', $plg->email)->first();
                }

                DB::table('pelanggan')->insertOrIgnore([
                    'id_pelanggan' => $plg->id_pelanggan, // Pertahankan ID Pelanggan
                    'user_id' => $user ? $user->id : null,
                    'nama' => $plg->nama,
                    'nomor_telepon' => $plg->nomor_telepon,
                    'tanggal_daftar' => $plg->tanggal_daftar,
                    'status_pelanggan' => $plg->status_pelanggan,
                    'email_verified_at' => $plg->email_verified_at,
                    'created_at' => $plg->created_at,
                    'updated_at' => $plg->updated_at,
                ]);
            }
            $this->command->info('3. Tabel Pelanggan SELESAI.');

            // ==========================================
            // 4. TRANSFER DATA PENDUKUNG (Urutan Penting!)
            // ==========================================
            
            // A. Diskon (Tidak ada foreign key, aman dipindah duluan)
            $diskons = DB::table("$dbLama.diskon")->get();
            foreach ($diskons as $d) {
                DB::table('diskon')->insertOrIgnore((array)$d);
            }

            // B. Kategori Layanan (Butuh id_admin, makanya Admin harus selesai duluan)
            $kategoris = DB::table("$dbLama.kategori_layanan")->get();
            foreach ($kategoris as $k) {
                DB::table('kategori_layanan')->insertOrIgnore((array)$k);
            }

            // C. Layanan (Butuh Admin, Kategori, & Diskon. Makanya ini dipindah belakangan)
            $layanans = DB::table("$dbLama.layanan")->get();
            foreach ($layanans as $l) {
                DB::table('layanan')->insertOrIgnore((array)$l);
            }

            // D. Galeri Foto (Butuh Layanan)
            $fotos = DB::table("$dbLama.galeri_fotos")->get();
            foreach ($fotos as $f) {
                DB::table('galeri_fotos')->insertOrIgnore((array)$f);
            }

            // E. Slot Jadwal (Butuh Admin & Layanan)
            $slots = DB::table("$dbLama.slot_jadwal")->get();
            foreach ($slots as $s) {
                DB::table('slot_jadwal')->insertOrIgnore((array)$s);
            }
            
            $this->command->info('4. Tabel Produk (Layanan, Kategori, Slot, dll) SELESAI.');

            // ==========================================
            // 5. TRANSFER PENGATURAN UMUM
            // ==========================================
            
            // Lokasi
            $lokasis = DB::table("$dbLama.lokasi")->get();
            foreach ($lokasis as $lok) {
                DB::table('lokasi')->insertOrIgnore((array)$lok);
            }

            // Metode Pembayaran
            $metodes = DB::table("$dbLama.metodepembayaran")->get();
            foreach ($metodes as $m) {
                DB::table('metodepembayaran')->insertOrIgnore((array)$m);
            }

            // Pengaturan Booking
            $settings = DB::table("$dbLama.pengaturan_booking")->get();
            foreach ($settings as $set) {
                DB::table('pengaturan_booking')->insertOrIgnore((array)$set);
            }

            // Tentang Kami
            $abouts = DB::table("$dbLama.tentang_kami")->get();
            foreach ($abouts as $a) {
                DB::table('tentang_kami')->insertOrIgnore((array)$a);
            }
            
            $this->command->info('5. Tabel Pengaturan SELESAI.');

            DB::commit();
            $this->command->info('=========================================');
            $this->command->info('SUKSES! SEMUA DATA MASTER SUDAH PINDAH.');
            $this->command->info('=========================================');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('GAGAL! Ada Error: ' . $e->getMessage());
        }
    }
}