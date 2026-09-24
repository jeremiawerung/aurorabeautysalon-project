import { expect } from '@playwright/test';

/**
 * Page Object untuk Main Menu pelanggan.
 *
 * Merepresentasikan komponen resources/views/components/nav-pelanggan.blade.php
 * yang tampil di seluruh halaman publik (Beranda, Tentang Kami, Hubungi Kami)
 * melalui layout resources/views/layouts/pelanggan.blade.php.
 */
export class MainMenuPage {
  /** @param {import('@playwright/test').Page} page */
  constructor(page) {
    this.page = page;

    // --- Navbar ---------------------------------------------------------
    this.nav = page.locator('nav').first();
    this.logo = this.nav.locator('.logo img');
    this.hamburger = this.nav.locator('.menu-toggle');

    // Tiga link utama (menu bahasa dikecualikan karena berupa <button>, bukan <a>).
    this.navLinks = this.nav.locator('.nav-links > li:not(.language-dropdown) > a');
    this.homeLink = this.navLinks.nth(0);
    this.aboutLink = this.navLinks.nth(1);
    this.contactLink = this.navLinks.nth(2);
    this.activeNavLink = this.nav.locator('.nav-links a.active');

    // --- Language switcher ----------------------------------------------
    this.languageDropdown = this.nav.locator('.language-dropdown');
    this.languageButton = this.languageDropdown.locator('.language-btn');
    this.languageOptionId = this.languageDropdown.locator('a[href$="/lang/id"]');
    this.languageOptionEn = this.languageDropdown.locator('a[href$="/lang/en"]');

    // --- Tombol kanan: berbeda antara state guest dan login --------------
    this.navButtons = this.nav.locator('.nav-buttons');
    this.signInButton = this.navButtons.locator('a.btn-login'); // guest saja
    this.signUpButton = this.navButtons.locator('a.btn-signup'); // guest saja
    this.bookingButton = this.navButtons.locator('button.btn-signup'); // login saja
    this.profileButton = this.navButtons.locator('button.profile-btn'); // login saja

    // --- Panel profil (sidebar) ------------------------------------------
    this.profilePanel = page.locator('#profileDropdown');
    this.profileOverlay = page.locator('#profileOverlay');
    this.profileCloseButton = this.profilePanel.locator('.profile-close');
    this.profileHeading = this.profilePanel.locator('.profile-header-content h3');
    this.profileMenuItems = this.profilePanel.locator('.profile-menu-item');

    // Item panel saat LOGIN. Dipilih via href/tag agar tidak tertukar dengan
    // item guest yang kebetulan memakai nama class yang sama.
    this.panelBookingHistoryLink = this.profilePanel.locator('a[href$="/booking/history"]');
    this.panelProfileLink = this.profilePanel.locator('a[href$="/profil"]');
    this.panelCartButton = this.profilePanel.locator('button.profile-menu-item.add-booking');
    this.panelLogoutButton = this.profilePanel.locator('button.profile-menu-item.logout');

    // Item panel saat GUEST (keduanya berupa <a>, bukan <button>).
    this.panelSignInLink = this.profilePanel.locator('a.profile-menu-item.add-booking');
    this.panelSignUpLink = this.profilePanel.locator('a.profile-menu-item.logout');

    // --- Dialog konfirmasi logout (SweetAlert2) --------------------------
    this.logoutDialog = page.locator('.swal2-container');
    this.logoutConfirmButton = page.locator('.swal2-confirm');
    this.logoutCancelButton = page.locator('.swal2-cancel');
  }

  /** Buka salah satu halaman yang memakai Main Menu. */
  async goto(pathname = '/') {
    await this.page.goto(pathname);
    await expect(this.nav).toBeVisible();
  }

  /** Buka dropdown bahasa lalu pilih locale ('id' atau 'en'). */
  async switchLanguage(locale) {
    await this.languageButton.click();
    await expect(this.languageDropdown).toHaveClass(/active/);

    const option = locale === 'id' ? this.languageOptionId : this.languageOptionEn;
    await option.click();
    await this.page.waitForLoadState('domcontentloaded');
  }

  /** Buka panel profil lewat tombol hamburger. */
  async openProfilePanelViaHamburger() {
    await this.hamburger.click();
    await expect(this.profilePanel).toHaveClass(/active/);
  }

  /** Buka panel profil lewat tombol ikon user (hanya tersedia saat login). */
  async openProfilePanelViaProfileButton() {
    await this.profileButton.click();
    await expect(this.profilePanel).toHaveClass(/active/);
  }

  /** Nilai `document.body.style.overflow` — dipakai memverifikasi scroll-lock. */
  async bodyOverflow() {
    return this.page.evaluate(() => document.body.style.overflow);
  }

  /** Teks ketiga link menu utama, dipakai untuk memverifikasi pergantian bahasa. */
  async navLinkTexts() {
    return (await this.navLinks.allTextContents()).map((text) => text.trim());
  }
}
