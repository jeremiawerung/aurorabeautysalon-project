@extends('layouts.app')

@section('title', 'Master Data Jadwal Slot Layanan - Aurora')

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
    .btn-outline-warning.btn-aksi:hover { color: #fff !important; }

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
    .btn-tambah-pink:hover { background-color: #dc3545; color: #fff; }

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
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center">
                <h5 class="mb-2 mb-md-0">Master Data Jadwal Slot Layanan</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('slot-jadwal.create') }}" class="btn btn-tambah-pink btn-sm">Tambah</a>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-3 g-2 align-items-center table-controls">
                    <div class="col-12 col-md-auto">
                        <label for="show-entries-jadwal" class="col-form-label-sm">
                            Menampilkan
                            <select id="show-entries-jadwal" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            Baris
                        </label>
                    </div>
                    <div class="col-12 col-md-auto ms-md-auto">
                        <label for="search-jadwal" class="col-form-label-sm d-flex align-items-center">
                            <span>Cari:</span>
                            <input type="search" id="search-jadwal" class="form-control form-control-sm ms-1" placeholder="Ketik untuk mencari...">
                        </label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Layanan</th>
                                <th>Waktu</th>
                                <th>Status Slot</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body-jadwal">
                            <tr><td colspan="5" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <span id="info-jadwal" class="text-muted mb-2 mb-md-0" style="font-size: 0.875rem;">
                        Menampilkan 0 dari 0 data
                    </span>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="pagination-jadwal"></ul>
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
    const tbody = document.getElementById('table-body-jadwal');
    const search = document.getElementById('search-jadwal');
    const info   = document.getElementById('info-jadwal');
    const perSel = document.getElementById('show-entries-jadwal');
    const pager  = document.getElementById('pagination-jadwal');

    let dataJadwalSlot = [];
    let viewData = [];
    let state = { page: 1, perPage: parseInt(perSel.value,10) };

    const editUrlTemplate    = '{{ route("slot-jadwal.edit", ["slot_jadwal" => "_PLACEHOLDER_"]) }}';
    const destroyUrlTemplate = '{{ route("slot-jadwal.destroy", ["slot_jadwal" => "_PLACEHOLDER_"]) }}';

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

    function draw(){
        const term = (search.value||'').toLowerCase();
        viewData = dataJadwalSlot.filter(item => {
            const nama   = item.layanan?.nama_layanan || '';
            const status = item.status_slot || '';
            const waktu  = item.waktu || '';
            return nama.toLowerCase().includes(term)
                || status.toLowerCase().includes(term)
                || waktu.toLowerCase().includes(term);
        });

        const {rows,total,totalPages,page} = paginate(viewData, state.page, state.perPage);

        if(!rows.length){
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Data tidak ditemukan</td></tr>';
        } else {
            tbody.innerHTML = rows.map((item, idx)=>{
                const editUrl  = editUrlTemplate.replace('_PLACEHOLDER_', item.id_slot);
                const waktu    = item.waktu || '-';
                const status   = item.status_slot || '-';
                const nama     = item.layanan?.nama_layanan || '-';

                return `
                <tr>
                    <td>${(idx+1)+((page-1)*state.perPage)}</td>
                    <td>${nama}</td>
                    <td>${waktu}</td>
                    <td>${status}</td>
                    <td>
                        <a href="${editUrl}" class="btn btn-outline-warning btn-aksi">Edit</a>
                        <button class="btn btn-outline-danger btn-aksi ms-1" onclick="handleDelete(${item.id_slot})">Hapus</button>
                    </td>
                </tr>`;
            }).join('');
        }

        const from = total ? ((page-1)*state.perPage)+1 : 0;
        const to   = ((page-1)*state.perPage) + rows.length;
        info.textContent = `Menampilkan ${from}–${to} dari ${total} data`;
        renderPager(pager, totalPages, page);
    }

    function fetchJadwalSlot() {
        fetch("{{ route('slot-jadwal.ajax') }}")
            .then(response => response.json())
            .then(res => {
                if(res.success) {
                    dataJadwalSlot = res.data || [];
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

    perSel.addEventListener('change', ()=>{ state.perPage=parseInt(perSel.value,10)||10; state.page=1; draw(); });
    search.addEventListener('keyup', ()=>{ state.page=1; draw(); });
    pager.addEventListener('click', (e)=>{
        const a=e.target.closest('a[data-page]'); if(!a) return; e.preventDefault();
        const t=parseInt(a.dataset.page,10);
        const tp=Math.max(1, Math.ceil(viewData.length/state.perPage));
        if(t>=1 && t<=tp){ state.page=t; draw(); }
    });

    window.handleDelete = function(id){
        Swal.fire({
            title: 'Yakin ingin menghapus semua slot layanan ini?',
            text: "Semua slot waktu untuk layanan ini akan dihapus dan tidak dapat dikembalikan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const url = destroyUrlTemplate.replace('_PLACEHOLDER_', id);
                const form = document.createElement('form');
                form.action = url;
                form.method = 'POST';
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    };

    fetchJadwalSlot();
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
