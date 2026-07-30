@php
    $footerNav = function_exists('site_footer_nav')
        ? site_footer_nav($doktor ?? null)
        : [];
@endphp
<footer class="th-modern-footer">
    <div class="container th-modern-footer-grid">
        <div>
            <div class="th-modern-footer-brand">{{ trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? '')) }}</div>
            <p>{{ $doktor['footer_metin'] ?? $doktor['kisa_bio'] ?? '' }}</p>
        </div>
        <div class="th-modern-footer-links">
            @foreach ($footerNav as $item)
                <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
            @endforeach
        </div>
        <div class="th-modern-footer-cta">
            <a href="{{ route('frontend.randevu') }}" class="th-modern-cta">Randevu Al</a>
            @if(!empty($doktor['telefon']))
                <a href="tel:{{ $doktor['telefon_raw'] ?? '' }}" class="th-modern-phone">{{ $doktor['telefon'] }}</a>
            @endif
        </div>
    </div>
    <div class="th-modern-footer-bar">
        <div class="container">© {{ date('Y') }} · Platform teması: Modern Tech</div>
    </div>
</footer>
