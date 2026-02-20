<?php

return [
    'badge' => 'TRANSAKSI',
    'title' => 'Pilih Kategori Layanan',
    'meta' => [
        // gunakan {!! !!} di blade karena :count akan berisi <strong>...</strong>
        'services_html' => 'Layanan: :count tersedia',
    ],
    'actions' => [
        'choose' => 'Pilih Kategori',
    ],
    'empty' => 'Tidak ada kategori layanan.',
];
