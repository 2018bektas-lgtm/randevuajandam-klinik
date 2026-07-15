@extends(theme_layout())

@section('baslik', 'Galeri | '.($doktor['ad_soyad'] ?? 'Hekim'))
@section('meta_aciklama', 'Klinik ve muayenehane görselleri.')

@section('icerik')
<div class="th-ocean-page">
<section class="page-hero th-ocean-page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>Galeri</span>
        </div>
        <h1>Klinik galeri</h1>
        <p>{{ $doktor['klinik_adi'] ?? ($doktor['ad_soyad'] ?? '') }} — muayenehane ve klinik ortamından kareler.</p>
    </div>
</section>

<section class="section th-ocean-section">
    <div class="container">
        @if(!empty($doktor['galeri']))
            <div class="section-head">
                <div>
                    <span class="eyebrow">Mekân</span>
                    <h2 class="section-title">Fotoğraf galerisi</h2>
                </div>
            </div>
            <div class="gallery-grid" id="gallery-lightbox">
                @foreach ($doktor['galeri'] as $i => $g)
                    <figure class="gallery-item" data-index="{{ $i }}" style="cursor:zoom-in">
                        <img src="{{ $g['image'] }}" alt="{{ $g['baslik'] ?? 'Galeri' }}" loading="lazy">
                        <figcaption>
                            <span>{{ $g['etiket'] ?? 'Klinik' }}</span>
                            <strong>{{ $g['baslik'] ?? 'Görsel' }}</strong>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @else
            <div class="card card-pad" style="text-align:center;max-width:520px;margin:0 auto">
                <p class="text-muted" style="margin:0 0 1rem">Henüz galeri fotoğrafı eklenmemiş.</p>
                <a href="{{ route('frontend.randevu') }}" class="btn btn-primary btn-sm">Randevu Al</a>
            </div>
        @endif
    </div>
</section>

@if(!empty($doktor['oncesi_sonrasi']))
<section class="section bg-white">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Sonuçlar</span>
                <h2 class="section-title">Öncesi / sonrası</h2>
            </div>
        </div>
        <div class="grid-2">
            @foreach ($doktor['oncesi_sonrasi'] as $item)
                <div class="ba-card">
                    <div class="ba-images">
                        <figure>
                            <img src="{{ $item['before'] }}" alt="Öncesi" loading="lazy">
                            <span>Öncesi</span>
                        </figure>
                        <figure>
                            <img src="{{ $item['after'] }}" alt="Sonrası" loading="lazy">
                            <span>Sonrası</span>
                        </figure>
                    </div>
                    <div class="ba-body">
                        <h3>{{ $item['baslik'] }}</h3>
                        <p>{{ $item['sure'] ?? '' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
</div>
@endsection
