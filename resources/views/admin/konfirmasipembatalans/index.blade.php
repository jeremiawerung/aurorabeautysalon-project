@extends('layouts.app')

@section('title', 'Konfirmasi Pembatalan Reservasi - Aurora')

@section('content')
<style>
    .card {
        border: 1px solid #f1b8d6 !important;
        border-radius: 10px;
        height: 100%;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f1b8d6 !important;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
    table th, table td {
        font-size: 0.8rem;
        vertical-align: middle;
        white-space: nowrap;
        padding: 0.75rem 0.5rem;
    }
    .content-wrapper { padding: 20px; }

    .btn-aksi {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
    }

    .badge-dp {
        background-color: #ffc107;
        color: #000;
    }
    .badge-lunas {
        background-color: #28a745;
        color: #fff;
    }
    .badge-belum {
        background-color: #6c757d;
        color: #fff;
    }

    @media (max-width: 576px) {
        .table-controls {
            flex-direction: column;
            align-items: stretch !important;
        }
        .table-controls .col-form-label-sm {
            display: block;
            margin-bottom: 0.25rem;
        }
    }

    .modal-body .row {
        margin-bottom: 0.75rem;
    }
    .modal-body .fw-bold {
        color: #333;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Konfirmasi Pembatalan Reservasi</h5>
            </div>

            <div class="card-body">
                <div class="row mb-3 g-2 align-items-center table-controls">
                    <div class="col-12 col-md-auto">
                        <label for="show-entries" class="col-form-label-sm">
                            Menampilkan
                            <select id="show-entries" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            Baris
                        </label>
                    </div>
                    <div class="col-12 col-md-auto ms-md-auto">
                        <label for="search-table" class="col-form-label-sm d-flex align-items-center">
                            <span>Cari:</span>
                            <input type="search" id="search-table" class="form-control form-control-sm ms-1" placeholder="Ketik untuk mencari...">
                        </label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Reservasi</th>
                                <th>Nama Pelanggan</th>
                                <th>Layanan</th>
                                <th>Tanggal</th>
                                <th>Status Pembayaran</th>
                                <th>Total Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <tr><td colspan="8" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <span id="info-text" class="text-muted mb-2 mb-md-0" style="font-size: 0.875rem;">
                        Menampilkan 0 dari 0 data
                    </span>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="pagination-list"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pembatalan Reservasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-detail-content">
                <!-- Content will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tbody = document.getElementById('table-body');
    const search = document.getElementById('search-table');
    const info = document.getElementById('info-text');
    const perSel = document.getElementById('show-entries');
    const pager = document.getElementById('pagination-list');

    let allData = [];
    let viewData = [];
    let state = { page: 1, perPage: parseInt(perSel.value, 10) };

    const approveUrl = '{{ route("admin.konfirmasi-pembatalan.approve", ["id" => "_ID_"]) }}';
    const rejectUrl = '{{ route("admin.konfirmasi-pembatalan.reject", ["id" => "_ID_"]) }}';

    function paginate(arr, p, n) {
        const total = arr.length, totalPages = Math.max(1, Math.ceil(total / n));
        const page = Math.min(Math.max(1, p), totalPages);
        const start = (page - 1) * n, end = start + n;
        return { rows: arr.slice(start, end), total, totalPages, page };
    }

    function renderPager(ul, totalPages, current) {
        const li = [];
        const add = (lbl, pg, dis = false, act = false) => li.push(
            `<li class="page-item ${dis ? 'disabled' : ''} ${act ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${pg}">${lbl}</a>
            </li>`
        );
        add('Previous', current - 1, current === 1);
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) add(i, i, false, i === current);
        } else {
            add(1, 1, false, current === 1);
            if (current > 4) li.push(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
            const s = Math.max(2, current - 1), e = Math.min(totalPages - 1, current + 1);
            for (let i = s; i <= e; i++) add(i, i, false, i === current);
            if (current < totalPages - 3) li.push(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
            add(totalPages, totalPages, false, current === totalPages);
        }
        add('Next', current + 1, current === totalPages || totalPages === 0);
        ul.innerHTML = li.join('');
    }

    function getBadgeClass(status) {
        switch(status) {
            case 'bayar_lunas': return 'badge-lunas';
            case 'bayar_dp': return 'badge-dp';
            default: return 'badge-belum';
        }
    }

    function getBadgeText(status) {
        switch(status) {
            case 'bayar_lunas': return 'bayar_lunas';
            case 'bayar_dp': return 'bayar_dp';
            default: return 'Belum Bayar';
        }
    }

    function draw() {
        const term = (search.value || '').toLowerCase();
        viewData = allData.filter(item => {
            return item.nama_pelanggan.toLowerCase().includes(term) ||
                   item.id_reservasi.toString().includes(term) ||
                   item.nama_layanan.toLowerCase().includes(term);
        });

        const { rows, total, totalPages, page } = paginate(viewData, state.page, state.perPage);

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">Data tidak ditemukan</td></tr>';
        } else {
            tbody.innerHTML = rows.map((item, idx) => {
                const badgeClass = getBadgeClass(item.status_pembayaran);
                const badgeText = getBadgeText(item.status_pembayaran);
                const layananText = item.total_layanan_lain > 0 
                    ? `${item.nama_layanan} +${item.total_layanan_lain} lainnya`
                    : item.nama_layanan;

                return `
                <tr>
                    <td>${(idx + 1) + ((page - 1) * state.perPage)}</td>
                    <td>#${item.id_reservasi}</td>
                    <td>${item.nama_pelanggan}</td>
                    <td>${layananText}</td>
                    <td>${item.tanggal_reservasi} ${item.waktu_reservasi}</td>
                    <td><span class="badge ${badgeClass}">${badgeText}</span></td>
                    <td>Rp ${item.jumlah_dibayar.toLocaleString('id-ID')}</td>
                    <td>
                        <button class="btn btn-outline-info btn-aksi" onclick="showDetail(${item.id_reservasi})">
                            Detail
                        </button>
                        ${item.status_pembayaran === 'bayar_dp' ? 
                            `<button class="btn btn-outline-success btn-aksi ms-1" onclick="handleApprove(${item.id_reservasi}, 'Down Payment')">
                                Setujui
                            </button>` : 
                            `<button class="btn btn-outline-warning btn-aksi ms-1" onclick="handleChat('${item.no_hp}', ${item.id_reservasi})">
                                Chat
                            </button>`
                        }
                        <button class="btn btn-outline-danger btn-aksi ms-1" onclick="handleReject(${item.id_reservasi})">
                            Tolak
                        </button>
                    </td>
                </tr>`;
            }).join('');
        }

        const from = total ? ((page - 1) * state.perPage) + 1 : 0;
        const to = ((page - 1) * state.perPage) + rows.length;
        info.textContent = `Menampilkan ${from}–${to} dari ${total} data`;
        renderPager(pager, totalPages, page);
    }

    function fetchData() {
        fetch("{{ route('admin.konfirmasi-pembatalan.ajax') }}")
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    allData = res.data || [];
                    state.page = 1;
                    draw();
                } else {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center">Gagal memuat data</td></tr>`;
                }
            })
            .catch(() => {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center">Terjadi kesalahan server</td></tr>`;
            });
    }

    window.showDetail = function(id) {
        const item = allData.find(d => d.id_reservasi === id);
        if (!item) return;

        const whatsappLink = `https://wa.me/${item.no_hp.replace(/[^0-9]/g, '')}`;
        const statusBadge = getBadgeText(item.status_pembayaran);
        const statusClass = getBadgeClass(item.status_pembayaran);

        const content = `
            <div class="row">
                <div class="col-md-6">
                    <p><span class="fw-bold">ID Reservasi:</span> #${item.id_reservasi}</p>
                    <p><span class="fw-bold">Nama Pelanggan:</span> ${item.nama_pelanggan}</p>
                    <p><span class="fw-bold">No. HP:</span> ${item.no_hp}</p>
                </div>
                <div class="col-md-6">
                    <p><span class="fw-bold">Layanan:</span> ${item.nama_layanan} ${item.total_layanan_lain > 0 ? `+${item.total_layanan_lain} lainnya` : ''}</p>
                    <p><span class="fw-bold">Tanggal:</span> ${item.tanggal_reservasi} ${item.waktu_reservasi}</p>
                    <p><span class="fw-bold">Status Pembayaran:</span> <span class="badge ${statusClass}">${statusBadge}</span></p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p><span class="fw-bold">Total Harga:</span> Rp ${item.total_harga.toLocaleString('id-ID')}</p>
                    <p><span class="fw-bold">Jumlah Dibayar:</span> Rp ${item.jumlah_dibayar.toLocaleString('id-ID')}</p>
                </div>
                <div class="col-md-6">
                    <p><span class="fw-bold">Tanggal Pembayaran:</span> ${item.tanggal_pembayaran}</p>
                    <p><span class="fw-bold">Permintaan Pembatalan:</span> ${item.updated_at}</p>
                </div>
            </div>
            ${item.catatan !== '-' ? `
            <hr>
            <div class="row">
                <div class="col-12">
                    <p><span class="fw-bold">Catatan:</span></p>
                    <p>${item.catatan}</p>
                </div>
            </div>` : ''}
            <hr>
            <div class="d-flex gap-2 justify-content-end">
                <a href="${whatsappLink}" target="_blank" class="btn btn-success btn-sm">
                    <i class="bi bi-whatsapp"></i> Chat WhatsApp
                </a>
                ${item.status_pembayaran === 'bayar_lunas' ? 
                    `<button class="btn btn-primary btn-sm" onclick="handleApprove(${item.id_reservasi}, 'bayar_lunas')">
                        Konfirmasi Pembatalan
                    </button>` : 
                    `<button class="btn btn-primary btn-sm" onclick="handleApprove(${item.id_reservasi}, 'Down Payment')">
                        Setujui Pembatalan
                    </button>`
                }
                <button class="btn btn-danger btn-sm" onclick="handleReject(${item.id_reservasi})">
                    Tolak Pembatalan
                </button>
            </div>
        `;

        document.getElementById('modal-detail-content').innerHTML = content;
        new bootstrap.Modal(document.getElementById('detailModal')).show();
    };

    window.handleChat = function(noHp, idReservasi) {
        const cleanNumber = noHp.replace(/[^0-9]/g, '');
        const message = encodeURIComponent(`Halo, saya ingin konfirmasi pembatalan reservasi #${idReservasi}. Apakah Anda setuju dengan pembatalan ini?`);
        window.open(`https://wa.me/${cleanNumber}?text=${message}`, '_blank');
    };

    window.handleApprove = function(id, paymentStatus) {
        const item = allData.find(d => d.id_reservasi === id);
        if (!item) return;

        let title, text, html;
        
        if (paymentStatus === 'bayar_dp') {
            title = 'Setujui pembatalan ini?';
            html = `
                <div class="text-start">
                    <p class="mb-2"><strong>Ketentuan Pembatalan DP:</strong></p>
                    <ul class="text-danger">
                        <li>Uang DP <strong>TIDAK akan dikembalikan</strong> ke pelanggan</li>
                        <li>Jumlah DP: <strong>Rp ${item.jumlah_dibayar.toLocaleString('id-ID')}</strong></li>
                        <li>Status akan berubah menjadi: <strong>Pembatalan DP</strong></li>
                    </ul>
                </div>
            `;
        } else if (paymentStatus === 'bayar_lunas') {
            const pengembalian = item.jumlah_dibayar / 2;
            title = 'Konfirmasi pembatalan setelah chat pelanggan?';
            html = `
                <div class="text-start">
                    <p class="mb-2"><strong>Ketentuan Pembatalan Lunas:</strong></p>
                    <ul class="text-warning">
                        <li>Pastikan Anda <strong>sudah chat dan konfirmasi</strong> dengan pelanggan</li>
                        <li>Total Pembayaran: <strong>Rp ${item.jumlah_dibayar.toLocaleString('id-ID')}</strong></li>
                        <li>Yang dikembalikan: <strong>Rp ${pengembalian.toLocaleString('id-ID')} (50%)</strong></li>
                        <li>Dipotong: <strong>Rp ${pengembalian.toLocaleString('id-ID')} (50%)</strong></li>
                        <li>Status akan berubah menjadi: <strong>Pembatalan Lunas</strong></li>
                    </ul>
                </div>
            `;
        } else {
            title = 'Setujui pembatalan ini?';
            text = 'Tidak ada pembayaran yang perlu dikembalikan.';
        }

        Swal.fire({
            title: title,
            html: html || text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Setujui!',
            cancelButtonText: 'Batal',
            width: '600px'
        }).then((result) => {
            if (result.isConfirmed) {
                const url = approveUrl.replace('_ID_', id);
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: data.message,
                            timer: 5000
                        });
                        fetchData();
                        bootstrap.Modal.getInstance(document.getElementById('detailModal'))?.hide();
                    } else {
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error!', 'Terjadi kesalahan server', 'error');
                });
            }
        });
    };

    window.handleReject = function(id) {
        Swal.fire({
            title: 'Tolak pembatalan ini?',
            text: 'Reservasi akan dikembalikan ke status pending',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Tolak!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const url = rejectUrl.replace('_ID_', id);
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil!', data.message, 'success');
                        fetchData();
                        bootstrap.Modal.getInstance(document.getElementById('detailModal'))?.hide();
                    } else {
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error!', 'Terjadi kesalahan server', 'error');
                });
            }
        });
    };

    perSel.addEventListener('change', () => {
        state.perPage = parseInt(perSel.value, 10) || 10;
        state.page = 1;
        draw();
    });
    
    search.addEventListener('keyup', () => {
        state.page = 1;
        draw();
    });
    
    pager.addEventListener('click', (e) => {
        const a = e.target.closest('a[data-page]');
        if (!a) return;
        e.preventDefault();
        const t = parseInt(a.dataset.page, 10);
        const tp = Math.max(1, Math.ceil(viewData.length / state.perPage));
        if (t >= 1 && t <= tp) {
            state.page = t;
            draw();
        }
    });

    const urlParams = new URLSearchParams(window.location.search);
    const urlSearch = urlParams.get('search');
    if (urlSearch) {
        search.value = urlSearch;
    }

    fetchData();
});

</script>

@if (session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Sukses!',
    text: '{{ session('success') }}',
    timer: 3000,
    showConfirmButton: false
});
</script>
@endif
@if (session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '{{ session('error') }}'
});
</script>
@endif
@endsection