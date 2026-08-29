@extends(theme_layout())

@php $klinikAd = $doktor['klinik_adi'] ?? $doktor['ad_soyad'] ?? 'Klinik'; @endphp
@section('baslik', 'Hekimlerimiz | '.$klinikAd)
@section('meta_aciklama', $klinikAd.' uzman hekim kadrosu ve online randevu.')

@section('icerik')
<div class="th-klasik-page">
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>Hekimlerimiz</span>
        </div>
        <h1>Hekimlerimiz</h1>
        <p>Uzman kadromuzdan size uygun hekimi seçerek online randevu oluşturun.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="team-grid team-grid-lg">
            @forelse (($doktor['hekimler'] ?? []) as $hekim)
                <article class="team-card team-card-lg">
                    <a href="{{ route('frontend.hekim.detay', $hekim['slug']) }}" class="team-card-media">
                        <img src="{{ $hekim['profil_resmi'] ?? avatar_placeholder(trim((string) ($hekim['unvan'] ?? '').' '.(string) ($hekim['ad_soyad'] ?? ''))) }}"
                             alt="{{ $hekim['ad_soyad'] }}" loading="lazy">
                        @if(!empty($hekim['randevuya_acik_mi']))
                            <span class="team-badge">Randevuya açık</span>
                        @endif
                    </a>
                    <div class="team-card-body">
                        <h3>
                            <a href="{{ route('frontend.hekim.detay', $hekim['slug']) }}">
                                {{ $hekim['unvan'] ?? '' }} {{ $hekim['ad_soyad'] }}
                            </a>
                        </h3>
                        <p class="team-card-meta">{{ $hekim['uzmanlik'] ?: implode(', ', $hekim['branslar'] ?? []) }}</p>
                        @if(!empty($hekim['branslar']))
                            <div class="team-tags">
                                @foreach (array_slice($hekim['branslar'], 0, 3) as $br)
                                    <span class="chip">{{ $br }}</span>
                                @endforeach
                            </div>
                        @endif
                        @if(!empty($hekim['kisa_bio']))
                            <p class="team-card-bio">{{ $hekim['kisa_bio'] }}</p>
                        @endif
                        <div class="team-card-actions">
                            <a href="{{ route('frontend.hekim.detay', $hekim['slug']) }}" class="btn btn-dark-outline btn-sm">Profil</a>
                            @if(!empty($hekim['randevuya_acik_mi']))
                                <a href="{{ route('frontend.randevu') }}?doktor_id={{ $hekim['id'] }}" class="btn btn-primary btn-sm">Randevu Al</a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="card card-pad" style="grid-column:1/-1;text-align:center">
                    <p class="text-muted">Henüz listelenecek hekim bulunmuyor. API bağlantısını ve klinik hekimlerini kontrol edin.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
</div>
@endsection
