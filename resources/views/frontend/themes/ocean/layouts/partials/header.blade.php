@php
    if (! isset($nav) || ! is_array($nav)) {
        $nav = function_exists('site_nav') ? site_nav(isset($doktor) && is_array($doktor) ? $doktor : null) : [];
    }
@endphp
{{-- Ocean: sol dikey marka şeridi + üst menü --}}
<header class="th-ocean-header" id="site-header">
    <div class="th-ocean-rail" aria-hidden="true">
        <span>{{ $doktor['uzmanlik'] ?? 'Klinik' }}</span>
    </div>
    <div class="th-ocean-top">
        <a href="{{ route('frontend.anasayfa') }}" class="th-ocean-brand">
            <strong>{{ trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim')) }}</strong>
        </a>
        <nav class="th-ocean-nav">
            @foreach ($nav as $item)
                @php $active = !empty($item['match']) && request()->routeIs($item['match']); @endphp
                <a href="{{ $item['href'] }}" class="{{ $active ? 'is-active' : '' }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <a href="{{ route('frontend.randevu') }}" class="th-ocean-cta">Randevu</a>
        <button type="button" class="menu-toggle" id="mobile-menu-btn" aria-label="Menü">☰</button>
    </div>
    <div class="mobile-nav" id="mobile-menu">
        @foreach ($nav as $item)
            <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
        @endforeach
    </div>
</header>
