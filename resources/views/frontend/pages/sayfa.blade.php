@extends(theme_layout())

@php
    $pageTitle = trim((string) ($page->meta_baslik ?? '')) !== ''
        ? $page->meta_baslik
        : ($baslik ?? 'Sayfa');
    $pageDesc = trim((string) ($page->meta_aciklama ?? ''));
    if ($pageDesc === '') {
        $pageDesc = \Illuminate\Support\Str::limit(strip_tags((string) ($icerik ?? '')), 160);
    }
    $pageKw = trim((string) ($page->meta_anahtar_kelimeler ?? ''));
    $siteName = $doktor['klinik_adi'] ?? $doktor['ad_soyad'] ?? 'Klinik';
@endphp

@section('baslik', $pageTitle.' | '.$siteName)
@section('meta_aciklama', $pageDesc)
@section('meta_anahtar', $pageKw)

@section('icerik')
<section class="fe-page" style="padding:2.5rem 0 4rem">
    <div class="container" style="max-width:48rem;margin:0 auto;padding:0 1rem">
        <h1 style="font-size:1.5rem;font-weight:800;margin:0 0 1.25rem;line-height:1.25">{{ $baslik }}</h1>
        <div class="sayfa-body dg-prose" style="font-size:15px;line-height:1.75;color:#1e293b">
            {!! $icerik !!}
        </div>
        <p style="margin-top:2rem">
            <a href="{{ route('frontend.anasayfa') }}" style="font-size:13px;font-weight:600">← Ana sayfa</a>
        </p>
    </div>
</section>
@endsection
