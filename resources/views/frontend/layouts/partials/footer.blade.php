@php
    $klinikAd = $doktor['klinik_adi'] ?? $doktor['ad_soyad'] ?? 'Klinik';
    $footerNav = function_exists('site_footer_nav')
        ? site_footer_nav($doktor ?? null)
        : [];
@endphp
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            @if(!empty($doktor['logo']))
                <a href="{{ route('frontend.anasayfa') }}" class="footer-logo-link">
                    <img src="{{ $doktor['logo'] }}" alt="{{ $klinikAd }}" class="footer-logo">
                </a>
            @endif
            <div class="footer-brand">{{ $klinikAd }}</div>
            <p style="margin:0;line-height:1.7;font-size:.92rem">{{ $doktor['footer_metin'] ?? $doktor['kisa_bio'] ?? '' }}</p>
            @php $sosyal = array_filter($doktor['sosyal'] ?? [], fn ($u) => filled($u)); @endphp
            @if(count($sosyal))
            <div class="socials">
                @foreach ($sosyal as $ad => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $ad }}">{{ strtoupper(mb_substr((string)$ad, 0, 2)) }}</a>
                @endforeach
            </div>
            @endif
        </div>

        <div>
            <h4>Keşfet</h4>
            <ul class="footer-list">
                @foreach ($footerNav as $item)
                    <li>
                        <a href="{{ $item['href'] }}" @if(!empty($item['external'])) target="_blank" rel="noopener" @endif>
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <h4>Hekimler</h4>
            <ul class="footer-list">
                @forelse (array_slice($doktor['hekimler'] ?? [], 0, 5) as $h)
                    <li>
                        <a href="{{ route('frontend.hekim.detay', $h['slug']) }}">
                            {{ $h['unvan'] ?? '' }} {{ $h['ad_soyad'] }}
                        </a>
                    </li>
                @empty
                    @foreach (array_slice($doktor['hizmetler'] ?? [], 0, 5) as $hz)
                        <li><a href="{{ route('frontend.hizmetler') }}">{{ $hz['baslik'] }}</a></li>
                    @endforeach
                @endforelse
            </ul>
        </div>

        <div>
            <h4>İletişim</h4>
            <ul class="footer-list">
                <li>{{ $doktor['adres'] ?? '' }}</li>
                @if(!empty($doktor['telefon']))
                    <li><a href="tel:{{ $doktor['telefon_raw'] ?? '' }}">{{ $doktor['telefon'] }}</a></li>
                @endif
                @if(!empty($doktor['e_posta']))
                    <li><a href="mailto:{{ $doktor['e_posta'] }}">{{ $doktor['e_posta'] }}</a></li>
                @endif
                <li><a class="btn btn-gold btn-sm" href="{{ route('frontend.randevu') }}" style="margin-top:.5rem;display:inline-flex">Randevu Al</a></li>
            </ul>
        </div>
    </div>

    <div class="container footer-bottom">
        <span>&copy; {{ date('Y') }} {{ $klinikAd }}. Tüm hakları saklıdır.</span>
        <span>
            Powered by Randevu Ajandam · Klinik Web
            @php $footerPages = site_footer_pages(); @endphp
            @if($footerPages !== [])
                <span class="footer-legal" style="display:block;margin-top:.45rem;font-size:11px;opacity:.9">
                    @foreach($footerPages as $i => $fp)
                        @if($i > 0) · @endif
                        <a href="{{ $fp['href'] }}">{{ $fp['baslik'] }}</a>
                    @endforeach
                </span>
            @endif
        </span>
    </div>
</footer>

@if(($doktor['whatsapp_goster'] ?? true) && !empty($doktor['whatsapp']))
<a class="wa-float" href="https://wa.me/{{ $doktor['whatsapp'] }}" target="_blank" rel="noopener" aria-label="WhatsApp">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M20.52 3.48A11.86 11.86 0 0012.04 0C5.5 0 .2 5.3.2 11.84c0 2.09.55 4.13 1.6 5.93L0 24l6.4-1.67a11.8 11.8 0 005.64 1.44h.01c6.54 0 11.84-5.3 11.84-11.84 0-3.16-1.23-6.13-3.47-8.45zM12.05 21.5h-.01a9.65 9.65 0 01-4.91-1.35l-.35-.21-3.8 1 1.01-3.7-.23-.38a9.65 9.65 0 01-1.48-5.14c0-5.34 4.35-9.69 9.7-9.69 2.59 0 5.02 1.01 6.85 2.84a9.63 9.63 0 012.84 6.85c0 5.34-4.35 9.68-9.62 9.68zm5.32-7.25c-.29-.15-1.72-.85-1.99-.95-.27-.1-.46-.15-.66.15-.19.29-.76.95-.93 1.14-.17.2-.34.22-.63.07-.29-.15-1.22-.45-2.33-1.43-.86-.77-1.44-1.72-1.61-2.01-.17-.29-.02-.45.13-.6.13-.13.29-.34.43-.51.15-.17.19-.29.29-.48.1-.2.05-.36-.02-.51-.07-.15-.66-1.59-.9-2.18-.24-.58-.48-.5-.66-.51h-.56c-.2 0-.51.07-.78.36-.27.29-1.02.99-1.02 2.42 0 1.43 1.05 2.81 1.19 3 .15.2 2.07 3.16 5.02 4.43.7.3 1.25.48 1.68.62.7.22 1.34.19 1.84.12.56-.08 1.72-.7 1.97-1.38.24-.68.24-1.26.17-1.38-.07-.12-.26-.2-.55-.34z"/>
    </svg>
</a>
@endif
