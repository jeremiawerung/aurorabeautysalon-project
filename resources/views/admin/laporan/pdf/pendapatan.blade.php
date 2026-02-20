<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $title }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h3 { margin: 0 0 12px 0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border:1px solid #000000ff; padding:6px; }
    th { background:#ff89caff; }
    .right { text-align:right; }
    .mt-16 { margin-top:16px; }
    .text-center { text-align: center; }
    .text-gray-500 { color: #6b7280; }
    .text-xs { font-size: 10px; }
  </style>
</head>
<body>
  {{-- Header Perusahaan --}}
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

  {{-- Judul Transaksi --}}
  <h3>{{ $title }}</h3>

  {{-- Tabel Detail Transaksi --}}
  <table>
    <thead>
      <tr>
        <th style="width:40px;">No</th>
        <th>Tanggal</th>
        <th>Jumlah</th>
        <th>Metode</th>
        <th>Status</th>
        <th>Total Diskon</th>
        <th>Total Bayar</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $i => $r)
        <tr>
          <td class="right">{{ $i+1 }}</td>
          <td>{{ $r['formatted_tanggal'] ?? '-' }}</td>
          <td class="right">{{ $r['formatted_jumlah'] ?? '-' }}</td>
          <td>{{ $r['formatted_metode'] ?? '-' }}</td>
          <td>{{ $r['formatted_status'] ?? '-' }}</td>
          <td>{{ $r['formatted_diskon'] ?? '-' }}</td>
          <td class="right">{{ $r['formatted_total'] ?? '-' }}</td>
        </tr>
      @empty
        <tr><td colspan="6" style="text-align:center;">Tidak ada data</td></tr>
      @endforelse
    </tbody>
  </table>

  {{-- Tabel Rangkuman --}}
  <table class="mt-16" style="width:auto;">
    <tr><th>Total Jumlah</th><td class="right" style="padding-left:12px;">{{ $summary['total_jumlah'] ?? '-' }}</td></tr>
    <tr><th>Total Diskon</th><td class="right" style="padding-left:12px;">{{ $summary['total_diskon'] ?? '-' }}</td></tr>
    <tr><th>Total Bayar</th><td class="right" style="padding-left:12px;">{{ $summary['total_bayar'] ?? '-' }}</td></tr>
    <tr><th>Total Bayar</th><td class="right" style="padding-left:12px;">{{ $summary['total_diskon'] ?? '-' }}</td></tr>
    <tr><th>Jumlah Transaksi</th><td class="right" style="padding-left:12px;">{{ $summary['count'] ?? 0 }}</td></tr>
  </table>
</body>
</html>