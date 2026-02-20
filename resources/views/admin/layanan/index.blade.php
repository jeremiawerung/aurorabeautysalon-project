@extends('layouts.app')

@section('title', 'Master Data Layanan - Aurora')

@section('content')
<style>
    .card { border: 1px solid #f1b8d6 !important; border-radius: 10px; height: 100%; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .card-header { background-color: #fff; border-bottom: 1px solid #f1b8d6 !important; font-weight: 600; padding: 1rem 1.25rem; }
    table th, table td { font-size: 0.8rem; vertical-align: middle; white-space: nowrap; padding: 0.75rem 0.5rem; }
    .content-wrapper { padding: 20px; }
    .table-controls .form-select, .table-controls .form-control { font-size: 0.875rem; }

    .btn-import-custom, .btn-tambah-custom { background-color: #fff; border-width: 2px; border-style: solid; font-weight: 600; border-radius: 0.5rem; padding: 0.375rem 1.25rem; font-size: 0.875rem; }
    .btn-tambah-custom { border-color: #198754; color: #198754; }
    .btn-tambah-custom:hover { background-color: #198754; color: #fff; }
    .btn-import-custom { border-color: #0d6efd; color: #0d6efd; }
    .btn-import-custom:hover { background-color: #0d6efd; color: #fff; }

    .btn-aksi { font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.375rem; }
    .btn-outline-warning.btn-aksi:hover { color: #fff !important; }

    .table-img-thumbnail { width: 60px; height: 60px; object-fit: cover; border-radius: 0.375rem; border: 1px solid #f1b8d6; }

    @media (max-width: 767.98px) {
        .table-controls .form-label { display: flex; justify-content: space-between; align-items: center; }
        .table-controls .form-label input { flex-grow: 1; margin-left: 0.5rem; }
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center">
                <h5 class="mb-2 mb-md-0">Master Data Layanan</h5>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-import-custom btn-sm" data-bs-toggle="modal" data-bs-target="#importExcelModal">Import</button>
                    <a href="{{ route('layanan.create') }}" class="btn btn-tambah-custom btn-sm">Tambah</a>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-3 g-2 align-items-center table-controls">
                    <div class="col-12 col-md-auto">
                        <label for="show-entries-layanan" class="col-form-label-sm">
                            Menampilkan
                            <select id="show-entries-layanan" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            Baris
                        </label>
                    </div>
                    <div class="col-12 col-md-auto ms-md-auto">
                        <label for="search-layanan" class="col-form-label-sm">
                            Cari:
                            <input type="search" id="search-layanan" class="form-control form-control-sm ms-1" placeholder="Ketik untuk mencari...">
                        </label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Gambar</th>
                                <th>Nama Layanan</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Durasi</th>
                                <th>Status Layanan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body-layanan">
                            <tr><td colspan="8" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <span id="info-layanan" class="text-muted mb-2 mb-md-0" style="font-size: 0.875rem;">Menampilkan 0 dari 0 data</span>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="pagination-layanan"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('layanan.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelLabel">Import Data Layanan (Excel)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3 mt-3">
                        <label for="fileExcel" class="form-label">Pilih File Excel (.xlsx / .xls)</label>
                        <input type="file" name="file" id="fileExcel" class="form-control" accept=".xlsx,.xls" required>

                        <small class="text-muted d-block mt-3 mb-1">📋 Kolom wajib pada file Excel:</small>
                        <ul class="mb-2">
                            <li><code>nama_layanan</code></li>
                            <li><code>id_kategoriLayanan</code></li>
                            <li><code>harga</code></li>
                            <li><code>deskripsi</code></li>
                            <li><code>durasi</code></li>
                            <li><code>status_layanan</code></li>
                        </ul>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    let allLayananData = [];
    let viewData = [];
    let state = { page: 1, perPage: parseInt(document.getElementById('show-entries-layanan').value, 10) };

    const editUrlTemplate = '{{ route("layanan.edit", ["layanan" => "_PLACEHOLDER_"]) }}';
    const destroyUrlTemplate = '{{ route("layanan.destroy", ["layanan" => "_PLACEHOLDER_"]) }}';
    const storageBaseUrl = '{{ asset("storage") }}';

    const tbody = document.getElementById('table-body-layanan');
    const search = document.getElementById('search-layanan');
    const info = document.getElementById('info-layanan');
    const perSel = document.getElementById('show-entries-layanan');
    const pager = document.getElementById('pagination-layanan');

    function formatRupiahInt(angkaInt){
        // angkaInt sudah berupa integer/angka tanpa string "Rp"
        let s = angkaInt.toString();
        let out = '';
        while (s.length > 3){
            out = '.' + s.slice(-3) + out;
            s = s.slice(0, -3);
        }
        out = s + out;
        return 'Rp ' + out;
    }

    function paginate(arr, p, n){
        const total = arr.length;
        const totalPages = Math.max(1, Math.ceil(total/n));
        const page = Math.min(Math.max(1,p), totalPages);
        const start = (page-1)*n, end = start+n;
        return { rows: arr.slice(start,end), total, totalPages, page };
    }

    function renderPager(ul, totalPages, current){
        const li=[];
        const add=(lbl,pg,dis=false,act=false)=>li.push(
            `<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
                <a class="page-link" href="#" data-page="${pg}">${lbl}</a>
            </li>`
        );
        add('Previous',current-1,current===1);
        if(totalPages<=7){
            for(let i=1;i<=totalPages;i++) add(i,i,false,i===current);
        }else{
            add(1,1,false,current===1);
            if(current>4) li.push(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
            const s=Math.max(2,current-1), e=Math.min(totalPages-1,current+1);
            for(let i=s;i<=e;i++) add(i,i,false,i===current);
            if(current<totalPages-3) li.push(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
            add(totalPages,totalPages,false,current===totalPages);
        }
        add('Next',current+1,current===totalPages || totalPages===0);
        ul.innerHTML = li.join('');
    }

    function draw(){
        const term = (search.value||'').toLowerCase();
        viewData = allLayananData.filter(item=>{
            const kategoriNama = item.kategori_layanan ? (item.kategori_layanan.nama || '') : '';
            return (item.nama_layanan||'').toLowerCase().includes(term)
                || kategoriNama.toLowerCase().includes(term)
                || (item.status_layanan||'').toLowerCase().includes(term)
                || (item.formatted_harga||'').toString().includes(term);
        });

        const {rows,total,totalPages,page} = paginate(viewData, state.page, state.perPage);

        if(!rows.length){
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">Data tidak ditemukan</td></tr>';
        }else{
            tbody.innerHTML = rows.map((item, idx)=>{
                const kategoriNama = item.kategori_layanan ? (item.kategori_layanan.nama || '<span class="text-muted">N/A</span>') : '<span class="text-muted">N/A</span>';
                const statusHtml = item.status_layanan === 'aktif'
                    ? '<span class="text-success fw-bold">Aktif</span>'
                    : '<span class="text-secondary fw-bold">Non-aktif</span>';

                const editUrl = editUrlTemplate.replace('_PLACEHOLDER_', item.id_layanan);
                const img = item.gambar ? `<img src="${storageBaseUrl}/${item.gambar}" alt="${item.nama_layanan}" class="table-img-thumbnail">`
                                        : `<span class="text-muted" style="font-size:.7rem;">(No img)</span>`;

                const hargaInt = parseInt(String(item.formatted_harga).replace(/\./g,''),10) || parseInt(item.harga||0,10) || 0;

                return `
                <tr>
                    <td>${(idx+1)+((page-1)*state.perPage)}</td>
                    <td>${img}</td>
                    <td>${item.nama_layanan}</td>
                    <td>${kategoriNama}</td>
                    <td>${formatRupiahInt(hargaInt)}</td>
                    <td>${item.durasi} Menit</td>
                    <td>${statusHtml}</td>
                    <td>
                        <a href="${editUrl}" class="btn btn-outline-warning btn-aksi">Edit</a>
                        <button class="btn btn-outline-danger btn-aksi ms-1" onclick="handleDelete(${item.id_layanan})">Hapus</button>
                    </td>
                </tr>`;
            }).join('');
        }

        const from = total ? ((page-1)*state.perPage)+1 : 0;
        const to   = ((page-1)*state.perPage) + rows.length;
        info.textContent = `Menampilkan ${from}–${to} dari ${total} data`;
        renderPager(pager, totalPages, page);
    }

    async function fetchData(){
        try{
            const res = await fetch('{{ route("layanan.ajax") }}');
            const json = await res.json();
            if(json.success && Array.isArray(json.data)){
                allLayananData = json.data;
                state.page = 1;
                draw();
            }else{
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">Gagal memuat data.</td></tr>';
            }
        }catch{
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">Terjadi kesalahan server.</td></tr>';
        }
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
            title:'Anda yakin?',
            text:'Data layanan yang dihapus tidak dapat dikembalikan!',
            icon:'warning',
            showCancelButton:true,
            confirmButtonColor:'#28a745',
            cancelButtonColor:'#d33',
            confirmButtonText:'Ya, hapus!',
            cancelButtonText:'Batal'
        }).then((r)=>{
            if(r.isConfirmed){
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

    fetchData();
});
</script>

@if (session('success'))
<script>Swal.fire({icon:'success',title:'Sukses!',text:'{{ session('success') }}',timer:3000,showConfirmButton:false});</script>
@endif
@if (session('error'))
<script>Swal.fire({icon:'error',title:'Gagal!',text:'{{ session('error') }}'});</script>
@endif
@endsection
