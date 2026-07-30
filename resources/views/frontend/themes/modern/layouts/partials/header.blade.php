@php
    if (! isset($nav) || ! is_array($nav)) {
        $nav = function_exists('site_nav') ? site_nav(isset($doktor) && is_array($doktor) ? $doktor : null) : [];
    }
@endphp
{{-- Modern Tech: koyu sticky bar, topbar yok, pill nav --}}
<header class="th-modern-header" id="site-header">
    <div class="th-modern-inner">
        <a href="{{ route('frontend.anasayfa') }}" class="th-modern-brand">
            @if(!empty($doktor['logo']))
                <img src="{{ $doktor['logo'] }}" alt="" class="th-modern-logo">
            @else
                <span class="th-modern-mark">{{ mb_strtoupper(mb_substr($doktor['ad_soyad'] ?? 'H', 0, 1)) }}</span>
            @endif
            <span>
                <strong>{{ trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim')) }}</strong>
                <small>{{ $doktor['uzmanlik'] ?? 'Klinik' }}</small>
            </span>
        </a>
        <nav class="th-modern-nav nav-desktop" aria-label="Ana menü">
            @include('frontend.layouts.partials.nav-items', ['nav' => $nav, 'mode' => 'desktop'])
        </nav>
        <div class="th-modern-actions">
            <a href="{{ route('frontend.randevu') }}" class="th-modern-cta">Randevu</a>
            <button type="button" class="menu-toggle th-modern-menu" id="mobile-menu-btn" aria-label="Menü">☰</button>
        </div>
    </div>
    <div class="mobile-nav th-modern-mobile" id="mobile-menu">
        @include('frontend.layouts.partials.nav-items', ['nav' => $nav, 'mode' => 'mobile'])
        <a href="{{ route('frontend.randevu') }}" class="btn btn-primary" style="margin-top:.5rem">Online Randevu</a>
    </div>
</header>
