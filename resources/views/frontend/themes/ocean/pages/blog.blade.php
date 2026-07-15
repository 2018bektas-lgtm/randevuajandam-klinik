@extends(theme_layout())

@section('baslik', 'Blog | '.($doktor['ad_soyad'] ?? 'Hekim'))
@section('meta_aciklama', ($doktor['uzmanlik'] ?? 'Sağlık').' hakkında bilgilendirici yazılar.')

@section('icerik')
<div class="th-ocean-page">
<section class="page-hero th-ocean-page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>Blog</span>
        </div>
        <h1>Sağlık blogu</h1>
        <p>{{ $doktor['unvan'] ?? '' }} {{ $doktor['ad_soyad'] ?? '' }} — {{ $doktor['uzmanlik'] ?? '' }} alanında bilgilendirici yazılar.</p>
    </div>
</section>

<section class="section th-ocean-section">
    <div class="container grid-2" style="gap:1.5rem">
        @forelse (($doktor['bloglar'] ?? []) as $yazi)
            <a href="{{ route('frontend.blog.detay', $yazi['slug']) }}" class="card blog-card" style="display:grid;grid-template-columns:1fr;overflow:hidden;text-decoration:none;color:inherit">
                <img src="{{ $yazi['image'] }}" alt="{{ $yazi['baslik'] }}" loading="lazy" style="height:220px;width:100%;object-fit:cover">
                <div class="card-pad">
                    <div class="meta" style="display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;margin-bottom:.65rem;font-size:.82rem;color:#64748b">
                        <span class="chip">{{ $yazi['kategori'] ?? 'Blog' }}</span>
                        @if(!empty($yazi['tarih']))<span>{{ $yazi['tarih'] }}</span>@endif
                        @if(!empty($yazi['okuma']))<span>{{ $yazi['okuma'] }}</span>@endif
                    </div>
                    <h3 style="margin:0 0 .5rem">{{ $yazi['baslik'] }}</h3>
                    <p class="text-muted" style="margin:0">{{ $yazi['ozet'] }}</p>
                    <span class="link-more" style="display:inline-block;margin-top:1rem">Devamını oku →</span>
                </div>
            </a>
        @empty
            <div class="card card-pad" style="grid-column:1/-1;text-align:center">
                <p class="text-muted" style="margin:0">Henüz yayınlanmış blog yazısı yok.</p>
                <a href="{{ route('frontend.randevu') }}" class="btn btn-primary btn-sm" style="margin-top:1rem">Randevu Al</a>
            </div>
        @endforelse
    </div>
</section>
</div>
@endsection
