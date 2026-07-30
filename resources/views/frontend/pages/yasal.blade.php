@extends(theme_layout())

@section('baslik', ($baslik ?? 'Yasal') . ' | ' . ($doktor['klinik_adi'] ?? $doktor['ad_soyad'] ?? 'Klinik'))
@section('meta_aciklama', \Illuminate\Support\Str::limit(strip_tags((string) ($icerik ?? '')), 160))

@section('icerik')
@php
    $ad = $doktor['klinik_adi'] ?? $doktor['ad_soyad'] ?? 'Klinik';
@endphp
<section class="fe-page" style="padding:2.5rem 0 4rem">
    <div class="container" style="max-width:48rem;margin:0 auto;padding:0 1rem">
        <p style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--brand,#C96A2B);margin:0 0 .5rem">Yasal bilgilendirme</p>
        <h1 style="font-size:1.5rem;font-weight:800;margin:0 0 1rem;line-height:1.25">{{ $baslik }}</h1>
        <p style="font-size:12px;color:#64748b;margin:0 0 1.5rem">
            Bu metin <strong>{{ $ad }}</strong> web sitesi ziyaretçileri içindir.
            Randevu Ajandam platform aboneliği yasal metinleri
            <a href="https://randevuajandam.com/kvkk" target="_blank" rel="noopener">randevuajandam.com</a> üzerindedir.
        </p>
        <div class="yasal-body" style="font-size:14px;line-height:1.75;color:#1e293b;white-space:pre-wrap">
            {!! nl2br(e($icerik)) !!}
        </div>
        <p style="margin-top:2rem">
            <a href="{{ route('frontend.anasayfa') }}" style="font-size:13px;font-weight:600">← Ana sayfa</a>
        </p>
    </div>
</section>
@endsection
