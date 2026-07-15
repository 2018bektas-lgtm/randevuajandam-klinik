@extends(theme_layout())

@section('baslik', trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim').' | '.($doktor['uzmanlik'] ?? 'Klinik')))
@section('meta_aciklama', $doktor['kisa_bio'] ?? '')

@section('icerik')
@php
    $photo = $doktor['profil_resmi']
        ?? 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=1200&q=80';
    $hizmetler = collect($doktor['hizmetler'] ?? [])->take(6);
    $bloglar = collect($doktor['bloglar'] ?? [])->take(3);
@endphp

{{-- Modern: full-bleed dark hero, farklı iskelet --}}
<section class="th-modern-hero">
    <div class="th-modern-hero-bg" style="background-image:url('{{ ($doktor['slider'][0]['image'] ?? null) ?: $photo }}')"></div>
    <div class="th-modern-hero-overlay"></div>
    <div class="container th-modern-hero-content">
        <p class="th-modern-eyebrow">{{ $doktor['vitrin_badge'] ?? 'Modern klinik' }}</p>
        <h1>{{ trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? 'Hekim')) }}</h1>
        <p class="th-modern-lead">{{ $doktor['kisa_bio'] ?? $doktor['slogan'] ?? ($doktor['uzmanlik'] ?? '') }}</p>
        <div class="th-modern-hero-actions">
            <a href="{{ route('frontend.randevu') }}" class="th-modern-cta th-modern-cta-lg">Online randevu</a>
            <a href="{{ route('frontend.hizmetler') }}" class="th-modern-ghost">Hizmetler</a>
        </div>
        @if(!empty($doktor['istatistikler']))
            <div class="th-modern-stats">
                @foreach (array_slice($doktor['istatistikler'], 0, 3) as $st)
                    <div>
                        <strong>{{ $st['deger'] ?? '' }}{{ $st['suffix'] ?? '' }}</strong>
                        <span>{{ $st['etiket'] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

@if($hizmetler->isNotEmpty())
<section class="th-modern-section">
    <div class="container">
        <div class="th-modern-section-head">
            <h2>Hizmetler</h2>
            <a href="{{ route('frontend.hizmetler') }}">Tümü →</a>
        </div>
        <div class="th-modern-cards">
            @foreach ($hizmetler as $h)
                <a href="{{ route('frontend.hizmet.detay', $h['slug'] ?? $h['id'] ?? '') }}" class="th-modern-card">
                    <h3>{{ $h['baslik'] ?? $h['ad'] ?? 'Hizmet' }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags((string)($h['kisa'] ?? $h['aciklama'] ?? '')), 100) }}</p>
                    <span>Detay</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="th-modern-section th-modern-about">
    <div class="container th-modern-about-grid">
        <div class="th-modern-about-photo">
            <img src="{{ $photo }}" alt="">
        </div>
        <div>
            <p class="th-modern-eyebrow">Hakkımda</p>
            <h2>{{ $doktor['uzmanlik'] ?? 'Uzman yaklaşım' }}</h2>
            <p>{{ $doktor['kisa_bio'] ?? '' }}</p>
            <a href="{{ route('frontend.hakkimda') }}" class="th-modern-ghost">Devamını oku</a>
        </div>
    </div>
</section>

@if($bloglar->isNotEmpty())
<section class="th-modern-section">
    <div class="container">
        <div class="th-modern-section-head">
            <h2>Blog</h2>
            <a href="{{ route('frontend.blog') }}">Tümü →</a>
        </div>
        <div class="th-modern-blog">
            @foreach ($bloglar as $b)
                <a href="{{ route('frontend.blog.detay', $b['slug'] ?? '') }}" class="th-modern-blog-item">
                    <h3>{{ $b['baslik'] ?? '' }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags((string)($b['ozet'] ?? $b['icerik'] ?? '')), 90) }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="th-modern-cta-band">
    <div class="container">
        <h2>Randevunuzu planlayın</h2>
        <p>Yüz yüze veya online görüşme — platform üzerinden.</p>
        <a href="{{ route('frontend.randevu') }}" class="th-modern-cta th-modern-cta-lg">Randevu al</a>
    </div>
</section>
@endsection
