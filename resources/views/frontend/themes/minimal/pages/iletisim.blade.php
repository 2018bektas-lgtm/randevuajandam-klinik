@extends(theme_layout())

@php
    $iletisim = $doktor['iletisim_sayfa'] ?? [];
    $pageBaslik = $iletisim['baslik'] ?? 'İletişim & online randevu';
    $pageAlt = $iletisim['alt_metin'] ?? 'Hekim seçerek hesap oluşturmadan randevu talebi bırakabilirsiniz. Onay sonrası bilgilendirilirsiniz.';
    $formGoster = (bool) ($iletisim['form_goster'] ?? true);
    $haritaGoster = (bool) ($iletisim['harita_goster'] ?? true);
    $saatlerGoster = (bool) ($iletisim['saatler_goster'] ?? true);
@endphp

@section('baslik', $pageBaslik.' | '.($doktor['ad_soyad'] ?? 'Hekim'))
@section('meta_aciklama', $pageAlt)

@section('icerik')
<div class="th-min-page">
<section class="page-hero th-min-page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <span>İletişim</span>
        </div>
        <h1>{{ $pageBaslik }}</h1>
        <p>{{ $pageAlt }}</p>
    </div>
</section>

<section class="section th-min-section" id="randevu">
    <div class="container" style="display:grid;gap:1.5rem">
        <div class="features">
            <div class="feature">
                <div class="feature-icon">☎</div>
                <h3>Telefon</h3>
                <p><a href="tel:{{ $doktor['telefon_raw'] ?? '' }}">{{ $doktor['telefon'] ?? '—' }}</a></p>
            </div>
            <div class="feature">
                <div class="feature-icon">✉</div>
                <h3>E-posta</h3>
                <p><a href="mailto:{{ $doktor['e_posta'] ?? '' }}">{{ $doktor['e_posta'] ?? '—' }}</a></p>
            </div>
            <div class="feature">
                <div class="feature-icon">📍</div>
                <h3>Adres</h3>
                <p>{{ $doktor['adres'] ?? '—' }}</p>
            </div>
            <div class="feature">
                <div class="feature-icon">◷</div>
                <h3>Saatler</h3>
                <p>
                    @php
                        $cs = $doktor['calisma_saatleri'] ?? [];
                        $aktifGunler = collect($cs)->filter(fn ($v) => is_string($v) && stripos($v, 'kapal') === false)->take(3);
                    @endphp
                    @if($aktifGunler->isNotEmpty())
                        {{ $aktifGunler->map(fn ($v, $k) => mb_substr($k, 0, 3).' '.$v)->implode(' · ') }}
                    @else
                        Randevu ile
                    @endif
                </p>
            </div>
        </div>

        <div class="two-col" style="align-items:start">
            @if($formGoster)
            <div class="card card-pad">
                <span class="eyebrow">Randevu formu</span>
                <h2 class="section-title" style="font-size:1.9rem">Misafir randevu talebi</h2>
                <p class="section-sub">Kayıt zorunlu değildir. Telefon numaranız ile randevunuz sisteme işlenir.</p>

                <div id="booking-alert" class="booking-alert" hidden></div>
                <div id="booking-success" class="booking-success" hidden></div>

                <form id="guest-booking-form" class="form-grid mt-3" autocomplete="on">
                    @csrf
                    {{-- Honeypot captcha --}}
                    <div style="position:absolute;left:-9999px;opacity:0;height:0;overflow:hidden" aria-hidden="true">
                        <label>Website</label>
                        <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="field full">
                        <label>Hekim *</label>
                        <select name="doktor_id" id="doktor_id" required>
                            <option value="">Yükleniyor…</option>
                        </select>
                    </div>
                    <div class="field full">
                        <label>Hizmet *</label>
                        <select name="hizmet_id" id="hizmet_id" required>
                            <option value="">Önce hekim seçin</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Tarih *</label>
                        <input type="date" name="tarih" id="tarih" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="field">
                        <label>Saat *</label>
                        <select name="saat" id="saat" required>
                            <option value="">Önce tarih seçin</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Ad *</label>
                        <input type="text" name="ad" id="ad" required maxlength="100" placeholder="Adınız" autocomplete="given-name">
                    </div>
                    <div class="field">
                        <label>Soyad *</label>
                        <input type="text" name="soyad" id="soyad" required maxlength="100" placeholder="Soyadınız" autocomplete="family-name">
                    </div>
                    <div class="field">
                        <label>Telefon *</label>
                        <input type="tel" name="telefon" id="telefon" required maxlength="30" placeholder="05xx xxx xx xx" autocomplete="tel">
                    </div>
                    <div class="field">
                        <label>E-posta</label>
                        <input type="email" name="e_posta" id="e_posta" maxlength="255" placeholder="opsiyonel@mail.com" autocomplete="email">
                    </div>
                    <div class="field full" id="otp-block" style="display:none">
                        <label>SMS doğrulama kodu</label>
                        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                            <input type="text" name="otp_kod" id="otp_kod" maxlength="6" placeholder="6 haneli kod" style="flex:1;min-width:140px"
                                   class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                            <button type="button" class="btn btn-dark-outline btn-sm" id="otp-send-btn">Kod Gönder</button>
                        </div>
                        <p class="text-muted" style="margin:.35rem 0 0;font-size:.78rem" id="otp-hint">Platform OTP zorunlu kıldığında kullanılır.</p>
                    </div>
                    <div class="field full">
                        <label>Not</label>
                        <textarea name="not" id="not" rows="3" maxlength="1000" placeholder="Kısaca belirtmek istediğiniz bir şey var mı?"></textarea>
                    </div>
                    <div class="field full" id="gorusme-tipi-block" style="display:none">
                        <label>Görüşme türü *</label>
                        <div style="display:flex;flex-direction:column;gap:.45rem;margin-top:.35rem">
                            <label style="display:flex;align-items:center;gap:.5rem;font-weight:500;cursor:pointer">
                                <input type="radio" name="gorusme_tipi" value="yuz_yuze" checked> Yüz yüze
                            </label>
                            <label style="display:flex;align-items:center;gap:.5rem;font-weight:500;cursor:pointer">
                                <input type="radio" name="gorusme_tipi" value="online"> Online görüşme
                            </label>
                        </div>
                        <p class="text-muted" style="margin:.4rem 0 0;font-size:.78rem;line-height:1.4">
                            Online seçilirse onay sonrası oda otomatik açılır. Hekimin paketinde yoksa sistem reddeder.
                        </p>
                    </div>
                    <div class="field full">
                        <label class="kvkk-label">
                            <input type="checkbox" name="kvkk_onay" id="kvkk_onay" value="1" required>
                            <span>Kişisel verilerimin randevu oluşturma amacıyla işlenmesini kabul ediyorum. *</span>
                        </label>
                    </div>
                    <div class="full">
                        <button type="submit" class="btn btn-primary" id="booking-submit">
                            Randevu Talebini Gönder
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="card card-pad">
                <span class="eyebrow">İletişim</span>
                <h2 class="section-title" style="font-size:1.9rem">Bize ulaşın</h2>
                <p class="section-sub">Online randevu formu şu an kapalı. Telefon veya e-posta ile iletişime geçebilirsiniz.</p>
                <div class="hero-actions mt-3" style="display:flex;flex-wrap:wrap;gap:.75rem">
                    @if(!empty($doktor['telefon_raw']))
                        <a href="tel:{{ $doktor['telefon_raw'] }}" class="btn btn-primary">{{ $doktor['telefon'] }}</a>
                    @endif
                    @if(!empty($doktor['e_posta']))
                        <a href="mailto:{{ $doktor['e_posta'] }}" class="btn btn-dark-outline">E-posta gönder</a>
                    @endif
                    @if(($doktor['whatsapp_goster'] ?? true) && !empty($doktor['whatsapp']))
                        <a href="https://wa.me/{{ $doktor['whatsapp'] }}" class="btn btn-dark-outline" target="_blank" rel="noopener">WhatsApp</a>
                    @endif
                </div>
            </div>
            @endif

            <div style="display:grid;gap:1rem">
                @if($saatlerGoster)
                <div class="card card-pad">
                    <h3 style="margin:0 0 .75rem;color:var(--ink)">Çalışma saatleri</h3>
                    <ul class="footer-list">
                        @forelse (($doktor['calisma_saatleri'] ?? []) as $gun => $saat)
                            <li class="flex-between" style="border-bottom:1px solid var(--line);padding:.55rem 0">
                                <span style="color:#0f172a;font-weight:500">{{ $gun }}</span>
                                <span>{{ $saat }}</span>
                            </li>
                        @empty
                            <li class="text-muted">Randevu ile planlanır.</li>
                        @endforelse
                    </ul>
                </div>
                @endif
                @if($haritaGoster && !empty($doktor['maps_embed']))
                <div class="card" style="padding:.65rem">
                    <iframe class="map-frame" src="{{ $doktor['maps_embed'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Harita"></iframe>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
</div>

@if($formGoster)
@include('frontend.partials.guest-booking-assets')
@endif
@endsection
