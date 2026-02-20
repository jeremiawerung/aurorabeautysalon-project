<?php

namespace App\Imports;

use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class PembayaranImport implements OnEachRow, WithHeadingRow
{
    protected int $inserted = 0;

    protected int $updated = 0;

    protected int $skipped = 0;

    protected array $errors = [];

    /**
     * Header Excel yang didukung (case-insensitive):
     * ID Pembayaran, ID Reservasi, Order ID, Jumlah, Diskon, Status, Metode Pembayaran, Tanggal Pembayaran (Y-m-d H:i:s)
     */
    public function onRow(Row $row)
    {
        $r = collect($row->toArray())->map(fn ($v) => is_string($v) ? trim($v) : $v);

        // ✅ Lewati baris kosong total
        if ($r->filter(fn ($v) => $v !== null && $v !== '')->isEmpty()) {
            return;
        }

        $idPembayaran = $r->get('id_pembayaran') ?? $r->get('id pembayaran');
        $idReservasi = $r->get('id_reservasi') ?? $r->get('id reservasi');
        $orderId = $r->get('order_id') ?? $r->get('order id');
        $jumlah = $r->get('jumlah');
        $diskon = $r->get('diskon') ?? 0;
        $status = $r->get('status');
        $metodeInput = $r->get('metode_pembayaran') ?? $r->get('metode pembayaran');
        $tglRaw = $r->get('tanggal_pembayaran')
            ?? $r->get('tanggal pembayaran (y-m-d h:i:s)')
            ?? $r->get('tanggal_pembayaran_y_m_d_h_i_s');

        // Validasi kolom wajib
        if (! $idReservasi || $jumlah === null || ! $status || ! $metodeInput) {
            $this->skipped++;
            $this->errors[] = [
                'row' => $row->getIndex(),
                'error' => 'Kolom id_reservasi/jumlah/status/metode_pembayaran wajib diisi.',
            ];

            return;
        }

        // Konversi jumlah & diskon
        $jumlah = (float) str_replace(',', '', $jumlah);
        $diskon = (float) str_replace(',', '', $diskon);

        // Konversi nama metode pembayaran → ID dari tabel
        $idMetode = null;
        if (is_numeric($metodeInput)) {
            $idMetode = (int) $metodeInput;
        } else {
            $metode = MetodePembayaran::whereRaw('LOWER(nama) = ?', [strtolower($metodeInput)])->first();
            if ($metode) {
                $idMetode = $metode->id_metodePembayaran;
            } else {
                $this->skipped++;
                $this->errors[] = [
                    'row' => $row->getIndex(),
                    'error' => "Metode pembayaran '{$metodeInput}' tidak ditemukan di database.",
                ];

                return;
            }
        }

        try {
            $tgl = $tglRaw ? Carbon::parse($tglRaw) : now();

            DB::transaction(function () use ($idPembayaran, $idReservasi, $orderId, $jumlah, $diskon, $status, $idMetode, $tgl) {
                if ($idPembayaran) {
                    $pb = Pembayaran::find($idPembayaran);
                    if ($pb) {
                        $pb->update([
                            'id_reservasi' => $idReservasi,
                            'order_id' => $orderId,
                            'jumlah' => $jumlah,
                            'diskon_applied' => $diskon,
                            'status_pembayaran' => $status,
                            'id_metodePembayaran' => $idMetode,
                            'tanggal_pembayaran' => $tgl,
                        ]);
                        $this->updated++;

                        return;
                    }
                }

                Pembayaran::create([
                    'id_reservasi' => $idReservasi,
                    'order_id' => $orderId,
                    'jumlah' => $jumlah,
                    'diskon_applied' => $diskon,
                    'status_pembayaran' => $status,
                    'id_metodePembayaran' => $idMetode,
                    'tanggal_pembayaran' => $tgl,
                ]);
                $this->inserted++;
            });
        } catch (\Throwable $e) {
            $this->skipped++;
            $this->errors[] = [
                'row' => $row->getIndex(),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getReport(): array
    {
        return [
            'inserted' => $this->inserted,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }
}
