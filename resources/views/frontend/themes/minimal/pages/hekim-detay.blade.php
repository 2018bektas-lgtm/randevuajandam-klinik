@extends(theme_layout())

@php $klinikAd = $doktor['klinik_adi'] ?? 'Klinik'; @endphp
@section('baslik', trim(($hekim['unvan'] ?? '').' '.($hekim['ad_soyad'] ?? 'Hekim')).' | '.$klinikAd)
@section('meta_aciklama', $hekim['kisa_bio'] ?? ($hekim['uzmanlik'] ?? ''))

@section('icerik')
<div class="th-min-page">
<section class="page-hero th-min-page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <a href="{{ route('frontend.hekimler') }}">Hekimlerimiz</a>
            <span>/</span>
            <span>{{ $hekim['ad_soyad'] }}</span>
        </div>
        <h1>{{ $hekim['unvan'] ?? '' }} {{ $hekim['ad_soyad'] }}</h1>
        <p>{{ $hekim['uzmanlik'] ?: implode(', ', $hekim['branslar'] ?? []) }} · {{ $klinikAd }}</p>
    </div>
</section>

<section class="section th-min-section">
    <div class="container two-col" style="align-items:start">
        <div class="media-frame">
            <img src="{{ $hekim['profil_resmi'] ?? avatar_placeholder(trim((string) ($hekim['unvan'] ?? '').' '.(string) ($hekim['ad_soyad'] ?? ''))) }}"
                 alt="{{ $hekim['ad_soyad'] }}" loading="lazy" decoding="async">
            @if(!empty($hekim['randevuya_acik_mi']))
                <div class="media-badge"><strong>Randevuya açık</strong><span>Online talep</span></div>
            @endif
        </div>
        <div>
            @if(!empty($hekim['branslar']))
                <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem">
                    @foreach ($hekim['branslar'] as $br)
                        <span class="chip">{{ $br }}</span>
                    @endforeach
                </div>
            @endif
            <div class="prose card card-pad">
                <span class="eyebrow">Hekim profili</span>
                <h2 style="font-size:1.5rem;margin:.35rem 0 1rem">{{ $hekim['unvan'] ?? '' }} {{ $hekim['ad_soyad'] }}</h2>
                <p>{{ $hekim['kisa_bio'] ?: 'Detaylı bilgi ve randevu için iletişime geçebilir veya online randevu oluşturabilirsiniz.' }}</p>
            </div>
            <div class="hero-actions" style="display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.25rem">
                @if(!empty($hekim['randevuya_acik_mi']))
                    <a href="{{ route('frontend.randevu') }}?doktor_id={{ $hekim['id'] }}" class="btn btn-primary">Bu hekimden randevu</a>
                @endif
                <a href="{{ route('frontend.hekimler') }}" class="btn btn-dark-outline">Tüm hekimler</a>
                <a href="{{ route('frontend.hizmetler') }}" class="btn btn-dark-outline">Hizmetler</a>
            </div>
        </div>
    </div>
</section>

@php
    $hekimHizmet = collect($doktor['hizmetler'] ?? [])->filter(fn ($h) => (int)($h['doktor_id'] ?? 0) === (int)($hekim['id'] ?? 0))->take(4);
@endphp
@if($hekimHizmet->isNotEmpty())
<section class="section bg-white" style="padding-top:0">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Hizmetler</span>
                <h2 class="section-title" style="font-size:1.6rem">Bu hekimin hizmetleri</h2>
            </div>
        </div>
        <div class="grid-2">
            @foreach ($hekimHizmet as $h)
                <a href="{{ route('frontend.hizmet.detay', $h['slug'] ?? \Illuminate\Support\Str::slug($h['baslik'] ?? '')) }}" class="card service-card" style="text-decoration:none;color:inherit">
                    <img src="{{ $h['image'] }}" alt="{{ $h['baslik'] }}" loading="lazy">
                    <div class="card-pad">
                        <h3 style="font-size:1.1rem">{{ $h['baslik'] }}</h3>
                        <p class="text-muted" style="margin:0;font-size:.88rem">{{ $h['kisa'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
</div>
@endsection
