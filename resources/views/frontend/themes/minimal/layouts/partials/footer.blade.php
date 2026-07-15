<footer class="th-min-footer">
    <div class="container th-min-footer-inner">
        <div>{{ trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? '')) }}</div>
        <div class="th-min-footer-links">
            <a href="{{ route('frontend.hakkimda') }}">Hakkımda</a>
            <a href="{{ route('frontend.hizmetler') }}">Hizmetler</a>
            <a href="{{ route('frontend.iletisim') }}">İletişim</a>
        </div>
        <div class="th-min-copy">© {{ date('Y') }} · Minimal tema</div>
    </div>
</footer>
