{{-- Pilihan bahasa di panel menu (versi HP gak punya dropdown bahasa di header) --}}
<div class="profile-menu-item" style="cursor: default;">
    <i class="fas fa-globe"></i>
    <span>{{ __('nav.language') }}:</span>
    <a href="{{ route('lang.switch', 'id') }}"
       style="margin-left: 8px; {{ app()->getLocale() === 'id' ? 'font-weight: 700; text-decoration: underline;' : '' }}">ID</a>
    <span style="margin: 0 4px;">|</span>
    <a href="{{ route('lang.switch', 'en') }}"
       style="{{ app()->getLocale() === 'en' ? 'font-weight: 700; text-decoration: underline;' : '' }}">ENG</a>
</div>
