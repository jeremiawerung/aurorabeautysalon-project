# E2E Test — Aurora Beauty Salon

Automation test end-to-end memakai [Playwright](https://playwright.dev).
Cakupan saat ini: **Main Menu** (navigasi pelanggan + halaman Beranda), **Registrasi**,
**Login**, dan **alur booking + pembayaran Midtrans** end-to-end.

## Cara menjalankan

```bash
# sekali saja, jika belum pernah
npm install

# jalankan seluruh test (server `php artisan serve` dinyalakan otomatis)
npm run test:e2e

# mode berjendela / mode UI interaktif
npm run test:e2e:headed
npm run test:e2e:ui

# lihat laporan HTML hasil run terakhir
npm run test:e2e:report
```

### Prasyarat

- MySQL menyala dan database `db_aurora_beauty_salon` berisi data master
  (minimal kategori layanan, layanan aktif, dan foto galeri).
- Google Chrome terpasang di mesin. Playwright dikonfigurasi memakai Chrome lokal
  (`channel: 'chrome'`) supaya tidak bergantung pada unduhan browser bundel Playwright.
  Ganti lewat env var bila perlu:

  ```bash
  E2E_BROWSER_CHANNEL=msedge npm run test:e2e     # pakai Microsoft Edge
  E2E_BROWSER_CHANNEL=chromium npm run test:e2e   # pakai chromium bundel Playwright
  E2E_BASE_URL=http://localhost:8000 npm run test:e2e
  ```

### Data test

- `e2e/support/seed-test-user.php` — membuat/menyinkronkan **satu akun pelanggan tetap**
  (`e2e.tester@aurora.test`) yang sudah terverifikasi, dipakai ulang lewat `storageState`
  oleh test Main Menu. Idempotent, dipanggil otomatis oleh `e2e/auth.setup.js`.
- `e2e/support/seed-unverified-user.php` — akun tetap yang SENGAJA belum verifikasi
  (`e2e.unverified@aurora.test`), dipakai satu skenario negatif di test Login.
- `e2e/support/verify-user.php <email>` — menandai satu akun sebagai terverifikasi lewat
  update DB langsung. Dipakai setelah registrasi lewat form asli, karena test tidak punya
  akses ke inbox email sungguhan (lihat catatan strategi di bagian Temuan).
- `e2e/support/delete-user.php <email>` — menghapus satu akun beserta seluruh data
  turunannya (pelanggan → reservasi → pembayaran/reservasi_slot_jadwal, lewat cascade FK).
  Dipakai sebagai cleanup di `afterEach` test Register & Booking, supaya akun yang dibuat
  lewat form registrasi sungguhan tidak menumpuk di database dev.

Semua akun di atas memakai domain `@aurora.test` yang unik/khusus — tidak pernah menyentuh
data pelanggan asli. Test Main Menu & pencarian bersifat baca-saja; test Register/Login/Booking
menulis data tapi membersihkan diri sendiri (kecuali skenario yang memang menguji *dead code*
tak terjangkau, lihat Temuan).

## Struktur

```
e2e/
  auth.setup.js                          # setup project: seed user + simpan session login
  fixtures/auth.fixture.js               # helper login manual (untuk test yang merusak session)
  pages/
    MainMenuPage.js                      # navbar, dropdown bahasa, panel profil
    HomePage.js                          # hero, pencarian, kategori, galeri, rekomendasi
    RegisterPage.js                      # form registrasi
    LoginPage.js                         # form login
    BookingFlowPage.js                   # wizard booking: kategori -> step1 -> step2 -> step3 -> Snap
    BookingHistoryPage.js                # halaman riwayat booking (baca JSON ajax-reservations)
  support/
    test-data.js                         # kredensial, rute, label per bahasa, konstanta
    php-runner.js                        # wrapper JS untuk memanggil script PHP di bawah ini
    seed-test-user.php                   # akun pelanggan tetap (Main Menu)
    seed-unverified-user.php             # akun pelanggan tetap yang belum verifikasi (Login)
    verify-user.php                      # tandai satu akun terverifikasi lewat DB
    delete-user.php                      # hapus satu akun + cascade datanya
  ui/
    main-menu-navigation-ui.e2e.test.js  # Grup A & B
    main-menu-auth-state-ui.e2e.test.js  # Grup C & D
    home-search-ui.e2e.test.js           # Grup E
    home-content-ui.e2e.test.js          # Grup F, G & H
    register-ui.e2e.test.js              # Grup I — Registrasi
    login-ui.e2e.test.js                 # Grup J — Login
    booking-payment-flow-ui.e2e.test.js  # Grup K — Booking + Pembayaran Midtrans end-to-end
```

## Cakupan test case (51 test + 1 setup)

### Grup A — Navigasi menu utama (tamu)

| # | Skenario |
|---|---|
| 1 | Beranda menampilkan logo, 3 link menu, pengalih bahasa, tombol Masuk & Daftar |
| 2 | Klik **Tentang Kami** → `/about`, link menjadi `active`, hanya satu link aktif |
| 3 | Klik **Hubungi Kami** → `/contact`, link menjadi `active` |
| 4 | Klik **Beranda** dari `/about` → kembali ke `/` |
| 5 | Menu tampil konsisten di `/`, `/about`, dan `/contact` |

### Grup B — Pengalih bahasa

| # | Skenario |
|---|---|
| 6 | Locale default aplikasi dipakai saat pengunjung belum memilih bahasa |
| 7 | Memilih bahasa lain mengganti teks menu **dan tetap di halaman yang sama** (`back()`) |
| 8 | Kembali ke locale default mengembalikan teks menu semula |
| 9 | Pilihan bahasa bertahan saat berpindah halaman (disimpan di session) |
| 10 | *Edge:* `/lang/fr` (locale tidak didukung) → **404** |
| 11 | *Edge:* dropdown bahasa tertutup saat klik di luar area dropdown |

### Grup C — State tamu vs pelanggan

| # | Skenario |
|---|---|
| 12 | Tamu hanya melihat Masuk & Daftar; tombol Booking / ikon profil tidak ada di DOM |
| 13 | Klik Masuk → `/login`; klik Daftar → `/register` |
| 14 | Login pelanggan → diarahkan ke `/`, muncul tombol Booking & ikon profil, tombol tamu hilang |
| 15 | Klik tombol **Booking** → `/booking/kategori` |

### Grup D — Panel profil (sidebar)

| # | Skenario |
|---|---|
| 16 | Klik ikon profil → panel & overlay `active`, scroll body terkunci (`overflow: hidden`) |
| 17 | Klik overlay → panel tertutup, scroll body pulih |
| 18 | Klik tombol **×** → panel tertutup |
| 19 | Panel pelanggan berisi nama user, Daftar Booking, Profil, Keranjang Booking, Keluar |
| 20 | Klik **Daftar Booking** → `/booking/history` |
| 21 | Klik **Profil** → `/profil` |
| 22 | Klik **Keranjang Booking** → `/layanan` |
| 23 | Logout → dialog konfirmasi → **Batal** → tetap login di halaman yang sama |
| 24 | Logout → dialog konfirmasi → **Ya** → diarahkan ke `/login`, menu kembali versi tamu |
| 25 | *Edge:* viewport mobile 375×812 → hamburger tampil, panel menu tamu berfungsi |

### Grup E — Pencarian layanan di Beranda

| # | Skenario |
|---|---|
| 26 | Hero, kolom pencarian, dan tombol Cari tampil; kotak saran tersembunyi di awal |
| 27 | *Edge:* keyword 1 karakter tidak memicu request ke `/layanan/search` |
| 28 | Keyword cocok → saran tampil lengkap dengan nama, kategori • durasi, dan harga |
| 29 | *Edge:* keyword tanpa hasil → pesan kosong, tidak ada item saran |
| 30 | Klik salah satu saran → `/layanan?highlight={id}` |
| 31 | Tekan **Enter** → `/layanan?search={keyword}` |
| 32 | Tombol Cari dengan kolom kosong → `/layanan` tanpa query |
| 33 | *Edge:* klik di luar kolom pencarian menutup daftar saran |

### Grup F — Grid kategori layanan (AJAX)

| # | Skenario |
|---|---|
| 34 | Placeholder loading tergantikan 5 kartu kategori berisi gambar & nama |
| 35 | Pelanggan login klik kartu → `/booking/{nama kategori}` |
| 36 | *Edge:* tamu klik kartu → diarahkan ke `/login` (rute booking terproteksi) |

### Grup G — Galeri "Experience" (AJAX)

| # | Skenario |
|---|---|
| 37 | 5 foto galeri tampil, placeholder loading tergantikan seluruhnya |

### Grup H — Rekomendasi layanan (AJAX)

| # | Skenario |
|---|---|
| 38 | Kartu rekomendasi tampil lengkap: nama, harga berformat `Rp`, tombol CTA |
| 39 | Klik CTA mengarahkan ke alur booking |
| 40 | Tombol panah kanan/kiri menggeser posisi scroll carousel |

### Grup I — Registrasi pelanggan (`register-ui.e2e.test.js`)

| # | Skenario |
|---|---|
| 41 | Mendaftar dengan data valid → diarahkan ke `/email/verify` (register() otomatis `Auth::login()`) |
| 42 | *Edge:* email yang sudah terdaftar → error validasi, tetap di `/register` |
| 43 | *Edge:* konfirmasi password tidak cocok → error validasi |
| 44 | *Edge:* password kurang dari 8 karakter → error validasi |
| 45 | *Edge:* nomor telepon berisi huruf → error validasi (`numeric`) |

### Grup J — Login pelanggan (`login-ui.e2e.test.js`)

| # | Skenario |
|---|---|
| 46 | Login dengan kredensial benar → menu pelanggan (ikon profil) tampil |
| 47 | *Edge:* password salah → pesan "Email atau password salah" |
| 48 | *Edge:* email tidak terdaftar → pesan generik yang sama (tidak membocorkan email valid) |
| 49 | *Edge:* akun belum verifikasi email → login ditolak & dipaksa logout ulang oleh server |

### Grup K — Booking + Pembayaran Midtrans end-to-end (`booking-payment-flow-ui.e2e.test.js`)

| # | Skenario |
|---|---|
| 50 | Alur penuh: daftar akun baru → verifikasi via DB → logout → login ulang → pilih kategori → tambah layanan ke keranjang → pilih jadwal → checkout Bayar Penuh → popup Snap Midtrans **sandbox nyata** terbuka (diverifikasi) → pembayaran diselesaikan lewat endpoint asli → halaman sukses → reservasi tercatat `is_lunas: true` di riwayat |
| 51 | *Edge:* tamu membuka `/booking/kategori` tanpa login → diarahkan ke `/login` |

## Catatan temuan

Diurutkan dari yang paling parah. Dua yang pertama sudah diperbaiki (lihat detail perbaikan
& cara verifikasi di masing-masing).

### 1. ✅ SUDAH DIPERBAIKI — Bypass autentikasi lewat route verifikasi email (KRITIS)

`EmailVerificationController::verify()` men-login-kan siapa pun yang mengunjungi
`/email/verify/{id}/{hash}` sebagai user dengan `id` tersebut **tanpa mengecek password sama
sekali**, dan route-nya semula **tidak dibungkus middleware `signed`** maupun validasi hash di
controller:
```php
if (!Auth::check()) {
     $user = \App\Models\User::find($userId);
     if ($user) {
         Auth::login($user);   // login tanpa cek apa pun
     }
}
```
Karena `id` di tabel `users` adalah angka berurutan, siapa pun bisa login sebagai user mana pun
(termasuk admin) hanya dengan menebak ID — **account takeover penuh**, bukan sekadar celah
verifikasi email.

**Perbaikan** ([routes/web.php](../routes/web.php), [EmailVerificationController.php](../app/Http/Controllers/EmailVerificationController.php)):
1. Route diberi middleware `signed` — menolak (403) URL apa pun yang tidak punya signature HMAC
   valid (hanya bisa dihasilkan oleh server sendiri lewat `URL::temporarySignedRoute`, yang
   sudah dipakai notifikasi verifikasi bawaan Laravel — jadi email asli tidak terpengaruh).
2. Controller memvalidasi `hash_equals($hash, sha1($user->getEmailForVerification()))` sebagai
   lapis kedua (defense-in-depth, konsisten dengan `EmailVerificationRequest` bawaan Laravel).

**Diverifikasi manual** (bukan cuma dibaca kodenya):
```
URL palsu   /email/verify/40/anyfakehash1234           -> HTTP 403 (ditolak)
URL asli    /email/verify/40/<hash>?expires=...&signature=...  -> HTTP 200, email_verified_at terisi
```
Seluruh 52 test Playwright tetap hijau setelah perbaikan ini (test verifikasi lewat
`verify-user.php` yang mengubah DB langsung, tidak lewat route ini, jadi tidak terdampak).

### 2. ✅ SUDAH DIPERBAIKI — Registrasi lambat karena email verifikasi dikirim sinkron

`PelangganAuthController::register()` memanggil `event(new Registered($user))`, dan notifikasi
verifikasi bawaan Laravel **tidak di-queue** di project ini — padahal `MAIL_MAILER=smtp`
mengarah ke SMTP Gmail sungguhan. Akibatnya request registrasi menunggu SMTP handshake selesai
secara sinkron sebelum redirect dikembalikan, terbukti **memakan lebih dari 10 detik**.

**Perbaikan**: [app/Notifications/QueuedVerifyEmail.php](../app/Notifications/QueuedVerifyEmail.php)
(extends `VerifyEmail` bawaan + `implements ShouldQueue`), dipakai lewat method baru
`App\Models\User::sendEmailVerificationNotification()` yang meng-override versi trait
default. `QUEUE_CONNECTION=database` di `.env` sudah aktif; worker perlu jalan
(`php artisan queue:work`, atau `composer dev` yang sudah menjalankan `queue:listen`).

**Diverifikasi manual**: waktu respons registrasi turun dari **>10 detik → ~1,4 detik**; job
`App\Notifications\QueuedVerifyEmail` terbukti masuk tabel `jobs` lalu berhasil diproses
(`queue:work --once`) tanpa masuk `failed_jobs`. Di suite Playwright, test registrasi yang
sebelumnya butuh 22–33 detik sekarang selesai dalam 4–8 detik.

### 3. Locale default tidak mengikuti `.env`

`config/app.php` menetapkan `'locale' => 'id'` secara hardcoded (skeleton Laravel standar
memakai `env('APP_LOCALE', 'en')`), sehingga `APP_LOCALE=en` di `.env` tidak berpengaruh.
Perilaku aplikasi konsisten, hanya konfigurasinya yang membingungkan. Test mengikuti perilaku
nyata lewat konstanta `DEFAULT_LOCALE` di `support/test-data.js`.

### 4. Dua route pembayaran menunjuk ke method yang tidak ada (bug nyata, terverifikasi)

`routes/web.php` mendaftarkan:
```php
Route::get('/booking/checkout', 'showBookingPage')->name('booking.checkout');
Route::post('/midtrans/generate-token', 'generateSnapToken')->name('midtrans.generate');
```
Namun **`PembayaranController` tidak punya method `showBookingPage` maupun `generateSnapToken`**
(sudah diverifikasi dengan `grep` langsung, bukan asumsi) — kedua route ini akan
`Error: Call to undefined method` kalau diakses. Untungnya alur booking yang sebenarnya
dipakai UI (`booking-step3.blade.php`) tidak memanggil route ini sama sekali — ia memakai
`PembayaranController::proses()` lewat route `booking.midtrans.proses` yang berfungsi normal
(dan sudah dites di Grup K). Tampaknya sisa refactor lama yang belum dibersihkan.

### 5. `BookingController::success($ids)` adalah dead code

Method ini (single-reservasi) dan view `booking-success.blade.php` tidak punya route yang
mengarah ke sana sama sekali — sudah dicek di `routes/web.php`, hanya `successMulti` yang
terdaftar (`booking.successMulti`, dipakai di Grup K). Aman diabaikan atau dihapus.

### 6. Field `metode` & `pay_type_selected` pada pembayaran didiamkan (silent data loss)

`PembayaranController::midtransJsCallback()` memanggil:
```php
Pembayaran::create([..., 'metode' => $paymentType, 'pay_type_selected' => $payTypeSelected, ...]);
```
tapi tabel `pembayaran` **tidak punya kolom `metode` maupun `pay_type_selected`**, dan
`$fillable` di `App\Models\Pembayaran` juga tidak menyebutkan keduanya. Karena Eloquent
`create()`/`fill()` diam-diam membuang key yang tidak ada di `$fillable` (tidak melempar
error), pembayaran tetap tersimpan dengan benar — cuma informasi channel pembayaran
(kartu/VA/dsb.) dan jenis bayar (`dp`/`full`) yang seharusnya tercatat per baris pembayaran
**hilang tanpa jejak**. Tidak menyebabkan crash, tapi kalau ada laporan admin yang
mengandalkan kolom ini, hasilnya akan selalu kosong.

### 7. Popup Snap Midtrans sandbox bersifat non-deterministik

Dikonfirmasi lewat eksplorasi manual berulang: popup Snap (sandbox, `MIDTRANS_IS_PRODUCTION=false`)
kadang menampilkan daftar lengkap metode pembayaran, kadang langsung redirect ke satu metode
"rekomendasi" acak (QRIS/ShopeePay/dll) tanpa jalan kembali yang konsisten ke daftar penuh.
Ini perilaku Midtrans sendiri (pihak ketiga), bukan bug aplikasi. Strategi test: **buktikan
integrasi nyata tersambung** (popup benar-benar terbuka dari `app.sandbox.midtrans.com`,
lihat Grup K #50), lalu **selesaikan pembayaran lewat endpoint `/booking/midtrans/callback`
milik aplikasi sendiri** — endpoint yang persis sama yang dipanggil oleh callback `onSuccess`
Snap.js di kode asli — bukan menulis status "lunas" langsung ke database. Ini tetap menguji
logika backend asli, hanya menghindari ketidakstabilan UI pihak ketiga sebagai gerbang lulus.
Detail lengkap ada sebagai komentar di `pages/BookingFlowPage.js`.

## Backlog — kandidat test berikutnya (belum dikerjakan)

### Prioritas tinggi (alur bisnis inti)
- **Keranjang booking lanjutan**: hapus/ubah item dari sidebar step1/step2, update jadwal
  yang sudah tersimpan, validasi slot bentrok antar 2 pelanggan (konflik saat rebutan slot).
- **Pembayaran DP (bukan hanya Bayar Penuh)**: verifikasi `sisa_pembayaran` & status `dp` di
  riwayat, lalu pelunasan sisa via `#btn-bayar-sekarang` di halaman riwayat.
- **Validasi voucher/diskon**: kode valid, kadaluwarsa, tidak berlaku, sudah terpakai
  (endpoint `booking.voucher.validate` sudah dipetakan, belum ditulis testnya).
- **Pembatalan booking** oleh pelanggan (`.btn-cancel`) + alur konfirmasi/penolakan oleh admin
  (`KonfirmasiPembatalanController`).
- **Callback pending/gagal**: Grup K baru menguji jalur sukses (`settlement`); belum ada test
  untuk `transaction_status: pending` atau popup ditutup manual (`onClose`).

### Prioritas menengah (autentikasi & akun)
- Verifikasi email lewat link asli (`/email/verify/{id}/{hash}` bertanda tangan, sudah
  diperbaiki di Temuan #1) — belum ada test otomatis untuk jalur ini secara langsung
  (test saat ini memverifikasi lewat DB, bukan lewat link sungguhan), plus kirim ulang
  (`throttle:6,1`), link kedaluwarsa (>60 menit), dan link yang sudah dipakai.
- Fitur *remember me* pada login.
- Lupa password: kirim link reset, reset dengan token valid/kadaluwarsa.
- Halaman profil pelanggan & ganti password.
- Riwayat booking: filter tab status (`semua`/`menunggu_konfirmasi`/dst), pencarian, tampilan kosong.

### Prioritas menengah (sisi admin)
- Navbar admin sebagai flow tersendiri (Dashboard, Booking, Pelanggan, POS, dropdown Laporan & Data Master).
- CRUD data master: layanan, kategori layanan, slot jadwal, diskon, metode pembayaran, pelanggan.
- POS: pencarian pelanggan, simpan transaksi, verifikasi password tunai, daftar DP, pelunasan.
- Laporan: pendapatan / transaksi / rata-rata + ekspor Excel dan PDF.
- Notifikasi admin: ambil daftar, tandai dibaca, tandai semua dibaca.
- Import/export pelanggan, layanan, dan pembayaran.
- Pengaturan: booking, arsip, tentang kami, lokasi.

### Prioritas rendah (pelengkap)
- Halaman `/layanan`: filter kategori, pencarian, parameter `highlight`.
- Halaman Tentang Kami & Hubungi Kami beserta pengiriman form kontak.
- Navigasi footer (Jelajahi, jam operasional, tautan media sosial).
- Kontrol akses berbasis peran: pelanggan mencoba membuka rute `/admin/*`.
- Aksesibilitas & responsive di beberapa breakpoint (480px, 768px, 992px).
- Test level API/Feature (PHPUnit) untuk endpoint AJAX: `/ajax/layanan/pelanggan`,
  `/ajax/kategorilayanan/pelanggan`, `/ajax/galeri/pelanggan`, `/layanan/search`.
