@extends(theme_layout())

@section('baslik', 'Hizmetler | '.($doktor['ad_soyad'] ?? 'Hekim'))
@section('meta_aciklama', ($doktor['uzmanlik'] ?? 'Hekimlik').' alanında sunduğum hizmetler.')

@section('icerik')
@php
    $hizmetler = collect($doktor['hizmetler'] ?? [])
        ->filter(fn ($h) => is_array($h) && (filled($h['baslik'] ?? null) || filled($h['ad'] ?? null) || filled($h['id'] ?? null)))
        ->values();
@endphp
<section class="mp-page-hero">
    <div class="mp-container">
        <div class="mp-breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>Hizmetler</span>
        </div>
        <h1>Hizmet & tedavi alanları</h1>
        <p>{{ $doktor['uzmanlik'] ?? 'Uzmanlık alanım' }} kapsamında randevu alabileceğiniz aktif hizmetler.</p>
    </div>
</section>

<section class="mp-section mp-page" id="hizmetler">
    <div class="mp-container">
        @if($hizmetler->isEmpty())
            <div class="mp-card" style="text-align:center;padding:2.5rem 1.5rem">
                <p style="margin:0 0 1rem;color:var(--muted)">Henüz yayınlanmış hizmet bulunamadı.</p>
                <p style="margin:0 0 1.25rem;font-size:.9rem;color:var(--muted)">
                    Hizmetler ana platform paneline eklendikten sonra burada listelenir.
                    @if(!empty($doktor['api_error']))
                        <br><small>API: {{ $doktor['api_error'] }}</small>
                    @endif
                </p>
                <a href="{{ route('frontend.randevu') }}" class="mp-btn mp-btn-primary">Randevu Al</a>
            </div>
        @else
            <div class="mp-svc-grid">
                @foreach ($hizmetler as $hizmet)
                    @php
                        $hAd = $hizmet['baslik'] ?? $hizmet['ad'] ?? 'Hizmet';
                        $hSlug = $hizmet['slug'] ?? \Illuminate\Support\Str::slug($hAd);
                        $hDesc = $hizmet['kisa'] ?: \Illuminate\Support\Str::limit(strip_tags((string)($hizmet['aciklama'] ?? '')), 160);
                    @endphp
                    <a href="{{ route('frontend.hizmet.detay', $hSlug ?: ($hizmet['id'] ?? '')) }}" class="mp-svc-card" id="{{ $hSlug }}">
                        @if(!empty($hizmet['image']))
                            <img src="{{ $hizmet['image'] }}" alt="{{ $hAd }}" class="mp-svc-thumb" loading="lazy">
                        @else
                            <div class="mp-svc-icon">✚</div>
                        @endif
                        <h3>{{ $hAd }}</h3>
                        <p>{{ $hDesc !== '' ? $hDesc : 'Detay ve randevu için tıklayın.' }}</p>
                        <div class="mp-svc-meta">
                            @if(!empty($hizmet['sure']))
                                <span class="mp-chip">⏱ {{ $hizmet['sure'] }}</span>
                            @endif
                            @if(!empty($hizmet['fiyat']))
                                <span class="mp-chip">{{ $hizmet['fiyat'] }}</span>
                            @endif
                        </div>
                        <span class="mp-svc-link">Detay &amp; randevu →</span>
                    </a>
                @endforeach
            </div>
            <div style="text-align:center;margin-top:32px">
                <a href="{{ route('frontend.randevu') }}" class="mp-btn mp-btn-primary mp-btn-lg">Randevu Al</a>
            </div>
        @endif
    </div>
</section>
@endsection
