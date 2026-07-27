@extends(theme_layout())

@section('baslik', 'Hakkımda | '.trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim')))
@section('meta_aciklama', $doktor['kisa_bio'] ?? $doktor['bio'] ?? '')

@section('icerik')
@php
    $photo = doctor_photo($doktor ?? null);
    $mezuniyet = $doktor['mezuniyet'] ?? [];
@endphp

<section class="mp-page-hero">
    <div class="mp-container">
        <div class="mp-breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>Hakkımda</span>
        </div>
        <h1>{{ trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? '')) }}</h1>
        <p>
            {{ $doktor['uzmanlik'] ?? '' }}
            @if(!empty($doktor['klinik_adi'])) · {{ $doktor['klinik_adi'] }} @endif
        </p>
    </div>
</section>

<section class="mp-section mp-page">
    <div class="mp-container">
        <div class="mp-about-grid">
            <div class="mp-about-photo">
                <img src="{{ $photo }}"
                     alt="{{ $doktor['ad_soyad'] ?? 'Hekim' }}"
                     width="560"
                     height="640"
                     loading="eager"
                     decoding="async"
                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=1000&q=80';">
            </div>
            <div>
                <span class="mp-eyebrow" style="color:var(--mp-blue);font-weight:600;font-size:.8rem;letter-spacing:.06em;text-transform:uppercase">Özgeçmiş</span>
                <h2 style="margin:.5rem 0 1rem;color:var(--mp-navy);font-size:1.6rem">{{ $doktor['slogan'] ?? 'Uzman hekimlik yaklaşımı' }}</h2>
                @if(!empty($doktor['bio_html']))
                    <div style="color:var(--muted);line-height:1.75;font-size:.95rem">{!! $doktor['bio_html'] !!}</div>
                @else
                    <p style="color:var(--muted);line-height:1.75">{{ $doktor['bio'] ?? $doktor['kisa_bio'] ?? '' }}</p>
                @endif
                @if(!empty($mezuniyet) && is_array($mezuniyet))
                    <ul class="mp-about-check" style="margin-top:18px">
                        @foreach ($mezuniyet as $m)
                            <li>{{ is_string($m) ? $m : json_encode($m) }}</li>
                        @endforeach
                    </ul>
                @endif
                @if(!empty($doktor['branslar']))
                    <div class="mp-svc-meta" style="margin-top:18px">
                        @foreach ($doktor['branslar'] as $b)
                            <span class="mp-chip">{{ is_string($b) ? $b : ($b['ad'] ?? '') }}</span>
                        @endforeach
                    </div>
                @endif
                <div style="margin-top:24px">
                    <a href="{{ route('frontend.randevu') }}" class="mp-btn mp-btn-primary">Randevu Al</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
