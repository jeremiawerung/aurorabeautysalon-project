import { expect } from '@playwright/test';

/**
 * Page Object untuk halaman Beranda (resources/views/welcome.blade.php).
 *
 * Sebagian besar konten halaman ini dimuat via AJAX setelah DOMContentLoaded:
 *  - grid kategori   → /ajax/kategorilayanan/pelanggan
 *  - galeri          → /ajax/galeri/pelanggan
 *  - rekomendasi     → /ajax/layanan/pelanggan
 *  - autocomplete    → /layanan/search (debounce 400ms, minimal 2 karakter)
 */
export class HomePage {
  /** @param {import('@playwright/test').Page} page */
  constructor(page) {
    this.page = page;

    // --- Hero + pencarian -------------------------------------------------
    this.hero = page.locator('section.hero');
    this.heroTitle = this.hero.locator('.hero-title');
    this.heroDescription = this.hero.locator('.hero-description');
    this.searchInput = page.locator('#searchLayanan');
    this.searchButton = page.locator('.search-btn');
    this.searchSuggestions = page.locator('#searchSuggestions');
    this.searchSuggestionItems = this.searchSuggestions.locator('.search-suggestion-item');
    this.searchSuggestionEmpty = this.searchSuggestions.locator('.search-suggestions-empty');

    // --- Grid kategori layanan -------------------------------------------
    this.servicesGrid = page.locator('.services-grid');
    this.serviceCards = this.servicesGrid.locator('a');

    // --- Galeri "Experience" ---------------------------------------------
    this.experienceGrid = page.locator('#experienceGrid');
    this.experienceImages = this.experienceGrid.locator('.experience-item img');

    // --- Rekomendasi ------------------------------------------------------
    this.recommendationsGrid = page.locator('#recommendationsGrid');
    this.recommendationCards = this.recommendationsGrid.locator('.recommendation-card');
    this.recommendationNames = this.recommendationsGrid.locator('.recommendation-name');
    this.recommendationPrices = this.recommendationsGrid.locator('.recommendation-price');
    this.recommendationCtas = this.recommendationsGrid.locator('.recommendation-btn');
    this.scrollLeftButton = page.locator('.scroll-btn-left');
    this.scrollRightButton = page.locator('.scroll-btn-right');
  }

  async goto() {
    await this.page.goto('/');
    await expect(this.hero).toBeVisible();
  }

  /**
   * Ketik keyword ke input pencarian.
   * Pengetikan dilakukan per karakter agar event `input` (pemicu debounce) benar-benar terjadi.
   */
  async typeSearchKeyword(keyword) {
    await this.searchInput.click();
    await this.searchInput.fill('');
    await this.searchInput.pressSequentially(keyword, { delay: 40 });
  }

  /** Tunggu sampai grid kategori selesai dimuat via AJAX. */
  async waitForServiceCategories() {
    await expect(this.serviceCards.first()).toBeVisible();
  }

  /** Tunggu sampai kartu rekomendasi selesai dimuat via AJAX. */
  async waitForRecommendations() {
    await expect(this.recommendationCards.first()).toBeVisible();
  }

  /** Posisi scroll horizontal container rekomendasi. */
  async recommendationsScrollLeft() {
    return this.recommendationsGrid.evaluate((el) => el.scrollLeft);
  }
}
