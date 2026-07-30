@php
    if (! isset($nav) || ! is_array($nav)) {
        $nav = function_exists('site_nav') ? site_nav(isset($doktor) && is_array($doktor) ? $doktor : null) : [];
    }
@endphp
{{-- Minimal: topbar yok, ortalanmış marka, alt menü --}}
<header class="th-min-header" id="site-header">
    <div class="container th-min-inner">
        <a href="{{ route('frontend.anasayfa') }}" class="th-min-brand">
            @if(!empty($doktor['logo']))
                <img src="{{ $doktor['logo'] }}" alt="" class="th-min-logo">
            @endif
            <span class="th-min-name">{{ trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim')) }}</span>
            <span class="th-min-role">{{ $doktor['uzmanlik'] ?? '' }}</span>
        </a>
        <nav class="th-min-nav nav-desktop" aria-label="Ana menü">
            @include('frontend.layouts.partials.nav-items', ['nav' => $nav, 'mode' => 'desktop'])
        </nav>
        <a href="{{ route('frontend.randevu') }}" class="th-min-cta">Randevu</a>
        <button type="button" class="menu-toggle th-min-menu" id="mobile-menu-btn" aria-label="Menü">☰</button>
    </div>
    <div class="mobile-nav" id="mobile-menu">
        @include('frontend.layouts.partials.nav-items', ['nav' => $nav, 'mode' => 'mobile'])
    </div>
</header>
