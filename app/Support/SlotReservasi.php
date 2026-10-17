<?php

namespace App\Support;

use App\Models\Reservasi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya tempat buat ngecek slot bentrok dan nahan slot pas checkout.
 * Satu slot (layanan + jam) di satu tanggal cuma boleh dipegang 1 reservasi.
 */
class SlotReservasi
{
    // Batas bayar di Midtrans. Slot ditahan 5 menit lebih lama biar pembayaran
    // yang masuk di detik-detik terakhir gak kalah sama pelanggan lain.
    public const MENIT_BAYAR = 30;

    public const MENIT_TAHAN = 35;

    /**
     * Ada reservasi lain yang udah bayar atau lagi bayar slot ini?
     */
    public static function bentrok(int $idLayanan, string $tanggal, string $waktu, ?int $kecualiId = null): bool
    {
        $waktu = strlen($waktu) === 5 ? $waktu.':00' : $waktu;

        return Reservasi::query()
            ->whereDate('tanggal_reservasi', $tanggal)
            ->where('waktu_reservasi', $waktu)
            ->when($kecualiId, fn ($q) => $q->where('id_reservasi', '!=', $kecualiId))
            ->whereHas('layanan', fn ($q) => $q->where('layanan.id_layanan', $idLayanan))
            ->where(function ($q) {
                $q->whereIn('status_reservasi', ['sudah dibayar', 'proses'])
                    ->orWhere(function ($q) {
                        $q->where('status_reservasi', 'pending')
                            ->where(function ($q) {
                                $q->whereHas('pembayaran')
                                    ->orWhere('ditahan_sampai', '>', now());
                            });
                    });
            })
            ->exists();
    }

    /**
     * Kunci baris slot_jadwal biar checkout lain di slot yang sama antre.
     * Wajib dipanggil di dalam DB::transaction.
     */
    public static function kunci(int $idLayanan, string $waktu): ?object
    {
        $waktu = strlen($waktu) === 5 ? $waktu.':00' : $waktu;

        return DB::table('slot_jadwal')
            ->where('id_layanan', $idLayanan)
            ->where('waktu', $waktu)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Kunci semua slot milik reservasi-reservasi ini, cek bentrok, terus tahan slotnya.
     * Balikin reservasi yang bentrok (kalau ada), atau null kalau semua aman dan udah ditahan.
     *
     * @param  Collection<int, Reservasi>  $reservasiList  wajib udah load relasi reservasiLayanan
     */
    public static function tahanUntukCheckout(Collection $reservasiList): ?Reservasi
    {
        return DB::transaction(function () use ($reservasiList) {
            $dicek = $reservasiList->filter(fn ($r) => $r->tanggal_reservasi && $r->waktu_reservasi);

            // Kunci slot dengan urutan yang sama di semua request biar gak deadlock
            $dicek->flatMap(fn ($r) => $r->reservasiLayanan->map(fn ($rl) => $rl->id_layanan.'|'.$r->waktu_reservasi))
                ->unique()
                ->sort()
                ->each(function ($kunci) {
                    [$idLayanan, $waktu] = explode('|', $kunci);
                    self::kunci((int) $idLayanan, $waktu);
                });

            foreach ($dicek as $reservasi) {
                foreach ($reservasi->reservasiLayanan as $rl) {
                    if (self::bentrok((int) $rl->id_layanan, $reservasi->tanggal_reservasi->toDateString(), $reservasi->waktu_reservasi, $reservasi->id_reservasi)) {
                        return $reservasi;
                    }
                }
            }

            Reservasi::whereIn('id_reservasi', $dicek->pluck('id_reservasi'))
                ->update(['ditahan_sampai' => now()->addMinutes(self::MENIT_TAHAN)]);

            return null;
        });
    }
}
