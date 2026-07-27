@extends(theme_layout())

@section('baslik', 'Blog | '.($doktor['ad_soyad'] ?? 'Hekim'))
@section('meta_aciklama', 'Sağlık yazıları ve bilgilendirmeler.')

@section('icerik')
<section class="mp-page-hero">
    <div class="mp-container">
        <div class="mp-breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>Blog</span>
        </div>
        <h1>Blog & yazılar</h1>
        <p>Güncel bilgilendirmeler ve sağlık içerikleri.</p>
    </div>
</section>

<section class="mp-section mp-page">
    <div class="mp-container">
        <div class="mp-blog-grid">
            @forelse (($doktor['bloglar'] ?? []) as $b)
                <a href="{{ route('frontend.blog.detay', $b['slug'] ?? '') }}" class="mp-blog-card">
                    <img src="{{ $b['image'] ?? 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=800&q=80' }}" alt="" loading="lazy">
                    <div class="mp-blog-body">
                        @if(!empty($b['tarih']))
                            <div class="mp-blog-date">{{ $b['tarih'] }}</div>
                        @endif
                        <h3>{{ $b['baslik'] ?? '' }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags((string)($b['ozet'] ?? '')), 120) }}</p>
                    </div>
                </a>
            @empty
                <p class="mp-book-empty">Henüz blog yazısı yok.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
