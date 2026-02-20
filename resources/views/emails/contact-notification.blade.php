<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pesan Baru dari Form Kontak</title>
</head>
<body style="font-family: Arial, sans-serif; font-size: 14px; color:#333;">
    <h2 style="margin-bottom: 16px;">Pesan Baru dari Form Kontak Website</h2>

    <p>Berikut detail pesan yang dikirim oleh pengunjung:</p>

    <table cellpadding="6" cellspacing="0" border="0" style="margin-top: 10px;">
        <tr>
            <td><strong>Nama</strong></td>
            <td>:</td>
            <td>{{ $data['name'] }}</td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td>:</td>
            <td>{{ $data['email'] }}</td>
        </tr>
        <tr>
            <td><strong>No. Telepon</strong></td>
            <td>:</td>
            <td>{{ $data['phone'] }}</td>
        </tr>
        <tr>
            <td><strong>Layanan yang diminati</strong></td>
            <td>:</td>
            <td>{{ $data['services'] }}</td>
        </tr>
    </table>

    <p style="margin-top:16px;"><strong>Catatan / Pesan:</strong></p>
    <p style="white-space:pre-line; border:1px solid #eee; padding:10px; border-radius:4px; background:#fafafa;">
        {{ $data['message'] }}
    </p>

    <p style="margin-top:20px; font-size:12px; color:#777;">
        Email ini dikirim otomatis dari halaman kontak Aurora Beauty Salon.
    </p>
</body>
</html>
