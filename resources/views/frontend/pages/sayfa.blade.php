@extends(theme_layout())

@section('baslik', ($baslik ?? 'Sayfa') . ' | ' . ($doktor['klinik_adi'] ?? $doktor['ad_soyad'] ?? 'Klinik'))
@section('meta_aciklama', \Illuminate\Support\Str::limit(strip_tags((string) ($icerik ?? '')), 160))

@section('icerik')
<section class="fe-page" style="padding:2.5rem 0 4rem">
    <div class="container" style="max-width:48rem;margin:0 auto;padding:0 1rem">
        <h1 style="font-size:1.5rem;font-weight:800;margin:0 0 1.25rem;line-height:1.25">{{ $baslik }}</h1>
        <div class="sayfa-body" style="font-size:14px;line-height:1.75;color:#1e293b;white-space:pre-wrap">
            {!! nl2br(e($icerik)) !!}
        </div>
        <p style="margin-top:2rem">
            <a href="{{ route('frontend.anasayfa') }}" style="font-size:13px;font-weight:600">← Ana sayfa</a>
        </p>
    </div>
</section>
@endsection
