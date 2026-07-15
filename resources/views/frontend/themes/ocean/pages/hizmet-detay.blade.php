@extends(theme_layout())

@php
    $hSlug = $hizmet['slug'] ?? \Illuminate\Support\Str::slug($hizmet['baslik'] ?? 'hizmet');
@endphp
@section('baslik', ($hizmet['baslik'] ?? 'Hizmet').' | '.($doktor['ad_soyad'] ?? 'Hekim'))
@section('meta_aciklama', $hizmet['kisa'] ?? \Illuminate\Support\Str::limit(strip_tags((string)($hizmet['aciklama'] ?? '')), 160))

@section('icerik')
<div class="th-ocean-page">
<section class="page-hero th-ocean-page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <a href="{{ route('frontend.hizmetler') }}">Hizmetler</a>
            <span>/</span>
            <span>{{ $hizmet['baslik'] ?? 'Hizmet' }}</span>
        </div>
        <h1>{{ $hizmet['baslik'] ?? 'Hizmet' }}</h1>
        <p>{{ $hizmet['kisa'] ?? ($doktor['uzmanlik'] ?? '') }}</p>
    </div>
</section>

<section class="section th-ocean-section">
    <div class="container two-col" style="align-items:start">
        <div class="media-frame">
            <img src="{{ $hizmet['image'] ?? ($doktor['profil_resmi'] ?? '') }}" alt="{{ $hizmet['baslik'] ?? 'Hizmet' }}">
        </div>
        <div>
            <div class="service-meta" style="margin-bottom:1rem">
                @if(!empty($hizmet['sure']))
                    <span class="chip">Süre: {{ $hizmet['sure'] }}</span>
                @endif
                @if(!empty($hizmet['fiyat']))
                    <span class="chip chip-gold">{{ $hizmet['fiyat'] }}</span>
                @endif
            </div>

            <div class="prose card card-pad">
                @if(!empty($hizmet['aciklama']))
                    <div class="blog-html">{!! nl2br(e(strip_tags((string)$hizmet['aciklama']))) !!}</div>
                @else
                    <p>{{ $hizmet['kisa'] ?? 'Detaylı bilgi için randevu alarak hekimimizle görüşebilirsiniz.' }}</p>
                @endif

                @if(!empty($hizmet['madde']))
                    <ul class="service-list" style="margin-top:1rem">
                        @foreach ($hizmet['madde'] as $m)
                            <li>{{ $m }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="hero-actions mt-3" style="display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.25rem">
                <a href="{{ route('frontend.randevu') }}" class="btn btn-primary">Bu hizmet için randevu</a>
                <a href="{{ route('frontend.hizmetler') }}" class="btn btn-dark-outline">Tüm hizmetler</a>
            </div>
        </div>
    </div>
</section>

@php
    $digerler = collect($doktor['hizmetler'] ?? [])
        ->filter(fn ($h) => ($h['slug'] ?? \Illuminate\Support\Str::slug($h['baslik'] ?? '')) !== $hSlug)
        ->take(3);
@endphp
@if($digerler->isNotEmpty())
<section class="section bg-white" style="padding-top:0">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Diğer</span>
                <h2 class="section-title" style="font-size:1.6rem">Benzer hizmetler</h2>
            </div>
        </div>
        <div class="grid-2">
            @foreach ($digerler as $d)
                @php $dSlug = $d['slug'] ?? \Illuminate\Support\Str::slug($d['baslik'] ?? ''); @endphp
                <a href="{{ route('frontend.hizmet.detay', $dSlug) }}" class="card service-card" style="text-decoration:none;color:inherit">
                    <img src="{{ $d['image'] }}" alt="{{ $d['baslik'] }}" loading="lazy">
                    <div class="card-pad">
                        <h3 style="font-size:1.15rem">{{ $d['baslik'] }}</h3>
                        <p class="text-muted" style="margin:0;font-size:.88rem">{{ $d['kisa'] }}</p>
                        <span class="link-more" style="display:inline-block;margin-top:.75rem">Detay →</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
</div>
@endsection
