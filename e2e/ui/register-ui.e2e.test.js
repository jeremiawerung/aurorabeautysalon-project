import { expect, test } from '@playwright/test';

import { RegisterPage } from '../pages/RegisterPage.js';
import { deleteUserByEmail } from '../support/php-runner.js';
import { ROUTES, uniqueTestEmail } from '../support/test-data.js';

/**
 * Grup I — Registrasi pelanggan (form asli, lewat PelangganAuthController::register).
 *
 * Setiap test happy-path membuat akun baru dengan email unik (uniqueTestEmail) supaya
 * tidak bentrok dengan validasi `unique:users,email` pada re-run, lalu menghapusnya lagi
 * di afterEach lewat delete-user.php supaya database dev tidak menumpuk akun test.
 */
test.describe('Registrasi pelanggan', () => {
  const createdEmails = [];

  test.afterEach(async () => {
    while (createdEmails.length > 0) {
      const email = createdEmails.pop();
      try {
        deleteUserByEmail(email);
      } catch {
        // Test negatif (mis. email duplikat) sengaja tidak membuat user baru — no-op saja.
      }
    }
  });

  test('should mendaftar dan diarahkan ke halaman verifikasi email when mengisi form dengan benar', async ({
    page,
  }) => {
    // Registrasi sukses memicu pengiriman email verifikasi sungguhan secara sinkron (lihat
    // catatan performa di RegisterPage.submit()) — beri ruang lebih dari default 30s.
    test.setTimeout(60_000);
    const register = new RegisterPage(page);
    const email = uniqueTestEmail('register');
    createdEmails.push(email);

    await register.goto();
    await register.fillAndSubmit({
      email,
      nama: 'Register Tester',
      nomorTelepon: '081234567890',
      password: 'RegisterPass1!',
    });

    // register() otomatis Auth::login() lalu redirect ke verification.notice (routes/web.php).
    await page.waitForURL(new RegExp(`${escapeRegExp(ROUTES.verifyNotice)}$`));
    await expect(page.getByText('Email verifikasi telah dikirim ke')).toBeVisible();
    await expect(page.getByText(email)).toBeVisible();
  });

  test('should menolak registrasi when email sudah terdaftar sebelumnya', async ({ page }) => {
    // Registrasi pertama di test ini sukses -> memicu pengiriman email sungguhan (lihat
    // catatan performa di RegisterPage.submit()).
    test.setTimeout(60_000);
    const register = new RegisterPage(page);
    const email = uniqueTestEmail('register-dup');
    // Didaftarkan SEBELUM submit (bukan setelah sukses): akun sudah tersimpan di DB begitu
    // request POST diterima server, sebelum email verifikasi (lambat, sinkron) selesai
    // dikirim — kalau langkah berikutnya gagal/timeout, cleanup tetap harus jalan
    // (delete-user.php no-op kalau usernya ternyata belum sempat ada).
    createdEmails.push(email);

    // Daftar pertama kali — sukses.
    await register.goto();
    await register.fillAndSubmit({
      email,
      nama: 'Dup Tester',
      nomorTelepon: '081234567891',
      password: 'RegisterPass1!',
    });
    await page.waitForURL(new RegExp(`${escapeRegExp(ROUTES.verifyNotice)}$`));

    // Logout supaya bisa mengunjungi /register lagi (dibungkus middleware 'guest').
    await page.locator('form[action$="/logout"] button[type="submit"]').click();
    await page.waitForLoadState('networkidle');

    // Coba daftar lagi dengan email yang sama — harus ditolak.
    await register.goto();
    await register.fillAndSubmit({
      email,
      nama: 'Dup Tester Kedua',
      nomorTelepon: '081234567892',
      password: 'RegisterPass1!',
    });

    await expect(register.errorAlert).toBeVisible();
    await expect(page).toHaveURL(new RegExp(`${ROUTES.register}$`));
  });

  test('should menampilkan error validasi when konfirmasi password tidak cocok', async ({ page }) => {
    const register = new RegisterPage(page);

    await register.goto();
    await register.fillAndSubmit({
      email: uniqueTestEmail('register-mismatch'),
      nama: 'Mismatch Tester',
      nomorTelepon: '081234567893',
      password: 'RegisterPass1!',
      passwordConfirmation: 'PasswordBeda1!',
    });

    await expect(register.errorAlert).toBeVisible();
    await expect(page).toHaveURL(new RegExp(`${ROUTES.register}$`));
  });

  test('should menampilkan error validasi when password kurang dari 8 karakter', async ({ page }) => {
    const register = new RegisterPage(page);

    await register.goto();
    await register.fillAndSubmit({
      email: uniqueTestEmail('register-short-pw'),
      nama: 'Short Password Tester',
      nomorTelepon: '081234567894',
      password: 'Sh0rt!',
      passwordConfirmation: 'Sh0rt!',
    });

    await expect(register.errorAlert).toBeVisible();
    await expect(page).toHaveURL(new RegExp(`${ROUTES.register}$`));
  });

  test('should menampilkan error validasi when nomor telepon berisi huruf', async ({ page }) => {
    const register = new RegisterPage(page);

    await register.goto();
    await register.fillAndSubmit({
      email: uniqueTestEmail('register-bad-phone'),
      nama: 'Bad Phone Tester',
      nomorTelepon: 'bukanangka123',
      password: 'RegisterPass1!',
    });

    await expect(register.errorAlert).toBeVisible();
    await expect(page).toHaveURL(new RegExp(`${ROUTES.register}$`));
  });
});

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
