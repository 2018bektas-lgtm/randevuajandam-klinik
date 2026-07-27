@extends(theme_layout())

@section('baslik', trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim').' | '.($doktor['uzmanlik'] ?? 'Klinik')))
@section('meta_aciklama', $doktor['kisa_bio'] ?? $doktor['slogan'] ?? '')

@section('icerik')
@php
    $dg = rtrim(asset('themes/delogis'), '/');
    $photo = function_exists('doctor_photo')
        ? doctor_photo($doktor ?? null, $dg.'/images/resources/about-three-img-1.jpg')
        : ($doktor['profil_resmi'] ?? $dg.'/images/resources/about-three-img-1.jpg');
    $slider = collect($doktor['slider'] ?? [])->filter(fn ($s) => is_array($s))->values()->all();
    if ($slider === []) {
        $slider = [[
            'baslik' => 'Size en iyi şekilde destek olmaya hazırız',
            'baslik_vurgulu' => $doktor['uzmanlik'] ?? null,
            'alt' => $doktor['kisa_bio'] ?? $doktor['slogan'] ?? '',
            'etiket' => $doktor['vitrin_badge'] ?? ($doktor['uzmanlik'] ?? 'Klinik'),
            'image' => $photo,
            'cta' => 'Randevu Al',
            'cta_url' => route('frontend.randevu'),
        ]];
    }
    $hizmetler = collect($doktor['hizmetler'] ?? [])
        ->filter(fn ($h) => is_array($h) && (filled($h['baslik'] ?? null) || filled($h['ad'] ?? null)))
        ->values()
        ->take(6);
    $bloglar = collect($doktor['bloglar'] ?? [])->take(3);
    $yorumlar = collect($doktor['yorumlar'] ?? [])->take(4);
    $stats = collect($doktor['istatistikler'] ?? [])->take(4);
    $ad = trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim'));
    $bolum = $doktor['anasayfa_bolumler'] ?? [];
    $show = fn (string $key) => (bool) ($bolum[$key] ?? true);
    $icons = ['icon-account', 'icon-in-love', 'icon-mental-health', 'icon-psychology', 'icon-brain', 'icon-help'];
@endphp

{{-- Hero slider (index3 main-slider-three) --}}
@if($show('slider'))
<section class="main-slider-three">
    <div class="main-slider__carousel owl-carousel owl-theme thm-owl__carousel"
         data-owl-options='{"loop": {{ count($slider) > 1 ? 'true' : 'false' }}, "items": 1, "navText": ["<span class=\"icon-left-arrow\"></span>","<span class=\"icon-right-arrow\"></span>"], "margin": 0, "dots": false, "nav": true, "animateOut": "fadeOut", "animateIn": "fadeIn", "active": true, "smartSpeed": 1000, "autoplay": true, "autoplayTimeout": 7000, "autoplayHoverPause": false}'>
        @foreach ($slider as $i => $slide)
            @php
                $img = $slide['image'] ?? $slide['thumb'] ?? $photo;
                $title = $slide['baslik'] ?? $ad;
                $vurgulu = $slide['baslik_vurgulu'] ?? null;
                $etiket = $slide['etiket'] ?? ($slide['badge'] ?? ($doktor['vitrin_badge'] ?? 'Hoş geldiniz'));
                $cta = $slide['cta'] ?? 'Randevu Al';
                $ctaUrl = $slide['cta_url'] ?? route('frontend.randevu');
                if ($ctaUrl === '/randevu') { $ctaUrl = route('frontend.randevu'); }
            @endphp
            <div class="item main-slider-three__slide-{{ ($i % 3) + 1 }}">
                <div class="main-slider-three__bg" style="background-image: url({{ $dg }}/images/backgrounds/main-slider-three-bg.jpg);"></div>
                <div class="main-slider-three__shape-3 img-bounce">
                    <img src="{{ $dg }}/images/shapes/main-slider-three-shape-3.png" alt="">
                </div>
                <div class="main-slider-three__img">
                    <img src="{{ $img }}" alt="{{ $title }}" style="max-height:520px;object-fit:contain">
                </div>
                <div class="main-slider-three__star-one zoominout">
                    <img src="{{ $dg }}/images/shapes/main-slider-three-star-1.png" alt="">
                </div>
                <div class="main-slider-three__star-two img-bounce">
                    <img src="{{ $dg }}/images/shapes/main-slider-three-star-2.png" alt="">
                </div>
                <div class="container">
                    <div class="main-slider-three__content">
                        <div class="main-slider-three__sub-title-box">
                            <div class="main-slider-three__shape-1" style="background-image: url({{ $dg }}/images/shapes/main-slider-three-shape-1.png);"></div>
                            <p class="main-slider-three__sub-title">{{ $etiket }}</p>
                        </div>
                        <h2 class="main-slider-three__title">
                            {{ $title }}
                            @if($vurgulu)
                                <br><span>{{ $vurgulu }}</span>
                            @endif
                        </h2>
                        <div class="main-slider-three__btn-founder-box">
                            <a href="{{ $ctaUrl }}" class="main-slider-two__btn-one thm-btn">{{ $cta }}</a>
                            <div class="main-slider-three__founder-box">
                                <h4 class="main-slider-three__founder-name">{{ $ad }}</h4>
                                <p class="main-slider-three__founder-sub-title">{{ $doktor['uzmanlik'] ?? 'Uzman hekim' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- Feature cards from services --}}
@if($hizmetler->isNotEmpty())
<section class="feature-two">
    <div class="container">
        <div class="row">
            @foreach ($hizmetler->take(3) as $idx => $h)
                @php
                    $hAd = $h['baslik'] ?? $h['ad'] ?? 'Hizmet';
                    $hSlug = $h['slug'] ?? \Illuminate\Support\Str::slug($hAd);
                    $hDesc = \Illuminate\Support\Str::limit(strip_tags((string)($h['kisa'] ?? $h['aciklama'] ?? '')), 90);
                    $hImg = $h['image'] ?? $dg.'/images/resources/feature-2-'.(($idx % 3) + 1).'.jpg';
                    $href = route('frontend.hizmet.detay', $hSlug ?: ($h['id'] ?? ''));
                @endphp
                <div class="col-xl-4 col-lg-4 wow fadeInUp" data-wow-delay="{{ ($idx + 1) * 100 }}ms">
                    <div class="feature-two__single">
                        <div class="feature-two__img-box">
                            <div class="feature-two__img">
                                <img src="{{ $hImg }}" alt="{{ $hAd }}">
                            </div>
                            <div class="feature-two__title-box">
                                <h3><a href="{{ $href }}">{{ $hAd }}</a></h3>
                                <div class="feature-two__icon">
                                    <span class="{{ $icons[$idx % count($icons)] }}"></span>
                                </div>
                            </div>
                            <div class="feature-two__hover-title-box">
                                <h3><a href="{{ $href }}">{{ $hAd }}</a></h3>
                                <p class="feature-two__hover-text">{{ $hDesc !== '' ? $hDesc : 'Detay ve randevu için tıklayın.' }}</p>
                                <div class="feature-two__hover-icon">
                                    <span class="{{ $icons[$idx % count($icons)] }}"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- About --}}
<section class="about-three">
    <div class="container">
        <div class="row">
            <div class="col-xl-6">
                <div class="about-three__left wow slideInLeft" data-wow-delay="100ms" data-wow-duration="2500ms">
                    <div class="about-three__img-box">
                        <div class="about-three__img">
                            <img src="{{ $photo }}" alt="{{ $ad }}" style="border-radius:12px;max-width:100%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="about-three__right">
                    <div class="section-title text-left">
                        <span class="section-title__tagline">Hakkımızda</span>
                        <h2 class="section-title__title">{{ $doktor['bolum_basliklar']['hakkimda']['baslik'] ?? $ad }}</h2>
                    </div>
                    <p class="about-three__text-1">
                        {{ \Illuminate\Support\Str::limit(strip_tags((string) ($doktor['kisa_bio'] ?? $doktor['bio'] ?? $doktor['slogan'] ?? '')), 280) }}
                    </p>
                    <div class="about-three__btn-box">
                        <a href="{{ route('frontend.hakkimda') }}" class="thm-btn">Devamını oku</a>
                        <a href="{{ route('frontend.randevu') }}" class="thm-btn thm-btn--two" style="margin-left:10px">Randevu Al</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Services grid --}}
@if($hizmetler->isNotEmpty())
<section class="services-three" id="hizmetler">
    <div class="container">
        <div class="section-title text-center">
            <span class="section-title__tagline">Hizmetler</span>
            <h2 class="section-title__title">{{ filled($doktor['hizmetler_baslik'] ?? null) ? $doktor['hizmetler_baslik'] : 'Sunduğumuz hizmetler' }}</h2>
        </div>
        <div class="row">
            @foreach ($hizmetler as $idx => $h)
                @php
                    $hAd = $h['baslik'] ?? $h['ad'] ?? 'Hizmet';
                    $hSlug = $h['slug'] ?? \Illuminate\Support\Str::slug($hAd);
                    $hDesc = \Illuminate\Support\Str::limit(strip_tags((string)($h['kisa'] ?? $h['aciklama'] ?? '')), 100);
                    $href = route('frontend.hizmet.detay', $hSlug ?: ($h['id'] ?? ''));
                @endphp
                <div class="col-xl-4 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="{{ ($idx % 3 + 1) * 100 }}ms">
                    <div class="services-three__single">
                        <div class="services-three__icon">
                            <span class="{{ $icons[$idx % count($icons)] }}"></span>
                        </div>
                        <h3 class="services-three__title"><a href="{{ $href }}">{{ $hAd }}</a></h3>
                        <p class="services-three__text">{{ $hDesc !== '' ? $hDesc : 'Detay için tıklayın.' }}</p>
                        <div class="services-three__btn-box">
                            <a href="{{ $href }}">İncele <span class="icon-right-arrow"></span></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center" style="margin-top:30px">
            <a href="{{ route('frontend.hizmetler') }}" class="thm-btn">Tüm hizmetler</a>
        </div>
    </div>
</section>
@endif

{{-- Counters --}}
@if($show('istatistik') && $stats->isNotEmpty())
<section class="counter-two">
    <div class="container">
        <div class="row">
            @foreach ($stats as $st)
                <div class="col-xl-3 col-lg-3 col-md-6">
                    <div class="counter-two__single">
                        <div class="counter-two__count-box">
                            <h3 class="odometer" data-count="{{ (int) preg_replace('/\D/', '', (string)($st['deger'] ?? 0)) }}">00</h3>
                            <span class="counter-two__plus">{{ $st['suffix'] ?? '+' }}</span>
                        </div>
                        <p class="counter-two__text">{{ $st['etiket'] ?? '' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Testimonials --}}
@if($yorumlar->isNotEmpty())
<section class="testimonial-three">
    <div class="container">
        <div class="section-title text-center">
            <span class="section-title__tagline">Yorumlar</span>
            <h2 class="section-title__title">Hasta deneyimleri</h2>
        </div>
        <div class="row">
            @foreach ($yorumlar as $y)
                <div class="col-xl-6 col-lg-6">
                    <div class="testimonial-three__single" style="margin-bottom:24px">
                        <p class="testimonial-three__text">“{{ \Illuminate\Support\Str::limit(strip_tags((string)($y['yorum'] ?? $y['metin'] ?? $y['content'] ?? '')), 180) }}”</p>
                        <div class="testimonial-three__client-info">
                            <h4 class="testimonial-three__client-name">{{ $y['hasta_adi'] ?? $y['ad'] ?? 'Hasta' }}</h4>
                            <p class="testimonial-three__client-sub-title">
                                @if(!empty($y['puan'])) {{ $y['puan'] }}/5 · @endif
                                Değerlendirme
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Blog --}}
@if($bloglar->isNotEmpty())
<section class="blog-two">
    <div class="container">
        <div class="section-title text-center">
            <span class="section-title__tagline">Blog</span>
            <h2 class="section-title__title">Son yazılar</h2>
        </div>
        <div class="row">
            @foreach ($bloglar as $b)
                @php
                    $bTitle = $b['baslik'] ?? $b['title'] ?? 'Yazı';
                    $bSlug = $b['slug'] ?? \Illuminate\Support\Str::slug($bTitle);
                    $bImg = $b['image'] ?? $b['kapak'] ?? $dg.'/images/blog/blog-2-1.jpg';
                    $href = route('frontend.blog.detay', $bSlug);
                @endphp
                <div class="col-xl-4 col-lg-4 wow fadeInUp">
                    <div class="blog-two__single">
                        <div class="blog-two__img">
                            <img src="{{ $bImg }}" alt="{{ $bTitle }}">
                            <a href="{{ $href }}"><span class="blog-two__plus"></span></a>
                        </div>
                        <div class="blog-two__content">
                            <h3 class="blog-two__title"><a href="{{ $href }}">{{ $bTitle }}</a></h3>
                            <p class="blog-two__text">{{ \Illuminate\Support\Str::limit(strip_tags((string)($b['ozet'] ?? $b['icerik'] ?? '')), 100) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- CTA --}}
<section class="cta-one">
    <div class="cta-one__shape-1 float-bob-x">
        <img src="{{ $dg }}/images/shapes/cta-one-shape-1.png" alt="">
    </div>
    <div class="container">
        <div class="cta-one__inner">
            <p class="cta-one__text">Sağlığınız için ilk adımı atın — online randevu alın</p>
            <div class="cta-one__btn-box">
                <a href="{{ route('frontend.randevu') }}" class="cta-one__btn thm-btn">Randevu Al</a>
            </div>
        </div>
    </div>
</section>
@endsection
