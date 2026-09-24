import { expect, test } from '@playwright/test';

import { MainMenuPage } from '../pages/MainMenuPage.js';
import { ALT_LOCALE, DEFAULT_LOCALE, NAV_LABELS, ROUTES } from '../support/test-data.js';

const DEFAULT_LABELS = NAV_LABELS[DEFAULT_LOCALE];
const ALT_LABELS = NAV_LABELS[ALT_LOCALE];

/**
 * Grup A & B — Navigasi Main Menu dan pengalih bahasa.
 *
 * Seluruh test di file ini berjalan sebagai TAMU pada viewport desktop,
 * karena pada viewport <= 768px elemen `.nav-right` disembunyikan oleh CSS.
 */

test.describe('Main Menu - navigasi antar halaman (guest)', () => {
  test('should render seluruh elemen menu utama when membuka Beranda sebagai tamu', async ({ page }) => {
    const menu = new MainMenuPage(page);

    await menu.goto(ROUTES.home);

    // Logo dan tiga link menu utama
    await expect(menu.logo).toBeVisible();
    await expect(menu.navLinks).toHaveCount(3);
    await expect(menu.homeLink).toHaveText(DEFAULT_LABELS.home);
    await expect(menu.aboutLink).toHaveText(DEFAULT_LABELS.about);
    await expect(menu.contactLink).toHaveText(DEFAULT_LABELS.contact);

    // Pengalih bahasa dan tombol khusus tamu
    await expect(menu.languageButton).toBeVisible();
    await expect(menu.signInButton).toHaveText(DEFAULT_LABELS.signIn);
    await expect(menu.signUpButton).toHaveText(DEFAULT_LABELS.signUp);
  });

  test('should membuka halaman Tentang Kami dan menandai link aktif when link About diklik', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);

    await menu.aboutLink.click();

    await expect(page).toHaveURL(new RegExp(`${ROUTES.about}$`));
    await expect(menu.aboutLink).toHaveClass(/active/);
    // Hanya satu link yang boleh berstatus aktif pada satu waktu.
    await expect(menu.activeNavLink).toHaveCount(1);
  });

  test('should membuka halaman Hubungi Kami dan menandai link aktif when link Contact diklik', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);

    await menu.contactLink.click();

    await expect(page).toHaveURL(new RegExp(`${ROUTES.contact}$`));
    await expect(menu.contactLink).toHaveClass(/active/);
    await expect(menu.activeNavLink).toHaveCount(1);
  });

  test('should kembali ke Beranda when link Home diklik dari halaman Tentang Kami', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.about);

    await menu.homeLink.click();

    await expect(page).toHaveURL(new RegExp(`${escapeRegExp(ROUTES.home)}$`));
    await expect(menu.homeLink).toHaveClass(/active/);
    await expect(menu.activeNavLink).toHaveCount(1);
  });

  test('should menampilkan menu utama yang konsisten when berpindah ke seluruh halaman publik', async ({ page }) => {
    const menu = new MainMenuPage(page);

    for (const pathname of [ROUTES.home, ROUTES.about, ROUTES.contact]) {
      await menu.goto(pathname);

      await expect(menu.nav).toBeVisible();
      await expect(menu.navLinks).toHaveCount(3);
      await expect(menu.languageButton).toBeVisible();
      await expect(menu.signInButton).toBeVisible();
      await expect(menu.signUpButton).toBeVisible();
    }
  });
});

test.describe('Main Menu - pengalih bahasa', () => {
  test('should memakai locale default aplikasi when pengunjung belum pernah mengganti bahasa', async ({ page }) => {
    const menu = new MainMenuPage(page);

    await menu.goto(ROUTES.home);

    // Locale default berasal dari config/app.php, lihat catatan pada DEFAULT_LOCALE.
    await expect(menu.languageButton).toContainText(DEFAULT_LOCALE.toUpperCase());
    expect(await menu.navLinkTexts()).toEqual([
      DEFAULT_LABELS.home,
      DEFAULT_LABELS.about,
      DEFAULT_LABELS.contact,
    ]);
  });

  test('should mengganti teks menu dan tetap di halaman yang sama when memilih bahasa lain', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.about);

    await menu.switchLanguage(ALT_LOCALE);

    // Route lang.switch memakai back(), sehingga user harus tetap berada di /about.
    await expect(page).toHaveURL(new RegExp(`${ROUTES.about}$`));
    await expect(menu.languageButton).toContainText(ALT_LOCALE.toUpperCase());
    expect(await menu.navLinkTexts()).toEqual([
      ALT_LABELS.home,
      ALT_LABELS.about,
      ALT_LABELS.contact,
    ]);
  });

  test('should mengembalikan teks menu ke bahasa semula when locale default dipilih lagi', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);

    await menu.switchLanguage(ALT_LOCALE);
    await expect(menu.languageButton).toContainText(ALT_LOCALE.toUpperCase());

    await menu.switchLanguage(DEFAULT_LOCALE);

    await expect(menu.languageButton).toContainText(DEFAULT_LOCALE.toUpperCase());
    expect(await menu.navLinkTexts()).toEqual([
      DEFAULT_LABELS.home,
      DEFAULT_LABELS.about,
      DEFAULT_LABELS.contact,
    ]);
  });

  test('should mempertahankan pilihan bahasa when berpindah halaman', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);

    await menu.switchLanguage(ALT_LOCALE);
    await expect(menu.languageButton).toContainText(ALT_LOCALE.toUpperCase());

    // Locale disimpan di session, jadi harus bertahan pada request berikutnya.
    await menu.goto(ROUTES.about);

    await expect(menu.languageButton).toContainText(ALT_LOCALE.toUpperCase());
    expect(await menu.navLinkTexts()).toEqual([
      ALT_LABELS.home,
      ALT_LABELS.about,
      ALT_LABELS.contact,
    ]);
  });

  test('should mengembalikan 404 when locale yang diminta tidak didukung', async ({ page }) => {
    // Edge case: route lang.switch hanya mengizinkan 'id' dan 'en'.
    const response = await page.goto('/lang/fr');

    expect(response?.status()).toBe(404);
  });

  test('should menutup dropdown bahasa when mengklik area di luar dropdown', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);

    await menu.languageButton.click();
    await expect(menu.languageDropdown).toHaveClass(/active/);

    // Klik di luar dropdown memicu listener global pada layout pelanggan.
    await page.locator('.hero-title').click();

    await expect(menu.languageDropdown).not.toHaveClass(/active/);
  });
});

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
