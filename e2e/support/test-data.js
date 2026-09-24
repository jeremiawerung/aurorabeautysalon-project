import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/** Root project (dipakai untuk memanggil script PHP seeder). */
export const PROJECT_ROOT = path.resolve(__dirname, '..', '..');

/** File penyimpan session hasil login pelanggan (dibuat oleh e2e/auth.setup.js). */
export const PELANGGAN_STORAGE_STATE = path.join(__dirname, '..', '.auth', 'pelanggan.json');

/**
 * Akun pelanggan khusus E2E.
 * Dibuat / disinkronkan oleh e2e/support/seed-test-user.php agar test tidak
 * bergantung pada data user asli di database dev.
 */
export const PELANGGAN = {
  name: 'E2E Tester',
  email: 'e2e.tester@aurora.test',
  password: 'E2ePassw0rd!',
};

/**
 * Akun pelanggan yang SENGAJA belum verifikasi email.
 * Disiapkan oleh e2e/support/seed-unverified-user.php, dipakai untuk menguji
 * bahwa login ditolak sebelum email diverifikasi.
 */
export const UNVERIFIED_PELANGGAN = {
  name: 'E2E Unverified Tester',
  email: 'e2e.unverified@aurora.test',
  password: 'E2ePassw0rd!',
};

/** Rute aplikasi yang disentuh oleh flow Main Menu. */
export const ROUTES = {
  home: '/',
  about: '/about',
  contact: '/contact',
  login: '/login',
  register: '/register',
  verifyNotice: '/email/verify',
  layanan: '/layanan',
  bookingCategories: '/booking/kategori',
  bookingSchedule: '/booking/jadwal',
  bookingPembayaran: '/booking/pembayaran',
  bookingMidtransProses: '/booking/midtrans/proses',
  bookingMidtransCallback: '/booking/midtrans/callback',
  bookingHistory: '/booking/history',
  profil: '/profil',
  langId: '/lang/id',
  langEn: '/lang/en',
};

/**
 * Membuat email unik per run test (mis. `register.1234567890@aurora.test`), supaya
 * test Register bisa dijalankan berulang kali tanpa bentrok dengan validasi
 * `unique:users,email`. Pasangkan dengan e2e/support/delete-user.php di teardown
 * agar akun yang dibuat tidak menumpuk di database dev.
 */
export function uniqueTestEmail(prefix = 'e2e') {
  return `${prefix}.${Date.now()}.${Math.floor(Math.random() * 1000)}@aurora.test`;
}

/**
 * Label menu per locale. Dipakai untuk memverifikasi language switcher benar-benar
 * mengganti bahasa (sumbernya resources/lang/{en,id}/nav.php).
 */
export const NAV_LABELS = {
  en: { home: 'Home', about: 'About Us', contact: 'Contact Us', signIn: 'Sign In', signUp: 'Sign Up' },
  id: { home: 'Beranda', about: 'Tentang Kami', contact: 'Hubungi Kami', signIn: 'Masuk', signUp: 'Daftar' },
};

/**
 * Locale default aplikasi untuk pengunjung yang belum pernah memilih bahasa.
 *
 * Sumbernya config/app.php ('locale' => 'id', nilainya hardcoded) — BUKAN APP_LOCALE
 * di .env yang berisi 'en' dan tidak terbaca. Konstanta ini sengaja dipisah supaya
 * kalau konfigurasi tersebut dirapikan, cukup satu baris ini yang diubah.
 */
export const DEFAULT_LOCALE = 'id';

/** Locale alternatif yang dipakai untuk menguji perpindahan bahasa. */
export const ALT_LOCALE = 'en';

/** Jumlah data master yang dipakai sebagai acuan assertion konten Beranda. */
export const EXPECTED = {
  /** Keyword yang pasti punya hasil pencarian (layanan "Facial" ada di data master). */
  searchKeywordWithResults: 'Facial',
  /** Keyword yang dipastikan tidak match layanan mana pun. */
  searchKeywordNoResults: 'zzzzzqqq',
  /** Panjang keyword minimum sebelum request pencarian ditembakkan (welcome.blade.php). */
  searchMinLength: 2,
};
