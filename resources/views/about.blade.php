@extends('layouts.pelanggan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/about.css') }}">

<!-- Hero Section -->
<section class="about-hero">
    <img src="{{ asset('img/bg-about.png') }}" alt="{{ __('about.hero.alt') }}" class="about-hero-image">
    <div class="about-hero-overlay"></div>

    <div class="about-hero-content">
        <div>
            <p class="about-hero-subtitle">{{ __('about.hero.subtitle') }}</p>
            <h1 class="about-hero-title">{!! __('about.hero.title_html') !!}</h1>
            <a href="{{ url('/contact') }}">
                <button class="about-hero-button">{{ __('about.hero.cta') }}</button>
            </a>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="about-values-section" id="values-section">
    <div class="about-values-container">
        <div class="about-values-header">
            <p class="about-values-label">{{ __('about.values.label') }}</p>
            <h2 class="about-values-title">{!! __('about.values.title_html') !!}</h2>
        </div>

        <div class="about-values-list">
            <!-- Value 1 -->
            <div class="about-value-item">
                <div class="about-value-icon-box">
                    <img src="{{ asset('img/about-Icon.png') }}" alt="{{ __('about.values.items.expert.title') }}">
                </div>
                <div class="about-value-content">
                    <h3 class="about-value-title">{{ __('about.values.items.expert.title') }}</h3>
                    <p class="about-value-description">
                        {{ __('about.values.items.expert.desc') }}
                    </p>
                </div>
            </div>

            <!-- Value 2 -->
            <div class="about-value-item">
                <div class="about-value-icon-box">
                    <img src="{{ asset('img/about-Icon-1.png') }}" alt="{{ __('about.values.items.quality.title') }}">
                </div>
                <div class="about-value-content">
                    <h3 class="about-value-title">{{ __('about.values.items.quality.title') }}</h3>
                    <p class="about-value-description">
                        {{ __('about.values.items.quality.desc') }}
                    </p>
                </div>
            </div>

            <!-- Value 3 -->
            <div class="about-value-item">
                <div class="about-value-icon-box">
                    <img src="{{ asset('img/about-Icon-2.png') }}" alt="{{ __('about.values.items.authentic.title') }}">
                </div>
                <div class="about-value-content">
                    <h3 class="about-value-title">{{ __('about.values.items.authentic.title') }}</h3>
                    <p class="about-value-description">
                        {{ __('about.values.items.authentic.desc') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Story Section -->
<section class="about-story-section">
    <div class="about-story-container">
        <div class="about-story-image-wrapper">
            <img src="{{ asset('img/about-pict.png') }}" alt="{{ __('about.story.img_alt') }}" class="about-story-image">
        </div>

        <div class="about-story-content">
            <p class="about-story-label">{{ __('about.story.label') }}</p>
            <h2 class="about-story-title">
                {{ __('about.story.title') }}
            </h2>
            <p class="about-story-description">
                {{ __('about.story.desc') }}
            </p>
        </div>
    </div>
</section>

<!-- Journey Section -->
<section class="about-journey-section">
    <div class="about-journey-container">
        <div class="about-journey-content">
            <p class="about-journey-label">{{ __('about.journey.label') }}</p>
            <h2 class="about-journey-title">{{ __('about.journey.title') }}</h2>
            <p class="about-journey-description">
                {{ __('about.journey.desc') }}
            </p>

            <div class="about-journey-list">
                <div class="about-journey-item">
                    <div class="about-journey-check">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="11" stroke="#D4A5A5" stroke-width="2" />
                            <path d="M7 12L10.5 15.5L17 9" stroke="#D4A5A5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="about-journey-item-content">
                        <h3 class="about-journey-item-title">{{ __('about.journey.items.vision.title') }}</h3>
                        <p class="about-journey-item-text">
                            {{ __('about.journey.items.vision.text') }}
                        </p>
                    </div>
                </div>

                <div class="about-journey-item">
                    <div class="about-journey-check">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="11" stroke="#D4A5A5" stroke-width="2" />
                            <path d="M7 12L10.5 15.5L17 9" stroke="#D4A5A5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="about-journey-item-content">
                        <h3 class="about-journey-item-title">{{ __('about.journey.items.mission.title') }}</h3>
                        <p class="about-journey-item-text">
                            {{ __('about.journey.items.mission.text') }}
                        </p>
                    </div>
                </div>

                <div class="about-journey-item">
                    <div class="about-journey-check">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="11" stroke="#D4A5A5" stroke-width="2" />
                            <path d="M7 12L10.5 15.5L17 9" stroke="#D4A5A5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="about-journey-item-content">
                        <h3 class="about-journey-item-title">{{ __('about.journey.items.motto.title') }}</h3>
                        <p class="about-journey-item-text">
                            {{ __('about.journey.items.motto.text') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="about-journey-media">
            <video class="about-journey-image" controls>
                <source src="{{ asset('storage/Video_Promosi_Salon_Kecantikan.mp4') }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <!-- <div class="about-journey-play-button">
                <svg width="24" height="28" viewBox="0 0 24 28" fill="none">
                    <path d="M22.5 11.134C24.1667 12.0189 24.1667 14.3145 22.5 15.1994L3.75 25.7224C2.08333 26.6073 0 25.4262 0 23.6564V2.67699C0 0.90718 2.08333 -0.273904 3.75 0.611007L22.5 11.134Z" fill="white" />
                </svg>
            </div> -->
        </div>

    </div>
</section>

<script>
    function scrollToValues() {
        const valuesSection = document.getElementById('values-section');
        valuesSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Parallax hero image
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const heroImage = document.querySelector('.about-hero-image');
        if (heroImage && scrolled < window.innerHeight) {
            heroImage.style.transform = 'translateY(' + (scrolled * 0.5) + 'px)';
        }
    });

    // Fade in cards
    function fadeInCards() {
        const items = document.querySelectorAll('.about-value-item');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 150);
                }
            });
        }, { threshold: 0.1 });

        items.forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(30px)';
            item.style.transition = 'all 0.6s ease';
            observer.observe(item);
        });
    }

    document.addEventListener('DOMContentLoaded', fadeInCards);
</script>
@endsection
