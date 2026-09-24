<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPendapatanExport;
use App\Exports\LaporanRerataExport;
use App\Exports\LaporanTransaksiExport;
use App\Exports\PembayaranExport;
use App\Imports\PembayaranImport;
use App\Models\Diskon;
use App\Models\Layanan;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;
use App\Traits\AdminNotifiable;


class PembayaranController extends Controller
{
    use AdminNotifiable;

    public function __construct()
    {
        Carbon::setLocale('id');
    }

    public function proses(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'pay_type' => 'required|in:dp,full',
            'metode_id' => 'nullable|integer|exists:metodepembayaran,id_metodePembayaran',
            'reservasi_ids' => 'required|array|min:1',
            'reservasi_ids.*' => 'integer|exists:reservasi,id_reservasi',
            'diskon_data' => 'nullable|array', // TAMBAHAN BARU untuk voucher
        ]);

        $reservasiIds = $request->reservasi_ids;
        $payType = $request->pay_type;
        $metodeId = $request->metode_id;
        $diskonData = $request->diskon_data; // TAMBAHAN BARU

        try {
            // 2. Konfigurasi Midtrans
            Config::$serverKey = config('midtrans.serverKey');
            Config::$isProduction = config('midtrans.isProduction');
            Config::$isSanitized = config('midtrans.isSanitized');
            Config::$is3ds = config('midtrans.is3ds');

            // 3. Ambil Pengaturan DP
            $pengaturanDp = DB::table('pengaturan_booking')->select('dp_tipe', 'dp_value')->first();
            $dpTipe = $pengaturanDp?->dp_tipe ?? 'persen';
            $dpValue = (float) ($pengaturanDp?->dp_value ?? 30);

            $totalGrossAmount = 0;
            $itemDetails = [];
            $customerDetails = null;
            $affectedReservasi = [];

            // 4. Loop Reservasi untuk Hitung Total Bayar
            $reservasiModels = Reservasi::with(['pelanggan.user', 'pembayaran' => function ($query) {
                $query->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp']);
            }])
                ->whereIn('id_reservasi', $reservasiIds)
                ->get();

            foreach ($reservasiModels as $reservasi) {
                $customer = $reservasi->pelanggan;

                if (is_null($customerDetails)) {
                    $customerDetails = [
                        'first_name' => $customer->nama_depan,
                        'last_name' => $customer->nama_belakang,
                        'email' => $customer->user->email,
                        'phone' => $customer->nomor_telepon,
                    ];
                }

                $finalAmountTotal = (float) $reservasi->total_harga;
                $totalPaid = (float) $reservasi->pembayaran->sum('jumlah'); // Hanya sukses
                $remaining = max($finalAmountTotal - $totalPaid, 0);

                if ($remaining <= 0) {
                    continue;
                }

                // 5. Hitung Jumlah Bayar (SINKRON dengan JS)
                $amountToPay = 0;
                $paymentName = '';

                // KASUS 1: WAJIB PELUNASAN (Sudah Bayar DP)
                if ($totalPaid > 0) {
                    $amountToPay = $remaining;
                    $paymentName = 'Pelunasan Sisa Tagihan';
                }
                // KASUS 2: BELUM BAYAR (Bisa DP atau Full)
                elseif ($payType === 'dp') {
                    if ($dpTipe === 'persen') {
                        $amountToPay = ceil($finalAmountTotal * ($dpValue / 100));
                    } else {
                        $amountToPay = $dpValue;
                    }
                    $amountToPay = max(1000, min($amountToPay, $remaining));
                    $paymentName = 'Down Payment (DP)';
                } else { // payType === 'full'
                    $amountToPay = $remaining;
                    $paymentName = 'Pembayaran Penuh Reservasi';
                }

                // TAMBAHAN BARU: Cari dan aplikasikan diskon untuk reservasi ini
                $diskonAmount = 0;
                if ($diskonData && isset($diskonData['applicable_reservasi'])) {
                    foreach ($diskonData['applicable_reservasi'] as $appRes) {
                        if ($appRes['id_reservasi'] == $reservasi->id_reservasi) {
                            $diskonAmount = (float) $appRes['diskon'];
                            break;
                        }
                    }
                }

                // Kurangi dengan diskon
                $amountToPay = max(0, $amountToPay - $diskonAmount);
                $amountToPay = round($amountToPay);

                if ($amountToPay <= 0) {
                    continue;
                }

                $totalGrossAmount += $amountToPay;

                // Detail Item untuk Midtrans Snap
                $itemName = "{$paymentName} (ID: {$reservasi->id_reservasi})";
                if ($diskonAmount > 0) {
                    $itemName .= ' - Diskon Rp '.number_format($diskonAmount, 0, ',', '.');
                }

                $itemDetails[] = [
                    'id' => "RSV-{$reservasi->id_reservasi}-".strtoupper($payType),
                    'price' => $amountToPay,
                    'quantity' => 1,
                    'name' => $itemName,
                ];

                // Detail Reservasi yang akan dikirim kembali ke JS Callback
                $affectedReservasi[] = [
                    'reservasi_id' => $reservasi->id_reservasi,
                    'amount' => $amountToPay,
                    'diskon_amount' => $diskonAmount, // TAMBAHAN BARU
                    'pay_type_calc' => $paymentName,
                ];

                // Update status reservasi menjadi pending jika belum pernah dibayar (initial payment)
                if ($reservasi->status_reservasi !== 'pending') {
                    $reservasi->update(['status_reservasi' => 'pending']);
                }
            }

            if ($totalGrossAmount <= 0) {
                return response()->json(['success' => false, 'message' => 'Tidak ada reservasi yang perlu dibayar.'], 400);
            }

            // 6. Generate Order ID TUNGGAL
            $orderId = 'TRX-MULTI-'.time().'-'.rand(100, 999);

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $totalGrossAmount,
                ],
                'customer_details' => $customerDetails,
                'item_details' => $itemDetails,
                // Gunakan custom_field1 untuk menyimpan tipe pembayaran yang dipilih klien
                'custom_field1' => json_encode([
                    'pay_type_selected' => $payType,
                    'diskon_applied' => $diskonData ? true : false, // TAMBAHAN BARU
                ]),
            ];

            // 7. Ambil Token Snap TUNGGAL
            $snapToken = Snap::getSnapToken($params);

            // 8. Kembalikan respons tunggal
            return response()->json([
                'success' => true,
                'token' => [
                    'snap_token' => $snapToken,
                    'order_id' => $orderId,
                    'amount' => $totalGrossAmount,
                    // Data yang dihitung di PHP harus dikirim kembali ke JS
                    'reservasi_ids' => array_column($affectedReservasi, 'reservasi_id'),
                    'amounts' => $affectedReservasi, // Penting: list ID, amount, dan diskon
                    'metode_id' => $metodeId,
                    'pay_type_selected' => $payType,
                    'diskon_data' => $diskonData, // TAMBAHAN BARU: kirim kembali data diskon
                ],
            ]);

            // ===========================
            // NOTIFIKASI ADMIN: NEW BOOKING (SNAP GENERATED)
            // ===========================
            $firstReservasi = $reservasiModels->first();
            $customerName = $firstReservasi->pelanggan->nama ?? 'Pelanggan';
            $tglList = $reservasiModels->map(function($r) { 
                return $r->tanggal_reservasi ? $r->tanggal_reservasi->format('d/m/Y') : '-'; 
            })->unique()->implode(', ');

            $this->notifyAdmins([
                'title'   => 'Booking Baru (Checkout)',
                'message' => "Pelanggan {$customerName} memesan (Tgl: {$tglList}). Menunggu Pembayaran: Rp " . number_format($totalGrossAmount, 0, ',', '.'),
                'type'    => 'info',
                'link'    => route('booking.list', ['search' => $orderId]),
                'icon'    => 'fas fa-calendar-plus',
            ]);


            return $response;


        } catch (\Throwable $e) {
            Log::error('Midtrans proses error (Multi): '.$e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function midtransJsCallback(Request $request)
    {
        // 1. Validasi Input dari JS
        try {
            $request->validate([
                'reservasi_ids' => 'required|array|min:1',
                'order_id' => 'required|string',
                'transaction_status' => 'required|string',
                'payment_type' => 'required|string',
                'metode_id' => 'required|integer|exists:metodepembayaran,id_metodePembayaran',
                'amounts' => 'required|array',
                'amounts.*.reservasi_id' => 'required|integer|exists:reservasi,id_reservasi',
                'amounts.*.amount' => 'required|numeric|min:0',
                'pay_type_selected' => 'required|in:dp,full',
                'diskon_data' => 'nullable|array', // TAMBAHAN BARU
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Midtrans Callback Validation Error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $reservasiIds = $request->reservasi_ids;
        $orderId = $request->order_id;
        $transactionStatus = $request->transaction_status;
        $paymentType = $request->payment_type;
        $metodeId = $request->metode_id;
        $amountsPaid = collect($request->amounts)->keyBy('reservasi_id');
        $payTypeSelected = $request->pay_type_selected;
        $diskonData = $request->diskon_data; // TAMBAHAN BARU

        // 2. Tentukan status pembayaran yang valid untuk disimpan
        $validStatusToSave = ['capture', 'settlement', 'pending'];

        if (! in_array($transactionStatus, $validStatusToSave)) {
            Log::warning('Midtrans Callback: Transaksi bukan status simpan. Status: '.$transactionStatus);

            return response()->json(['success' => false, 'message' => 'Transaksi gagal/dibatalkan. Tidak ada data yang disimpan.'], 400);
        }

        DB::beginTransaction();
        try {
            $midtransStatus = ($transactionStatus === 'pending' ? 'pending' : 'paid');
            $updatedReservasiIds = [];

            // 3. Loop untuk setiap Reservasi yang terlibat
            foreach ($reservasiIds as $rid) {
                $amountData = $amountsPaid->get((int) $rid);
                $amountToSave = $amountData['amount'] ?? 0;

                if ($amountToSave <= 0) {
                    continue;
                }

                $order_id_per_reservasi = $orderId.'-'.$rid;

                // TAMBAHAN BARU: Ambil diskon amount untuk reservasi ini
                $diskonAmount = 0;
                if ($diskonData && isset($diskonData['applicable_reservasi'])) {
                    foreach ($diskonData['applicable_reservasi'] as $appRes) {
                        if ($appRes['id_reservasi'] == $rid) {
                            $diskonAmount = (float) $appRes['diskon'];
                            break;
                        }
                    }
                }

                // Cek apakah pembayaran dengan Order ID (order_id) sudah pernah disimpan untuk reservasi ini
                $existingPayment = Pembayaran::where('order_id', $order_id_per_reservasi)
                    ->where('id_reservasi', $rid)
                    ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp', 'pending'])
                    ->first();

                // 4. Jika sudah ada, update statusnya jika berubah dari pending ke paid/lunas
                if ($existingPayment) {
                    if ($midtransStatus === 'paid' && $existingPayment->status_pembayaran === 'pending') {
                        $reservasi = Reservasi::findOrFail($rid);
                        $finalAmountTotal = (float) $reservasi->total_harga;


                        // Total bayar SEBELUM update (hanya yang sudah pasti PAID)
                        $totalPaidBeforeUpdate = (float) $reservasi->pembayaran()
                            ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                            ->where('id_pembayaran', '!=', $existingPayment->id_pembayaran)
                            ->sum('jumlah');

                        // Total Diskon Applied SEBELUM update
                        $totalDiskonBefore = (float) $reservasi->pembayaran()
                            ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                            ->where('id_pembayaran', '!=', $existingPayment->id_pembayaran)
                            ->sum('diskon_applied');

                        $totalPaidAfterUpdate = $totalPaidBeforeUpdate + $amountToSave;
                        $totalDiskonAfterUpdate = $totalDiskonBefore + $existingPayment->diskon_applied; // diskon pembayaran ini

                        $remainingAfterThisPayment = max($finalAmountTotal - $totalPaidAfterUpdate - $totalDiskonAfterUpdate, 0);

                        $newPaymentStatus = ($remainingAfterThisPayment <= 0) ? 'bayar_lunas' : 'bayar_dp';


                        // Tentukan status pembayaran DESKRIPTIF baru (bayar_lunas atau bayar_dp)

                        $existingPayment->update([
                            'tanggal_pembayaran' => now(),
                            'status_pembayaran' => $newPaymentStatus,
                            'diskon_applied' => $diskonAmount, // TAMBAHAN BARU
                        ]);

                        // Status Reservasi TIDAK diubah di sini, hanya di NotificationHandler
                    }
                    $updatedReservasiIds[] = $rid;

                    continue;
                }

                // 5. Jika Belum ada, buat record baru

                $reservasi = Reservasi::findOrFail($rid);
                $finalAmountTotal = (float) $reservasi->total_harga;
                $totalPaidBefore = (float) $reservasi->pembayaran()->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])->sum('jumlah');

                $pembayaranStatus = 'pending';

                if ($midtransStatus === 'paid') {
                    $totalPaidCurrent = $totalPaidBefore + $amountToSave;
                    
                    // Ambil total diskon yang SUDAH ada
                    $totalDiskonExisting = (float) $reservasi->pembayaran()->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])->sum('diskon_applied');
                    $totalDiskonCurrent = $totalDiskonExisting + $diskonAmount;

                    $remainingAfterThisPayment = max($finalAmountTotal - $totalPaidCurrent - $totalDiskonCurrent, 0);

                    $pembayaranStatus = ($remainingAfterThisPayment <= 0) ? 'bayar_lunas' : 'bayar_dp';


                } else { // pending
                    $pembayaranStatus = 'pending';
                }

                // Simpan ke tabel pembayaran
                Pembayaran::create([
                    'id_reservasi' => $rid,
                    'id_metodePembayaran' => $metodeId,
                    'tanggal_pembayaran' => $midtransStatus === 'paid' ? now() : null,
                    'jumlah' => $amountToSave,
                    'diskon_applied' => $diskonAmount, // TAMBAHAN BARU
                    'metode' => $paymentType,
                    'status_pembayaran' => $pembayaranStatus,
                    'order_id' => $order_id_per_reservasi,
                    'pay_type_selected' => $payTypeSelected,
                ]);

                $updatedReservasiIds[] = $rid;

                // --- CART CLEANUP: Hapus reservasi PENDING lain yang menempati slot yang sama ---
                try {
                    // Cari reservasi lain (layanan sama, waktu sama, tgl sama) yang masih 'pending' dan BELUM bayar.
                    $conflictingReservations = Reservasi::where('id_reservasi', '!=', $rid)
                        ->where('tanggal_reservasi', $reservasi->tanggal_reservasi)
                        ->where('waktu_reservasi', $reservasi->waktu_reservasi)
                        ->where('status_reservasi', 'pending')
                        ->whereDoesntHave('pembayaran')
                        ->whereHas('layanan', function($q) use ($reservasi) {
                            $q->whereIn('layanan.id_layanan', $reservasi->layanan->pluck('id_layanan'));
                        })
                        ->get();

                    foreach ($conflictingReservations as $conRes) {
                        $conRes->delete();
                        Log::info("Cart cleanup: Reservasi #{$conRes->id_reservasi} dihapus karena slot telah dibayar oleh Reservasi #{$rid}");
                    }
                } catch (\Exception $eCleanup) {
                    Log::error("Cart cleanup error for ID {$rid}: " . $eCleanup->getMessage());
                    // Kita tidak melempar exception agar transaksi utama tetap jalan
                }
            }

            DB::commit();

            // ===========================
            // NOTIFIKASI ADMIN: PEMBAYARAN BERHASIL (JS CALLBACK)
            // ===========================
            if ($midtransStatus === 'paid') {
                $firstRid = $updatedReservasiIds[0] ?? null;
                $reservasiObj = \App\Models\Reservasi::with('pelanggan')->find($firstRid);
                $customerName = $reservasiObj->pelanggan->nama ?? 'Pelanggan';
                $tglBooking = $reservasiObj->tanggal_reservasi ? $reservasiObj->tanggal_reservasi->format('d/m/Y') : '-';

                $this->notifyAdmins([
                    'title'   => 'Pembayaran Berhasil',
                    'message' => "Pembayaran Pelanggan {$customerName} (Reservasi tgl {$tglBooking}) telah diterima.",
                    'type'    => 'success',
                    'link'    => route('booking.list', ['search' => $orderId]),
                    'icon'    => 'fas fa-check-circle',
                ]);

            }

            return response()->json([
                'success' => true,
                'reservasi_ids' => $updatedReservasiIds,
                'status' => $midtransStatus,
                'message' => 'Data pembayaran telah disimpan.',
            ]);



        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Midtrans Callback DB Error (Multi): '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan pembayaran ke DB.'], 500);
        }
    }

    // ==========================================================
    // MIDTRANS NOTIFICATION HANDLER (WEBHOOK)
    // ==========================================================
    /**
     * Penanganan notifikasi Midtrans (Webhook) untuk transaksi JAMAK.
     */
    public function notificationHandler(Request $request)
    {
        // 1. Konfigurasi
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction', false);

        try {
            // 2. Terima Notifikasi
            $notification = new Notification;
            $orderId = $notification->order_id; // Order ID TUNGGAL dari Midtrans
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;

            // 3. Tentukan Status Midtrans (paid/failed/pending)
            $midtransStatus = null;
            if ($transactionStatus == 'settlement' || ($transactionStatus == 'capture' && $fraudStatus == 'accept')) {
                $midtransStatus = 'paid';
            } elseif ($transactionStatus == 'pending') {
                $midtransStatus = 'pending';
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $midtransStatus = 'failed';
            }

            if (is_null($midtransStatus)) {
                return response('Status not processed: '.$transactionStatus, 200);
            }

            // 4. Ambil SEMUA pembayaran yang terkait dengan order_id TUNGGAL ini
            $payments = Pembayaran::where('order_id', 'LIKE', $orderId.'-%')->get();

            if ($payments->isEmpty()) {
                Log::warning("Midtrans Notification: Order ID (order_id) {$orderId} tidak ditemukan.");

                return response('Order ID not found', 404);
            }

            DB::beginTransaction();

            foreach ($payments as $pembayaran) {
                $oldStatus = $pembayaran->status_pembayaran;
                $reservasi = Reservasi::find($pembayaran->id_reservasi);

                // Jika status sudah final di DB dan notifikasi bukan status perubahan (misal dari pending ke paid), lewati
                if (($oldStatus === 'bayar_lunas' || $oldStatus === 'bayar_dp') && $midtransStatus !== 'failed') {
                    continue;
                }

                // 5. Update Status Pembayaran (Deskritif)
                if ($midtransStatus === 'paid') {

                    $reservasi = Reservasi::findOrFail($pembayaran->id_reservasi);
                    $finalAmountTotal = (float) $reservasi->total_harga;

                    // Total Diskon SEBELUM pembayaran ini
                    $totalDiskonBefore = (float) $reservasi->pembayaran()
                        ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                        ->where('id_pembayaran', '!=', $pembayaran->id_pembayaran)
                        ->sum('diskon_applied');

                    $amountToSave = (float) $pembayaran->jumlah;
                    $diskonToSave = (float) $pembayaran->diskon_applied;

                    $totalPaidBefore = (float) $reservasi->pembayaran()
                        ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                        ->where('id_pembayaran', '!=', $pembayaran->id_pembayaran)
                        ->sum('jumlah');

                    $totalPaidCurrent = $totalPaidBefore + $amountToSave;
                    $totalDiskonCurrent = $totalDiskonBefore + $diskonToSave;

                    $remainingAfterThisPayment = max($finalAmountTotal - $totalPaidCurrent - $totalDiskonCurrent, 0);

                    // Tentukan newPaymentStatus
                    $newPaymentStatus = ($remainingAfterThisPayment <= 0) ? 'bayar_lunas' : 'bayar_dp';

                    // Lakukan Update Pembayaran
                    $pembayaran->update([
                        'status_pembayaran' => $newPaymentStatus,
                        'tanggal_pembayaran' => Carbon::now(),
                    ]);

                    // Update Status Reservasi (Jika Lunas)
                    // Logic Baru: Bayar lunas = 'proses' (Siap dilayani), bukan 'selesai'.
                    // Nanti middleware yang akan ubah ke 'selesai' jika waktunya sudah lewat.
                    if ($newPaymentStatus === 'bayar_lunas' && $reservasi->status_reservasi === 'pending') {
                        $reservasi->update(['status_reservasi' => 'proses']);
                    }

                    // --- CART CLEANUP: Hapus reservasi PENDING lain yang menempati slot yang sama ---
                    try {
                        $conflictingReservations = Reservasi::where('id_reservasi', '!=', $reservasi->id_reservasi)
                            ->where('tanggal_reservasi', $reservasi->tanggal_reservasi)
                            ->where('waktu_reservasi', $reservasi->waktu_reservasi)
                            ->where('status_reservasi', 'pending')
                            ->whereDoesntHave('pembayaran')
                            ->whereHas('layanan', function($q) use ($reservasi) {
                                $q->whereIn('layanan.id_layanan', $reservasi->layanan->pluck('id_layanan'));
                            })
                            ->get();

                        foreach ($conflictingReservations as $conRes) {
                            $conRes->delete();
                            Log::info("Cart cleanup (Webhook): Reservasi #{$conRes->id_reservasi} dihapus karena slot telah dibayar oleh Reservasi #{$reservasi->id_reservasi}");
                        }
                    } catch (\Exception $eCleanup) {
                        Log::error("Cart cleanup error (Webhook) for ID {$reservasi->id_reservasi}: " . $eCleanup->getMessage());
                    }

                } elseif ($midtransStatus === 'failed') {
                    $newPaymentStatus = 'failed';
                    $pembayaran->update(['status_pembayaran' => $newPaymentStatus]);

                    // Jika gagal dan tidak ada pembayaran PAID lain yang masuk, batalkan reservasi
                    $totalPaidLain = (float) Pembayaran::where('id_reservasi', $pembayaran->id_reservasi)
                        ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                        ->sum('jumlah');

                    if ($totalPaidLain == 0 && $reservasi->status_reservasi !== 'dibatalkan') {
                        $reservasi->update(['status_reservasi' => 'dibatalkan']);
                    }
                }
            }

            DB::commit();

            // ===========================
            // NOTIFIKASI ADMIN: PEMBAYARAN BERHASIL (WEBHOOK)
            // ===========================
            if ($midtransStatus === 'paid') {
                $firstReservasi = $reservasi; // dari loop terakhir? Sebaiknya ambil satu saja
                $customerName = $firstReservasi->pelanggan->nama ?? 'Pelanggan';
                $tglBooking = $firstReservasi->tanggal_reservasi ? $firstReservasi->tanggal_reservasi->format('d/m/Y') : '-';

                $this->notifyAdmins([
                    'title'   => 'Pembayaran Berhasil (Webhook)',
                    'message' => "Konfirmasi Pembayaran Pelanggan {$customerName} (Tgl: {$tglBooking}) telah diproses.",
                    'type'    => 'success',
                    'link'    => route('booking.list', ['search' => $orderId]),
                    'icon'    => 'fas fa-check-circle',
                ]);

            }


            return response('OK', 200);


        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Midtrans notification handler error (Multi): '.$e->getMessage());

            return response('Server Error', 500);
        }
    }
    // ====== Helpers dan Metode Laporan (Metode pendukung lain) ======

    /**
     * Builder dasar + filter, TANPA select kolom.
     */
    private function baseQueryWithFilters(Request $request)
    {
        $q = Pembayaran::query()
            ->leftJoin('metodepembayaran as mp', 'mp.id_metodePembayaran', '=', 'pembayaran.id_metodePembayaran');

        $start = $request->query('start_date');
        $end = $request->query('end_date');

        if ($start) {
            $q->where('pembayaran.tanggal_pembayaran', '>=', Carbon::parse($start)->startOfDay());
        }
        if ($end) {
            $q->where('pembayaran.tanggal_pembayaran', '<=', Carbon::parse($end)->endOfDay());
        }

        if ($request->filled('metode')) {
            $metode = MetodePembayaran::find($request->metode);           // Cari ID dari DB
            $q->where('mp.nama', $metode->nama ?? 'INVALID_METHOD_NAME'); // Pastikan menggunakan nama metode
        }

        if ($request->filled('status')) {
            $q->where('pembayaran.status_pembayaran', $request->query('status'));
        }

        return $q;
    }

    private function mapRow($p): array
    {
        $fmt = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $ts = ! empty($p->tanggal_pembayaran) ? Carbon::parse($p->tanggal_pembayaran) : null;

        $jumlah = (float) ($p->jumlah ?? 0);
        $diskon = (float) ($p->diskon_applied ?? 0);
        $total = $jumlah;

        return [
            'id_pembayaran' => $p->id_pembayaran,
            'id_reservasi' => $p->id_reservasi,
            'tanggal_pembayaran' => $ts ? $ts->format('Y-m-d H:i:s') : null,
            'formatted_tanggal' => $ts ? $ts->format('d-m-Y H:i') : '-',
            'jumlah' => $jumlah,
            'formatted_jumlah' => $fmt($jumlah),
            'diskon' => $diskon,
            'formatted_diskon' => $fmt($diskon),
            'total' => $total,
            'formatted_total' => $fmt($total),
            'status' => $p->status_pembayaran ?? null,
            'formatted_status' => ucfirst($p->status_pembayaran ?? '-'),
            'metode' => $p->nama_metode ?? null,
            'formatted_metode' => $p->nama_metode ?? '-',
        ];
    }

    // ====== Views
    public function laporan_pendapatan()
    {
        // ambil semua metode pembayaran aktif
        $metodes = MetodePembayaran::where('status', 'aktif')
            ->orderBy('nama')
            ->get(['id_metodePembayaran', 'nama']);

        return view('admin.laporan.pendapatan', compact('metodes'));
    }

    public function laporan_transaksi()
    {
        return view('admin.laporan.transaksi');
    }

    public function laporan_rata_rata()
    {
        return view('admin.laporan.rata_rata');
    }

    protected function buildPendapatanData(Request $request)
    {
        // Query agregat per HARI
        $rows = $this->baseQueryWithFilters($request)
            ->selectRaw('DATE(pembayaran.tanggal_pembayaran) as tanggal_hari')
            ->selectRaw('SUM(pembayaran.jumlah) as total_jumlah')
            ->selectRaw('SUM(COALESCE(pembayaran.diskon_applied,0)) as total_diskon')
            ->selectRaw('COUNT(*) as total_transaksi')
            ->groupBy('tanggal_hari')
            ->orderByDesc('tanggal_hari')
            ->get();

        $fmtRupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');

        // Label metode pembayaran (sesuai filter)
        $metodeLabel = 'Semua Metode';
        if ($request->filled('metode')) {
            $metode = MetodePembayaran::find($request->metode);
            $metodeLabel = $metode->nama ?? ('Metode #'.$request->metode);
        }

        // Data baris (dipakai TABEL / PDF / EXCEL)
        $mapped = $rows->map(function ($row) use ($fmtRupiah, $metodeLabel) {
            $tanggal = Carbon::parse($row->tanggal_hari);

            return [
                'formatted_tanggal' => $tanggal->translatedFormat('l, d F Y'),
                'formatted_jumlah' => $row->total_transaksi.' transaksi',
                'formatted_metode' => $metodeLabel,
                'formatted_status' => 'Rekap Harian',
                'formatted_diskon' => $row->total_diskon,
                'formatted_total' => $fmtRupiah($row->total_jumlah - $row->total_diskon),
            ];
        });

        // Summary total (untuk periode yg difilter)
        $sumJumlah = (float) $rows->sum('total_jumlah');
        $sumDiskon = (float) $rows->sum('total_diskon');
        $sumTotal = $sumJumlah - $sumDiskon;

        $summary = [
            'total_jumlah' => $fmtRupiah($sumJumlah),
            'total_diskon' => $fmtRupiah($sumDiskon),
            'total_bayar' => $fmtRupiah($sumTotal),
            'count' => $rows->count(), // jumlah HARI
        ];

        // Judul laporan
        $title = $this->makeTitle(
            'Laporan Pendapatan Harian',
            $request->query('start_date'),
            $request->query('end_date')
        );
        if (! $request->query('start_date') && ! $request->query('end_date')) {
            $title = 'Laporan Pendapatan Harian';
        }

        return [$mapped, $summary, $title];
    }

    // ====== AJAX untuk tabel "Pembayaran" (umum)
    public function ajax(Request $request)
    {
        try {
            $rows = $this->baseQueryWithFilters($request)
                ->with(['reservasi.pelanggan', 'reservasi.layanan']) // Eager Load
                ->select('pembayaran.*', 'mp.nama as nama_metode')
                ->orderByDesc('pembayaran.tanggal_pembayaran')
                ->get();

            // Filter unique per reservation (ambil yang paling baru karena orderByDesc)
            $uniqueRows = $rows->unique('id_reservasi');

            $data = $uniqueRows->map(function ($p) {
                // Base fields
                $row = $this->mapRow($p);

                // --- CUSTOMER & RESERVATION DETAILS ---
                $pelanggan = $p->reservasi ? $p->reservasi->pelanggan : null;
                $row['pelanggan_nama'] = $pelanggan ? $pelanggan->nama : '-';
                $row['pelanggan_telp'] = $pelanggan ? $pelanggan->nomor_telepon : '-';
                
                // Layanan Names
                $layananNames = '-';
                if ($p->reservasi && $p->reservasi->layanan) {
                     $layananNames = $p->reservasi->layanan->map(function($l) {
                        return $l->pivot->nama_layanan_snapshot ?? $l->nama_layanan;
                    })->join(', ');
                }
                $row['layanan_nama'] = $layananNames;

                // Catatan & Financials
                $row['catatan'] = $p->reservasi ? $p->reservasi->catatan : '-';
                $row['total_reservasi'] = $p->reservasi ? $p->reservasi->total_harga : 0;
                $row['formatted_total_reservasi'] = 'Rp ' . number_format($row['total_reservasi'], 0, ',', '.');
                
                // Total Paid calculation & History
                $totalPaid = 0;
                $history = [];
                
                if ($p->reservasi) {
                    $payments = $p->reservasi->pembayaran()
                        ->with('metodePembayaran')
                        ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                        ->orderBy('tanggal_pembayaran')
                        ->get();

                    foreach ($payments as $pay) {
                        $totalPaid += $pay->jumlah;
                        $history[] = [
                            'id' => $pay->id_pembayaran,
                            'tanggal' => $pay->tanggal_pembayaran ? Carbon::parse($pay->tanggal_pembayaran)->format('d-m-Y H:i') : '-',
                            'jumlah' => 'Rp ' . number_format($pay->jumlah, 0, ',', '.'),
                            'tipe' => $pay->status_pembayaran == 'bayar_dp' ? 'DP' : 'Pelunasan',
                            'metode' => $pay->metodePembayaran ? $pay->metodePembayaran->nama : '-',
                            'status' => ucfirst($pay->status_pembayaran)
                        ];
                    }
                }
                
                $row['total_paid'] = $totalPaid;
                $row['formatted_total_paid'] = 'Rp ' . number_format($totalPaid, 0, ',', '.');
                
                // OVERWRITE 'jumlah' column to show Total Paid accumulated, NOT single transaction amount
                $row['jumlah'] = $totalPaid;
                $row['formatted_jumlah'] = $row['formatted_total_paid'];
                
                // OVERWRITE 'total' column to show Total Reservation Price (Tagihan)
                $row['total'] = $row['total_reservasi'];
                $row['formatted_total'] = $row['formatted_total_reservasi'];

                $row['payment_history'] = $history; // Simpan history sebagai array

                // Recalculate Total Discount from history
                $totalDiskon = 0;
                if ($p->reservasi) {
                     $totalDiskon = $p->reservasi->pembayaran()
                        ->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp'])
                        ->sum('diskon_applied');
                }
                
                $sisa = max($row['total_reservasi'] - $totalPaid - $totalDiskon, 0);
                $row['sisa_pembayaran'] = $sisa;
                $row['formatted_sisa'] = 'Rp ' . number_format($sisa, 0, ',', '.');

                // --- Calculate Biaya Tambahan ---
                $sumLayanan = 0;
                if ($p->reservasi && $p->reservasi->layanan) {
                    $sumLayanan = $p->reservasi->layanan->sum(function($l) {
                        return $l->pivot->harga_deal ?? $l->harga;
                    });
                }
                $biayaTambahan = max($row['total_reservasi'] - $sumLayanan, 0);
                $row['biaya_tambahan'] = $biayaTambahan;
                $row['formatted_biaya_tambahan'] = $biayaTambahan > 0 ? 'Rp ' . number_format($biayaTambahan, 0, ',', '.') : '-';

                // --- Override Status if Unpaid Balance Exists ---
                if ($sisa > 0) {
                     $row['formatted_status'] = 'Tunggakan / Belum Lunas';
                     // Optional: You might want to add a class for styling in frontend
                     $row['status_class_override'] = 'bg-danger';
                }

                return $row;
            })->values(); // Reset keys after unique

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('Pembayaran ajax error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'message' => 'Server error memuat pembayaran'], 500);
        }
    }

    // ====== AJAX: Report Pendapatan
    public function ajaxPendapatan(Request $request)
    {
        try {
            [$data, $summary] = $this->buildPendapatanData($request);

            return response()->json([
                'success' => true,
                'data' => $data,
                'summary' => $summary,
            ]);
        } catch (\Throwable $e) {
            \Log::error('ajaxPendapatan error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error memuat pendapatan',
            ], 500);
        }
    }

    // ====== AJAX: Report Transaksi
    public function ajaxTransaksi(Request $request)
    {
        try {
            $rows = $this->baseQueryWithFilters($request)
                ->with(['reservasi.layanan'])
                ->select('pembayaran.*', 'mp.nama as nama_metode')
                ->orderByDesc('pembayaran.tanggal_pembayaran')
                ->get();

            $data = $rows->map(function ($p) {
                $row = $this->mapRow($p);
                $row['pelanggan_nama'] = $row['pelanggan_nama'] ?? '-';
                
                // Format nama layanan dari snapshot
                $layananNames = $p->reservasi && $p->reservasi->layanan 
                    ? $p->reservasi->layanan->map(function($l) {
                        return $l->pivot->nama_layanan_snapshot ?? $l->nama_layanan;
                    })->join(', ')
                    : '-';
                    
                $row['layanan_nama'] = $layananNames ?: '-';

                return $row;
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $rows->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('ajaxTransaksi error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'message' => 'Server error memuat transaksi'], 500);
        }
    }

    // ====== Export / Import
    public function export()
    {
        $filename = 'pembayaran-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new PembayaranExport, $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls|max:20480',
        ]);

        try {
            $importer = new PembayaranImport;
            Excel::import($importer, $request->file('file'));
            $report = $importer->getReport();

            $msg = "Import pembayaran selesai. Inserted: {$report['inserted']}, Updated: {$report['updated']}, Skipped: {$report['skipped']}.";

            if (! empty($report['errors'])) {
                $errorMsg = collect($report['errors'])
                    ->map(fn ($e) => "Baris {$e['row']}: {$e['error']}")
                    ->join(' | ');

                return back()->with('warning', $msg.' Beberapa baris gagal: '.$errorMsg);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error('Import pembayaran gagal: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Gagal import pembayaran: '.$e->getMessage());
        }
    }

    // ====== Helpers
    private function makeTitle(?string $base, ?string $start, ?string $end): ?string
    {
        if (! $base) {
            return null;
        }
        if ($start || $end) {
            $sd = $start ? Carbon::parse($start)->format('j/n/Y') : '-';
            $ed = $end ? Carbon::parse($end)->format('j/n/Y') : '-';

            return "{$base} dari {$sd} - {$ed}";
        }

        return $base;
    }

    // ====== EXCEL EXPORTS ======
    public function exportPendapatan(Request $request)
    {
        [$mapped, $summary, $title] = $this->buildPendapatanData($request);

        $filename = 'laporan-pendapatan-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(
            new LaporanPendapatanExport($mapped, $title),
            $filename
        );
    }

    public function exportTransaksi(Request $request)
    {
        $rows = $this->baseQueryWithFilters($request)
            ->select('pembayaran.*', 'mp.nama as nama_metode')
            ->orderByDesc('pembayaran.tanggal_pembayaran')
            ->get();

        $mapped = $rows->map(fn ($p) => $this->mapRow($p));
        $title = $this->makeTitle('Data Laporan Transaksi', $request->query('start_date'), $request->query('end_date'));
        if (! $request->query('start_date') && ! $request->query('end_date')) {
            $title = 'Data Laporan Transaksi';
        }

        $filename = 'laporan-transaksi-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new LaporanTransaksiExport($mapped, $request->query('start_date') || $request->query('end_date') ? $title : null), $filename);
    }

    // ====== PDF EXPORTS ======
    public function pdfPendapatan(Request $request)
    {
        [$mapped, $summary, $title] = $this->buildPendapatanData($request);
        
        // Generate PDF
        $pdf = PDF::loadView('admin.laporan.pdf.pendapatan', [
            'title' => $title,
            'rows' => $mapped,
            'summary' => $summary,
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)  // Mengaktifkan HTML5
        ->setOption('isPhpEnabled', true);

        return $pdf->stream('laporan-pendapatan-'.now()->format('Ymd-His').'.pdf');
    }

    public function pdfTransaksi(Request $request)
    {
        $rows = $this->baseQueryWithFilters($request)
            ->with(['reservasi.layanan'])
            ->select('pembayaran.*', 'mp.nama as nama_metode')
            ->orderByDesc('pembayaran.tanggal_pembayaran')
            ->get();

        $mapped = $rows->map(function ($p) {
            $row = $this->mapRow($p);
            
            // Format nama layanan dari snapshot
            $layananNames = $p->reservasi && $p->reservasi->layanan 
                ? $p->reservasi->layanan->map(function($l) {
                    return $l->pivot->nama_layanan_snapshot ?? $l->nama_layanan;
                })->join(', ')
                : '-';

            $row['layanan_nama'] = $layananNames ?: '-';
            
            return $row;
        });

        $title = $this->makeTitle('Laporan Transaksi', $request->query('start_date'), $request->query('end_date'));
        if (! $request->query('start_date') && ! $request->query('end_date')) {
            $title = 'Laporan Transaksi';
        }

        $pdf = PDF::loadView('admin.laporan.pdf.transaksi', [
            'title' => $title,
            'rows' => $mapped,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('laporan-transaksi-'.now()->format('Ymd-His').'.pdf');
    }

    protected function buildRerataData(Request $request): array
    {
        $tipe = $request->get('tipe', 'harian'); // harian|mingguan|bulanan

        // baseQueryWithFilters tetap boleh dipakai untuk filter metode / status
        // tapi kita tidak kirim start_date / end_date dari front-end
        $q = $this->baseQueryWithFilters($request);

        $fmt = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');

        if ($tipe === 'mingguan') {
            $rows = $q
                ->selectRaw('YEAR(pembayaran.tanggal_pembayaran) as tahun')
                ->selectRaw('WEEK(pembayaran.tanggal_pembayaran, 3) as minggu')
                ->selectRaw('MIN(DATE(pembayaran.tanggal_pembayaran)) as tgl_mulai')
                ->selectRaw('MAX(DATE(pembayaran.tanggal_pembayaran)) as tgl_selesai')
                ->selectRaw('SUM(pembayaran.jumlah - COALESCE(pembayaran.diskon_applied,0)) as total_bayar')
                ->selectRaw('COUNT(*) as trx_count')
                ->groupBy('tahun', 'minggu')
                ->orderBy('tahun')
                ->orderBy('minggu')
                ->get();

            $data = $rows->map(function ($r, $idx) use ($fmt) {
                $avg = $r->trx_count ? ((float) $r->total_bayar / (int) $r->trx_count) : 0;
                $mulai = Carbon::parse($r->tgl_mulai);
                $selesai = Carbon::parse($r->tgl_selesai);

                return [
                    'no' => $idx + 1,
                    'waktu' => sprintf(
                        'Minggu %d (%s - %s)',
                        $r->minggu,
                        $mulai->translatedFormat('d M Y'),
                        $selesai->translatedFormat('d M Y')
                    ),
                    'rerata' => $fmt($avg),
                    'jumlah' => (int) $r->trx_count,
                ];
            });
        } elseif ($tipe === 'bulanan') {
            $rows = $q
                ->selectRaw("DATE_FORMAT(pembayaran.tanggal_pembayaran, '%Y-%m-01') as bulan")
                ->selectRaw('SUM(pembayaran.jumlah - COALESCE(pembayaran.diskon_applied,0)) as total_bayar')
                ->selectRaw('COUNT(*) as trx_count')
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get();

            $data = $rows->map(function ($r, $idx) use ($fmt) {
                $avg = $r->trx_count ? ((float) $r->total_bayar / (int) $r->trx_count) : 0;
                $bulan = Carbon::parse($r->bulan);

                return [
                    'no' => $idx + 1,
                    'waktu' => $bulan->translatedFormat('F Y'), // contoh: "November 2025"
                    'rerata' => $fmt($avg),
                    'jumlah' => (int) $r->trx_count,
                ];
            });
        } else { // HARIAN (default)
            $rows = $q
                ->selectRaw('DATE(pembayaran.tanggal_pembayaran) as tgl_group')
                ->selectRaw('SUM(pembayaran.jumlah - COALESCE(pembayaran.diskon_applied,0)) as total_bayar')
                ->selectRaw('COUNT(*) as trx_count')
                ->groupBy('tgl_group')
                ->orderBy('tgl_group', 'asc')
                ->get();

            $data = $rows->map(function ($r, $idx) use ($fmt) {
                $avg = $r->trx_count ? ((float) $r->total_bayar / (int) $r->trx_count) : 0;
                $tgl = Carbon::parse($r->tgl_group);

                return [
                    'no' => $idx + 1,
                    'waktu' => $tgl->translatedFormat('l, d F Y'), // "Kamis, 20 November 2025"
                    'rerata' => $fmt($avg),
                    'jumlah' => (int) $r->trx_count,
                ];
            });
        }

        $labelTipe = match ($tipe) {
            'mingguan' => 'Mingguan',
            'bulanan' => 'Bulanan',
            default => 'Harian',
        };

        $title = 'Laporan Rerata Transaksi '.$labelTipe;

        return [$data, $title];
    }

    // ====== AJAX: Report Rerata Pendapatan
    public function ajaxRerata(Request $request)
    {
        try {
            [$data, $title] = $this->buildRerataData($request);

            return response()->json([
                'success' => true,
                'data' => $data,
                'title' => $title,
            ]);
        } catch (\Throwable $e) {
            Log::error('ajaxRerata error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'message' => 'Server error memuat rerata'], 500);
        }
    }

    // ====== EXPORT EXCEL RERATA
    public function exportRerata(Request $request)
    {
        [$data, $title] = $this->buildRerataData($request);

        $filename = 'laporan-rerata-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(
            new LaporanRerataExport($data, $title),
            $filename
        );
    }

    // ====== EXPORT PDF RERATA
    public function pdfRerata(Request $request)
    {
        [$data, $title] = $this->buildRerataData($request);

        $pdf = Pdf::loadView('admin.laporan.pdf.rerata', [
            'title' => $title,
            'rows' => $data,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('laporan-rerata-'.now()->format('Ymd-His').'.pdf');
    }

public function validateVoucher(Request $request)
{
    $request->validate([
        'kode_diskon' => 'required|string',
        'reservasi_ids' => 'required|array',
        'reservasi_ids.*' => 'integer|exists:reservasi,id_reservasi',
        'pay_type' => 'required|in:dp,full',
    ]);

    try {
        $payType = $request->input('pay_type');

        // Validasi: Voucher hanya untuk full payment
        if ($payType !== 'full') {
            return response()->json([
                'success' => false,
                'message' => 'Voucher diskon hanya berlaku untuk pembayaran penuh (Full Payment), bukan untuk Down Payment (DP).',
            ], 400);
        }

        // Cari voucher berdasarkan kode
        $diskon = Diskon::where('kode_diskon', $request->kode_diskon)
            ->where('status_diskon', 'aktif')
            ->first();

        if (!$diskon) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher tidak valid atau sudah tidak aktif.',
            ], 404);
        }

        // DEBUG: Cek data diskon yang di-load
        Log::info('Diskon Data Loaded', [
            'id' => $diskon->id,
            'kode' => $diskon->kode_diskon,
            'nama' => $diskon->nama_diskon,
            'persentase_diskon' => $diskon->persentase_diskon,
            'persentase_type' => gettype($diskon->persentase_diskon),
            'persentase_raw' => var_export($diskon->persentase_diskon, true),
        ]);

        // PENTING: Pastikan persentase_diskon adalah angka yang valid
        $persentaseDiskon = (float) $diskon->persentase_diskon;
        
        if ($persentaseDiskon <= 0) {
            Log::error('Persentase diskon tidak valid', [
                'persentase' => $persentaseDiskon,
                'diskon_id' => $diskon->id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Voucher ini memiliki persentase diskon yang tidak valid (0%). Silakan hubungi administrator.',
            ], 400);
        }

        // Cek apakah voucher masih berlaku
        if (!$diskon->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher sudah kadaluarsa atau belum aktif.',
            ], 400);
        }

        // Ambil pengaturan DP
        $pengaturanDp = DB::table('pengaturan_booking')->select('dp_tipe', 'dp_value')->first();
        $dpTipe = $pengaturanDp?->dp_tipe ?? 'persen';
        $dpValue = (float)($pengaturanDp?->dp_value ?? 30);

        // Ambil semua reservasi yang dipilih dengan relasi lengkap
        $reservasiIds = $request->reservasi_ids;
        $reservasiModels = Reservasi::with([
            'reservasiLayanan.layanan' => function($query) {
                // Hanya perlu id_layanan, nama, dan id_diskon
                $query->select('id_layanan', 'nama_layanan', 'id_diskon');
            },
            'pembayaran' => function ($query) {
                $query->whereIn('status_pembayaran', ['bayar_lunas', 'bayar_dp']);
            }
        ])
        ->whereIn('id_reservasi', $reservasiIds)
        ->get();

        // Debug: Cek data reservasi yang di-load
        Log::info('Reservasi Models Loaded', [
            'count' => $reservasiModels->count(),
            'ids' => $reservasiModels->pluck('id_reservasi')->toArray(),
        ]);

        // Debug detail per reservasi
        foreach ($reservasiModels as $res) {
            Log::info('Reservasi Detail', [
                'id' => $res->id_reservasi,
                'total_harga' => $res->total_harga,
                'layanan_count' => $res->reservasiLayanan->count(),
                'layanan_data' => $res->reservasiLayanan->map(function($rl) {
                    return [
                        'id_layanan' => $rl->id_layanan,
                        'nama' => $rl->layanan->nama_layanan ?? 'NULL',
                        'id_diskon' => $rl->layanan->id_diskon ?? 'NULL',
                    ];
                })->toArray(),
            ]);
        }

        // CEK: Apakah ini voucher global atau voucher spesifik?
        $layananWithDiskon = Layanan::where('id_diskon', $diskon->id)
            ->pluck('id_layanan')
            ->toArray();
        
        $isGlobalVoucher = empty($layananWithDiskon);
        
        Log::info('Validate Voucher - Start', [
            'diskon_id' => $diskon->id,
            'kode' => $diskon->kode_diskon,
            'persentase' => $persentaseDiskon,
            'is_global' => $isGlobalVoucher,
            'layanan_with_diskon' => $layananWithDiskon,
        ]);

        $totalDiskon = 0;
        $applicableReservasi = [];
        $reservasiWithoutDiskon = [];
        $reservasiWithDiskon = [];

        Log::info('Validate Voucher - Start', [
            'diskon_id' => $diskon->id,
            'kode' => $diskon->kode_diskon,
            'persentase' => $diskon->persentase_diskon,
            'layanan_with_diskon' => $layananWithDiskon,
        ]);

        // Loop setiap reservasi
        foreach ($reservasiModels as $reservasi) {
            $finalAmountTotal = (float)$reservasi->total_harga; // AMBIL DARI SINI
            $totalPaid = (float)$reservasi->pembayaran->sum('jumlah');
            $remaining = max($finalAmountTotal - $totalPaid, 0);

            if ($remaining <= 0) {
                continue;
            }

            // Hitung amount yang akan dibayar
            $amountToPay = 0;
            if ($totalPaid > 0) {
                $amountToPay = $remaining;
            } elseif ($payType === 'dp') {
                if ($dpTipe === 'persen') {
                    $amountToPay = ceil($finalAmountTotal * ($dpValue / 100));
                } else {
                    $amountToPay = $dpValue;
                }
                $amountToPay = max(1000, min($amountToPay, $remaining));
            } else {
                $amountToPay = $remaining;
            }

            // Cek layanan dalam reservasi ini
            $reservasiDiskon = 0;
            $hasApplicableService = false;
            $layananWithDiskonDetail = [];
            $layananWithoutDiskonDetail = [];
            $countLayananWithDiskon = 0;

            // PASS 1: Hitung berapa layanan yang eligible untuk diskon
            foreach ($reservasi->reservasiLayanan as $resLay) {
                $layanan = $resLay->layanan;
                
                if (!$layanan) {
                    Log::error('Layanan tidak ter-load', [
                        'reservasi_id' => $reservasi->id_reservasi,
                        'id_layanan' => $resLay->id_layanan,
                    ]);
                    continue;
                }
                
                $layananDiskonId = $layanan->id_diskon;

                // Cek eligibility
                if ($isGlobalVoucher) {
                    $countLayananWithDiskon++;
                    $hasApplicableService = true;
                } else {
                    if ($layananDiskonId == $diskon->id) {
                        $countLayananWithDiskon++;
                        $hasApplicableService = true;
                    }
                }
            }

            Log::info('Count Layanan Eligible', [
                'reservasi_id' => $reservasi->id_reservasi,
                'total_layanan' => $reservasi->reservasiLayanan->count(),
                'layanan_eligible' => $countLayananWithDiskon,
                'total_harga_reservasi' => $finalAmountTotal,
                'amount_to_pay' => $amountToPay,
            ]);

            // Jika tidak ada layanan yang eligible, skip
            if (!$hasApplicableService || $countLayananWithDiskon === 0) {
                // Semua layanan tidak dapat diskon
                foreach ($reservasi->reservasiLayanan as $resLay) {
                    $layanan = $resLay->layanan;
                    if (!$layanan) continue;
                    
                    $layananName = $layanan->nama_layanan ?? "Layanan #{$resLay->id_layanan}";
                    $layananDiskonId = $layanan->id_diskon;
                    
                    $reason = is_null($layananDiskonId) 
                        ? 'Layanan ini tidak memiliki diskon yang sesuai' 
                        : 'Layanan ini memiliki diskon berbeda (ID: '.$layananDiskonId.')';
                    
                    $layananWithoutDiskonDetail[] = [
                        'nama' => $layananName,
                        'harga' => 0,
                        'reason' => $reason,
                    ];
                }
                
                $reservasiWithoutDiskon[] = [
                    'id' => $reservasi->id_reservasi,
                    'layanan' => $layananWithoutDiskonDetail,
                ];
                
                continue;
            }

            // PASS 2: Aplikasikan diskon ke amount_to_pay
            // Diskon dihitung dari amount yang akan dibayar, bukan total harga
            $reservasiDiskon = $amountToPay * ($persentaseDiskon / 100);
            
            Log::info('Diskon Calculation', [
                'reservasi_id' => $reservasi->id_reservasi,
                'amount_to_pay' => $amountToPay,
                'persentase_diskon' => $persentaseDiskon,
                'total_diskon' => $reservasiDiskon,
            ]);

            // PASS 3: Detail per layanan (untuk tampilan saja)
            foreach ($reservasi->reservasiLayanan as $resLay) {
                $layanan = $resLay->layanan;
                if (!$layanan) continue;
                
                $layananName = $layanan->nama_layanan ?? "Layanan #{$resLay->id_layanan}";
                $layananDiskonId = $layanan->id_diskon;

                $canGetDiscount = false;
                $reason = '';

                if ($isGlobalVoucher) {
                    $canGetDiscount = true;
                } else {
                    if ($layananDiskonId == $diskon->id) {
                        $canGetDiscount = true;
                    } else {
                        $reason = is_null($layananDiskonId) 
                            ? 'Layanan ini tidak memiliki diskon yang sesuai' 
                            : 'Layanan ini memiliki diskon berbeda (ID: '.$layananDiskonId.')';
                    }
                }

                if ($canGetDiscount) {
                    // Bagikan diskon secara merata ke layanan yang eligible (untuk display)
                    $diskonPerLayanan = $reservasiDiskon / $countLayananWithDiskon;
                    
                    $layananWithDiskonDetail[] = [
                        'nama' => $layananName,
                        'harga' => round($finalAmountTotal / $reservasi->reservasiLayanan->count(), 2),
                        'diskon' => round($diskonPerLayanan, 2),
                    ];

                    Log::info('✓ Layanan DAPAT Diskon', [
                        'layanan' => $layananName,
                        'diskon_amount' => $diskonPerLayanan,
                    ]);
                } else {
                    $layananWithoutDiskonDetail[] = [
                        'nama' => $layananName,
                        'harga' => round($finalAmountTotal / $reservasi->reservasiLayanan->count(), 2),
                        'reason' => $reason,
                    ];
                    
                    Log::info('✗ Layanan TIDAK DAPAT Diskon', [
                        'layanan' => $layananName,
                        'reason' => $reason,
                    ]);
                }
            }

            // Pastikan diskon tidak melebihi amount yang dibayar
            $reservasiDiskon = min($reservasiDiskon, $amountToPay);

            if ($hasApplicableService && $reservasiDiskon > 0) {
                // Ada layanan yang dapat diskon
                $applicableReservasi[] = [
                    'id_reservasi' => $reservasi->id_reservasi,
                    'diskon' => round($reservasiDiskon, 2),
                ];
                $totalDiskon += $reservasiDiskon;

                $reservasiWithDiskon[] = [
                    'id' => $reservasi->id_reservasi,
                    'layanan_dapat_diskon' => $layananWithDiskonDetail,
                    'layanan_tidak_dapat_diskon' => $layananWithoutDiskonDetail,
                    'total_diskon' => round($reservasiDiskon, 2),
                ];
            } else {
                // Tidak ada layanan yang dapat diskon
                $reservasiWithoutDiskon[] = [
                    'id' => $reservasi->id_reservasi,
                    'layanan' => $layananWithoutDiskonDetail,
                ];
            }
        }

        Log::info('Validate Voucher - Result', [
            'total_diskon' => $totalDiskon,
            'with_diskon' => count($reservasiWithDiskon),
            'without_diskon' => count($reservasiWithoutDiskon),
        ]);

        // VALIDASI AKHIR: Jika tidak ada satupun reservasi yang dapat diskon
        if (count($applicableReservasi) === 0) {
            // Kumpulkan semua layanan yang tidak dapat diskon dengan alasannya
            $detailLayanan = [];
            foreach ($reservasiWithoutDiskon as $res) {
                foreach ($res['layanan'] as $lay) {
                    $detailLayanan[] = sprintf(
                        '%s (%s)', 
                        $lay['nama'], 
                        $lay['reason']
                    );
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => sprintf(
                    'Voucher "%s" tidak dapat diterapkan pada layanan yang Anda pilih.',
                    $diskon->nama_diskon
                ),
                'detail' => [
                    'layanan_dipilih' => $detailLayanan,
                    'voucher' => [
                        'kode' => $diskon->kode_diskon,
                        'nama' => $diskon->nama_diskon,
                        'persentase' => $diskon->persentase_diskon . '%',
                    ],
                    'hint' => 'Voucher ini hanya berlaku untuk layanan tertentu yang sudah terdaftar. Pastikan layanan yang Anda pilih memiliki diskon yang sama dengan voucher ini.',
                ],
            ], 400);
        }

        // VALIDASI PARSIAL: Ada beberapa yang dapat diskon, ada yang tidak
        $warningMessage = null;
        $warningDetails = [];
        
        // Kumpulkan detail layanan yang tidak dapat diskon
        foreach ($reservasiWithDiskon as $res) {
            if (!empty($res['layanan_tidak_dapat_diskon'])) {
                foreach ($res['layanan_tidak_dapat_diskon'] as $lay) {
                    $warningDetails[] = sprintf(
                        '%s - %s',
                        $lay['nama'],
                        $lay['reason']
                    );
                }
            }
        }
        
        if (!empty($warningDetails)) {
            $warningMessage = sprintf(
                'Voucher berhasil diterapkan! Namun %d layanan tidak mendapat diskon karena:\n• %s',
                count($warningDetails),
                implode("\n• ", $warningDetails)
            );
        }

        // SUKSES: Ada diskon yang dapat diterapkan
        $successMessage = $warningMessage ?? sprintf(
            'Voucher berhasil diterapkan%s pada %d reservasi!',
            $isGlobalVoucher ? ' (Global)' : '',
            count($applicableReservasi)
        );

        return response()->json([
            'success' => true,
            'message' => $successMessage,
            'data' => [
                'diskon_id' => $diskon->id,
                'kode_diskon' => $diskon->kode_diskon,
                'nama_diskon' => $diskon->nama_diskon,
                'persentase' => $persentaseDiskon,
                'total_diskon' => round($totalDiskon, 2),
                'applicable_reservasi' => $applicableReservasi,
                'is_global' => $isGlobalVoucher,
                'detail' => [
                    'reservasi_with_diskon' => $reservasiWithDiskon,
                    'reservasi_without_diskon' => $reservasiWithoutDiskon,
                ],
            ],
            'warning' => $warningMessage ? true : false,
        ]);

    } catch (\Throwable $e) {
        Log::error('Validate voucher error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat memvalidasi voucher: ' . $e->getMessage(),
        ], 500);
    }
}
}
