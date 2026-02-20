@extends('layouts.app')

@section('title', 'Data Pesan Kontak - Aurora')

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

    .modal-header {
        background-color: #fff;
        border-bottom: 2px solid #f1b8d6;
    }
    .modal-content {
        border: 1px solid #f1b8d6;
        border-radius: 10px;
    }
    .detail-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 0.25rem;
    }
    .detail-value {
        color: #333;
        margin-bottom: 1rem;
        padding: 0.5rem;
        background-color: #f8f9fa;
        border-radius: 0.375rem;
    }
    .badge-service {
        background-color: #dc3545;
        color: white;
        padding: 0.35rem 0.75rem;
        border-radius: 0.375rem;
        font-weight: 500;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center">
                <h5 class="mb-2 mb-md-0">Data Pesan Kontak</h5>
            </div>

            <div class="card-body">
                <div class="row mb-3 g-2 align-items-center table-controls">
                    <div class="col-12 col-md-auto">
                        <label for="show-entries-contact" class="col-form-label-sm">
                            Menampilkan
                            <select id="show-entries-contact" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            Baris
                        </label>
                    </div>
                    <div class="col-12 col-md-auto ms-md-auto">
                        <label for="search-contact" class="col-form-label-sm d-flex align-items-center">
                            <span>Cari:</span>
                            <input type="search" id="search-contact" class="form-control form-control-sm ms-1" placeholder="Ketik untuk mencari...">
                        </label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. Telepon</th>
                                <th>Layanan</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body-contact">
                            <tr><td colspan="7" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <span id="info-contact" class="text-muted mb-2 mb-md-0" style="font-size: 0.875rem;">
                        Menampilkan 0 dari 0 data
                    </span>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="pagination-contact"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Pesan Kontak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
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
    const tbody = document.getElementById('table-body-contact');
    const search = document.getElementById('search-contact');
    const info   = document.getElementById('info-contact');
    const perSel = document.getElementById('show-entries-contact');
    const pager  = document.getElementById('pagination-contact');

    let dataContacts = [];
    let viewData = [];
    let state = { page: 1, perPage: parseInt(perSel.value,10) };

    const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
    const modalDetailContent = document.getElementById('modalDetailContent');

    function paginate(arr, p, n){
        const total = arr.length, totalPages = Math.max(1, Math.ceil(total/n));
        const page  = Math.min(Math.max(1,p), totalPages);
        const start = (page-1)*n, end = start + n;
        return {rows: arr.slice(start,end), total, totalPages, page};
    }

    function renderPager(ul, totalPages, current){
        const li = [];
        const add = (lbl, pg, dis=false, act=false) => li.push(
            `<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
                <a class="page-link" href="#" data-page="${pg}">${lbl}</a>
            </li>`
        );
        add('Previous', current-1, current===1);
        if(totalPages <= 7){
            for(let i=1;i<=totalPages;i++) add(i,i,false,i===current);
        } else {
            add(1,1,false,current===1);
            if(current>4) li.push(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
            const s = Math.max(2,current-1), e = Math.min(totalPages-1,current+1);
            for(let i=s;i<=e;i++) add(i,i,false,i===current);
            if(current<totalPages-3) li.push(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
            add(totalPages,totalPages,false,current===totalPages);
        }
        add('Next', current+1, current===totalPages || totalPages===0);
        ul.innerHTML = li.join('');
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return date.toLocaleDateString('id-ID', options);
    }

    function draw(){
        const term = (search.value||'').toLowerCase();
        viewData = dataContacts.filter(item => {
            const nama     = item.name || '';
            const email    = item.email || '';
            const phone    = item.phone || '';
            const services = item.services || '';
            return nama.toLowerCase().includes(term)
                || email.toLowerCase().includes(term)
                || phone.toLowerCase().includes(term)
                || services.toLowerCase().includes(term);
        });

        const {rows,total,totalPages,page} = paginate(viewData, state.page, state.perPage);

        if(!rows.length){
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">Data tidak ditemukan</td></tr>';
        } else {
            tbody.innerHTML = rows.map((item, idx)=>{
                const nama     = item.name || '-';
                const email    = item.email || '-';
                const phone    = item.phone || '-';
                const services = item.services || '-';
                const tanggal  = formatDate(item.created_at);

                return `
                <tr>
                    <td>${(idx+1)+((page-1)*state.perPage)}</td>
                    <td>${nama}</td>
                    <td>${email}</td>
                    <td>${phone}</td>
                    <td><span class="badge-service">${services}</span></td>
                    <td>${tanggal}</td>
                    <td>
                        <button class="btn btn-outline-primary btn-aksi" onclick="showDetail(${item.id})">Detail</button>
                    </td>
                </tr>`;
            }).join('');
        }

        const from = total ? ((page-1)*state.perPage)+1 : 0;
        const to   = ((page-1)*state.perPage) + rows.length;
        info.textContent = `Menampilkan ${from}–${to} dari ${total} data`;
        renderPager(pager, totalPages, page);
    }

    function fetchContacts() {
        fetch("{{ route('contacts.ajax') }}")
            .then(response => response.json())
            .then(res => {
                if(res.success) {
                    dataContacts = res.data || [];
                    state.page = 1;
                    draw();
                } else {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center">Gagal memuat data</td></tr>`;
                }
            })
            .catch(() => {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center">Terjadi kesalahan server</td></tr>`;
            });
    }

    window.showDetail = function(id) {
        modalDetailContent.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        detailModal.show();

        fetch(`{{ url('admin/contacts') }}/${id}`)
            .then(response => response.json())
            .then(res => {
                if(res.success) {
                    const item = res.data;
                    modalDetailContent.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-label">Nama Lengkap</div>
                                <div class="detail-value">${item.name}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">${item.email}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">No. Telepon</div>
                                <div class="detail-value">${item.phone}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Layanan yang Diminati</div>
                                <div class="detail-value"><span class="badge-service">${item.services}</span></div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">Pesan</div>
                                <div class="detail-value" style="white-space: pre-wrap;">${item.message}</div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">Tanggal Dikirim</div>
                                <div class="detail-value">${formatDate(item.created_at)}</div>
                            </div>
                        </div>
                    `;
                } else {
                    modalDetailContent.innerHTML = `
                        <div class="alert alert-danger">Data tidak ditemukan</div>
                    `;
                }
            })
            .catch(() => {
                modalDetailContent.innerHTML = `
                    <div class="alert alert-danger">Terjadi kesalahan saat memuat detail</div>
                `;
            });
    };

    perSel.addEventListener('change', ()=>{ state.perPage=parseInt(perSel.value,10)||10; state.page=1; draw(); });
    search.addEventListener('keyup', ()=>{ state.page=1; draw(); });
    pager.addEventListener('click', (e)=>{
        const a=e.target.closest('a[data-page]'); if(!a) return; e.preventDefault();
        const t=parseInt(a.dataset.page,10);
        const tp=Math.max(1, Math.ceil(viewData.length/state.perPage));
        if(t>=1 && t<=tp){ state.page=t; draw(); }
    });

    fetchContacts();
});
</script>

@if (session('success'))
<script>
Swal.fire({
    icon:'success',
    title:'Sukses!',
    text:'{{ session('success') }}',
    timer:3000,
    showConfirmButton:false
});
</script>
@endif
@if (session('error'))
<script>
Swal.fire({
    icon:'error',
    title:'Gagal!',
    text:'{{ session('error') }}'
});
</script>
@endif
@if (session('warning'))
<script>
Swal.fire({
    icon:'warning',
    title:'Peringatan!',
    text:'{{ session('warning') }}'
});
</script>
@endif
@if (session('info'))
<script>
Swal.fire({
    icon:'info',
    title:'Informasi',
    text:'{{ session('info') }}'
});
</script>
@endif
@endsection