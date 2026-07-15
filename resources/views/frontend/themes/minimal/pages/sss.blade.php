@extends(theme_layout())

@section('baslik', 'S.S.S. | '.($doktor['ad_soyad'] ?? 'Hekim'))
@section('meta_aciklama', 'Randevu ve klinik hakkında sık sorulan sorular.')

@section('icerik')
<div class="th-min-page">
<section class="page-hero th-min-page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>S.S.S.</span>
        </div>
        <h1>Sıkça sorulan sorular</h1>
        <p>{{ $doktor['unvan'] ?? '' }} {{ $doktor['ad_soyad'] ?? '' }} kliniği hakkında merak edilenler.</p>
    </div>
</section>

<section class="section th-min-section">
    <div class="container" style="max-width:820px;margin-inline:auto">
        <div class="faq">
            @forelse (($doktor['sss'] ?? []) as $item)
                <details {{ $loop->first ? 'open' : '' }}>
                    <summary>{{ $item['soru'] }}</summary>
                    <div class="prose" style="padding:.25rem 0 .5rem">
                        {!! nl2br(e($item['cevap'] ?? '')) !!}
                    </div>
                </details>
            @empty
                <div class="card card-pad" style="text-align:center">
                    <p class="text-muted" style="margin:0">Henüz SSS kaydı eklenmemiş.</p>
                </div>
            @endforelse
        </div>

        <div class="cta-band mt-3" style="margin-top:2.5rem">
            <div>
                <h2 class="section-title" style="color:#fff;margin:0">Hâlâ sorunuz mu var?</h2>
                <p style="color:rgba(255,255,255,.8);margin:.75rem 0 0">Randevu veya muayene hakkında bize yazın / arayın.</p>
            </div>
            <div class="hero-actions">
                <a href="{{ route('frontend.iletisim') }}" class="btn btn-gold">İletişime Geç</a>
                @if(!empty($doktor['telefon_raw']))
                    <a href="tel:{{ $doktor['telefon_raw'] }}" class="btn btn-outline">{{ $doktor['telefon'] }}</a>
                @endif
            </div>
        </div>
    </div>
</section>
</div>
@endsection
