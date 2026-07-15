@php
    $footerNav = ! empty($doktor['menu']) && is_array($doktor['menu'])
        ? collect($doktor['menu'])->filter(fn ($i) => ($i['key'] ?? '') !== 'anasayfa')->map(fn ($item) => [
            'href' => nav_href($item),
            'label' => $item['label'] ?? '',
        ])->values()->all()
        : [
            ['href' => route('frontend.hakkimda'), 'label' => 'Hakkımda'],
            ['href' => route('frontend.hizmetler'), 'label' => 'Hizmetler'],
            ['href' => route('frontend.blog'), 'label' => 'Blog'],
            ['href' => route('frontend.iletisim'), 'label' => 'İletişim'],
        ];
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
