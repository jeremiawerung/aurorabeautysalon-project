<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckReservasiStatusMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Middleware ini akan:
     *  - pending + (bayar_lunas / bayar_dp) -> proses jika waktu slot SUDAH mulai dan BELUM selesai
     *  - proses -> selesai jika waktu slot + durasi SUDAH lewat
     *
     * Dijalankan otomatis setiap request yang memakai middleware ini,
     * dengan throttle 1 menit (biar nggak nembak query terus).
     */
    public function handle(Request $request, Closure $next)
    {
        // Biar nggak terlalu sering: maksimal 1x / menit
        $throttleMinutes = 1;
        $cacheKey = 'check_reservasi_last_run';

        $lastRun = Cache::get($cacheKey);
        if ($lastRun && Carbon::now()->diffInMinutes(Carbon::parse($lastRun)) < $throttleMinutes) {
            return $next($request);
        }

        try {
            // Pakai timezone aplikasi (bisa set di config/app.php atau langsung Asia/Jakarta)
            $now       = Carbon::now('Asia/Jakarta');
            $nowString = $now->format('Y-m-d H:i:s');

            // ========================================
            // 1. PENDING + (bayar_lunas | bayar_dp) -> PROSES
            // ========================================
            //
            // Syarat:
            // - r.status_reservasi = 'pending'
            // - ada pembayaran p.status_pembayaran IN ('bayar_lunas', 'bayar_dp')
            // - NOW >= (tanggal_reservasi + waktu slot)
            // - NOW <  (tanggal_reservasi + waktu slot + durasi layanan)
            //
            DB::table('reservasi as r')
                ->join('reservasi_slot_jadwal as rsj', 'r.id_reservasi', '=', 'rsj.id_reservasi')
                ->join('slot_jadwal as sj', 'rsj.id_slot', '=', 'sj.id_slot')
                ->join('layanan as l', 'sj.id_layanan', '=', 'l.id_layanan')
                ->join('pembayaran as p', 'r.id_reservasi', '=', 'p.id_reservasi')
                ->where('r.status_reservasi', 'pending')
                ->whereIn('p.status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                // waktu mulai slot <= sekarang
                ->whereRaw("
                    CONCAT(r.tanggal_reservasi, ' ', sj.waktu) <= ?
                ", [$nowString])
                // sekarang masih dalam durasi layanan
                ->whereRaw("
                    DATE_ADD(CONCAT(r.tanggal_reservasi, ' ', sj.waktu), INTERVAL l.durasi MINUTE) > ?
                ", [$nowString])
                ->update([
                    'r.status_reservasi' => 'proses',
                    'r.updated_at'       => $nowString,
                ]);

            // ========================================
            // 2. PROSES -> SELESAI
            // ========================================
            //
            // Syarat:
            // - r.status_reservasi = 'proses'
            // - NOW >= (tanggal_reservasi + waktu slot + durasi layanan)
            //
            DB::table('reservasi as r')
                ->join('reservasi_slot_jadwal as rsj', 'r.id_reservasi', '=', 'rsj.id_reservasi')
                ->join('slot_jadwal as sj', 'rsj.id_slot', '=', 'sj.id_slot')
                ->join('layanan as l', 'sj.id_layanan', '=', 'l.id_layanan')
                ->join('pembayaran as p', 'r.id_reservasi', '=', 'p.id_reservasi') // Join ke pembayaran
                ->where('r.status_reservasi', 'proses')
                ->where('p.status_pembayaran', 'bayar_lunas') // Hanya yang sudah lunas
                ->whereRaw("
                    DATE_ADD(CONCAT(r.tanggal_reservasi, ' ', sj.waktu), INTERVAL l.durasi MINUTE) <= ?
                ", [$nowString])
                ->update([
                    'r.status_reservasi' => 'selesai',
                    'r.updated_at'       => $nowString,
                ]);

            // Catat terakhir running supaya ketahan 1 menit
            Cache::put($cacheKey, $nowString, $throttleMinutes * 60);

        } catch (\Throwable $e) {
            // Jangan sampai nge-block request, cukup log error-nya
            Log::error('CheckReservasiStatusMiddleware error: '.$e->getMessage());
        }

        return $next($request);
    }
}
