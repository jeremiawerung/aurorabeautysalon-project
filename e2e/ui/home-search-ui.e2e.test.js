import { expect, test } from '@playwright/test';

import { HomePage } from '../pages/HomePage.js';
import { EXPECTED, ROUTES } from '../support/test-data.js';

/**
 * Grup E — Pencarian layanan pada Beranda (hero + autocomplete).
 *
 * Autocomplete memanggil GET /layanan/search dengan debounce 400ms dan
 * baru menembak request bila keyword minimal 2 karakter (welcome.blade.php).
 */

test.describe('Beranda - hero dan pencarian layanan', () => {
  test('should menampilkan hero beserta kolom pencarian when Beranda dibuka', async ({ page }) => {
    const home = new HomePage(page);

    await home.goto();

    await expect(home.heroTitle).toBeVisible();
    await expect(home.heroTitle).not.toBeEmpty();
    await expect(home.heroDescription).toBeVisible();
    await expect(home.searchInput).toBeVisible();
    await expect(home.searchButton).toBeVisible();
    // Kotak saran tersembunyi sebelum ada input.
    await expect(home.searchSuggestions).toBeHidden();
  });

  test('should tidak memanggil API pencarian when keyword kurang dari 2 karakter', async ({ page }) => {
    const home = new HomePage(page);
    const searchRequests = [];
    page.on('request', (request) => {
      if (request.url().includes('/layanan/search')) searchRequests.push(request.url());
    });

    await home.goto();
    await home.typeSearchKeyword('F');
    // Beri waktu melewati jendela debounce 400ms.
    await page.waitForTimeout(900);

    expect(searchRequests, 'keyword 1 karakter tidak boleh memicu request').toHaveLength(0);
    await expect(home.searchSuggestions).toBeHidden();
  });

  test('should menampilkan daftar saran layanan when keyword yang cocok diketik', async ({ page }) => {
    const home = new HomePage(page);
    await home.goto();

    await home.typeSearchKeyword(EXPECTED.searchKeywordWithResults);

    await expect(home.searchSuggestionItems.first()).toBeVisible();
    const firstSuggestion = home.searchSuggestionItems.first();
    // Setiap saran memuat nama layanan, meta (kategori • durasi), dan harga.
    await expect(firstSuggestion.locator('.search-suggestion-title')).not.toBeEmpty();
    await expect(firstSuggestion.locator('.search-suggestion-meta')).toContainText('•');
    await expect(firstSuggestion.locator('.search-suggestion-price')).toContainText('Rp');
  });

  test('should menampilkan pesan kosong when keyword tidak cocok dengan layanan mana pun', async ({ page }) => {
    const home = new HomePage(page);
    await home.goto();

    await home.typeSearchKeyword(EXPECTED.searchKeywordNoResults);

    await expect(home.searchSuggestionEmpty).toBeVisible();
    await expect(home.searchSuggestionItems).toHaveCount(0);
  });

  test('should membuka daftar layanan dengan parameter highlight when salah satu saran diklik', async ({ page }) => {
    const home = new HomePage(page);
    await home.goto();
    await home.typeSearchKeyword(EXPECTED.searchKeywordWithResults);
    await expect(home.searchSuggestionItems.first()).toBeVisible();

    await home.searchSuggestionItems.first().click();

    await expect(page).toHaveURL(/\/layanan\?highlight=\d+$/);
  });

  test('should membuka daftar layanan dengan parameter search when menekan Enter di kolom pencarian', async ({
    page,
  }) => {
    const home = new HomePage(page);
    await home.goto();
    await home.typeSearchKeyword(EXPECTED.searchKeywordWithResults);

    await home.searchInput.press('Enter');

    await expect(page).toHaveURL(
      new RegExp(`/layanan\\?search=${EXPECTED.searchKeywordWithResults}$`, 'i')
    );
  });

  test('should membuka daftar layanan tanpa filter when tombol Search ditekan dengan kolom kosong', async ({
    page,
  }) => {
    const home = new HomePage(page);
    await home.goto();

    await expect(home.searchInput).toHaveValue('');
    await home.searchButton.click();

    await expect(page).toHaveURL(new RegExp(`${ROUTES.layanan}$`));
  });

  test('should menutup daftar saran when mengklik area di luar kolom pencarian', async ({ page }) => {
    const home = new HomePage(page);
    await home.goto();
    await home.typeSearchKeyword(EXPECTED.searchKeywordWithResults);
    await expect(home.searchSuggestionItems.first()).toBeVisible();

    await home.heroTitle.click();

    await expect(home.searchSuggestions).toBeHidden();
  });
});
