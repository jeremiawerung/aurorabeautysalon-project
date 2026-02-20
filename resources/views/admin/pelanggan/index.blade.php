@extends('layouts.app')

@section('title', 'Data Pelanggan - Aurora')

@section('content')
<style>
    .card { border: 1px solid #f1b8d6 !important; border-radius: 10px; height: 100%; }
    .card-header { background-color: #fff; border-bottom: 1px solid #f1b8d6 !important; font-weight: 600; border-radius: 25rem; }
    table th, table td { font-size: 0.8rem; vertical-align: middle; white-space: nowrap; padding-left: .5rem; padding-right: .5rem; }
    .content-wrapper { padding: 20px; }
    .btn-import { background-color:#fff; border:2px solid #0d6efd!important; color:#0d6efd; font-weight:600; border-radius:.375rem; }
    .btn-import:hover { background:#0d6efd; color:#fff; }
    .btn-export { background-color:#fff; border:2px solid #d13a8a!important; color:#d13a8a; font-weight:600; border-radius:.5rem; }
    .btn-export:hover { background:#d13a8a; color:#fff; }
    .table-controls .form-select, .table-controls .form-control { font-size: .875rem; }
</style>

<div class="content-wrapper">
  <div class="container-fluid">

    {{-- =======================
         KARTU DATA PELANGGAN
       ======================= --}}
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Pelanggan</h5>
        <div>
          <button id="btn-import-pelanggan" class="btn btn-import btn-sm">Import</button>
          <button id="btn-export-pelanggan" class="btn btn-export btn-sm ms-2">Export</button>
        </div>
      </div>

      <div class="card-body">
        <div class="row mb-3 g-2 align-items-center table-controls">
          <div class="col-md-auto">
            <label class="col-form-label-sm">
              Menampilkan
              <select id="show-entries-pelanggan" class="form-select form-select-sm d-inline-block w-auto">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
              item
            </label>
          </div>

          <div class="col-md-auto ms-auto">
            <label class="col-form-label-sm">
              Cari:
              <input type="search" id="search-pelanggan" class="form-control form-control-sm d-inline-block w-auto ms-1" placeholder="Ketik untuk mencari...">
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
                <th>No Telepon</th>
                <th>Email</th>
                <th>Status Pelanggan</th>
                <th>Tanggal Daftar</th>
                <th>Detail</th>
              </tr>
            </thead>
            <tbody id="table-body-pelanggan">
              <tr><td colspan="8" class="text-center">Memuat data...</td></tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <span id="info-pelanggan" class="text-muted" style="font-size:.875rem;">Menampilkan 0 dari 0 data</span>
          <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0" id="pagination-pelanggan"></ul>
          </nav>
        </div>
      </div>
    </div>

    {{-- ===========================================
         KARTU PEMBAYARAN (tabel bawah pelanggan)
       =========================================== --}}
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Pembayaran (Transaksi)</h5>
        <div>
          <button id="btn-import-transaksi" class="btn btn-import btn-sm">Import</button>
          <button id="btn-export-transaksi" class="btn btn-export btn-sm ms-2">Export</button>
        </div>
      </div>

      <div class="card-body">
        <div class="row mb-3 g-2 align-items-center table-controls">
          <div class="col-md-auto">
            <label class="col-form-label-sm">
              Menampilkan
              <select id="show-entries-transaksi" class="form-select form-select-sm d-inline-block w-auto">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
              item
            </label>
          </div>

          <div class="col-md-auto ms-auto">
            <label class="col-form-label-sm">
              Cari:
              <input type="search" id="search-transaksi" class="form-control form-control-sm d-inline-block w-auto ms-1" placeholder="Ketik untuk mencari...">
            </label>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>Pelanggan</th>
                <th>ID Reservasi</th>
                <th>Jumlah</th>
                <th>Diskon</th>
                <th>Biaya Tambahan</th> <!-- BARU -->
                <th>Total</th>
                <th>Status</th>
                <th>Metode</th>
                <th>Tanggal</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody id="table-body-transaksi">
              <tr><td colspan="11" class="text-center">Memuat data...</td></tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <span id="info-transaksi" class="text-muted" style="font-size:.875rem;">Menampilkan 0 dari 0 data</span>
          <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0" id="pagination-transaksi"></ul>
          </nav>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Hidden Forms for Import/Export --}}
<form id="form-import-pelanggan" action="{{ route('pelanggan.import') }}" method="POST" enctype="multipart/form-data" style="display:none;">
    @csrf
    <input type="file" id="input-import-pelanggan" name="file" accept=".xlsx,.xls,.csv">
</form>
<form id="form-export-pelanggan" action="{{ route('pelanggan.export') }}" method="GET" style="display:none;"></form>

<form id="form-import-transaksi" action="{{ route('pembayaran.import') }}" method="POST" enctype="multipart/form-data" style="display:none;">
    @csrf
    <input type="file" id="input-import-transaksi" name="file" accept=".xlsx,.xls,.csv">
</form>
<form id="form-export-transaksi" action="{{ route('pembayaran.export') }}" method="GET" style="display:none;"></form>

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="transactionDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Transaksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Pelanggan:</strong> <span id="modalTrxPelangganNama"></span></p>
        <p><strong>No. Telp:</strong> <span id="modalTrxPelangganTelp"></span></p>
        <p><strong>Layanan:</strong> <span id="modalTrxLayanan"></span></p>
        <p><strong>Catatan:</strong> <span id="modalTrxCatatan"></span></p>
        <hr>
        <p><strong>Total Reservasi:</strong> <span id="modalTrxTotalReservasi"></span></p>
        <p><strong>Total Paid:</strong> <span id="modalTrxTotalPaid"></span></p>
        <p><strong>Sisa:</strong> <span id="modalTrxSisa" class="text-danger fw-bold"></span></p>
        <hr>
        <h6>Riwayat Pembayaran</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                    </tr>
                </thead>
                <tbody id="modalTrxHistoryBody"></tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="modalUpdateStatus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Update Status Reservasi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="update_id_reservasi">
            <div class="mb-3">
                <label class="form-label">Status Baru</label>
                <select class="form-select" id="select_status_reservasi">
                    <option value="pending">Menunggu Pembayaran</option>
                    <option value="proses">Sedang Berjalan</option>
                    <option value="selesai">Selesai</option>
                    <option value="dibatalkan">Dibatalkan</option>
                    <option value="menunggu_konfirmasi_pembatalan">Menunggu Konfirmasi Pembatalan</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-primary" onclick="submitUpdateStatus()">Simpan</button>
        </div>
    </div>
  </div>
</div>

<!-- Modal Mark Paid -->
<div class="modal fade" id="modalMarkPaid" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Tandai Lunas (Manual)</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="paid_id_reservasi">
            <div class="mb-3">
                <label class="form-label">Jumlah Pembayaran (Rp)</label>
                <input type="number" class="form-control" id="input_jumlah_bayar" required min="0">
            </div>
            <div class="mb-3">
                <label class="form-label">Metode Pembayaran</label>
                <select class="form-select" id="input_metode_bayar">
                    @foreach($metodePembayaran as $m)
                        <option value="{{ $m->nama }}">{{ $m->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-success" onclick="submitMarkPaid()">Proses Pembayaran</button>
        </div>
    </div>
  </div>
</div>

<!-- Modal Add Cost -->
<div class="modal fade" id="modalAddCost" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Tambah Biaya Tambahan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="cost_id_reservasi">
            <div class="alert alert-info py-2" style="font-size: 0.9rem;">
                Menambahkan biaya akan mengubah Total Tagihan dan mungkin mengubah status pembayaran menjadi Belum Lunas.
            </div>
            <div class="mb-3">
                <label class="form-label">Nominal Tambahan (Rp)</label>
                <input type="number" class="form-control" id="input_cost_nominal" placeholder="Contoh: 50000">
            </div>
            <div class="mb-3">
                <label class="form-label">Catatan / Keterangan</label>
                <textarea class="form-control" id="input_cost_catatan" rows="2" placeholder="Contoh: Tambah vitamin rambut"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-warning" onclick="submitAddCost()">Simpan Tambahan</button>
        </div>
    </div>
  </div>
</div>

<!-- Modal Detail Pelanggan -->
<div class="modal fade" id="modalDetailPelanggan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-user-circle"></i> Detail Pelanggan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <i class="fas fa-user fa-3x text-secondary"></i>
            </div>
            <h5 class="mt-2 mb-0" id="detailNama"></h5>
            <small class="text-muted" id="detailEmail"></small>
        </div>
        
        <ul class="list-group list-group-flush">
            <!-- Informasi tambahan yang tidak ada di kolom tabel -->
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clock me-2 text-muted"></i> Terakhir Diupdate</span>
                <span id="detailUpdated"></span>
            </li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // State & Data
    let dataPelanggan = [], viewPelanggan = [];
    let stateP = { page: 1, perPage: 10 };

    let dataTx = [], viewTx = [];
    let stateT = { page: 1, perPage: 10 };

    // DOM Elements - Pelanggan
    const tbodyPelanggan = document.getElementById('table-body-pelanggan');
    const infoPelanggan = document.getElementById('info-pelanggan');
    const pagerPelanggan = document.getElementById('pagination-pelanggan');
    const searchPelanggan = document.getElementById('search-pelanggan');
    const perPageSelectP = document.getElementById('show-entries-pelanggan');

    // DOM Elements - Transaksi
    const tbodyTx = document.getElementById('table-body-transaksi');
    const infoTx = document.getElementById('info-transaksi');
    const pagerTx = document.getElementById('pagination-transaksi');
    const searchTx = document.getElementById('search-transaksi');
    const perPageSelectT = document.getElementById('show-entries-transaksi');

    // Helper: Paginate & Render Pager
    function paginate(items, page, perPage) {
        const total = items.length;
        const totalPages = Math.ceil(total / perPage);
        const p = Math.max(1, Math.min(page, totalPages));
        const offset = (p - 1) * perPage;
        const rows = items.slice(offset, offset + perPage);
        return { rows, total, totalPages, page: p };
    }

    function renderPager(pagerEl, totalPages, currentPage) {
        pagerEl.innerHTML = '';
        if (totalPages <= 1) return;
        
        // Simple Prev
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}">&laquo;</a>`;
        pagerEl.appendChild(prevLi);

        // Pages
        let start = Math.max(1, currentPage - 2);
        let end = Math.min(totalPages, currentPage + 2);
        
        if (start > 1) {
             const li = document.createElement('li');
             li.className = 'page-item';
             li.innerHTML = `<a class="page-link" href="#" data-page="1">1</a>`;
             pagerEl.appendChild(li);
             if(start > 2) {
                 const dot = document.createElement('li');
                 dot.className = 'page-item disabled';
                 dot.innerHTML = `<span class="page-link">...</span>`;
                 pagerEl.appendChild(dot);
             }
        }

        for (let i = start; i <= end; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            pagerEl.appendChild(li);
        }
        
        if (end < totalPages) {
             if(end < totalPages - 1) {
                 const dot = document.createElement('li');
                 dot.className = 'page-item disabled';
                 dot.innerHTML = `<span class="page-link">...</span>`;
                 pagerEl.appendChild(dot);
             }
             const li = document.createElement('li');
             li.className = 'page-item';
             li.innerHTML = `<a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>`;
             pagerEl.appendChild(li);
        }

        // Simple Next
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}">&raquo;</a>`;
        pagerEl.appendChild(nextLi);
    }

    // --- LOGIC PELANGGAN ---
    function drawPelanggan() {
        const { rows, total, totalPages, page } = paginate(viewPelanggan, stateP.page, stateP.perPage);
        
        if (!rows.length) {
            tbodyPelanggan.innerHTML = '<tr><td colspan="8" class="text-center">Data tidak ditemukan</td></tr>';
        } else {
            let html = '';
            rows.forEach((p, i) => {
                // Determine badge class based on status
                const status = (p.status_pelanggan || '').toLowerCase();
                const badgeClass = status === 'aktif' ? 'bg-success' : 'bg-secondary';

                html += `<tr>
                    <td>${(i + 1) + (page-1)*stateP.perPage}</td>
                    <td>${p.nama}</td>
                    <td>${p.id_pelanggan || '-'}</td>
                    <td>${p.formatted_nomor || p.nomor_telepon || '-'}</td>
                    <td>${p.email || '-'}</td>
                    <td><span class="badge ${badgeClass}">${p.formatted_status || p.status_pelanggan || '-'}</span></td>
                    <td>${p.formatted_tanggal_daftar || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-info text-white btn-detail-pelanggan"
                            data-nama="${p.nama}"
                            data-email="${p.email}"
                            data-updated="${p.updated_at || '-'}"
                        >
                            <i class="fas fa-info-circle"></i> Detail
                        </button>
                    </td>
                </tr>`;
            });
            tbodyPelanggan.innerHTML = html;
        }
        
        const from = total ? ((page-1)*stateP.perPage)+1 : 0;
        const to   = ((page-1)*stateP.perPage) + rows.length;
        infoPelanggan.textContent = `Menampilkan ${from}–${to} dari ${total} data`;
        renderPager(pagerPelanggan, totalPages, page);
    }

    function loadPelanggan() {
        fetch('{{ route("pelanggan.ajax") }}')
            .then(r => r.json())
            .then(res => {
                if (res.success && Array.isArray(res.data)) {
                    dataPelanggan = res.data;
                    viewPelanggan = [...dataPelanggan];
                    stateP.page = 1;
                    drawPelanggan();
                } else {
                    tbodyPelanggan.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Gagal memuat data.</td></tr>';
                }
            })
            .catch(() => {
                tbodyPelanggan.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error koneksi.</td></tr>';
            });
    }

    searchPelanggan.addEventListener('keyup', function() {
        const term = this.value.toLowerCase();
        viewPelanggan = dataPelanggan.filter(p => 
            (p.nama && p.nama.toLowerCase().includes(term)) ||
            (p.email && p.email.toLowerCase().includes(term)) ||
            (p.no_telepon && p.no_telepon.includes(term))
        );
        stateP.page = 1;
        drawPelanggan();
    });

    perPageSelectP.addEventListener('change', () => {
        stateP.perPage = parseInt(perPageSelectP.value) || 10;
        stateP.page = 1;
        drawPelanggan();
    });

    pagerPelanggan.addEventListener('click', (e) => {
        const a = e.target.closest('a[data-page]');
        if (!a) return; 
        e.preventDefault();
        const target = parseInt(a.dataset.page);
        const tp = Math.ceil(viewPelanggan.length / stateP.perPage);
        if (target >= 1 && target <= tp) {
            stateP.page = target;
            drawPelanggan();
        }
    });

    // --- LOGIC TRANSAKSI (Existing Below) ---

  function drawTx() {
    const { rows, total, totalPages, page } = paginate(viewTx, stateT.page, stateT.perPage);

    if (!rows.length) {
      tbodyTx.innerHTML = '<tr><td colspan="11" class="text-center">Data tidak ditemukan</td></tr>';
    } else {
      let html = '';
      rows.forEach((t, i) => {
        // Logika Status
        let badgeClass = 'bg-secondary';
        const st = (t.formatted_status || '').toLowerCase();
        
        if (t.status_class_override) {
            badgeClass = t.status_class_override;
        } else if (st.indexOf('lunas') >= 0 || st.indexOf('paid') >= 0) {
            badgeClass = 'bg-success';
        } else if (st.indexOf('pending') >= 0) {
            badgeClass = 'bg-warning text-dark';
        }

        html += `<tr>
          <td>${(i + 1) + (page-1)*stateT.perPage}</td>
          <td>${t.pelanggan_nama}</td>
          <td>${t.id_reservasi || '-'}</td>
          <td>${t.formatted_jumlah}</td> <!-- Total Paid -->
          <td>${t.formatted_diskon}</td>
          <td>${t.formatted_biaya_tambahan || '-'}</td> <!-- BARU -->
          <td>${t.formatted_total}</td>   <!-- Total Tagihan -->
          <td><span class="badge ${badgeClass}">${t.formatted_status}</span></td>
          <td>${t.formatted_metode}</td>
          <td>${t.formatted_tanggal}</td>
          <td class="text-center">
            <div class="d-flex justify-content-center gap-1">
                <!-- ... (Buttons) ... -->
                <button class="btn btn-info btn-sm text-white btn-detail-trx" type="button"
                    data-id="${t.id_reservasi}"
                    data-status="${t.formatted_status}"
                    data-pelanggan="${t.pelanggan_nama}"
                    data-telp="${t.pelanggan_telp}"
                    data-layanan="${t.layanan_nama}"
                    data-catatan="${t.catatan}"
                    data-total-res="${t.formatted_total_reservasi}"
                    data-total-paid="${t.formatted_total_paid}"
                    data-sisa="${t.formatted_sisa}"
                    data-history='${JSON.stringify(t.payment_history || [])}'
                >
                    <i class="fas fa-info-circle"></i> Detail
                </button>
                <div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="viewport">
                        Aksi
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="openUpdateStatusModal(${t.id_reservasi}, '${t.formatted_status}')">Ubah Status</a></li>
                        <li><a class="dropdown-item" href="#" onclick="openAddCostModal(${t.id_reservasi})"><i class="fas fa-plus-circle"></i> Biaya Tambahan</a></li>
                        ${ t.sisa_pembayaran > 0 ? 
                        `<li><a class="dropdown-item text-success" href="#" onclick="openMarkPaidModal(${t.id_reservasi}, ${t.sisa_pembayaran})"><i class="fas fa-money-bill-wave"></i> Tandai Lunas</a></li>` 
                        : '' }
                    </ul>
                </div>
            </div>
          </td>
        </tr>`;
      });
      tbodyTx.innerHTML = html;
    }

    const from = total ? ((page-1)*stateT.perPage)+1 : 0;
    const to   = ((page-1)*stateT.perPage) + rows.length;
    infoTx.textContent = `Menampilkan ${from}–${to} dari ${total} data`;
    renderPager(pagerTx, totalPages, page);
  }

  function loadTx() {
    fetch(`{{ route('pembayaran.ajax') }}`)
      .then(r => r.json())
      .then(res => {
        if (res.success && Array.isArray(res.data)) {
          dataTx = res.data;
          viewTx = [...dataTx];
          stateT.page = 1;
          drawTx();
        } else {
          tbodyTx.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Gagal memuat data pembayaran.</td></tr>';
        }
      })
      .catch(() => {
        tbodyTx.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Terjadi kesalahan saat memuat data.</td></tr>';
      });
  }

  searchTx.addEventListener('keyup', function() {
    const term = this.value.toLowerCase();
    viewTx = dataTx.filter(t => Object.values(t).some(v => String(v).toLowerCase().includes(term)));
    stateT.page = 1;
    drawTx();
  });

  perPageSelectT.addEventListener('change', () => {
    stateT.perPage = parseInt(perPageSelectT.value, 10) || 10;
    stateT.page = 1;
    drawTx();
  });

  pagerTx.addEventListener('click', (e) => {
    const a = e.target.closest('a[data-page]'); if (!a) return; e.preventDefault();
    const target = parseInt(a.dataset.page, 10);
    const tp = Math.max(1, Math.ceil(viewTx.length / stateT.perPage));
    if (target >= 1 && target <= tp) { stateT.page = target; drawTx(); }
  });

  // import/export pembayaran
  const btnImportT = document.getElementById('btn-import-transaksi');
  const inputImportT = document.getElementById('input-import-transaksi');
  const formImportT = document.getElementById('form-import-transaksi');
  const btnExportT = document.getElementById('btn-export-transaksi');
  btnImportT.addEventListener('click', () => inputImportT.click());
  inputImportT.addEventListener('change', () => { if (inputImportT.files.length) formImportT.submit(); });
  btnExportT.addEventListener('click', () => document.getElementById('form-export-transaksi').submit());

  // Handler Tombol Detail
  document.addEventListener('click', function(e) {
      if (e.target && e.target.classList.contains('btn-detail-trx')) {
          e.preventDefault(); // Prevent default if it's an anchor tag
          const btn = e.target;
          const modal = document.getElementById('transactionDetailModal');
          
          // Populate data
          modal.querySelector('#modalTrxPelangganNama').textContent = btn.dataset.pelanggan;
          modal.querySelector('#modalTrxPelangganTelp').textContent = btn.dataset.telp;
          modal.querySelector('#modalTrxLayanan').textContent = btn.dataset.layanan;
          modal.querySelector('#modalTrxCatatan').textContent = btn.dataset.catatan !== '-' ? btn.dataset.catatan : 'Tidak ada catatan tambahan';
          
          modal.querySelector('#modalTrxTotalReservasi').textContent = btn.dataset.totalRes;
          modal.querySelector('#modalTrxTotalPaid').textContent = btn.dataset.totalPaid;
          modal.querySelector('#modalTrxSisa').textContent = btn.dataset.sisa;
          
          // Populate History
          const historyBody = modal.querySelector('#modalTrxHistoryBody');
          historyBody.innerHTML = ''; // clear previous
          
          try {
              const historyData = JSON.parse(btn.dataset.history || '[]');
              if (historyData.length > 0) {
                  historyData.forEach(function(h) {
                      const row = `
                        <tr>
                            <td>${h.tanggal}</td>
                            <td><span class="badge ${h.tipe === 'DP' ? 'bg-info' : 'bg-success'}">${h.tipe}</span></td>
                            <td class="text-end">${h.jumlah}</td>
                            <td>${h.metode}</td>
                        </tr>
                      `;
                      historyBody.innerHTML += row;
                  });
              } else {
                  historyBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Belum ada riwayat pembayaran</td></tr>';
              }
          } catch (err) {
              console.error('Error parsing history:', err);
              historyBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error memuat riwayat</td></tr>';
          }

          // Show modal
          const bsModal = new bootstrap.Modal(modal);
          bsModal.show();
      }
  });

  // --- ACTIONS LOGIC (Update Status, Mark Paid, Add Cost) ---
  
  // Expose functions to window so onclick works in generated HTML
  window.openUpdateStatusModal = function(id, currentStatus) {
      document.getElementById('update_id_reservasi').value = id;
      const select = document.getElementById('select_status_reservasi');
      select.selectedIndex = 0; 
      
      const statusMap = {
          'menunggu pembayaran': 'pending',
          'sedang berjalan': 'proses',
          'selesai': 'selesai',
          'dibatalkan': 'dibatalkan',
          'menunggu konfirmasi pembatalan': 'menunggu_konfirmasi_pembatalan'
      };
      
      let val = String(currentStatus).toLowerCase();
      if (val.includes('berjalan') || val.includes('proses')) val = 'proses';
      else if (val.includes('selesai')) val = 'selesai';
      else if (val.includes('dibatalkan')) val = 'dibatalkan';
      else if (val.includes('pending') || val.includes('menunggu')) val = 'pending';
      
      if (statusMap[val]) val = statusMap[val];
      select.value = val;
      
      const modal = new bootstrap.Modal(document.getElementById('modalUpdateStatus'));
      modal.show();
  };

  window.submitUpdateStatus = function() {
      const id = document.getElementById('update_id_reservasi').value;
      const status = document.getElementById('select_status_reservasi').value;
      
      // Ambil CSRF dari meta tag jika input hidden tidak ada (opsional, tapi robust)
      const csrfToken = document.querySelector('input[name="_token"]')?.value 
        || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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
              loadTx(); // Reload table
          } else {
              Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
          }
      })
      .catch(err => Swal.fire('Error', 'Gagal menghubungi server', 'error'));
  };

  window.openMarkPaidModal = function(id, sisa) {
      document.getElementById('paid_id_reservasi').value = id;
      document.getElementById('input_jumlah_bayar').value = sisa || '';
      document.getElementById('input_metode_bayar').selectedIndex = 0;
      
      const modal = new bootstrap.Modal(document.getElementById('modalMarkPaid'));
      modal.show();
  };

  window.submitMarkPaid = function() {
      const id = document.getElementById('paid_id_reservasi').value;
      const jumlah = document.getElementById('input_jumlah_bayar').value;
      const metode = document.getElementById('input_metode_bayar').value;
      const csrfToken = document.querySelector('input[name="_token"]')?.value 
        || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      if (!jumlah || jumlah <= 0) {
          Swal.fire('Warning', 'Masukkan jumlah pembayaran yang valid', 'warning');
          return;
      }
      
      fetch('{{ route("reservasi.mark_paid") }}', {
          method: 'POST',
          headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
          },
          body: JSON.stringify({ 
              id_reservasi: id, 
              jumlah: jumlah,
              metode: metode
          })
      })
      .then(r => r.json())
      .then(data => {
          if (data.success) {
              Swal.fire('Berhasil', data.message, 'success');
              bootstrap.Modal.getInstance(document.getElementById('modalMarkPaid')).hide();
              loadTx(); // Reload table
          } else {
              Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
          }
      })
      .catch(err => Swal.fire('Error', 'Gagal menghubungi server', 'error'));
  };

  window.openAddCostModal = function(id) {
      document.getElementById('cost_id_reservasi').value = id;
      document.getElementById('input_cost_nominal').value = '';
      document.getElementById('input_cost_catatan').value = '';
      
      const modal = new bootstrap.Modal(document.getElementById('modalAddCost'));
      modal.show();
  };

  window.submitAddCost = function() {
      const id = document.getElementById('cost_id_reservasi').value;
      const nominal = document.getElementById('input_cost_nominal').value;
      const catatan = document.getElementById('input_cost_catatan').value;
      const csrfToken = document.querySelector('input[name="_token"]')?.value 
        || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      
      if (!nominal || nominal <= 0) {
          Swal.fire('Warning', 'Masukkan nominal tambahan yang valid', 'warning');
          return;
      }
      
      fetch('{{ route("reservasi.add_cost") }}', {
          method: 'POST',
          headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
          },
          body: JSON.stringify({ 
              id_reservasi: id, 
              nominal: nominal,
              catatan: catatan
          })
      })
      .then(r => r.json())
      .then(data => {
          if (data.success) {
              Swal.fire('Berhasil', data.message, 'success');
              bootstrap.Modal.getInstance(document.getElementById('modalAddCost')).hide();
              loadTx(); // Reload table
          } else {
              Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
          }
      })
      .catch(err => Swal.fire('Error', 'Gagal menghubungi server', 'error'));
  };

  // Handler Detail Pelanggan
  document.addEventListener('click', function(e) {
      if (e.target && e.target.classList.contains('btn-detail-pelanggan')) { // Check button class directly
          handleDetailClick(e.target);
      } else if (e.target && e.target.closest('.btn-detail-pelanggan')) { // Check if icon inside button was clicked
          handleDetailClick(e.target.closest('.btn-detail-pelanggan'));
      }
  });

  function handleDetailClick(btn) {
      const modal = document.getElementById('modalDetailPelanggan');
      
      // Populate Data
      document.getElementById('detailNama').textContent = btn.dataset.nama;
      document.getElementById('detailEmail').textContent = btn.dataset.email;
      document.getElementById('detailUpdated').textContent = btn.dataset.updated;

      // Show Modal
      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();
  }

  // initial load
  loadPelanggan();
  loadTx();
});
</script>
@endsection
