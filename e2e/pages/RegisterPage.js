import { expect } from '@playwright/test';

/** Page Object untuk resources/views/auth/register.blade.php. */
export class RegisterPage {
  /** @param {import('@playwright/test').Page} page */
  constructor(page) {
    this.page = page;

    this.emailInput = page.locator('#email');
    this.namaInput = page.locator('#nama');
    this.nomorTeleponInput = page.locator('#nomor_telepon');
    this.passwordInput = page.locator('#password');
    this.passwordConfirmationInput = page.locator('#password_confirmation');
    this.submitButton = page.locator('form button[type="submit"], form button:not([type])').first();
    this.errorAlert = page.locator('.alert-danger');
  }

  async goto() {
    await this.page.goto('/register');
    await expect(this.emailInput).toBeVisible();
  }

  /** Isi seluruh field form registrasi. */
  async fill({ email, nama, nomorTelepon, password, passwordConfirmation = password }) {
    await this.emailInput.fill(email);
    await this.namaInput.fill(nama);
    await this.nomorTeleponInput.fill(nomorTelepon);
    await this.passwordInput.fill(password);
    await this.passwordConfirmationInput.fill(passwordConfirmation);
  }

  /**
   * register.blade.php juga memasang listener `submit` yang menampilkan modal Bootstrap
   * saat form dikirim, tapi TIDAK memanggil preventDefault() — jadi klik tombol biasa tetap
   * memicu POST asli ke server seperti biasa; modal itu hanya tampilan sekilas sebelum halaman
   * berpindah (redirect ke halaman verifikasi email).
   *
   * Timeout klik sengaja lebih panjang dari default (`actionTimeout` global 10s): registrasi
   * yang SUKSES memicu `event(new Registered($user))`, dan notifikasi verifikasi Laravel
   * (VerifyEmail) TIDAK di-queue di project ini, jadi email sungguhan dikirim SINKRON lewat
   * SMTP Gmail (lihat .env MAIL_MAILER=smtp) sebelum response redirect dikembalikan — ini bisa
   * memakan >10 detik. Ini temuan performa nyata pada aplikasi, bukan artefak test; lihat catatan
   * di e2e/README.md.
   */
  async submit() {
    await this.submitButton.click({ timeout: 45_000 });
  }

  async fillAndSubmit(data) {
    await this.fill(data);
    await this.submit();
  }
}
