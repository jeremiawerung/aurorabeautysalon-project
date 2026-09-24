<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Traits\AdminNotifiable;


class ReservasiController extends Controller
{
    use AdminNotifiable;

    public function booking()
    {
        return view('admin.booking');
    }

    public function bookingList()
    {
        return view('admin.booking.list');
    }

    public function getBookingListData(Request $request)
    {
        // Mirip dengan getReservasiBooking tapi return JSON untuk DataTables/Pagination
        $query = DB::table('reservasi')
            ->join('pelanggan', 'reservasi.id_pelanggan', '=', 'pelanggan.id_pelanggan')
            ->leftJoin('reservasi_layanan', 'reservasi.id_reservasi', '=', 'reservasi_layanan.id_reservasi')
            ->leftJoin('layanan', 'reservasi_layanan.id_layanan', '=', 'layanan.id_layanan')
            ->select(
                'reservasi.id_reservasi',
                'pelanggan.nama as pelanggan_nama',
                'reservasi.tanggal_reservasi',
                'reservasi.waktu_reservasi',
                'reservasi.status_reservasi',
                'reservasi.total_harga',
                DB::raw('GROUP_CONCAT(COALESCE(reservasi_layanan.nama_layanan_snapshot, layanan.nama_layanan) SEPARATOR ", ") as nama_layanan'),
                DB::raw('(SELECT COALESCE(SUM(jumlah + COALESCE(diskon_applied, 0)), 0) FROM pembayaran WHERE pembayaran.id_reservasi = reservasi.id_reservasi AND status_pembayaran IN ("bayar_lunas", "bayar_dp")) as total_bayar'),
                DB::raw('(SELECT status_pembayaran FROM pembayaran WHERE pembayaran.id_reservasi = reservasi.id_reservasi ORDER BY id_pembayaran DESC LIMIT 1) as last_payment_status')
            )
            ->groupBy(
                'reservasi.id_reservasi', 
                'pelanggan.nama', 
                'reservasi.tanggal_reservasi', 
                'reservasi.waktu_reservasi', 
                'reservasi.status_reservasi',
                'reservasi.total_harga'
            );

        // Filter Search
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('pelanggan.nama', 'like', "%{$term}%")
                  ->orWhere('reservasi.id_reservasi', 'like', "%{$term}%");
            });
        }

        // Filter Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('reservasi.tanggal_reservasi', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('reservasi.tanggal_reservasi', '<=', $request->end_date);
        }

        // Filter Status
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status !== 'all') {
                $query->where('reservasi.status_reservasi', $status);
            }
        }

        // Order
        $query->orderBy('reservasi.tanggal_reservasi', 'desc')
              ->orderBy('reservasi.waktu_reservasi', 'desc');

        // Pagination manually (since we group by, standard paginate might be tricky with joins, but let's try get() then slice for simplicity or simple paginate)
        // Using simple get() and returning all for client-side pagination as requested by pattern in other controllers?
        // Actually PembayaranController used get() and client-side pagination. Let's stick to that pattern for consistency.
        
        $data = $query->get()->map(function($row) {
            $status = strtolower($row->status_reservasi ?? '');
            $badgeClass = 'bg-secondary';
            $statusText = ucfirst($status);
            
            $totalBayar = (float) ($row->total_bayar ?? 0);
            $totalHarga = (float) ($row->total_harga ?? 0);
            $lastPaymentStatus = strtolower($row->last_payment_status ?? '');

            if ($status === 'pending') {
                if ($totalBayar >= $totalHarga && $totalHarga > 0) {
                     $badgeClass = 'bg-success';
                     $statusText = 'Lunas';
                } elseif ($lastPaymentStatus === 'bayar_dp' || $totalBayar > 0) {
                     $badgeClass = 'bg-info text-white';
                     $statusText = 'DP';
                } else {
                    $badgeClass = 'bg-warning text-dark';
                    $statusText = 'Belum Lunas';
                }
            } elseif ($status === 'proses') {
                $badgeClass = 'bg-info text-white';
                $statusText = 'Sedang Berjalan';
            } elseif ($status === 'selesai') {
                $badgeClass = 'bg-success';
                $statusText = 'Selesai';
            } elseif ($status === 'dibatalkan') {
                $badgeClass = 'bg-danger';
                $statusText = 'Dibatalkan';
            } elseif ($status === 'menunggu_konfirmasi_pembatalan') {
                $badgeClass = 'bg-warning text-dark';
                $statusText = 'Menunggu Konfirmasi Batal';
            }

            return [
                'id_reservasi' => $row->id_reservasi,
                'pelanggan_nama' => $row->pelanggan_nama,
                'layanan_nama' => $row->nama_layanan ?: '-',
                'tanggal' => date('d-m-Y', strtotime($row->tanggal_reservasi)),
                'jam' => substr($row->waktu_reservasi, 0, 5),
                'status_reservasi' => $status, // raw status for logic
                'formatted_status' => $statusText,
                'status_class' => $badgeClass
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getReservasiBooking(Request $request)
    {
        $date = $request->input('date') ?? now()->toDateString();

        $data = DB::table('reservasi_layanan')
            ->join('reservasi', 'reservasi_layanan.id_reservasi', '=', 'reservasi.id_reservasi')
            ->join('layanan', 'reservasi_layanan.id_layanan', '=', 'layanan.id_layanan')
            ->join('pelanggan', 'reservasi.id_pelanggan', '=', 'pelanggan.id_pelanggan')
            ->leftJoin('pembayaran', 'pembayaran.id_reservasi', '=', 'reservasi.id_reservasi')
            ->whereDate('reservasi.tanggal_reservasi', $date)
            ->select(
                'reservasi.id_reservasi',
                'pelanggan.nama',
                'reservasi.tanggal_reservasi as tanggal',
                'reservasi.waktu_reservasi as jam_mulai',
                'reservasi.status_reservasi',
                'pembayaran.status_pembayaran',
                DB::raw('COALESCE(reservasi_layanan.nama_layanan_snapshot, layanan.nama_layanan) as nama_layanan')
            )
            ->orderBy('reservasi.waktu_reservasi', 'asc')
            ->get()
            ->map(function ($row) {

                $statusReservasi = strtolower($row->status_reservasi ?? '');
                $statusPembayaran = strtolower($row->status_pembayaran ?? '');

                // ===============================
                //  PRIORITAS UTAMA: STATUS RESERVASI
                // ===============================

                // 1. PENDING
                if ($statusReservasi === 'pending') {

                    if ($statusPembayaran === 'bayar_lunas') {
                        $label = 'Sudah Dibayar Lunas';
                        $cls = 'bg-success';
                    } elseif ($statusPembayaran === 'bayar_dp') {
                        $label = 'Pembayaran DP';
                        $cls = 'bg-info';
                    } else {
                        $label = 'Menunggu Pembayaran';
                        $cls = 'bg-warning text-dark';
                    }
                }

                // 2. PROSES
                elseif ($statusReservasi === 'proses') {
                    $label = 'Sedang Berjalan';
                    $cls = 'bg-info text-white';
                }

                // 3. SELESAI
                elseif ($statusReservasi === 'selesai') {
                    $label = 'Selesai';
                    $cls = 'bg-success';
                }

                // 4. MENUNGGU KONFIRMASI PEMBATALAN
                elseif ($statusReservasi === 'menunggu_konfirmasi_pembatalan') {
                    $label = 'Menunggu Konfirmasi Pembatalan';
                    $cls = 'bg-warning text-dark';
                }

                // 5. DIBATALKAN
                elseif ($statusReservasi === 'dibatalkan') {

                    if ($statusPembayaran === 'pembatalan_lunas') {
                        $label = 'Pembatalan Lunas';
                        $cls = 'bg-warning text-dark';
                    } elseif ($statusPembayaran === 'pembatalan_dp') {
                        $label = 'Pembatalan DP';
                        $cls = 'bg-warning text-dark';
                    } else {
                        $label = 'Dibatalkan';
                        $cls = 'bg-danger';
                    }
                }

                // 6. STATUS TIDAK DIKENAL
                else {
                    $label = ucfirst($statusReservasi);
                    $cls = 'bg-secondary';
                }

                return [
                    'id_reservasi' => $row->id_reservasi,
                    'nama' => $row->nama,
                    'tanggal' => $row->tanggal,
                    'jam_mulai' => $row->jam_mulai,
                    'nama_layanan' => $row->nama_layanan,
                    'status_txt' => $label,
                    'status_cls' => $cls,
                ];
            });

        return response()->json(['data' => $data]);
    }

    public function getPengaturanBooking()
    {
        $data = DB::table('pengaturan_booking')
            ->select('booking_aktif', 'jam_mulai', 'jam_selesai', 'opsi_staff', 'dp_value', 'dp_tipe', 'maks_rentang_booking', 'interval_min_booking', 'kebijakan')
            ->first();

        return response()->json(['data' => $data]);
    }

    public function getPengaturanLokasi()
    {
        $data = DB::table('lokasi')
            ->select('nama', 'alamat', 'koordinat')
            ->first();

        return response()->json(['data' => $data]);
    }

    public function savePengaturanBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_aktif' => 'required|boolean',
            'jam_mulai' => 'nullable|regex:/^\d{2}:\d{2}(:\d{2})?$/',
            'jam_selesai' => 'nullable|regex:/^\d{2}:\d{2}(:\d{2})?$/',
            'opsi_staff' => 'required|boolean',
            'dp_value' => 'nullable|integer|min:0',
            'maks_rentang_booking' => 'required|integer|min:1',
            'interval_min_booking' => 'required|integer|min:1',
            'kebijakan' => 'nullable|string',
        ], [
            'jam_mulai.regex' => 'Format jam mulai harus H:i atau H:i:s (contoh: 09:00).',
            'jam_selesai.regex' => 'Format jam selesai harus H:i atau H:i:s (contoh: 17:00).',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();

            $old = DB::table('pengaturan_booking')->where('id', 1)->first();
            $data['jam_mulai'] = $data['jam_mulai'] ?? ($old->jam_mulai ?? null);
            $data['jam_selesai'] = $data['jam_selesai'] ?? ($old->jam_selesai ?? null);

            if (! empty($data['jam_mulai'])) {
                $data['jam_mulai'] = date('H:i', strtotime($data['jam_mulai']));
            }
            if (! empty($data['jam_selesai'])) {
                $data['jam_selesai'] = date('H:i', strtotime($data['jam_selesai']));
            }

            DB::table('pengaturan_booking')->updateOrInsert(
                ['id' => 1],
                [
                    'booking_aktif' => $data['booking_aktif'],
                    'jam_mulai' => $data['jam_mulai'],
                    'jam_selesai' => $data['jam_selesai'],
                    'opsi_staff' => $data['opsi_staff'],
                    'dp_value' => $data['dp_value'],
                    'maks_rentang_booking' => $data['maks_rentang_booking'],
                    'interval_min_booking' => $data['interval_min_booking'],
                    'kebijakan' => $data['kebijakan'] ?? null,
                    'updated_at' => now(),
                ]
            );

            return response()->json([
                'success' => 'Pengaturan booking berhasil disimpan.',
                'data' => [
                    'jam_mulai' => $data['jam_mulai'],
                    'jam_selesai' => $data['jam_selesai'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan Pengaturan Booking: '.$e->getMessage());

            return response()->json(['error' => 'Terjadi kesalahan: '.$e->getMessage()], 500);
        }
    }

    public function ajaxgaleripelanggan(Request $request)
    {
        try {
            $query = DB::table('galeri_fotos');

            if ($request->has('id_layanan') && $request->id_layanan) {

                $validator = Validator::make($request->all(), [
                    'id_layanan' => 'integer|exists:layanan,id_layanan',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 422);
                }

                $query->where('id_layanan', $request->id_layanan);
            }

            $images = $query->orderBy('id_layanan', 'asc')
                ->orderBy('urutan', 'asc')
                ->get(['id', 'id_layanan', 'path_url', 'keterangan']);

            $transformedImages = $images->map(function ($item) {
                if (! empty($item->path_url)) {
                    $item->path_url = asset($item->path_url);
                } else {
                    $item->path_url = asset('img/defaults/galeri.png');
                }

                return $item;
            });

            return response()->json([
                'success' => true,
                'data' => ['galeri' => $transformedImages],
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil Pengaturan Arsip: '.$e->getMessage().' di baris '.$e->getLine());

            return response()->json([
                'success' => false,
                'error' => 'Gagal memuat data arsip.',
            ], 500);
        }
    }

    public function getPengaturanArsip(Request $request)
    {
        try {
            $query = DB::table('galeri_fotos');

            if ($request->has('id_layanan') && $request->id_layanan) {

                $validator = Validator::make($request->all(), [
                    'id_layanan' => 'integer|exists:layanan,id_layanan',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 422);
                }

                $query->where('id_layanan', $request->id_layanan);
            }

            $images = $query->orderBy('id_layanan', 'asc')
                ->orderBy('urutan', 'asc')
                ->get(['id', 'id_layanan', 'path_url', 'keterangan']);

            $transformedImages = $images->map(function ($item) {
                if (! empty($item->path_url)) {
                    $item->path_url = asset($item->path_url);
                }

                return $item;
            });

            return response()->json([
                'success' => true,
                'data' => ['galeri' => $transformedImages],
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil Pengaturan Arsip: '.$e->getMessage().' di baris '.$e->getLine());

            return response()->json([
                'success' => false,
                'error' => 'Gagal memuat data arsip.',
            ], 500);
        }
    }

    public function savePengaturanArsip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_layanan' => 'required|integer|exists:layanan,id_layanan',

            'new_images' => 'nullable|array',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',

            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'integer|exists:galeri_fotos,id',

            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $idLayanan = $data['id_layanan'];

        $directoryPath = 'arsip';
        $savedFiles = [];

        DB::beginTransaction();
        try {
            if (! empty($data['deleted_images'])) {
                foreach ($data['deleted_images'] as $id) {
                    $foto = DB::table('galeri_fotos')->where('id', $id)->first();
                    if ($foto) {
                        $path = str_replace('/storage/', '', $foto->path_url);
                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                        DB::table('galeri_fotos')->where('id', $id)->delete();
                    }
                }
            }

            if ($request->hasFile('new_images')) {
                if (! Storage::disk('public')->exists($directoryPath)) {
                    Storage::disk('public')->makeDirectory($directoryPath);
                }

                $lastOrder = DB::table('galeri_fotos')
                    ->where('id_layanan', $idLayanan)
                    ->max('urutan') ?? -1;

                foreach ($request->file('new_images') as $index => $file) {
                    $filePath = $file->store($directoryPath, 'public');
                    $savedFiles[] = $filePath;

                    $lastOrder++;

                    DB::table('galeri_fotos')->insert([
                        'id_layanan' => $idLayanan,
                        'path_url' => '/storage/'.$filePath,
                        'keterangan' => $data['captions'][$index] ?? null,
                        'urutan' => $lastOrder,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => 'Pengaturan arsip berhasil disimpan.']);

        } catch (\Exception $e) {
            DB::rollBack();

            foreach ($savedFiles as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            Log::error('Gagal menyimpan Pengaturan Arsip: '.$e->getMessage().' di baris '.$e->getLine());

            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan data.'], 500);
        }
    }

    public function getPengaturanTentangKami()
    {
        $data = DB::table('tentang_kami')->select('id', 'deskripsi', 'hari_operasional')->where('id', 1)->first();

        if (! $data) {
            $data = (object) [
                'deskripsi' => '',
                'hari_operasional' => json_encode([]), // Kirim array JSON kosong
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function savePengaturanTentangKami(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deskripsi_booking' => 'nullable|string|max:5000',
            'jam' => 'nullable|array',
            'jam.*.status' => 'nullable|string|in:on', // Hanya izinkan 'on' (dari checkbox)
            'jam.*.mulai' => 'nullable|date_format:H:i',
            'jam.*.selesai' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $jam = $request->input('jam', []);
            $hari_operasional = [];

            foreach ($jam as $hari => $data) {
                if (isset($data['status']) && $data['status'] === 'on') {
                    $hari_operasional[$hari] = [
                        'mulai' => $data['mulai'] ?? '09:00',
                        'selesai' => $data['selesai'] ?? '19:00',
                    ];
                }
            }

            $dataToUpdate = [
                'deskripsi' => $request->input('deskripsi_booking'),

                'hari_operasional' => json_encode($hari_operasional),
                'updated_at' => now(),
            ];

            DB::table('tentang_kami')->updateOrInsert(
                ['id' => 1],
                $dataToUpdate
            );

            return response()->json(['success' => 'Pengaturan Tentang Kami berhasil disimpan.']);

        } catch (\Exception $e) {
            Log::error('Gagal menyimpan Pengaturan Tentang Kami: '.$e->getMessage());

            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan data.'], 500);
        }
    }

    public function savePengaturanLokasi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'koordinat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            DB::table('lokasi')->updateOrInsert(
                ['id' => 1],
                $validator->validated()
            );

            return response()->json(['success' => 'Pengaturan lokasi berhasil disimpan.']);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan Pengaturan Lokasi: '.$e->getMessage());

            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan data.'], 500);
        }
    }

    public function ajaxCheckReservasi(Request $request)
    {
        app(\App\Http\Middleware\CheckUserReservasiStatusMiddleware::class)
            ->handle($request, function ($req) {
                return response()->json(['ok' => true]);
            });

        return response()->json(['ok' => true]);
    }

    /**
     * Admin: Update Status Reservasi Manual
     */
    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_reservasi' => 'required|integer|exists:reservasi,id_reservasi',
            'status' => 'required|in:pending,proses,selesai,dibatalkan,menunggu_konfirmasi_pembatalan',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $id = $request->id_reservasi;
        $status = $request->status;

        $reservasi = \App\Models\Reservasi::find($id);

        // Validation: Cannot set 'selesai' if not fully paid
        if ($status === 'selesai') {
             $totalBayar = $reservasi->pembayaran()
                ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                ->sum('jumlah');

             if ($totalBayar < $reservasi->total_harga) {
                 return response()->json(['success' => false, 'message' => 'Harap lunasi pembayaran (termasuk biaya tambahan) sebelum menyelesaikan reservasi.'], 400);
             }
        }

        $reservasi->status_reservasi = $status;
        $reservasi->save();

        return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui.']);
    }

    /**
     * Admin: Add Additional Cost to Reservation
     */
    public function addAdditionalCost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_reservasi' => 'required|integer|exists:reservasi,id_reservasi',
            'nominal' => 'required|numeric|min:1',
            'catatan' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $reservasi = \App\Models\Reservasi::find($request->id_reservasi);

        $reservasi->total_harga += $request->nominal;

        if ($request->filled('catatan')) {
            $currentNote = $reservasi->catatan ?? '';
            $addNote = "Tambahan: Rp " . number_format($request->nominal, 0, ',', '.') . " (" . $request->catatan . ")";
            $reservasi->catatan = $currentNote ? $currentNote . " | " . $addNote : $addNote;
        }

        $reservasi->save();

        // Cek apakah dengan penambahan biaya ini, status pembayaran menjadi belum lunas?
        // Jika iya, ubah status reservasi menjadi 'pending' atau similar agar muncul di tagihan pelanggan
        $totalBayar = $reservasi->pembayaran()->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])->sum('jumlah');
        
        if ($reservasi->total_harga > $totalBayar) {
             // Jika sebelumnya lunas/proses, kembalikan ke pending agar bisa dibayar sisanya
             if ($reservasi->status_reservasi !== 'pending' && $reservasi->status_reservasi !== 'dibatalkan') {
                 $reservasi->status_reservasi = 'pending';
                 $reservasi->save();
             }
        }

        return response()->json([
            'success' => true,
            'message' => 'Biaya tambahan berhasil ditambahkan.',
            'total_baru' => $reservasi->total_harga
        ]);
    }

    /**
     * Admin: Mark as Paid (Manual Payment)
     */
    public function markAsPaid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_reservasi' => 'required|integer|exists:reservasi,id_reservasi',
            'jumlah' => 'required|numeric|min:0',
            'metode' => 'required|string', // e.g., 'Cash', 'Transfer Manual'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $id = $request->id_reservasi;
        
        // Cari ID metode pembayaran berdasarkan nama yang dikirim
        $metode = \App\Models\MetodePembayaran::where('nama', $request->metode)->first();
        
        if (!$metode) {
             return response()->json(['success' => false, 'message' => 'Metode pembayaran tidak valid/ditemukan.'], 400);
        }

        $reservasi = \App\Models\Reservasi::find($id);
        if (!$reservasi) {
             return response()->json(['success' => false, 'message' => 'Reservasi tidak ditemukan.'], 404);
        }

        DB::beginTransaction();
        try {
            // HITUNG SISA PEMBAYARAN SEBELUM TRANSAKSI INI
            $totalPaidAlready = $reservasi->pembayaran()
                ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                ->sum('jumlah');
            $totalDiskonAlready = $reservasi->pembayaran()
                ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                ->sum('diskon_applied');

            $remaining = max($reservasi->total_harga - $totalPaidAlready - $totalDiskonAlready, 0);
            
            // JIKA JUMLAH YANG DIBAYAR KURANG DARI SISA, ANGGAP SELISIHNYA SEBAGAI DISKON
            // Karena ini fitur "Tandai Lunas", berarti user menganggap transaksi selesai dengan nilai yg dibayarkan.
            $amountToPay = (float) $request->jumlah;
            $diskonApplied = max($remaining - $amountToPay, 0);

            // Create Pembayaran Record
            \App\Models\Pembayaran::create([
                'id_reservasi' => $id,
                'order_id' => 'MANUAL-' . time() . '-' . $id,
                'jumlah' => $amountToPay,
                'diskon_applied' => $diskonApplied, // SIMPAN DISKON OTOMATIS
                'status_pembayaran' => 'bayar_lunas',
                'metode' => $request->metode,
                'id_metodePembayaran' => $metode->id_metodePembayaran,
                'tanggal_pembayaran' => now(),
            ]);

            // Auto-update status reservasi if pending -> proses?
            // Or leave it to middleware/scheduler?
            // User request says "sehingga status reservasi bisa diubah ke selesai" impliying manual update
            // But usually "Mark as Paid" implies confirming the booking.
            
            // Auto-update status reservasi if pending -> proses?
            // Or leave it to middleware/scheduler?
            // User request says "sehingga status reservasi bisa diubah ke selesai" impliying manual update
            // But usually "Mark as Paid" implies confirming the booking.
            
            // Re-fetch reservasi just in case
            // $reservasi = \App\Models\Reservasi::find($id); // Already fetched above
            if ($reservasi->status_reservasi === 'pending') {
                // If paid, can we move to proses? Or remains pending until date?
                // Usually 'pending' means waiting for payment. If paid, it's 'proses' (confirmed/active).
                // Let's update to 'proses' if date is future or today.
                $reservasi->status_reservasi = 'proses';
                $reservasi->save();
            }

            // ===========================
            // NOTIFIKASI ADMIN: PEMBAYARAN MANUAL
            // ===========================
            $tglBooking = $reservasi->tanggal_reservasi ? $reservasi->tanggal_reservasi->format('d/m/Y') : '-';
            $this->notifyAdmins([
                'title'   => 'Pembayaran Manual Diterima',
                'message' => "Pelanggan {$reservasi->pelanggan->nama} melunasi Reservasi tgl {$tglBooking} secara manual (Metode: {$request->metode}).",
                'type'    => 'success',
                'link'    => route('booking.list', ['search' => $id]),
                'icon'    => 'fas fa-hand-holding-usd',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pembayaran manual berhasil dicatat.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }
}
