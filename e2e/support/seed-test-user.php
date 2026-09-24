<?php

/**
 * Menyiapkan satu akun pelanggan khusus E2E test.
 *
 * Script ini idempotent: dijalankan berulang kali hasilnya sama, dan HANYA menyentuh
 * baris milik akun test (tidak mengubah data pelanggan asli di database dev).
 *
 * Jalankan:  php e2e/support/seed-test-user.php
 * Dipanggil otomatis oleh e2e/auth.setup.js sebelum test berjalan.
 */

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Harus sinkron dengan konstanta PELANGGAN di e2e/support/test-data.js
const E2E_NAME = 'E2E Tester';
const E2E_EMAIL = 'e2e.tester@aurora.test';
const E2E_PASSWORD = 'E2ePassw0rd!';
const E2E_PHONE = '081200000000';

try {
    $user = User::updateOrCreate(
        ['email' => E2E_EMAIL],
        [
            'name' => E2E_NAME,
            'password' => Hash::make(E2E_PASSWORD),
            'role' => 'pelanggan',
            // AuthController menolak login bila email belum terverifikasi.
            'email_verified_at' => now(),
        ]
    );

    Pelanggan::updateOrCreate(
        ['user_id' => $user->id],
        [
            'nama' => E2E_NAME,
            'nomor_telepon' => E2E_PHONE,
            'status_pelanggan' => 'aktif',
            'tanggal_daftar' => now()->toDateString(),
            'email_verified_at' => now(),
        ]
    );

    fwrite(STDOUT, sprintf("[e2e-seed] OK - user id=%d email=%s siap dipakai.\n", $user->id, $user->email));
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, '[e2e-seed] GAGAL: '.$e->getMessage()."\n");
    exit(1);
}
