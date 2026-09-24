<?php

/**
 * Menandai satu akun pelanggan sebagai sudah verifikasi email, langsung lewat DB.
 *
 * Dipakai oleh test Register/Booking: setelah mendaftar lewat form UI yang sebenarnya,
 * kita tidak punya akses ke inbox email asli (SMTP di .env memakai akun Gmail sungguhan),
 * jadi verifikasi disimulasikan dengan mengubah kolom `email_verified_at` secara langsung —
 * persis seperti yang diarahkan: "kalau tidak ada akses ubah verifikasi selain DB, buat
 * seolah sudah terverifikasi lewat DB".
 *
 * Jalankan:  php e2e/support/verify-user.php <email>
 */

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$email = $argv[1] ?? null;

if (! $email) {
    fwrite(STDERR, "[verify-user] Penggunaan: php e2e/support/verify-user.php <email>\n");
    exit(1);
}

try {
    $user = User::where('email', $email)->firstOrFail();
    $user->email_verified_at = now();
    $user->save();

    Pelanggan::where('user_id', $user->id)->update(['email_verified_at' => now()]);

    fwrite(STDOUT, sprintf("[verify-user] OK - user id=%d email=%s ditandai terverifikasi.\n", $user->id, $user->email));
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, '[verify-user] GAGAL: '.$e->getMessage()."\n");
    exit(1);
}
