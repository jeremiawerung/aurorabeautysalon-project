<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Notifikasi verifikasi email bawaan Laravel, dijalankan lewat queue.
 *
 * Tanpa ini, pengiriman email diproses SINKRON di dalam request registrasi (SMTP asli ke
 * Gmail bisa memakan >10 detik), sehingga pengguna menunggu lama sebelum halaman berpindah.
 * QUEUE_CONNECTION di .env sudah "database" — cukup pastikan worker jalan
 * (`php artisan queue:work`, atau `composer dev` yang sudah menjalankan queue:listen).
 */
class QueuedVerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;
}
