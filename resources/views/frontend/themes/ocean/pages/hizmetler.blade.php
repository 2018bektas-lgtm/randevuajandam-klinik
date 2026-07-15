@extends(theme_layout())

@php $klinikAd = $doktor['klinik_adi'] ?? $doktor['ad_soyad'] ?? 'Klinik'; @endphp
@section('baslik', 'Hizmetler | '.$klinikAd)
@section('meta_aciklama', $klinikAd.' bünyesinde sunulan hizmet ve tedavi alanları.')

@section('icerik')
<div class="th-ocean-page">
<section class="page-hero th-ocean-page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>Hizmetler</span>
        </div>
        <h1>Hizmet & tedavi alanları</h1>
        <p>{{ $klinikAd }} bünyesinde hekimlerimizin sunduğu aktif hizmetler. Randevu için hekim seçebilirsiniz.</p>
    </div>
</section>

<section class="section th-ocean-section">
    <div class="container grid-2">
        @forelse (($doktor['hizmetler'] ?? []) as $hizmet)
            @php $hSlug = $hizmet['slug'] ?? \Illuminate\Support\Str::slug($hizmet['baslik'] ?? ''); @endphp
            <article class="card service-card" id="{{ $hSlug }}">
                <a href="{{ route('frontend.hizmet.detay', $hSlug) }}" style="display:block;color:inherit;text-decoration:none">
                    <img src="{{ $hizmet['image'] }}" alt="{{ $hizmet['baslik'] }}" loading="lazy">
                </a>
                <div class="card-pad">
                    <h3><a href="{{ route('frontend.hizmet.detay', $hSlug) }}" style="color:inherit;text-decoration:none">{{ $hizmet['baslik'] }}</a></h3>
                    <p class="text-muted" style="margin:0">{{ $hizmet['kisa'] ?: \Illuminate\Support\Str::limit(strip_tags((string)($hizmet['aciklama'] ?? '')), 140) }}</p>
                    <div class="service-meta">
                        @if(!empty($hizmet['sure']))
                            <span class="chip">Süre: {{ $hizmet['sure'] }}</span>
                        @endif
                        @if(!empty($hizmet['fiyat']))
                            <span class="chip chip-gold">{{ $hizmet['fiyat'] }}</span>
                        @endif
                    </div>
                    @if(!empty($hizmet['madde']))
                        <ul class="service-list">
                            @foreach (array_slice($hizmet['madde'], 0, 3) as $m)
                                <li>{{ $m }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($hizmet['doktor_adi']))
                        <p style="margin:.5rem 0 0;font-size:.82rem;color:var(--brand-700);font-weight:600">{{ $hizmet['doktor_adi'] }}</p>
                    @endif
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1rem">
                        <a href="{{ route('frontend.hizmet.detay', $hSlug) }}" class="btn btn-dark-outline btn-sm">Detay</a>
                        <a href="{{ route('frontend.randevu') }}{{ !empty($hizmet['doktor_id']) ? '?doktor_id='.$hizmet['doktor_id'] : '' }}" class="btn btn-primary btn-sm">Randevu</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="card card-pad" style="grid-column:1/-1;text-align:center">
                <p class="text-muted">Henüz yayınlanmış hizmet bulunmuyor.</p>
            </div>
        @endforelse
    </div>
</section>

@if(!empty($doktor['surec']))
<section class="section th-ocean-section" style="padding-top:0">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Nasıl ilerler?</span>
                <h2 class="section-title">Klinik süreç</h2>
            </div>
        </div>
        <div class="process">
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
</div>
@endsection
