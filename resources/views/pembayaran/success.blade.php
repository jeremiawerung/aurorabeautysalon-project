<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md bg-white shadow-xl rounded-xl p-8 text-center space-y-6">

        @php
            $icon = '✅';
            $bgColor = 'bg-green-500';
            if ($status === 'pending') {
                $icon = '⏳';
                $bgColor = 'bg-yellow-500';
            } elseif ($status === 'error') {
                $icon = '❌';
                $bgColor = 'bg-red-500';
            }
        @endphp

        <div class="mx-auto w-20 h-20 rounded-full {{ $bgColor }} flex items-center justify-center text-4xl shadow-lg">
            {{ $icon }}
        </div>

        <h1 class="text-3xl font-bold text-gray-800">
            @if ($status === 'success')
                Pembayaran Berhasil!
            @elseif ($status === 'pending')
                Pembayaran Tertunda
            @else
                Transaksi Gagal
            @endif
        </h1>

        <p class="text-gray-600 leading-relaxed">{{ $message }}</p>

        <a href="{{ url('/booking') }}" class="inline-block px-6 py-2 mt-4 text-sm font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition duration-300">
            Kembali ke Booking
        </a>
    </div>
</body>
</html>
