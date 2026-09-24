import { expect, test } from '@playwright/test';

import { BookingFlowPage } from '../pages/BookingFlowPage.js';
import { BookingHistoryPage } from '../pages/BookingHistoryPage.js';
import { LoginPage } from '../pages/LoginPage.js';
import { MainMenuPage } from '../pages/MainMenuPage.js';
import { RegisterPage } from '../pages/RegisterPage.js';
import { deleteUserByEmail, verifyUserByEmail } from '../support/php-runner.js';
import { ROUTES, uniqueTestEmail } from '../support/test-data.js';

/**
 * Grup K — Alur end-to-end: Registrasi akun baru → verifikasi email → Login →
 * Booking layanan → Pembayaran (Midtrans Snap sandbox) → halaman sukses → Riwayat booking.
 *
 * Catatan penting soal titik pembayaran (baca sebelum mengubah test ini):
 * Midtrans di project ini memakai kredensial SANDBOX (.env: MIDTRANS_IS_PRODUCTION=false).
 * Popup Snap sandbox NYATA berhasil dibuka & diverifikasi lewat eksplorasi manual (frame
 * dari app.sandbox.midtrans.com, menampilkan nominal yang benar). Namun popup itu:
 *  1) Pihak ketiga (di luar kendali aplikasi/test ini), dan
 *  2) NON-DETERMINISTIK — pada percobaan berulang, Snap kadang menampilkan daftar metode
 *     pembayaran, kadang langsung redirect ke metode "rekomendasi" acak (QRIS/ShopeePay/dll),
 *     sehingga tidak ada satu selector/langkah tetap untuk menyelesaikan pembayaran di dalamnya.
 * Karena itu, test ini MEMBUKTIKAN integrasi asli tersambung (popup benar-benar terbuka,
 * lihat langkah "Snap Midtrans sandbox terbuka"), lalu MENYELESAIKAN pembayaran dengan
 * memanggil langsung endpoint aplikasi POST /booking/midtrans/callback — endpoint yang SAMA
 * yang dipanggil oleh callback `onSuccess` Snap.js di kode asli (booking-step3.blade.php).
 * Ini tetap menguji logika backend asli (validasi, update status, response), bukan menulis
 * langsung ke database.
 */
test.describe('Alur booking end-to-end (register -> login -> booking -> bayar)', () => {
  let email;

  test.afterEach(async () => {
    if (email) {
      try {
        deleteUserByEmail(email);
      } catch {
        // no-op kalau user memang belum sempat terbuat (test gagal di awal)
      }
      email = undefined;
    }
  });

  test('should mendaftar akun baru, login, booking layanan, bayar lunas, dan tercatat di riwayat', async ({
    page,
  }) => {
    // Alur panjang: registrasi (email sungguhan dikirim sinkron, bisa >10s — lihat catatan
    // di RegisterPage.submit()) + booking + menunggu popup Snap sandbox pihak ketiga.
    test.setTimeout(120_000);

    const register = new RegisterPage(page);
    const login = new LoginPage(page);
    const menu = new MainMenuPage(page);
    const booking = new BookingFlowPage(page);
    const history = new BookingHistoryPage(page);

    email = uniqueTestEmail('booking-flow');
    const password = 'BookingFlow1!';

    await test.step('Registrasi akun pelanggan baru lewat form asli', async () => {
      await register.goto();
      await register.fillAndSubmit({
        email,
        nama: 'Booking Flow Tester',
        nomorTelepon: '081345678900',
        password,
      });
      await page.waitForURL(new RegExp(`${escapeRegExp(ROUTES.verifyNotice)}$`));
    });

    await test.step('Simulasikan verifikasi email lewat DB (tidak ada akses inbox email asli)', async () => {
      verifyUserByEmail(email);
    });

    await test.step('Logout, lalu login ulang dengan akun yang baru dibuat', async () => {
      await page.locator('form[action$="/logout"] button[type="submit"]').click();
      await page.waitForLoadState('networkidle');

      await login.goto();
      await login.login(email, password);
      await page.waitForURL((url) => url.pathname === ROUTES.home);
      await expect(menu.profileButton).toBeVisible();
    });

    let reservationServiceName;

    await test.step('Pilih kategori dan tambahkan layanan pertama ke keranjang', async () => {
      await booking.gotoCategories();
      await booking.openFirstCategory();
      reservationServiceName = (await booking.serviceRows.first().locator('.service-title').innerText()).trim();
      await booking.addFirstServiceToCart();
      await booking.goToSchedule();
    });

    await test.step('Pilih jadwal (hari & slot pertama yang tersedia)', async () => {
      await booking.scheduleFirstAvailableSlot();
      await booking.goToPaymentStep();
    });

    let tokenData;

    await test.step('Pilih Bayar Penuh, klik bayar, dan tangkap Snap token dari endpoint asli', async () => {
      await booking.selectFullPayment();
      const proses = await booking.submitPaymentAndCaptureToken();
      expect(proses.success).toBe(true);
      tokenData = proses.token;
      expect(tokenData.snap_token).toBeTruthy();
    });

    await test.step('Verifikasi popup Snap Midtrans sandbox benar-benar terbuka (integrasi nyata)', async () => {
      const snapFrame = await booking.waitForSnapPopup();
      expect(snapFrame.url()).toContain('sandbox.midtrans.com');
    });

    let settlement;

    await test.step('Selesaikan pembayaran lewat endpoint callback asli (lihat catatan strategi di atas)', async () => {
      settlement = await booking.simulatePaymentSettlement(tokenData);
      expect(settlement.success).toBe(true);
      expect(settlement.status).toBe('paid');
      expect(settlement.reservasi_ids.length).toBeGreaterThan(0);
    });

    await test.step('Buka halaman sukses', async () => {
      await booking.gotoSuccessPage(settlement.reservasi_ids);
      await expect(page.locator('.header-success')).toContainText('Berhasil');
    });

    await test.step('Verifikasi riwayat booking menampilkan reservasi dengan status LUNAS', async () => {
      const historyJson = await history.gotoAndCaptureList();
      const row = BookingHistoryPage.findReservasi(historyJson, settlement.reservasi_ids[0]);

      expect(row, 'reservasi yang baru dibayar harus muncul di riwayat').toBeTruthy();
      expect(row.is_lunas).toBe(true);
      expect(row.nama_layanan).toBe(reservationServiceName);
    });
  });
});

test.describe('Booking - kontrol akses', () => {
  test('should mengarahkan ke halaman login when tamu mencoba membuka halaman kategori booking', async ({
    page,
  }) => {
    await page.goto(ROUTES.bookingCategories);

    await expect(page).toHaveURL(new RegExp(`${ROUTES.login}$`));
  });
});

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
