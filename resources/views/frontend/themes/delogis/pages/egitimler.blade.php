@extends(theme_layout())

@section('baslik', 'Eğitimler | '.($doktor['ad_soyad'] ?? 'Hekim'))
@section('meta_aciklama', 'Kurs, webinar ve eğitim programları.')

@section('icerik')
<section class="mp-page-hero">
    <div class="mp-container">
        <div class="mp-breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>Eğitimler</span>
        </div>
        <h1>Eğitimler</h1>
        <p>Kurs, webinar ve workshop programları.</p>
    </div>
</section>

<section class="mp-section mp-page">
    <div class="mp-container">
        <div class="mp-svc-grid">
            @forelse (($doktor['egitimler'] ?? []) as $e)
                <a href="{{ route('frontend.egitim.detay', $e['slug'] ?? $e['id'] ?? '') }}" class="mp-svc-card">
                    @if(!empty($e['image']))
                        <img src="{{ $e['image'] }}" alt="" style="width:100%;height:140px;object-fit:cover;border-radius:6px;margin-bottom:14px" loading="lazy">
                    @else
                        <div class="mp-svc-icon">🎓</div>
                    @endif
                    <span class="mp-chip" style="align-self:flex-start;margin-bottom:8px">{{ $e['tip'] ?? 'eğitim' }}</span>
                    <h3>{{ $e['baslik'] ?? '' }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags((string)($e['ozet'] ?? '')), 120) }}</p>
                    <div class="mp-svc-meta">
                        @if(!empty($e['baslangic_label']))
                            <span class="mp-chip">{{ $e['baslangic_label'] }}</span>
                        @endif
                        @if(!empty($e['fiyat_label']))
                            <span class="mp-chip">{{ $e['fiyat_label'] }}</span>
                        @endif
                    </div>
                    <span class="mp-svc-link">Detay →</span>
                </a>
            @empty
                <p class="mp-book-empty">Henüz eğitim yayınlanmamış.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
