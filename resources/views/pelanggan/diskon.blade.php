@extends('layouts.pelanggan')

@section('content')
<style>
    :root{
        --pink:#e91e63;
        --pink-light:#fce4ec;
        --ink:#2d2a2a;
        --muted:#757575;
        --bg:#fafafa;
        --card:#fff;
        --border:#e0e0e0;
        --shadow:0 2px 8px rgba(0,0,0,.08);
    }

    .page-wrap{max-width:1100px;margin:0 auto;padding:16px}

    /* HERO STRIP */
    .hero-strip{
        width:100%;
        margin:0 auto 18px;
        background:
            linear-gradient(90deg, rgba(0,0,0,.35), rgba(0,0,0,.15)),
            url('{{ asset('img/image.png') }}') center/cover no-repeat;
        color:#fff;
    }
    .hero-strip-inner{
        max-width:1100px;
        margin:0 auto;
        padding:12px 16px;
        display:flex;
        align-items:center;
        position:relative;
        min-height:52px;
    }
    .hero-back{
        color:#fff;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        gap:6px;
        font-size:14px;
        font-weight:500;
        z-index:2;
    }
    .hero-back-icon{font-size:18px;line-height:1;}
    .hero-back:hover{opacity:.85}

    .hero-title{
        position:absolute;
        left:50%;
        transform:translateX(-50%);
        color: white !important;
        margin:0;
        font-size:16px;
        letter-spacing:.15em;
        text-transform:uppercase;
        font-weight:700;
        text-align:center;
        white-space:nowrap;
    }

    @media (max-width:768px){
        .hero-strip-inner{flex-direction:row;justify-content:flex-start;}
        .hero-title{
            position:static;
            transform:none;
            margin-left:16px;
            font-size:14px;
            letter-spacing:.12em;
            white-space:normal;
        }
    }

    .card{
        background:var(--card);
        border-radius:10px;
        box-shadow:var(--shadow);
        padding:18px;
        border:1px solid #f3e5f5;
    }

    .card-title{
        margin:0 0 8px;
        font-size:16px;
        font-weight:700;
        color:var(--ink);
    }
    .card-subtitle{
        margin:0 0 16px;
        font-size:13px;
        color:var(--muted);
    }

    /* Table Styles */
    .table-responsive{
        overflow-x:auto;
        margin-top:12px;
    }
    
    .diskon-table{
        width:100%;
        border-collapse:collapse;
        font-size:13px;
    }
    
    .diskon-table thead{
        background:var(--pink-light);
    }
    
    .diskon-table th{
        padding:10px 12px;
        text-align:left;
        font-weight:600;
        color:var(--pink);
        border-bottom:2px solid var(--pink);
        white-space:nowrap;
    }
    
    .diskon-table td{
        padding:12px;
        border-bottom:1px solid var(--border);
        color:var(--ink);
    }
    
    .diskon-table tbody tr:hover{
        background:#fafafa;
    }

    /* Badge Styles */
    .badge{
        display:inline-block;
        padding:4px 10px;
        border-radius:999px;
        font-size:11px;
        font-weight:600;
        text-transform:uppercase;
        letter-spacing:.02em;
    }
    
    .badge-global{
        background:#e3f2fd;
        color:#1976d2;
    }
    
    .badge-personal{
        background:var(--pink-light);
        color:var(--pink);
    }
    
    .badge-aktif{
        background:#e8f5e9;
        color:#2e7d32;
    }
    
    .badge-tidak-aktif{
        background:#f5f5f5;
        color:#757575;
    }
    
    .badge-kedaluwarsa{
        background:#fff3e0;
        color:#f57c00;
    }

    .kode-diskon{
        font-family:monospace;
        background:#f5f5f5;
        padding:4px 8px;
        border-radius:4px;
        font-weight:700;
        color:var(--pink);
        font-size:14px;
    }

    .persentase{
        font-weight:700;
        color:var(--pink);
        font-size:15px;
    }

    .empty-state{
        text-align:center;
        padding:40px 20px;
        color:var(--muted);
    }
    
    .empty-state-icon{
        font-size:48px;
        margin-bottom:12px;
        opacity:.5;
    }
    
    .empty-state-text{
        font-size:14px;
        margin:0;
    }

    .info-note{
        background:#f8f9fa;
        border-left:3px solid var(--pink);
        padding:10px 12px;
        margin-bottom:16px;
        border-radius:4px;
        font-size:12px;
        color:var(--muted);
    }

    @media (max-width:768px){
        .diskon-table{font-size:12px;}
        .diskon-table th, .diskon-table td{padding:8px 6px;}
        .kode-diskon{font-size:12px;}
    }
</style>

{{-- HERO --}}
<div class="hero-strip">
    <div class="hero-strip-inner">
        <a href="{{ route('welcome') }}" class="hero-back">
            <span class="hero-back-icon">‹</span>
            <span>Kembali</span>
        </a>
        <h1 class="hero-title">Diskon Saya</h1>
    </div>
</div>

<div class="page-wrap">
    <div class="card">

        <div class="info-note">
            <strong>Info:</strong> Diskon bisa di gunakan saat anda akan melakukan transaksi.
        </div>

        @if($diskons->count() > 0)
            <div class="table-responsive">
                <table class="diskon-table">
                    <thead>
                        <tr>
                            <th>Kode Diskon</th>
                            <th>Nama Diskon</th>
                            <th>Tipe</th>
                            <th>Persentase</th>
                            <th>Periode</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($diskons as $diskon)
                        <tr>
                            <td>
                                <span class="kode-diskon">{{ $diskon->kode_diskon }}</span>
                            </td>
                            <td>
                                <strong>{{ $diskon->nama_diskon }}</strong>
                                @if($diskon->keterangan)
                                    <br>
                                    <small style="color:var(--muted);">{{ Str::limit($diskon->keterangan, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($diskon->tipe_diskon == 'GLOBAL')
                                    <span class="badge badge-global">Global</span>
                                @else
                                    <span class="badge badge-personal">Personal</span>
                                @endif
                            </td>
                            <td>
                                <span class="persentase">{{ number_format($diskon->persentase_diskon, 0) }}%</span>
                            </td>
                            <td>
                                <div style="font-size:12px;">
                                    <div><strong>Mulai:</strong> {{ \Carbon\Carbon::parse($diskon->tanggal_mulai)->format('d M Y') }}</div>
                                    @if($diskon->tanggal_berakhir)
                                        <div><strong>Berakhir:</strong> {{ \Carbon\Carbon::parse($diskon->tanggal_berakhir)->format('d M Y') }}</div>
                                    @else
                                        <div><strong>Berakhir:</strong> <span style="color:var(--muted);">Tidak terbatas</span></div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($diskon->status == 'aktif')
                                    <span class="badge badge-aktif">Aktif</span>
                                @elseif($diskon->status == 'tidak aktif')
                                    <span class="badge badge-tidak-aktif">Tidak Aktif</span>
                                @else
                                    <span class="badge badge-kedaluwarsa">Kedaluwarsa</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">🎟️</div>
                <p class="empty-state-text">
                    Belum ada diskon yang tersedia untuk Anda saat ini.<br>
                    Pantau terus halaman ini untuk mendapatkan penawaran menarik!
                </p>
            </div>
        @endif
    </div>
</div>
@endsection