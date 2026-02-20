@extends('layouts.app')

@section('title', 'Master Data Kategori - Aurora')

@section('content')
<style>
    .card { border: 1px solid #f1b8d6 !important; border-radius: 10px; height: 100%; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .card-header { background-color: #fff; border-bottom: 1px solid #f1b8d6 !important; font-weight: 600; padding: 1rem 1.25rem; }
    table th, table td { font-size: 0.8rem; vertical-align: middle; white-space: nowrap; padding: 0.75rem 0.5rem; }
    .content-wrapper { padding: 20px; }
    .table-controls .form-select, .table-controls .form-control { font-size: 0.875rem; }

    .btn-aksi { font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.375rem; }
    .btn-outline-warning.btn-aksi:hover { color: #fff !important; }

    .btn-tambah-pink { background-color: #fff; border-width: 2px; border-style: solid; font-weight: 600; border-radius: 0.5rem; padding: 0.375rem 1.25rem; font-size: 0.875rem; border-color: #dc3545; color: #dc3545; }
    .btn-tambah-pink:hover { background-color: #dc3545; color: #fff; }

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
                <h5 class="mb-2 mb-md-0">Master Data Kategori</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('kategori-layanan.create') }}" class="btn btn-tambah-pink btn-sm">Tambah</a>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-3 g-2 align-items-center table-controls">
                    <div class="col-12 col-md-auto">
                        <label for="show-entries-kategori" class="col-form-label-sm">
                            Menampilkan
                            <select id="show-entries-kategori" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            Baris
                        </label>
                    </div>
                    <div class="col-12 col-md-auto ms-md-auto">
                        <label for="search-kategori" class="col-form-label-sm">
                            Cari:
                            <input type="search" id="search-kategori" class="form-control form-control-sm ms-1" placeholder="Ketik untuk mencari...">
                        </label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Gambar</th>
                                <th>Kategori</th>
                                <th>Jumlah Layanan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body-kategori">
                            <tr><td colspan="6" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <span id="info-kategori" class="text-muted mb-2 mb-md-0" style="font-size: 0.875rem;">Menampilkan 0 dari 0 data</span>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="pagination-kategori"></ul>
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
    let allKategoriData = [];
    let viewData = [];
    let state = { page: 1, perPage: parseInt(document.getElementById('show-entries-kategori').value, 10) };

    const editUrlTemplate = '{{ route("kategori-layanan.edit", ["kategori_layanan" => "_PLACEHOLDER_"]) }}';
    const destroyUrlTemplate = '{{ route("kategori-layanan.destroy", ["kategori_layanan" => "_PLACEHOLDER_"]) }}';
    const storageBaseUrl = '{{ asset("storage") }}';

    const tbodyKategori = document.getElementById('table-body-kategori');
    const searchKategori = document.getElementById('search-kategori');
    const infoKategori = document.getElementById('info-kategori');
    const perSelect = document.getElementById('show-entries-kategori');
    const pager = document.getElementById('pagination-kategori');

    function paginate(arr, p, n){
        const total = arr.length;
        const totalPages = Math.max(1, Math.ceil(total/n));
        const page = Math.min(Math.max(1,p), totalPages);
        const start = (page-1)*n, end = start+n;
        return { rows: arr.slice(start, end), total, totalPages, page };
    }

    function renderPager(ul, totalPages, current){
        const li = [];
        const add = (lbl, pg, dis=false, act=false) => li.push(
            `<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
                <a class="page-link" href="#" data-page="${pg}">${lbl}</a>
            </li>`
        );

        add('Previous', current-1, current===1);
        if (totalPages <= 7) {
            for (let i=1;i<=totalPages;i++) add(i,i,false,i===current);
        } else {
            add(1,1,false,current===1);
            if (current>4) li.push(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
            const s=Math.max(2,current-1), e=Math.min(totalPages-1,current+1);
            for(let i=s;i<=e;i++) add(i,i,false,i===current);
            if (current<totalPages-3) li.push(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
            add(totalPages,totalPages,false,current===totalPages);
        }
        add('Next', current+1, current===totalPages || totalPages===0);
        ul.innerHTML = li.join('');
    }

    function draw(){
        const term = (searchKategori.value||'').toLowerCase();
        viewData = allKategoriData.filter(item =>
            (item.nama||'').toLowerCase().includes(term) ||
            (item.status||'').toLowerCase().includes(term)
        );

        const {rows, total, totalPages, page} = paginate(viewData, state.page, state.perPage);

        if (!rows.length) {
            tbodyKategori.innerHTML = '<tr><td colspan="6" class="text-center">Data tidak ditemukan</td></tr>';
        } else {
            tbodyKategori.innerHTML = rows.map((item, idx) => {
                const statusHtml = (item.status === 'aktif')
                    ? '<span class="text-success fw-bold">Aktif</span>'
                    : '<span class="text-secondary fw-bold">Non-aktif</span>';
                const editUrl = editUrlTemplate.replace('_PLACEHOLDER_', item.id_kategoriLayanan);
                const gambarHtml = item.gambar
                    ? `<img src="${storageBaseUrl}/${item.gambar}" alt="${item.nama}" class="table-img-thumbnail">`
                    : `<span class="text-muted" style="font-size:.7rem;">(No img)</span>`;
                return `
                <tr>
                    <td>${(idx+1)+((page-1)*state.perPage)}</td>
                    <td>${gambarHtml}</td>
                    <td>${item.nama}</td>
                    <td>${item.layanan_count ?? 0}</td>
                    <td>${statusHtml}</td>
                    <td>
                        <a href="${editUrl}" class="btn btn-outline-warning btn-aksi">Edit</a>
                        <button class="btn btn-outline-danger btn-aksi ms-1" onclick="handleDelete(${item.id_kategoriLayanan})">Hapus</button>
                    </td>
                </tr>`;
            }).join('');
        }

        const from = total ? ((page-1)*state.perPage)+1 : 0;
        const to   = ((page-1)*state.perPage) + (rows.length);
        infoKategori.textContent = `Menampilkan ${from}–${to} dari ${total} data`;
        renderPager(pager, totalPages, page);
    }

    async function fetchKategoriData() {
        try {
            const res = await fetch('{{ route("kategori-layanan.ajax") }}');
            const json = await res.json();
            if (json.success && Array.isArray(json.data)) {
                allKategoriData = json.data;
                state.page = 1;
                draw();
            } else {
                tbodyKategori.innerHTML = `<tr><td colspan="6" class="text-center">Gagal memuat data</td></tr>`;
            }
        } catch {
            tbodyKategori.innerHTML = `<tr><td colspan="6" class="text-center">Terjadi kesalahan server</td></tr>`;
        }
    }

    perSelect.addEventListener('change', () => { state.perPage = parseInt(perSelect.value,10)||10; state.page=1; draw(); });
    searchKategori.addEventListener('keyup', () => { state.page = 1; draw(); });
    pager.addEventListener('click', (e)=>{
        const a = e.target.closest('a[data-page]'); if(!a) return; e.preventDefault();
        const t = parseInt(a.dataset.page,10);
        const tp = Math.max(1, Math.ceil(viewData.length / state.perPage));
        if (t>=1 && t<=tp) { state.page = t; draw(); }
    });

    window.handleDelete = function(id){
        Swal.fire({
            title: 'Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((r)=>{
            if(r.isConfirmed){
                const actionUrl = destroyUrlTemplate.replace('_PLACEHOLDER_', id);
                const form = document.createElement('form');
                form.action = actionUrl;
                form.method = 'POST';
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    };

    fetchKategoriData();
});
</script>

@if (session('success'))
<script>Swal.fire({icon:'success',title:'Sukses!',text:'{{ session('success') }}',timer:3000,showConfirmButton:false});</script>
@endif
@if (session('error'))
<script>Swal.fire({icon:'error',title:'Gagal!',text:'{{ session('error') }}'});</script>
@endif
@endsection
