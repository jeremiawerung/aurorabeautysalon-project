<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startMonth = Carbon::now()->startOfMonth();
        $endDay = Carbon::now()->endOfDay();
        $start7Days = Carbon::today()->subDays(6)->startOfDay();
        $start30Days = Carbon::today()->subDays(29)->startOfDay();
        Carbon::setLocale('id');

        $shortIDR = function ($n) {
            $n = (float) $n;
            if ($n >= 1_000_000_000) {
                return number_format($n / 1_000_000_000, 2, ',', '.').' M';
            } elseif ($n >= 1_000_000) {
                return number_format($n / 1_000_000, 2, ',', '.').' Jt';
            } elseif ($n >= 1_000) {
                return number_format($n / 1_000, 0, ',', '.').' K';
            }

            return number_format($n, 0, ',', '.');
        };

        $totalPelanggan = (int) DB::table('pelanggan')->count();

        $totalTransaksi = (int) DB::table('pembayaran')
            ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp', 'pembatalan_lunas', 'pembatalan_dp'])
            ->count();

        $pemasukanHariIni = (float) DB::table('pembayaran')
            ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp', 'pembatalan_lunas', 'pembatalan_dp'])
            ->whereDate('tanggal_pembayaran', $today)
            ->selectRaw('COALESCE(SUM(jumlah - COALESCE(diskon_applied,0)),0) AS total')
            ->value('total');

        $layananAktif = (int) DB::table('layanan')
            ->whereRaw('LOWER(status_layanan) = "aktif"')
            ->count();

        $rerataTransaksi = (float) DB::table('pembayaran')
            ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp', 'pembatalan_lunas', 'pembatalan_dp'])
            ->selectRaw('AVG(jumlah - COALESCE(diskon_applied,0)) AS avg_total')
            ->value('avg_total');

        $pemasukanBulanIni = (float) DB::table('pembayaran')
            ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp', 'pembatalan_lunas', 'pembatalan_dp'])
            ->whereBetween('tanggal_pembayaran', [$startMonth, $endDay])
            ->selectRaw('COALESCE(SUM(jumlah - COALESCE(diskon_applied,0)),0) AS total')
            ->value('total');

        $stats = [
            'total_pelanggan' => number_format($totalPelanggan, 0, ',', '.'),
            'total_transaksi' => number_format($totalTransaksi, 0, ',', '.'),
            'pemasukan_hari_ini' => $shortIDR(max($pemasukanHariIni, 0)),
            'layanan_aktif' => number_format($layananAktif, 0, ',', '.'),
            'rerata_transaksi' => $shortIDR(max($rerataTransaksi, 0)),
            'pemasukan_bulan_ini' => $shortIDR(max($pemasukanBulanIni, 0)),
        ];

        $baseSelect = DB::table('reservasi as r')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 'r.id_pelanggan')
            ->leftJoin('reservasi_slot_jadwal as rsj', 'rsj.id_reservasi', '=', 'r.id_reservasi')
            ->leftJoin('slot_jadwal as sj', 'sj.id_slot', '=', 'rsj.id_slot')
            ->leftJoin('reservasi_layanan as rl', 'rl.id_reservasi', '=', 'r.id_reservasi')
            ->leftJoin('layanan as l', 'l.id_layanan', '=', 'rl.id_layanan')
            ->leftJoin('pembayaran as pb', 'pb.id_reservasi', '=', 'r.id_reservasi')
            ->selectRaw('
                r.id_reservasi,
                p.nama as nama_pelanggan,
                r.tanggal_reservasi,
                r.waktu_reservasi,
                sj.waktu as slot_waktu,
                r.status_reservasi,
                pb.status_pembayaran,
                GROUP_CONCAT(DISTINCT l.nama_layanan ORDER BY l.nama_layanan SEPARATOR ", ") AS layanan
            ')
            ->groupBy('r.id_reservasi', 'p.nama', 'r.tanggal_reservasi', 'r.waktu_reservasi', 'sj.waktu', 'r.status_reservasi', 'pb.status_pembayaran');

        $jadwalUpcoming = (clone $baseSelect)
            ->whereDate('r.tanggal_reservasi', '>=', $today)
            ->orderBy('r.tanggal_reservasi')
            ->orderBy('r.waktu_reservasi')
            ->limit(10)
            ->get();

        $jadwalRows = $jadwalUpcoming->count()
            ? $jadwalUpcoming
            : (clone $baseSelect)
                ->orderByDesc('r.tanggal_reservasi')
                ->orderByDesc('r.waktu_reservasi')
                ->limit(10)
                ->get();

        $mapStatus = function ($reservasi) {
            $statusReservasi = strtolower($reservasi->status_reservasi ?? '');
            $statusPembayaran = strtolower($reservasi->status_pembayaran ?? '');

            // --- PRIORITAS UTAMA: STATUS RESERVASI ---
            switch ($statusReservasi) {

                case 'pending':
                    // Cek payment di dalam pending
                    if ($statusPembayaran === 'bayar_lunas') {
                        return ['label' => 'Lunas', 'class' => 'bg-success'];
                    }
                    if ($statusPembayaran === 'bayar_dp') {
                        return ['label' => 'DP', 'class' => 'bg-info'];
                    }

                    return ['label' => 'Belum Lunas', 'class' => 'bg-warning text-dark'];

                case 'proses':
                    return ['label' => 'Sedang Berjalan', 'class' => 'bg-info text-white'];

                case 'selesai':
                    return ['label' => 'Selesai', 'class' => 'bg-success'];

                case 'menunggu_konfirmasi_pembatalan':
                    // Jika butuh cek pembayaran di sini juga bisa ditambah
                    return ['label' => 'Menunggu Konfirmasi Batal', 'class' => 'bg-warning text-dark'];

                case 'dibatalkan':
                    // Jika butuh cek apakah DP/Lunas cancel bisa taruh di sini
                    if ($statusPembayaran === 'pembatalan_lunas') {
                        return ['label' => 'Pembatalan Lunas', 'class' => 'bg-warning text-dark'];
                    }
                    if ($statusPembayaran === 'pembatalan_dp') {
                        return ['label' => 'Pembatalan DP', 'class' => 'bg-warning text-dark'];
                    }

                    return ['label' => 'Dibatalkan', 'class' => 'bg-danger'];
            }

            // --- JIKA STATUS RESERVASI TIDAK DIKENALI ---
            return ['label' => ucfirst($statusReservasi), 'class' => 'bg-secondary'];
        };

        $jadwal = $jadwalRows->map(function ($r) use ($mapStatus) {
            $tgl = $r->tanggal_reservasi ? Carbon::parse($r->tanggal_reservasi)->format('d-m-Y') : '-';
            $jamReservasi = $r->waktu_reservasi ? Carbon::parse($r->waktu_reservasi)->format('H:i:s') : '-';
            $slotWaktu = $r->slot_waktu ? Carbon::parse($r->slot_waktu)->format('H:i:s') : '-';
            $status = $mapStatus($r);

            return [
                'nama' => $r->nama_pelanggan,
                'layanan' => $r->layanan ?: '-',
                'tanggal' => $tgl,
                'waktu' => $jamReservasi,
                'slot' => $slotWaktu,
                'status_txt' => $status['label'],
                'status_cls' => $status['class'],
            ];
        });

        // ============= CHART: JUMLAH TRANSAKSI / HARI (7 HARI) =============
        $harianCounts = DB::table('pembayaran')
            ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
            ->whereBetween('tanggal_pembayaran', [$start7Days, $endDay])
            ->selectRaw('DATE(tanggal_pembayaran) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $chartHarianLabels = [];
        $chartHarianData = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $start7Days->copy()->addDays($i);
            $chartHarianLabels[] = $d->locale('id')->isoFormat('ddd');
            $chartHarianData[] = (int) ($harianCounts[$d->toDateString()] ?? 0);
        }

        // ============= CHART: RERATA TRANSAKSI / JAM (30 HARI) =============
        $distinctDays = (int) DB::table('pembayaran')
            ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
            ->whereBetween('tanggal_pembayaran', [$start30Days, $endDay])
            ->selectRaw('COUNT(DISTINCT DATE(tanggal_pembayaran)) AS d')
            ->value('d');

        $perHourCounts = DB::table('pembayaran')
            ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
            ->whereBetween('tanggal_pembayaran', [$start30Days, $endDay])
            ->selectRaw('HOUR(tanggal_pembayaran) as h, COUNT(*) as c')
            ->groupBy('h')
            ->pluck('c', 'h');

        $chartJamLabels = range(0, 23);
        $chartJamData = [];
        $denom = max($distinctDays, 1);

        foreach ($chartJamLabels as $h) {
            $count = (int) ($perHourCounts[$h] ?? 0);
            $avgHour = round($count / $denom, 2);
            $chartJamData[] = $avgHour;
        }

        return view('admin.dashboard', [
            'stats' => $stats,
            'jadwal' => $jadwal,
            'chartHarianLabels' => $chartHarianLabels,
            'chartHarianData' => $chartHarianData,
            'chartJamLabels' => $chartJamLabels,
            'chartJamData' => $chartJamData,
        ]);
    }
}
