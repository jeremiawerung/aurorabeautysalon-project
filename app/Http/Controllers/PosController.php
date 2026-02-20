<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\ReservasiLayanan;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class PosController extends Controller
{
    public function index()
    {
        try {
            $categories = \App\Models\KategoriLayanan::with(['layanan' => function ($q) {
                $q->where('status_layanan', 'aktif');
            }])->get();

            $customers = DB::table('pelanggan')
                ->join('users', 'pelanggan.user_id', '=', 'users.id')
                ->select('pelanggan.id_pelanggan', 'pelanggan.nama', 'users.email', 'pelanggan.nomor_telepon')
                ->orderBy('pelanggan.nama')
                ->get();

            $dates = $this->getBookingDates();

            $methods = MetodePembayaran::where('status', 'aktif')
                ->orderBy('nama')
                ->get(['id_metodePembayaran', 'nama', 'keterangan']);

            $pengaturan_booking = DB::table('pengaturan_booking')
                ->select('booking_aktif', 'jam_mulai', 'jam_selesai', 'opsi_staff', 'dp_value', 'dp_tipe', 'maks_rentang_booking', 'interval_min_booking', 'kebijakan')
                ->first();

            return view('admin.point-of-sale', compact(
                'categories',
                'customers',
                'dates',
                'pengaturan_booking',
                'methods'
            ));
        } catch (Exception $e) {
            Log::error('PosController@index: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function save(Request $request)
    {
        $request->validate([
            'pelanggan_id' => 'nullable|integer|exists:pelanggan,id_pelanggan',
            'pelanggan_nama' => 'required_without:pelanggan_id|string|max:60',
            'pelanggan_telepon' => 'required_without:pelanggan_id|string|max:15',
            'pelanggan_email' => 'nullable|email|max:65',
            'schedules' => 'required|json',
            'pay_type' => 'required|in:dp,full',
            'metode_id' => 'required|integer|exists:metodepembayaran,id_metodePembayaran',
        ]);

        try {
            $pelangganId = $request->pelanggan_id ? (int) $request->pelanggan_id : null;
            $nama = trim((string) $request->pelanggan_nama);
            $telp = trim((string) $request->pelanggan_telepon);
            $email = $request->pelanggan_email ? trim((string) $request->pelanggan_email) : null;

            if (! $pelangganId) {
                // Cari Pelanggan berdasarkan Email (via User) atau Telepon
                $pelangganQuery = \App\Models\Pelanggan::query();
                
                if ($email) {
                    $pelangganQuery->whereHas('user', function($q) use ($email) {
                        $q->where('email', $email);
                    });
                }

                if ($telp) {
                    // Jika ada email, pakai OR. Jika tidak, WHERE biasa.
                    // Tapi logika asli: jika email matched OR telp matched OR nama matched
                    $pelangganQuery->orWhere('nomor_telepon', $telp);
                }

                 // Nama tidak unik, sebaiknya tidak jadi patokan utama jika ada email/telp.
                 // Tapi ikuti logika lama:
                if ($nama) {
                    $pelangganQuery->orWhere('nama', $nama);
                }

                $existing = $pelangganQuery->first();

                if ($existing) {
                    $pelangganId = (int) $existing->id_pelanggan;
                } else {
                    // Create User first if email is provided
                    $userId = null;
                    if ($email) {
                        $user = \App\Models\User::firstOrCreate(
                            ['email' => $email],
                            [
                                'name' => $nama ?: 'Guest',
                                'password' => Hash::make('password'), // Default password
                                'role' => 'pelanggan',
                                'email_verified_at' => now(),
                            ]
                        );
                        $userId = $user->id;
                    }

                    // Create Pelanggan
                    $pelangganId = DB::table('pelanggan')->insertGetId([
                        'user_id' => $userId, // Link to user
                        'nama' => $nama ?: 'Guest',
                        'nomor_telepon' => $telp ?: '-',
                        // 'email' => $email, // REMOVED
                        // 'password' => null, // REMOVED
                        'tanggal_daftar' => now()->toDateString(),
                        'status_pelanggan' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $schedules = json_decode($request->schedules, true);
            if (! is_array($schedules) || empty($schedules)) {
                return response()->json(['success' => false, 'message' => 'Jadwal kosong.'], 400);
            }

            $seen = [];
            foreach ($schedules as $sid => $dt) {
                $key = $dt['date'].'|'.$dt['time'];
                if (isset($seen[$key])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak boleh jadwal sama untuk layanan berbeda.',
                    ], 400);
                }
                $seen[$key] = true;
            }

            foreach ($schedules as $sid => $dt) {
                if ($this->isSlotTaken((int) $sid, $dt['date'], $dt['time'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Slot {$dt['time']} pada {$dt['date']} sudah terpakai.",
                    ], 400);
                }
            }

            $serviceIds = array_keys($schedules);
            $layanan = Layanan::whereIn('id_layanan', $serviceIds)->get();
            $admin = Auth::user()->id;

            DB::beginTransaction();

            $reservasiIds = [];

            // Buat 1 reservasi per layanan
            foreach ($schedules as $idLayanan => $schedule) {
                $svc = $layanan->firstWhere('id_layanan', $idLayanan);
                if (! $svc) {
                    continue;
                }

                $reservasi = Reservasi::create([
                    'id_pelanggan' => $pelangganId,
                    'tanggal_reservasi' => $schedule['date'],
                    'waktu_reservasi' => $schedule['time'].':00',
                    'status_reservasi' => 'pending',
                    'catatan' => 'BOOKING OFFLINE - POS',
                    'id_admin' => $admin,
                    'total_harga' => $svc->harga,
                    'id_metode' => (int) $request->metode_id,
                ]);

                ReservasiLayanan::create([
                    'id_reservasi' => $reservasi->id_reservasi,
                    'id_layanan' => $idLayanan,
                    'harga_deal' => $svc->harga,
                    'nama_layanan_snapshot' => $svc->nama_layanan,
                ]);

                $waktu = $schedule['time'].':00';
                $slot = DB::table('slot_jadwal')
                    ->where('id_layanan', $idLayanan)
                    ->where('waktu', $waktu)
                    ->first();

                if ($slot) {
                    DB::table('reservasi_slot_jadwal')->insert([
                        'id_reservasi' => $reservasi->id_reservasi,
                        'id_slot' => $slot->id_slot,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }

                $reservasiIds[] = $reservasi->id_reservasi;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibuat.',
                'reservasi_ids' => $reservasiIds,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('PosController@save: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan booking: '.$e->getMessage()], 500);
        }
    }

    public function prosesMidtrans(Request $request)
    {
        $request->validate([
            'reservasi_ids' => 'required|array|min:1',
            'reservasi_ids.*' => 'integer|exists:reservasi,id_reservasi',
            'pay_type' => 'required|in:dp,full',
            'metode_id' => 'required|integer|exists:metodepembayaran,id_metodePembayaran',
            'diskon_data' => 'nullable|array', // TAMBAHAN VALIDASI DISKON
        ]);

        try {
            Config::$serverKey = config('midtrans.serverKey');
            Config::$isProduction = config('midtrans.isProduction');
            Config::$isSanitized = config('midtrans.isSanitized');
            Config::$is3ds = config('midtrans.is3ds');

            $reservasiIds = $request->reservasi_ids;
            $payType = $request->pay_type;
            $metodeId = $request->metode_id;
            $diskonData = $request->diskon_data; // AMBIL DATA DISKON

            $pengaturanDp = DB::table('pengaturan_booking')->select('dp_tipe', 'dp_value')->first();
            $dpTipe = $pengaturanDp?->dp_tipe ?? 'persen';
            $dpValue = (float) ($pengaturanDp?->dp_value ?? 30);

            $reservasiModels = Reservasi::with(['pelanggan', 'reservasiLayanan'])
                ->whereIn('id_reservasi', $reservasiIds)
                ->get();

            $totalGrossAmount = 0;
            $itemDetails = [];
            $customerDetails = null;
            $affectedReservasi = []; // UNTUK TRACKING AMOUNT PER RESERVASI

            foreach ($reservasiModels as $reservasi) {
                $customer = $reservasi->pelanggan;

                if (is_null($customerDetails)) {
                    $customerDetails = [
                        'first_name' => $customer->nama ?? 'Guest',
                        'email' => $customer->email ?? 'guest@example.com',
                        'phone' => $customer->nomor_telepon ?? '0000000000',
                    ];
                }

                $finalAmountTotal = (float) $reservasi->total_harga;
                $totalPaid = (float) $reservasi->pembayaran()
                    ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                    ->sum('jumlah');

                $remaining = max($finalAmountTotal - $totalPaid, 0);

                if ($remaining <= 0) {
                    continue;
                }

                $amountToPay = 0;
                $paymentName = '';

                if ($payType === 'dp') {
                    if ($totalPaid > 0) {
                        $amountToPay = $remaining;
                        $paymentName = 'Pelunasan';
                    } else {
                        if ($dpTipe === 'persen') {
                            $amountToPay = ceil($finalAmountTotal * ($dpValue / 100));
                        } else {
                            $amountToPay = $dpValue;
                        }
                        $amountToPay = max(1000, min($amountToPay, $remaining));
                        $paymentName = 'Down Payment';
                    }
                } else {
                    $amountToPay = $remaining;
                    $paymentName = 'Pembayaran Penuh';
                }

                // HITUNG DISKON PER RESERVASI
                $diskonAmount = 0;
                if ($diskonData && isset($diskonData['applicable_layanan'])) {
                    // Ambil layanan dari reservasi ini
                    $layananIdsInReservasi = $reservasi->reservasiLayanan->pluck('id_layanan')->toArray();

                    foreach ($diskonData['applicable_layanan'] as $appLayanan) {
                        if (in_array($appLayanan['id_layanan'], $layananIdsInReservasi)) {
                            $diskonAmount += (float) $appLayanan['diskon'];
                        }
                    }
                }

                // KURANGI DENGAN DISKON
                $amountToPay = max(0, $amountToPay - $diskonAmount);
                $amountToPay = round($amountToPay);

                if ($amountToPay <= 0) {
                    continue;
                }

                $totalGrossAmount += $amountToPay;

                // BUAT ITEM DETAIL DENGAN INFO DISKON
                $itemName = "{$paymentName} #{$reservasi->id_reservasi}";
                if ($diskonAmount > 0) {
                    $itemName .= ' (Diskon: Rp '.number_format($diskonAmount, 0, ',', '.').')';
                }

                $itemDetails[] = [
                    'id' => "RSV-{$reservasi->id_reservasi}",
                    'price' => $amountToPay,
                    'quantity' => 1,
                    'name' => $itemName,
                ];

                // SIMPAN DETAIL UNTUK CALLBACK
                $affectedReservasi[] = [
                    'reservasi_id' => $reservasi->id_reservasi,
                    'amount' => $amountToPay,
                    'diskon_amount' => $diskonAmount,
                    'pay_type_calc' => $paymentName,
                ];
            }

            if ($totalGrossAmount <= 0) {
                return response()->json(['success' => false, 'message' => 'Tidak ada reservasi yang perlu dibayar.'], 400);
            }

            $orderId = 'POS-'.time().'-'.rand(100, 999);

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $totalGrossAmount,
                ],
                'customer_details' => $customerDetails,
                'item_details' => $itemDetails,
            ];

            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
                'amount' => $totalGrossAmount,
                'reservasi_ids' => $reservasiIds,
                'amounts' => $affectedReservasi, // KIRIM DETAIL AMOUNTS
                'metode_id' => $metodeId,
                'pay_type' => $payType,
                'diskon_data' => $diskonData, // KIRIM BALIK DISKON DATA
            ]);

        } catch (\Throwable $e) {
            Log::error('PosController@prosesMidtrans: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function midtransCallback(Request $request)
    {
        $request->validate([
            'reservasi_ids' => 'required|array|min:1',
            'reservasi_ids.*' => 'integer|exists:reservasi,id_reservasi',
            'order_id' => 'required|string',
            'transaction_status' => 'required|string',
            'metode_id' => 'required|integer|exists:metodepembayaran,id_metodePembayaran',
            'pay_type' => 'required|in:dp,full',
            'amounts' => 'nullable|array',
            'diskon_data' => 'nullable|array',
        ]);

        $reservasiIds = $request->reservasi_ids;
        $orderId = $request->order_id;
        $transactionStatus = $request->transaction_status;
        $metodeId = $request->metode_id;
        $payType = $request->pay_type;
        $amountsPaid = collect($request->amounts ?? [])->keyBy('reservasi_id');
        $diskonData = $request->diskon_data;

        $validStatusToSave = ['capture', 'settlement', 'pending'];
        $isSuccess = in_array($transactionStatus, ['capture', 'settlement']);

        if (! in_array($transactionStatus, $validStatusToSave)) {
            return response()->json(['success' => false, 'message' => 'Transaksi gagal/dibatalkan.'], 400);
        }

        DB::beginTransaction();
        try {
            $pengaturanDp = DB::table('pengaturan_booking')->select('dp_tipe', 'dp_value')->first();
            $dpTipe = $pengaturanDp?->dp_tipe ?? 'persen';
            $dpValue = (float) ($pengaturanDp?->dp_value ?? 30);

            foreach ($reservasiIds as $rid) {
                $order_id_per_reservasi = $orderId.'-'.$rid;

                $existingPayment = Pembayaran::where('order_id', $order_id_per_reservasi)
                    ->where('id_reservasi', $rid)
                    ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp', 'pending'])
                    ->first();

                if ($existingPayment) {
                    if ($isSuccess && $existingPayment->status_pembayaran === 'pending') {
                        $reservasi = Reservasi::findOrFail($rid);
                        $finalAmountTotal = (float) $reservasi->total_harga;

                        $totalDiskonBefore = (float) $reservasi->pembayaran()
                            ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                            ->where('id_pembayaran', '!=', $existingPayment->id_pembayaran)
                            ->sum('diskon_applied');

                        $amountToSave = (float) $existingPayment->jumlah;
                        $diskonToSave = (float) $existingPayment->diskon_applied;

                        $totalPaidAfterUpdate = $totalPaidBeforeUpdate + $amountToSave;
                        $totalDiskonAfterUpdate = $totalDiskonBefore + $diskonToSave;

                        $remainingAfterThisPayment = max($finalAmountTotal - $totalPaidAfterUpdate - $totalDiskonAfterUpdate, 0);

                        $newPaymentStatus = ($remainingAfterThisPayment <= 0) ? 'bayar_lunas' : 'bayar_dp';

                        $existingPayment->update([
                            'tanggal_pembayaran' => now(),
                            'status_pembayaran' => $newPaymentStatus,
                        ]);

                        if ($remainingAfterThisPayment <= 0) {
                            $reservasi->update(['status_reservasi' => 'pending']);
                        }
                    }

                    continue;
                }

                // BUAT BARU JIKA BELUM ADA
                $reservasi = Reservasi::with('reservasiLayanan')->findOrFail($rid);
                $finalAmountTotal = (float) $reservasi->total_harga;
                $totalPaidBefore = (float) $reservasi->pembayaran()
                    ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                    ->sum('jumlah');
                
                $totalDiskonBefore = (float) $reservasi->pembayaran()
                    ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                    ->sum('diskon_applied');

                $remaining = max($finalAmountTotal - $totalPaidBefore - $totalDiskonBefore, 0);

                if ($remaining <= 0) {
                    continue;
                }

                // AMBIL AMOUNT DARI DATA YANG DIKIRIM (SUDAH DIKURANGI DISKON)
                $amountData = $amountsPaid->get((int) $rid);
                $amountToPay = $amountData['amount'] ?? 0;
                $diskonAmount = $amountData['diskon_amount'] ?? 0;

                // JIKA TIDAK ADA DATA AMOUNTS, HITUNG MANUAL
                if ($amountToPay <= 0) {
                    if ($payType === 'dp') {
                        if ($totalPaidBefore > 0) {
                            $amountToPay = $remaining;
                        } else {
                            if ($dpTipe === 'persen') {
                                $amountToPay = ceil($finalAmountTotal * ($dpValue / 100));
                            } else {
                                $amountToPay = $dpValue;
                            }
                            $amountToPay = max(1000, min($amountToPay, $remaining));
                        }
                    } else {
                        $amountToPay = $remaining;
                    }

                    // HITUNG DISKON JIKA ADA
                    if ($diskonData && isset($diskonData['applicable_layanan'])) {
                        $layananIdsInReservasi = $reservasi->reservasiLayanan->pluck('id_layanan')->toArray();

                        foreach ($diskonData['applicable_layanan'] as $appLayanan) {
                            if (in_array($appLayanan['id_layanan'], $layananIdsInReservasi)) {
                                $diskonAmount += (float) $appLayanan['diskon'];
                            }
                        }
                    }

                    $amountToPay = max(0, $amountToPay - $diskonAmount);
                }

                $amountToPay = round($amountToPay);

                if ($amountToPay <= 0) {
                    continue;
                }

                $pembayaranStatus = 'pending';

                if ($isSuccess) {
                    // HITUNG TOTAL YANG SUDAH DIBAYAR + PEMBAYARAN BARU
                    $totalPaidCurrent = $totalPaidBefore + $amountToPay;
                    $totalDiskonCurrent = $totalDiskonBefore + $diskonAmount;

                    // HITUNG SISA DARI TOTAL HARGA ASLI (BELUM DISKON)
                    $remainingAfterThisPayment = max($finalAmountTotal - $totalPaidCurrent - $totalDiskonCurrent, 0);

                    // TENTUKAN STATUS BERDASARKAN PAY_TYPE DAN REMAINING
                    if ($payType === 'full') {
                        // JIKA BAYAR PENUH, SELALU LUNAS (karena remaining sudah 0 setelah diskon)
                        $pembayaranStatus = 'bayar_lunas';
                    } else {
                        // JIKA DP, CEK APAKAH MASIH ADA SISA
                        $pembayaranStatus = ($remainingAfterThisPayment <= 0) ? 'bayar_lunas' : 'bayar_dp';
                    }

                    // UPDATE STATUS RESERVASI JIKA SUDAH LUNAS
                    if ($pembayaranStatus === 'bayar_lunas') {
                        $reservasi->update(['status_reservasi' => 'pending']);
                    }
                }

                // SIMPAN PEMBAYARAN DENGAN DISKON
                Pembayaran::create([
                    'id_reservasi' => $rid,
                    'id_metodePembayaran' => $metodeId,
                    'tanggal_pembayaran' => $isSuccess ? now() : null,
                    'jumlah' => $amountToPay,
                    'diskon_applied' => $diskonAmount,
                    'status_pembayaran' => $pembayaranStatus,
                    'order_id' => $order_id_per_reservasi,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil disimpan.',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PosController@midtransCallback: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan pembayaran.'], 500);
        }
    }

    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        try {
            $admin = Auth::user();

            if (! $admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak valid.',
                ], 401);
            }

            if (! Hash::check($request->password, $admin->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password salah.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Password benar.',
            ]);

        } catch (Exception $e) {
            Log::error('PosController@verifyPassword: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
            ], 500);
        }
    }

    public function dpList(Request $request)
    {
        try {
            $reservations = Reservasi::with(['pelanggan', 'pembayaran'])
                ->where(function ($q) {
                    $q->where('catatan', 'like', '%BOOKING OFFLINE%');
                })
                ->where('status_reservasi', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $reservations->map(function ($reservasi) {
                $totalPaid = (float) $reservasi->pembayaran()
                    ->whereIn('status_pembayaran', ['bayar_dp', 'bayar_lunas'])
                    ->sum('jumlah');

                $totalHarga = (float) $reservasi->total_harga;
                $diskonReservasi = (float) ($reservasi->diskon ?? 0);

                // TOTAL YANG WAJIB DIBAYAR SETELAH DISKON
                // $diskonReservasi is fixed discount on reservation, different from payment discount?
                // Assuming we want to use the payment discount accumulation here too.
                $totalDiskonPayment = (float) $reservasi->pembayaran()
                    ->whereIn('status_pembayaran', ['bayar_dp', 'bayar_lunas'])
                    ->sum('diskon_applied');

                $grandTotal = max($totalHarga - $diskonReservasi, 0);

                $remaining = max($grandTotal - $totalPaid - $totalDiskonPayment, 0);

                // Cek apakah sudah ada pembayaran dengan status bayar_lunas
                $hasLunas = $reservasi->pembayaran()
                    ->where('status_pembayaran', 'bayar_lunas')
                    ->exists();

                $statusDisplay = 'Belum Dibayar';
                if ($hasLunas || $remaining <= 0) {
                    $statusDisplay = 'Lunas';
                } elseif ($totalPaid > 0 && $remaining > 0) {
                    $statusDisplay = 'DP';
                }

                return [
                    'id_reservasi'   => $reservasi->id_reservasi,
                    'pelanggan_nama' => $reservasi->pelanggan->nama ?? 'Guest',
                    'tanggal'        => $reservasi->tanggal_reservasi,
                    'waktu'          => substr($reservasi->waktu_reservasi, 0, 5),
                    'total_harga'    => $totalHarga,
                    'total_bersih'   => $grandTotal,
                    'total_paid'     => $totalPaid,
                    'remaining'      => $remaining,
                    'diskon'         => $diskonReservasi,
                    'status'         => $reservasi->status_reservasi,
                    'status_display' => $statusDisplay,
                    'is_lunas'       => $hasLunas || $remaining <= 0,
                ];
            })
            // dpList = hanya yang BELUM lunas (sisa > 0)
            ->filter(function ($item) {
                return !$item['is_lunas'] && $item['remaining'] > 0;
            })
            ->values();


            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (Exception $e) {
            Log::error('PosController@dpList: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data.',
                'data' => [],
            ], 500);
        }
    }

    public function payRemaining(Request $request)
    {
        $request->validate([
            'reservasi_ids' => 'required|array|min:1',
            'reservasi_ids.*' => 'integer|exists:reservasi,id_reservasi',
            'pay_type' => 'required|in:dp,full',
            'metode_id' => 'required|integer|exists:metodepembayaran,id_metodePembayaran',
            'diskon_data' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $reservasiIds = $request->reservasi_ids;
            $admin = Auth::user();
            $orderId = 'CASH-'.time().'-'.rand(100, 999);
            $diskonData = $request->diskon_data;

            $pengaturanDp = DB::table('pengaturan_booking')->first();
            $dpTipe = $pengaturanDp?->dp_tipe ?? 'persen';
            $dpValue = (float) ($pengaturanDp?->dp_value ?? 30);

            foreach ($reservasiIds as $rid) {
                $reservasi = Reservasi::with(['pembayaran', 'reservasiLayanan'])->findOrFail($rid);

                $totalPaid = (float) $reservasi->pembayaran()
                    ->whereIn('status_pembayaran', ['bayar_dp', 'bayar_lunas'])
                    ->sum('jumlah');
                
                $totalDiskonPaid = (float) $reservasi->pembayaran()
                    ->whereIn('status_pembayaran', ['bayar_dp', 'bayar_lunas'])
                    ->sum('diskon_applied');

                $totalHarga = (float) $reservasi->total_harga;
                $remaining = max($totalHarga - $totalPaid - $totalDiskonPaid, 0);

                if ($remaining <= 0) {
                    continue;
                }

                $amountToPay = 0;

                if ($request->pay_type === 'dp' && $totalPaid <= 0) {
                    if ($dpTipe === 'persen') {
                        $amountToPay = ceil($totalHarga * ($dpValue / 100));
                    } else {
                        $amountToPay = $dpValue;
                    }
                    $amountToPay = max(1000, min($amountToPay, $remaining));
                } else {
                    $amountToPay = $remaining;
                }

                // HITUNG DISKON
                $diskonAmount = 0;
                if ($diskonData && isset($diskonData['applicable_layanan'])) {
                    $layananIdsInReservasi = $reservasi->reservasiLayanan->pluck('id_layanan')->toArray();

                    foreach ($diskonData['applicable_layanan'] as $appLayanan) {
                        if (in_array($appLayanan['id_layanan'], $layananIdsInReservasi)) {
                            $diskonAmount += (float) $appLayanan['diskon'];
                        }
                    }
                }

                // KURANGI DENGAN DISKON
                $amountToPay = max(0, $amountToPay - $diskonAmount);
                $amountToPay = round($amountToPay);

                if ($amountToPay <= 0) {
                    continue;
                }

                // TENTUKAN STATUS BERDASARKAN PAY_TYPE DAN TOTAL PEMBAYARAN
                $newTotalPaid = $totalPaid + $amountToPay;
                $newTotalDiskon = $totalDiskonPaid + $diskonAmount;
                
                $remainingAfterPayment = max($totalHarga - $newTotalPaid - $newTotalDiskon, 0);

                if ($request->pay_type === 'full') {
                    // JIKA BAYAR PENUH, SELALU LUNAS
                    $statusPembayaran = 'bayar_lunas';
                } else {
                    // JIKA DP, CEK APAKAH MASIH ADA SISA
                    $statusPembayaran = ($remainingAfterPayment <= 0) ? 'bayar_lunas' : 'bayar_dp';
                }

                Pembayaran::create([
                    'id_admin' => $admin->id ?? null,
                    'id_reservasi' => $reservasi->id_reservasi,
                    'id_metodePembayaran' => $request->metode_id,
                    'order_id' => $orderId.'-'.$rid,
                    'tanggal_pembayaran' => Carbon::now(),
                    'jumlah' => $amountToPay,
                    'diskon_applied' => $diskonAmount,
                    'status_pembayaran' => $statusPembayaran,
                    'bukti_pembayaran' => 'Pembayaran Tunai/Transfer - POS',
                ]);

                // UPDATE STATUS RESERVASI JIKA SUDAH LUNAS
                if ($statusPembayaran === 'bayar_lunas') {
                    $reservasi->update(['status_reservasi' => 'pending']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses.',
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('PosController@payRemaining: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran.',
            ], 500);
        }
    }

    private function isSlotTaken($serviceId, $date, $time)
    {
        $taken = $this->takenTimesOnDate($serviceId, $date);

        return in_array($time, $taken);
    }

    private function takenTimesOnDate($serviceId, $date)
    {
        $reservations = Reservasi::where('tanggal_reservasi', $date)
            ->whereIn('status_reservasi', ['pending', 'proses'])
            ->whereHas('pembayaran', function ($q) {
                $q->whereIn('status_pembayaran', ['bayar_dp', 'bayar_lunas']);
            })
            ->get();

        $taken = [];
        foreach ($reservations as $res) {
            $taken[] = substr($res->waktu_reservasi, 0, 5);
        }

        return array_unique($taken);
    }

    public function searchCustomer(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1',
        ]);

        try {
            $keyword = trim($request->q);

            $data = DB::table('pelanggan')
                ->leftJoin('users', 'pelanggan.user_id', '=', 'users.id') // Left join in case user was deleted but pelanggan remains (should not happen with cascade)
                ->select('pelanggan.id_pelanggan', 'pelanggan.nama', 'users.email', 'pelanggan.nomor_telepon')
                ->where(function ($q) use ($keyword) {
                    $q->where('pelanggan.nama', 'like', "%{$keyword}%")
                        ->orWhere('users.email', 'like', "%{$keyword}%")
                        ->orWhere('pelanggan.nomor_telepon', 'like', "%{$keyword}%");
                })
                ->orderBy('pelanggan.nama')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error('PosController@searchCustomer: '.$e->getMessage());

            return response()->json(['success' => false, 'data' => []], 500);
        }
    }

    public function slotJadwal(Request $request)
    {
        try {
            $request->validate([
                'id_layanan' => 'required|integer',
                'date' => 'required|date_format:Y-m-d',
            ]);

            $idLayanan = $request->id_layanan;
            $tanggalCek = $request->date;
            $tanggalCarbon = Carbon::parse($tanggalCek);
            $now = Carbon::now();

            $pengaturan = DB::table('pengaturan_booking')->first();
            $tentangKami = DB::table('tentang_kami')->first();

            if (! $pengaturan || ! $tentangKami) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengaturan tidak ditemukan.',
                ], 500);
            }

            $hariOperasional = json_decode($tentangKami->hari_operasional, true);
            if (! $hariOperasional || ! is_array($hariOperasional)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format hari operasional tidak valid.',
                ], 500);
            }

            $dayNames = [
                'Monday' => 'senin',
                'Tuesday' => 'selasa',
                'Wednesday' => 'rabu',
                'Thursday' => 'kamis',
                'Friday' => 'jumat',
                'Saturday' => 'sabtu',
                'Sunday' => 'minggu',
            ];

            $hariInggris = $tanggalCarbon->format('l');
            $hariIndonesia = $dayNames[$hariInggris] ?? null;

            if (! $hariIndonesia || ! isset($hariOperasional[$hariIndonesia])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Toko tutup pada hari ini.',
                    'slots' => [],
                ], 200);
            }

            $jamMulai = Carbon::parse($hariOperasional[$hariIndonesia]['mulai']);
            $jamSelesai = Carbon::parse($hariOperasional[$hariIndonesia]['selesai']);
            $interval = (int) $pengaturan->interval_min_booking ?: 30;

            $jadwalList = [];
            for ($waktu = $jamMulai->copy(); $waktu->lte($jamSelesai); $waktu->addMinutes($interval)) {
                $timeString = $waktu->format('H:i');

                $isDisabled = false;

                if ($tanggalCarbon->isToday()) {
                    $slotDateTime = Carbon::parse($tanggalCek.' '.$timeString);
                    if ($slotDateTime->lte($now)) {
                        $isDisabled = true;
                    }
                }

                $jadwalList[] = [
                    'time' => $timeString,
                    'disabled' => $isDisabled,
                ];
            }

            $reservedReservations = Reservasi::where('tanggal_reservasi', $tanggalCek)
                ->whereIn('status_reservasi', ['pending', 'proses'])
                ->whereHas('pembayaran', function ($q) {
                    $q->whereIn('status_pembayaran', ['bayar_dp', 'bayar_lunas']);
                })
                ->get();

            $reservedTimes = $reservedReservations
                ->pluck('waktu_reservasi')
                ->map(fn ($t) => substr($t, 0, 5))
                ->toArray();

            $finalSlots = collect($jadwalList)->map(function ($slot) use ($reservedTimes) {
                if (in_array($slot['time'], $reservedTimes)) {
                    $slot['disabled'] = true;
                }

                return $slot;
            })->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Slot jadwal berhasil dimuat',
                'slots' => $finalSlots,
            ], 200);

        } catch (Exception $e) {
            Log::error('PosController@slotJadwal: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    private function getBookingDates()
    {
        try {
            $pengaturan = DB::table('pengaturan_booking')->first();
            $tentangKami = DB::table('tentang_kami')->first();

            if (! $pengaturan || ! $tentangKami) {
                return [];
            }

            $hariOperasional = json_decode($tentangKami->hari_operasional, true);
            if (! $hariOperasional || ! is_array($hariOperasional)) {
                return [];
            }

            $dayNames = [
                'Monday' => 'senin',
                'Tuesday' => 'selasa',
                'Wednesday' => 'rabu',
                'Thursday' => 'kamis',
                'Friday' => 'jumat',
                'Saturday' => 'sabtu',
                'Sunday' => 'minggu',
            ];

            $maksRentang = (int) $pengaturan->maks_rentang_booking ?: 30;

            $dates = [];
            $tanggalMulai = Carbon::today();
            $tanggalSelesai = Carbon::today()->addDays($maksRentang);
            Carbon::setLocale('id');

            for ($tanggal = $tanggalMulai->copy(); $tanggal->lte($tanggalSelesai); $tanggal->addDay()) {
                $hariInggris = $tanggal->format('l');
                $hariIndonesia = $dayNames[$hariInggris] ?? null;

                if ($hariIndonesia && isset($hariOperasional[$hariIndonesia])) {
                    $dates[] = [
                        'date' => $tanggal->toDateString(),
                        'dw' => $tanggal->translatedFormat('D'),
                        'dd' => $tanggal->format('d'),
                        'mon' => $tanggal->translatedFormat('M'),
                    ];
                }
            }

            return $dates;
        } catch (Exception $e) {
            Log::error('PosController@getBookingDates: '.$e->getMessage());

            return [];
        }
    }

    public function getAvailableVouchers(Request $request)
    {
        $request->validate([
            'layanan_ids' => 'required|array',
            'layanan_ids.*' => 'integer|exists:layanan,id_layanan',
        ]);

        try {
            $layananIds = $request->layanan_ids;

            // Ambil semua diskon yang terkait dengan layanan yang dipilih
            $diskons = DB::table('diskon')
                ->join('layanan', 'layanan.id_diskon', '=', 'diskon.id')
                ->whereIn('layanan.id_layanan', $layananIds)
                ->where('diskon.status_diskon', 'aktif')
                ->where('diskon.tanggal_mulai', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('diskon.tanggal_berakhir')
                        ->orWhere('diskon.tanggal_berakhir', '>=', now());
                })
                ->select('diskon.*')
                ->distinct()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $diskons,
            ]);
        } catch (Exception $e) {
            Log::error('PosController@getAvailableVouchers: '.$e->getMessage());

            return response()->json(['success' => false, 'data' => []], 500);
        }
    }

    public function validateVoucherPos(Request $request)
    {
        $request->validate([
            'kode_diskon' => 'required|string',
            'layanan_ids' => 'required|array',
            'layanan_ids.*' => 'integer|exists:layanan,id_layanan',
        ]);

        try {
            $diskon = DB::table('diskon')
                ->where('kode_diskon', $request->kode_diskon)
                ->where('status_diskon', 'aktif')
                ->where('tanggal_mulai', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('tanggal_berakhir')
                        ->orWhere('tanggal_berakhir', '>=', now());
                })
                ->first();

            if (! $diskon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode voucher tidak valid atau sudah tidak aktif.',
                ], 404);
            }

            $layananIds = $request->layanan_ids;

            // Cek layanan mana yang memiliki diskon ini
            $layananWithDiskon = DB::table('layanan')
                ->whereIn('id_layanan', $layananIds)
                ->where('id_diskon', $diskon->id)
                ->pluck('id_layanan', 'id_layanan')
                ->toArray();

            if (empty($layananWithDiskon)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher tidak dapat diterapkan pada layanan yang dipilih.',
                ], 400);
            }

            // Hitung total diskon
            $layananData = DB::table('layanan')
                ->whereIn('id_layanan', array_keys($layananWithDiskon))
                ->get(['id_layanan', 'harga']);

            $totalDiskon = 0;
            $applicableLayanan = [];

            foreach ($layananData as $layanan) {
                $diskonAmount = $layanan->harga * ($diskon->persentase_diskon / 100);
                $totalDiskon += $diskonAmount;

                $applicableLayanan[] = [
                    'id_layanan' => $layanan->id_layanan,
                    'diskon' => round($diskonAmount, 2),
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil diterapkan!',
                'data' => [
                    'diskon_id' => $diskon->id,
                    'kode_diskon' => $diskon->kode_diskon,
                    'nama_diskon' => $diskon->nama_diskon,
                    'persentase' => $diskon->persentase_diskon,
                    'total_diskon' => round($totalDiskon, 2),
                    'applicable_layanan' => $applicableLayanan,
                ],
            ]);

        } catch (Exception $e) {
            Log::error('PosController@validateVoucherPos: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memvalidasi voucher.',
            ], 500);
        }
    }
}
