<?php

// BookingController.php

namespace App\Http\Controllers;

use App\Models\KategoriLayanan;
use App\Models\Layanan;
use App\Models\MetodePembayaran;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\User;
use App\Notifications\AdminNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\AdminNotifiable;


class BookingController extends Controller
{
    use AdminNotifiable;

    /* =========================
        DATABASE / AUTH HELPERS
    ========================== */

    /**
     * Mengambil ID Pelanggan yang sedang login.
     *
     * @return int|null
     */
    private function getCurrentPelangganId()
    {
        // Asumsi relasi User -> Pelanggan ada
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        // Pastikan model User memiliki relasi pelanggan()
        $pelanggan = $user->pelanggan;
        if (! $pelanggan) {
            return null;
        }

        return $pelanggan->id_pelanggan;
    }

    /**
     * Memparsing JSON Hari Operasional dari database.
     *
     * @param  string  $hariStr
     * @return array
     */
    private function parseHariOperasionalJson($hariStr)
    {
        try {
            $decoded = json_decode($hariStr, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Gagal parse JSON hari_operasional: '.$e->getMessage());

            return [];
        }
    }

    /* =========================
        AJAX CART MANAGEMENT (STEP 1 & SIDEBAR)
    ========================== */

    public function ajaxGetCart(Request $request)
    {
        try {
            $id_pelanggan = $this->getCurrentPelangganId();

            if (! $id_pelanggan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pelanggan tidak ditemukan. Akun Anda belum terhubung ke data pelanggan.',
                ], 400);
            }

            // Ambil reservasi pelanggan dengan status 'pending' yang TIDAK memiliki pembayaran
            $reservasiDiproses = Reservasi::with('layanan')
                ->where('id_pelanggan', $id_pelanggan)
                ->where('status_reservasi', 'pending')
                                            // **FILTER BARU: Pastikan TIDAK ADA entri di tabel 'pembayaran'**
                ->whereDoesntHave('pembayaran') // Asumsi ada relasi 'pembayaran' di model Reservasi
                ->get();

            if ($reservasiDiproses->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'id_pelanggan' => $id_pelanggan,
                    'cart' => [],
                    'total_harga' => 0,
                    'active_reservasi_ids' => [],
                    'message' => 'Belum ada layanan di keranjang yang menunggu pembayaran.',
                ]);
            }

            $cartData = [];
            $totalHarga = 0;

            foreach ($reservasiDiproses as $reservasi) {
                // Ambil layanan pertama (asumsi satu reservasi untuk satu layanan)
                $layanan = $reservasi->layanan->first();

                // Karena kita menggunakan whereDoesntHave('pembayaran'), kita TAHU pembayaran TIDAK ADA (NULL)
                $statusPembayaran = 'belum_bayar'; // Langsung set status pembayaran

                if ($layanan) {
                    // Use pivot data if available (snapshot), otherwise master data
                    $harga = (float) ($layanan->pivot->harga_deal ?? $layanan->harga);
                    $nama = $layanan->pivot->nama_layanan_snapshot ?? $layanan->nama_layanan;
                    
                    $totalHarga += $harga;

                    $cartData[] = [
                        'id_layanan' => (int) $layanan->id_layanan,
                        'nama_layanan' => $nama,
                        'harga' => $harga,
                        'durasi' => (int) $layanan->durasi,
                        'gambar' => $layanan->gambar
                            ? asset('storage/'.$layanan->gambar)
                            : asset('img/favicon.svg'),
                        'reservasi' => [
                            'id_reservasi' => (int) $reservasi->id_reservasi,
                            'tanggal_reservasi' => $reservasi->tanggal_reservasi ? Carbon::parse($reservasi->tanggal_reservasi)->toDateString() : null,
                            'waktu_reservasi' => $reservasi->waktu_reservasi ? substr($reservasi->waktu_reservasi, 0, 5) : null,
                            'status_reservasi' => $reservasi->status_reservasi,
                            'status_pembayaran' => $statusPembayaran, // Selalu 'belum_bayar'
                            'catatan' => $reservasi->catatan,
                        ],
                    ];
                }
            }

            $activeReservasiIds = $reservasiDiproses->pluck('id_reservasi')->toArray();

            return response()->json([
                'success' => true,
                'id_pelanggan' => $id_pelanggan,
                'cart' => $cartData,
                'total_harga' => $totalHarga,
                'active_reservasi_ids' => $activeReservasiIds,
                'message' => 'Keranjang belum terbayar berhasil dimuat.',
            ]);
        } catch (Exception $e) {
            // Jangan lupa untuk mengimpor facade Log di awal file (use Illuminate\Support\Facades\Log;)
            Log::error('Error ajaxGetCart: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil keranjang.',
            ], 500);
        }
    }

    public function ajaxManageCart(Request $request)
    {
        try {
            $request->validate([
                'id_reservasi' => 'nullable|integer|exists:reservasi,id_reservasi',
                'id_layanan' => 'required|integer|exists:layanan,id_layanan',
                'action' => 'required|in:add,remove',
            ]);

            $id_pelanggan = $this->getCurrentPelangganId();

            if (! $id_pelanggan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pelanggan tidak ditemukan. Akun Anda belum terhubung ke data pelanggan.',
                ], 400);
            }

            $id_layanan = $request->id_layanan;
            $action = $request->action;
            $id_reservasi_request = $request->id_reservasi;

            $message = '';
            $current_reservasi_id = $id_reservasi_request;

            $layanan_item = Layanan::find($id_layanan);
            if (! $layanan_item) {
                return response()->json(['success' => false, 'message' => 'Layanan tidak valid.'], 404);
            }

            // --- Logika ADD: SELALU BUAT RESERVASI BARU ---
            if ($action === 'add') {
                DB::beginTransaction();
                try {
                    $reservasi = Reservasi::create([
                        'id_pelanggan' => $id_pelanggan,
                        'status_reservasi' => 'pending', // Status keranjang
                        'total_harga' => $layanan_item->harga,
                    ]);

                    $current_reservasi_id = $reservasi->id_reservasi;
                    $reservasi->layanan()->attach($id_layanan, [
                        'harga_deal' => $layanan_item->harga,
                        'nama_layanan_snapshot' => $layanan_item->nama_layanan
                    ]);

                    // Membuat entri kosong di reservasi_slot_jadwal (Opsional, tergantung skema)
                    // DB::table('reservasi_slot_jadwal')->insert([
                    //     'id_reservasi' => $current_reservasi_id,
                    //     'id_slot' => null, // Jika id_slot boleh null
                    //     'created_at' => Carbon::now(),
                    //     'updated_at' => Carbon::now(),
                    // ]);

                    $message = 'Layanan berhasil ditambahkan. Reservasi baru dibuat.';
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error ADD to Cart: '.$e->getMessage());

                    return response()->json(['success' => false, 'message' => 'Gagal membuat reservasi baru.'], 500);
                }

                // --- Logika REMOVE ---
            } elseif ($action === 'remove') {
                if (empty($id_reservasi_request)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ID Reservasi wajib untuk penghapusan.',
                    ], 400);
                }

                $reservasi = Reservasi::where('id_reservasi', $id_reservasi_request)
                    ->where('id_pelanggan', $id_pelanggan)
                    ->whereIn('status_reservasi', ['diproses', 'pending'])
                    ->first();

                if (! $reservasi) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Reservasi (Keranjang) tidak ditemukan atau bukan milik Anda.',
                    ], 404);
                }

                DB::beginTransaction();
                try {
                    // Cek apakah ada layanan lain di reservasi ini (Jika Anda mengizinkan multi-layanan dalam 1 reservasi)
                    $layananCount = $reservasi->layanan()->count();

                    // Logika aslinya adalah menghapus seluruh reservasi jika item dihapus
                    $reservasi->delete();

                    $message = 'Layanan berhasil dihapus dari keranjang.';
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error REMOVE from Cart: '.$e->getMessage());

                    return response()->json(['success' => false, 'message' => 'Gagal menghapus layanan.'], 500);
                }
            }

            // Ambil data keranjang terbaru setelah aksi
            $response = $this->ajaxGetCart($request)->getData(true);
            $response['message'] = $message;
            $response['id_layanan_updated'] = $id_layanan;
            $response['id_reservasi_updated'] = $current_reservasi_id;

            return response()->json($response);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal: '.array_values($e->errors())[0][0],
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error ajaxManageCart: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses layanan: '.$e->getMessage(),
            ], 500);
        }
    }

    /* =========================
        AJAX SCHEDULE MANAGEMENT (STEP 2)
    ========================== */

    public function ajaxUpdateSchedule(Request $request)
    {
        try {
            $id_pelanggan = $this->getCurrentPelangganId();

            if (! $id_pelanggan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pelanggan tidak ditemukan. Akun Anda belum terhubung ke data pelanggan.',
                ], 400);
            }

            $request->validate([
                'id_reservasi' => 'required|integer|exists:reservasi,id_reservasi',
                'tanggal' => 'required|date_format:Y-m-d', // Diubah menjadi required
                'time' => 'required|date_format:H:i',   // Diubah menjadi required
            ]);

            $id_reservasi = $request->id_reservasi;
            $tanggal = $request->tanggal;
            $waktu = $request->time.':00'; // Tambahkan detik untuk format time database

            $reservasi = Reservasi::with('layanan')->where('id_reservasi', $id_reservasi)
                ->where('id_pelanggan', $id_pelanggan)
                ->whereIn('status_reservasi', ['diproses', 'pending'])
                ->firstOrFail();

            $layanan = $reservasi->layanan->first();
            if (! $layanan) {
                return response()->json(['success' => false, 'message' => 'Layanan terkait tidak ditemukan.'], 404);
            }
            $idLayanan = $layanan->id_layanan;

            DB::beginTransaction();
            try {
                // 1. Lock Slot Resource (Mutex for this slot/time)
                $waktu_h_i_s = $request->time . ':00';
                $slot = DB::table('slot_jadwal')
                    ->where('id_layanan', $idLayanan)
                    ->where('waktu', $waktu_h_i_s)
                    ->lockForUpdate() // Lock baris ini agar transaksi lain menunggu
                    ->first();

                if (! $slot) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Slot jadwal tidak ditemukan untuk waktu yang dipilih.'], 404);
                }

                // 2. Double Check Ketersediaan (Inside Transaction)
                $isSlotAvailable = $this->checkGlobalSlotAvailability($idLayanan, $tanggal, $request->time, $id_reservasi);
                
                if (! $isSlotAvailable) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Slot waktu yang Anda pilih baru saja terisi oleh pengguna lain.'], 409);
                }

                // Hapus relasi lama
                DB::table('reservasi_slot_jadwal')->where('id_reservasi', $id_reservasi)->delete();

                // Buat relasi baru
                DB::table('reservasi_slot_jadwal')->insert([
                    'id_reservasi' => $id_reservasi,
                    'id_slot' => $slot->id_slot,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                // 3. Update Reservasi
                $reservasi->tanggal_reservasi = $tanggal;
                $reservasi->waktu_reservasi = $waktu_h_i_s;
                $reservasi->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Jadwal reservasi berhasil diperbarui.',
                    'reservasi' => [
                        'tanggal_reservasi' => $reservasi->tanggal_reservasi,
                        'waktu_reservasi' => substr($reservasi->waktu_reservasi, 0, 5) ?? null,
                    ],
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Error ajaxUpdateSchedule: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jadwal. Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper untuk mengecek ketersediaan slot di sisi server.
     * Hanya mengecek reservasi untuk ID Layanan yang sama.
     */
    // BookingController.php

    // ... (kode lainnya)

    /**
     * Helper untuk mengecek ketersediaan slot di sisi server.
     * Hanya mengecek reservasi untuk ID Layanan yang sama.
     */
    protected function checkGlobalSlotAvailability($idLayanan, $tanggal, $waktu_h_i, $ignoreReservasiId)
    {
        // 1. Cari ID Slot (Tidak berubah)
        $waktu_h_i_s = $waktu_h_i.':00';
        $slot = DB::table('slot_jadwal')
            ->where('id_layanan', $idLayanan)
            ->where('waktu', $waktu_h_i_s)
            ->first();

        if (! $slot) {
            return false; // Slot tidak ada untuk layanan ini pada waktu tersebut
        }

        // 2. Cek apakah ada reservasi lain yang sudah menggunakan slot ini
        $reservasiCount = DB::table('reservasi')
            ->join('reservasi_layanan', 'reservasi.id_reservasi', '=', 'reservasi_layanan.id_reservasi')
        // Memperbaiki Ambiguity Kolom:
            ->where('reservasi.tanggal_reservasi', $tanggal)
            ->where('reservasi.waktu_reservasi', $waktu_h_i_s)

        // Perbaikan di sini: Tambahkan prefix 'reservasi.'
            ->where('reservasi.id_reservasi', '!=', $ignoreReservasiId)

            ->whereIn('reservasi.status_reservasi', ['sudah dibayar', 'proses'])
            ->orWhere(function($q) use ($tanggal, $waktu_h_i_s, $ignoreReservasiId, $idLayanan) {
                $q->where('reservasi.tanggal_reservasi', $tanggal)
                  ->where('reservasi.waktu_reservasi', $waktu_h_i_s)
                  ->where('reservasi.id_reservasi', '!=', $ignoreReservasiId)
                  ->where('reservasi.status_reservasi', 'pending')
                  ->whereExists(function ($query) {
                      $query->select(DB::raw(1))
                            ->from('pembayaran')
                            ->whereColumn('pembayaran.id_reservasi', 'reservasi.id_reservasi');
                  });
            })
            ->where('reservasi_layanan.id_layanan', $idLayanan)
            ->lockForUpdate()
            ->count();

        return $reservasiCount === 0;
    }

    public function getRentangSlotDinamis(Request $request)
    {
        try {
            $request->validate([
                'id_layanan' => 'required|integer',
            ]);

            $idLayanan = $request->input('id_layanan');
            // $idLayanan = '1';

            $now = Carbon::now('Asia/Jakarta');
            Carbon::setLocale('id');

            // 1. Ambil Pengaturan & Jam Operasional
            $pengaturan = DB::table('pengaturan_booking')->first();
            $tentangKami = DB::table('tentang_kami')->first();

            if (! $pengaturan || ! $tentangKami) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengaturan tidak ditemukan.',
                ], 500);
            }

            $maksRentangBooking = $pengaturan->maks_rentang_booking;
            $intervalMinBooking = $pengaturan->interval_min_booking;

            // 2. Ambil Slot Dasar untuk Layanan ini
            $baseSlots = DB::table('slot_jadwal')
                ->where('id_layanan', $idLayanan)
                ->where('status_slot', 'aktif')
                ->orderBy('waktu')
                ->pluck('waktu', 'id_slot')
                ->toArray();

            if (empty($baseSlots)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada slot jadwal yang disetel untuk layanan ini.',
                    'data_slot_per_tanggal' => [],
                ], 200);
            }

            $jadwalBukaJson = $this->parseHariOperasionalJson($tentangKami->hari_operasional);

            $tanggalMulai = $now->copy()->toDateString();
            $tanggalSelesai = $now->copy()->addDays($maksRentangBooking - 1)->toDateString();

            // 3. Ambil Semua Reservasi Aktif untuk Layanan ini (ID LAYANAN SAJA)
            $semuaReservasiAktif = DB::table('reservasi')
                ->whereBetween('tanggal_reservasi', [$tanggalMulai, $tanggalSelesai])
                ->where(function($q) {
                    $q->whereIn('status_reservasi', ['sudah dibayar', 'proses'])
                      ->orWhere(function($subq) {
                          $subq->where('status_reservasi', 'pending')
                               ->whereExists(function ($query) {
                                   $query->select(DB::raw(1))
                                         ->from('pembayaran')
                                         ->whereColumn('pembayaran.id_reservasi', 'reservasi.id_reservasi');
                               });
                      });
                })
                ->join('reservasi_layanan', 'reservasi.id_reservasi', '=', 'reservasi_layanan.id_reservasi')
                ->where('reservasi_layanan.id_layanan', $idLayanan) // Pengecekan Ketersediaan berdasarkan ID LAYANAN
                ->join('reservasi_slot_jadwal', 'reservasi.id_reservasi', '=', 'reservasi_slot_jadwal.id_reservasi')
                ->whereIn('reservasi_slot_jadwal.id_slot', array_keys($baseSlots))
                ->select(
                    'reservasi.tanggal_reservasi',
                    'reservasi.waktu_reservasi',
                    'reservasi_slot_jadwal.id_slot',
                    'reservasi.id_reservasi',
                    'reservasi.id_pelanggan',
                    'reservasi.status_reservasi',
                    'reservasi.catatan',
                    'reservasi.total_harga'
                )
                ->get();

            $rentangJadwal = [];

            // 4. Proses Iterasi Tanggal dan Slot
            for ($i = 0; $i < $maksRentangBooking; $i++) {
                $tanggalCek = $now->copy()->addDays($i);
                $tanggalCekStr = $tanggalCek->toDateString();
                $hariNamaCarbon = strtolower($tanggalCek->translatedFormat('l'));
                $hariNamaLengkap = $tanggalCek->translatedFormat('l, d F Y');

                $jamOperasional = $jadwalBukaJson[$hariNamaCarbon] ?? null;

                if (empty($jamOperasional) || ! isset($jamOperasional['mulai']) || ! isset($jamOperasional['selesai'])) {
                    continue; // Skip jika hari tutup
                }

                $jamMulaiHariIni = Carbon::parse($jamOperasional['mulai']);
                $jamSelesaiHariIni = Carbon::parse($jamOperasional['selesai']);

                $reservasiHariIni = $semuaReservasiAktif
                    ->where('tanggal_reservasi', $tanggalCekStr);

                $jadwalList = [];

                foreach ($baseSlots as $idSlot => $slotTime) {
                    $isDisabled = false;
                    $reservasiDetail = null;

                    $slotDateTime = Carbon::parse($tanggalCekStr.' '.$slotTime, 'Asia/Jakarta');

                    // Cek Batas Jam Operasional
                    $slotTimeCarbon = Carbon::parse($slotTime);
                    if ($slotTimeCarbon->lt($jamMulaiHariIni) || $slotTimeCarbon->gt($jamSelesaiHariIni)) {
                        continue;
                    }

                    // Cek Reservasi yang Sudah Ada (Global Check - berdasarkan ID Layanan)
                    $reservasiDiSlot = $reservasiHariIni
                        ->where('waktu_reservasi', $slotTime)
                        ->first();

                    if ($reservasiDiSlot) {
                        $isDisabled = true;
                        $reservasiDetail = [
                            'id_reservasi' => $reservasiDiSlot->id_reservasi,
                            'id_pelanggan' => $reservasiDiSlot->id_pelanggan,
                            'status_reservasi' => $reservasiDiSlot->status_reservasi,
                            'catatan' => $reservasiDiSlot->catatan,
                            'total_harga' => (float) $reservasiDiSlot->total_harga,
                        ];
                    }

                    // Cek Batas Waktu Minimum Booking
                    if ($tanggalCekStr === $now->toDateString()) {
                        if ($slotDateTime->lt($now->copy()->addMinutes($intervalMinBooking))) {
                            $isDisabled = true;
                        }
                    }

                    $slotOutput = [
                        'id_layanan' => $idLayanan,
                        'id_slot' => $idSlot,
                        'time' => substr($slotTime, 0, 5), // Format HH:MM
                        'disabled' => $isDisabled,
                    ];

                    if ($reservasiDetail) {
                        $slotOutput['reservasi'] = $reservasiDetail;
                    }

                    $jadwalList[] = $slotOutput;
                }

                $rentangJadwal[] = [
                    'tanggal' => $hariNamaLengkap,
                    'date_ymd' => $tanggalCekStr,
                    'slots' => $jadwalList,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Slot jadwal berhasil dimuat',
                'rentang_hari' => $maksRentangBooking.' hari',
                'interval_min_booking' => $intervalMinBooking.' menit',
                'jadwalbuka' => $jadwalBukaJson,
                'data_slot_per_tanggal' => $rentangJadwal,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getRentangSlotDinamis: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
                'debug' => $e->getMessage().' on line '.$e->getLine(),
            ], 500);
        }
    }

    /* =========================
        BOOKING FLOW (VIEW CONTROLLERS)
    ========================== */

    public function chooseCategory()
    {
        $categories = KategoriLayanan::withCount('layanan')->get();

        return view('pelanggan.booking-pilih-kategori-layanan', compact('categories'));
    }

    public function step1($kategori)
    {
        $services = Layanan::whereHas('kategoriLayanan', function ($q) use ($kategori) {
            $q->where('nama', $kategori);
        })
            ->where('status_layanan', 'aktif')
            ->get();

        $category = KategoriLayanan::where('nama', $kategori)->firstOrFail();

        return view('pelanggan.booking-step1', compact('services', 'category'));
    }

    public function step2()
    {
        $id_pelanggan = $this->getCurrentPelangganId();

        if (! $id_pelanggan) {
            return redirect()->route('home')->with('error', 'Data pelanggan tidak ditemukan.');
        }

        $pengaturan = DB::table('pengaturan_booking')->first();
        $bookingAktif = $pengaturan ? $pengaturan->booking_aktif : true;
        
        // Jika booking tidak aktif, redirect dengan pesan
        if (!$bookingAktif) {
            return redirect()->route('daftar-layanan.index')
                ->with('error', 'Mohon maaf, sistem booking sedang tidak aktif. Silakan hubungi admin untuk informasi lebih lanjut.');
        }

        // Cek Jam Mulai Booking
        if ($pengaturan && $pengaturan->jam_mulai) {
            $now = Carbon::now('Asia/Jakarta');
            $jamMulai = Carbon::parse($pengaturan->jam_mulai, 'Asia/Jakarta')->setDate($now->year, $now->month, $now->day);

            if ($now->lt($jamMulai)) {
                return redirect()->route('daftar-layanan.index')
                    ->with('error', 'Mohon maaf, sistem booking baru dibuka pada pukul ' . $jamMulai->format('H:i') . '.');
            }
        }


        // 2. Ambil reservasi yang aktif dan BELUM memiliki pembayaran
        $reservasiAktif = Reservasi::with(['layanan', 'layanan.slotJadwal'])
            ->where('id_pelanggan', $id_pelanggan)
            ->whereIn('status_reservasi', ['diproses', 'pending'])
                                        // **KONDISI BARU: LEFT JOIN dan pastikan TIDAK ADA entri di tabel 'pembayaran'**
            ->whereDoesntHave('pembayaran') // Asumsi relasi 'pembayaran' sudah ada di model Reservasi
            ->get();

        if ($reservasiAktif->isEmpty()) {
            return redirect()
                ->route('booking.categories')
                ->with('error', 'Saat ini, tidak ada layanan di keranjang yang belum dibayar. Silahkan pilih layanan lain atau lanjutkan ke pembayaran yang sudah ada.');
        }

        // 3. Map data ke format keranjang (cart)
        $cart = $reservasiAktif->map(function ($reservasi) {
            $layanan = $reservasi->layanan->first();
            if (! $layanan) {
                return null;
            }

            // Karena kita sudah memfilter yang belum ada pembayaran, kita bisa berasumsi
            // status pembayaran adalah 'belum_bayar'

            return [
                'id' => $layanan->id_layanan,
                'id_reservasi' => $reservasi->id_reservasi,
                'title' => $layanan->nama_layanan,
                'price' => (float) $layanan->harga,
                'duration' => $layanan->durasi,
                'image' => $layanan->gambar
                    ? asset('storage/'.$layanan->gambar)
                    : asset('img/favicon.svg'),
                'reservasi' => [
                    'tanggal_reservasi' => $reservasi->tanggal_reservasi ? Carbon::parse($reservasi->tanggal_reservasi)->toDateString() : null,
                    'waktu_reservasi' => $reservasi->waktu_reservasi ? substr($reservasi->waktu_reservasi, 0, 5) : null,
                    'status_pembayaran' => 'belum_bayar', // Status pembayaran pasti ini karena sudah difilter
                ],
            ];
        })->filter()->values()->all();

        // 4. Tampilkan View
        // dd($cart);
        return view('pelanggan.booking-step2', compact(
            'cart',
        ));
    }

    public function step2Save(Request $request)
    {
        // Metode ini tampaknya sudah digantikan oleh ajaxUpdateSchedule dan goPay di front-end.
        // Jika masih digunakan, logika ini perlu diupdate untuk memproses multiple schedules jika ada.

        // Logika saat ini hanya mengambil schedule pertama
        $id_pelanggan = $this->getCurrentPelangganId();

        if (! $id_pelanggan) {
            return redirect()->route('home')->with('error', 'Data pelanggan tidak ditemukan.');
        }

        $request->validate([
            'schedules' => 'required|json',                                  // Data JSON dari frontend
            'id_reservasi' => 'required|integer|exists:reservasi,id_reservasi', // Ini mungkin id reservasi utama jika multi-reservasi di-group
        ]);

        $schedules = json_decode($request->schedules, true);

        // Asumsi: Jika logic Step 2 sudah selesai, user di-redirect ke Step 3 (Pembayaran)
        return redirect()
            ->route('booking.step3')
            ->with('success', 'Jadwal reservasi berhasil disimpan.');
    }

    public function step3(Request $request)
    {
        // 1. Validasi Pelanggan
        // Asumsi getCurrentPelangganId() ada dan berfungsi
        $id_pelanggan = $this->getCurrentPelangganId();

        // 1. Validasi Pelanggan
        // Asumsi getCurrentPelangganId() ada dan berfungsi
        $id_pelanggan = $this->getCurrentPelangganId();

        // 2. Ambil Pengaturan
        $pengaturan = DB::table('pengaturan_booking')->select('dp_tipe', 'dp_value', 'kebijakan')->first();
        // $bookingAktif = $pengaturan ? $pengaturan->booking_aktif : true;
        
        // // Jika booking tidak aktif, redirect dengan pesan
        // if (!$bookingAktif) {
        //     return redirect()->route('daftar-layanan.index')
        //         ->with('error', 'Mohon maaf, sistem booking sedang tidak aktif. Silakan hubungi admin untuk informasi lebih lanjut.');
        // }

        // if (! $id_pelanggan) {
        //     return redirect()->route('home')->with('error', 'Data pelanggan tidak ditemukan.');
        // }

        // // Cek Jam Mulai Booking
        // if ($pengaturan && $pengaturan->jam_mulai) {
        //     $now = Carbon::now('Asia/Jakarta');
        //     $jamMulai = Carbon::parse($pengaturan->jam_mulai, 'Asia/Jakarta')->setDate($now->year, $now->month, $now->day);

        //     if ($now->lt($jamMulai)) {
        //         return redirect()->route('daftar-layanan.index')
        //             ->with('error', 'Mohon maaf, sistem booking baru dibuka pada pukul ' . $jamMulai->format('H:i') . '.');
        //     }
        // }

        // 2. Validasi ID Reservasi (Dari History Blade)
        $preSelectedIds = (array) $request->input('reservasi_ids', []);
        $preSelectedIds = array_filter($preSelectedIds);

        if (empty($preSelectedIds)) {
            // Redirect ke halaman history/list jika tidak ada ID dipilih
            return redirect()->route('booking.history')->with('error', 'Silakan pilih reservasi untuk dilanjutkan ke pembayaran.');
        }

        // 3. Ambil Pengaturan DP
        $pengaturanDp = DB::table('pengaturan_booking')->select('dp_tipe', 'dp_value')->first();
        $dpTipe = $pengaturanDp ? $pengaturanDp->dp_tipe : 'persen';
        $dpValue = $pengaturanDp ? (float) $pengaturanDp->dp_value : 30.0;

        $cards = [];

        // QUERY: Ambil reservasi milik pelanggan yang dipilih (HANYA status 'pending' untuk pembayaran)
        $reservasiList = Reservasi::with([
            'layanan',
            'pembayaran' => function ($query) {
                // Hanya ambil pembayaran yang sudah sukses (lunas/dp)
                $query->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp']);
            },
        ])
            ->where('id_pelanggan', $id_pelanggan)
            ->whereIn('reservasi.id_reservasi', $preSelectedIds)
            ->where('status_reservasi', 'pending')
            ->orderBy('reservasi.created_at', 'desc')
            ->get();

        // 4. Proses Setiap Reservasi
        foreach ($reservasiList as $reservasi) {
            $totalHarga = 0;
            $items = [];

            // A. Hitung Total Tagihan (Final Amount)
            foreach ($reservasi->layanan as $layanan) {
                if (! $layanan) {
                    continue;
                }

                $hargaLayanan = (float) ($layanan->pivot->harga_deal ?? $layanan->harga);
                $totalHarga += $hargaLayanan;

                $items[] = [
                    'id' => $layanan->id_layanan,
                    'title' => $layanan->pivot->nama_layanan_snapshot ?? $layanan->nama_layanan,
                    'price' => $hargaLayanan,
                    'image' => $layanan->gambar ? asset('storage/layanan/'.$layanan->gambar) : asset('img/favicon.svg'),
                ];
            }



            // A2. Cek Biaya Tambahan (Selisih antara total di DB dengan total harga layanan)
            // ReservasiController::addAdditionalCost mengupdate kolom total_harga di tabel reservasi.
            // Maka kita harus gunakan $reservasi->total_harga sebagai acuan final.
            $dbTotal = (float) $reservasi->total_harga;
            $serviceTotal = $totalHarga;

            if ($dbTotal > $serviceTotal) {
                $diff = $dbTotal - $serviceTotal;
                $items[] = [
                    'id' => 'ADD-COST', // Fake ID
                    'title' => 'Biaya Tambahan / Adjustment',
                    'price' => $diff,
                    'image' => asset('img/favicon.svg'), // Placeholder
                ];
                // Update total harga loop konseptual (ini untuk variable lokal saja)
                $totalHarga += $diff;
            }

            // Gunakan total dari DB sebagai Final Amount truth
            $finalAmount = $dbTotal;

            // B. Hitung Pembayaran Sudah Masuk (Hanya yang bayar_lunas / bayar_dp)
            $totalPaid = (float) $reservasi->pembayaran->sum('jumlah');
            $totalDiskon = (float) $reservasi->pembayaran->sum('diskon_applied');

            // Sisa yang Harus Dibayar
            $remaining = max($finalAmount - $totalPaid - $totalDiskon, 0);

            // Filter: Hanya lanjutkan untuk reservasi yang masih ada sisa (remaining > 0)
            if ($remaining <= 0) {
                continue;
            }

            // C. Hitung Nominal DP (Rp) untuk View
            $dpAmount = ($dpTipe === 'persen')
                ? ceil($finalAmount * ($dpValue / 100))
                : $dpValue;

            // Batasan DP minimal dan DP tidak lebih dari Sisa Pembayaran
            $dpAmount = max(1000, $dpAmount);
            $dpAmount = min($dpAmount, $remaining);

            // D. Tentukan Status Teks untuk View
            if ($totalPaid <= 0) {
                $statusText = 'Belum bayar'; // Bisa DP atau Lunas
            } else {
                $statusText = 'Down Payment'; // Wajib Lunas
            }

            // E. Susun Data untuk View
            $cards[] = [
                'reservasi' => $reservasi,
                'items' => $items,
                'statusText' => $statusText,
                'finalAmount' => $finalAmount,
                'dpAmount' => $dpAmount, // Nominal DP yang dihitung dari total
                'totalPaid' => $totalPaid,
                'remaining' => $remaining,
            ];
        }

        // Cek lagi setelah filter sisa
        if (empty($cards)) {
            return redirect()
                ->route('booking.history')
                ->with('success', 'Semua reservasi yang Anda pilih sudah lunas atau tidak valid untuk dibayar.');
        }

        // 5. Ambil Metode Pembayaran
        $methods = MetodePembayaran::where('status', 'aktif')->get();

        // 6. Siapkan data DP untuk ditampilkan di View
        $dpDisplay = ['tipe' => $dpTipe, 'value' => $dpValue];

        // 7. Tampilkan View
        $kebijakan = $pengaturan->kebijakan ?? 'Harap datang tepat waktu. Keterlambatan lebih dari 15 menit dapat membatalkan reservasi.';

        return view('pelanggan.booking-step3', compact(
            'cards', 
            'methods', 
            'dpDisplay',
            'kebijakan' // Pass data kebijakan
        ));
    }

    /**
     * FUNGSI BARU: Menangani redirect sukses untuk MULTI reservasi.
     * Menerima ID reservasi yang dipisahkan koma.
     */
    public function successMulti(string $ids)
    {
        // Pisahkan string ID menjadi array integer
        $reservasiIds = array_map('intval', explode(',', $ids));

        // Ambil semua reservasi yang terlibat dan pembayaran yang terkait
        $reservasiList = Reservasi::with([
            'layanan',
            'pembayaran' => function ($query) use ($reservasiIds) {
                // Hanya ambil pembayaran yang statusnya PAID/LUNAS dan terkait dengan reservasi terpilih
                $query->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                    ->whereIn('id_reservasi', $reservasiIds)
                    ->orderBy('created_at', 'desc'); // Ambil yang terbaru dulu
            },
            'pelanggan.user',
        ])
            ->whereIn('id_reservasi', $reservasiIds)
            ->get();

        if ($reservasiList->isEmpty()) {
            return redirect()->route('booking.categories')->with('error', 'Reservasi tidak ditemukan.');
        }

        $cards = [];
        foreach ($reservasiList as $reservasi) {
            $totalHarga = (float) $reservasi->total_harga;

            // Cari pembayaran yang *terakhir* dan *sudah paid* untuk reservasi ini
            // Ini adalah pembayaran yang membuat reservasi ini sukses/dp
            $lastPaidPayment = $reservasi->pembayaran->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                ->sortByDesc('id_pembayaran')
                ->first();

            // Logika rincian dinamis
            $pembayaranSebelumnya = (float) $reservasi->pembayaran
                ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                ->where('id_pembayaran', '!=', optional($lastPaidPayment)->id_pembayaran)
                ->sum('jumlah');

            $diskonSebelumnya = (float) $reservasi->pembayaran
                ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                ->where('id_pembayaran', '!=', optional($lastPaidPayment)->id_pembayaran)
                ->sum('diskon_applied');

            $amountDibayar = (float) optional($lastPaidPayment)->jumlah;

            $tipePembayaran = '';
            $keterangan = '';

            if (optional($lastPaidPayment)->status_pembayaran === 'bayar_lunas' && ($pembayaranSebelumnya > 0 || $diskonSebelumnya > 0)) {
                $tipePembayaran = 'Pelunasan';
                $keterangan = 'Pembayaran ini melunasi sisa tagihan reservasi.';
            } elseif (optional($lastPaidPayment)->status_pembayaran === 'bayar_lunas' && $pembayaranSebelumnya <= 0 && $diskonSebelumnya <= 0) {
                $tipePembayaran = 'Pembayaran Penuh';
                $keterangan = 'Reservasi ini dibayar lunas dalam satu kali pembayaran.';
            } elseif (optional($lastPaidPayment)->status_pembayaran === 'bayar_dp') {
                $tipePembayaran = 'Down Payment (DP)';
                $keterangan = 'Hanya DP yang dibayar, sisa tagihan akan dilunasi nanti.';
            } else {
                continue; // Skip jika tidak ada data pembayaran paid yang valid
            }

            // Hitung Biaya Tambahan for Receipt
            $sumLayanan = $reservasi->layanan->sum(function($l) {
                return $l->pivot->harga_deal ?? $l->harga;
            });
            $biayaTambahan = max($totalHarga - $sumLayanan, 0);

            $cards[] = [
                'reservasi' => $reservasi,
                'layanan' => $reservasi->layanan,
                'total_tagihan' => $totalHarga,
                'biaya_tambahan' => $biayaTambahan, // NEW
                'sudah_dibayar' => $pembayaranSebelumnya + $diskonSebelumnya, // Total PAID + DISKON sebelum transaksi ini
                'transaksi_ini' => $amountDibayar + (float) optional($lastPaidPayment)->diskon_applied, // Amount + Diskon transaksi ini (Value Covered)
                'diskon_ini' => (float) optional($lastPaidPayment)->diskon_applied, // New field for display
                'real_paid' => $amountDibayar, // New field for actual money paid
                'tipe_pembayaran' => $tipePembayaran,
                'keterangan' => $keterangan,
                'metode_midtrans' => optional($lastPaidPayment)->metode ?? 'Online',
                'status_reservasi' => $reservasi->status_reservasi,
            ];
        }

        // Jika semua reservasi yang dipilih ternyata masih pending (misal ditutup midtrans), redirect
        if (empty($cards)) {
            return redirect()->route('booking.categories')->with('warning', 'Transaksi Anda masih tertunda. Silakan cek status di riwayat reservasi.');
        }

        return view('pelanggan.booking-success-multi', compact('cards'));
    }

    // FUNGSI success lama (jika masih digunakan untuk Single ID redirect)
    // public function success($id)
    // {
    //     $reservasi = Reservasi::with([
    //         'pelanggan',
    //         'layanan',
    //         'pembayaran',
    //     ])
    //         ->where('id_reservasi', $id)
    //         ->whereIn('status_reservasi', ['pending', 'sudah dibayar', 'down payment', 'proses', 'selesai'])
    //         ->firstOrFail();

    //     $pembayaranTerbaru = $reservasi->pembayaran()->latest()->first();

    //     return view('pelanggan.booking-success', compact('reservasi', 'pembayaranTerbaru'));
    // }
    public function finish(Request $request)
    {
        // Metode ini tampaknya digunakan untuk FINAL SUBMIT sebelum Midtrans/Payment Gateway
        $id_pelanggan = $this->getCurrentPelangganId();

        if (! $id_pelanggan) {
            return redirect()->route('home')->with('error', 'Data pelanggan tidak ditemukan.');
        }

        $request->validate([
            'reservasi_id' => 'required|integer|exists:reservasi,id_reservasi', // ID reservasi yang dipilih
            'pay_type' => 'required|in:dp,full',                            // Tipe pembayaran: DP atau Full
            'metode_id' => 'required|integer|exists:metode_pembayaran,id_metode',
        ]);

        $reservasi = Reservasi::where('id_reservasi', $request->reservasi_id)
            ->where('id_pelanggan', $id_pelanggan)
            ->whereIn('status_reservasi', ['diproses', 'pending'])
            ->firstOrFail();

        // Total harga seharusnya sudah dihitung di step3 dan disimpan di kolom total_harga
        $totalHarga = $reservasi->total_harga;

        $reservasi->id_metode = $request->metode_id;
        // Status reservasi akan diubah setelah Midtrans callback, tapi di sini kita simpan metode dan total
        // $reservasi->status_reservasi = 'pending'; // Dibiarkan 'pending' sampai callback Midtrans
        $reservasi->save();

        // Di sini Anda biasanya memanggil Midtrans atau Payment Gateway

        // ===========================
        // NOTIFIKASI ADMIN: NEW BOOKING
        // ===========================
        $tglFormat = $reservasi->tanggal_reservasi ? $reservasi->tanggal_reservasi->format('d/m/Y') : '-';
        $customerName = $reservasi->pelanggan->nama ?? 'Pelanggan';
        
        $this->notifyAdmins([
            'title'   => 'Booking Baru Masuk',
            'message' => "Pelanggan {$customerName} memesan untuk tgl {$tglFormat}. Status: Menunggu Pembayaran. Total: Rp " . number_format($totalHarga, 0, ',', '.'),
            'type'    => 'info',
            'link'    => route('booking.list', ['search' => $reservasi->id_reservasi]), // Link ke admin booking
            'icon'    => 'fas fa-calendar-plus',
        ]);


        // Redirect ke halaman sukses/tunggu pembayaran
        return redirect()
            ->route('booking.success', ['id' => $reservasi->id_reservasi])
            ->with('success', 'Reservasi berhasil dibuat. Silahkan lakukan pembayaran.');
    }

    // ... di dalam class BookingController ...

    public function success($ids)
    {
        // 1. Pecah string ID menjadi array integer
        $reservasiIds = explode(',', $ids);
        $reservasiIds = array_filter(array_map('intval', $reservasiIds));

        if (empty($reservasiIds)) {
            return redirect()->route('home')->with('error', 'ID reservasi tidak valid.');
        }

        // 2. Ambil semua reservasi yang terlibat
        $reservasiList = Reservasi::with([
            'pelanggan',
            'layanan',
            'pembayaran' => function ($query) {
                // Hanya ambil pembayaran yang PAID/PENDING, diurutkan terbaru
                $query->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp', 'pending'])
                    ->latest();
            },
        ])
            ->whereIn('id_reservasi', $reservasiIds)
            ->get();

        if ($reservasiList->isEmpty()) {
            return redirect()->route('home')->with('error', 'Reservasi yang dicari tidak ditemukan.');
        }

        // 3. Ambil semua pembayaran terbaru dari reservasi yang terlibat
        // Kita ambil SEMUA pembayaran yang memiliki order_id yang sama dengan pembayaran terbaru
        // dari reservasi pertama (untuk mengelompokkan transaksi)
        $firstReservasi = $reservasiList->first();
        $latestPayment = $firstReservasi->pembayaran()->latest()->first();

        if (! $latestPayment) {
            // Jika tidak ada pembayaran, kemungkinan status pending/dibatalkan
            return redirect()->route('home')->with('error', 'Tidak ada riwayat pembayaran yang ditemukan untuk transaksi ini.');
        }

        // Ambil SEMUA pembayaran yang merupakan bagian dari transaksi Midtrans TUNGGAL ini
        $transactionPayments = Pembayaran::where('order_id', 'LIKE', $latestPayment->order_id.'-%')
            ->whereIn('id_reservasi', $reservasiIds)
            ->get();

        // 4. Hitung total dan status agregat
        $grandTotalPaid = $transactionPayments->sum('jumlah');
        $status = $transactionPayments->contains('status_pembayaran', 'pending') ? 'pending' : 'paid';

        // Data yang akan dikirim ke view
        $successData = [
            'reservasiList' => $reservasiList,           // Daftar semua reservasi yang diproses
            'transactionPayments' => $transactionPayments,     // Detail pembayaran (bisa banyak)
            'midtransOrderId' => $latestPayment->order_id, // Order ID Midtrans (yang tunggal)
            'grandTotalPaid' => $grandTotalPaid,
            'transactionStatus' => $status, // Status agregat
        ];

        // Buat View baru: pelanggan.booking-success-multi
        return view('pelanggan.booking-success', $successData);
    }
    // ... sisa controller Anda ...

    /* =========================
        HISTORY MANAGEMENT
    ========================== */

    // Fungsi untuk me-return view history saja
    public function history(Request $request)
    {
        $id_pelanggan = $this->getCurrentPelangganId();

        if (! $id_pelanggan) {
            return redirect()
                ->route('home')
                ->with('error', 'Akun Anda tidak memiliki data pelanggan.');
        }

        $status = $request->get('status', 'semua');
        $search = $request->get('search');

        return view('pelanggan.booking-history', [
            'status' => $status,
            'currentSearch' => $search,
        ]);
    }

    public function ajaxGetReservations(Request $request)
    {
        try {
            $id_pelanggan = $this->getCurrentPelangganId();
            if (! $id_pelanggan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pelanggan tidak ditemukan.',
                ], 400);
            }

            // ===========================
            // AUTO DELETE EXPIRED UNPAID BOOKINGS
            // ===========================
            // Logic: Hapus jika status 'pending', tidak ada pembayaran sukses, 
            // dan waktu reservasi + durasi (atau buffer waktu) sudah lewat.
            // Kita gunakan buffer misal 1 jam setelah waktu reservasi.
            
            $now = Carbon::now('Asia/Jakarta');
            
            // Ambil semua reservasi pending user ini yang sudah lewat
            // Perlu join dengan layanan untuk tau durasi, tapi untuk simplifikasi
            // kita anggap expired jika waktu reservasi < now (atau now - 1 jam)
            
            $expiredReservations = Reservasi::where('id_pelanggan', $id_pelanggan)
                ->where('status_reservasi', 'pending')
                ->where('waktu_reservasi', '<', $now->toTimeString()) // Cek jam
                ->whereDate('tanggal_reservasi', '<=', $now->toDateString()) // Cek tanggal
                ->whereDoesntHave('pembayaran', function($q) {
                     $q->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp']);
                })
                ->get();
                
            foreach ($expiredReservations as $expired) {
                // Double check tanggal + waktu benar2 sudah lewat
                // Gabungkan tanggal dan waktu
                try {
                     $resDateTime = Carbon::parse($expired->tanggal_reservasi . ' ' . $expired->waktu_reservasi, 'Asia/Jakarta');
                     
                     // Jika waktu sekarang > waktu reservasi (kita bisa kasih toleransi misal 15 menit)
                     if ($now->gt($resDateTime->addMinutes(15))) {
                         // Hapus relasi
                         DB::table('reservasi_layanan')->where('id_reservasi', $expired->id_reservasi)->delete();
                         DB::table('reservasi_slot_jadwal')->where('id_reservasi', $expired->id_reservasi)->delete();
                         $expired->delete();
                     }
                } catch (\Exception $e) {
                    // Ignore parsing error, continue
                }
            }




            $status = $request->get('status', 'semua');
            // Normalize status: replace spaces with underscores to match switch cases
            $status = str_replace(' ', '_', $status);
            
            $search = $request->get('search');
            $perPage = 10;

            $query = Reservasi::where('id_pelanggan', $id_pelanggan)
                ->with([
                    'reservasiLayanan.layanan.kategoriLayanan',
                    'pembayaran',
                ]);

            // ===========================
            // FILTER STATUS
            // ===========================
            $query->when($status && $status !== 'semua', function (Builder $q) use ($status) {
                $now = Carbon::now('Asia/Jakarta');

                switch ($status) {
                    case 'menunggu_konfirmasi':
                        // Logic Baru: Pending DAN Status Pembayaran != bayar_lunas
                        $q->where('status_reservasi', 'pending')
                            ->whereDoesntHave('pembayaran', function ($qP) {
                                $qP->where('status_pembayaran', 'bayar_lunas');
                            });
                        break;

                    case 'sedang_berjalan':
                    case 'sedang berjalan':
                        // Logic Strict Time based (Sesuai Request User):
                        // 1. Tanggal Hari Ini & Status Aktif
                        // 2. Waktu Sekarang berada di antara [Waktu Mulai, Waktu Selesai (Mulai + Durasi)]
                        
                        $q->whereDate('tanggal_reservasi', $now->toDateString())
                          ->where('status_reservasi', '!=', 'dibatalkan')
                          ->where('status_reservasi', '!=', 'selesai')
                          ->where('status_reservasi', '!=', 'menunggu_konfirmasi_pembatalan')
                          ->whereHas('reservasiLayanan.layanan', function ($qL) use ($now) {
                                // Subquery untuk cek waktu berdasarkan durasi layanan
                                $currentTime = $now->format('H:i:s');
                                
                                // Start Time <= Current Time AND End Time >= Current Time
                                // End Time = reservasi.waktu_reservasi + layanan.durasi (menit)
                                $qL->whereRaw("reservasi.waktu_reservasi <= ?", [$currentTime])
                                   ->whereRaw("ADDTIME(reservasi.waktu_reservasi, SEC_TO_TIME(layanan.durasi * 60)) > ?", [$currentTime]);
                          });
                        break;

                    case 'selesai':
                        // Logic Baru: status_reservasi = 'selesai'
                        // Tanpa syarat pembayaran di query, agar jika ada data anomali tetap terlihat
                        $q->where('status_reservasi', 'selesai');
                        break;

                    case 'dibatalkan':
                        $q->where(function (Builder $qCancel) {
                            $qCancel->whereIn('status_reservasi', ['menunggu_konfirmasi_pembatalan', 'dibatalkan'])
                                ->orWhereHas('pembayaran', function (Builder $qP) {
                                    $qP->whereIn('status_pembayaran', ['pembatalan_dp', 'pembatalan_lunas']);
                                });
                        });
                        break;
                }
            });

            // ===========================
            // FILTER SEARCH
            // ===========================
            $query->when($search, function (Builder $q) use ($search) {
                $q->where('id_reservasi', 'like', "%{$search}%")
                    ->orWhereDate('tanggal_reservasi', 'like', "%{$search}%")
                    ->orWhereHas('reservasiLayanan.layanan', function (Builder $qL) use ($search) {
                        $qL->where('nama_layanan', 'like', "%{$search}%");
                    });
            });

            $reservations = $query->orderBy('tanggal_reservasi', 'desc')
                ->orderBy('waktu_reservasi', 'desc')
                ->paginate($perPage);

            $historyData = [];
            $now = Carbon::now('Asia/Jakarta');

            foreach ($reservations as $reservasi) {
                // ===========================
                // INISIALISASI STATUS PEMBAYARAN
                // ===========================
                $statusPembayaran = 'belum_bayar';
                $jumlahDibayar   = 0;
                $jumlahDiskon    = 0;

                // 1. Pembayaran paid/cancel terakhir
                $paidPayment = $reservasi->pembayaran()
                    ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp', 'pembatalan_dp', 'pembatalan_lunas'])
                    ->orderByDesc('id_pembayaran')
                    ->first();

                // 2. Pembayaran pending midtrans
                $pendingPayment = $reservasi->pembayaran()
                    ->where('status_pembayaran', 'pending')
                    ->first();

                // 3. Tentukan status & jumlah dibayar
                if ($paidPayment) {
                    $statusPembayaran = $paidPayment->status_pembayaran;
                    // jumlah yang benar-benar sudah masuk (hanya bayar_lunas + bayar_dp)
                    $jumlahDibayar = $reservasi->pembayaran()
                        ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                        ->sum('jumlah');
                    
                    $jumlahDiskon = $reservasi->pembayaran()
                        ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                        ->sum('diskon_applied');
                } elseif ($pendingPayment) {
                    $statusPembayaran = 'pending_midtrans';
                    $jumlahDibayar   = 0;
                    $jumlahDiskon    = 0;
                }

                // ===========================
                // AMBIL LAYANAN UTAMA
                // ===========================
                $reservasiLayananCollection = $reservasi->reservasiLayanan;
                $resLayananUtama = $reservasiLayananCollection->first();
                $layananUtama    = optional($resLayananUtama)->layanan;

                if ($layananUtama) {
                    $totalLayananLain = $reservasiLayananCollection->count() - 1;

                    // Tentukan status tampilan
                    [$statusClass, $statusText, $isClickable, $clickHint, $showCancel, $showDelete] = $this->determineDisplayStatus(
                        $reservasi->status_reservasi,
                        $statusPembayaran,
                        $reservasi->tanggal_reservasi,
                        $reservasi->waktu_reservasi,
                        $now,
                        $layananUtama->durasi // Tambahkan parameter durasi
                    );

                    // ===========================
                    // HITUNG SISA PEMBAYARAN (LOGIKA BARU - ROBUST)
                    // ===========================
                    $totalHarga          = $reservasi->total_harga ?? 0;
                    $sisaPembayaranAwal  = max($totalHarga - $jumlahDibayar, 0);

                    // Cek apakah ada record 'bayar_lunas' di collection (Eager Load)
                    // Ini lebih aman daripada query ulang jika database atau timing bermasalah
                    $hasLunas = $reservasi->pembayaran->contains('status_pembayaran', 'bayar_lunas');
                    
                    // Cek status pembatalan dari $paidPayment (query sebelumnya)
                    $isCancelled = in_array($statusPembayaran, ['pembatalan_dp', 'pembatalan_lunas']);
                    $isResCancelled = in_array($reservasi->status_reservasi, ['dibatalkan', 'menunggu_konfirmasi_pembatalan']);
                    // NEW: Force hide sisa if selesai
                    $isSelesai = $reservasi->status_reservasi === 'selesai';

                    if ($hasLunas || $isCancelled || $isResCancelled || $isSelesai) {
                        $sisaPembayaran = 0;
                    } else {
                        // selain itu gunakan selisih normal (max 0 untuk menghindari negatif)
                        $sisaPembayaran = max($totalHarga - $jumlahDibayar - $jumlahDiskon, 0);
                    }

                    $historyData[] = [
                        'id_reservasi' => (int) $reservasi->id_reservasi,
                        'card_link'    => '#',
                        'thumb'        => $layananUtama->gambar
                            ? asset('storage/' . $layananUtama->gambar)
                            : asset('img/favicon.svg'),
                        'nama_layanan'       => $resLayananUtama->nama_layanan_snapshot ?? $layananUtama->nama_layanan,
                        'total_layanan_lain' => $totalLayananLain,
                        'kategori'           => optional($layananUtama->kategoriLayanan->first())->nama_kategori ?? 'Umum',
                        'tanggal_reservasi'  => $reservasi->tanggal_reservasi
                            ? Carbon::parse($reservasi->tanggal_reservasi)->isoFormat('D MMMM Y')
                            : 'Belum Ditentukan',
                        'waktu_reservasi'    => $reservasi->waktu_reservasi
                            ? substr($reservasi->waktu_reservasi, 0, 5) . ' WIB'
                            : 'Belum Ditentukan',

                        // SISA PEMBAYARAN SUDAH DI-ZERO KAN UNTUK LUNAS & BATAL
                        'sisa_pembayaran'    => number_format($sisaPembayaran, 0, ',', '.'),
                        'total_harga_deal'   => $reservasi->reservasiLayanan->sum('harga_deal'),
                        'catatan'            => $reservasi->catatan, // Added catatan

                        'status_reservasi_class' => $statusClass,
                        'status_reservasi_text'  => $statusText,
                        'is_clickable'           => $isClickable,
                        'click_hint'             => $clickHint,
                        'show_cancel'            => $showCancel,
                        'show_delete'            => $showDelete, 
                        'is_lunas'               => $statusPembayaran === 'bayar_lunas', // Add is_lunas flag
                    ];
                } else {
                    // fallback kalau layanan tidak ditemukan
                    Log::warning("Reservasi #{$reservasi->id_reservasi} tidak memiliki layanan utama.");

                    $historyData[] = [
                        'id_reservasi'          => (int) $reservasi->id_reservasi,
                        'card_link'             => '#',
                        'thumb'                 => asset('img/favicon.svg'),
                        'nama_layanan'          => 'Layanan Tidak Ditemukan',
                        'total_layanan_lain'    => 0,
                        'kategori'              => 'Error',
                        'tanggal_reservasi'     => 'Belum Ditentukan',
                        'waktu_reservasi'       => 'Belum Ditentukan',
                        'sisa_pembayaran'       => number_format($reservasi->total_harga, 0, ',', '.'),
                        'status_reservasi_class'=> 'dibatalkan',
                        'status_reservasi_text' => 'Data Layanan Error',
                        'is_clickable'          => false,
                        'click_hint'            => 'Hubungi Admin',
                        'show_cancel'           => false,
                    ];
                }
            }

            $paginationData = $reservations->toArray();
            unset($paginationData['data']);

            return response()->json([
                'success'    => true,
                'data'       => $historyData,
                'pagination' => $paginationData,
                'message'    => 'Riwayat reservasi berhasil dimuat.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error ajaxGetReservations (History): ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil riwayat: ' . $e->getMessage(),
            ], 500);
        }
    }


    // =======================================================================
    // 5. HELPER FUNCTION
    // =======================================================================

    /**
     * Fungsi pembantu untuk menentukan status tampilan di UI.
     *
     * @return array [class, text, isClickable, clickHint, showCancelButton]
     */
    private function determineDisplayStatus($reservasiStatus, $pembayaranStatus, $tanggalReservasi, $waktuReservasi, $now, $durasi = 0)
    {
        $class = 'default';
        $text = 'Menunggu';
        $clickable = true;
        $hint = 'Klik untuk detail';
        $showCancel = false;

        $isPast = false;
        $isFuture = true;
        $isOngoing = false;

        // Kode penentuan waktu
        if ($tanggalReservasi && $waktuReservasi) {
            try {
                $startDateTime = Carbon::parse($tanggalReservasi.' '.$waktuReservasi, 'Asia/Jakarta');
                $endDateTime = $startDateTime->copy()->addMinutes($durasi);
                
                $isPast = $endDateTime->lt($now); // Sudah lewat jam selesai
                $isFuture = $startDateTime->gt($now); // Belum mulai
                $isOngoing = $now->between($startDateTime, $endDateTime); // Sedang dalam rentang durasi
            
            } catch (\Exception $e) {
                // ...
            }
        }

        // 1. Prioritas Utama: Status Pembatalan Pembayaran
        if ($pembayaranStatus == 'pembatalan_dp') {
            $class = 'pembatalan_dp';
            $text = 'Pembatalan DP Berhasil';
            $hint = 'DP telah dibatalkan/dikembalikan, reservasi dibatalkan.';
            $clickable = false;
            $showCancel = false;

            return [$class, $text, $clickable, $hint, $showCancel, false];
        }

        if ($pembayaranStatus == 'pembatalan_lunas') {
            $class = 'pembatalan_lunas';
            $text = 'Pembatalan Lunas Berhasil';
            $hint = 'Pembayaran lunas telah dibatalkan/dikembalikan, reservasi dibatalkan.';
            $clickable = false;
            $showCancel = false;

            return [$class, $text, $clickable, $hint, $showCancel, false];
        }

        // 2. Status Reservasi Khusus (Menunggu Konfirmasi Pembatalan)
        if ($reservasiStatus == 'menunggu_konfirmasi_pembatalan') {
            $class = 'menunggu_konfirmasi_pembatalan';
            $text = 'Menunggu Konfirmasi Pembatalan';
            $hint = 'Permintaan pembatalan sedang menunggu konfirmasi admin';
            $showCancel = true;

            return [$class, $text, $clickable, $hint, $showCancel, false];
        }

        // 3. Status Final (Selesai/Dibatalkan)
        if ($reservasiStatus == 'dibatalkan') {
            $class = 'dibatalkan';
            $text = 'Dibatalkan';
            $hint = 'Reservasi ini telah dibatalkan';
        } elseif ($reservasiStatus == 'selesai') {
            $class = 'selesai';
            $text = 'Selesai';
            $hint = 'Layanan telah selesai digunakan';
        } elseif ($reservasiStatus == 'pending') {
            // 4. Logika Reservasi 'Pending'
            
            if ($pembayaranStatus == 'bayar_lunas') {
                if ($isOngoing) {
                    $class = 'proses'; // Sedang Berjalan (based on time)
                    $text = 'Sedang Berjalan';
                    $hint = 'Layanan sedang berlangsung sesuai jadwal';
                    $showCancel = false; // TIDAK BISA CANCEL KALAU SUDAH JALAN
                } elseif ($isFuture) {
                    $class = 'pending'; // Dijadwalkan
                    $text = 'Dijadwalkan (Lunas)';
                    $hint = 'Menunggu waktu layanan tiba';
                    $showCancel = true; // BISA CANCEL KALAU MASIH FUTURE
                } else {
                    // isPast tapi status masih pending (admin belum klik selesai)
                    $class = 'proses'; // Anggap sedang berjalan atau menunggu admin
                    $text = 'Menunggu Penyelesaian';
                    $hint = 'Layanan selesai, menunggu konfirmasi admin';
                    $showCancel = false; // TIDAK BISA CANCEL KALAU SUDAH LEWAT
                }
                
            } elseif ($pembayaranStatus == 'bayar_dp') {
                 if ($isOngoing) {
                    $class = 'proses'; 
                    $text = 'Sedang Berjalan';
                    $hint = 'Layanan sedang berlangsung';
                    $showCancel = false; // TIDAK BISA CANCEL
                } elseif ($isFuture) {
                    $class = 'pending';
                    $text = 'Dijadwalkan (DP)';
                    $hint = 'Menunggu waktu layanan. Sisa pembayaran diperlukan.';
                    $showCancel = true; // BISA CANCEL
                } else {
                    $class = 'proses';
                    $text = 'Menunggu Penyelesaian';
                    $hint = 'Layanan selesai, menunggu konfirmasi admin';
                    $showCancel = false; // TIDAK BISA CANCEL
                }

            } else {
                // Belum lunas / Belum DP
                $class = 'pending';
                $text = 'Menunggu Pembayaran'; // Explicit Text
                $hint = 'Lakukan pembayaran untuk konfirmasi';
                $showCancel = false; // Unpaid -> Show Delete instead
            }
        
        } elseif ($reservasiStatus == 'proses') {
            // 5. Logika Reservasi 'Proses' -> Overrides everything if admin set to proses
             $class = 'proses';
             $text = 'Sedang Berjalan';
             $hint = 'Layanan sedang berlangsung atau menunggu waktu';
             $showCancel = false; // Process cannot cancel
        }

        // Penentuan umum (catch-all)
        if (! in_array($reservasiStatus, ['pending', 'proses', 'selesai', 'dibatalkan', 'menunggu_konfirmasi_pembatalan'])) {
            $class = 'default';
            $text = 'Status Tidak Dikenal';
            $clickable = false;
            $hint = '';
        }

        if (in_array($reservasiStatus, ['selesai', 'dibatalkan']) && ! in_array($pembayaranStatus, ['pembatalan_dp', 'pembatalan_lunas'])) {
            $clickable = false;
        }

        $showDelete = false;
        if ($reservasiStatus == 'pending' && (!in_array($pembayaranStatus, ['bayar_dp', 'bayar_lunas']))) {
            $showDelete = true;
        }

        return [$class, $text, $clickable, $hint, $showCancel, $showDelete];
    }

    // D:\laragon\www\aurorabeautysalon\app\Http\Controllers\BookingController.php

    public function cancel($id)
    {
        $id_pelanggan = $this->getCurrentPelangganId();

        if (! $id_pelanggan) {
            // Mengubah redirect menjadi respons JSON
            return response()->json([
                'success' => false,
                'message' => 'Data pelanggan tidak ditemukan.',
            ], 400); // 400 Bad Request
        }

        $reservasi = Reservasi::where('id_reservasi', $id)
            ->where('id_pelanggan', $id_pelanggan)
            ->whereIn('status_reservasi', ['proses', 'pending'])
            ->first();

        if (! $reservasi) {
            // Mengubah back() menjadi respons JSON
            return response()->json([
                'success' => false,
                'message' => 'Reservasi tidak dapat dibatalkan atau tidak ditemukan.',
            ], 404); // 404 Not Found
        }

        // Jika user klik batal -> menunggu_konfirmasi_pembatalan
        $reservasi->update(['status_reservasi' => 'menunggu_konfirmasi_pembatalan']);

        // ===========================
        // NOTIFIKASI ADMIN: CANCELLATION REQUEST
        // ===========================
        $customerName = $reservasi->pelanggan->nama ?? 'Pelanggan';
        $tglBooking = $reservasi->tanggal_reservasi ? $reservasi->tanggal_reservasi->format('d/m/Y') : '-';

        $this->notifyAdmins([
            'title'   => 'Permintaan Pembatalan',
            'message' => "{$customerName} meminta pembatalan reservasi (Tgl: {$tglBooking}).",
            'type'    => 'warning',
            'link'    => route('admin.konfirmasi-pembatalan.index', ['search' => $reservasi->id_reservasi]), // Link ke menu pembatalan
            'icon'    => 'fas fa-calendar-times',
        ]);


        // Mengubah back() menjadi respons JSON sukses
        return response()->json([
            'success' => true,
            'message' => 'Permintaan pembatalan dikirim. Menunggu konfirmasi admin.',
        ], 200); // 200 OK
    }
    public function destroy($id)
    {
        $id_pelanggan = $this->getCurrentPelangganId();

        if (! $id_pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Data pelanggan tidak ditemukan.',
            ], 400);
        }

        $reservasi = Reservasi::where('id_reservasi', $id)
            ->where('id_pelanggan', $id_pelanggan)
            ->where('status_reservasi', 'pending')
            ->whereDoesntHave('pembayaran') // Pastikan belum ada pembayaran (aman untuk dihapus)
            ->first();

        if (! $reservasi) {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi tidak dapat dihapus (Mungkin sudah dibayar atau diproses).',
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Hapus relasi jika ada (Cascading biasanya handle ini, tapi manual lebih aman)
            DB::table('reservasi_layanan')->where('id_reservasi', $id)->delete();
            DB::table('reservasi_slot_jadwal')->where('id_reservasi', $id)->delete();
            
            $reservasi->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reservasi berhasil dihapus.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting reservation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus reservasi.',
            ], 500);
        }
    }

}
