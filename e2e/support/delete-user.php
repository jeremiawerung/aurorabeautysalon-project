<?php

/**
 * Menghapus satu akun pelanggan beserta seluruh data turunannya (pelanggan, reservasi,
 * reservasi_slot_jadwal, pembayaran) lewat cascade delete di level foreign key
 * (lihat migrasi pelanggan.user_id, reservasi.id_pelanggan, pembayaran.id_reservasi,
 * reservasi_slot_jadwal.id_reservasi — semuanya onDelete('cascade')).
 *
 * Dipakai sebagai cleanup setelah test Register/Booking selesai, supaya akun yang
 * dibuat lewat form registrasi sungguhan tidak menumpuk di database dev.
 *
 * Jalankan:  php e2e/support/delete-user.php <email>
 */

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$email = $argv[1] ?? null;

if (! $email) {
    fwrite(STDERR, "[delete-user] Penggunaan: php e2e/support/delete-user.php <email>\n");
    exit(1);
}

try {
    $user = User::where('email', $email)->first();

    if (! $user) {
        fwrite(STDOUT, "[delete-user] OK - user email={$email} sudah tidak ada (no-op).\n");
        exit(0);
    }

    $id = $user->id;
    $user->delete();

    fwrite(STDOUT, sprintf("[delete-user] OK - user id=%d email=%s dihapus beserta data turunannya.\n", $id, $email));
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, '[delete-user] GAGAL: '.$e->getMessage()."\n");
    exit(1);
}
