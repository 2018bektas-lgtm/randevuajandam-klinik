@extends(theme_layout())

@section('baslik', ($yazi['baslik'] ?? 'Yazı').' | Blog')
@section('meta_aciklama', $yazi['ozet'] ?? '')

@section('icerik')
<div class="th-modern-page">
<section class="page-hero th-modern-page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <a href="{{ route('frontend.blog') }}">Blog</a>
            <span>/</span>
            <span>{{ $yazi['kategori'] ?? 'Yazı' }}</span>
        </div>
        <h1 style="max-width:46rem">{{ $yazi['baslik'] }}</h1>
        <p>
            @if(!empty($yazi['tarih'])){{ $yazi['tarih'] }} · @endif
            @if(!empty($yazi['okuma'])){{ $yazi['okuma'] }} okuma · @endif
            {{ $doktor['unvan'] ?? '' }} {{ $doktor['ad_soyad'] ?? '' }}
        </p>
    </div>
</section>

<section class="section th-modern-section">
    <div class="container" style="display:grid;gap:2rem;grid-template-columns:1fr;max-width:820px;margin-inline:auto">
        @if(!empty($yazi['image']))
            <div class="media-frame">
                <img src="{{ $yazi['image'] }}" alt="{{ $yazi['baslik'] }}" style="min-height:320px">
            </div>
        @endif
        <article class="card card-pad prose">
            @if(!empty($yazi['ozet']))
                <p style="font-size:1.05rem;color:#0f172a;font-weight:500">{{ $yazi['ozet'] }}</p>
            @endif

            @if(!empty($yazi['icerik_html']))
                <div class="blog-html">{!! $yazi['icerik_html'] !!}</div>
            @else
                @foreach (($yazi['icerik'] ?? []) as $par)
                    <p>{{ $par }}</p>
                @endforeach
            @endif

            <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--line)">
                <p class="text-muted" style="font-size:.9rem">Bu içerik bilgilendirme amaçlıdır; tanı ve tedavi yerine geçmez. Kişisel durumunuz için randevu almanızı öneririz.</p>
                <a href="{{ route('frontend.randevu') }}" class="btn btn-primary">Muayene Randevusu</a>
            </div>
        </article>

        @php
            $digerler = collect($doktor['bloglar'] ?? [])->where('slug', '!=', $yazi['slug'] ?? '')->take(2);
        @endphp
        @if($digerler->isNotEmpty())
            <div>
                <h3 class="section-title" style="font-size:1.6rem;margin-bottom:1rem">Diğer yazılar</h3>
                <div class="grid-2">
                    @foreach ($digerler as $diger)
                        <a href="{{ route('frontend.blog.detay', $diger['slug']) }}" class="card blog-card" style="text-decoration:none;color:inherit">
                            <img src="{{ $diger['image'] }}" alt="{{ $diger['baslik'] }}" loading="lazy">
                            <div class="card-pad">
                                <h3 style="font-size:1.15rem">{{ $diger['baslik'] }}</h3>
                                <p class="text-muted" style="margin:0;font-size:.88rem">{{ $diger['ozet'] }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
</div>
@endsection
