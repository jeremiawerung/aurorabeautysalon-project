import { expect } from '@playwright/test';

import { ROUTES } from '../support/test-data.js';

/**
 * Page Object untuk seluruh wizard booking pelanggan: pilih kategori → step1 (tambah ke
 * keranjang) → step2 (pilih jadwal) → step3 (checkout & pembayaran Midtrans Snap).
 *
 * Dipetakan lewat eksplorasi kode sumber (BookingController, PembayaranController, dan
 * blade booking-pilih-kategori-layanan / booking-step1 / booking-step2 / booking-step3),
 * bukan dokumentasi resmi — beberapa nama endpoint/field mengikuti implementasi asli, bukan
 * konvensi REST standar (mis. body `pay_type`, bukan `paymentType`).
 */
export class BookingFlowPage {
  /** @param {import('@playwright/test').Page} page */
  constructor(page) {
    this.page = page;

    // --- Pilih kategori ---
    this.categoryRows = page.locator('.category-row');

    // --- Step1: pilih layanan ---
    this.serviceRows = page.locator('.service-row[data-service-id]');
    this.nextToScheduleButton = page.locator('#nextBtn');

    // --- Step2: pilih jadwal ---
    this.scheduleButtons = page.locator('button.btn-schedule');
    this.modalBackdrop = page.locator('#modalBackdrop');
    this.dayTabs = page.locator('#dayStrip .day-tab');
    this.timeSlots = page.locator('#timeList .time-slot-row:not(.disabled)');
    this.applyScheduleButton = page.locator('.modal-footer .btn-primary');
    this.payButtonStep2 = page.locator('#payBtn');

    // --- Step3: checkout & pembayaran ---
    this.payTypeFull = page.locator('input[name="paytype"][value="full"]');
    this.paymentMethodRadios = page.locator('input[name="paymethod"]');
    this.payButtonStep3 = page.locator('#payButton');
  }

  /** Buka halaman pilih kategori (butuh login sebagai pelanggan). */
  async gotoCategories() {
    await this.page.goto(ROUTES.bookingCategories);
    await expect(this.categoryRows.first()).toBeVisible();
  }

  /** Klik kategori pertama yang tersedia → masuk ke step1 (daftar layanan kategori itu). */
  async openFirstCategory() {
    await this.categoryRows.first().locator('a.btn-outline').click();
    await this.page.waitForLoadState('networkidle');
    await expect(this.serviceRows.first()).toBeVisible();
  }

  /** Tambahkan layanan pertama pada step1 ke keranjang. */
  async addFirstServiceToCart() {
    const addButton = this.serviceRows.first().locator('button[id^="btn-add-"]');
    await addButton.click();
    // Tunggu tombol berubah status (menandakan request cart/manage selesai & sidebar ter-update).
    await expect(addButton).toHaveClass(/btn-added/, { timeout: 8000 });
  }

  /** Lanjut dari step1 ke step2 (tombol aktif setelah subtotal > 0). */
  async goToSchedule() {
    await expect(this.nextToScheduleButton).not.toHaveClass(/disabled/, { timeout: 8000 });
    await this.nextToScheduleButton.click();
    await this.page.waitForLoadState('networkidle');
    await expect(this.page).toHaveURL(new RegExp(`${escapeRegExp(ROUTES.bookingSchedule)}$`));
  }

  /**
   * Buka modal jadwal untuk item pertama, cari hari pertama yang masih punya slot jam
   * kosong (bisa saja hari ke-1 sudah penuh/lewat jam operasional kalau test dijalankan
   * larut malam, jadi tidak boleh asumsikan hari pertama selalu punya slot), lalu simpan.
   */
  async scheduleFirstAvailableSlot() {
    await this.scheduleButtons.first().click();
    await expect(this.modalBackdrop).toHaveClass(/show/, { timeout: 8000 });

    // Modal memuat tanggal & slot lewat AJAX (POST getRentangSlotDinamis) setelah terbuka —
    // tunggu tab hari pertama benar-benar dirender sebelum menghitungnya, jangan langsung count().
    await expect(this.dayTabs.first()).toBeVisible({ timeout: 10000 });

    const dayCount = await this.dayTabs.count();
    let slotFound = false;

    for (let i = 0; i < dayCount; i += 1) {
      await this.dayTabs.nth(i).click();
      // eslint-disable-next-line no-await-in-loop
      await this.page.waitForTimeout(300); // tunggu #timeList di-render ulang untuk hari ini
      // eslint-disable-next-line no-await-in-loop
      const availableCount = await this.timeSlots.count();
      if (availableCount > 0) {
        slotFound = true;
        break;
      }
    }

    if (!slotFound) {
      throw new Error(
        'Tidak ada slot jadwal kosong di rentang hari manapun untuk layanan ini — cek data slot_jadwal.'
      );
    }

    await this.timeSlots.first().click();
    await this.applyScheduleButton.click();
    await expect(this.modalBackdrop).not.toHaveClass(/show/, { timeout: 8000 });
  }

  /**
   * Klik "Selesai" (goPay) di step2. Ini berupa native <form> POST + full page reload
   * (bukan fetch AJAX) — perlu menunggu navigasi, bukan hanya response jaringan.
   */
  async goToPaymentStep() {
    await expect(this.payButtonStep2).toBeEnabled({ timeout: 8000 });
    await this.payButtonStep2.click();
    await this.page.waitForLoadState('networkidle');
    await expect(this.page).toHaveURL(new RegExp(`${escapeRegExp(ROUTES.bookingPembayaran)}$`));
  }

  /** Pilih opsi "Bayar Penuh" (bukan DP) di step3. */
  async selectFullPayment() {
    await this.payTypeFull.check();
  }

  /**
   * Klik tombol bayar di step3, menangkap response JSON dari
   * POST /booking/midtrans/proses (endpoint generate Snap token milik aplikasi sendiri).
   *
   * @returns {Promise<{success: boolean, token: object}>}
   */
  async submitPaymentAndCaptureToken() {
    const [response] = await Promise.all([
      this.page.waitForResponse(
        (res) => res.url().includes(ROUTES.bookingMidtransProses) && res.request().method() === 'POST'
      ),
      this.payButtonStep3.click(),
    ]);

    return response.json();
  }

  /**
   * Menunggu popup Snap Midtrans (sandbox) benar-benar terbuka setelah `snap.pay()` dipanggil.
   * Ini iframe sungguhan dari app.sandbox.midtrans.com — dipakai untuk MEMBUKTIKAN integrasi
   * Midtrans benar-benar tersambung, bukan untuk menyelesaikan pembayaran di dalamnya (lihat
   * catatan strategi di README: UI Snap sandbox bersifat non-deterministik — kadang menampilkan
   * daftar metode pembayaran, kadang langsung redirect ke metode "rekomendasi" acak seperti
   * QRIS/ShopeePay — sehingga tidak stabil untuk dijadikan gerbang kelulusan test otomatis).
   *
   * @returns {Promise<import('@playwright/test').Frame>}
   */
  async waitForSnapPopup() {
    // PENTING: deteksi frame HARUS lewat page.frames() sisi Playwright/Node, bukan lewat
    // page.evaluate()/waitForFunction() yang berjalan di JS browser — membaca
    // `iframe.contentWindow.location` untuk frame lintas-origin (sandbox.midtrans.com di
    // halaman 127.0.0.1:8000) diblokir same-origin policy dan akan selalu gagal walau
    // framenya benar-benar ada. page.frames() memakai CDP, tidak terikat batasan itu.
    const deadline = Date.now() + 15000;
    let frame;

    while (Date.now() < deadline) {
      frame = this.page.frames().find((f) => f.url().includes('sandbox.midtrans.com'));
      if (frame) break;
      // eslint-disable-next-line no-await-in-loop
      await this.page.waitForTimeout(300);
    }

    if (!frame) {
      throw new Error('Popup Snap Midtrans sandbox tidak ditemukan setelah menunggu 15 detik.');
    }
    return frame;
  }

  /**
   * Mensimulasikan pembayaran BERHASIL dengan memanggil langsung endpoint aplikasi
   * POST /booking/midtrans/callback — endpoint yang SAMA yang dipanggil oleh callback
   * `onSuccess` milik Snap.js di kode asli (lihat booking-step3.blade.php baris ~1019).
   *
   * Dipakai sebagai pengganti menyelesaikan pembayaran sungguhan di popup Snap sandbox,
   * karena popup itu pihak ketiga dan non-deterministik (lihat waitForSnapPopup). Ini TETAP
   * menguji logika asli aplikasi (validasi, update status reservasi/pembayaran, response
   * redirect) — cuma titik masuknya lewat pemanggilan langsung, bukan lewat UI Snap.
   *
   * @param {object} tokenData hasil dari submitPaymentAndCaptureToken().token
   * @returns {Promise<{success: boolean, reservasi_ids: number[], status: string}>}
   */
  async simulatePaymentSettlement(tokenData) {
    const payload = {
      reservasi_ids: tokenData.reservasi_ids,
      order_id: tokenData.order_id,
      transaction_status: 'settlement',
      payment_type: 'credit_card',
      metode_id: tokenData.metode_id,
      amounts: tokenData.amounts.map((a) => ({ reservasi_id: a.reservasi_id, amount: a.amount })),
      pay_type_selected: tokenData.pay_type_selected,
      diskon_data: tokenData.diskon_data ?? null,
    };

    return this.page.evaluate(
      async ({ url, body }) => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify(body),
        });
        return res.json();
      },
      { url: ROUTES.bookingMidtransCallback, body: payload }
    );
  }

  /** Navigasi ke halaman sukses persis seperti yang dilakukan JS asli setelah callback sukses. */
  async gotoSuccessPage(reservasiIds) {
    await this.page.goto(`/booking/success-multi/${reservasiIds.join(',')}`);
  }
}

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
