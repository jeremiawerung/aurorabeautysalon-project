<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $title }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
    h3 { margin: 0 0 12px 0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border:1px solid #000000ff; padding:5px; vertical-align: top; }
    th { background:#f1b8d6; }
    .right { text-align:right; }
    .center { text-align:center; }
    .mt-16 { margin-top:16px; }
    .text-xs { font-size: 10px; color: #666; }
  </style>
</head>
<body>
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

  <h3 class="center">{{ $title }}</h3>

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
</body>
</html>
