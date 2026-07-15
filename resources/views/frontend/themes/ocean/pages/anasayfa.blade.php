@extends(theme_layout())

@section('baslik', trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim')))
@section('meta_aciklama', $doktor['kisa_bio'] ?? '')

@section('icerik')
@php
    $photo = $doktor['profil_resmi'] ?? 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?auto=format&fit=crop&w=1000&q=80';
    $hizmetler = collect($doktor['hizmetler'] ?? [])->take(6);
@endphp

<section class="th-ocean-hero">
    <div class="container th-ocean-hero-grid">
        <div>
            <p class="th-ocean-kicker">{{ $doktor['il'] ?? 'Türkiye' }} · {{ $doktor['uzmanlik'] ?? 'Hekimlik' }}</p>
            <h1>{{ trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim')) }}</h1>
            <p>{{ $doktor['kisa_bio'] ?? '' }}</p>
            <div class="th-ocean-actions">
                <a href="{{ route('frontend.randevu') }}" class="th-ocean-cta">Online randevu</a>
                <a href="{{ route('frontend.hakkimda') }}" class="th-ocean-link">Özgeçmiş</a>
            </div>
        </div>
        <div class="th-ocean-hero-media">
            <img src="{{ $photo }}" alt="">
        </div>
    </div>
</section>

@if($hizmetler->isNotEmpty())
<section class="th-ocean-section">
    <div class="container">
        <h2>Tedavi & hizmetler</h2>
        <div class="th-ocean-services">
            @foreach ($hizmetler as $i => $h)
                <a href="{{ route('frontend.hizmet.detay', $h['slug'] ?? $h['id'] ?? '') }}" class="th-ocean-svc">
                    <span class="th-ocean-num">{{ str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) }}</span>
                    <span>{{ $h['baslik'] ?? $h['ad'] ?? 'Hizmet' }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="th-ocean-band">
    <div class="container">
        <h2>Güvenilir klinik iletişim</h2>
        <p>{{ $doktor['telefon'] ?? '' }} · {{ $doktor['e_posta'] ?? '' }}</p>
        <a href="{{ route('frontend.randevu') }}" class="th-ocean-cta">Randevu planla</a>
    </div>
</section>
@endsection
