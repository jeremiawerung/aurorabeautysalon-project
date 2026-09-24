import { expect } from '@playwright/test';

import { PELANGGAN, ROUTES } from '../support/test-data.js';

/** State kosong: memaksa sebuah test berjalan sebagai tamu meski project punya storageState. */
export const GUEST_STORAGE_STATE = { cookies: [], origins: [] };

/**
 * Login sebagai pelanggan lewat form login sungguhan.
 *
 * Dipakai oleh test yang memang perlu memverifikasi proses login itu sendiri, atau
 * yang akan merusak session (mis. logout) sehingga tidak boleh memakai storageState bersama.
 *
 * @param {import('@playwright/test').Page} page
 */
export async function loginAsPelanggan(page) {
  await page.goto(ROUTES.login);
  await page.locator('input[name="email"]').fill(PELANGGAN.email);
  await page.locator('input[name="password"]').fill(PELANGGAN.password);
  await page.locator('button[type="submit"]').first().click();

  // Pelanggan yang berhasil login diarahkan ke Beranda (AuthController::login).
  await page.waitForURL((url) => url.pathname === ROUTES.home);
  await expect(page.locator('nav .nav-buttons button.profile-btn')).toBeVisible();
}
