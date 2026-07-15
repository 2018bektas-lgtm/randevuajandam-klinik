@extends('panel.layouts.app')
@section('baslik', 'Site Ayarları · SEO')
@section('sayfa_baslik', 'Site Ayarları · SEO')

@section('icerik')
@include('panel.site-ayarlari._shell')

@php
    $kwRaw = trim((string) ($ayarlar['meta_anahtar'] ?? ''));
    $siteHost = parse_url(url('/'), PHP_URL_HOST) ?: 'siteniz.com';
@endphp

<form method="POST" action="{{ route('panel.site-ayarlari.seo.kaydet') }}" class="sa-wrap" id="seoForm">
    @csrf
    <div class="sa-layout">
        <div class="sa-card">
            <div class="sa-card-head">
                <div>
                    <h3>Meta etiketleri</h3>
                    <p class="sa-hint">Boş alanlar hekim adı ve uzmanlık ile otomatik dolar. Arama motorları için varsayılanlar.</p>
                </div>
                <span class="sa-badge">SEO</span>
            </div>
            <div class="sa-card-body">
                <div class="sa-field">
                    <label class="sa-label">
                        Meta başlık
                        <span class="sa-counter" id="cnt_title">0 / 60</span>
                    </label>
                    <input type="text" name="meta_baslik" id="meta_baslik"
                           value="{{ $ayarlar['meta_baslik'] }}" maxlength="70"
                           placeholder="Örn. Dr. Ahmet Yılmaz | Dermatoloji · İstanbul"
                           class="sa-input" data-counter="cnt_title" data-soft="60" data-hard="70">
                    <p class="sa-help">Önerilen uzunluk 50–60 karakter.</p>
                </div>

                <div class="sa-field">
                    <label class="sa-label">
                        Meta açıklama
                        <span class="sa-counter" id="cnt_desc">0 / 160</span>
                    </label>
                    <textarea name="meta_aciklama" id="meta_aciklama" rows="4" maxlength="200"
                              class="sa-textarea" data-counter="cnt_desc" data-soft="160" data-hard="200"
                              placeholder="Kısa, net ve anahtar kelime içeren açıklama…">{{ $ayarlar['meta_aciklama'] }}</textarea>
                    <p class="sa-help">Önerilen uzunluk 140–160 karakter.</p>
                </div>

                <div class="sa-field">
                    <label class="sa-label">Anahtar kelimeler <span class="text-slate-400 normal-case font-medium tracking-normal">(etiket)</span></label>
                    <div class="sa-tags" id="seoTags" data-tag-root>
                        <input type="text" class="sa-tags-input" placeholder="Yazıp Enter veya virgül…" autocomplete="off">
                        <input type="hidden" name="meta_anahtar" value="{{ $kwRaw }}">
                    </div>
                    <p class="sa-help">Enter veya virgül ile ekleyin · tıklayarak × ile silin · en fazla 30 etiket.</p>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="sa-card">
                <div class="sa-card-head">
                    <div>
                        <h3>Google önizleme</h3>
                        <p class="sa-hint">Arama sonucunda yaklaşık görünüm.</p>
                    </div>
                </div>
                <div class="sa-card-body">
                    <div class="sa-serp">
                        <div class="serp-url">https://{{ $siteHost }}/</div>
                        <div class="serp-title" id="serp_title">{{ $ayarlar['meta_baslik'] ?: 'Sayfa başlığı buraya gelir' }}</div>
                        <div class="serp-desc" id="serp_desc">{{ $ayarlar['meta_aciklama'] ?: 'Meta açıklama burada görünecek. Boş bırakırsanız hekim bilgileri kullanılır.' }}</div>
                    </div>
                </div>
            </div>

            <div class="sa-callout">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <strong class="block text-[.78rem] mb-0.5">İpucu</strong>
                    Anahtar kelimeleri doğal dilde kullanın. Aşırı tekrar (keyword stuffing) sıralamaya zarar verebilir.
                </div>
            </div>
        </div>
    </div>

    <div class="sa-card" style="margin-top:1.25rem">
        <div class="sa-card-head">
            <div>
                <h3>Analitik &amp; reklam (bu site)</h3>
                <p class="sa-hint">Kodlar yalnızca sizin public sitenizde çalışır. Ana platform (Randevu Ajandam) kendi reklam kodlarını ayrı tutar — buraya yazdıklarınız oraya gitmez.</p>
            </div>
            <span class="sa-badge">GTM · GA4 · Meta</span>
        </div>
        <div class="sa-card-body">
            <div class="sa-field">
                <label class="sa-label">Google Tag Manager</label>
                <input type="text" name="gtm_container_id" value="{{ old('gtm_container_id', $ayarlar['gtm_container_id'] ?? '') }}"
                       class="sa-input font-mono" placeholder="GTM-XXXXXXX" maxlength="40">
                @error('gtm_container_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="sa-field">
                <label class="sa-label">Google Analytics 4</label>
                <input type="text" name="ga4_measurement_id" value="{{ old('ga4_measurement_id', $ayarlar['ga4_measurement_id'] ?? '') }}"
                       class="sa-input font-mono" placeholder="G-XXXXXXXXXX" maxlength="40">
                @error('ga4_measurement_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="sa-field">
                <label class="sa-label">Meta (Facebook) Pixel</label>
                <input type="text" name="meta_pixel_id" value="{{ old('meta_pixel_id', $ayarlar['meta_pixel_id'] ?? '') }}"
                       class="sa-input font-mono" placeholder="123456789012345" maxlength="40">
                @error('meta_pixel_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="sa-field">
                <label class="sa-label">Google Ads</label>
                <input type="text" name="google_ads_id" value="{{ old('google_ads_id', $ayarlar['google_ads_id'] ?? '') }}"
                       class="sa-input font-mono" placeholder="AW-XXXXXXXXXX" maxlength="40">
                @error('google_ads_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <p class="sa-help">GTM kullanıyorsanız GA4/Meta’yı GTM panelinden de ekleyebilirsiniz. Boş bırakılan alanlar yüklenmez.</p>

            <div class="sa-field" style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid #e5e7eb">
                <label class="sa-label">Google reCAPTCHA v3</label>
                <p class="sa-help mb-2">Bu sitenin domain’i için Google’dan v3 anahtarı oluşturun. Secret asla ziyaretçiye gitmez.</p>
                <label class="flex items-center gap-2 text-xs font-semibold mb-3 cursor-pointer">
                    <input type="checkbox" name="recaptcha_enabled" value="1" @checked(old('recaptcha_enabled', $ayarlar['recaptcha_enabled'] ?? true))>
                    reCAPTCHA aktif
                </label>
                <input type="text" name="recaptcha_site_key" value="{{ old('recaptcha_site_key', $ayarlar['recaptcha_site_key'] ?? '') }}"
                       class="sa-input font-mono mb-2" placeholder="Site key (6L…)" maxlength="100">
                <input type="password" name="recaptcha_secret_key" value="{{ old('recaptcha_secret_key', $ayarlar['recaptcha_secret_key'] ?? '') }}"
                       class="sa-input font-mono" placeholder="Secret key (6L…)" maxlength="100" autocomplete="new-password">
            </div>
        </div>
    </div>

    <div class="sa-actions">
        <p class="sa-hint m-0">Etiketler virgülle birleştirilerek kaydedilir.</p>
        <button type="submit" class="sa-btn sa-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            SEO ayarlarını kaydet
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.saInitTagInput(document.getElementById('seoTags'));

    function bindCounter(el) {
        if (!el) return;
        const cnt = document.getElementById(el.dataset.counter);
        const soft = parseInt(el.dataset.soft || '60', 10);
        const hard = parseInt(el.dataset.hard || '70', 10);
        const update = () => {
            const n = (el.value || '').length;
            if (cnt) {
                cnt.textContent = n + ' / ' + soft;
                cnt.classList.toggle('warn', n > soft && n <= hard);
                cnt.classList.toggle('bad', n > hard * 0.95);
            }
        };
        el.addEventListener('input', update);
        update();
    }

    const title = document.getElementById('meta_baslik');
    const desc = document.getElementById('meta_aciklama');
    const serpT = document.getElementById('serp_title');
    const serpD = document.getElementById('serp_desc');

    bindCounter(title);
    bindCounter(desc);

    title?.addEventListener('input', () => {
        serpT.textContent = title.value.trim() || 'Sayfa başlığı buraya gelir';
    });
    desc?.addEventListener('input', () => {
        serpD.textContent = desc.value.trim() || 'Meta açıklama burada görünecek. Boş bırakırsanız hekim bilgileri kullanılır.';
    });
});
</script>
@endpush
