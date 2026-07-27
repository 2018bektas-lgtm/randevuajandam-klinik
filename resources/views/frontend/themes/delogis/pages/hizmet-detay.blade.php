@extends(theme_layout())

@php
    $hSlug = $hizmet['slug'] ?? \Illuminate\Support\Str::slug($hizmet['baslik'] ?? $hizmet['ad'] ?? 'hizmet');
    $baslik = $hizmet['baslik'] ?? $hizmet['ad'] ?? 'Hizmet';
@endphp
@section('baslik', $baslik.' | '.($doktor['ad_soyad'] ?? 'Hekim'))
@section('meta_aciklama', $hizmet['kisa'] ?? \Illuminate\Support\Str::limit(strip_tags((string)($hizmet['aciklama'] ?? '')), 160))

@section('icerik')
<section class="mp-page-hero">
    <div class="mp-container">
        <div class="mp-breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <a href="{{ route('frontend.hizmetler') }}">Hizmetler</a>
            <span>/</span>
            <span>{{ $baslik }}</span>
        </div>
        <h1>{{ $baslik }}</h1>
        <p>{{ $hizmet['kisa'] ?? ($doktor['uzmanlik'] ?? '') }}</p>
    </div>
</section>

<section class="mp-section mp-page">
    <div class="mp-container">
        <div class="mp-about-grid">
            <div class="mp-about-photo">
                <img src="{{ $hizmet['image'] ?? ($doktor['profil_resmi'] ?? '') }}" alt="{{ $baslik }}">
            </div>
            <div>
                <div class="mp-svc-meta" style="margin-bottom:1rem">
                    @if(!empty($hizmet['sure']))
                        <span class="mp-chip">{{ $hizmet['sure'] }}</span>
                    @endif
                    @if(!empty($hizmet['fiyat']))
                        <span class="mp-chip">{{ $hizmet['fiyat'] }}</span>
                    @endif
                </div>
                <div class="mp-card">
                    @if(!empty($hizmet['aciklama']))
                        <div style="color:var(--muted);line-height:1.7">{!! nl2br(e(strip_tags((string)$hizmet['aciklama']))) !!}</div>
                    @else
                        <p style="color:var(--muted)">{{ $hizmet['kisa'] ?? 'Detaylı bilgi için randevu alarak görüşebilirsiniz.' }}</p>
                    @endif
                    @if(!empty($hizmet['madde']))
                        <ul class="mp-about-check" style="margin-top:1rem">
                            @foreach ($hizmet['madde'] as $m)
                                <li>{{ $m }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div style="margin-top:20px">
                    <a href="{{ route('frontend.randevu') }}" class="mp-btn mp-btn-primary mp-btn-lg">Bu hizmet için randevu al</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
