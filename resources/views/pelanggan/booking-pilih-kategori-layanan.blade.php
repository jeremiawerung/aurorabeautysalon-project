@extends('layouts.pelanggan')

@section('content')
<style>
    :root{
        --pink:#ff6b9e; --pink-200:#ffe3ec; --ink:#2d2a2a; --muted:#8a8888; --bg:#fff7fa;
        --shadow-lg:0 10px 30px rgba(45,42,42,.12)
    }
    body { background: var(--bg) }
    .hero{
        height:220px;
        background:linear-gradient(0deg,rgba(0,0,0,.35),rgba(0,0,0,.2)),
        url('https://images.unsplash.com/photo-1556228720-195a672e8a03?q=80&w=1920&auto=format&fit=crop') center/cover;
        color:#fff;display:flex;align-items:flex-end;justify-content:center;margin-bottom:20px
    }
    .hero-content{width:100%;max-width:1200px;padding:0 40px 28px;text-align:center}
    .hero h1{margin:6px 0 0;font-size:28px;letter-spacing:.08em;text-transform:uppercase}
    .label{opacity:.9;letter-spacing:.25em;text-transform:uppercase}

    .container-page{max-width:1200px;margin:0 auto;padding:0 20px 40px;display:grid;gap:28px}
    .category-list{display:flex;flex-direction:column;gap:16px}
    .category-row{
        background:#fff;border:1px solid #f2d8e2;border-radius:16px;padding:18px;
        display:grid;grid-template-columns:180px 1fr 160px;gap:18px;transition:.2s
    }
    .category-row:hover{box-shadow:var(--shadow-lg);transform:translateY(-1px)}
    .category-thumb{width:100%;height:140px;object-fit:cover;border-radius:12px}
    .category-title{margin:0 0 6px;font-weight:800}
    .category-meta{color:var(--muted);font-size:13px;margin-bottom:8px}
    .btn-outline{
        background:#fff;border:1px solid #f0d5de;border-radius:12px;padding:10px 14px;
        font-weight:700;cursor:pointer;width:100%
    }
    .btn-outline:hover{border-color:var(--pink);color:var(--pink)}
    @media (max-width:900px){.category-row{grid-template-columns:160px 1fr}.action{grid-column:span 2}}
    @media (max-width:640px){.category-row{grid-template-columns:1fr}.category-thumb{height:180px}.action{grid-column:span 1}}
</style>

<div class="hero">
    <div class="hero-content">
        <span class="label">{{ __('categories.badge') }}</span>
        <h1>{{ __('categories.title') }}</h1>
    </div>
</div>

<div class="container-page">
    <div class="category-list">
        @forelse($categories as $cat)
            <div class="category-row">
                <img class="category-thumb"
                     src="{{ $cat->gambar ? asset('storage/'.$cat->gambar) : asset('img/favicon.svg') }}"
                     alt="{{ $cat->nama }}">
                <div>
                    <h3 class="category-title">{{ $cat->nama }}</h3>
                    <div class="category-meta">
                        {!! __('categories.meta.services_html', ['count' => '<strong>'.($cat->layanan->count() ?? 0).'</strong>']) !!}
                    </div>
                    @if(!empty($cat->keterangan))
                        <div style="color:#666;font-size:14px;line-height:1.5">
                            {{ \Illuminate\Support\Str::limit($cat->keterangan, 140) }}
                        </div>
                    @endif
                </div>
                <div class="action" style="display:flex;align-items:center">
                    {{-- Arahkan ke step1 berdasarkan "nama" kategori --}}
                    <a class="btn-outline" href="{{ route('booking.step1', ['kategori' => $cat->nama]) }}">
                        {{ __('categories.actions.choose') }}
                    </a>
                </div>
            </div>
        @empty
            <div class="category-row"><p>{{ __('categories.empty') }}</p></div>
        @endforelse
    </div>
</div>
@endsection
