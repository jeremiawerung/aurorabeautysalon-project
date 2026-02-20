@extends('layouts.app')

@section('title', 'Daftar Jadwal Pelanggan - Aurora')

@section('content')
<style>
    .card { border: 1px solid #f1b8d6 !important; border-radius: 10px; height: 100%; overflow: hidden; }
    .card-header { background-color: #fff; border-bottom: 1px solid #f1b8d6 !important; font-weight: 600; border-radius: 25rem; }
    table th, table td { font-size: 0.8rem; vertical-align: middle; white-space: nowrap; padding-left: .5rem; padding-right: .5rem; }
    .content-wrapper { padding: 20px; }
    .table-controls .form-select, .table-controls .form-control { font-size: .875rem; }
</style>

<div class="content-wrapper">
  <div class="container-fluid">

    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Jadwal Pelanggan</h5>
        <a href="{{ route('booking.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Booking
        </a>
      </div>

      <div class="card-body">
        {{-- Filters --}}
        <div class="row mb-3 g-2">
            <div class="col-md-3">
                <label class="form-label small">Cari (Nama/ID)</label>
                <input type="text" id="filter-search" class="form-control form-control-sm" placeholder="Nama Pelanggan / ID Reservasi">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="all">Semua Status</option>
                    <option value="pending">Menunggu Pembayaran</option>
                    <option value="proses">Sedang Berjalan</option>
                    <option value="selesai">Selesai</option>
                    <option value="dibatalkan">Dibatalkan</option>
                    <option value="menunggu_konfirmasi_pembatalan">Menunggu Konfirmasi Batal</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Dari Tanggal</label>
                <input type="date" id="filter-start" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Sampai Tanggal</label>
                <input type="date" id="filter-end" class="form-control form-control-sm">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button id="btn-filter" class="btn btn-primary btn-sm w-100">Terapkan Filter</button>
            </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>ID Reservasi</th>
                <th>Pelanggan</th>
                <th>Layanan</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Status Pembayaran</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="table-body-booking">
              <tr><td colspan="8" class="text-center">Memuat data...</td></tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <span id="info-booking" class="text-muted" style="font-size:.875rem;">Menampilkan 0 dari 0 data</span>
          <div class="d-flex align-items-center gap-2">
              <select id="per-page" class="form-select form-select-sm" style="width: auto;">
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
              </select>
              <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0" id="pagination-booking"></ul>
              </nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- MODAL UPDATE STATUS (Copied from Booking/Payment) --}}
<div class="modal fade" id="modalUpdateStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status Reservasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formUpdateStatus">
                    <input type="hidden" id="update_id_reservasi" name="id_reservasi">
                    <div class="mb-3">
                        <label class="form-label">Pilih Status Baru:</label>
                        <select class="form-select" name="status" id="select_status_reservasi">
                            <option value="pending">Pending</option>
                            <option value="proses">Sedang Berjalan (Proses)</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                    <div class="alert alert-info py-2" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> Pastikan pembayaran lunas sebelum ubah ke Selesai.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitUpdateStatus()">Simpan</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // State
    let dataBooking = [];
    let viewBooking = [];
    let state = { page: 1, perPage: 10 };

    // Elements
    const tbody = document.getElementById('table-body-booking');
    const info = document.getElementById('info-booking');
    const pager = document.getElementById('pagination-booking');
    const perPageSelect = document.getElementById('per-page');
    
    // Filters
    const filterSearch = document.getElementById('filter-search');
    const filterStatus = document.getElementById('filter-status');
    const filterStart = document.getElementById('filter-start');
    const filterEnd = document.getElementById('filter-end');
    const btnFilter = document.getElementById('btn-filter');

    // Helper Pagination
    function renderPager(ul, totalPages, currentPage) {
        let li = '';
        const add = (label, page, disabled=false, active=false) => {
            li += `<li class="page-item ${disabled?'disabled':''} ${active?'active':''}">
                <a class="page-link" href="#" data-page="${page}">${label}</a></li>`;
        };
        add('Prev', currentPage-1, currentPage===1);
        
        if (totalPages <= 7) {
            for (let p=1; p<=totalPages; p++) add(p, p, false, p===currentPage);
        } else {
            add(1,1,false,currentPage===1);
            if (currentPage > 4) li += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            
            let s = Math.max(2, currentPage-1);
            let e = Math.min(totalPages-1, currentPage+1);
            for (let p=s; p<=e; p++) add(p, p, false, p===currentPage);
            
            if (currentPage < totalPages-3) li += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            add(totalPages, totalPages, false, currentPage===totalPages);
        }
        
        add('Next', currentPage+1, currentPage===totalPages || totalPages===0);
        ul.innerHTML = li;
    }

    function paginate(data, page, perPage) {
        const total = data.length;
        const totalPages = Math.max(1, Math.ceil(total / perPage));
        const safePage = Math.min(Math.max(1, page), totalPages);
        const start = (safePage - 1) * perPage;
        return { rows: data.slice(start, start + perPage), total, totalPages, page: safePage };
    }

    function drawTable() {
        const { rows, total, totalPages, page } = paginate(viewBooking, state.page, state.perPage);
        state.page = page;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">Data tidak ditemukan</td></tr>';
        } else {
            tbody.innerHTML = rows.map((item, idx) => {
                const no = (page - 1) * state.perPage + idx + 1;
                return `
                    <tr>
                        <td>${no}</td>
                        <td>${item.id_reservasi}</td>
                        <td>${item.pelanggan_nama}</td>
                        <td class="text-wrap" style="max-width: 200px;">${item.layanan_nama}</td>
                        <td>${item.tanggal}</td>
                        <td>${item.jam}</td>
                        <td><span class="badge ${item.status_class}">${item.formatted_status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="openUpdateStatusModal(${item.id_reservasi}, '${item.status_reservasi}')">
                                <i class="fas fa-edit"></i> Ubah Status
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        info.textContent = `Menampilkan ${total ? ((page-1)*state.perPage)+1 : 0}–${((page-1)*state.perPage) + rows.length} dari ${total} data`;
        renderPager(pager, totalPages, page);
    }

    function loadData() {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">Memuat data...</td></tr>';
        
        const params = new URLSearchParams({
            search: filterSearch.value,
            status: filterStatus.value,
            start_date: filterStart.value,
            end_date: filterEnd.value
        });

        fetch(`{{ route('booking.list.data') }}?${params.toString()}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    dataBooking = res.data;
                    viewBooking = [...dataBooking];
                    state.page = 1;
                    drawTable();
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Terjadi kesalahan sistem</td></tr>';
            });
    }

    // Event Listeners
    btnFilter.addEventListener('click', loadData);
    
    perPageSelect.addEventListener('change', () => {
        state.perPage = parseInt(perPageSelect.value);
        state.page = 1;
        drawTable();
    });

    pager.addEventListener('click', (e) => {
        e.preventDefault();
        const a = e.target.closest('a');
        if (!a || a.parentElement.classList.contains('disabled')) return;
        state.page = parseInt(a.dataset.page);
        drawTable();
    });

    // Initial Load
    loadData();

    // --- MODAL ACTION (Update Status) ---
    window.openUpdateStatusModal = function(id, currentStatus) {
        document.getElementById('update_id_reservasi').value = id;
        const select = document.getElementById('select_status_reservasi');
        
        let val = String(currentStatus).toLowerCase();
        // Mapping simple text to select values
        if (val.includes('menunggu') || val.includes('pending')) val = 'pending';
        else if (val.includes('proses') || val.includes('berjalan')) val = 'proses';
        else if (val.includes('selesai')) val = 'selesai';
        else if (val.includes('dibatalkan')) val = 'dibatalkan';
        else if (val.includes('konfirmasi')) val = 'menunggu_konfirmasi_pembatalan';
        
        select.value = val;
        new bootstrap.Modal(document.getElementById('modalUpdateStatus')).show();
    };

    window.submitUpdateStatus = function() {
        const id = document.getElementById('update_id_reservasi').value;
        const status = document.getElementById('select_status_reservasi').value;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('{{ route("reservasi.update_status") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id_reservasi: id, status: status })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Berhasil', data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('modalUpdateStatus')).hide();
                loadData(); // Reload table
            } else {
                Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Gagal menghubungi server', 'error'));
    };
});
</script>
@endsection
