<footer class="th-ocean-footer">
    <div class="container th-ocean-footer-inner">
        <div>
            <strong>{{ trim(($doktor['unvan'] ?? '').' '.($doktor['ad_soyad'] ?? '')) }}</strong>
            <p>{{ $doktor['adres'] ?? '' }}</p>
        </div>
        <div>
            <a href="{{ route('frontend.randevu') }}">Randevu</a>
            <a href="{{ route('frontend.iletisim') }}">İletişim</a>
        </div>
        <div>© {{ date('Y') }} · Ocean tema</div>
    </div>
</footer>
