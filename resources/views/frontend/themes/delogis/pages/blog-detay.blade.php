@extends(theme_layout())

@section('baslik', ($yazi['baslik'] ?? 'Yazı').' | Blog')
@section('meta_aciklama', $yazi['ozet'] ?? '')

@section('icerik')
<section class="mp-page-hero">
    <div class="mp-container">
        <div class="mp-breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <a href="{{ route('frontend.blog') }}">Blog</a>
            <span>/</span>
            <span>{{ $yazi['kategori'] ?? 'Yazı' }}</span>
        </div>
        <h1 style="max-width:46rem">{{ $yazi['baslik'] ?? '' }}</h1>
        <p>
            @if(!empty($yazi['tarih'])){{ $yazi['tarih'] }} · @endif
            @if(!empty($yazi['okuma'])){{ $yazi['okuma'] }} okuma · @endif
            {{ trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? '')) }}
        </p>
    </div>
</section>

<section class="mp-section mp-page">
    <div class="mp-container" style="max-width:820px">
        @if(!empty($yazi['image']))
            <div class="mp-about-photo" style="margin-bottom:24px">
                <img src="{{ $yazi['image'] }}" alt="{{ $yazi['baslik'] ?? '' }}" style="max-height:380px">
            </div>
        @endif
        <article class="mp-card">
            @if(!empty($yazi['ozet']))
                <p style="font-size:1.05rem;color:var(--mp-navy);font-weight:500;line-height:1.6">{{ $yazi['ozet'] }}</p>
            @endif
            @if(!empty($yazi['icerik_html']))
                <div class="blog-html" style="color:var(--muted);line-height:1.75">{!! $yazi['icerik_html'] !!}</div>
            @elseif(!empty($yazi['icerik']) && is_array($yazi['icerik']))
                @foreach ($yazi['icerik'] as $p)
                    <p style="color:var(--muted);line-height:1.75">{{ $p }}</p>
                @endforeach
            @endif
        </article>
        <div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap">
            <a href="{{ route('frontend.blog') }}" class="mp-btn mp-btn-outline">← Blog</a>
            <a href="{{ route('frontend.randevu') }}" class="mp-btn mp-btn-primary">Randevu Al</a>
        </div>
    </div>
</section>
@endsection
