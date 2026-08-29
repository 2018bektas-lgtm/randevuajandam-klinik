@extends(theme_layout())

@php
    $klinikAd = $doktor['klinik_adi'] ?? $doktor['ad_soyad'] ?? 'Klinik';
    $photo = $doktor['logo']
        ?? $doktor['profil_resmi']
        ?? ($doktor['galeri'][0]['image'] ?? null)
        ?? image_placeholder();
@endphp
@section('baslik', 'Hakkımızda | '.$klinikAd)
@section('meta_aciklama', $doktor['kisa_bio'] ?? $doktor['bio'] ?? '')

@section('icerik')
<div class="th-ocean-page">
<section class="page-hero th-ocean-page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>Hakkımızda</span>
        </div>
        <h1>{{ $klinikAd }}</h1>
        <p>
            {{ $doktor['slogan'] ?? $doktor['uzmanlik'] ?? 'Güvenilir klinik hizmeti' }}
            @if(!empty($doktor['ilce']) || !empty($doktor['il']))
                · {{ trim(($doktor['ilce'] ?? '').' / '.($doktor['il'] ?? ''), ' /') }}
            @endif
        </p>
    </div>
</section>

<section class="section th-ocean-section">
    <div class="container two-col">
        <div class="media-frame">
            <img src="{{ $photo }}" alt="{{ $klinikAd }}" loading="lazy" decoding="async">
            <div class="media-badge">
                <strong>{{ $klinikAd }}</strong>
                <span>{{ count($doktor['hekimler'] ?? []) }} hekim · {{ count($doktor['hizmetler'] ?? []) }} hizmet</span>
            </div>
        </div>
        <div class="prose">
            <span class="eyebrow">Klinik</span>
            <h2>{{ $doktor['slogan'] ?? 'Hasta odaklı, modern klinik yaklaşımı' }}</h2>
            @if(!empty($doktor['bio_html']))
                {!! $doktor['bio_html'] !!}
            @else
                <p>{{ $doktor['bio'] ?? $doktor['kisa_bio'] ?? 'Uzman hekim kadromuz ve modern altyapımızla yanınızdayız.' }}</p>
            @endif
            <div class="hero-actions" style="margin-top:1.25rem;display:flex;flex-wrap:wrap;gap:.75rem">
                <a href="{{ route('frontend.randevu') }}" class="btn btn-primary">Randevu Al</a>
                <a href="{{ route('frontend.hekimler') }}" class="btn btn-dark-outline">Hekimlerimiz</a>
            </div>
        </div>
    </div>
</section>

@if(!empty($doktor['istatistikler']))
<section class="section bg-white" style="padding-top:0">
    <div class="container">
        <div class="stats-row reveal">
            @foreach ($doktor['istatistikler'] as $stat)
                <div class="stat-item">
                    <div class="stat-value">{{ $stat['deger'] ?? 0 }}{{ $stat['suffix'] ?? '' }}</div>
                    <div class="stat-label">{{ $stat['etiket'] ?? '' }}</div>
                    @if(!empty($stat['aciklama']))
                        <div class="stat-desc">{{ $stat['aciklama'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(!empty($doktor['hekimler']))
<section class="section th-ocean-section">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Kadro</span>
                <h2 class="section-title">Hekim kadromuz</h2>
                <p class="section-sub">Branşınıza uygun uzmanla görüşmek için hekim seçin.</p>
            </div>
            <a href="{{ route('frontend.hekimler') }}" class="link-more">Tümü →</a>
        </div>
        <div class="team-grid">
            @foreach (array_slice($doktor['hekimler'], 0, 6) as $hekim)
                <article class="team-card">
                    <a href="{{ route('frontend.hekim.detay', $hekim['slug']) }}" class="team-card-media">
                        <img src="{{ $hekim['profil_resmi'] ?? avatar_placeholder(trim((string) ($hekim['unvan'] ?? '').' '.(string) ($hekim['ad_soyad'] ?? ''))) }}"
                             alt="{{ $hekim['ad_soyad'] }}" loading="lazy">
                    </a>
                    <div class="team-card-body">
                        <h3><a href="{{ route('frontend.hekim.detay', $hekim['slug']) }}">{{ $hekim['unvan'] ?? '' }} {{ $hekim['ad_soyad'] }}</a></h3>
                        <p class="team-card-meta">{{ $hekim['uzmanlik'] ?: implode(', ', $hekim['branslar'] ?? []) }}</p>
                        @if(!empty($hekim['randevuya_acik_mi']))
                            <a href="{{ route('frontend.randevu') }}?doktor_id={{ $hekim['id'] }}" class="btn btn-primary btn-sm">Randevu</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(!empty($doktor['adres']) || !empty($doktor['telefon']))
<section class="section bg-white">
    <div class="container">
        <div class="features">
            @if(!empty($doktor['adres']))
            <div class="feature">
                <div class="feature-icon">📍</div>
                <h3>Adres</h3>
                <p>{{ $doktor['adres'] }}</p>
            </div>
            @endif
            @if(!empty($doktor['telefon']))
            <div class="feature">
                <div class="feature-icon">☎</div>
                <h3>Telefon</h3>
                <p><a href="tel:{{ $doktor['telefon_raw'] ?? '' }}">{{ $doktor['telefon'] }}</a></p>
            </div>
            @endif
            @if(!empty($doktor['e_posta']))
            <div class="feature">
                <div class="feature-icon">✉</div>
                <h3>E-posta</h3>
                <p><a href="mailto:{{ $doktor['e_posta'] }}">{{ $doktor['e_posta'] }}</a></p>
            </div>
            @endif
            <div class="feature">
                <div class="feature-icon">◷</div>
                <h3>Randevu</h3>
                <p><a href="{{ route('frontend.randevu') }}" class="link-more">Online randevu al →</a></p>
            </div>
        </div>
    </div>
</section>
@endif
</div>
@endsection
