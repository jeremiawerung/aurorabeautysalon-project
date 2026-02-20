<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Booking - Midtrans Snap</title>
    <!-- Load Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    <!-- Load Midtrans Snap Script -->
    <!-- Ganti 'your-client-key' dengan MIDTRANS_CLIENT_KEY di .env (contoh: SB-Mid-client-XXXX) -->
        <script type="text/javascript"
        src="https://app.{{ config('midtrans.isProduction') ? 'midtrans' : 'sandbox.midtrans' }}.com/snap/snap.js"
        data-client-key="{{ config('midtrans.clientKey') }}">
</script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <!-- Card Pembayaran -->
    <div class="w-full max-w-lg bg-white shadow-2xl rounded-xl p-8 space-y-6">
        <h1 class="text-3xl font-bold text-center text-blue-600">Konfirmasi Pembayaran</h1>
        <p class="text-center text-gray-500">Selesaikan transaksi Anda untuk menyelesaikan pesanan.</p>

        <!-- Detail Transaksi -->
        <div class="border border-gray-200 rounded-lg p-4 space-y-3">
            <div class="flex justify-between items-center pb-2 border-b">
                <span class="text-sm font-semibold text-gray-700">Kode Transaksi:</span>
                <span class="text-sm font-bold text-blue-600" id="order-id">{{ $reservasi->kode_reservasi }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-lg font-medium text-gray-800">Total Pembayaran:</span>
                <span class="text-2xl font-extrabold text-red-600">
                    Rp {{ number_format($reservasi->total_harga, 0, ',', '.') }}
                </span>
            </div>
            <div class="pt-3 border-t">
                <p class="text-sm text-gray-500">
                    Detail Pemesan: {{ $user->name ?? 'Guest' }} ({{ $user->email ?? '-' }})
                </p>
                <p class="text-sm text-gray-500">
                    Status Pembayaran: <span class="font-semibold text-orange-500">{{ $reservasi->status_pembayaran }}</span>
                </p>
            </div>
        </div>

        <!-- Tombol Bayar -->
        <button id="pay-button"
            data-reservasi-id="{{ $reservasi->id }}"
            class="w-full py-3 transition duration-300 ease-in-out font-bold text-white bg-green-500 hover:bg-green-600 rounded-lg shadow-lg shadow-green-200 focus:outline-none focus:ring-4 focus:ring-green-300 disabled:opacity-50">
            Bayar Sekarang
        </button>

        <!-- Pesan Status/Error -->
        <div id="status-message" class="hidden p-3 rounded-lg text-center font-medium"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const payButton = document.getElementById('pay-button');
            const reservasiId = payButton.getAttribute('data-reservasi-id');
            const statusMessage = document.getElementById('status-message');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (!window.snap) {
                showStatus('danger', 'Error: Midtrans Snap.js gagal dimuat. Pastikan MIDTRANS_CLIENT_KEY sudah benar.');
                payButton.disabled = true;
                return;
            }

            payButton.addEventListener('click', async function () {
                payButton.disabled = true;
                payButton.textContent = 'Memproses...';
                showStatus('info', 'Menghubungkan ke Midtrans...');

                try {
                    // 1. Panggil endpoint Laravel untuk mendapatkan Snap Token
                    const response = await fetch('{{ route('midtrans.generate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ reservasi_id: reservasiId })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        showStatus('danger', data.error || 'Gagal membuat token pembayaran.');
                        return;
                    }

                    // 2. Buka modal Midtrans Snap
                    const snapToken = data.snap_token;
                    window.snap.pay(snapToken, {
                        onSuccess: function(result) {
                            showStatus('success', 'Pembayaran berhasil! Redirecting...');
                            // Redirect ke halaman sukses setelah transaksi
                            window.location.href = '{{ url('/booking/success') }}/' + result.order_id;
                        },
                        onPending: function(result) {
                            showStatus('warning', 'Pembayaran pending. Silakan selesaikan instruksi pembayaran.');
                            // Redirect ke halaman sukses setelah transaksi
                            window.location.href = '{{ url('/booking/success') }}/' + result.order_id;
                        },
                        onError: function(result) {
                            showStatus('danger', 'Pembayaran gagal.');
                            // Redirect ke halaman sukses setelah transaksi
                            window.location.href = '{{ url('/booking/success') }}/' + result.order_id;
                        },
                        onClose: function() {
                            showStatus('info', 'Anda menutup jendela pembayaran.');
                        }
                    });

                } catch (error) {
                    console.error('Fetch Error:', error);
                    showStatus('danger', 'Terjadi kesalahan pada koneksi server.');
                } finally {
                    payButton.disabled = false;
                    payButton.textContent = 'Bayar Sekarang';
                }
            });

            function showStatus(type, message) {
                statusMessage.textContent = message;
                statusMessage.className = 'p-3 rounded-lg text-center font-medium'; // Reset classes
                statusMessage.classList.remove('hidden');

                if (type === 'success') {
                    statusMessage.classList.add('bg-green-100', 'text-green-700');
                } else if (type === 'warning' || type === 'pending') {
                    statusMessage.classList.add('bg-yellow-100', 'text-yellow-700');
                } else if (type === 'danger') {
                    statusMessage.classList.add('bg-red-100', 'text-red-700');
                } else {
                    statusMessage.classList.add('bg-blue-100', 'text-blue-700');
                }
            }
        });
    </script>
</body>
</html>
