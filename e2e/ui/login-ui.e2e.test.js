import { expect, test } from '@playwright/test';

import { LoginPage } from '../pages/LoginPage.js';
import { MainMenuPage } from '../pages/MainMenuPage.js';
import { seedUnverifiedUser } from '../support/php-runner.js';
import { PELANGGAN, ROUTES, UNVERIFIED_PELANGGAN } from '../support/test-data.js';

/**
 * Grup J — Login pelanggan (AuthController::login).
 *
 * Memakai akun pelanggan yang sudah tersedia (bukan mendaftar dulu di setiap test) supaya
 * skenario login bisa diuji sebagai fitur yang berdiri sendiri, terpisah dari skenario
 * registrasi di register-ui.e2e.test.js.
 */
test.describe('Login pelanggan', () => {
  test('should berhasil login dan menampilkan menu pelanggan when kredensial benar', async ({ page }) => {
    const login = new LoginPage(page);
    const menu = new MainMenuPage(page);

    await login.goto();
    await login.login(PELANGGAN.email, PELANGGAN.password);

    await page.waitForURL((url) => url.pathname === ROUTES.home);
    await expect(menu.profileButton).toBeVisible();
  });

  test('should menolak login when password salah', async ({ page }) => {
    const login = new LoginPage(page);

    await login.goto();
    await login.login(PELANGGAN.email, 'PasswordSalahBanget123');

    await expect(login.errorAlert).toContainText('Email atau password salah');
    await expect(page).toHaveURL(new RegExp(`${ROUTES.login}$`));
  });

  test('should menolak login when email tidak terdaftar', async ({ page }) => {
    const login = new LoginPage(page);

    await login.goto();
    await login.login('tidak.ada.akun.begini@aurora.test', 'PasswordApapun123');

    await expect(login.errorAlert).toContainText('Email atau password salah');
    await expect(page).toHaveURL(new RegExp(`${ROUTES.login}$`));
  });

  test('should menolak login dan meminta verifikasi email when akun belum diverifikasi', async ({ page }) => {
    seedUnverifiedUser();
    const login = new LoginPage(page);

    await login.goto();
    await login.login(UNVERIFIED_PELANGGAN.email, UNVERIFIED_PELANGGAN.password);

    // AuthController::login memaksa logout ulang & menampilkan pesan ini saat email belum terverifikasi.
    await expect(login.errorAlert).toContainText('verifikasi email');
    await expect(page).toHaveURL(new RegExp(`${ROUTES.login}$`));

    // Pastikan benar-benar TIDAK ter-autentikasi (bukan cuma redirect balik ke /login).
    await page.goto(ROUTES.home);
    const menu = new MainMenuPage(page);
    await expect(menu.signInButton).toBeVisible();
  });
});
