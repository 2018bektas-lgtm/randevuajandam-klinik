@extends(theme_layout())

@section('baslik', 'Galeri | '.($doktor['ad_soyad'] ?? 'Hekim'))
@section('meta_aciklama', 'Klinik ve muayenehane görselleri.')

@section('icerik')
<section class="mp-page-hero">
    <div class="mp-container">
        <div class="mp-breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>Galeri</span>
        </div>
        <h1>Galeri</h1>
        <p>Klinik ve muayene ortamından kareler.</p>
    </div>
</section>

<section class="mp-section mp-page">
    <div class="mp-container">
        <div class="mp-svc-grid">
            @forelse (($doktor['galeri'] ?? []) as $g)
                <div class="mp-card" style="padding:0;overflow:hidden">
                    <img src="{{ $g['image'] ?? '' }}" alt="{{ $g['baslik'] ?? 'Galeri' }}" style="width:100%;height:220px;object-fit:cover;display:block" loading="lazy">
                    @if(!empty($g['baslik']))
                        <div style="padding:12px 14px;font-weight:600;color:var(--mp-navy)">{{ $g['baslik'] }}</div>
                    @endif
                </div>
            @empty
                <p class="mp-book-empty">Galeri henüz boş.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
