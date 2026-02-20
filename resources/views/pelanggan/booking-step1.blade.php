@extends('layouts.pelanggan')

@section('content')
    <style>
        /* CSS DARI KODE ANDA SEBELUMNYA TETAP SAMA */
        :root{
            --pink:#ff6b9e;
            --pink-200:#fff0f6;
            --pink-dark:#e55588;
            --ink:#2d2a2a;
            --muted:#7a7a7a;
            --bg:#fff7fa;
            --radius:14px;
            --shadow-sm:0 2px 8px rgba(45,42,42,.04);
            --shadow-md:0 6px 16px rgba(45,42,42,.08);
            --shadow-lg:0 12px 30px rgba(45,42,42,.10)
        }
        body { background: var(--bg) }

        .hero {
            height: 200px;
            /* Perbaikan: Menggunakan inline style untuk gambar agar dinamis */
            background:
                linear-gradient(0deg, rgba(0,0,0,.35), rgba(0,0,0,.15)),
                url('{{ $category->gambar ? asset('storage/'.$category->gambar) : 'https://images.unsplash.com/photo-1556228720-195a672e8a03?q=80&w=1920&auto=format&fit=crop' }}') center/cover;
            color: #fff;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            margin-bottom: 18px
        }
        .hero-content {
            width: 100%;
            max-width: 1200px;
            padding: 0 24px 22px;
            text-align: center
        }
        .hero h1 {
            margin: 4px 0 0;
            font-size: 26px;
            letter-spacing: .06em;
            text-transform: uppercase
        }
        .label {
            opacity: .9;
            letter-spacing: .18em;
            font-size: 12px
        }

        .container-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 24px
        }

        /* LIST */
        .service-list{ display:flex; flex-direction:column; gap:14px }

        .service-row{
            background:#fff;
            border:1px solid #f5dbe7;
            border-radius:16px;
            padding:16px;
            display:grid;
            grid-template-columns:160px 1fr;
            gap:16px;
            transition:.18s
        }
        .service-row:hover{ box-shadow:var(--shadow-md); transform: translateY(-1px) }
        .service-row.in-cart{ border-color:var(--pink); background:var(--pink-200); }

        .service-thumb{
            width:100%;
            height:140px;
            object-fit:cover;
            border-radius:12px;
            background:#fafafa
        }

        .service-content{ display:flex; flex-direction:column; gap:10px }
        .service-header{
            display:flex; align-items:flex-start; gap:14px
        }
        .service-title{
            margin:0; font-weight:800; font-size:18px; color:var(--ink); flex:1
        }
        .service-actions{ margin-left:auto }
        .btn-add{
            background:var(--pink);
            color:#fff;
            border:none;
            border-radius:12px;
            padding:10px 14px;
            font-weight:700;
            cursor:pointer;
            transition:.18s;
            box-shadow:var(--shadow-sm)
        }
        .btn-add:hover{ background:var(--pink-dark) }
        .btn-add[disabled]{ opacity:.5; cursor:not-allowed }
        .btn-added{ background:#00AA00; }
        .btn-added:hover{ background:#008800; }

        .service-meta{ color:var(--muted); font-size:13px }
        .service-price{ color:var(--pink); font-weight:800; font-size:18px }

        /* SIDEBAR */
        .sidebar{
            position:sticky; top:18px;
            background:#fff; border:1px solid #f5dbe7; border-radius:18px;
            padding:18px; height:max-content; box-shadow:var(--shadow-sm)
        }
        .sidebar-title{
            font-weight:800; margin:0 0 10px;
            padding-bottom:10px; border-bottom:2px solid var(--pink-200)
        }
        .booking-card{
            background:#fff; border:1px solid #f5e3ec; border-radius:12px;
            padding:12px; margin-bottom:10px
        }
        .booking-card-title{ margin:0 0 6px; font-weight:700 }
        .btn-remove{
            background:#fff; border:1px solid #efd6df; border-radius:8px;
            width:28px; height:28px; display:flex; align-items:center; justify-content:center;
            color:#c33; cursor:pointer
        }
        .booking-total{ border-top:1px solid #efd6df; padding-top:12px; margin-top:12px }

        /* Gaya Link A sebagai Tombol */
        .btn-primary{
            display: block;
            text-align: center;
            text-decoration: none;
            width:100%;
            background:var(--pink);
            color:#fff;
            border:none;
            border-radius:12px;
            padding:13px;
            font-weight:800;
            cursor:pointer;
            transition:.18s
        }
        .btn-primary:hover{ background:var(--pink-dark) }
        .btn-primary.disabled{
            opacity:.5;
            cursor:not-allowed;
            pointer-events: none; /* Menonaktifkan klik pada link */
        }

        @media (max-width:1024px){ .container-page{ grid-template-columns:1fr } }
        @media (max-width:640px){
            .service-row{ grid-template-columns:1fr }
            .service-thumb{ height:180px }
        }
    </style>


    <div class="hero">
        <div class="hero-content">
            {{-- Multilingual: Category Label --}}
            <span class="label">{{ __('booking.category_label') }}: {{ strtoupper($category->nama) }}</span>

            {{-- Display Category Description --}}
            <h1>{{ $category->keterangan ?? $category->nama }}</h1>
        </div>
    </div>

<div class="container-page">
    <div>
        <div class="service-list">
            @forelse ($services as $svc)
                <div class="service-row" data-service-id="{{ $svc->id_layanan }}">
                    {{-- Thumbnail + fallback --}}
                    <img
                        class="service-thumb"
                        src="{{ $svc->gambar ? asset('storage/'.$svc->gambar) : asset('img/favicon.svg') }}"
                        alt="{{ $svc->nama_layanan }}"
                        loading="lazy"
                        decoding="async"
                        onerror="this.onerror=null;this.src='{{ asset('img/favicon.svg') }}';"
                    >

                    <div class="service-content">
                        <div class="service-header">
                            <h3 class="service-title">{{ $svc->nama_layanan }}</h3>

                            <div class="service-actions">
                                <button
                                    class="btn-add"
                                    type="button"
                                    id="btn-add-{{ $svc->id_layanan }}"
                                    onclick="handleCartAction({{ $svc->id_layanan }})"
                                    data-service-id="{{ $svc->id_layanan }}"
                                >
                                    {{ __('booking.btn_add') }}
                                </button>
                            </div>
                        </div>

                        {{-- Multilingual: Duration --}}
                        <div class="service-meta">{{ __('booking.duration') }}: {{ $svc->durasi }} {{ __('booking.minutes') }}</div>
                        <div class="service-price">Rp {{ number_format($svc->harga, 0, ',', '.') }}</div>
                    </div>
                </div>
            @empty
                <div class="service-row">
                    {{-- Multilingual: No Services Message --}}
                    <p>{{ __('booking.no_services') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <aside class="sidebar">
        {{-- Multilingual: Cart Title --}}
        <h3 class="sidebar-title">{{ __('booking.cart_title') }}</h3>
        <div id="cartList">
            {{-- Multilingual: Empty Cart Message --}}
            <div style="text-align:center;color:#888;padding:20px">{{ __('booking.cart_empty') }}</div>
        </div>
        <div class="booking-total">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;color:#777">
                {{-- Multilingual: Subtotal --}}
                <span>{{ __('booking.subtotal') }}</span>
                <span id="subtotal">Rp 0</span>
            </div>

            <a
                id="nextBtn"
                href="{{ route('booking.step2') }}"
                class="btn-primary disabled"
            >
                {{ __('booking.next_step') }}
            </a>
        </div>
    </aside>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Token CSRF
    const CSRF_TOKEN = '{{ csrf_token() }}';

    // Konstanta URL yang menunjuk ke Controller
    const ROUTES = {
        get: '{{ route('booking.cart.get') }}',
        manage: '{{ route('booking.cart.manage') }}',
        next: '{{ route('booking.step2') }}'
    };

    // Translations untuk JavaScript
    const TRANS = {
        btnAdd: '{{ __('booking.btn_add') }}',
        btnAdded: '{{ __('booking.btn_added') }}',
        btnAdding: '{{ __('booking.btn_adding') }}',
        btnRemoving: '{{ __('booking.btn_removing') }}',
        cartEmpty: '{{ __('booking.cart_empty') }}',
        cartLoading: '{{ __('booking.cart_loading') }}',
        cartError: '{{ __('booking.cart_error') }}',
        confirmRemove: '{{ __('booking.confirm_remove') }}',
        errorIdNotFound: '{{ __('booking.error_id_not_found') }}',
        errorCartProcess: '{{ __('booking.error_cart_process') }}',
        errorRequestFailed: '{{ __('booking.error_request_failed') }}',
        errorLoadItems: '{{ __('booking.error_load_items') }}',
        duration: '{{ __('booking.duration') }}',
        minutes: '{{ __('booking.minutes') }}'
    };

    // Data Layanan Awal (Dilemparkan dari Controller)
    const SERVICES = @json($services);
    const SERVICE_MAP = SERVICES.reduce((acc, s) => {
        acc[s.id_layanan] = s;
        return acc;
    }, {});

    // Data Cart yang disinkronkan dengan DB/Session
    let currentCart = [];

    const nf = n => new Intl.NumberFormat('id-ID').format(n);

    // --- Helper Fungsi AJAX ---

    async function fetchCart(method = 'GET', url = ROUTES.get, data = null) {
        const isManage = url.includes(ROUTES.manage);
        if (isManage) document.body.style.cursor = 'wait';

        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const responseText = await response.text();

            if (!response.ok) {
                console.error('Server returned non-OK status:', response.status);
                document.body.style.cursor = 'default';
                try {
                    const errorJson = JSON.parse(responseText);
                    throw new Error(errorJson.message || `${TRANS.errorRequestFailed}: ${response.status}`);
                } catch (e) {
                    throw new Error(`${TRANS.errorRequestFailed}. Status: ${response.status}`);
                }
            }

            document.body.style.cursor = 'default';
            return JSON.parse(responseText);

        } catch (error) {
            console.error('AJAX Error:', error);
            alert(`${TRANS.errorCartProcess}: ${error.message || 'Unknown Error'}`);
            document.body.style.cursor = 'default';
            return null;
        }
    }


    // --- Cart Actions ---

    async function handleCartAction(id_layanan) {
        const itemInCart = currentCart.find(item => item.id_layanan === id_layanan);

        const btn = document.getElementById(`btn-add-${id_layanan}`);
        if (btn) btn.disabled = true;

        const action = itemInCart ? 'remove' : 'add';
        const id_reservasi_to_manage = itemInCart ? itemInCart.reservasi.id_reservasi : null;

        await updateCart(id_layanan, action, id_reservasi_to_manage);
    }

async function updateCart(id_layanan, action, id_reservasi = null) {
    const data = {
        id_layanan: id_layanan,
        action: action,
        id_reservasi: id_reservasi
    };

    const btn = document.getElementById(`btn-add-${id_layanan}`);
    let originalText = btn ? btn.textContent : (action === 'remove' ? TRANS.btnAdded : TRANS.btnAdd);

    if (btn) {
        btn.textContent = (action === 'add' ? TRANS.btnAdding : TRANS.btnRemoving);
        btn.disabled = true;
    }

    const result = await fetchCart('POST', ROUTES.manage, data);

    if (btn && !result) {
        btn.textContent = originalText;
        btn.disabled = false;
    }

    if (result && result.success) {
        currentCart = Array.isArray(result.cart) ? result.cart : [];
        renderCart();
        
        // Tampilkan notifikasi sukses
        if (action === 'remove') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Layanan berhasil dihapus dari keranjang',
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else if (action === 'add') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Layanan berhasil ditambahkan ke keranjang',
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    } else if (result === null) {
        await initCart();
    }
}

async function removeFromCart(id_layanan, id_reservasi) {
    if (!id_layanan || !id_reservasi) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: TRANS.errorIdNotFound,
            confirmButtonColor: '#ff6b9e'
        });
        return;
    }

    const result = await Swal.fire({
        title: 'Konfirmasi',
        text: TRANS.confirmRemove,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ff6b9e',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
        await updateCart(id_layanan, 'remove', id_reservasi);
    }
}

    // --- Render dan Init ---

    function renderCart() {
        const list = document.getElementById('cartList');
        const nextBtn = document.getElementById('nextBtn');
        const idsInCart = currentCart.map(item => item.id_layanan);
        let subtotal = 0;

        // 1. Update tombol di daftar layanan
        document.querySelectorAll('.service-row').forEach(row => {
            const id = parseInt(row.dataset.serviceId);
            const btn = document.getElementById(`btn-add-${id}`);
            const isSelected = idsInCart.includes(id);

            if (btn) {
                if (isSelected) {
                    row.classList.add('in-cart');
                    btn.textContent = TRANS.btnAdded;
                    btn.classList.add('btn-added');
                } else {
                    row.classList.remove('in-cart');
                    btn.textContent = TRANS.btnAdd;
                    btn.classList.remove('btn-added');
                }
                btn.disabled = false;
            }
        });

        // 2. Update Sidebar Cart List
        if (currentCart.length === 0) {
            list.innerHTML = `<div style="text-align:center;color:#888;padding:20px">${TRANS.cartEmpty}</div>`;
            document.getElementById('subtotal').textContent = 'Rp 0';
            nextBtn.classList.add('disabled');
            return;
        }

        let html = '';
        currentCart.sort((a, b) => a.id_layanan - b.id_layanan);

        for (const it of currentCart) {
            const id_layanan = it.id_layanan;
            const id_reservasi = it.reservasi && it.reservasi.id_reservasi ? it.reservasi.id_reservasi : null;

            if (!id_reservasi) continue;

            const itemPrice = it.harga || SERVICE_MAP[id_layanan]?.harga || 0;
            const duration = it.durasi || SERVICE_MAP[id_layanan]?.durasi || '-';

            subtotal += itemPrice;

            html += `
                <div class="booking-card" data-id="${id_layanan}" data-reservasi-id="${id_reservasi}">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <h4 class="booking-card-title">${it.nama_layanan}</h4>
                        <button class="btn-remove" onclick="removeFromCart(${id_layanan}, ${id_reservasi})" type="button">×</button>
                    </div>
                    <div style="color:#777;font-size:13px">${TRANS.duration}: ${duration} ${TRANS.minutes}</div>
                    <div style="color:#ff6b9e;font-weight:800">Rp ${nf(itemPrice)}</div>
                </div>`;
        }

        if (subtotal === 0 && currentCart.length > 0) {
            list.innerHTML = `<div style="text-align:center;color:#c33;padding:20px">${TRANS.errorLoadItems}</div>`;
        } else {
            list.innerHTML = html;
        }

        document.getElementById('subtotal').textContent = 'Rp ' + nf(subtotal);
        if (subtotal > 0) {
            nextBtn.classList.remove('disabled');
        } else {
            nextBtn.classList.add('disabled');
        }

        nextBtn.href = ROUTES.next;
    }

    async function initCart() {
        document.getElementById('cartList').innerHTML = `<div style="text-align:center;color:#888;padding:20px">${TRANS.cartLoading}</div>`;

        const result = await fetchCart('GET', ROUTES.get);
        if (result && result.success) {
            currentCart = Array.isArray(result.cart) ? result.cart : [];
            renderCart();
        } else {
            document.getElementById('cartList').innerHTML = `<div style="text-align:center;color:#c33;padding:20px">${TRANS.cartError}</div>`;
            document.getElementById('nextBtn').classList.add('disabled');
        }
    }

    document.addEventListener('DOMContentLoaded', initCart);

    // Ekspor fungsi ke window
    window.handleCartAction = handleCartAction;
    window.removeFromCart = removeFromCart;
</script>
@endsection
