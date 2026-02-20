<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiskonController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\KategoriLayananController;
use App\Http\Controllers\KonfirmasiPembatalanController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\MetodepembayaranController;
use App\Http\Controllers\PelangganAuthController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\Pilihan_layananController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reservasi_layananController;
use App\Http\Controllers\Reservasi_slot_jadwalController;
use App\Http\Controllers\ReservasiController;
use App\Http\Controllers\SlotJadwalController;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['id', 'en']), 404);
    session(['locale' => $locale]);

    return back(); // kembali ke halaman sebelumnya
})->name('lang.switch');

Route::middleware(['check.reservasi.status'])->group(function () {
    Route::view('/', 'welcome')->name('welcome');
    Route::view('/about', 'about')->name('tentang');
    Route::view('/contact', 'contact')->name('kontak');

    Route::get('/ajax/galeri/pelanggan', [ReservasiController::class, 'ajaxgaleripelanggan'])
        ->name('galeripelanggan.ajax');

    Route::post('/contact/send', [ContactController::class, 'send'])
        ->name('contact.send');
});


Route::post('/ajax/check-reservasi', [ReservasiController::class, 'ajaxCheckReservasi'])
    ->name('ajax.check.reservasi')
    ->middleware('auth');

// Halaman daftar layanan
Route::get('/layanan', [App\Http\Controllers\LayananPelangganController::class, 'index'])
    ->name('daftar-layanan.index');

Route::get('/booking', function () {
    return redirect()->route('booking.categories');
})->name('booking.redirect');

Route::get('/layanan/search', [App\Http\Controllers\LayananPelangganController::class, 'search'])
    ->name('layanan.search');

// ------------------------------
// Auth (tanpa Fortify, versi custom)
// ------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/register', [PelangganAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [PelangganAuthController::class, 'register'])->name('register.store');

    // Forgot password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

// ------------------------------
// Email verification (Isolated from other middleware)
// ------------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')->name('verification.send');
});

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

// ------------------------------
// Main Reservation Routes
// ------------------------------
Route::middleware(['auth', 'check.reservasi.status'])->group(function () {
    Route::get('/home', [AuthController::class, 'redirectAfterLogin'])->name('home');



// ------------------------------
// Email verification (tanpa middleware verified)
// ------------------------------

    Route::get('/profil', [ProfileController::class, 'index'])->name('pelanggan.profile');
    Route::post('/profil/password', [ProfileController::class, 'updatePassword'])->name('pelanggan.profile.password');

    Route::prefix('booking')->name('booking.')->middleware(['auth', 'check.user.reservasi.status'])->group(function () {

        Route::post('/pembayaran', [BookingController::class, 'step3'])->name('step3');

        Route::post('/voucher/validate', [PembayaranController::class, 'validateVoucher'])
            ->name('voucher.validate');

        Route::post('/midtrans/proses', [PembayaranController::class, 'proses'])->name('midtrans.proses');
        Route::post('/midtrans/callback', [PembayaranController::class, 'midtransJsCallback'])->name('midtrans.callback');

        Route::get('/success-multi/{ids}', [BookingController::class, 'successMulti'])->name('successMulti');
        Route::get('ajax-reservations', [BookingController::class, 'ajaxGetReservations'])
            ->name('ajaxGetReservations');
        Route::post('cancel/{id_reservasi}', [BookingController::class, 'cancel'])
            ->name('cancel');
        Route::delete('/{id}', [BookingController::class, 'destroy'])->name('destroy'); // Add this line
        Route::get('history', [BookingController::class, 'history'])->name('history');
        Route::post('/getRentangSlotDinamis', [BookingController::class, 'getRentangSlotDinamis'])->name('getRentangSlotDinamis');
        Route::get('/cart/get', [BookingController::class, 'ajaxGetCart'])->name('cart.get');
        Route::post('/cart/manage', [BookingController::class, 'ajaxManageCart'])->name('cart.manage');
        Route::post('/schedule/update', [BookingController::class, 'ajaxUpdateSchedule'])->name('schedule.update');
        Route::get('/jadwal', [BookingController::class, 'step2'])->name('step2');
        Route::post('/jadwal/simpan', [BookingController::class, 'step2Save'])->name('step2.save');
        Route::get('/kategori', [BookingController::class, 'chooseCategory'])->name('categories');

        Route::post('/finish', [BookingController::class, 'finish'])->name('finish');
        Route::get('/{kategori}', [BookingController::class, 'step1'])->name('step1');

    });
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ------------------------------
// Admin Routes
// ------------------------------
Route::prefix('admin')->middleware(['auth', 'admin', 'check.reservasi.status'])->group(function () {
    Route::post('/vouchers/available', [PosController::class, 'getAvailableVouchers'])->name('admin.pos.vouchers.available');
    Route::post('/vouchers/validate', [PosController::class, 'validateVoucherPos'])->name('admin.pos.vouchers.validate');

    // Endpoint slot jadwal (dipakai POS & pelanggan)
    Route::get('/booking/slot-jadwal', [PosController::class, 'slotJadwal'])
        ->name('booking.slot_jadwal');

    // Notifications
    Route::get('/notifications/get', [App\Http\Controllers\Admin\NotificationController::class, 'getNotifications'])->name('admin.notifications.get');
    Route::post('/notifications/mark-read/{id}', [App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('admin.notifications.markRead');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('admin.notifications.markAllRead');


    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Management admin
    Route::get('/account', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/ajax', [AdminController::class, 'ajax'])->name('admin.ajax');
    Route::get('/register', [AdminController::class, 'showRegistrationForm'])->name('admin.register');
    Route::post('/register', [AdminController::class, 'store'])->name('admin.store');
    Route::get('/edit/{id}', [AdminController::class, 'edit'])->name('admin.edit');
    Route::put('/update/{id}', [AdminController::class, 'update'])->name('admin.update');
    Route::delete('/delete/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');

    // Update password admin
    Route::get('/password/edit', [AdminController::class, 'editPasswordForm'])->name('admin.password.edit');
    Route::put('/password', [AdminController::class, 'updatePassword'])->name('admin.password.update');

    // --------------------------
    // Laporan (VIEW)
    // --------------------------
    Route::get('/laporan-pendapatan', [PembayaranController::class, 'laporan_pendapatan'])->name('laporan.pendapatan');
    Route::get('/laporan-transaksi', [PembayaranController::class, 'laporan_transaksi'])->name('laporan.transaksi');
    Route::get('/laporan-rata-rata', [PembayaranController::class, 'laporan_rata_rata'])->name('laporan.rata_rata');

    // --------------------------
    // Laporan (AJAX API)
    // --------------------------
    Route::get('/ajax/laporan/pendapatan', [PembayaranController::class, 'ajaxPendapatan'])->name('ajax.laporan.pendapatan');
    Route::get('/ajax/laporan/transaksi', [PembayaranController::class, 'ajaxTransaksi'])->name('ajax.laporan.transaksi');
    Route::get('/ajax/laporan/rerata', [PembayaranController::class, 'ajaxRerata'])->name('ajax.laporan.rerata');

    // --------------------------
    // Laporan (EXPORTS)
    // --------------------------
    Route::get('/export/laporan/pendapatan', [PembayaranController::class, 'exportPendapatan'])->name('laporan.pendapatan.export');
    Route::get('/export/laporan/transaksi', [PembayaranController::class, 'exportTransaksi'])->name('laporan.transaksi.export');
    Route::get('/export/laporan/rerata', [PembayaranController::class, 'exportRerata'])->name('laporan.rerata.export');

    // --------------------------
    // Laporan (PDF)
    // --------------------------
    Route::get('/pdf/laporan/pendapatan', [PembayaranController::class, 'pdfPendapatan'])->name('laporan.pendapatan.pdf');
    Route::get('/pdf/laporan/transaksi', [PembayaranController::class, 'pdfTransaksi'])->name('laporan.transaksi.pdf');
    Route::get('/pdf/laporan/rerata', [PembayaranController::class, 'pdfRerata'])->name('laporan.rerata.pdf');

    // --------------------------
    // Master data & pengaturan
    // --------------------------
    Route::resource('/pelanggan', PelangganController::class);
    Route::get('/data-pelanggan', [PelangganController::class, 'data_pelanggan'])->name('pelanggan.data_pelanggan');
    Route::get('/ajax/pelanggan', [PelangganController::class, 'ajax'])->name('pelanggan.ajax');

    Route::resource('/kategori-layanan', KategoriLayananController::class);
    Route::get('/ajax/kategori-layanan', [KategoriLayananController::class, 'ajax'])->name('kategori-layanan.ajax');

    Route::resource('/layanan', LayananController::class);
    Route::get('/ajax/layanan', [LayananController::class, 'ajax'])->name('layanan.ajax');
    Route::post('/import/layanan', [LayananController::class, 'import'])->name('layanan.import');

    Route::resource('/slot-jadwal', SlotJadwalController::class);
    Route::get('/ajax/slot-jadwal', [SlotJadwalController::class, 'ajax'])->name('slot-jadwal.ajax');

    Route::resource('/metode-pembayaran', MetodepembayaranController::class);
    Route::get('/ajax/metode-pembayaran', [MetodepembayaranController::class, 'ajax'])->name('ajax.metode-pembayaran');

    // Pelanggan Import/Export
    Route::get('/export/pelanggan', [PelangganController::class, 'export'])->name('pelanggan.export');
    Route::post('/import/pelanggan', [PelangganController::class, 'import'])->name('pelanggan.import');

    // Pembayaran: ajax untuk tabel bawah pelanggan + import/export
    Route::get('/ajax/pembayaran', [PembayaranController::class, 'ajax'])->name('pembayaran.ajax');
    Route::get('/export/pembayaran', [PembayaranController::class, 'export'])->name('pembayaran.export');
    Route::post('/import/pembayaran', [PembayaranController::class, 'import'])->name('pembayaran.import');

    // Lain-lain
    Route::resource('/diskon', DiskonController::class);
    Route::resource('/pilihan-layanan', Pilihan_layananController::class);
    Route::resource('/reservasi-slot-jadwal', Reservasi_slot_jadwalController::class);
    Route::resource('/reservasi-layanan', Reservasi_layananController::class);
    Route::resource('/reservasi', ReservasiController::class);
    Route::post('/reservasi/update-status', [ReservasiController::class, 'updateStatus'])->name('reservasi.update_status');
    Route::post('/reservasi/mark-paid', [ReservasiController::class, 'markAsPaid'])->name('reservasi.mark_paid');
    Route::post('/reservasi/add-cost', [ReservasiController::class, 'addAdditionalCost'])->name('reservasi.add_cost');
    Route::get('/booking/list', [ReservasiController::class, 'bookingList'])->name('booking.list');
    Route::get('/booking/list/data', [ReservasiController::class, 'getBookingListData'])->name('booking.list.data');
    Route::get('/booking', [ReservasiController::class, 'booking'])->name('booking.index');

    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::get('/reservasibooking', [ReservasiController::class, 'getReservasiBooking'])->name('reservasibooking');
        Route::get('/booking', [ReservasiController::class, 'getPengaturanBooking'])->name('booking');
        Route::get('/arsip', [ReservasiController::class, 'getPengaturanArsip'])->name('arsip');
        Route::get('/tentang-kami', [ReservasiController::class, 'getPengaturanTentangKami'])->name('tentangkami');
        Route::get('/lokasi', [ReservasiController::class, 'getPengaturanLokasi'])->name('lokasi');
        Route::post('/booking', [ReservasiController::class, 'savePengaturanBooking'])->name('booking.simpan');
        Route::post('/arsip', [ReservasiController::class, 'savePengaturanArsip'])->name('arsip.simpan');
        Route::post('/tentang-kami', [ReservasiController::class, 'savePengaturanTentangKami'])->name('tentangkami.simpan');
        Route::post('/lokasi', [ReservasiController::class, 'savePengaturanLokasi'])->name('lokasi.simpan');
    });

    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/ajax', [ContactController::class, 'ajax'])->name('contacts.ajax');
    Route::get('/contacts/{id}', [ContactController::class, 'show'])->name('contacts.show');

    Route::get('/konfirmasi-pembatalan', [KonfirmasiPembatalanController::class, 'index'])
        ->name('admin.konfirmasi-pembatalan.index');

    Route::get('/konfirmasi-pembatalan/ajax', [KonfirmasiPembatalanController::class, 'ajaxGetPendingCancellations'])
        ->name('admin.konfirmasi-pembatalan.ajax');

    Route::post('/konfirmasi-pembatalan/{id}/approve', [KonfirmasiPembatalanController::class, 'approve'])
        ->name('admin.konfirmasi-pembatalan.approve');

    Route::post('/konfirmasi-pembatalan/{id}/reject', [KonfirmasiPembatalanController::class, 'reject'])
        ->name('admin.konfirmasi-pembatalan.reject');

});

// Routes untuk POS (Admin only)
Route::middleware(['auth', 'admin'])->prefix('admin/pos')->name('admin.pos.')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('index');
    Route::post('/save', [PosController::class, 'save'])->name('save');
    Route::get('/search-customer', [PosController::class, 'searchCustomer'])->name('searchCustomer');

    // Password verification untuk cash payment
    Route::post('/verify-password', [PosController::class, 'verifyPassword'])->name('verifyPassword');

    // DP List
    Route::get('/dp-list', [PosController::class, 'dpList'])->name('dpList');

    // Pay remaining (untuk tunai/transfer)
    Route::post('/pay-remaining', [PosController::class, 'payRemaining'])->name('payRemaining');

    // ✅ ROUTE BARU UNTUK MIDTRANS POS
    Route::post('/midtrans/proses', [PosController::class, 'prosesMidtrans'])->name('midtrans.proses');
    Route::post('/midtrans/callback', [PosController::class, 'midtransCallback'])->name('midtrans.callback');
});

// Route untuk slot jadwal (bisa diakses oleh POS dan booking online)
Route::get('/booking/slot-jadwal', [PosController::class, 'slotJadwal'])->name('booking.slot_jadwal');

// ------------------------------
// Pembayaran Midtrans
// ------------------------------
Route::controller(PembayaranController::class)->group(function () {
    // 1. Halaman Checkout/Booking (pindah ke /booking/checkout supaya /booking dipakai kategori)
    Route::get('/booking/checkout', 'showBookingPage')->name('booking.checkout');

    // 2. Endpoint AJAX untuk Generate Snap Token
    Route::post('/midtrans/generate-token', 'generateSnapToken')->name('midtrans.generate');

    // 3. Halaman Sukses/Status Redirect (Dipanggil setelah pembayaran di Midtrans)

    // 4. Webhook Notifikasi Midtrans (Rute ini harus PUBLIC)
    // Route::post('/midtrans/notification', 'notificationHandler')->name('midtrans.notification');
});

Route::get('/ajax/layanan/pelanggan', [LayananController::class, 'ajaxpelanggan'])->name('layananpelanggan.ajax');
Route::get('/ajax/kategorilayanan/pelanggan', [KategoriLayananController::class, 'ajaxpelanggan'])->name('kategorilayananpelanggan.ajax');

// ------------------------------
// Pelanggan area (tanpa prefix)
// ------------------------------
Route::middleware(['auth', 'verified', 'pelanggan'])->group(function () {
    Route::post('/addreservasi', [ReservasiController::class, 'addreservasipelanggan'])->name('reservasi.addreservasipelanggan');
});
