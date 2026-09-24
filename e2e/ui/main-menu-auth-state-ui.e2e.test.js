import { expect, test } from '@playwright/test';

import { loginAsPelanggan } from '../fixtures/auth.fixture.js';
import { MainMenuPage } from '../pages/MainMenuPage.js';
import { PELANGGAN, PELANGGAN_STORAGE_STATE, ROUTES } from '../support/test-data.js';

/**
 * Grup C & D — Perbedaan Main Menu antara tamu dan pelanggan yang login,
 * serta perilaku panel profil (sidebar) beserta konfirmasi logout.
 */

test.describe('Main Menu - state tamu', () => {
  test('should hanya menampilkan tombol Sign In dan Sign Up when pengunjung belum login', async ({ page }) => {
    const menu = new MainMenuPage(page);

    await menu.goto(ROUTES.home);

    await expect(menu.signInButton).toBeVisible();
    await expect(menu.signUpButton).toBeVisible();
    // Tombol khusus pelanggan tidak boleh ada sama sekali di DOM.
    await expect(menu.bookingButton).toHaveCount(0);
    await expect(menu.profileButton).toHaveCount(0);
  });

  test('should mengarahkan ke halaman login dan registrasi when tombol Sign In / Sign Up diklik', async ({ page }) => {
    const menu = new MainMenuPage(page);

    await menu.goto(ROUTES.home);
    await menu.signInButton.click();
    await expect(page).toHaveURL(new RegExp(`${ROUTES.login}$`));

    await menu.goto(ROUTES.home);
    await menu.signUpButton.click();
    await expect(page).toHaveURL(new RegExp(`${ROUTES.register}$`));
  });
});

test.describe('Main Menu - transisi tamu menjadi pelanggan', () => {
  test('should menampilkan tombol Booking dan ikon profil when pelanggan berhasil login', async ({ page }) => {
    const menu = new MainMenuPage(page);

    await loginAsPelanggan(page);

    await expect(page).toHaveURL(new RegExp(`${escapeRegExp(ROUTES.home)}$`));
    await expect(menu.bookingButton).toBeVisible();
    await expect(menu.profileButton).toBeVisible();
    // Tombol tamu harus hilang setelah login.
    await expect(menu.signInButton).toHaveCount(0);
    await expect(menu.signUpButton).toHaveCount(0);
  });
});

test.describe('Main Menu - state pelanggan login', () => {
  test.use({ storageState: PELANGGAN_STORAGE_STATE });

  test('should membuka halaman pilih kategori booking when tombol Booking diklik', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);

    await menu.bookingButton.click();

    await expect(page).toHaveURL(new RegExp(`${ROUTES.bookingCategories}$`));
  });

  test('should membuka panel profil dan mengunci scroll when ikon profil diklik', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);

    await menu.openProfilePanelViaProfileButton();

    await expect(menu.profileOverlay).toHaveClass(/active/);
    expect(await menu.bodyOverflow()).toBe('hidden');
  });

  test('should menutup panel profil dan memulihkan scroll when overlay diklik', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);
    await menu.openProfilePanelViaProfileButton();

    await menu.profileOverlay.click();

    await expect(menu.profilePanel).not.toHaveClass(/active/);
    await expect(menu.profileOverlay).not.toHaveClass(/active/);
    expect(await menu.bodyOverflow()).toBe('');
  });

  test('should menutup panel profil when tombol silang diklik', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);
    await menu.openProfilePanelViaProfileButton();

    await menu.profileCloseButton.click();

    await expect(menu.profilePanel).not.toHaveClass(/active/);
    await expect(menu.profileOverlay).not.toHaveClass(/active/);
  });

  test('should menampilkan menu khusus pelanggan when panel profil dibuka', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);

    await menu.openProfilePanelViaProfileButton();

    await expect(menu.profileHeading).toHaveText(PELANGGAN.name);
    await expect(menu.panelBookingHistoryLink).toBeVisible();
    await expect(menu.panelProfileLink).toBeVisible();
    await expect(menu.panelCartButton).toBeVisible();
    await expect(menu.panelLogoutButton).toBeVisible();
    // Link Home/About/Contact di dalam panel memang khusus mobile (.profile-nav-links).
    await expect(menu.profilePanel.locator('.profile-nav-links')).toBeHidden();
  });

  test('should mengarahkan ke halaman riwayat booking when menu Daftar Booking diklik', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);
    await menu.openProfilePanelViaProfileButton();

    await menu.panelBookingHistoryLink.click();

    await expect(page).toHaveURL(new RegExp(`${ROUTES.bookingHistory}$`));
  });

  test('should mengarahkan ke halaman profil when menu Profil diklik', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);
    await menu.openProfilePanelViaProfileButton();

    await menu.panelProfileLink.click();

    await expect(page).toHaveURL(new RegExp(`${ROUTES.profil}$`));
  });

  test('should mengarahkan ke daftar layanan when menu Keranjang Booking diklik', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);
    await menu.openProfilePanelViaProfileButton();

    await menu.panelCartButton.click();

    await expect(page).toHaveURL(new RegExp(`${ROUTES.layanan}$`));
  });
});

test.describe('Main Menu - konfirmasi logout', () => {
  // Sengaja login manual (bukan storageState bersama) karena logout akan
  // meng-invalidate session di server dan dapat mengganggu test lain.

  test('should tetap login when konfirmasi logout dibatalkan', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await loginAsPelanggan(page);
    await menu.openProfilePanelViaProfileButton();

    await menu.panelLogoutButton.click();
    await expect(menu.logoutDialog).toBeVisible();
    await menu.logoutCancelButton.click();

    await expect(menu.logoutDialog).toHaveCount(0);
    await expect(page).toHaveURL(new RegExp(`${escapeRegExp(ROUTES.home)}$`));
    await expect(menu.profileButton).toBeVisible();
  });

  test('should keluar dan mengarahkan ke halaman login when konfirmasi logout disetujui', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await loginAsPelanggan(page);
    await menu.openProfilePanelViaProfileButton();

    await menu.panelLogoutButton.click();
    await expect(menu.logoutDialog).toBeVisible();
    await menu.logoutConfirmButton.click();

    await page.waitForURL(new RegExp(`${ROUTES.login}$`));

    // Kembali ke Beranda harus kembali menampilkan menu versi tamu.
    await menu.goto(ROUTES.home);
    await expect(menu.signInButton).toBeVisible();
    await expect(menu.profileButton).toHaveCount(0);
  });
});

test.describe('Main Menu - viewport mobile', () => {
  test.use({ viewport: { width: 375, height: 812 } });

  test('should menampilkan hamburger dan panel menu tamu when dibuka pada layar mobile', async ({ page }) => {
    const menu = new MainMenuPage(page);
    await menu.goto(ROUTES.home);

    // Pada <= 768px, `.nav-right` disembunyikan dan hamburger menjadi satu-satunya akses menu.
    await expect(menu.hamburger).toBeVisible();

    await menu.openProfilePanelViaHamburger();

    await expect(menu.profileOverlay).toHaveClass(/active/);
    await expect(menu.profileHeading).toBeVisible();
    // Panel tamu memuat tautan halaman publik + Sign In & Sign Up.
    await expect(menu.profilePanel.locator(`a[href$="${ROUTES.about}"]`).first()).toBeVisible();
    await expect(menu.profilePanel.locator(`a[href$="${ROUTES.contact}"]`).first()).toBeVisible();
    await expect(menu.panelSignInLink).toBeVisible();
    await expect(menu.panelSignUpLink).toBeVisible();
  });
});

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
