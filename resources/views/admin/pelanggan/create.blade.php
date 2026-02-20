@extends('layouts.app')

@section('title', 'Tambah Pelanggan - Aurora')

@section('content')
<style>
    /* CARD & WRAPPER */
    .card {
        border: 1px solid #f1b8d6 !important;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f1b8d6 !important;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
    .content-wrapper { padding: 20px; }

    /* LABEL & INPUT */
    .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
    }
    .form-control {
        border: 1px solid #f1b8d6;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }
    .form-control:focus {
        border-color: #e673ae;
        box-shadow: 0 0 0 0.25rem rgba(241,184,214,0.5);
    }
    .form-control[disabled] {
        background-color: #f8f9fa;
        border-color: #f1b8d6;
    }

    /* TOMBOL */
    .btn-simpan-pink {
        background-color: #e91e63;
        border-color: #e91e63;
        color: #fff;
        font-weight: 600;
        border-radius: 0.375rem;
        padding: 0.5rem 1.25rem;
    }
    .btn-simpan-pink:hover {
        background-color: #d81b60;
        border-color: #d81b60;
    }
    .btn-batal-outline {
        background-color: #fff;
        border: 1px solid #6c757d;
        color: #6c757d;
        font-weight: 600;
        border-radius: 0.375rem;
        padding: 0.5rem 1.25rem;
    }
    .btn-batal-outline:hover {
        background-color: #6c757d;
        color: #fff;
    }

    /* STATUS BUTTON GROUP */
    .status-toggle-group {
        display: flex;
        border-radius: 0.375rem;
        overflow: hidden;
        border: 1px solid #ced4da;
    }
    .status-toggle-group input[type="radio"] {
        display: none;
    }
    .status-btn {
        flex: 1;
        text-align: center;
        padding: 0.5rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        background-color: #f8f9fa;
        color: #6c757d;
        border-left: 1px solid #ced4da;
        transition: all 0.2s;
    }
    .status-btn:first-of-type {
        border-left: none;
    }
    .status-toggle-group input[type="radio"]:checked + .status-btn {
        background-color: #198754;
        color: #fff;
        border-color: #198754;
    }
    #status_nonaktif:checked + .status-btn {
        background-color: #6c757d;
        color: #fff;
        border-color: #6c757d;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">

        {{-- TAMPILKAN PESAN ERROR --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi Kesalahan!</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORM --}}
        <form action="{{ route('pelanggan.store') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center">
                    <h5 class="mb-2 mb-md-0">Form Pelanggan</h5>
                </div>

                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nama" class="form-label">Nama Pelanggan</label>
                            <input type="text" name="nama" id="nama" class="form-control" placeholder="Nama pelanggan..." required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-block">Status</label>
                            <div class="status-toggle-group">
                                <input type="radio" name="status" id="status_aktif" value="aktif" checked>
                                <label for="status_aktif" class="status-btn">Aktif</label>

                                <input type="radio" name="status" id="status_nonaktif" value="non-aktif">
                                <label for="status_nonaktif" class="status-btn">Non-Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="telepon" class="form-label">Nomor Telepon</label>
                            <input type="tel" name="telepon" id="telepon" class="form-control" placeholder="+62 8000-0000-000" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="contoh@email.com" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password <small class="text-muted">(dari pelanggan)</small></label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password..." required>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password..." required>
                        </div>
                    </div>


                </div>

                <div class="card-footer d-flex justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-simpan-pink">Simpan</button>
                        <a href="{{ route('pelanggan.data_pelanggan') }}" class="btn btn-batal-outline ms-2">Kembali</a>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const slotContainer = document.getElementById('slot-container');
    const addBtn        = document.getElementById('btn-add-slot');
    const form          = document.getElementById('form-slot-jadwal-create');
    const layananSelect = document.getElementById('id_layanan');
    const durasiInfo    = document.getElementById('durasi-info');

    let durasiMenit = 0;
    let lastChangedInput = null;

    function updateDurasiInfo() {
        const opt = layananSelect.options[layananSelect.selectedIndex];
        if (opt && opt.dataset.durasi) {
            durasiMenit = parseInt(opt.dataset.durasi, 10) || 0;
            if (durasiMenit > 0) {
                durasiInfo.textContent = 'Durasi layanan: ' + durasiMenit + ' menit.';
            } else {
                durasiInfo.textContent = 'Durasi layanan belum diatur.';
            }
        } else {
            durasiMenit = 0;
            durasiInfo.textContent = 'Pilih layanan untuk melihat durasi.';
        }
    }

    layananSelect.addEventListener('change', updateDurasiInfo);
    updateDurasiInfo();

    function createSlotRow() {
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end slot-item';

        row.innerHTML = `
            <div class="col-8 col-md-4">
                <label class="form-label d-none d-md-block">Waktu</label>
                <input type="time" class="form-control waktu-input" name="waktu[]" required>
            </div>
            <div class="col-4 col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-slot">
                    Hapus
                </button>
            </div>
        `;
        return row;
    }

    function refreshRemoveButtonsVisibility() {
        const items = slotContainer.querySelectorAll('.slot-item');
        items.forEach((item) => {
            const btn = item.querySelector('.btn-remove-slot');
            if (btn) {
                btn.classList.toggle('d-none', items.length === 1);
            }
        });
    }

    addBtn.addEventListener('click', function () {
        const row = createSlotRow();
        slotContainer.appendChild(row);
        refreshRemoveButtonsVisibility();
    });

    slotContainer.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-remove-slot')) {
            const item = e.target.closest('.slot-item');
            if (item) {
                item.remove();
                refreshRemoveButtonsVisibility();
            }
        }
    });

    // --- VALIDASI SLOT DENGAN OPSIONAL "LAST CHANGED INPUT" ---
    function validateSlots(lastInput = null) {
        if (!durasiMenit || durasiMenit <= 0) {
            // Kalau durasi tidak di-set, biarkan (backend tetap cek)
            return true;
        }

        const inputs = Array.from(slotContainer.querySelectorAll('.waktu-input'));
        const times  = inputs
            .map(i => i.value)
            .filter(v => v && v.length > 0);

        if (times.length <= 1) return true;

        const unique = Array.from(new Set(times));
        unique.sort(); // format HH:MM => aman di-sort string

        let prev = null;
        for (const t of unique) {
            const [h,m] = t.split(':').map(Number);
            const minutes = h*60 + m;
            if (prev !== null) {
                const diff = minutes - prev;
                if (diff < durasiMenit) {
                    // Ada tabrakan
                    Swal.fire({
                        icon: 'warning',
                        title: 'Slot bertabrakan',
                        text: 'Jarak antar slot minimal ' + durasiMenit + ' menit.',
                    }).then(() => {
                        if (lastInput) {
                            lastInput.value = '';
                            lastInput.focus();
                        }
                    });
                    return false;
                }
            }
            prev = minutes;
        }
        return true;
    }

    // --- DEBOUNCE HELPER ---
    function debounce(fn, delay) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    const validateDebounced = debounce(() => {
        if (lastChangedInput) {
            validateSlots(lastChangedInput);
        }
    }, 1000); // 1 detik

    // Event real-time ketika user mengetik/mengubah jam
    slotContainer.addEventListener('input', function (e) {
        if (e.target.classList.contains('waktu-input')) {
            lastChangedInput = e.target;
            validateDebounced();
        }
    });

    // Validasi juga ketika submit (fallback)
    form.addEventListener('submit', function (e) {
        const ok = validateSlots(); // tanpa lastInput, tidak clear otomatis
        if (!ok) {
            e.preventDefault();
        }
    });

    // Inisialisasi tombol hapus
    refreshRemoveButtonsVisibility();
});
</script>

@if (session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Sukses!',
    text: '{{ session('success') }}',
    timer: 3000,
    showConfirmButton: false
});
</script>
@endif
@if (session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '{{ session('error') }}'
});
</script>
@endif
@endsection

