import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

import { expect, test as setup } from '@playwright/test';

import { PELANGGAN, PELANGGAN_STORAGE_STATE, PROJECT_ROOT, ROUTES } from './support/test-data.js';

/**
 * Setup project: dijalankan sekali sebelum semua test.
 *
 * 1. Memastikan akun pelanggan E2E ada & terverifikasi (lewat script PHP).
 * 2. Login sekali, lalu simpan cookie session ke file storageState supaya
 *    test yang butuh state login tidak perlu login berulang kali.
 */
setup('siapkan akun pelanggan E2E dan simpan session login', async ({ page }) => {
  // --- Arrange: pastikan akun test tersedia di database -------------------
  const seedOutput = execFileSync('php', [path.join('e2e', 'support', 'seed-test-user.php')], {
    cwd: PROJECT_ROOT,
    encoding: 'utf8',
  });
  expect(seedOutput, 'seeder user E2E harus sukses').toContain('[e2e-seed] OK');

  // --- Act: login sebagai pelanggan ---------------------------------------
  await page.goto(ROUTES.login);
  await page.locator('input[name="email"]').fill(PELANGGAN.email);
  await page.locator('input[name="password"]').fill(PELANGGAN.password);
  await page.locator('button[type="submit"]').first().click();

  // --- Assert: login berhasil dan diarahkan ke Beranda pelanggan ----------
  await page.waitForURL(new RegExp(`${escapeForRegExp(ROUTES.home)}$`));
  await expect(page.locator('nav .nav-buttons .profile-btn')).toBeVisible();

  // --- Simpan session untuk dipakai ulang oleh test lain ------------------
  fs.mkdirSync(path.dirname(PELANGGAN_STORAGE_STATE), { recursive: true });
  await page.context().storageState({ path: PELANGGAN_STORAGE_STATE });
});

function escapeForRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
