@extends('layouts.app')

@section('title', 'Master Data Diskon - Aplikasi')

@section('content')
<style>
    /* Styling seragam dengan Master Data Jadwal Slot Layanan */
    .card {
        border: 1px solid #f1b8d6 !important; /* Warna pink lembut */
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
    .btn-outline-warning.btn-aksi {
        color: #ffc107; /* Warna default warning */
        border-color: #ffc107;
    }
    .btn-outline-warning.btn-aksi:hover {
        background-color: #ffc107;
        color: #fff !important;
    }
    .btn-outline-danger.btn-aksi {
        color: #dc3545; /* Warna default danger (merah) */
        border-color: #dc3545;
    }
    .btn-outline-danger.btn-aksi:hover {
        background-color: #dc3545;
        color: #fff !important;
    }

    /* Tombol Tambah seragam dengan warna pink/merah */
    .btn-tambah-pink {
        background-color: #fff;
        border-width: 2px;
        border-style: solid;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.375rem 1.25rem;
        font-size: 0.875rem;
        border-color: #dc3545; /* Merah untuk konsistensi */
        color: #dc3545;
    }
    .btn-tambah-pink:hover { 
        background-color: #dc3545; 
        color: #fff; 
    }

    /* Status badge styling */
    .badge-status-aktif { background-color: #d4edda; color: #155724; font-weight: 600; }
    .badge-status-tidak-aktif { background-color: #f8d7da; color: #721c24; font-weight: 600; }
    .badge-status-kedaluwarsa { background-color: #ffeeba; color: #856404; font-weight: 600; }

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
                <h5 class="mb-2 mb-md-0">Master Data Diskon</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('diskon.create') }}" class="btn btn-tambah-pink btn-sm">Tambah Diskon</a>
                </div>
            </div>

            <div class="card-body">
                {{-- Bagian Kontrol (Search dan Entries) --}}
                <div class="row mb-3 g-2 align-items-center table-controls">
                    <div class="col-12 col-md-auto">
                        <label for="show-entries-diskon" class="col-form-label-sm">
                            Menampilkan
                            <select id="show-entries-diskon" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            Baris
                        </label>
                    </div>
                    <div class="col-12 col-md-auto ms-md-auto">
                        <label for="search-diskon" class="col-form-label-sm d-flex align-items-center">
                            <span>Cari:</span>
                            <input type="search" id="search-diskon" class="form-control form-control-sm ms-1" placeholder="Ketik untuk mencari...">
                        </label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Diskon</th>
                                <th>Kode Diskon</th>
                                <th>Persentase</th>
                                <th>Periode</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body-diskon">
                        @if(isset($diskons) && $diskons->count())
                            @foreach($diskons as $index => $diskon)
                                @php
                                    $statusClass = '';
                                    switch ($diskon->status_diskon) {
                                        case 'aktif':
                                            $statusClass = 'badge-status-aktif';
                                            break;
                                        case 'tidak aktif':
                                            $statusClass = 'badge-status-tidak-aktif';
                                            break;
                                        case 'kedaluwarsa':
                                            $statusClass = 'badge-status-kedaluwarsa';
                                            break;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $diskon->nama_diskon }}</td>
                                    <td><strong>{{ $diskon->kode_diskon }}</strong></td>
                                    <td>{{ number_format($diskon->persentase_diskon, 0) }}%</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($diskon->tanggal_mulai)->format('d M Y') }} 
                                        s/d 
                                        @if($diskon->tanggal_berakhir)
                                            {{ \Carbon\Carbon::parse($diskon->tanggal_berakhir)->format('d M Y') }}
                                        @else
                                            (Tak Terbatas)
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusClass }}">
                                            {{ ucfirst($diskon->status_diskon) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('diskon.edit', $diskon->id) }}" class="btn btn-outline-warning btn-aksi">Edit</a>
                                        <button class="btn btn-outline-danger btn-aksi ms-1" onclick="deleteConfirmation({{ $diskon->id }})">Hapus</button>
                                        
                                        <form id="delete-form-{{ $diskon->id }}" action="{{ route('diskon.destroy', $diskon->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data diskon yang tersedia.</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    @if(isset($diskons) && method_exists($diskons, 'links'))
                        {{ $diskons->links() }}
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Fungsi untuk menampilkan konfirmasi hapus menggunakan SweetAlert2
    function deleteConfirmation(id) {
        Swal.fire({
            title: 'Yakin ingin menghapus diskon ini?',
            text: "Diskon yang dihapus tidak dapat dikembalikan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545', // Warna merah untuk aksi hapus
            cancelButtonColor: '#6c757d', // Warna abu-abu untuk batal
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    // SweetAlert2 untuk menampilkan pesan dari session
    @if (session('success'))
    Swal.fire({
        icon:'success',
        title:'Sukses!',
        text:'{{ session('success') }}',
        timer:3000,
        showConfirmButton:false
    });
    @endif
    
    // Menambahkan logika SweetAlert untuk error, warning, dan info
    @if (session('error'))
    Swal.fire({
        icon:'error',
        title:'Gagal!',
        text:'{{ session('error') }}'
    });
    @endif
    @if (session('warning'))
    Swal.fire({
        icon:'warning',
        title:'Peringatan!',
        text:'{{ session('warning') }}'
    });
    @endif
    @if (session('info'))
    Swal.fire({
        icon:'info',
        title:'Informasi',
        text:'{{ session('info') }}'
    });
    @endif
</script>
@endsection