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
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
    h3 { margin: 0 0 12px 0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border:1px solid #000000ff; padding:5px; vertical-align: top; }
    th { background:#f1b8d6; } {{-- Warna pink header tabel --}}
    .right { text-align:right; }
    .center { text-align:center; }
    .mt-16 { margin-top:16px; }
    .text-xs { font-size: 10px; color: #666; }
  </style>
</head>
<body>

  {{-- ============================================================
       ZONA 2: HEADER PERUSAHAAN (Logo, Nama, Alamat)
       - Ubah width di <img> → ukuran logo
       - Ubah teks <h3> → nama perusahaan
       - Ubah/tambah <p> → alamat, telepon
       - Ubah class di <div> → posisi header (center/left/right)
       ============================================================ --}}
  <div class="center mb-4">
    @php
        $logoPath = public_path('img/pdf-img.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/png;base64,' . $logoData;
        }
    @endphp

    @if($logoBase64)
    <img src="{{ $logoBase64 }}" alt="Logo" style="width: 60px; height: auto; display: block; margin: 0 auto 5px;" />
    @endif
    <h3>AURORA BEAUTY SALON</h3>
    <p class="text-xs">Jl. Siliwangi, Kotabangon, Kec. Kotamobagu Tim., Kota Kotamobagu, Sulawesi Utara 95716</p>
  </div>

  {{-- ============================================================
       ZONA 3: JUDUL LAPORAN
       - Ubah tag <h3> → ganti judul atau styling
       ============================================================ --}}
  <h3 class="center">{{ $title }}</h3>

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
        <th style="width:25px;">No</th>
        <th>ID Transaksi</th>
        <th>Tanggal</th>
        <th>Layanan</th>
        <th>Metode</th>
        <th>Status</th>
        <th>Total Bayar</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $i => $r)
        <tr>
          <td class="center">{{ $i+1 }}</td>
          <td>{{ $r['id_pembayaran'] }}</td>
          <td>{{ $r['formatted_tanggal'] ?? '-' }}</td>
          <td>{{ $r['layanan_nama'] ?? '-' }}</td>
          <td>{{ $r['formatted_metode'] ?? '-' }}</td>
          <td>{{ $r['formatted_status'] ?? '-' }}</td>
          <td class="right">{{ $r['formatted_total'] ?? '-' }}</td>
        </tr>
      @empty
        <tr><td colspan="7" class="center">Tidak ada data transaksi.</td></tr>
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
