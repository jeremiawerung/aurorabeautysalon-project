@extends('layouts.pelanggan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">

<div class="contact-section">
    <!-- Hero Section -->
    <div class="contact-hero">
        <div class="contact-hero-label">{{ __('contact.hero.label') }}</div>
        <h1 class="contact-hero-title">{{ __('contact.hero.title') }}</h1>
        <div class="contact-hero-time">{{ __('contact.hero.time') }}</div>
    </div>

    <!-- Content Section -->
    <div class="contact-content-wrapper">
        <div class="contact-content">
            <!-- Image Section -->
            <div class="contact-image-container">
                <div class="contact-image-inner">
                    <img src="{{ asset('img/contact-left-image.png') }}" alt="{{ __('contact.hero.alt') }}" class="contact-image">
                </div>
            </div>

            <!-- Info Section -->
            <div class="contact-info-container">
                <div class="contact-info-label">{{ __('contact.info.label') }}</div>
                <h2 class="contact-info-title">{{ __('contact.info.title') }}</h2>
                <p class="contact-info-description">
                    {{ __('contact.info.desc') }}
                </p>

                <div class="contact-details-list">
                    <!-- Visit Us -->
                    <div class="contact-detail-item">
                        <div class="contact-icon-box">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                            </svg>
                        </div>
                        <div class="contact-detail-text">
                            <div class="contact-detail-label">{{ __('contact.details.visit_label') }}</div>
                            <div class="contact-detail-value">{{ __('contact.details.visit_value') }}</div>
                        </div>
                    </div>

                    <!-- Drop Us -->
                    <div class="contact-detail-item">
                        <div class="contact-icon-box">
                            <svg viewBox="0 0 24 24">
                                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
                            </svg>
                        </div>
                        <div class="contact-detail-text">
                            <div class="contact-detail-label">{{ __('contact.details.drop_label') }}</div>
                            <div class="contact-detail-value">{{ __('contact.details.drop_value') }}</div>
                        </div>
                    </div>

                    <!-- Call Us -->
                    <div class="contact-detail-item">
                        <div class="contact-icon-box">
                            <svg viewBox="0 0 24 24">
                                <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z" />
                            </svg>
                        </div>
                        <div class="contact-detail-text">
                            <div class="contact-detail-label">{{ __('contact.details.call_label') }}</div>
                            <div class="contact-detail-value">{{ __('contact.details.call_value') }}</div>
                        </div>
                    </div>
                </div>
            </div> <!-- /info -->
        </div>
    </div>
</div>

<!-- Form Section -->
<div class="contact-form-wrapper">
    <div class="contact-form-header">
        <div class="contact-form-label">{{ __('contact.form.header_label') }}</div>
        <h2 class="contact-form-title">{{ __('contact.form.header_title') }}</h2>
        <p class="contact-form-description">
            {{ __('contact.form.header_desc') }}
        </p>
    </div>

    <div class="contact-form-card">
        <form class="contact-form" method="POST" action="{{ route('contact.send') }}">
            @csrf
            <div class="form-group">
                <div class="form-icon">
                    <!-- icon -->
                </div>
                <input
                    type="text"
                    name="name"
                    class="form-input"
                    value="{{ old('name') }}"
                    placeholder="{{ __('contact.form.name') }}"
                    required
                >
            </div>

            <div class="form-group">
                <div class="form-icon">
                    <!-- icon -->
                </div>
                <input
                    type="email"
                    name="email"
                    class="form-input"
                    value="{{ old('email') }}"
                    placeholder="{{ __('contact.form.email') }}"
                    required
                >
            </div>

            <div class="form-group">
                <div class="form-icon">
                    <!-- icon -->
                </div>
                <input
                    type="tel"
                    name="phone"
                    class="form-input"
                    value="{{ old('phone') }}"
                    placeholder="{{ __('contact.form.phone') }}"
                    required
                >
            </div>

            <div class="form-group">
                <div class="form-icon">
                    <!-- icon -->
                </div>
                <input
                    type="text"
                    name="services"
                    class="form-input"
                    value="{{ old('services') }}"
                    placeholder="{{ __('contact.form.services') }}"
                    required
                >
            </div>

            <div class="form-group form-group-full">
                <div class="form-icon">
                    <!-- icon -->
                </div>
                <textarea
                    name="message"
                    class="form-input form-textarea"
                    placeholder="{{ __('contact.form.note') }}"
                    rows="4"
                    required
                >{{ old('message') }}</textarea>
            </div>

            <button type="submit" class="form-submit-btn">
                {{ __('contact.form.submit') }}
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('success')),
                    confirmButtonColor: '#d13a8a'
                });
            @endif

            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan. Mohon periksa kembali data yang Anda isi.',
                    confirmButtonColor: '#d13a8a'
                });
            @endif
        });
    </script>
@endpush
