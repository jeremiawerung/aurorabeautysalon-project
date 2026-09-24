<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $title }}</title>

  {{-- ============================================================
       ZONA 1: CSS / STYLE
       - Ubah font-size di body → ukuran teks keseluruhan
       - Ubah background di th → warna header tabel
       - Ubah padding di th,td → jarak dalam sel tabel
       - Tambah class baru di sini untuk dipakai di bawah
       ============================================================ --}}
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h3 { margin: 0 0 12px 0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border:1px solid #000000ff; padding:6px; }
    th { background:#ff89caff; } {{-- Warna pink header tabel --}}
    .right { text-align:right; }
    .mt-16 { margin-top:16px; }
    .text-center { text-align: center; }
    .text-gray-500 { color: #6b7280; }
    .text-xs { font-size: 10px; }
  </style>
</head>
<body>

  {{-- ============================================================
       ZONA 2: HEADER PERUSAHAAN (Logo, Nama, Alamat, Telepon)
       - Ubah width di <img> → ukuran logo
       - Ubah teks <h3> → nama perusahaan
       - Ubah/tambah <p> → alamat, telepon
       - Ubah style di <div> → posisi header (center/left/right)
       ============================================================ --}}
  <div class="text-center mb-4">
    @php
        $logoPath = public_path('img/pdf-img.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/png;base64,' . $logoData;
        }
    @endphp

    <img src="{{ $logoBase64 }}" alt="Logo" style="width: 80px; height: auto; display: block; margin: 0 auto 10px;" />
    <h3>AURORA BEAUTY SALON</h3>
    <p class="text-xs text-gray-500">Jl. Siliwangi, Kotabangon, Kec. Kotamobagu Tim., Kota Kotamobagu, Sulawesi Utara 95716</p>
    <p class="text-xs text-gray-500">Telp: 0811437065</p>
  </div>

  {{-- ============================================================
       ZONA 3: JUDUL LAPORAN
       - Ubah tag <h3> → ganti judul atau styling
       ============================================================ --}}
  <h3>{{ $title }}</h3>

  {{-- ============================================================
       ZONA 4: TABEL DATA UTAMA
       - Tambah/hapus <th> di thead → tambah/hapus kolom header
       - Tambah/hapus <td> di tbody → tambah/hapus kolom data
       - Ubah width di <th> → lebar kolom
       - Ubah class "right"/"center" di <td> → posisi teks
       - Data dari variabel $rows (array dari controller)
       ============================================================ --}}
  <table>
    <thead>
      <tr>
        <th style="width:40px;">No</th>
        <th>Waktu (Per Hari)</th>
        <th>Rerata Transaksi</th>
        <th>Jumlah Transaksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $i => $r)
        <tr>
          <td class="right">{{ $i+1 }}</td>
          <td>{{ $r['waktu'] ?? '-' }}</td>
          <td class="right">{{ $r['rerata'] ?? '-' }}</td>
          <td class="right">{{ $r['jumlah'] ?? 0 }}</td>
        </tr>
      @empty
        <tr><td colspan="4" style="text-align:center;">Tidak ada data</td></tr>
      @endforelse
    </tbody>
  </table>

  {{-- ============================================================
       ZONA 5: FOOTER (Belum ada — tambahkan di sini jika diminta)
       - Letakkan <div> di sini, sebelum </body>
       - Gunakan position:fixed; bottom:0; → menempel di bawah halaman
       - Contoh isi: tanggal cetak, nama salon, tanda tangan
       Contoh:
       <div style="position:fixed; bottom:0; left:0; right:0; text-align:center; font-size:10px; color:#666; border-top:1px solid #ccc; padding-top:5px;">
         <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }} | Aurora Beauty Salon</p>
       </div>
       ============================================================ --}}

</body>
</html>
