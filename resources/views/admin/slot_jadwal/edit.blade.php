@extends('layouts.app')

@section('title', 'Edit Slot Jadwal - Aurora')

@section('content')
<style>
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
    .card-body {
        padding: 1.25rem;
    }
    .card-footer {
        background-color: #fff;
        border-top: 1px solid #f1b8d6 !important;
        padding: 1rem 1.25rem;
    }
    .content-wrapper { 
        padding: 20px;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    .form-control, .form-select {
        border: 1.5px solid #f1b8d6;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        transition: all 0.2s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #e673ae;
        box-shadow: 0 0 0 0.25rem rgba(241, 184, 214, 0.25);
        outline: none;
    }

    .info-box {
        background-color: #f8f9fa;
        border-left: 4px solid #e91e63;
        padding: 1rem;
        border-radius: 0.375rem;
        margin-top: 1rem;
    }
    .info-box small {
        font-size: 0.8rem;
        color: #6c757d;
        display: block;
        line-height: 1.6;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f1f1f1;
    }
    .section-header h6 {
        margin: 0;
        font-weight: 600;
        color: #495057;
        font-size: 1rem;
    }

    .btn-add-slot {
        background-color: #fff;
        border: 2px solid #e91e63;
        color: #e91e63;
        font-weight: 600;
        border-radius: 0.375rem;
        padding: 0.375rem 1rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    .btn-add-slot:hover {
        background-color: #e91e63;
        color: #fff;
    }

    .btn-simpan-pink {
        background-color: #fff;
        border: 2px solid #dc3545;
        color: #dc3545;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.375rem 1.25rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    .btn-simpan-pink:hover {
        background-color: #dc3545;
        color: #fff;
    }
    .btn-batal {
        background-color: #fff;
        border: 2px solid #6c757d;
        color: #6c757d;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.375rem 1.25rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    .btn-batal:hover {
        background-color: #6c757d;
        color: #fff;
    }

    .slot-item {
        background-color: #fff;
        border: 1.5px solid #f1b8d6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }
    .slot-item:hover {
        box-shadow: 0 2px 8px rgba(241, 184, 214, 0.3);
        border-color: #e673ae;
    }

    .btn-remove-slot {
        background-color: #fff;
        border: 1.5px solid #dc3545;
        color: #dc3545;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-remove-slot:hover:not(:disabled) {
        background-color: #dc3545;
        color: #fff;
    }
    .btn-remove-slot:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .content-wrapper {
            padding: 15px;
        }
        .card-header, .card-body, .card-footer {
            padding: 1rem;
        }
        .slot-item {
            padding: 0.75rem;
        }
        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .section-header .btn-add-slot {
            width: 100%;
        }
        .btn-remove-slot {
            width: 100%;
            margin-top: 0.5rem;
        }
        .card-footer {
            padding: 1rem;
        }
        .card-footer .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        .card-footer .btn:last-child {
            margin-bottom: 0;
        }
    }

    @media (max-width: 576px) {
        .slot-item .row > div {
            margin-bottom: 0.75rem;
        }
        .slot-item .row > div:last-child {
            margin-bottom: 0;
        }
    }



        /* Select2 Custom Styling */
        .select2-container--default .select2-selection--single {
            border: 1.5px solid #f1b8d6 !important;
            border-radius: 0.375rem !important;
            height: auto !important;
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #495057 !important;
            line-height: normal !important;
            padding: 0 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 8px !important;
        }

        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #e673ae !important;
            box-shadow: 0 0 0 0.25rem rgba(241, 184, 214, 0.25) !important;
            outline: none !important;
        }

        .select2-dropdown {
            border: 1.5px solid #e673ae !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #e91e63 !important;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #f8f9fa !important;
            color: #495057 !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1.5px solid #f1b8d6 !important;
            border-radius: 0.375rem !important;
            padding: 0.5rem !important;
            font-size: 0.875rem !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #e673ae !important;
            outline: none !important;
        }
</style>

<div class="content-wrapper">
    <div class="container-fluid">

        <form id="form-slot-jadwal-edit" action="{{ route('slot-jadwal.update', $slotJadwal->id_slot) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card mb-4">
                <div class="card-header d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center">
                    <h5 class="mb-2 mb-md-0">
                        <i class="bi bi-pencil-square me-2"></i>Edit Slot Jadwal
                    </h5>
                    <a href="{{ route('slot-jadwal.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <!-- Pilih Layanan -->
                    <div class="mb-4">
                        <label for="id_layanan" class="form-label">
                            <i class="bi bi-list-ul me-1"></i>Nama Layanan
                        </label>
                        <select class="form-select" id="id_layanan" name="id_layanan" required>
                            @foreach($layanan as $l)
                                <option value="{{ $l->id_layanan }}"
                                    data-durasi="{{ $l->durasi }}"
                                    {{ $slotJadwal->id_layanan == $l->id_layanan ? 'selected' : '' }}>
                                    {{ $l->nama_layanan }} ({{ $l->durasi }} menit)
                                </option>
                            @endforeach
                        </select>

                        <div class="info-box">
                            <small id="durasi-info">
                                <i class="bi bi-clock me-1"></i><strong>Durasi:</strong> <span id="durasi-value">{{ $slotJadwal->layanan->durasi ?? 0 }}</span> menit
                            </small>
                        </div>
                    </div>

                    <hr>

                    <!-- Daftar Slot -->
                    <div class="section-header">
                        <h6><i class="bi bi-clock-fill me-2"></i>Daftar Slot Waktu</h6>
                        <button type="button" id="btn-add-slot" class="btn btn-add-slot">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Slot
                        </button>
                    </div>

                    <div id="slot-container">
                        @forelse($slots as $index => $s)
                            <div class="slot-item">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5 col-12">
                                        <label class="form-label">
                                            <i class="bi bi-clock me-1"></i>Waktu
                                        </label>
                                        <input type="time" class="form-control waktu-input" name="waktu[]"
                                               value="{{ \Carbon\Carbon::parse($s->waktu)->format('H:i') }}" required>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <label class="form-label">
                                            <i class="bi bi-toggle-on me-1"></i>Status
                                        </label>
                                        <select class="form-select" name="status_slot[]" required>
                                            <option value="aktif" {{ $s->status_slot == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                            <option value="non-aktif" {{ $s->status_slot == 'non-aktif' ? 'selected' : '' }}>Non-Aktif</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <button type="button" class="btn btn-remove-slot w-100">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="slot-item">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5 col-12">
                                        <label class="form-label">
                                            <i class="bi bi-clock me-1"></i>Waktu
                                        </label>
                                        <input type="time" class="form-control waktu-input" name="waktu[]" required>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <label class="form-label">
                                            <i class="bi bi-toggle-on me-1"></i>Status
                                        </label>
                                        <select class="form-select" name="status_slot[]" required>
                                            <option value="aktif" selected>Aktif</option>
                                            <option value="non-aktif">Non-Aktif</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <button type="button" class="btn btn-remove-slot w-100">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="alert alert-info d-flex align-items-start" style="font-size: 0.8rem; margin-top: 1rem;">
                        <i class="bi bi-info-circle me-2" style="font-size: 1.1rem;"></i>
                        <div>
                            <strong>Perhatian:</strong> Mengubah daftar slot akan mengganti semua slot waktu untuk layanan ini.
                            Tidak boleh ada slot dengan waktu yang sama dan jarak antar slot minimal mengikuti durasi layanan.
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex flex-column flex-md-row justify-content-md-end gap-2">
                    <a href="{{ route('slot-jadwal.index') }}" class="btn btn-batal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-simpan-pink">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi Select2
    $('#id_layanan').select2({
        placeholder: '-- Pilih Layanan --',
        allowClear: false,
        width: '100%',
        language: {
            noResults: function() {
                return "Layanan tidak ditemukan";
            },
            searching: function() {
                return "Mencari...";
            }
        }
    });

    const slotContainer = document.getElementById('slot-container');
    const addBtn        = document.getElementById('btn-add-slot');
    const form          = document.getElementById('form-slot-jadwal-edit');
    const layananSelect = document.getElementById('id_layanan');
    const durasiValue   = document.getElementById('durasi-value');

    let durasiMenit = parseInt(layananSelect.options[layananSelect.selectedIndex].dataset.durasi || '0', 10);
    let lastChangedInput = null;

    function updateDurasiInfo() {
        const opt = layananSelect.options[layananSelect.selectedIndex];
        if (opt && opt.dataset.durasi) {
            durasiMenit = parseInt(opt.dataset.durasi, 10) || 0;
            durasiValue.textContent = durasiMenit;
        } else {
            durasiMenit = 0;
            durasiValue.textContent = '-';
        }
    }

    // Event listener untuk Select2 change
    $('#id_layanan').on('change', function() {
        updateDurasiInfo();
    });

    updateDurasiInfo();

    function createSlotRow() {
        const row = document.createElement('div');
        row.className = 'slot-item';

        row.innerHTML = `
            <div class="row g-3 align-items-end">
                <div class="col-md-5 col-12">
                    <label class="form-label">
                        <i class="bi bi-clock me-1"></i>Waktu
                    </label>
                    <input type="time" class="form-control waktu-input" name="waktu[]" required>
                </div>
                <div class="col-md-4 col-12">
                    <label class="form-label">
                        <i class="bi bi-toggle-on me-1"></i>Status
                    </label>
                    <select class="form-select" name="status_slot[]" required>
                        <option value="aktif" selected>Aktif</option>
                        <option value="non-aktif">Non-Aktif</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <button type="button" class="btn btn-remove-slot w-100">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </div>
            </div>
        `;
        return row;
    }

    function refreshRemoveButtonsVisibility() {
        const items = slotContainer.querySelectorAll('.slot-item');
        items.forEach((item) => {
            const btn = item.querySelector('.btn-remove-slot');
            if (btn) {
                if (items.length === 1) {
                    btn.disabled = true;
                    btn.style.opacity = '0.5';
                    btn.style.cursor = 'not-allowed';
                } else {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                }
            }
        });
    }

    addBtn.addEventListener('click', function () {
        const row = createSlotRow();
        slotContainer.appendChild(row);
        refreshRemoveButtonsVisibility();
    });

    slotContainer.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-remove-slot') || e.target.closest('.btn-remove-slot')) {
            const btn = e.target.classList.contains('btn-remove-slot') ? e.target : e.target.closest('.btn-remove-slot');
            const item = btn.closest('.slot-item');
            if (item && !btn.disabled) {
                item.remove();
                refreshRemoveButtonsVisibility();
            }
        }
    });

    function validateSlots(lastInput = null) {
        const inputs = Array.from(slotContainer.querySelectorAll('.waktu-input'));
        const times  = inputs
            .map(i => i.value)
            .filter(v => v && v.length > 0);

        if (times.length === 0) return true;

        const uniqueTimes = Array.from(new Set(times));
        if (uniqueTimes.length !== times.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Slot Duplikat',
                text: 'Tidak boleh ada slot dengan waktu yang sama.',
                confirmButtonColor: '#e91e63'
            }).then(() => {
                if (lastInput) {
                    lastInput.value = '';
                    lastInput.focus();
                }
            });
            return false;
        }

        if (!durasiMenit || durasiMenit <= 0 || uniqueTimes.length <= 1) {
            return true;
        }

        uniqueTimes.sort();
        let prev = null;
        for (const t of uniqueTimes) {
            const [h,m] = t.split(':').map(Number);
            const minutes = h*60 + m;
            if (prev !== null) {
                const diff = minutes - prev;
                if (diff < durasiMenit) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Slot Bertabrakan',
                        text: 'Jarak antar slot minimal ' + durasiMenit + ' menit untuk mencegah bentrokan jadwal.',
                        confirmButtonColor: '#e91e63'
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
    }, 1000);

    slotContainer.addEventListener('input', function (e) {
        if (e.target.classList.contains('waktu-input')) {
            lastChangedInput = e.target;
            validateDebounced();
        }
    });

    form.addEventListener('submit', function (e) {
        const ok = validateSlots();
        if (!ok) {
            e.preventDefault();
        }
    });

    refreshRemoveButtonsVisibility();
});
</script>

@if (session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '{{ session('success') }}',
    timer: 3000,
    showConfirmButton: false,
    confirmButtonColor: '#e91e63'
});
</script>
@endif
@if (session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '{{ session('error') }}',
    confirmButtonColor: '#e91e63'
});
</script>
@endif
@endsection