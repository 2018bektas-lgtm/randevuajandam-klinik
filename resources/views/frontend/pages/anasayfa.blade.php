@extends(theme_layout())

@php
    $klinikAd = $doktor['klinik_adi'] ?? $doktor['ad_soyad'] ?? 'Klinik';
    $ilLine = trim(($doktor['ilce'] ?? '').(!empty($doktor['il']) ? ' / '.$doktor['il'] : ''), ' /');
@endphp
@section('baslik', $klinikAd.(!empty($doktor['il']) ? ' · '.$doktor['il'] : '').' | Klinik')
@section('meta_aciklama', $doktor['kisa_bio'] ?? $doktor['slogan'] ?? '')

@section('icerik')
@php
    $slider = $doktor['slider'] ?? [];
    $photo = $doktor['logo']
        ?? $doktor['profil_resmi']
        ?? ($doktor['hekimler'][0]['profil_resmi'] ?? null)
        ?? 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1200&q=80';
    $clinicHeroImg = $doktor['galeri'][0]['image']
        ?? 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=2000&q=85';
    $bolum = $doktor['anasayfa_bolumler'] ?? [];
    $basliklar = $doktor['bolum_basliklar'] ?? [];
    $sira = $doktor['anasayfa_sira'] ?? [
        'slider', 'istatistik', 'ozellikler', 'hakkimda', 'hekimler', 'hizmetler',
        'surec', 'galeri', 'yorumlar', 'blog', 'cta',
    ];
    if (! in_array('slider', $sira, true)) {
        array_unshift($sira, 'slider');
    }
    $hekimSay = count($doktor['hekimler'] ?? []);
    $hizmetSay = count($doktor['hizmetler'] ?? []);
@endphp

@foreach ($sira as $sectionKey)
    @if(!($bolum[$sectionKey] ?? true))
        @continue
    @endif

    @switch($sectionKey)
        @case('slider')
            @php
                $statsFallback = collect($doktor['istatistikler'] ?? [])->take(3)->map(fn ($s) => [
                    'sayi' => (int) preg_replace('/\D/', '', (string) ($s['deger'] ?? 0)),
                    'suffix' => $s['suffix'] ?? '',
                    'etiket' => $s['etiket'] ?? '',
                ])->filter(fn ($s) => ($s['etiket'] ?? '') !== '')->values()->all();
                if (empty($statsFallback)) {
                    $statsFallback = array_values(array_filter([
                        $hekimSay ? ['sayi' => $hekimSay, 'suffix' => '', 'etiket' => 'Uzman Hekim'] : null,
                        $hizmetSay ? ['sayi' => $hizmetSay, 'suffix' => '', 'etiket' => 'Hizmet'] : null,
                        ['sayi' => 1, 'suffix' => '', 'etiket' => 'Online Randevu'],
                    ]));
                }
                // Panel slider yoksa / azsa: Dentaire tarzı multi-slide hero
                $autoSlides = [];
                if ($hekimSay > 0) {
                    $firstDoc = $doktor['hekimler'][0];
                    $autoSlides[] = [
                        'baslik' => 'Uzman hekim kadromuz',
                        'baslik_vurgulu' => trim(($firstDoc['unvan'] ?? '').' '.($firstDoc['ad_soyad'] ?? '')),
                        'alt' => $firstDoc['kisa_bio'] ?? 'Size uygun hekimi seçerek randevu oluşturun.',
                        'etiket' => 'Hekimler',
                        'badge' => $firstDoc['uzmanlik'] ?? null,
                        'image' => $firstDoc['profil_resmi'] ?? $clinicHeroImg,
                        'thumb' => $firstDoc['profil_resmi'] ?? $photo,
                        'cta' => 'Hekimleri Gör',
                        'cta_url' => route('frontend.hekimler'),
                        'cta2' => 'Randevu Al',
                        'cta2_url' => route('frontend.randevu'),
                        'istatistikler' => $statsFallback,
                        'float_1_baslik' => $firstDoc['uzmanlik'] ?? 'Uzmanlık',
                        'float_1_aciklama' => 'Alanında deneyim',
                        'float_2_baslik' => 'Randevu',
                        'float_2_aciklama' => 'Online planlama',
                    ];
                }
                $autoSlides[] = [
                    'baslik' => 'Online randevu ile kolay planlama',
                    'baslik_vurgulu' => 'Hekim seçin, saatinizi ayırın',
                    'alt' => 'Randevu talebiniz klinik paneline anında düşer; onay sonrası bilgilendirilirsiniz.',
                    'etiket' => 'Randevu',
                    'badge' => '7/24 talep',
                    'image' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=2000&q=85',
                    'thumb' => $photo,
                    'cta' => 'Randevu Oluştur',
                    'cta_url' => route('frontend.randevu'),
                    'cta2' => 'İletişim',
                    'cta2_url' => route('frontend.iletisim'),
                    'istatistikler' => $statsFallback,
                    'float_1_baslik' => 'Hızlı talep',
                    'float_1_aciklama' => 'Misafir form',
                    'float_2_baslik' => $doktor['telefon'] ?? 'Klinik',
                    'float_2_aciklama' => 'Telefon ile ulaşın',
                ];

                if (empty($slider)) {
                    $slider = array_merge([[
                        'baslik' => $klinikAd,
                        'baslik_vurgulu' => $doktor['slogan'] ?? 'Uzman kadro · güvenilir klinik',
                        'alt' => $doktor['kisa_bio'] ?? 'Modern klinik altyapısı, uzman hekimler ve kolay online randevu.',
                        'etiket' => $doktor['vitrin_badge'] ?? 'Klinik',
                        'badge' => $ilLine ?: null,
                        'image' => $clinicHeroImg,
                        'thumb' => $photo,
                        'cta' => 'Online Randevu',
                        'cta_url' => route('frontend.randevu'),
                        'cta2' => 'Hekimlerimiz',
                        'cta2_url' => route('frontend.hekimler'),
                        'istatistikler' => $statsFallback,
                        'float_1_baslik' => $hekimSay ? $hekimSay.' hekim' : 'Uzman kadro',
                        'float_1_aciklama' => 'Branşında deneyimli ekip',
                        'float_2_baslik' => 'Online randevu',
                        'float_2_aciklama' => 'Hekim seç · hızlı talep',
                    ]], $autoSlides);
                } elseif (count($slider) < 2) {
                    // Tek panel slaytı: Dentaire multi-slider hissi için ek slaytlar
                    $slider = array_merge(array_values($slider), $autoSlides);
                }
                $slideTotal = count($slider);
                $defaultStats = $statsFallback;
            @endphp
            {{-- AUREUM cinematic hero — glass copy + full-bleed stage + marquee --}}
            @php
                $marqueeItems = collect($doktor['branslar'] ?? [])
                    ->merge(collect($doktor['hizmetler'] ?? [])->pluck('baslik')->filter())
                    ->unique()
                    ->take(8)
                    ->values()
                    ->all();
                if (empty($marqueeItems)) {
                    $marqueeItems = ['Uzman kadro', 'Online randevu', 'Modern klinik', 'Hasta odaklı bakım', 'Güvenilir teşhis', 'Kişiye özel plan'];
                }
            @endphp
            <section class="dn-hero lx-hero" aria-label="Ana slider">
                <div class="dn-hero-shapes lx-mesh" aria-hidden="true">
                    <span class="dn-blob dn-blob-1"></span>
                    <span class="dn-blob dn-blob-2"></span>
                    <span class="dn-blob dn-blob-3"></span>
                </div>

                <div class="lx-hero-shell">
                    <aside class="lx-rail" aria-hidden="true">
                        <span class="lx-rail-text">{{ $klinikAd }} · {{ date('Y') }}</span>
                        <div class="lx-scroll-hint">Kaydır</div>
                    </aside>

                    <div class="swiper dn-hero-swiper lx-swiper">
                        <div class="swiper-wrapper">
                            @foreach ($slider as $i => $slide)
                                @php
                                    $meta = is_array($slide['meta'] ?? null) ? $slide['meta'] : [];
                                    $baslikVurgulu = $slide['baslik_vurgulu'] ?? ($meta['baslik_vurgulu'] ?? null);
                                    $stats = $slide['istatistikler'] ?? ($meta['istatistikler'] ?? $defaultStats);
                                    $f1b = $slide['float_1_baslik'] ?? ($meta['float_1_baslik'] ?? null);
                                    $f1a = $slide['float_1_aciklama'] ?? ($meta['float_1_aciklama'] ?? null);
                                    $f2b = $slide['float_2_baslik'] ?? ($meta['float_2_baslik'] ?? null);
                                    $f2a = $slide['float_2_aciklama'] ?? ($meta['float_2_aciklama'] ?? null);
                                    $docThumb = $slide['thumb'] ?? ($doktor['hekimler'][0]['profil_resmi'] ?? $photo);
                                    $docName = $slide['float_1_baslik']
                                        ?? (isset($doktor['hekimler'][0])
                                            ? trim(($doktor['hekimler'][0]['unvan'] ?? '').' '.($doktor['hekimler'][0]['ad_soyad'] ?? ''))
                                            : ($f1b ?: 'Uzman hekim'));
                                    $docRole = $f1a
                                        ?? ($doktor['hekimler'][0]['uzmanlik'] ?? 'Klinik hekimi');
                                    if (! $f2b) {
                                        $f2b = 'Randevu hattı';
                                        $f2a = $doktor['telefon'] ?? 'Online talep';
                                    }
                                @endphp
                                <div class="swiper-slide">
                                    <div class="dn-hero-slide lx-slide">
                                        <div class="dn-hero-grid lx-slide-grid">
                                            <div class="dn-hero-content lx-copy">
                                                <div class="dn-hero-kicker lx-kicker dn-anim" data-anim="up">
                                                    <span class="dn-pulse"></span>
                                                    {{ $slide['etiket'] ?? $doktor['vitrin_badge'] ?? 'Aureum Klinik' }}
                                                    @if(!empty($slide['badge']))
                                                        <span class="dn-dot">·</span> {{ $slide['badge'] }}
                                                    @endif
                                                </div>

                                                <h1 class="dn-hero-title lx-title dn-anim" data-anim="up" data-delay="80">
                                                    {{ $slide['baslik'] ?? $klinikAd }}
                                                    @if($baslikVurgulu)
                                                        <span>{{ $baslikVurgulu }}</span>
                                                    @endif
                                                </h1>

                                                @if(!empty($slide['alt']))
                                                    <p class="dn-hero-desc lx-desc dn-anim" data-anim="up" data-delay="140">{{ $slide['alt'] }}</p>
                                                @endif

                                                <div class="dn-hero-actions lx-actions dn-anim" data-anim="up" data-delay="200">
                                                    @if(!empty($slide['cta']) && !empty($slide['cta_url']))
                                                        <a href="{{ $slide['cta_url'] }}" class="btn btn-gold btn-lg btn-glow"
                                                           @if(\Illuminate\Support\Str::startsWith($slide['cta_url'], ['http://','https://'])) target="_blank" rel="noopener" @endif>
                                                            {{ $slide['cta'] }}
                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                                        </a>
                                                    @endif
                                                    @if(!empty($slide['cta2']) && !empty($slide['cta2_url']))
                                                        <a href="{{ $slide['cta2_url'] }}" class="btn btn-soft btn-lg"
                                                           @if(\Illuminate\Support\Str::startsWith($slide['cta2_url'], ['http://','https://'])) target="_blank" rel="noopener" @endif>
                                                            {{ $slide['cta2'] }}
                                                        </a>
                                                    @endif
                                                </div>

                                                <div class="dn-hero-rating lx-trust dn-anim" data-anim="up" data-delay="260">
                                                    <div class="dn-stars" aria-hidden="true">★★★★★</div>
                                                    <div>
                                                        <strong>5.0 hasta memnuniyeti</strong>
                                                        <span>{{ $hekimSay }} hekim · {{ $hizmetSay }} hizmet · online randevu</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="dn-hero-visual lx-stage dn-anim" data-anim="zoom" data-delay="120">
                                                <div class="dn-hero-photo lx-frame">
                                                    <img src="{{ $slide['image'] ?? $clinicHeroImg }}" alt="{{ $slide['baslik'] ?? $klinikAd }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                                                    <div class="dn-photo-glow"></div>
                                                    <div class="lx-frame-ring" aria-hidden="true"></div>
                                                </div>

                                                <div class="dn-float dn-float-doctor lx-pill" data-parallax="1">
                                                    <img src="{{ $docThumb }}" alt="">
                                                    <div>
                                                        <strong>{{ $docName }}</strong>
                                                        <span>{{ $docRole }}</span>
                                                    </div>
                                                </div>

                                                <div class="dn-float dn-float-hours lx-pill" data-parallax="1.4">
                                                    <div class="dn-float-ico">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $f2b }}</strong>
                                                        <span>{{ $f2a }}</span>
                                                    </div>
                                                </div>

                                                @if(!empty($stats) && is_array($stats))
                                                    <div class="dn-float dn-float-stats lx-pill" data-parallax=".7">
                                                        @foreach (array_slice($stats, 0, 2) as $ist)
                                                            <div>
                                                                <b class="dn-count"
                                                                   data-count="{{ (int) ($ist['sayi'] ?? $ist['deger'] ?? 0) }}"
                                                                   data-suffix="{{ $ist['suffix'] ?? '' }}">0</b>
                                                                <em>{{ $ist['etiket'] ?? '' }}</em>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>

                <div class="lx-marquee" aria-hidden="true">
                    <div class="lx-marquee-track">
                        @foreach (array_merge($marqueeItems, $marqueeItems) as $item)
                            <span class="lx-marquee-item"><i>✦</i> {{ $item }}</span>
                        @endforeach
                    </div>
                </div>
            </section>
            @break

        @case('istatistik')
            @if(!empty($doktor['istatistikler']))
            <section class="stats-bar">
                <div class="container">
                    <div class="stats-panel">
                        @foreach (($doktor['istatistikler'] ?? []) as $stat)
                            <div class="stat-item">
                                <div class="stat-value"
                                     data-counter="{{ $stat['deger'] }}"
                                     data-suffix="{{ $stat['suffix'] ?? '' }}">0</div>
                                <div class="stat-label">{{ $stat['etiket'] }}</div>
                                <div class="stat-desc">{{ $stat['aciklama'] ?? '' }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('ozellikler')
            @if(!empty($doktor['ozellikler']))
            <section class="section-tight">
                <div class="container">
                    @if(!empty($basliklar['ozellikler']['baslik']) || !empty($basliklar['ozellikler']['alt']))
                        <div class="section-head reveal" style="margin-bottom:1.25rem">
                            <div>
                                @if(!empty($basliklar['ozellikler']['baslik']))
                                    <h2 class="section-title" style="font-size:1.75rem">{{ $basliklar['ozellikler']['baslik'] }}</h2>
                                @endif
                                @if(!empty($basliklar['ozellikler']['alt']))
                                    <p class="section-sub">{{ $basliklar['ozellikler']['alt'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                    <div class="features reveal">
                        @foreach ($doktor['ozellikler'] as $i => $oz)
                            <div class="feature">
                                <div class="feature-icon">0{{ $i + 1 }}</div>
                                <h3>{{ $oz['baslik'] }}</h3>
                                <p>{{ $oz['aciklama'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('hekimler')
            @if(!empty($doktor['hekimler']))
            <section class="section">
                <div class="container">
                    <div class="section-head reveal">
                        <div>
                            <span class="eyebrow">Kadro</span>
                            <h2 class="section-title">{{ $basliklar['hekimler']['baslik'] ?? 'Hekimlerimiz' }}</h2>
                            <p class="section-sub">{{ $basliklar['hekimler']['alt'] ?? 'Uzman kadromuzla tanışın.' }}</p>
                        </div>
                        <div class="section-nav">
                            <button type="button" class="swiper-button-prev team-prev" aria-label="Önceki hekim"></button>
                            <button type="button" class="swiper-button-next team-next" aria-label="Sonraki hekim"></button>
                            <a href="{{ route('frontend.hekimler') }}" class="link-more">Tümü →</a>
                        </div>
                    </div>
                    <div class="swiper team-swiper reveal">
                        <div class="swiper-wrapper">
                            @foreach (array_slice($doktor['hekimler'], 0, 8) as $hekim)
                                <div class="swiper-slide">
                                    <article class="team-card">
                                        <a href="{{ route('frontend.hekim.detay', $hekim['slug']) }}" class="team-card-media">
                                            <img src="{{ $hekim['profil_resmi'] ?? $photo }}" alt="{{ $hekim['ad_soyad'] }}" loading="lazy">
                                        </a>
                                        <div class="team-card-body">
                                            <h3><a href="{{ route('frontend.hekim.detay', $hekim['slug']) }}">{{ $hekim['unvan'] ?? '' }} {{ $hekim['ad_soyad'] }}</a></h3>
                                            <p class="team-card-meta">{{ $hekim['uzmanlik'] ?: implode(', ', $hekim['branslar'] ?? []) }}</p>
                                            <a href="{{ route('frontend.randevu') }}?doktor_id={{ $hekim['id'] }}" class="btn btn-primary btn-sm">Randevu al</a>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('hakkimda')
            <section class="section bg-white">
                <div class="container two-col reveal">
                    <div class="media-frame">
                        <img src="{{ $clinicHeroImg }}" alt="{{ $doktor['klinik_adi'] ?? $doktor['ad_soyad'] ?? 'Klinik' }}">
                        <div class="media-badge">
                            <strong>{{ $doktor['klinik_adi'] ?? $doktor['ad_soyad'] ?? '' }}</strong>
                            <span>{{ $doktor['uzmanlik'] ?? '' }}@if(!empty($doktor['il'])) · {{ $doktor['il'] }}@endif</span>
                        </div>
                    </div>
                    <div>
                        <span class="eyebrow">Hakkımızda</span>
                        <h2 class="section-title">{{ $basliklar['hakkimda']['baslik'] ?? $doktor['slogan'] ?? 'Güvenilir klinik, uzman kadro' }}</h2>
                        @if(!empty($basliklar['hakkimda']['alt']))
                            <p class="section-sub">{{ $basliklar['hakkimda']['alt'] }}</p>
                        @endif
                        @if(!empty($doktor['branslar']))
                            <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin:1rem 0">
                                @foreach ($doktor['branslar'] as $br)
                                    <span class="chip">{{ $br }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="prose mt-2">
                            <p>{{ $doktor['bio'] ?? $doktor['kisa_bio'] ?? '' }}</p>
                        </div>
                        <div class="hero-actions mt-3">
                            <a href="{{ route('frontend.hakkimda') }}" class="btn btn-primary">Klinik hakkında</a>
                            <a href="{{ route('frontend.hekimler') }}" class="btn btn-dark-outline">Hekimlerimiz</a>
                            <a href="{{ route('frontend.randevu') }}" class="btn btn-dark-outline">Randevu Planla</a>
                        </div>
                    </div>
                </div>
            </section>
            @break

        @case('hizmetler')
            @if(!empty($doktor['hizmetler']))
            <section class="section">
                <div class="container">
                    <div class="section-head reveal">
                        <div>
                            <span class="eyebrow">Hizmetler</span>
                            <h2 class="section-title">{{ $doktor['hizmetler_baslik'] ?: ($basliklar['hizmetler']['baslik'] ?? 'Sunduğum hizmetler') }}</h2>
                            <p class="section-sub">{{ $doktor['hizmetler_alt'] ?: ($basliklar['hizmetler']['alt'] ?? (($doktor['uzmanlik'] ?? 'Uzmanlık alanım').' kapsamında randevu alabileceğiniz aktif hizmetler.')) }}</p>
                        </div>
                        <div class="section-nav">
                            <button type="button" class="swiper-button-prev svc-prev" aria-label="Önceki"></button>
                            <button type="button" class="swiper-button-next svc-next" aria-label="Sonraki"></button>
                            <a href="{{ route('frontend.hizmetler') }}" class="link-more">Tümü →</a>
                        </div>
                    </div>

                    <div class="swiper services-swiper reveal">
                        <div class="swiper-wrapper">
                            @foreach ($doktor['hizmetler'] as $hizmet)
                                @php $hSlug = $hizmet['slug'] ?? \Illuminate\Support\Str::slug($hizmet['baslik'] ?? ''); @endphp
                                <div class="swiper-slide">
                                    <article class="card service-card">
                                        <a href="{{ route('frontend.hizmet.detay', $hSlug) }}" style="display:block">
                                            <img src="{{ $hizmet['image'] }}" alt="{{ $hizmet['baslik'] }}" loading="lazy">
                                        </a>
                                        <div class="card-pad" style="flex:1;display:flex;flex-direction:column">
                                            <h3><a href="{{ route('frontend.hizmet.detay', $hSlug) }}" style="color:inherit;text-decoration:none">{{ $hizmet['baslik'] }}</a></h3>
                                            <p class="text-muted" style="margin:0;font-size:.92rem">{{ $hizmet['kisa'] }}</p>
                                            <div class="service-meta">
                                                @if(!empty($hizmet['sure']))
                                                    <span class="chip">{{ $hizmet['sure'] }}</span>
                                                @endif
                                                @if(!empty($hizmet['fiyat']))
                                                    <span class="chip chip-gold">{{ $hizmet['fiyat'] }}</span>
                                                @endif
                                            </div>
                                            @if(!empty($hizmet['madde']))
                                                <ul class="service-list" style="margin-bottom:1rem">
                                                    @foreach (array_slice($hizmet['madde'], 0, 3) as $m)
                                                        <li>{{ $m }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            <a href="{{ route('frontend.hizmet.detay', $hSlug) }}" class="link-more" style="margin-top:auto">Detay & randevu →</a>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('surec')
            @if(!empty($doktor['surec']))
            <section class="section">
                <div class="container">
                    <div class="section-head reveal">
                        <div>
                            <span class="eyebrow">Süreç</span>
                            <h2 class="section-title">{{ $basliklar['surec']['baslik'] ?? 'Randevudan takibe adım adım' }}</h2>
                            @if(!empty($basliklar['surec']['alt']))
                                <p class="section-sub">{{ $basliklar['surec']['alt'] }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="process reveal">
                        @foreach ($doktor['surec'] as $adim)
                            <div class="process-step">
                                <div class="num">{{ $adim['adim'] }}</div>
                                <h3>{{ $adim['baslik'] }}</h3>
                                <p>{{ $adim['aciklama'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('galeri')
            @if(!empty($doktor['galeri']))
            <section class="section">
                <div class="container">
                    <div class="section-head reveal">
                        <div>
                            <span class="eyebrow">Klinik</span>
                            <h2 class="section-title">{{ $basliklar['galeri']['baslik'] ?? 'Galeri' }}</h2>
                        </div>
                        <a href="{{ route('frontend.galeri') }}" class="link-more">Tüm galeri →</a>
                    </div>
                    <div class="gallery-grid reveal">
                        @foreach (array_slice($doktor['galeri'], 0, 6) as $g)
                            <figure class="gallery-item">
                                <img src="{{ $g['image'] }}" alt="{{ $g['baslik'] }}" loading="lazy">
                                <figcaption>
                                    <span>{{ $g['etiket'] ?? 'Klinik' }}</span>
                                    <strong>{{ $g['baslik'] }}</strong>
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('yorumlar')
            @if(!empty($doktor['yorumlar']))
            <section class="section">
                <div class="container">
                    <div class="section-head reveal">
                        <div>
                            <span class="eyebrow">Görüşler</span>
                            <h2 class="section-title">{{ $basliklar['yorumlar']['baslik'] ?? 'Danışan değerlendirmeleri' }}</h2>
                        </div>
                        <div class="section-nav">
                            <button type="button" class="swiper-button-prev rev-prev" aria-label="Önceki yorum"></button>
                            <button type="button" class="swiper-button-next rev-next" aria-label="Sonraki yorum"></button>
                        </div>
                    </div>
                    <div class="swiper reviews-swiper reveal">
                        <div class="swiper-wrapper">
                            @foreach ($doktor['yorumlar'] as $y)
                                <div class="swiper-slide">
                                    <article class="card review-card">
                                        <div class="review-quote" aria-hidden="true">“</div>
                                        <div class="review-stars">
                                            {!! str_repeat('★', max(1, min(5, (int)($y['puan'] ?? 5)))) !!}
                                        </div>
                                        <p class="review-text">“{{ $y['metin'] }}”</p>
                                        <div class="review-author">
                                            <span class="review-avatar">{{ mb_substr($y['ad'] ?? 'H', 0, 1) }}</span>
                                            <strong>{{ $y['ad'] }}</strong>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('blog')
            @if(!empty($doktor['bloglar']))
            <section class="section">
                <div class="container">
                    <div class="section-head reveal">
                        <div>
                            <span class="eyebrow">Blog</span>
                            <h2 class="section-title">{{ $basliklar['blog']['baslik'] ?? 'Sağlık yazıları' }}</h2>
                        </div>
                        <a href="{{ route('frontend.blog') }}" class="link-more">Tüm yazılar →</a>
                    </div>
                    <div class="blog-grid reveal">
                        @foreach (array_slice($doktor['bloglar'], 0, 3) as $yazi)
                            <article class="card blog-card">
                                <a href="{{ route('frontend.blog.detay', $yazi['slug']) }}">
                                    <img src="{{ $yazi['image'] }}" alt="{{ $yazi['baslik'] }}" loading="lazy">
                                </a>
                                <div class="card-pad">
                                    <div class="blog-meta">
                                        <span>{{ $yazi['tarih'] ?? '' }}</span>
                                        <span>{{ $yazi['okuma'] ?? '' }}</span>
                                    </div>
                                    <h3><a href="{{ route('frontend.blog.detay', $yazi['slug']) }}">{{ $yazi['baslik'] }}</a></h3>
                                    <p class="text-muted">{{ $yazi['ozet'] ?? '' }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('cta')
            <section class="section">
                <div class="container">
                    <div class="cta-band reveal">
                        <div>
                            <span class="eyebrow" style="color:#FCD34D">Randevu</span>
                            <h2 class="section-title" style="color:#fff;margin:0">
                                {{ ($doktor['cta_baslik'] ?? null) ?: ($basliklar['cta']['baslik'] ?? ($klinikAd.' ile randevu planlayın')) }}
                            </h2>
                            <p style="color:rgba(255,255,255,.78);margin:.75rem 0 0;max-width:36rem">
                                {{ ($doktor['cta_metin'] ?? null) ?: ($basliklar['cta']['alt'] ?? ('Hekim seçerek online randevu oluşturun.'.(!empty($doktor['telefon']) ? ' · '.$doktor['telefon'] : ''))) }}
                            </p>
                        </div>
                        <div class="hero-actions">
                            <a href="{{ route('frontend.randevu') }}" class="btn btn-gold">Online Randevu Al</a>
                            <a href="{{ route('frontend.hekimler') }}" class="btn btn-outline">Hekimlerimiz</a>
                            @if(!empty($doktor['telefon_raw']))
                                <a href="tel:{{ $doktor['telefon_raw'] }}" class="btn btn-outline">Hemen Ara</a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
            @break
    @endswitch
@endforeach
@endsection
