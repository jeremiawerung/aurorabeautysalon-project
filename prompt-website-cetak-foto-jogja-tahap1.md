# PROMPT: Website Cetak Foto Jogja & Sewa Fotografer (Tahap 1)

Salin semua isi file ini dan tempel sebagai pesan pertama di Claude Code.

---

## Instruksi Pembuka

Saya ingin membuat website untuk usaha fotografer dengan menggunakan teknologi website seperti pada proyek yang sedang terbuka ini. Tolong buatkan sebagai proyek baru di folder terpisah, jangan mengubah atau menambah apapun di proyek yang sedang berjalan sekarang, cukup ikuti pola stack, struktur folder, dan konvensi coding yang sama. Jadi saya tinggal sesuaikan bagian isi dan fungsi sesuai kebutuhan usaha saya, seperti berikut ini.

## Profil Usaha

- Nama usaha: Cetak Foto Jogja
- Dua lini layanan yang dijalankan bersamaan:
  1. Cetak foto (pas foto, cetak reguler, pigura, polaroid, photo strip, photo square, photo block, mini canvas, jasa editing)
  2. Sewa / panggilan fotografer (fotografer datang ke lokasi acara)
- Kontak: WhatsApp 0895 708 600 900
- Instagram: @cetakfotojogja
- Website referensi konten: www.cetakfotojogja.com

## Ruang Lingkup Tahap Ini

Kerjakan dulu HANYA fitur di bawah (kelompok A, B, C). Fitur lain seperti galeri, testimoni, FAQ, dan halaman lokasi/Maps BELUM perlu dibuat sekarang, itu akan menyusul lewat prompt lanjutan setelah semua fitur di tahap ini sudah berjalan sesuai.

## A. Fitur Layanan Cetak Foto

1. **Halaman Katalog & Harga** — tampilkan seluruh data produk di bawah (lihat bagian "Data Katalog"), dikelompokkan per kategori, layout kartu atau tabel yang rapi dan mudah dibaca di HP.
2. **Form Order + Upload Foto** — pelanggan pilih kategori produk, ukuran, jumlah lembar/pcs, lalu upload file foto dari perangkat mereka.
3. **Tombol Order via WhatsApp** — setelah form diisi, generate link `wa.me` ke nomor 0895 708 600 900 dengan teks pesan yang otomatis terisi ringkasan order (produk, ukuran, jumlah, estimasi harga).
4. **Kalkulator Estimasi Harga** — total harga muncul otomatis secara real-time saat pelanggan mengisi form, berdasarkan data harga di bagian "Data Katalog".

## B. Fitur Layanan Sewa Fotografer

5. **Form Detail Acara** — input: jenis acara (prewedding, ulang tahun, produk, keluarga, dll — buat dropdown), lokasi pemotretan, tanggal, jam, estimasi jumlah orang, dan catatan tambahan. Setelah submit, generate juga link WA otomatis seperti poin 3.
6. **Kalender Booking Sesi Foto** — tampilan kalender sederhana untuk pilih tanggal dan jam sesi. Sistem cukup mengecek apakah tanggal-jam yang sama sudah dibooking orang lain (baca dari data yang tersimpan), tidak perlu logic yang rumit, cukup validasi bentrok dasar.

## C. Pendekatan Data: Hybrid Ringan

- Setiap order (cetak maupun booking fotografer) mengarah ke WhatsApp untuk closing, seperti biasa.
- Bersamaan dengan itu, data order otomatis tersimpan ke penyimpanan ringan di belakang layar (pilih salah satu yang paling gampang diintegrasikan dengan stack proyek referensi: Google Sheets API, Supabase, atau Airtable).
- Tujuannya cuma sebagai log/rekap harian, JANGAN buat dashboard admin dengan sistem login, itu belum diperlukan di tahap ini.

## Data Katalog & Harga (Pakai Data Ini Apa Adanya)

### Pas Photo — Paket Hemat
| Paket | Harga Normal | Harga Promo | Rincian |
|---|---|---|---|
| A | 22.000 | 20.000 | 2x3: 12 lbr, 3x4: 5 lbr, 4x6: 4 lbr |
| B | 26.000 | 25.000 | 2x3: 6 lbr, 3x4: 10 lbr, 4x6: 8 lbr |
| C | 28.000 | 25.000 | 2x3: 18 lbr, 3x4: 5 lbr, 4x6: 4 lbr |
| D | 32.000 | 30.000 | 2x3: 12 lbr, 3x4: 15 lbr, 4x6: 4 lbr |

### Pas Photo — Satuan
- 4x6: Rp15.000 / 12 foto
- 3x4: Rp15.000 / 15 foto
- 2x3: Rp15.000 / 15 foto

Kebutuhan yang bisa dicantumkan sebagai info tambahan: pas foto umum, visa, pendaftaran haji/umroh, syarat menikah, ijazah, pendaftaran studi, dan syarat administrasi lainnya.

### Jasa Editing Foto
- Ganti background: Rp5.000 / foto
- Ganti baju: Rp10.000 / foto
- Ganti jilbab: Rp10.000 / foto

### Cetak Reguler (per ukuran)
| Ukuran | Dimensi | Harga |
|---|---|---|
| 2R | 5,5x8,5 cm | Rp10.000 / 5 foto |
| 3R | 8,3x12,8 cm | Rp12.000 / 4 foto |
| 4R | 10,2x15,2 cm | Rp20.000 / 5 foto |
| 5R | 12,7x17,8 cm | Rp10.000 / 2 foto |
| 6R | 15,2x20,3 cm | Rp20.000 / 3 foto |
| 10R | 20x25 cm | Rp10.000 / foto |
| 10Rw | 20x30 cm | Rp15.000 / foto |
| 12R | 30x40 cm | Rp50.000 / foto |
| 12Rw | 30x45 cm | Rp60.000 / foto |
| 16R | 40x50 cm | Rp100.000 / foto |
| 16Rw | 40x60 cm | Rp115.000 / foto |
| 20R | 50x60 cm | Rp125.000 / foto |
| 20Rw | 50x75 cm | Rp140.000 / foto |
| 24R | 60x90 cm | Rp200.000 / foto |

### Pigura (per ukuran)
| Ukuran | Dimensi | Harga |
|---|---|---|
| 4R | 10,2x15,2 cm | Rp20.000 / pcs |
| 5R | 12,7x17,8 cm | Rp30.000 / pcs |
| 6R | 15,2x20,3 cm | Rp40.000 / pcs |
| 10R | 20x25 cm | Rp50.000 / pcs |
| 10Rw | 20x30 cm | Rp60.000 / pcs |
| 12R | 30x40 cm | Rp120.000 / pcs |
| 12Rw | 30x45 cm | Rp140.000 / pcs |
| 16R | 40x50 cm | Rp280.000 / pcs |
| 16Rw | 40x60 cm | Rp300.000 / pcs |
| 20R | 50x60 cm | Rp325.000 / pcs |
| 20Rw | 50x75 cm | Rp400.000 / pcs |
| 24R | 60x90 cm | Rp500.000 / pcs |

Custom motif frame: konfirmasi admin, cantumkan sebagai catatan "harga custom, hubungi admin".

### Polaroid
Ukuran 5,5x8,5 cm, rasio portrait, kertas doff Fujifilm.
- 1-9 foto: Rp2.000 / foto
- 10-19 foto: Rp1.900 / foto
- 20 foto ke atas: Rp1.800 / foto

### Photo Strip
Ukuran 5x15 cm, rasio 1:1, 1 strip berisi 3 foto.
- Ecer: Rp4.000 / strip
- Per 8 strip: Rp30.000

### Photo Square
Ukuran cetak 10x10 cm (area foto 9x9 cm), rasio 1:1.
- Ecer: Rp5.000 / foto
- Per 6 foto: Rp28.000

### Photo Block
Pigura minimalis tanpa kaca, sudah termasuk laminasi.
- 20x25: Rp55.000 / pcs
- 20x30: Rp60.000 / pcs
- 30x40: Rp90.000 / pcs
- 30x45: Rp95.000 / pcs
- 40x60: Rp135.000 / pcs

### Mini Canvas
Ukuran 10x7 cm, bahan kanvas, sudah termasuk spanram dan senderan kayu, bisa request desain custom.
- Bentuk kotak: Rp25.000 / foto
- Bentuk polygon: Rp25.000 / foto

## Data Paket Sewa Fotografer (Sementara / Placeholder)

Harga dan ketentuan paket sewa fotografer belum saya tentukan final. Untuk sekarang, buat saja struktur data dummy dulu (misal: Paket Basic, Standard, Premium dengan field durasi, jumlah foto hasil edit, harga placeholder "TBD"), supaya nanti saya tinggal isi manual lewat file data/config, tanpa perlu ubah struktur kode.

## Gaya Desain & UX

- Bahasa antarmuka: Bahasa Indonesia.
- Layout mobile-friendly, mengingat kebanyakan calon pelanggan akan akses lewat HP.
- Navigasi dipisah jelas jadi dua bagian besar: "Cetak Foto" dan "Sewa Fotografer".
- Tampilan simpel, tidak perlu animasi berat atau elemen visual berlebihan di tahap ini.

## Batasan — JANGAN Dikerjakan Dulu di Tahap Ini

- Payment gateway online
- Sistem voucher / diskon otomatis
- Dashboard admin dengan sistem login
- Tracking status order otomatis
- Galeri, testimoni, FAQ, dan halaman lokasi/Maps (fitur-fitur ini masuk Prioritas 3, akan dikerjakan lewat prompt terpisah setelah Tahap 1 ini selesai dan sudah sesuai kebutuhan)
