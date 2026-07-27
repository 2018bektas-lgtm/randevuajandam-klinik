@extends(theme_layout())

@section('baslik', 'SSS | '.($doktor['ad_soyad'] ?? 'Hekim'))
@section('meta_aciklama', 'Sık sorulan sorular.')

@section('icerik')
<section class="mp-page-hero">
    <div class="mp-container">
        <div class="mp-breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>SSS</span>
        </div>
        <h1>Sık sorulan sorular</h1>
        <p>Merak ettikleriniz için kısa yanıtlar.</p>
    </div>
</section>

<section class="mp-section mp-page">
    <div class="mp-container" style="max-width:800px">
        @forelse (($doktor['sss'] ?? []) as $i => $item)
            <div class="mp-card" style="margin-bottom:12px">
                <h3 style="margin:0 0 8px;font-size:1.05rem">{{ $item['soru'] ?? '' }}</h3>
                <p style="margin:0;color:var(--muted);line-height:1.65">{{ $item['cevap'] ?? '' }}</p>
            </div>
        @empty
            <p class="mp-book-empty">Henüz SSS eklenmemiş.</p>
        @endforelse
        <div style="text-align:center;margin-top:28px">
            <a href="{{ route('frontend.randevu') }}" class="mp-btn mp-btn-primary">Randevu Al</a>
        </div>
    </div>
</section>
@endsection
