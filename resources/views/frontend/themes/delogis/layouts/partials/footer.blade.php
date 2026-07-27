@php
    $dg = rtrim(asset('themes/delogis'), '/');
    $footerNav = ! empty($doktor['menu']) && is_array($doktor['menu'])
        ? collect($doktor['menu'])->filter(fn ($i) => ($i['key'] ?? '') !== 'anasayfa')->map(fn ($item) => [
            'href' => function_exists('nav_href') ? nav_href($item) : ($item['href'] ?? '#'),
            'label' => $item['label'] ?? '',
        ])->values()->all()
        : [
            ['href' => route('frontend.hakkimda'), 'label' => 'Hakkımda'],
            ['href' => route('frontend.hizmetler'), 'label' => 'Hizmetler'],
            ['href' => route('frontend.blog'), 'label' => 'Blog'],
            ['href' => route('frontend.iletisim'), 'label' => 'İletişim'],
        ];
    $sosyal = array_filter($doktor['sosyal'] ?? [], fn ($u) => filled($u));
    $tel = $doktor['telefon'] ?? null;
    $telRaw = $doktor['telefon_raw'] ?? preg_replace('/\D+/', '', (string) $tel);
    $eposta = $doktor['e_posta'] ?? null;
    $adres = $doktor['adres'] ?? trim(($doktor['ilce'] ?? '').' '.($doktor['il'] ?? ''));
    $adSoyad = trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim'));
    $logo = $doktor['logo'] ?? null;
    $cs = $doktor['calisma_saatleri'] ?? [];
    $csText = '';
    if (is_array($cs) && $cs !== []) {
        $first = collect($cs)->take(2)->map(fn ($v, $k) => (is_string($k) ? $k.': ' : '').(is_array($v) ? implode('-', $v) : $v))->implode(' · ');
        $csText = $first;
    }
@endphp
<footer class="site-footer">
    <div class="site-footer__shape-1 float-bob-y">
        <img src="{{ $dg }}/images/shapes/site-footer-shape-1.png" alt="">
    </div>
    <div class="site-footer__top">
        <div class="container">
            <div class="site-footer__top-inner">
                <div class="site-footer__top-left">
                    <div class="site-footer__top-icon">
                        <span class="icon-business-people"></span>
                    </div>
                    <div class="site-footer__top-content">
                        <h3>
                            Çalışma saatleri:
                            <span>{{ $csText !== '' ? $csText : 'Randevu ile' }}</span>
                        </h3>
                    </div>
                </div>
                <div class="site-footer__top-right">
                    @if(count($sosyal))
                        <div class="site-footer__social-title"><p>Takip edin:</p></div>
                        <div class="site-footer__social">
                            @foreach ($sosyal as $key => $url)
                                @php
                                    $icon = match (strtolower((string) $key)) {
                                        'twitter', 'x' => 'fab fa-twitter',
                                        'facebook' => 'fab fa-facebook',
                                        'instagram' => 'fab fa-instagram',
                                        'linkedin' => 'fab fa-linkedin-in',
                                        'youtube' => 'fab fa-youtube',
                                        default => 'fas fa-link',
                                    };
                                @endphp
                                <a href="{{ $url }}" target="_blank" rel="noopener"><i class="{{ $icon }}"></i></a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="site-footer__middle">
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="100ms">
                    <div class="footer-widget__column footer-widget__about">
                        <div class="footer-widget__logo">
                            <a href="{{ route('frontend.anasayfa') }}">
                                @if($logo)
                                    <img src="{{ $logo }}" alt="{{ $adSoyad }}" style="max-height:48px;width:auto">
                                @else
                                    <strong style="color:#fff;font-size:1.2rem">{{ $adSoyad }}</strong>
                                @endif
                            </a>
                        </div>
                        <p class="footer-widget__about-text">
                            {{ \Illuminate\Support\Str::limit(strip_tags((string) ($doktor['kisa_bio'] ?? $doktor['slogan'] ?? 'Güvenilir, kişiye özel sağlık hizmeti.')), 140) }}
                        </p>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="200ms">
                    <div class="footer-widget__column footer-widget__link">
                        <div class="footer-widget__title-box">
                            <h3 class="footer-widget__title">Keşfet</h3>
                        </div>
                        <ul class="footer-widget__link-list list-unstyled">
                            @foreach ($footerNav as $item)
                                <li><a href="{{ $item['href'] }}">{{ $item['label'] }}</a></li>
                            @endforeach
                            <li><a href="{{ route('frontend.randevu') }}">Randevu Al</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="300ms">
                    <div class="footer-widget__column footer-widget__Contact">
                        <div class="footer-widget__title-box">
                            <h3 class="footer-widget__title">İletişim</h3>
                        </div>
                        <ul class="footer-widget__Contact-list list-unstyled">
                            @if($adres)
                                <li>
                                    <div class="icon"><span class="fas fa-map-marker"></span></div>
                                    <div class="text"><span>Adres</span><p>{{ $adres }}</p></div>
                                </li>
                            @endif
                            @if($eposta)
                                <li>
                                    <div class="icon"><span class="fas fa-envelope"></span></div>
                                    <div class="text"><span>E-posta</span><p><a href="mailto:{{ $eposta }}">{{ $eposta }}</a></p></div>
                                </li>
                            @endif
                            @if($tel)
                                <li>
                                    <div class="icon"><span class="fas fa-phone-square"></span></div>
                                    <div class="text"><span>Telefon</span><p><a href="tel:{{ $telRaw }}">{{ $tel }}</a></p></div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="400ms">
                    <div class="footer-widget__column footer-widget__newsletter">
                        <div class="footer-widget__title-box">
                            <h3 class="footer-widget__title">Randevu</h3>
                        </div>
                        <p class="footer-widget__newsletter-text">Online randevu alın; size en uygun saati seçin.</p>
                        <a href="{{ route('frontend.randevu') }}" class="thm-btn">Randevu Al</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="site-footer__bottom">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="site-footer__bottom-inner">
                        <p class="site-footer__bottom-text">
                            © {{ date('Y') }} <a href="{{ route('frontend.anasayfa') }}">{{ $adSoyad }}</a>
                            · Randevu Ajandam
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
