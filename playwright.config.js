// @ts-check
import { defineConfig, devices } from '@playwright/test';

/**
 * Konfigurasi Playwright untuk E2E test Aurora Beauty Salon.
 *
 * Struktur project:
 *  - "setup"    : menyiapkan user test + menyimpan storageState hasil login (dijalankan lebih dulu)
 *  - "chromium" : menjalankan seluruh test. Default-nya state GUEST (tanpa storageState);
 *                 test yang butuh login memakai `test.use({ storageState: PELANGGAN_STORAGE_STATE })`.
 */
export const BASE_URL = process.env.E2E_BASE_URL ?? 'http://127.0.0.1:8000';

/**
 * Browser yang dipakai. Default 'chrome' (Google Chrome yang sudah terpasang di mesin)
 * agar tidak bergantung pada unduhan browser bundel Playwright.
 * Set E2E_BROWSER_CHANNEL=chromium bila browser bundel Playwright sudah tersedia,
 * atau 'msedge' untuk memakai Microsoft Edge.
 */
const BROWSER_CHANNEL = process.env.E2E_BROWSER_CHANNEL ?? 'chrome';

export default defineConfig({
  testDir: './e2e',
  // Pola nama file mengikuti .agent/rules/testing-strategy.md → {feature}-{ui|api}.e2e.test.js
  testMatch: ['**/*.e2e.test.js', '**/*.setup.js'],

  // Test menjalankan navigasi lintas halaman; jalankan serial agar sesi & locale (session-based)
  // tidak saling mengganggu antar worker pada database dev yang sama.
  fullyParallel: false,
  workers: 1,

  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  timeout: 30_000,
  expect: { timeout: 7_000 },

  reporter: [['list'], ['html', { outputFolder: 'e2e-report', open: 'never' }]],

  use: {
    baseURL: BASE_URL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'off',
    // Konten Beranda di-load via AJAX ke DB lokal — beri ruang untuk navigasi lambat.
    navigationTimeout: 20_000,
    actionTimeout: 10_000,
  },

  projects: [
    {
      name: 'setup',
      testMatch: /auth\.setup\.js/,
      use: { ...devices['Desktop Chrome'], channel: BROWSER_CHANNEL },
    },
    {
      name: 'chromium',
      dependencies: ['setup'],
      testMatch: /.*\.e2e\.test\.js/,
      use: { ...devices['Desktop Chrome'], channel: BROWSER_CHANNEL },
    },
  ],

  // Jalankan `php artisan serve` otomatis; kalau server sudah hidup, dipakai ulang.
  webServer: {
    command: 'php artisan serve --host=127.0.0.1 --port=8000',
    url: BASE_URL,
    reuseExistingServer: true,
    timeout: 60_000,
    stdout: 'ignore',
    stderr: 'pipe',
  },
});
