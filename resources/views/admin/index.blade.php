@extends('layouts.app')

@section('title', 'Daftar Admin - Aurora')

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
    .card-body {
        padding: 1.25rem;
    }
    .content-wrapper { 
        padding: 20px; 
    }

    table th, table td {
        font-size: 0.8rem;
        vertical-align: middle;
        white-space: nowrap;
        padding: 0.75rem 0.5rem;
    }

    .btn-tambah-pink {
        background-color: #fff;
        border-width: 2px;
        border-style: solid;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.375rem 1.25rem;
        font-size: 0.875rem;
        border-color: #dc3545;
        color: #dc3545;
        transition: all 0.2s ease;
    }
    .btn-tambah-pink:hover { 
        background-color: #dc3545; 
        color: #fff; 
    }

    .btn-aksi {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-weight: 500;
    }
    .btn-outline-warning.btn-aksi:hover { 
        color: #fff !important; 
    }
    .btn-outline-danger.btn-aksi:hover { 
        color: #fff !important; 
    }

    .badge-status {
        font-size: 0.75rem;
        padding: 0.35em 0.6em;
        border-radius: 999px;
        font-weight: 500;
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
        .btn-aksi {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">

        <div class="card mb-4">
            <div class="card-header d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center">
                <h5 class="mb-2 mb-md-0">
                    <i class="bi bi-person-gear me-2"></i>Daftar Admin
                </h5>
                <a href="{{ route('admin.register') }}" class="btn btn-tambah-pink btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Admin Baru
                </a>
            </div>

            <div class="card-body">
                <!-- Table Controls -->
                <div class="row mb-3 g-2 align-items-center table-controls">
                    <div class="col-12 col-md-auto">
                        <label for="show-entries-admin" class="col-form-label-sm">
                            Menampilkan
                            <select id="show-entries-admin" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            Baris
                        </label>
                    </div>
                    <div class="col-12 col-md-auto ms-md-auto">
                        <label for="search-admin" class="col-form-label-sm d-flex align-items-center">
                            <span>Cari:</span>
                            <input type="search" id="search-admin" class="form-control form-control-sm ms-1" placeholder="Ketik untuk mencari...">
                        </label>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 150px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body-admin">
                            <tr>
                                <td colspan="5" class="text-center">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Info & Controls -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <span id="info-admin" class="text-muted mb-2 mb-md-0" style="font-size: 0.875rem;">
                        Menampilkan 0 dari 0 data
                    </span>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="pagination-admin"></ul>
                    </nav>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tbody = document.getElementById('table-body-admin');
    const search = document.getElementById('search-admin');
    const info   = document.getElementById('info-admin');
    const perSel = document.getElementById('show-entries-admin');
    const pager  = document.getElementById('pagination-admin');

    let dataAdmin = [];
    let viewData = [];
    let state = { page: 1, perPage: parseInt(perSel.value, 10) };

    // ✅ Sesuaikan dengan route structure Anda
    const editUrlBase = '{{ url("/admin/edit") }}';
    const destroyUrlBase = '{{ url("/admin/delete") }}';

    function paginate(arr, p, n) {
        const total = arr.length;
        const totalPages = Math.max(1, Math.ceil(total / n));
        const page = Math.min(Math.max(1, p), totalPages);
        const start = (page - 1) * n;
        const end = start + n;
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
            for (let i = 1; i <= totalPages; i++) {
                add(i, i, false, i === current);
            }
        } else {
            add(1, 1, false, current === 1);
            if (current > 4) {
                li.push(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
            }
            const s = Math.max(2, current - 1);
            const e = Math.min(totalPages - 1, current + 1);
            for (let i = s; i <= e; i++) {
                add(i, i, false, i === current);
            }
            if (current < totalPages - 3) {
                li.push(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
            }
            add(totalPages, totalPages, false, current === totalPages);
        }
        
        add('Next', current + 1, current === totalPages || totalPages === 0);
        ul.innerHTML = li.join('');
    }

    function draw() {
        const term = (search.value || '').toLowerCase();
        viewData = dataAdmin.filter(item => {
            const nama = item.nama || '';
            const email = item.email || '';
            const status = item.status_admin || '';
            return nama.toLowerCase().includes(term)
                || email.toLowerCase().includes(term)
                || status.toLowerCase().includes(term);
        });

        const { rows, total, totalPages, page } = paginate(viewData, state.page, state.perPage);

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Data tidak ditemukan</td></tr>';
        } else {
            tbody.innerHTML = rows.map((item, idx) => {
                // ✅ Build URL dengan base + id
                const editUrl = `${editUrlBase}/${item.id_admin}`;
                const statusBadge = item.status_admin === 'aktif'
                    ? '<span class="badge bg-success badge-status">Aktif</span>'
                    : '<span class="badge bg-secondary badge-status">Non-Aktif</span>';

                return `
                <tr>
                    <td>${(idx + 1) + ((page - 1) * state.perPage)}</td>
                    <td>${item.nama || '-'}</td>
                    <td>${item.email || '-'}</td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        <a href="${editUrl}" class="btn btn-outline-warning btn-aksi">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <button class="btn btn-outline-danger btn-aksi ms-1" onclick="handleDelete(${item.id_admin})">
                            <i class="bi bi-trash"></i> Hapus
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

    function fetchAdminData() {
        fetch("{{ route('admin.ajax') }}")
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    dataAdmin = res.data || [];
                    state.page = 1;
                    draw();
                } else {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center">Gagal memuat data</td></tr>`;
                }
            })
            .catch(() => {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center">Terjadi kesalahan server</td></tr>`;
            });
    }

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

    window.handleDelete = function(id) {
        Swal.fire({
            title: 'Hapus admin?',
            text: "Tindakan ini tidak dapat dibatalkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // ✅ Build URL dengan base + id
                const url = `${destroyUrlBase}/${id}`;
                const form = document.createElement('form');
                form.action = url;
                form.method = 'POST';
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    };

    // Fetch data saat halaman dimuat
    fetchAdminData();
});
</script>

@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Sukses!',
    text: '{{ session('success') }}',
    timer: 3000,
    showConfirmButton: false,
    confirmButtonColor: '#e91e63'
});
</script>
@endif

@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '{{ session('error') }}',
    confirmButtonColor: '#e91e63'
});
</script>
@endif
@endsection