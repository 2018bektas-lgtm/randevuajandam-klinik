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
<div class="th-klasik-page">
<section class="page-hero">
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

<section class="section" id="randevu">
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
                    <div class="field full" id="gorusme-tipi-block">
                        <label>Görüşme türü *</label>
                        <div style="display:flex;flex-direction:column;gap:.45rem;margin-top:.35rem">
                            <label style="display:flex;align-items:center;gap:.5rem;font-weight:500;cursor:pointer">
                                <input type="radio" name="gorusme_tipi" value="yuz_yuze" checked> Yüz yüze
                            </label>
                            <label style="display:flex;align-items:center;gap:.5rem;font-weight:500;cursor:pointer">
                                <input type="radio" name="gorusme_tipi" value="online"> Online Görüşme
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
@endsection

@if($formGoster)
@push('head')
<style>
    .booking-alert {
        margin: 1rem 0 0;
        padding: .85rem 1rem;
        border-radius: 12px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        font-size: .9rem;
    }
    .booking-success {
        margin: 1rem 0 0;
        padding: 1rem 1.1rem;
        border-radius: 12px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        font-size: .95rem;
        line-height: 1.55;
    }
    .kvkk-label {
        display: flex !important;
        align-items: flex-start;
        gap: .55rem;
        text-transform: none !important;
        letter-spacing: 0 !important;
        font-size: .86rem !important;
        font-weight: 500 !important;
        color: #475569 !important;
        cursor: pointer;
    }
    .kvkk-label input { margin-top: .2rem; width: auto; }
    #saat:disabled, #hizmet_id:disabled, #booking-submit:disabled {
        opacity: .65;
        cursor: not-allowed;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const BOOKING_BASE = @json(url('/site-api/booking'));
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('#guest-booking-form input[name="_token"]')?.value
        || '';

    function apiHeaders(extra = {}) {
        return {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(CSRF ? { 'X-CSRF-TOKEN': CSRF } : {}),
            ...extra,
        };
    }

    const form = document.getElementById('guest-booking-form');
    if (!form) return;

    const preselectDoktor = new URLSearchParams(window.location.search).get('doktor_id') || '';
    const doktorEl = document.getElementById('doktor_id');
    const hizmetEl = document.getElementById('hizmet_id');
    const tarihEl = document.getElementById('tarih');
    const saatEl = document.getElementById('saat');
    const alertEl = document.getElementById('booking-alert');
    const successEl = document.getElementById('booking-success');
    const submitBtn = document.getElementById('booking-submit');

    function showAlert(msg, ok) {
        successEl.hidden = true;
        alertEl.hidden = false;
        alertEl.textContent = msg;
        if (ok) {
            alertEl.style.background = '#ecfdf5';
            alertEl.style.borderColor = '#a7f3d0';
            alertEl.style.color = '#065f46';
        } else {
            alertEl.style.background = '';
            alertEl.style.borderColor = '';
            alertEl.style.color = '';
        }
    }
    function hideAlert() { alertEl.hidden = true; alertEl.textContent = ''; }
    function showSuccess(html) {
        hideAlert();
        successEl.hidden = false;
        successEl.innerHTML = html;
        form.hidden = true;
    }

    async function apiGet(path) {
        const res = await fetch(BOOKING_BASE + path, { headers: apiHeaders(), credentials: 'same-origin' });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || ('İstek başarısız (' + res.status + ')'));
        return data;
    }
    async function apiPost(path, body) {
        const res = await fetch(BOOKING_BASE + path, {
            method: 'POST',
            headers: apiHeaders({ 'Content-Type': 'application/json' }),
            credentials: 'same-origin',
            body: JSON.stringify(body),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : null) || ('İstek başarısız (' + res.status + ')');
            throw new Error(msg);
        }
        return data;
    }

    async function loadDoctors() {
        doktorEl.innerHTML = '<option value="">Yükleniyor…</option>';
        try {
            const res = await apiGet('/doctors');
            const list = (res.data || []).filter(d => d.randevuya_acik_mi !== false);
            if (!list.length) {
                doktorEl.innerHTML = '<option value="">Aktif hekim yok</option>';
                return;
            }
            doktorEl.innerHTML = '<option value="">Hekim seçin</option>' +
                list.map(d => {
                    const label = ((d.unvan || '') + ' ' + (d.ad_soyad || '')).trim();
                    return `<option value="${d.id}">${label}${d.uzmanlik_alani ? ' — ' + d.uzmanlik_alani : ''}</option>`;
                }).join('');
            if (preselectDoktor) {
                doktorEl.value = preselectDoktor;
                if (doktorEl.value) loadServices();
            }
        } catch (e) {
            doktorEl.innerHTML = '<option value="">Hekimler yüklenemedi</option>';
            showAlert(e.message || 'Randevu sistemi kullanılamıyor.');
        }
    }

    async function loadServices() {
        const did = Number(doktorEl.value);
        hizmetEl.innerHTML = '<option value="">Yükleniyor…</option>';
        if (!did) {
            hizmetEl.innerHTML = '<option value="">Önce hekim seçin</option>';
            return;
        }
        try {
            const res = await apiGet('/services?doktor_id=' + did);
            const list = res.data || [];
            if (!list.length) {
                hizmetEl.innerHTML = '<option value="">Bu hekime ait hizmet yok</option>';
                return;
            }
            hizmetEl.innerHTML = '<option value="">Hizmet seçin</option>' +
                list.map(h => {
                    const sure = h.sure ? ` (${h.sure} dk)` : '';
                    return `<option value="${h.id}">${h.ad || h.baslik || 'Hizmet'}${sure}</option>`;
                }).join('');
        } catch (e) {
            hizmetEl.innerHTML = '<option value="">Hizmetler yüklenemedi</option>';
            showAlert(e.message);
        }
    }

    async function loadSlots() {
        const date = tarihEl.value;
        const did = Number(doktorEl.value);
        saatEl.innerHTML = '<option value="">Yükleniyor…</option>';
        saatEl.disabled = true;
        if (!did || !date) {
            saatEl.innerHTML = '<option value="">Önce hekim ve tarih seçin</option>';
            return;
        }
        try {
            const res = await apiGet('/slots?date=' + encodeURIComponent(date) + '&doktor_id=' + did);
            const slots = (res.data && res.data.slots) || [];
            if (!slots.length) {
                saatEl.innerHTML = '<option value="">Bu tarihte boş slot yok</option>';
                return;
            }
            saatEl.innerHTML = '<option value="">Saat seçin</option>' +
                slots.map(s => {
                    const saat = s.saat || s;
                    const bitis = s.saat_bitis ? ' – ' + s.saat_bitis : '';
                    return `<option value="${saat}">${saat}${bitis}</option>`;
                }).join('');
            saatEl.disabled = false;
        } catch (e) {
            saatEl.innerHTML = '<option value="">Slotlar alınamadı</option>';
            showAlert(e.message);
        }
    }

    doktorEl?.addEventListener('change', () => {
        hideAlert();
        loadServices();
        saatEl.innerHTML = '<option value="">Önce tarih seçin</option>';
        saatEl.disabled = true;
    });
    tarihEl?.addEventListener('change', () => { hideAlert(); loadSlots(); });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideAlert();
        const gorusmeRadio = document.querySelector('input[name="gorusme_tipi"]:checked');
        const payload = {
            doktor_id: Number(doktorEl.value),
            hizmet_id: Number(hizmetEl.value),
            tarih: tarihEl.value,
            saat: saatEl.value,
            ad: document.getElementById('ad').value.trim(),
            soyad: document.getElementById('soyad').value.trim(),
            telefon: document.getElementById('telefon').value.trim(),
            e_posta: document.getElementById('e_posta').value.trim() || null,
            not: document.getElementById('not').value.trim() || null,
            gorusme_tipi: gorusmeRadio ? gorusmeRadio.value : 'yuz_yuze',
            kvkk_onay: document.getElementById('kvkk_onay').checked ? 1 : 0,
            website_url: document.getElementById('website_url')?.value || '',
            otp_kod: document.getElementById('otp_kod')?.value?.trim() || null,
        };
        if (!payload.doktor_id || !payload.hizmet_id || !payload.tarih || !payload.saat) {
            showAlert('Hekim, hizmet, tarih ve saat seçimi zorunludur.');
            return;
        }
        submitBtn.disabled = true;
        submitBtn.textContent = 'Gönderiliyor…';
        try {
            if (window.raGetRecaptchaToken) { payload.recaptcha_token = await window.raGetRecaptchaToken('randevu'); }
            const res = await apiPost('/appointments', payload);
            const d = res.data || {};
            const yonet = d.yonetim_url ? `<br><a href="${d.yonetim_url}" style="color:var(--brand-700);font-weight:700">Randevuyu yönet →</a>` : '';
            const join = d.platform_join_url
                ? `<br><a href="${d.platform_join_url}" style="color:var(--brand-700);font-weight:700">Görüşmeye katıl →</a>`
                : (payload.gorusme_tipi === 'online' ? `<br><span style="opacity:.85">Online oda onay sonrası yönetim linkinde açılır.</span>` : '');
            showSuccess(
                `<strong>${res.message || 'Talebiniz alındı.'}</strong><br>` +
                `Tarih: ${d.tarih || payload.tarih} · Saat: ${d.saat || payload.saat}<br>` +
                `Görüşme: ${payload.gorusme_tipi === 'online' ? 'Online' : 'Yüz yüze'}<br>` +
                `Durum: ${d.durum || '-'}` + yonet + join
            );
        } catch (err) {
            showAlert(err.message || 'Randevu oluşturulamadı.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Randevu Talebini Gönder';
        }
    });

    document.getElementById('otp-send-btn')?.addEventListener('click', async () => {
        const telefon = document.getElementById('telefon').value.trim();
        const did = Number(doktorEl.value);
        if (!did) { showAlert('Önce hekim seçin.'); return; }
        if (!telefon) { showAlert('Önce telefon girin.'); return; }
        try {
            hideAlert();
            const res = await apiPost('/otp/send', { telefon, doktor_id: did });
            showAlert(res.message || 'Doğrulama kodu gönderildi.', true);
        } catch (e) {
            showAlert(e.message);
        }
    });

    loadDoctors();
    const otpBlock = document.getElementById('otp-block');
    if (otpBlock) otpBlock.style.display = 'block';
})();
</script>
@endpush
@endif
