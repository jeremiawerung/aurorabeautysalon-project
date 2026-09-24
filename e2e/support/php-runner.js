import { execFileSync } from 'node:child_process';
import path from 'node:path';

import { PROJECT_ROOT } from './test-data.js';

/** Menjalankan salah satu script PHP di e2e/support/ dan mengembalikan stdout-nya. */
function runPhpScript(scriptName, args = []) {
  return execFileSync('php', [path.join('e2e', 'support', scriptName), ...args], {
    cwd: PROJECT_ROOT,
    encoding: 'utf8',
  });
}

/** Menandai satu akun sebagai terverifikasi email langsung lewat DB (lihat verify-user.php). */
export function verifyUserByEmail(email) {
  return runPhpScript('verify-user.php', [email]);
}

/** Menghapus satu akun beserta seluruh data turunannya (lihat delete-user.php). */
export function deleteUserByEmail(email) {
  return runPhpScript('delete-user.php', [email]);
}

/** Memastikan akun pelanggan belum-terverifikasi tersedia (lihat seed-unverified-user.php). */
export function seedUnverifiedUser() {
  return runPhpScript('seed-unverified-user.php');
}
