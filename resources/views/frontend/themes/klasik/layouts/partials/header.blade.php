@php $nav = site_nav($doktor ?? null); @endphp
@php
    $matchMap = [
        'anasayfa' => 'frontend.anasayfa',
        'hakkimda' => 'frontend.hakkimda',
        'hekimler' => 'frontend.hekim*',
        'hizmetler' => 'frontend.hizmet*',
        'galeri' => 'frontend.galeri',
        'blog' => 'frontend.blog*',
        'sss' => 'frontend.sss',
        'iletisim' => 'frontend.iletisim',
    ];
    $klinikAd = $doktor['klinik_adi'] ?? trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Klinik'));
    if (! empty($doktor['menu']) && is_array($doktor['menu'])) {
        $nav = collect($doktor['menu'])->map(function ($item) use ($matchMap) {
            $key = $item['key'] ?? '';
            $href = nav_href($item);
            $isExternal = filled($item['url'] ?? null)
                && (str_starts_with($item['url'], 'http') || str_starts_with($item['url'], '//'));

            return [
                'href' => $href,
                'label' => $item['label'] ?? $key,
                'match' => $matchMap[$key] ?? ($item['route'] ?? null),
                'external' => $isExternal,
            ];
        })->values()->all();
    } else {
        $nav = [
            ['href' => route('frontend.anasayfa'), 'label' => 'Ana Sayfa', 'match' => 'frontend.anasayfa', 'external' => false],
            ['href' => route('frontend.hakkimda'), 'label' => 'Hakkımızda', 'match' => 'frontend.hakkimda', 'external' => false],
            ['href' => route('frontend.hekimler'), 'label' => 'Hekimlerimiz', 'match' => 'frontend.hekim*', 'external' => false],
            ['href' => route('frontend.hizmetler'), 'label' => 'Hizmetler', 'match' => 'frontend.hizmet*', 'external' => false],
            ['href' => route('frontend.galeri'), 'label' => 'Galeri', 'match' => 'frontend.galeri', 'external' => false],
            ['href' => route('frontend.blog'), 'label' => 'Blog', 'match' => 'frontend.blog*', 'external' => false],
            ['href' => route('frontend.sss'), 'label' => 'S.S.S.', 'match' => 'frontend.sss', 'external' => false],
            ['href' => route('frontend.iletisim'), 'label' => 'İletişim', 'match' => 'frontend.iletisim', 'external' => false],
        ];
    }
@endphp

<div class="topbar">
    <div class="container">
        <div>{{ $doktor['adres'] ?? $klinikAd }}</div>
        <div class="topbar-right">
            @if(!empty($doktor['telefon']))
                <a href="tel:{{ $doktor['telefon_raw'] ?? '' }}">{{ $doktor['telefon'] }}</a>
            @endif
            @if(!empty($doktor['e_posta']))
                <a href="mailto:{{ $doktor['e_posta'] }}">{{ $doktor['e_posta'] }}</a>
            @endif
            @if(!empty($doktor['api_synced']))
                <span>Klinik · canlı platform verisi</span>
            @elseif(!empty($doktor['api_error']))
                <span style="color:#fecaca">API bağlantı sorunu</span>
            @else
                <span>Randevu ile</span>
            @endif
        </div>
    </div>
</div>

<header class="site-header" id="site-header">
    <div class="header-inner">
        <a href="{{ route('frontend.anasayfa') }}" class="brand {{ !empty($doktor['logo']) ? 'has-logo' : '' }}">
            @if(!empty($doktor['logo']))
                <img src="{{ $doktor['logo'] }}" alt="{{ $klinikAd }}" class="brand-logo">
            @else
                <span class="brand-mark">{{ mb_substr($klinikAd, 0, 1) }}</span>
            @endif
            <span class="brand-text">
                <strong>{{ $klinikAd }}</strong>
                <span>{{ $doktor['uzmanlik'] ?? 'Sağlık Kliniği' }}</span>
            </span>
        </a>

        <nav class="nav-desktop" aria-label="Ana menü">
            @include('frontend.layouts.partials.nav-items', ['nav' => $nav, 'mode' => 'desktop'])
        </nav>

        <div class="header-actions">
            @if($doktor['hekim_girisi_goster'] ?? true)
                <a href="{{ route('panel.giris') }}" class="btn btn-dark-outline btn-sm hidden sm:inline-flex">Yönetim</a>
            @endif
            <a href="{{ route('frontend.randevu') }}" class="btn btn-primary btn-sm">Randevu Al</a>
            <button type="button" class="menu-toggle" id="mobile-menu-btn" aria-label="Menüyü aç">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="mobile-nav" id="mobile-menu">
        @include('frontend.layouts.partials.nav-items', ['nav' => $nav, 'mode' => 'mobile'])
        <a href="{{ route('frontend.randevu') }}" class="btn btn-primary" style="margin:.5rem 0 0;width:100%">Online Randevu</a>
    </div>
</header>
