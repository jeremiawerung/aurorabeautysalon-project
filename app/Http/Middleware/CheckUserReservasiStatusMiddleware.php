<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Middleware untuk otomatis update status reservasi user
 * Hanya cek reservasi user yang sedang login
 */
class CheckUserReservasiStatusMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return $next($request);
        }

        // Throttle menggunakan session: hanya cek setiap 2 menit
        // $throttleMinutes = 2;
        // $lastChecked = session('reservasi.last_checked_at');

        // if ($lastChecked && now()->diffInMinutes($lastChecked) < $throttleMinutes) {
        //    return $next($request);
        // }

        $user = Auth::user();

        // Ambil id_pelanggan dari table pelanggan berdasarkan email yang sama
        $idPelanggan = null;

        if ($user) {
            $pelanggan = DB::table('pelanggan')
                ->where('user_id', $user->id)
                ->first();

            if ($pelanggan) {
                $idPelanggan = $pelanggan->id_pelanggan;
            }
        }

        // Jika tidak ditemukan pelanggan, skip update
        if (empty($idPelanggan)) {
            return $next($request);
        }

        try {
            // Tentukan timezone aplikasi
            $timezone = config('app.timezone', 'Asia/Jakarta');
            $now = Carbon::now($timezone);

            // ========================================
            // 1. UPDATE PENDING -> PROSES
            // ========================================
            // Kondisi:
            // - status_reservasi = 'pending'
            // - ada pembayaran dengan status 'settlement' atau 'capture' (Midtrans)
            // - waktu sekarang >= tanggal_reservasi + waktu_reservasi
            // - waktu sekarang < tanggal_reservasi + waktu_reservasi + durasi total layanan

            $pendingToProses = DB::table('reservasi as r')
                ->join('layanan_reservasi as lr', 'r.id_reservasi', '=', 'lr.id_reservasi')
                ->join('layanan as l', 'lr.id_layanan', '=', 'l.id_layanan')
                ->join('pembayaran as p', 'r.id_reservasi', '=', 'p.id_reservasi')
                ->where('r.id_pelanggan', $idPelanggan)
                ->where('r.status_reservasi', 'pending')
                ->whereIn('p.status_pembayaran', ['settlement', 'capture'])
                ->whereNotNull('r.tanggal_reservasi')
                ->whereNotNull('r.waktu_reservasi')
                ->select('r.id_reservasi', 'r.tanggal_reservasi', 'r.waktu_reservasi')
                ->selectRaw('SUM(l.durasi) as total_durasi')
                ->groupBy('r.id_reservasi', 'r.tanggal_reservasi', 'r.waktu_reservasi')
                ->get();

            foreach ($pendingToProses as $reservasi) {
                try {
                    // Parse tanggal dan waktu reservasi dengan fleksibel
                    $startDateTime = Carbon::parse($reservasi->tanggal_reservasi.' '.$reservasi->waktu_reservasi, $timezone);
                    $endDateTime = $startDateTime->copy()->addMinutes($reservasi->total_durasi);

                    // Cek apakah sudah masuk waktu layanan dan belum selesai
                    if ($now->gte($startDateTime) && $now->lt($endDateTime)) {
                        DB::table('reservasi')
                            ->where('id_reservasi', $reservasi->id_reservasi)
                            ->update([
                                'status_reservasi' => 'proses',
                                'updated_at' => $now,
                            ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parsing reservasi #'.$reservasi->id_reservasi.': '.$e->getMessage());

                    continue;
                }
            }

            // ========================================
            // 2. UPDATE PROSES -> SELESAI
            // ========================================
            // Kondisi:
            // - status_reservasi = 'proses'
            // - waktu sekarang >= tanggal_reservasi + waktu_reservasi + durasi total layanan

            $prosesToSelesai = DB::table('reservasi as r')
                ->join('layanan_reservasi as lr', 'r.id_reservasi', '=', 'lr.id_reservasi')
                ->join('layanan as l', 'lr.id_layanan', '=', 'l.id_layanan')
                ->where('r.id_pelanggan', $idPelanggan)
                ->where('r.status_reservasi', 'proses')
                ->whereNotNull('r.tanggal_reservasi')
                ->whereNotNull('r.waktu_reservasi')
                ->select('r.id_reservasi', 'r.tanggal_reservasi', 'r.waktu_reservasi')
                ->selectRaw('SUM(l.durasi) as total_durasi')
                ->groupBy('r.id_reservasi', 'r.tanggal_reservasi', 'r.waktu_reservasi')
                ->get();

            foreach ($prosesToSelesai as $reservasi) {
                try {
                    // Parse tanggal dan waktu reservasi dengan fleksibel
                    $startDateTime = Carbon::parse($reservasi->tanggal_reservasi.' '.$reservasi->waktu_reservasi, $timezone);
                    $endDateTime = $startDateTime->copy()->addMinutes($reservasi->total_durasi);

                    // Cek apakah sudah melewati waktu selesai
                    if ($now->gte($endDateTime)) {
                        // Cek status pembayaran lunas (Strict Check)
                        $isLunas = DB::table('pembayaran')
                            ->where('id_reservasi', $reservasi->id_reservasi)
                            ->where('status_pembayaran', 'bayar_lunas')
                            ->exists();

                        // TENTUKAN STATUS AKHIR
                        // Jika sudah lunas -> Selesai
                        // Jika belum lunas -> Tetap Proses
                        if ($isLunas) {
                            DB::table('reservasi')
                                ->where('id_reservasi', $reservasi->id_reservasi)
                                ->update([
                                    'status_reservasi' => 'selesai',
                                    'updated_at' => $now,
                                ]);
                        }
                        // ELSE: Jangan update apa-apa. Biarkan tetap 'proses'.
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parsing reservasi #'.$reservasi->id_reservasi.': '.$e->getMessage());

                    continue;
                }
            }

            // ========================================
            // 3. UPDATE PENDING -> SELESAI (Catch-up / Loncat Status)
            // ========================================
            // Kondisi:
            // - status_reservasi = 'pending'
            // - waktu sekarang >= waktu selesai
            // - SUDAH LUNAS (Syarat mutlak untuk Selesai)
            
            $pendingToSelesai = DB::table('reservasi as r')
                ->join('layanan_reservasi as lr', 'r.id_reservasi', '=', 'lr.id_reservasi')
                ->join('layanan as l', 'lr.id_layanan', '=', 'l.id_layanan')
                ->join('pembayaran as p', 'r.id_reservasi', '=', 'p.id_reservasi')
                ->where('r.id_pelanggan', $idPelanggan)
                ->where('r.status_reservasi', 'pending')
                ->where('p.status_pembayaran', 'bayar_lunas') // Wajib lunas
                ->whereNotNull('r.tanggal_reservasi')
                ->whereNotNull('r.waktu_reservasi')
                ->select('r.id_reservasi', 'r.tanggal_reservasi', 'r.waktu_reservasi')
                ->selectRaw('SUM(l.durasi) as total_durasi')
                ->groupBy('r.id_reservasi', 'r.tanggal_reservasi', 'r.waktu_reservasi')
                ->get();

            foreach ($pendingToSelesai as $reservasi) {
                try {
                    $startDateTime = Carbon::parse($reservasi->tanggal_reservasi.' '.$reservasi->waktu_reservasi, $timezone);
                    $endDateTime = $startDateTime->copy()->addMinutes($reservasi->total_durasi);

                    // Jika waktu sekarang sudah melewati waktu selesai
                    if ($now->gte($endDateTime)) {
                        DB::table('reservasi')
                            ->where('id_reservasi', $reservasi->id_reservasi)
                            ->update([
                                'status_reservasi' => 'selesai',
                                'updated_at' => $now,
                            ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parsing reservasi (Pending->Selesai) #'.$reservasi->id_reservasi.': '.$e->getMessage());
                    continue;
                }
            }

        } catch (\Throwable $e) {
            // Log error tapi jangan block request user
            Log::error('CheckUserReservasiStatusMiddleware error: '.$e->getMessage(), [
                'user_id' => $user->id ?? null,
                'email' => $user->email ?? null,
                'id_pelanggan' => $idPelanggan ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Simpan waktu cek terakhir di session untuk throttling
        session(['reservasi.last_checked_at' => now()]);

        return $next($request);
    }
}
