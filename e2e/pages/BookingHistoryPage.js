import { expect } from '@playwright/test';

import { ROUTES } from '../support/test-data.js';

/**
 * Page Object untuk resources/views/pelanggan/booking-history.blade.php.
 *
 * Halaman ini render shell kosong lalu mengisi daftar riwayat lewat AJAX
 * (`GET /booking/ajax-reservations`). Response JSON-nya sudah membawa field
 * `is_lunas` (boolean) yang dihitung server — jauh lebih stabil untuk dijadikan
 * assertion dibanding menebak teks/kelas badge status di DOM.
 */
export class BookingHistoryPage {
  /** @param {import('@playwright/test').Page} page */
  constructor(page) {
    this.page = page;
    this.cards = page.locator('#reservations-list .card');
  }

  /**
   * Buka halaman riwayat dan kembalikan payload JSON dari request
   * GET /booking/ajax-reservations pertama yang dipicu saat halaman dimuat.
   */
  async gotoAndCaptureList() {
    const responsePromise = this.page.waitForResponse(
      (res) => res.url().includes('/booking/ajax-reservations') && res.request().method() === 'GET'
    );
    await this.page.goto(ROUTES.bookingHistory);
    const response = await responsePromise;
    await expect(this.cards.first()).toBeVisible();
    return response.json();
  }

  /**
   * Cari satu baris data reservasi dari payload JSON berdasarkan id_reservasi.
   * Bentuk response asli: `{ success, data: [...historyData], pagination, message }`
   * (lihat BookingController::ajaxGetReservations).
   */
  static findReservasi(historyJson, idReservasi) {
    const items = Array.isArray(historyJson?.data) ? historyJson.data : [];
    return items.find((item) => Number(item.id_reservasi) === Number(idReservasi));
  }
}
