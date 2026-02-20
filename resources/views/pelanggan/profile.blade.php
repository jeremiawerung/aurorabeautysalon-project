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

    /* HERO STRIP (sama style nuansanya) */
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

    /* Layout */
    .grid{
        display:grid;
        grid-template-columns:2fr 1.4fr;
        gap:18px;
    }
    @media (max-width:900px){
        .grid{grid-template-columns:1fr;}
    }

    .card{
        background:var(--card);
        border-radius:10px;
        box-shadow:var(--shadow);
        padding:18px 18px 16px;
        border:1px solid #f3e5f5;
    }

    .card-title{
        margin:0 0 10px;
        font-size:16px;
        font-weight:700;
        color:var(--ink);
    }
    .card-subtitle{
        margin:0 0 16px;
        font-size:13px;
        color:var(--muted);
    }

    .info-row{
        display:flex;
        justify-content:space-between;
        margin-bottom:8px;
        font-size:14px;
    }
    .info-label{color:var(--muted);}
    .info-value{font-weight:600;color:var(--ink);}

    .badge-count{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:6px 12px;
        border-radius:999px;
        background:var(--pink-light);
        color:var(--pink);
        font-size:13px;
        font-weight:700;
        margin-top:6px;
    }

    .btn-primary,
    .btn-outline{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:9px 16px;
        border-radius:999px;
        font-size:13px;
        font-weight:600;
        text-decoration:none;
        cursor:pointer;
        transition:.15s;
    }

    .btn-primary{
        background:var(--pink);
        border:1px solid var(--pink);
        color:#fff;
    }
    .btn-primary:hover{background:#d81b60;border-color:#d81b60;}

    .btn-outline{
        background:#fff;
        border:1px solid var(--border);
        color:var(--ink);
    }
    .btn-outline:hover{
        border-color:var(--pink);
        color:var(--pink);
    }

    .form-group{margin-bottom:12px;}
    .form-label{font-size:13px;font-weight:600;color:var(--muted);margin-bottom:4px;display:block;}
    .form-input{
        width:100%;
        border-radius:8px;
        border:1px solid var(--border);
        padding:8px 10px;
        font-size:14px;
    }
    .form-input:focus{
        outline:none;
        border-color:var(--pink);
        box-shadow:0 0 0 1px rgba(233,30,99,.1);
    }

    .text-danger{color:#d32f2f;font-size:12px;margin-top:3px;}
    .alert{
        padding:10px 12px;
        border-radius:8px;
        font-size:13px;
        margin-bottom:12px;
    }
    .alert-success{
        background:#e8f5e9;
        color:#2e7d32;
        border:1px solid #c8e6c9;
    }
    .alert-error{
        background:#ffebee;
        color:#c62828;
        border:1px solid #ffcdd2;
    }
</style>
{{-- HERO --}}
<div class="hero-strip">
    <div class="hero-strip-inner">
        <a href="{{ route('welcome') }}" class="hero-back">
            <span class="hero-back-icon">‹</span>
            <span>{{ __('profile.back') }}</span>
        </a>
        <h1 class="hero-title">{{ __('profile.page_title') }}</h1>
    </div>
</div>

<div class="page-wrap">

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid" style="margin-bottom: 20px;">
        {{-- Kartu Data Pelanggan --}}
        <section class="card">
            <h2 class="card-title">{{ __('profile.customer_data') }}</h2>
            <p class="card-subtitle">{{ __('profile.customer_data_desc') }}</p>

            <div class="info-row">
                <span class="info-label">{{ __('profile.name') }}</span>
                <span class="info-value">{{ $pelanggan->nama }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">{{ __('profile.email') }}</span>
                <span class="info-value">{{ $user->email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">{{ __('profile.phone_number') }}</span>
                <span class="info-value">{{ $pelanggan->nomor_telepon }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">{{ __('profile.registration_date') }}</span>
                <span class="info-value">
                    {{ $pelanggan->tanggal_daftar
                        ? \Carbon\Carbon::parse($pelanggan->tanggal_daftar)->format('d-m-Y')
                        : '-' }}
                </span>
            </div>

            <hr style="margin:14px 0;border:none;border-top:1px solid #f1f1f1;">

            <div class="info-row" style="align-items:center;">
                <div>
                    <span class="info-label">{{ __('profile.total_reservations') }}</span><br>
                    <span class="badge-count">{{ __('profile.reservations_count', ['count' => $totalReservasi . 'x']) }}</span>
                </div>
                <a href="{{ route('booking.history') }}" class="btn-primary">
                    {{ __('profile.view_all_reservations') }}
                </a>
            </div>
        </section>

        {{-- Kartu Ubah Password --}}
        <section class="card">
            <h2 class="card-title">{{ __('profile.change_password') }}</h2>
            <p class="card-subtitle">{{ __('profile.change_password_desc') }}</p>

            <form action="{{ route('pelanggan.profile.password') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="password">{{ __('profile.new_password') }}</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        required
                        autocomplete="new-password">
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">{{ __('profile.confirm_new_password') }}</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-input"
                        required
                        autocomplete="new-password">
                </div>

                <button type="submit" class="btn-primary">
                    {{ __('profile.save_password') }}
                </button>
            </form>
        </section>
    </div>
</div>
@endsection