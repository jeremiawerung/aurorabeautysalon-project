@extends('layouts.app')

@section('title', 'Master Data Pelanggan - Aurora')

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
    .btn-outline-warning.btn-aksi:hover {
        color: #fff !important;
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
    }
    .btn-tambah-pink:hover {
        background-color: #dc3545;
        color: #fff;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">

        {{-- ============================================= --}}
        {{-- KARTU MASTER DATA PELANGGAN --}}
        {{-- ============================================= --}}
        <div class="card mb-4">
            <div class="card-header d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center">
                <h5 class="mb-2 mb-md-0">Master Data Pelanggan</h5>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('pelanggan.create') }}" class="btn btn-tambah-pink btn-sm">Tambah</a>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-3 g-2 align-items-center table-controls">
                    <div class="col-12 col-md-auto">
                        <label for="show-entries-pelanggan" class="col-form-label-sm">
                            Menampilkan
                            <select id="show-entries-pelanggan" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                            </select>
                            Baris
                        </label>
                    </div>
                    <div class="col-12 col-md-auto ms-md-auto">
                        <label for="search-pelanggan" class="col-form-label-sm">
                            Cari:
                            <input type="search" id="search-pelanggan" class="form-control form-control-sm ms-1">
                        </label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>ID Pelanggan</th>
                                <th>Email</th>
                                <th>No. Telepon</th>
                                <th>Tanggal Daftar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body-pelanggan">
                            <tr>
                                <td colspan="7" class="text-center">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <span id="info-pelanggan" class="text-muted mb-2 mb-md-0" style="font-size: 0.875rem;">
                        Menampilkan 0 dari 0 data
                    </span>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
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

    let allPelangganData = [];
    let currentPage = 1;
    let rowsPerPage = 10;

    const editUrlTemplate = '{{ route("pelanggan.edit", ["pelanggan" => "_PLACEHOLDER_"]) }}';
    const destroyUrlTemplate = '{{ route("pelanggan.destroy", ["pelanggan" => "_PLACEHOLDER_"]) }}';

    const tbodyPelanggan = document.getElementById('table-body-pelanggan');
    const searchPelanggan = document.getElementById('search-pelanggan');
    const infoPelanggan = document.getElementById('info-pelanggan');
    const showEntriesSelect = document.getElementById('show-entries-pelanggan');
    const paginationContainer = document.querySelector('.pagination');

    // === Fetch Data Pelanggan ===
    async function fetchPelangganData() {
        const url = '{{ route("pelanggan.ajax") }}';
        try {
            const response = await fetch(url);
            const result = await response.json();
            if (result.success && result.data) {
                allPelangganData = result.data;
                renderPelanggan();
            } else {
                tbodyPelanggan.innerHTML = `<tr><td colspan="7" class="text-center">${result.message || 'Gagal memuat data.'}</td></tr>`;
                infoPelanggan.textContent = 'Menampilkan 0 dari 0 data';
            }
        } catch (error) {
            console.error("Fetch error:", error);
            tbodyPelanggan.innerHTML = `<tr><td colspan="7" class="text-center">Gagal mengambil data. Cek koneksi atau console.</td></tr>`;
            infoPelanggan.textContent = 'Menampilkan 0 dari 0 data';
        }
    }

    // === Render Data ke Tabel ===
    function renderPelanggan(filteredData = allPelangganData) {
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginatedData = filteredData.slice(start, end);

        tbodyPelanggan.innerHTML = '';
        if (paginatedData.length === 0) {
            tbodyPelanggan.innerHTML = '<tr><td colspan="7" class="text-center">Data tidak ditemukan</td></tr>';
            infoPelanggan.textContent = 'Menampilkan 0 dari ' + filteredData.length + ' data';
            paginationContainer.innerHTML = '';
            return;
        }

        let htmlString = '';
        paginatedData.forEach((item, index) => {
            let statusHtml = (item.formatted_status === 'Aktif')
                ? '<span class="text-success fw-bold">Aktif</span>'
                : '<span class="text-secondary fw-bold">Non-aktif</span>';

            const editUrl = editUrlTemplate.replace('_PLACEHOLDER_', item.id_pelanggan);

            htmlString += `
                <tr>
                    <td>${start + index + 1}</td>
                    <td>${item.nama}</td>
                    <td>${item.id_pelanggan}</td>
                    <td>${item.email}</td>
                    <td>${item.formatted_nomor || item.nomor_telepon}</td>
                    <td>${item.formatted_tanggal_daftar || '-'}</td>
                    <td>${statusHtml}</td>
                    <td>
                        <a href="${editUrl}" class="btn btn-outline-warning btn-aksi">Edit</a>
                        <button class="btn btn-outline-danger btn-aksi ms-1" onclick="handleDelete(${item.id_pelanggan})">Hapus</button>
                    </td>
                </tr>
            `;
        });
        tbodyPelanggan.innerHTML = htmlString;
        infoPelanggan.textContent = `Menampilkan ${Math.min(end, filteredData.length)} dari ${filteredData.length} data`;

        renderPagination(filteredData);
    }

    // === Render Pagination ===
    function renderPagination(filteredData = allPelangganData) {
        const totalPages = Math.ceil(filteredData.length / rowsPerPage);
        let html = '';

        html += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
            </li>
        `;

        for (let i = 1; i <= totalPages; i++) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        html += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>
        `;

        paginationContainer.innerHTML = html;

        // Event listener untuk pagination
        paginationContainer.querySelectorAll('.page-link').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const newPage = parseInt(this.dataset.page);
                if (newPage >= 1 && newPage <= totalPages) {
                    currentPage = newPage;
                    renderPelanggan(getFilteredData());
                }
            });
        });
    }

    // === Filter Data (Search) ===
    function getFilteredData() {
        const searchTerm = searchPelanggan.value.toLowerCase();
        return allPelangganData.filter(item => {
            const nama = (item.nama || '').toLowerCase();
            const email = (item.email || '').toLowerCase();
            const telepon = (item.nomor_telepon || item.formatted_nomor || '').toLowerCase();
            const status = (item.status_pelanggan || '').toLowerCase();
            const tanggal = (item.formatted_tanggal_daftar || '').toLowerCase();

            return (
                nama.includes(searchTerm) ||
                email.includes(searchTerm) ||
                telepon.includes(searchTerm) ||
                status.includes(searchTerm) ||
                tanggal.includes(searchTerm)
            );
        });
    }

    searchPelanggan.addEventListener('keyup', function() {
        currentPage = 1;
        renderPelanggan(getFilteredData());
    });

    showEntriesSelect.addEventListener('change', function() {
        rowsPerPage = parseInt(this.value);
        currentPage = 1;
        renderPelanggan(getFilteredData());
    });

    // === Fungsi Hapus Data ===
    function handleDelete(id) {
        Swal.fire({
            title: 'Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const actionUrl = destroyUrlTemplate.replace('_PLACEHOLDER_', id);
                let form = document.createElement('form');
                form.action = actionUrl;
                form.method = 'POST';
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        })
    }
    window.handleDelete = handleDelete;

    // === Load Data Awal ===
    fetchPelangganData();
});
</script>

{{-- SweetAlert Session --}}
@if (session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Sukses!', text: '{{ session('success') }}', timer: 3000, showConfirmButton: false });
</script>
@endif
@if (session('error'))
<script>Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ session('error') }}' });</script>
@endif
@if (session('warning'))
<script>Swal.fire({ icon: 'warning', title: 'Peringatan!', text: '{{ session('warning') }}' });</script>
@endif
@if (session('info'))
<script>Swal.fire({ icon: 'info', title: 'Informasi', text: '{{ session('info') }}' });</script>
@endif
@endsection

