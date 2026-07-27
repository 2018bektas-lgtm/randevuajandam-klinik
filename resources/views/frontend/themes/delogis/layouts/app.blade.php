<!DOCTYPE html>
<html lang="tr">
<head>
    @include('frontend.themes.delogis.layouts.partials.head')
</head>
@php
    $nav = site_nav(is_array($doktor ?? null) ? $doktor : null);
    $dg = rtrim(asset('themes/delogis'), '/');
    $adSoyad = trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim'));
    $tel = $doktor['telefon'] ?? null;
    $telRaw = $doktor['telefon_raw'] ?? preg_replace('/\D+/', '', (string) $tel);
    $eposta = $doktor['e_posta'] ?? null;
    $logo = $doktor['logo'] ?? null;
    $sosyal = array_filter($doktor['sosyal'] ?? [], fn ($u) => filled($u));
@endphp
<body class="custom-cursor theme-delogis layout-delogis theme-pack-delogis">
    @include('frontend.layouts.partials.tracking-body')

    <div class="custom-cursor__cursor"></div>
    <div class="custom-cursor__cursor-two"></div>

    <div class="preloader" style="display:none">
        <div class="preloader__image" style="background-image:url({{ $dg }}/images/loader.png)"></div>
    </div>

    <div class="page-wrapper">
        @include('frontend.themes.delogis.layouts.partials.header', ['doktor' => $doktor ?? [], 'nav' => $nav])

        @yield('icerik')

        @include('frontend.themes.delogis.layouts.partials.footer', ['doktor' => $doktor ?? [], 'nav' => $nav])
    </div>

    <div class="mobile-nav__wrapper">
        <div class="mobile-nav__overlay mobile-nav__toggler"></div>
        <div class="mobile-nav__content">
            <span class="mobile-nav__close mobile-nav__toggler"><i class="fa fa-times"></i></span>
            <div class="logo-box">
                <a href="{{ route('frontend.anasayfa') }}" aria-label="logo">
                    @if($logo)
                        <img src="{{ $logo }}" width="135" alt="{{ $adSoyad }}">
                    @else
                        <strong style="color:#fff">{{ $adSoyad }}</strong>
                    @endif
                </a>
            </div>
            <div class="mobile-nav__container"></div>
            <ul class="mobile-nav__contact list-unstyled">
                @if($eposta)
                    <li><i class="fa fa-envelope"></i> <a href="mailto:{{ $eposta }}">{{ $eposta }}</a></li>
                @endif
                @if($tel)
                    <li><i class="fa fa-phone-alt"></i> <a href="tel:{{ $telRaw }}">{{ $tel }}</a></li>
                @endif
            </ul>
            @if(count($sosyal))
                <div class="mobile-nav__top">
                    <div class="mobile-nav__social">
                        @foreach ($sosyal as $key => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="fab fa-{{ str_contains(strtolower((string)$key),'insta') ? 'instagram' : (str_contains(strtolower((string)$key),'face') ? 'facebook-square' : 'link') }}"></a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <a href="#" data-target="html" class="scroll-to-target scroll-to-top"><i class="icon-up-arrow"></i></a>

    @include('frontend.themes.delogis.layouts.partials.assets-js')
    @stack('scripts')
</body>
</html>
