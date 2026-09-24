<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Reservasi;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\AdminNotifiable;


class KonfirmasiPembatalanController extends Controller
{
    use AdminNotifiable;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.konfirmasipembatalans.index');
    }

    /**
     * Ajax: Get all pending cancellations
     */
    public function ajaxGetPendingCancellations(Request $request)
    {
        try {
            $reservasiList = Reservasi::with([
                'pelanggan',
                'reservasiLayanan.layanan',
                'pembayaran',
            ])
                ->where('status_reservasi', 'menunggu_konfirmasi_pembatalan')
                ->orderBy('updated_at', 'desc')
                ->get();

            $data = [];

            foreach ($reservasiList as $reservasi) {
                $pembayaran = $reservasi->pembayaran()
                    ->orderBy('created_at', 'desc')
                    ->first();

                $layananUtama = $reservasi->reservasiLayanan->first();
                $namaLayanan = $layananUtama ? $layananUtama->layanan->nama_layanan : '-';
                $totalLayananLain = $reservasi->reservasiLayanan->count() - 1;

                // Ambil nomor telepon
                $noHp = $reservasi->no_hp ?? $reservasi->pelanggan->nomor_telepon ?? '-';

                $data[] = [
                    'id_reservasi' => $reservasi->id_reservasi,
                    'id_pelanggan' => $reservasi->id_pelanggan,
                    'nama_pelanggan' => $reservasi->pelanggan->nama ?? '-',
                    'no_hp' => $noHp,
                    'nama_layanan' => $namaLayanan,
                    'total_layanan_lain' => $totalLayananLain,
                    'tanggal_reservasi' => $reservasi->tanggal_reservasi
                        ? $reservasi->tanggal_reservasi->format('d M Y')
                        : '-',
                    'waktu_reservasi' => $reservasi->waktu_reservasi
                        ? substr($reservasi->waktu_reservasi, 0, 5)
                        : '-',
                    'total_harga' => (float) $reservasi->total_harga,
                    'status_pembayaran' => $pembayaran->status_pembayaran ?? 'belum_bayar',
                    'jumlah_dibayar' => $pembayaran ? (float) $pembayaran->jumlah : 0,
                    'tanggal_pembayaran' => $pembayaran && $pembayaran->created_at
                        ? $pembayaran->created_at->format('d M Y H:i')
                        : '-',
                    'catatan' => $reservasi->catatan ?? '-',
                    'updated_at' => $reservasi->updated_at->format('d M Y H:i'),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data berhasil dimuat',
            ]);

        } catch (Exception $e) {
            Log::error('Error ajaxGetPendingCancellations: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Konfirmasi pembatalan (setujui)
     */
    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $reservasi = Reservasi::with('pembayaran')->findOrFail($id);

            if ($reservasi->status_reservasi !== 'menunggu_konfirmasi_pembatalan') {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi bukan dalam status menunggu konfirmasi pembatalan',
                ], 400);
            }

            $pembayaran = $reservasi->pembayaran()->latest()->first();

            if (! $pembayaran) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan',
                ], 404);
            }

            $statusPembayaranSebelumnya = $pembayaran->status_pembayaran;
            $jumlahSebelumnya = $pembayaran->jumlah;

            // Update status reservasi menjadi dibatalkan
            $reservasi->status_reservasi = 'dibatalkan';
            $reservasi->save();

            // Logika berdasarkan status pembayaran
            if ($statusPembayaranSebelumnya === 'bayar_lunas') {
                // Jika LUNAS: Kembalikan setengah dari jumlah pembayaran
                $pembayaran->status_pembayaran = 'pembatalan_lunas';
                $pembayaran->jumlah = $jumlahSebelumnya / 2;
                $pembayaran->save();

                $message = 'Pembatalan berhasil dikonfirmasi. Setengah dari pembayaran (Rp '.number_format($pembayaran->jumlah, 0, ',', '.').') akan dikembalikan ke pelanggan.';

            } elseif ($statusPembayaranSebelumnya === 'bayar_dp') {
                // Jika DP: Uang tidak dikembalikan, jumlah tetap
                $pembayaran->status_pembayaran = 'pembatalan_dp';
                // jumlah tetap tidak berubah
                $pembayaran->save();

                $message = 'Pembatalan berhasil dikonfirmasi. Uang DP (Rp '.number_format($jumlahSebelumnya, 0, ',', '.').') tidak dikembalikan ke pelanggan.';

            } else {
                // Status pembayaran lain (belum_bayar, dll)
                $pembayaran->status_pembayaran = 'dibatalkan';
                $pembayaran->save();

                $message = 'Pembatalan berhasil dikonfirmasi. Tidak ada pembayaran yang perlu dikembalikan.';
            }

            DB::commit();

            // ===========================
            // NOTIFIKASI ADMIN: PEMBATALAN DISETUJUI
            // ===========================
            $customerName = $reservasi->pelanggan->nama ?? 'Pelanggan';
            $tglBooking = $reservasi->tanggal_reservasi ? $reservasi->tanggal_reservasi->format('d/m/Y') : '-';

            $this->notifyAdmins([
                'title'   => 'Pembatalan Disetujui',
                'message' => "Pembatalan reservasi {$customerName} (Tgl: {$tglBooking}) telah disetujui.",
                'type'    => 'danger',
                'link'    => route('booking.list', ['search' => $id]),
                'icon'    => 'fas fa-user-times',
            ]);


            Log::info("Pembatalan reservasi #{$id} disetujui. Status pembayaran: {$statusPembayaranSebelumnya} -> {$pembayaran->status_pembayaran}");


            return response()->json([
                'success' => true,
                'message' => $message,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error approve cancellation: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tolak pembatalan
     */
    public function reject(Request $request, $id)
    {
        try {
            $reservasi = Reservasi::findOrFail($id);

            if ($reservasi->status_reservasi !== 'menunggu_konfirmasi_pembatalan') {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservasi bukan dalam status menunggu konfirmasi pembatalan',
                ], 400);
            }

            // Kembalikan ke status sebelumnya (proses)
            $reservasi->status_reservasi = 'pending';
            $reservasi->save();

            // ===========================
            // NOTIFIKASI ADMIN: PEMBATALAN DITOLAK
            // ===========================
            $customerName = $reservasi->pelanggan->nama ?? 'Pelanggan';
            $tglBooking = $reservasi->tanggal_reservasi ? $reservasi->tanggal_reservasi->format('d/m/Y') : '-';

            $this->notifyAdmins([
                'title'   => 'Pembatalan Ditolak',
                'message' => "Permintaan pembatalan {$customerName} (Tgl: {$tglBooking}) telah ditolak.",
                'type'    => 'info',
                'link'    => route('booking.list', ['search' => $id]),
                'icon'    => 'fas fa-user-check',
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Pembatalan ditolak, reservasi dikembalikan ke status dibooking (pending)',
            ]);

        } catch (Exception $e) {
            Log::error('Error reject cancellation: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }
}
