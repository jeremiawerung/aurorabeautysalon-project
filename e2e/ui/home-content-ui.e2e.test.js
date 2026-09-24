import { expect, test } from '@playwright/test';

import { HomePage } from '../pages/HomePage.js';
import { MainMenuPage } from '../pages/MainMenuPage.js';
import { PELANGGAN_STORAGE_STATE, ROUTES } from '../support/test-data.js';

/**
 * Grup F, G & H — Konten Beranda yang dimuat via AJAX:
 * grid kategori layanan, galeri "Experience", dan carousel rekomendasi.
 */

test.describe('Beranda - grid kategori layanan', () => {
  test('should mengganti placeholder loading dengan kartu kategori when data selesai dimuat', async ({ page }) => {
    const home = new HomePage(page);
    await home.goto();

    await home.waitForServiceCategories();

    // Data master saat ini berisi 5 kategori layanan.
    await expect(home.serviceCards).toHaveCount(5);
    const firstCard = home.serviceCards.first();
    await expect(firstCard.locator('img')).toBeVisible();
    await expect(firstCard.locator('p')).not.toBeEmpty();
    // Placeholder loading berupa <p> anak langsung grid, harus sudah tergantikan.
    // Dicek lewat struktur DOM, bukan teks, agar tidak bergantung pada bahasa aktif.
    await expect(home.servicesGrid.locator('> p')).toHaveCount(0);
  });

  test('should membuka halaman booking kategori terkait when kartu kategori diklik oleh pelanggan login', async ({
    browser,
  }) => {
    const context = await browser.newContext({ storageState: PELANGGAN_STORAGE_STATE });
    const page = await context.newPage();
    const home = new HomePage(page);

    try {
      await home.goto();
      await home.waitForServiceCategories();

      const firstCard = home.serviceCards.first();
      const kategori = (await firstCard.locator('p').innerText()).trim();

      await firstCard.click();

      // URL akhir berbentuk /booking/{nama kategori} (spasi ter-encode oleh browser).
      await page.waitForURL(/\/booking\//);
      expect(decodeURIComponent(new URL(page.url()).pathname)).toBe(`/booking/${kategori}`);
    } finally {
      await context.close();
    }
  });

  test('should mengarahkan ke halaman login when kartu kategori diklik oleh tamu', async ({ page }) => {
    const home = new HomePage(page);
    await home.goto();
    await home.waitForServiceCategories();

    // Edge case: rute booking terproteksi middleware auth.
    await home.serviceCards.first().click();

    await expect(page).toHaveURL(new RegExp(`${ROUTES.login}$`));
  });
});

test.describe('Beranda - galeri Experience', () => {
  test('should menampilkan foto galeri when data galeri selesai dimuat', async ({ page }) => {
    const home = new HomePage(page);
    await home.goto();

    await expect(home.experienceImages.first()).toBeVisible();

    // Data master saat ini berisi 5 foto galeri.
    await expect(home.experienceImages).toHaveCount(5);
    // Placeholder loading sudah tergantikan seluruhnya oleh item galeri.
    await expect(home.experienceGrid.locator('> div:not(.experience-item)')).toHaveCount(0);
    await expect(home.experienceImages.first()).toHaveAttribute('src', /.+/);
  });
});

test.describe('Beranda - rekomendasi layanan', () => {
  test('should menampilkan kartu rekomendasi lengkap when data layanan selesai dimuat', async ({ page }) => {
    const home = new HomePage(page);
    await home.goto();

    await home.waitForRecommendations();

    const cardCount = await home.recommendationCards.count();
    expect(cardCount).toBeGreaterThan(0);

    const firstCard = home.recommendationCards.first();
    await expect(firstCard.locator('.recommendation-name')).not.toBeEmpty();
    await expect(firstCard.locator('.recommendation-price')).toContainText('Rp');
    await expect(firstCard.locator('.recommendation-btn')).toBeVisible();
  });

  test('should membuka halaman booking kategori when tombol pesan pada kartu rekomendasi diklik', async ({ page }) => {
    const home = new HomePage(page);
    const menu = new MainMenuPage(page);
    await home.goto();
    await home.waitForRecommendations();

    await home.recommendationCtas.first().click();

    // Sebagai tamu, rute booking terproteksi sehingga berakhir di halaman login;
    // yang diverifikasi di sini adalah tombol CTA benar-benar menuju alur booking.
    await page.waitForURL(new RegExp(`${ROUTES.login}$`));
    await expect(menu.nav.or(page.locator('form[action$="/login"]')).first()).toBeVisible();
  });

  test('should menggeser carousel ke kanan when tombol panah kanan ditekan', async ({ page }) => {
    const home = new HomePage(page);
    await home.goto();
    await home.waitForRecommendations();

    expect(await home.recommendationsScrollLeft()).toBe(0);
    await home.scrollRightButton.click();

    // scroll-behavior: smooth, jadi posisi scroll dipoll sampai berubah.
    await expect.poll(() => home.recommendationsScrollLeft()).toBeGreaterThan(0);

    const scrolledPosition = await home.recommendationsScrollLeft();
    await home.scrollLeftButton.click();
    await expect.poll(() => home.recommendationsScrollLeft()).toBeLessThan(scrolledPosition);
  });
});
