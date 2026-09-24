<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DummySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== DummySeeder: mulai seeding data dummy realistis ===');
        Carbon::setLocale('id');
        $faker = \Faker\Factory::create('id_ID');
        $now = Carbon::now();

        // Kuantitas data (silakan ubah sesuai kebutuhan)
        $N_ADMIN = 3;
        $N_KATEGORI = 6;
        $N_LAYANAN = 18;
        $N_PELANGGAN = 3;
        // $N_RESERVASI = 380;
        $SLOT_START_HOUR = 9;
        $SLOT_END_HOUR = 19;
        $SLOT_STEP_MIN = 60;

        $this->command->newLine();

        // =========================
        // 0) SAFETY HELPERS
        // =========================
        $has = fn (string $t) => Schema::hasTable($t);
        $hasCol = fn (string $t, string $c) => Schema::hasColumn($t, $c);

        // =========================
        // 1) ADMIN
        // =========================
        $adminIDs = [];
        if ($has('admin')) {
            $this->command->info('> Seeding tabel admin ...');
            // Admin utama agar mudah login di area yang menggunakan tabel admin
            $mainAdminEmail = 'admin@audrora.test';
            
            // Check User first
            $user = \App\Models\User::where('email', $mainAdminEmail)->first();
            if (!$user) {
                $user = \App\Models\User::create([
                    'name' => 'Super Admin',
                    'email' => $mainAdminEmail,
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'email_verified_at' => $now,
                ]);
                $this->command->info('  - User Admin utama dibuat: '.$mainAdminEmail);
            }

            if (! DB::table('admin')->where('user_id', $user->id)->exists()) {
                $adminIDs[] = DB::table('admin')->insertGetId([
                    'user_id' => $user->id,
                    'nama' => 'Super Admin',
                    'last_login' => $now,
                    'status_admin' => 'aktif',
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'id_admin');
                $this->command->info('  - Admin utama dibuat (linked to user)');
            } else {
                $adminIDs[] = DB::table('admin')->where('user_id', $user->id)->value('id_admin');
                $this->command->info('  - Admin utama sudah ada');
            }

            // Tambahan admin lain
            $bar = $this->command->getOutput()->createProgressBar(max(0, $N_ADMIN - 1));
            $bar->start();
            for ($i = 1; $i < $N_ADMIN; $i++) {
                $nm = $faker->name();
                $em = 'admin'.$i.'@audrora.test';
                
                $userLine = \App\Models\User::firstOrCreate(
                    ['email' => $em],
                    [
                        'name' => $nm,
                        'password' => Hash::make('password'),
                        'role' => 'admin',
                        'email_verified_at' => $now,
                    ]
                );

                if (DB::table('admin')->where('user_id', $userLine->id)->exists()) {
                    $bar->advance();
                    continue;
                }
                
                $adminIDs[] = DB::table('admin')->insertGetId([
                    'user_id' => $userLine->id,
                    'nama' => $nm,
                    'last_login' => null,
                    'status_admin' => $faker->randomElement(['aktif', 'non-aktif', 'aktif']),
                    'created_at' => $now, 'updated_at' => $now,
                ], 'id_admin');
                $bar->advance();
            }
            $bar->finish();
            $this->command->newLine();
            $this->command->info('  - Admin total: '.count($adminIDs));
        } else {
            $this->command->warn('! Tabel admin tidak ditemukan, skip admin');
        }

        // =========================
        // 2) USERS (opsional; jika ada)
        // =========================
        $usersAvailable = $has('users') && $hasCol('users', 'role');
        if ($usersAvailable) {
            $this->command->info('> Menyiapkan user admin (tabel users) untuk akses aplikasi ...');
            $appAdminEmail = 'app.admin@audrora.test';
            if (! DB::table('users')->where('email', $appAdminEmail)->exists()) {
                DB::table('users')->insert([
                    'name' => 'App Admin',
                    'email' => $appAdminEmail,
                    'email_verified_at' => $now,
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $this->command->info('  - Users admin dibuat: '.$appAdminEmail.' (password: password)');
            } else {
                $this->command->info('  - Users admin sudah ada: '.$appAdminEmail);
            }
        } else {
            $this->command->warn('! Tabel users / kolom role tidak ditemukan, sinkron pelanggan->users akan di-skip.');
        }

        // =========================
        // 3) KATEGORI_LAYANAN
        // =========================
        $kategoriIDs = [];
        if ($has('kategori_layanan')) {
            $this->command->info('> Seeding tabel kategori_layanan ...');
            $default = [
                'Perawatan Rambut',
                'Perawatan Wajah',
                'Pijat & Relaksasi',
                'Kuku & Spa',
                'Perawatan Tubuh',
                'Makeup & Rias',
            ];
            $bar = $this->command->getOutput()->createProgressBar($N_KATEGORI);
            $bar->start();
            for ($i = 0; $i < $N_KATEGORI; $i++) {
                $nm = $default[$i % count($default)];
                $nm = $i >= count($default) ? $nm.' '.$i : $nm;
                $exists = DB::table('kategori_layanan')->where('nama', $nm)->exists();
                if ($exists) {
                    $kategoriIDs[] = DB::table('kategori_layanan')->where('nama', $nm)->value('id_kategoriLayanan');
                    $bar->advance();

                    continue;
                }
                $kategoriIDs[] = DB::table('kategori_layanan')->insertGetId([
                    'id_admin' => ! empty($adminIDs) ? $adminIDs[array_rand($adminIDs)] : null,
                    'gambar' => null,
                    'nama' => $nm,
                    'status' => $faker->randomElement(['aktif', 'aktif', 'aktif', 'non-aktif']),
                    'keterangan' => $faker->boolean(60) ? $faker->sentence(8) : null,
                    'tanggal_update' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'id_kategoriLayanan');
                $bar->advance();
            }
            $bar->finish();
            $this->command->newLine();
            $this->command->info('  - Kategori total: '.count($kategoriIDs));
        } else {
            $this->command->warn('! Tabel kategori_layanan tidak ditemukan, skip kategori');
        }

        // =========================
        // 4) DISKON (minimal)
        // =========================
        // $diskonIDs = [];
        // if ($has('diskon')) {
        //     $this->command->info('> Seeding tabel diskon (minimalis) ...');
        //     for ($i = 0; $i < 5; $i++) {
        //         $diskonIDs[] = DB::table('diskon')->insertGetId([
        //             'created_at' => $now, 'updated_at' => $now,
        //         ]);
        //     }
        //     $this->command->info('  - Diskon total: ' . count($diskonIDs));
        // } else {
        //     $this->command->warn('! Tabel diskon tidak ditemukan, skip diskon');
        // }

        // =========================
        // 5) LAYANAN
        // =========================
        $layananIDs = [];
        if ($has('layanan')) {
            $this->command->info('> Seeding tabel layanan ...');
            $namaLayananPool = [
                'Hair Spa', 'Hair Cut', 'Hair Coloring', 'Blow Dry',
                'Facial Glow', 'Acne Treatment', 'Brightening Facial',
                'Body Massage', 'Hot Stone Massage', 'Aromatherapy Massage',
                'Manicure', 'Pedicure', 'Body Scrub', 'Body Mask',
                'Makeup Party', 'Bridal Makeup', 'Eyebrow Shaping', 'Eyelash Extension',
            ];

            $bar = $this->command->getOutput()->createProgressBar($N_LAYANAN);
            $bar->start();
            for ($i = 0; $i < $N_LAYANAN; $i++) {
                $nama = $namaLayananPool[$i % count($namaLayananPool)];
                $nama = $i >= count($namaLayananPool) ? $nama.' '.$i : $nama;

                $exists = DB::table('layanan')->where('nama_layanan', $nama)->exists();
                if ($exists) {
                    $layananIDs[] = DB::table('layanan')->where('nama_layanan', $nama)->value('id_layanan');
                    $bar->advance();

                    continue;
                }

                $harga = 100000;
                $dur = $faker->randomElement([30, 45, 60, 75, 90, 120]);
                $stat = $faker->randomElement(['aktif', 'aktif', 'aktif', 'non-aktif']);

                $layId = DB::table('layanan')->insertGetId([
                    'id_admin' => ! empty($adminIDs) ? $adminIDs[array_rand($adminIDs)] : null,
                    'id_kategoriLayanan' => ! empty($kategoriIDs) ? $kategoriIDs[array_rand($kategoriIDs)] : null,
                    'gambar' => null,
                    'nama_layanan' => $nama,
                    'harga' => $harga,
                    'deskripsi' => $faker->boolean(70) ? $faker->sentence(10) : null,
                    'durasi' => $dur,
                    'status_layanan' => $stat,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'id_layanan');

                $layananIDs[] = $layId;
                $bar->advance();
            }
            $bar->finish();
            $this->command->newLine();
            $this->command->info('  - Layanan total: '.count($layananIDs));
        } else {
            $this->command->warn('! Tabel layanan tidak ditemukan, skip layanan');
        }

        // =========================
        // 5b) GALERI FOTO (Beranda - galeri "Experience")
        // =========================
        if ($has('galeri_fotos') && ! empty($layananIDs)) {
            if (DB::table('galeri_fotos')->count() === 0) {
                $this->command->info('> Seeding tabel galeri_fotos ...');
                foreach (array_slice($layananIDs, 0, 5) as $i => $layId) {
                    DB::table('galeri_fotos')->insert([
                        'id_layanan' => $layId,
                        'path_url' => '/images/gallery-placeholder-'.($i + 1).'.jpg',
                        'keterangan' => 'Contoh hasil layanan',
                        'urutan' => $i,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                $this->command->info('  - Galeri foto total: 5');
            }
        } else {
            $this->command->warn('! Tabel galeri_fotos tidak ditemukan atau tidak ada layanan, skip galeri');
        }

        // =========================
        // 6) PELANGGAN (+ sinkron ke users jika ada)
        // =========================
        $pelangganIDs = [];
        if ($has('pelanggan')) {
            $this->command->info('> Seeding tabel pelanggan (sinkron ke users bila ada) ...');
            $bar = $this->command->getOutput()->createProgressBar($N_PELANGGAN);
            $bar->start();

            for ($i = 0; $i < $N_PELANGGAN; $i++) {
                $nama = $faker->name();
                $email = strtolower(Str::slug($nama, '.')).$i.'@example.test';
                $telp = '08'.$faker->numerify(str_repeat('#', $faker->numberBetween(9, 11)));
                $pass = 'password';
                $stat = $faker->randomElement(['aktif', 'aktif', 'non-aktif']);

                // Create User first if not exists
                $userPel = \App\Models\User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $nama,
                        'email_verified_at' => $now,
                        'password' => Hash::make($pass),
                        'role' => 'pelanggan',
                    ]
                );

                if (DB::table('pelanggan')->where('user_id', $userPel->id)->exists()) {
                    $bar->advance();
                    continue;
                }

                $tglDaftar = Carbon::now()->subDays($faker->numberBetween(0, 400))->toDateString();
                $pid = DB::table('pelanggan')->insertGetId([
                    'user_id' => $userPel->id,
                    'nama' => $nama,
                    'nomor_telepon' => $telp,
                    'tanggal_daftar' => $tglDaftar,
                    'status_pelanggan' => $stat,
                    // 'email_verified_at' => $now, // Removed from schema based on previous files, checked in migration
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'id_pelanggan');

                $pelangganIDs[] = $pid;
                $bar->advance();
            }
            $bar->finish();
            $this->command->newLine();
            $this->command->info('  - Pelanggan total: '.count($pelangganIDs));
        } else {
            $this->command->warn('! Tabel pelanggan tidak ditemukan, skip pelanggan');
        }

        // =========================
        // 7) SLOT_JADWAL (PER LAYANAN, DINAMIS BERDASARKAN DURASI)
        // =========================
        $slotMapByServiceTime = [];
        $allGeneratedSlotTimes = []; // Untuk tracking semua waktu slot yang dihasilkan

        if ($has('slot_jadwal') && ! empty($layananIDs)) {
            $this->command->info('> Seeding tabel slot_jadwal per layanan (dinamis berdasarkan durasi) ...');

            $inserted = 0;
            $bar = $this->command->getOutput()->createProgressBar(count($layananIDs));
            $bar->start();

            foreach ($layananIDs as $layId) {
                // Ambil data layanan untuk mendapatkan durasi
                $layanan = DB::table('layanan')->where('id_layanan', $layId)->first();
                if (! $layanan || ! $layanan->durasi) {
                    $bar->advance();

                    continue;
                }

                $durasiMenit = (int) $layanan->durasi;
                $slotMapByServiceTime[$layId] = [];

                // Generate slot waktu mulai dari jam operasional
                $currentTime = Carbon::createFromTime($SLOT_START_HOUR, 0, 0);
                $endTime = Carbon::createFromTime($SLOT_END_HOUR, 0, 0);

                while ($currentTime->lessThanOrEqualTo($endTime)) {
                    $waktu = $currentTime->format('H:i:s');

                    // Cek apakah slot sudah ada
                    $exists = DB::table('slot_jadwal')
                        ->where('id_layanan', $layId)
                        ->where('waktu', $waktu)
                        ->exists();

                    if ($exists) {
                        $id = DB::table('slot_jadwal')
                            ->where('id_layanan', $layId)
                            ->where('waktu', $waktu)
                            ->value('id_slot');
                        $slotMapByServiceTime[$layId][$waktu] = $id;
                        $allGeneratedSlotTimes[] = $waktu;
                    } else {
                        // Pastikan slot tidak melewati jam tutup
                        $slotEndTime = $currentTime->copy()->addMinutes($durasiMenit);

                        if ($slotEndTime->lessThanOrEqualTo($endTime->copy()->addHour())) {
                            $id = DB::table('slot_jadwal')->insertGetId([
                                'id_admin' => ! empty($adminIDs) ? $adminIDs[array_rand($adminIDs)] : null,
                                'id_layanan' => $layId,
                                'waktu' => $waktu,
                                'status_slot' => $faker->randomElement(['aktif', 'aktif', 'aktif', 'non-aktif']),
                                'is_default' => 1,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ], 'id_slot');

                            $slotMapByServiceTime[$layId][$waktu] = $id;
                            $allGeneratedSlotTimes[] = $waktu;
                            $inserted++;
                        }
                    }

                    // Tambah waktu sesuai durasi layanan (minimal interval)
                    $currentTime->addMinutes($durasiMenit);
                }

                $bar->advance();
            }

            $bar->finish();
            $this->command->newLine();
            $this->command->info('  - Slot per layanan tersisip: '.$inserted);

            // Buat array slotTimes dari semua waktu yang di-generate (untuk keperluan lain)
            $slotTimes = array_values(array_unique($allGeneratedSlotTimes));
            sort($slotTimes);

        } else {
            $this->command->warn('! Tabel slot_jadwal/layanan tidak ditemukan, skip slot');
        }

        // =========================
        // 8) METODE PEMBAYARAN
        // =========================
        $metodeIDs = [];
        if ($has('metodepembayaran')) {
            $this->command->info('> Seeding tabel metodepembayaran ...');

            // HANYA dua metode pembayaran
            $metodes = [
                'Midtrans',
                'Tunai',
            ];

            foreach ($metodes as $nm) {
                $exists = DB::table('metodepembayaran')->where('nama', $nm)->exists();
                if ($exists) {
                    $metodeIDs[] = DB::table('metodepembayaran')->where('nama', $nm)->value('id_metodePembayaran');

                    continue;
                }

                $metodeIDs[] = DB::table('metodepembayaran')->insertGetId([
                    'id_admin' => ! empty($adminIDs) ? $adminIDs[array_rand($adminIDs)] : null,
                    'nama' => $nm,
                    'status' => 'aktif',
                    'keterangan' => null,
                    'tanggal_dibuat' => $now,
                    'tanggal_update' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'id_metodePembayaran');
            }

            $this->command->info('  - Metode pembayaran total: '.count($metodeIDs));
        } else {
            $this->command->warn('! Tabel metodepembayaran tidak ditemukan, skip metode');
        }

        // // =========================
        // // 9) RESERVASI + PIVOT (ANTI-BENTROK)
        // // =========================
        // $reservasiIDs = [];
        // // Track slot yang sudah digunakan per tanggal: ['2025-01-15' => [slot_id_1, slot_id_2, ...]]
        // $usedSlotsByDate = [];

        // if ($has('reservasi') && $has('reservasi_layanan') && $has('reservasi_slot_jadwal') && !empty($pelangganIDs)) {
        //     $this->command->info('> Seeding tabel reservasi (+pivot reservasi_layanan & reservasi_slot_jadwal) ...');
        //     $bar = $this->command->getOutput()->createProgressBar($N_RESERVASI);
        //     $bar->start();

        //     for ($i = 0; $i < $N_RESERVASI; $i++) {
        //         $pid = $pelangganIDs[array_rand($pelangganIDs)];
        //         $tanggal = Carbon::today()->addDays(random_int(-35, 35))->toDateString();
        //         $jam = !empty($slotTimes) ? $slotTimes[array_rand($slotTimes)] : Carbon::createFromTime(9, 0)->format('H:i:s');

        //         // STATUS RESERVASI: pending, proses, selesai, menunggu_konfirmasi_pembatalan, dibatalkan
        //         // Untuk menunggu_konfirmasi_pembatalan dan dibatalkan, harus dipastikan ada pembayaran dulu
        //         $status = $faker->randomElement([
        //             'pending',
        //             'pending',
        //             'pending',
        //             'proses',
        //             'proses',
        //             'selesai',
        //             'selesai',
        //             // menunggu_konfirmasi_pembatalan dan dibatalkan akan ditangani di bagian pembayaran
        //         ]);

        //         // HANYA 1 LAYANAN PER RESERVASI
        //         $pilihLayanan = null;
        //         if (!empty($layananIDs)) {
        //             $pilihLayanan = $layananIDs[array_rand($layananIDs)];
        //         }

        //         $totalHarga = 0;
        //         if ($pilihLayanan) {
        //             $layananData = DB::table('layanan')->where('id_layanan', $pilihLayanan)->first();
        //             $totalHarga = $layananData ? (float) $layananData->harga : 100000;
        //         }

        //         $rid = DB::table('reservasi')->insertGetId([
        //             'id_pelanggan' => $pid,
        //             'tanggal_reservasi' => $tanggal,
        //             'waktu_reservasi' => $jam,
        //             'status_reservasi' => $status,
        //             'catatan' => $faker->boolean(15) ? $faker->sentence(8) : null,
        //             'total_harga' => $totalHarga,
        //             'created_at' => $now,
        //             'updated_at' => $now,
        //         ], 'id_reservasi');
        //         $reservasiIDs[] = $rid;

        //         // Pivot layanan - HANYA 1 LAYANAN
        //         if ($pilihLayanan) {
        //             $exists = DB::table('reservasi_layanan')
        //                 ->where('id_reservasi', $rid)
        //                 ->where('id_layanan', $pilihLayanan)
        //                 ->exists();
        //             if (!$exists) {
        //                 DB::table('reservasi_layanan')->insert([
        //                     'id_reservasi' => $rid,
        //                     'id_layanan' => $pilihLayanan,
        //                     'created_at' => $now,
        //                     'updated_at' => $now,
        //                 ]);
        //             }
        //         }

        //         // ========================================
        //         // PIVOT SLOT - ANTI BENTROK
        //         // ========================================
        //         $primaryLay = $pilihLayanan ?? ($layananIDs[0] ?? null);
        //         if ($primaryLay && !empty($slotMapByServiceTime)) {
        //             if (!isset($usedSlotsByDate[$tanggal])) {
        //                 $usedSlotsByDate[$tanggal] = [];
        //             }

        //             $idSlot = null;
        //             $attempts = 0;
        //             $maxAttempts = 50;

        //             while ($idSlot === null && $attempts < $maxAttempts) {
        //                 $attempts++;

        //                 if (isset($slotMapByServiceTime[$primaryLay][$jam])) {
        //                     $candidateSlot = $slotMapByServiceTime[$primaryLay][$jam];

        //                     if (!in_array($candidateSlot, $usedSlotsByDate[$tanggal])) {
        //                         $idSlot = $candidateSlot;
        //                         break;
        //                     }
        //                 }

        //                 if (isset($slotMapByServiceTime[$primaryLay]) && $idSlot === null) {
        //                     foreach ($slotMapByServiceTime[$primaryLay] as $waktuAlt => $slotAlt) {
        //                         if (!in_array($slotAlt, $usedSlotsByDate[$tanggal])) {
        //                             $idSlot = $slotAlt;
        //                             $jam = $waktuAlt;
        //                             break;
        //                         }
        //                     }
        //                 }

        //                 if ($idSlot === null) {
        //                     foreach ($slotMapByServiceTime as $layId => $mapWaktu) {
        //                         foreach ($mapWaktu as $waktuAny => $slotAny) {
        //                             if (!in_array($slotAny, $usedSlotsByDate[$tanggal])) {
        //                                 $idSlot = $slotAny;
        //                                 $jam = $waktuAny;
        //                                 break 2;
        //                             }
        //                         }
        //                     }
        //                 }

        //                 if ($idSlot === null && $attempts >= $maxAttempts / 2) {
        //                     $tanggal = Carbon::today()->addDays(random_int(-35, 35))->toDateString();
        //                     if (!isset($usedSlotsByDate[$tanggal])) {
        //                         $usedSlotsByDate[$tanggal] = [];
        //                     }
        //                 }
        //             }

        //             if ($idSlot) {
        //                 DB::table('reservasi')->where('id_reservasi', $rid)->update([
        //                     'tanggal_reservasi' => $tanggal,
        //                     'waktu_reservasi' => $jam,
        //                     'updated_at' => $now,
        //                 ]);

        //                 $existsPivot = DB::table('reservasi_slot_jadwal')
        //                     ->where('id_reservasi', $rid)
        //                     ->where('id_slot', $idSlot)
        //                     ->exists();

        //                 if (!$existsPivot) {
        //                     DB::table('reservasi_slot_jadwal')->insert([
        //                         'id_reservasi' => $rid,
        //                         'id_slot' => $idSlot,
        //                         'created_at' => $now,
        //                         'updated_at' => $now,
        //                     ]);

        //                     $usedSlotsByDate[$tanggal][] = $idSlot;
        //                 }
        //             }
        //         }

        //         $bar->advance();
        //     }
        //     $bar->finish();
        //     $this->command->newLine();
        //     $this->command->info('  - Reservasi total: ' . count($reservasiIDs));
        // } else {
        //     $this->command->warn('! Tabel reservasi / pivot tidak lengkap atau data pelanggan kosong; skip reservasi');
        // }

        // // =========================
        // // 10) PEMBAYARAN
        // // =========================
        // $pembayaranCount = 0;
        // if ($has('pembayaran') && !empty($metodeIDs)) {
        //     $this->command->info('> Seeding tabel pembayaran (dengan logika pembatalan) ...');

        //     // Ambil DP percentage dari pengaturan_booking atau default 15%
        //     $dpPercentage = 15; // default 15%
        //     if ($has('pengaturan_booking') && $hasCol('pengaturan_booking', 'dp_value')) {
        //         $dpValueDb = DB::table('pengaturan_booking')->value('dp_value');
        //         if (!is_null($dpValueDb)) {
        //             $dpPercentage = (float) $dpValueDb;
        //         }
        //     }

        //     $bar = $this->command->getOutput()->createProgressBar(count($reservasiIDs));
        //     $bar->start();

        //     // Array untuk menyimpan reservasi yang akan diubah statusnya ke menunggu_konfirmasi_pembatalan/dibatalkan
        //     $reservasiDibatalkan = [];

        //     foreach ($reservasiIDs as $rid) {
        //         $res = DB::table('reservasi')->where('id_reservasi', $rid)->first();
        //         if (!$res) {
        //             $bar->advance();
        //             continue;
        //         }

        //         $jumlah = (float) $res->total_harga;
        //         if ($jumlah <= 0) {
        //             $bar->advance();
        //             continue;
        //         }

        //         $dpNominal = round($jumlah * $dpPercentage / 100);
        //         $statusReservasi = $res->status_reservasi;

        //         // ========================================
        //         // FASE 1: PEMBAYARAN NORMAL (pending, proses, selesai)
        //         // ========================================
        //         if (in_array($statusReservasi, ['pending', 'proses', 'selesai'])) {
        //             // Tentukan apakah akan membuat data pembatalan nanti
        //             // 20% kemungkinan reservasi ini akan dibatalkan
        //             $akanDibatalkan = $faker->boolean(20);

        //             // Reservasi normal -> bisa DP atau Lunas
        //             $statusPembayaran = $faker->randomElement(['bayar_dp', 'bayar_lunas', 'bayar_lunas']);

        //             if ($statusPembayaran === 'bayar_dp') {
        //                 $jumlahBayar = $dpNominal;
        //             } else {
        //                 $jumlahBayar = $jumlah;
        //             }

        //             $tglRes = Carbon::parse($res->tanggal_reservasi);
        //             $tglBay = $tglRes->copy()->addDays($faker->numberBetween(-2, 3))
        //                 ->setTime($faker->numberBetween(8, 20), $faker->numberBetween(0, 59));

        //             // Generate ORDER ID: TRX-YYYYMMDD-XXXX
        //             $orderId = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        //             $pembayaranData = [
        //                 'id_admin' => !empty($adminIDs) ? $adminIDs[array_rand($adminIDs)] : null,
        //                 'id_reservasi' => $rid,
        //                 'jumlah' => $jumlahBayar,
        //                 'diskon_applied' => 0,
        //                 'status_pembayaran' => $statusPembayaran,
        //                 'id_metodePembayaran' => $metodeIDs[array_rand($metodeIDs)],
        //                 'tanggal_pembayaran' => $tglBay,
        //                 'bukti_pembayaran' => null,
        //                 'created_at' => $now,
        //                 'updated_at' => $now,
        //             ];

        //             if ($hasCol('pembayaran', 'order_id')) {
        //                 $pembayaranData['order_id'] = $orderId;
        //             }

        //             DB::table('pembayaran')->insert($pembayaranData);
        //             $pembayaranCount++;

        //             // Simpan info untuk pembatalan nanti
        //             if ($akanDibatalkan) {
        //                 $reservasiDibatalkan[] = [
        //                     'id_reservasi' => $rid,
        //                     'status_pembayaran_awal' => $statusPembayaran,
        //                     'jumlah_awal' => $jumlahBayar,
        //                     'total_harga' => $jumlah,
        //                 ];
        //             }
        //         }

        //         $bar->advance();
        //     }

        //     // ========================================
        //     // FASE 2: PROSES PEMBATALAN
        //     // ========================================
        //     if (!empty($reservasiDibatalkan)) {
        //         $this->command->newLine();
        //         $this->command->info('> Memproses pembatalan reservasi ...');
        //         $barBatal = $this->command->getOutput()->createProgressBar(count($reservasiDibatalkan));
        //         $barBatal->start();

        //         foreach ($reservasiDibatalkan as $dataBatal) {
        //             $rid = $dataBatal['id_reservasi'];
        //             $statusPembayaranAwal = $dataBatal['status_pembayaran_awal'];
        //             $jumlahAwal = $dataBatal['jumlah_awal'];
        //             $totalHarga = $dataBatal['total_harga'];

        //             // Tentukan status pembatalan dan jumlah refund
        //             $statusPembatalanBaru = null;
        //             $jumlahRefund = 0;

        //             if ($statusPembayaranAwal === 'bayar_dp') {
        //                 // DP tidak dikembalikan
        //                 $statusPembatalanBaru = 'pembatalan_dp';
        //                 $jumlahRefund = 0; // Tidak ada refund
        //             } else {
        //                 // Lunas dikembalikan 50%
        //                 $statusPembatalanBaru = 'pembatalan_lunas';
        //                 $jumlahRefund = round($totalHarga * 0.5); // Refund 50%
        //             }

        //             // Tentukan status reservasi (menunggu_konfirmasi_pembatalan atau langsung dibatalkan)
        //             $statusReservasiBaru = $faker->randomElement(['menunggu_konfirmasi_pembatalan', 'dibatalkan']);

        //             // Update status reservasi
        //             DB::table('reservasi')->where('id_reservasi', $rid)->update([
        //                 'status_reservasi' => $statusReservasiBaru,
        //                 'updated_at' => $now,
        //             ]);

        //             // Insert pembayaran pembatalan
        //             $tglBatal = Carbon::now()->addDays($faker->numberBetween(1, 5))
        //                 ->setTime($faker->numberBetween(8, 20), $faker->numberBetween(0, 59));

        //             // Generate ORDER ID untuk pembatalan: REFUND-YYYYMMDD-XXXX
        //             $orderIdRefund = 'REFUND-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        //             $pembayaranBatalData = [
        //                 'id_admin' => !empty($adminIDs) ? $adminIDs[array_rand($adminIDs)] : null,
        //                 'id_reservasi' => $rid,
        //                 'jumlah' => $jumlahRefund,
        //                 'diskon_applied' => 0,
        //                 'status_pembayaran' => $statusPembatalanBaru,
        //                 'id_metodePembayaran' => $metodeIDs[array_rand($metodeIDs)],
        //                 'tanggal_pembayaran' => $tglBatal,
        //                 'bukti_pembayaran' => null,
        //                 'created_at' => $now,
        //                 'updated_at' => $now,
        //             ];

        //             if ($hasCol('pembayaran', 'order_id')) {
        //                 $pembayaranBatalData['order_id'] = $orderIdRefund;
        //             }

        //             DB::table('pembayaran')->insert($pembayaranBatalData);
        //             $pembayaranCount++;

        //             $barBatal->advance();
        //         }

        //         $barBatal->finish();
        //         $this->command->newLine();
        //     }

        //     $this->command->info('  - Pembayaran total tersisip: ' . $pembayaranCount);
        //     $this->command->info('  - Reservasi dibatalkan: ' . count($reservasiDibatalkan));
        // } else {
        //     $this->command->warn('! Tabel pembayaran / metodepembayaran tidak ditemukan, skip pembayaran');
        // }

        // // =========================
        // // 11) PILIHAN_LAYANAN
        // // =========================
        // if ($has('pilihan_layanan') && !empty($pelangganIDs) && !empty($slotMapByServiceTime)) {
        //     $this->command->info('> Seeding tabel pilihan_layanan ...');
        //     $target = (int) floor(count($pelangganIDs) * 0.2);
        //     $bar = $this->command->getOutput()->createProgressBar($target);
        //     $bar->start();

        //     for ($i = 0; $i < $target; $i++) {
        //         $pid = $pelangganIDs[array_rand($pelangganIDs)];
        //         $n = $faker->randomElement([1, 1, 2, 3]);
        //         for ($k = 0; $k < $n; $k++) {
        //             $tgl = Carbon::today()->addDays($faker->numberBetween(-20, 20))->toDateString();
        //             $tm = !empty($slotTimes) ? $slotTimes[array_rand($slotTimes)] : '09:00:00';

        //             $randLay = $layananIDs[array_rand($layananIDs)];
        //             $idSlot = $slotMapByServiceTime[$randLay][$tm] ?? null;
        //             if (!$idSlot) {
        //                 foreach ($slotMapByServiceTime as $layId => $map) {
        //                     if (isset($map[$tm])) {
        //                         $idSlot = $map[$tm];
        //                         break;
        //                     }
        //                 }
        //             }
        //             if (!$idSlot) {
        //                 continue;
        //             }

        //             $exists = DB::table('pilihan_layanan')
        //                 ->where('id_pelanggan', $pid)
        //                 ->where('id_slot', $idSlot)
        //                 ->where('tanggal_dipilih', $tgl)
        //                 ->exists();
        //             if ($exists) {
        //                 continue;
        //             }

        //             // Status pilihan: menunggu, diproses, ditolak, disetujui
        //             DB::table('pilihan_layanan')->insert([
        //                 'id_pelanggan' => $pid,
        //                 'id_slot' => $idSlot,
        //                 'tanggal_dipilih' => $tgl,
        //                 'status_pilihan' => $faker->randomElement(['menunggu', 'diproses', 'ditolak', 'disetujui', 'menunggu']),
        //                 'created_at' => $now,
        //                 'updated_at' => $now,
        //             ]);
        //         }
        //         $bar->advance();
        //     }
        //     $bar->finish();
        //     $this->command->newLine();
        //     $this->command->info('  - Pilihan_layanan: selesai');
        // } else {
        //     $this->command->warn('! Tabel pilihan_layanan tidak ditemukan atau data pendukung kosong, skip');
        // }

        // =========================
        // 12) SELESAI
        // =========================
        $this->command->newLine();
        $this->command->info('=== DummySeeder: SELESAI ===');
        $this->command->newLine();
        $this->command->comment('Tips: Login admin (tabel users) => app.admin@audrora.test / password');
        $this->command->comment('      Login admin (tabel admin) => admin@audrora.test      / password');
    }
}
