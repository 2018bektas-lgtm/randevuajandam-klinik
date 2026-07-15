@extends(theme_layout())

@section('baslik', trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim')))
@section('meta_aciklama', $doktor['kisa_bio'] ?? '')

@section('icerik')
@php
    $photo = $doktor['profil_resmi'] ?? 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=900&q=80';
    $hizmetler = collect($doktor['hizmetler'] ?? [])->take(4);
@endphp

<section class="th-min-hero">
    <div class="container th-min-hero-inner">
        <p class="th-min-label">{{ $doktor['uzmanlik'] ?? 'Klinik' }}</p>
        <h1>{{ trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim')) }}</h1>
        <p class="th-min-text">{{ $doktor['kisa_bio'] ?? $doktor['slogan'] ?? '' }}</p>
        <div class="th-min-actions">
            <a href="{{ route('frontend.randevu') }}" class="th-min-btn">Randevu al</a>
            <a href="{{ route('frontend.hakkimda') }}" class="th-min-link">Hakkımda</a>
        </div>
    </div>
</section>

<section class="th-min-photo-band">
    <div class="container">
        <img src="{{ $photo }}" alt="" class="th-min-hero-img">
    </div>
</section>

@if($hizmetler->isNotEmpty())
<section class="th-min-section">
    <div class="container">
        <h2 class="th-min-h2">Hizmetler</h2>
        <ul class="th-min-list">
            @foreach ($hizmetler as $h)
                <li>
                    <a href="{{ route('frontend.hizmet.detay', $h['slug'] ?? $h['id'] ?? '') }}">
                        <span>{{ $h['baslik'] ?? $h['ad'] ?? 'Hizmet' }}</span>
                        <span>→</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif

<section class="th-min-section th-min-cta-block">
    <div class="container">
        <h2 class="th-min-h2">İletişim</h2>
        <p class="th-min-text">{{ $doktor['telefon'] ?? '' }} @if(!empty($doktor['e_posta'])) · {{ $doktor['e_posta'] }} @endif</p>
        <a href="{{ route('frontend.randevu') }}" class="th-min-btn">Randevu talebi</a>
    </div>
</section>
@endsection
