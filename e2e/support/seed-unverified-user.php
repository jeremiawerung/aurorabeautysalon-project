<?php

/**
 * Menyiapkan satu akun pelanggan yang SENGAJA belum verifikasi email.
 *
 * Dipakai oleh test Login untuk skenario "login ditolak karena email belum
 * diverifikasi" (lihat AuthController::login — Auth::logout() dipanggil paksa
 * kalau hasVerifiedEmail() false). Idempotent: setiap dijalankan, email_verified_at
 * dipaksa kembali ke null (kalau test lain pernah memverifikasinya).
 *
 * Jalankan:  php e2e/support/seed-unverified-user.php
 */

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Harus sinkron dengan konstanta UNVERIFIED_PELANGGAN di e2e/support/test-data.js
const E2E_NAME = 'E2E Unverified Tester';
const E2E_EMAIL = 'e2e.unverified@aurora.test';
const E2E_PASSWORD = 'E2ePassw0rd!';
const E2E_PHONE = '081200000001';

try {
    $user = User::updateOrCreate(
        ['email' => E2E_EMAIL],
        [
            'name' => E2E_NAME,
            'password' => Hash::make(E2E_PASSWORD),
            'role' => 'pelanggan',
            'email_verified_at' => null,
        ]
    );

    Pelanggan::updateOrCreate(
        ['user_id' => $user->id],
        [
            'nama' => E2E_NAME,
            'nomor_telepon' => E2E_PHONE,
            'status_pelanggan' => 'aktif',
            'tanggal_daftar' => now()->toDateString(),
            'email_verified_at' => null,
        ]
    );

    fwrite(STDOUT, sprintf("[e2e-seed-unverified] OK - user id=%d email=%s (belum terverifikasi).\n", $user->id, $user->email));
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, '[e2e-seed-unverified] GAGAL: '.$e->getMessage()."\n");
    exit(1);
}
