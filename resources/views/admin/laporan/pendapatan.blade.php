@extends('layouts.app')
@section('title','Report Pendapatan - Aurora')

@section('content')
<style>
.card{border:1px solid #f1b8d6!important;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.05)}
.card-header{background:#fff;border-bottom:1px solid #f1b8d6!important;font-weight:600;padding:1rem 1.25rem}
.table-controls .form-select,.table-controls .form-control{font-size:.875rem}
.btn-export-excel,.btn-export-pdf,.btn-export-print{background:#fff;border:2px solid;font-weight:600;border-radius:.5rem;padding:.375rem 1.25rem;font-size:.875rem}
.btn-export-excel{border-color:#198754;color:#198754}.btn-export-excel:hover{background:#198754;color:#fff}
.btn-export-pdf{border-color:#dc3545;color:#dc3545}.btn-export-pdf:hover{background:#dc3545;color:#fff}
.btn-export-print{border-color:#0d6efd;color:#0d6efd}.btn-export-print:hover{background:#0d6efd;color:#fff}
@media(max-width:767.98px){.table-controls .form-label{display:flex;justify-content:space-between;align-items:center}.table-controls .form-label input{flex-grow:1;margin-left:.5rem}}
</style>

<div class="content-wrapper">
  <div class="container-fluid">

    {{-- Filter --}}
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="fw-semibold mb-3">PENCARIAN PERIODE</h5>
        <form id="form-filter-pendapatan" class="row g-3 align-items-end">
          <div class="col-md-5">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" class="form-control" id="pd_start">
          </div>
          <div class="col-md-5">
            <label class="form-label">Tanggal Selesai</label>
            <input type="date" class="form-control" id="pd_end">
          </div>
          <div class="col-md-3" hidden>
            <label class="form-label">Metode Pembayaran</label>
            <select id="pd_metode" class="form-select">
              <option value="">Semua</option>
              @foreach($metodes as $m)
                  <option value="{{ $m->id_metodePembayaran }}">{{ $m->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">PERGI</button>
          </div>
        </form>
      </div>
    </div>

    {{-- Tabel --}}
    <div class="card mb-4">
      <div class="card-header d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center">
        <h5 class="mb-2 mb-md-0">Report Pendapatan</h5>
        <div class="d-flex flex-wrap gap-2">
          <a href="#" id="pd_export_excel" class="btn btn-export-excel btn-sm">EXCEL</a>
          <a href="#" id="pd_export_pdf" class="btn btn-export-pdf btn-sm">PDF</a>
          <button class="btn btn-export-print btn-sm" onclick="window.print()">PRINT</button>
        </div>
      </div>
      <div class="card-body">
        <div class="row mb-3 g-2 align-items-center table-controls">
          <div class="col-12 col-md-auto">
            <label class="col-form-label-sm">
              Menampilkan
              <select id="pd_perpage" class="form-select form-select-sm d-inline-block w-auto">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
              item
            </label>
          </div>
          <div class="col-12 col-md-auto ms-md-auto">
            <label class="col-form-label-sm">
              Cari:
              <input type="search" id="pd_search" class="form-control form-control-sm ms-1">
            </label>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Total Diskon</th>
                <th>Total Bayar</th>
              </tr>
            </thead>
            <tbody id="pd_tbody"></tbody>
          </table>
        </div>

        <div class="mt-3 d-flex justify-content-between align-items-center">
            <div id="pd_info" class="text-muted" style="font-size:.875rem">Menampilkan 0 data</div>
            <nav aria-label="Page navigation">
              <ul class="pagination pagination-sm mb-0" id="pd_pager"></ul>
            </nav>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.getElementById('pd_tbody');
  const info  = document.getElementById('pd_info');
  const search= document.getElementById('pd_search');
  const perSel= document.getElementById('pd_perpage');
  const pager = document.getElementById('pd_pager');
  const form  = document.getElementById('form-filter-pendapatan');

  const excelBtn = document.getElementById('pd_export_excel');
  const pdfBtn   = document.getElementById('pd_export_pdf');

  let raw=[], view=[];
  let page=1, perPage=parseInt(perSel.value,10)||10;

  function paramsObj(){
    return {
      start_date: document.getElementById('pd_start').value || '',
      end_date:   document.getElementById('pd_end').value   || '',
      metode:     document.getElementById('pd_metode').value|| ''
    };
  }
  function updateExportLinks(){
    const p = new URLSearchParams(paramsObj()).toString();
    excelBtn.href = `{{ route('laporan.pendapatan.export') }}?`+p;
    pdfBtn.href   = `{{ route('laporan.pendapatan.pdf') }}` + `?` + p;
  }

  function paginate(arr,p,n){
    const total=arr.length, totalPages=Math.max(1,Math.ceil(total/n));
    const current=Math.min(Math.max(1,p),totalPages);
    const start=(current-1)*n, end=start+n;
    return {rows:arr.slice(start,end), total, totalPages, page:current};
  }
  function buildPager(ul,totalPages,current){
    const li=[]; const add=(lbl,pg,dis=false,act=false)=>li.push(
      `<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
        <a class="page-link" href="#" data-page="${pg}">${lbl}</a>
      </li>`
    );
    add('Previous',current-1,current===1);
    if(totalPages<=7){ for(let i=1;i<=totalPages;i++) add(i,i,false,i===current); }
    else{
      add(1,1,false,current===1);
      if(current>4) li.push(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
      const s=Math.max(2,current-1), e=Math.min(totalPages-1,current+1);
      for(let i=s;i<=e;i++) add(i,i,false,i===current);
      if(current<totalPages-3) li.push(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
      add(totalPages,totalPages,false,current===totalPages);
    }
    add('Next',current+1,current===totalPages || totalPages===0);
    ul.innerHTML=li.join('');
  }

  function draw(){
    const term=(search.value||'').toLowerCase();
    view = raw.filter(r=>Object.values(r).some(v=>String(v).toLowerCase().includes(term)));
    const {rows,total,totalPages,page:cur}=paginate(view,page,perPage);

    if(!rows.length){
      tbody.innerHTML = `<tr><td colspan="6" class="text-center">Data tidak ditemukan</td></tr>`;
    } else {
      tbody.innerHTML = rows.map((r,i)=>`
        <tr>
          <td>${(i+1)+(cur-1)*perPage}</td>
          <td>${r.formatted_tanggal}</td>
          <td>${r.formatted_jumlah}</td>
          <td>${r.formatted_metode}</td>
          <td>${r.formatted_status}</td>
          <td>${r.formatted_diskon}</td>
          <td>${r.formatted_total}</td>
        </tr>
      `).join('');
    }
    info.textContent = `Menampilkan ${rows.length} dari ${total} data (Halaman ${cur}/${totalPages})`;
    buildPager(pager,totalPages,cur);
  }

  function load(){
    const params = new URLSearchParams(paramsObj());
    updateExportLinks();
    fetch(`{{ route('ajax.laporan.pendapatan') }}?`+params)
      .then(r=>r.json())
      .then(res=>{
        if(!res.success) throw new Error('load failed');
        raw = res.data || [];
        page = 1; draw();
      })
      .catch(()=>{ tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>`; });
  }

  form.addEventListener('submit', e => { e.preventDefault(); load(); });
  search.addEventListener('keyup', ()=>{ page=1; draw(); });
  perSel.addEventListener('change', ()=>{ perPage=parseInt(perSel.value,10)||10; page=1; draw(); });
  pager.addEventListener('click', e=>{
    const a=e.target.closest('a[data-page]'); if(!a) return; e.preventDefault();
    const tp=Math.max(1,Math.ceil(view.length/perPage));
    const t=parseInt(a.dataset.page,10);
    if(t>=1 && t<=tp){ page=t; draw(); }
  });

  // default bulan ini
  const today=new Date();
  const first=new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0,10);
  const last =new Date(today.getFullYear(), today.getMonth()+1, 0).toISOString().slice(0,10);
  document.getElementById('pd_start').value=first;
  document.getElementById('pd_end').value=last;
  updateExportLinks();
  load();

  // update link saat filter input berubah
  ['pd_start','pd_end','pd_metode'].forEach(id=>{
    document.getElementById(id).addEventListener('change', updateExportLinks);
  });

});
</script>
@endsection
