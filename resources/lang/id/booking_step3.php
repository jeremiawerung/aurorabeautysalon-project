<?php

return [
    // Page & Section Titles
    'select_reservation' => 'Pilih Reservasi',
    'select_reservation_desc' => 'Daftar reservasi di bawah adalah yang sudah Anda pilih dan belum lunas.',
    'payment_confirmation' => 'Konfirmasi Pembayaran',
    'payment_confirmation_desc' => 'Pilih jenis pembayaran dan metode, lalu konfirmasi total sebelum melanjutkan.',
    'payment_method' => 'Metode Pembayaran',
    'payment_method_desc' => 'Semua pembayaran online diproses secara aman melalui Midtrans Snap.',

    // Reservation Card
    'reservation' => 'Reservasi',
    'total_bill' => 'Total tagihan',
    'already_paid' => 'Sudah dibayar',
    'remaining' => 'Sisa',
    'standard_dp' => 'DP standar',
    'nominal' => 'Nominal',

    // Payment Type
    'pay_dp' => 'Bayar DP',
    'pay_dp_desc' => 'Untuk reservasi baru, bayar DP. Yang sudah DP akan dilunasi sisanya.',
    'pay_full' => 'Bayar Penuh',
    'pay_full_desc' => 'Membayar seluruh sisa tagihan dari reservasi yang Anda pilih.',
    'dp_disabled_note' => 'Opsi "Bayar DP" dinonaktifkan karena Anda memilih reservasi dengan status Down Payment. Pembayaran berikutnya akan dihitung sebagai pelunasan (bayar sisa).',

    // Payment Method Options
    'online_payment' => 'Pembayaran Online',
    'online_payment_desc' => 'Kartu, transfer bank, e-wallet, dan metode lain yang didukung.',
    'no_payment_method' => 'Belum ada metode pembayaran yang aktif.',

    // Total & Button
    'total_to_pay' => 'Total yang akan dibayar',
    'total_calculated_note' => 'Total di atas sudah memperhitungkan DP dan pelunasan sesuai pilihan Anda.',
    'continue_payment' => 'Lanjutkan Pembayaran',
    'back_to_history' => 'Kembali ke Riwayat Reservasi',
    'select_reservation_btn' => 'Pilih Reservasi',

    // Notes
    'note' => 'Catatan',
    'note_dp' => 'Jika memilih jenis pembayaran DP, maka reservasi yang belum pernah dibayar akan dikenakan DP.',
    'note_settlement' => 'Untuk reservasi dengan status Down Payment, pembayaran berikutnya akan selalu menjadi pelunasan sisa tagihan.',
    'no_reservation' => 'Tidak ada reservasi yang perlu dibayar saat ini. Silakan kembali ke halaman pilihan layanan.',

    // Loading & Processing
    'processing' => 'Sedang memproses transaksi, mohon tunggu...',
    'processing_reservations' => 'Sedang memproses :count reservasi (:amount), mohon tunggu...',
    'payment_success_saving' => 'Pembayaran **sukses**! Sedang menyimpan data :count reservasi ke database...',
    'payment_pending_saving' => 'Pembayaran **tertunda**! Sedang menyimpan data :count reservasi ke database...',

    // SweetAlert Messages
    'no_bill_title' => 'Belum ada tagihan',
    'no_bill_text' => 'Silakan pilih minimal satu reservasi yang masih memiliki tagihan.',
    'select_method_title' => 'Metode Pembayaran',
    'select_method_text' => 'Silakan pilih metode pembayaran.',
    'failed' => 'Gagal',
    'failed_token' => 'Gagal membuat token pembayaran Midtrans.',
    'save_failed_title' => 'Gagal Menyimpan Riwayat',
    'save_failed_text' => 'Pembayaran berhasil, tapi gagal memperbarui riwayat reservasi. Harap hubungi admin.',
    'pending_title' => 'Pembayaran Tertunda',
    'pending_text' => 'Transaksi berhasil dibuat (:status). Cek riwayat untuk instruksi pembayaran.',
    'save_pending_failed' => 'Pembayaran tertunda, tapi gagal memperbarui riwayat reservasi. Harap hubungi admin.',
    'error_title' => 'Pembayaran Gagal',
    'error_text' => 'Terjadi kesalahan saat memproses pembayaran.',
    'cancelled_title' => 'Pembayaran Dibatalkan',
    'cancelled_text' => 'Anda menutup pop-up pembayaran Midtrans. Status reservasi tidak berubah.',
    'server_error_title' => 'Server error',
    'server_error_text' => 'Terjadi kesalahan server saat memproses pembayaran.',

    'use_voucher' => 'Gunakan Voucher Diskon',
    'use_voucher_desc' => 'Hemat lebih banyak dengan voucher diskon',
    'no_voucher' => 'Tanpa Voucher',
    'no_voucher_desc' => 'Lanjut tanpa diskon',
    'yes_voucher' => 'Pakai Voucher',
    'yes_voucher_desc' => 'Masukkan kode voucher',
    'voucher_code' => 'Kode Voucher',
    'voucher_placeholder' => 'Contoh: LEBARAN2025',
    'apply' => 'Terapkan',
];
