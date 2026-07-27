@extends(theme_layout())

@php
    $egitimTitle = trim((string) ($egitim['meta_baslik'] ?? '')) !== ''
        ? $egitim['meta_baslik']
        : (($egitim['baslik'] ?? 'Eğitim').' | '.($doktor['ad_soyad'] ?? 'Hekim'));
    $egitimDesc = trim((string) ($egitim['meta_aciklama'] ?? '')) !== ''
        ? $egitim['meta_aciklama']
        : \Illuminate\Support\Str::limit(strip_tags((string) ($egitim['ozet'] ?? '')), 160);
@endphp
@section('baslik', $egitimTitle)
@section('meta_aciklama', $egitimDesc)

@section('icerik')
<section class="mp-page-hero">
    <div class="mp-container">
        <div class="mp-breadcrumb">
            <a href="{{ route('frontend.anasayfa') }}">Ana Sayfa</a>
            <span>/</span>
            <a href="{{ route('frontend.egitimler') }}">Eğitimler</a>
            <span>/</span>
            <span>{{ $egitim['baslik'] ?? '' }}</span>
        </div>
        <h1>{{ $egitim['baslik'] ?? 'Eğitim' }}</h1>
        <p>{{ $egitim['ozet'] ?? '' }}</p>
    </div>
</section>

<section class="mp-section mp-page">
    <div class="mp-container" style="display:grid;grid-template-columns:1.4fr 1fr;gap:2rem;align-items:start">
        <div>
            @if(!empty($egitim['image']))
                <img src="{{ $egitim['image'] }}" alt="" style="width:100%;border-radius:8px;object-fit:cover;max-height:320px;margin-bottom:1.25rem">
            @endif
            <div class="mp-svc-meta" style="margin-bottom:1rem">
                <span class="mp-chip">{{ $egitim['tip'] ?? '' }}</span>
                @if(!empty($egitim['baslangic_label']))
                    <span class="mp-chip">{{ $egitim['baslangic_label'] }}</span>
                @endif
                @if(!empty($egitim['fiyat_label']))
                    <span class="mp-chip">{{ $egitim['fiyat_label'] }}</span>
                @endif
            </div>
            @if(!empty($egitim['mekan']))
                <p style="color:var(--muted)"><strong>Mekan:</strong> {{ $egitim['mekan'] }}</p>
            @endif
            @if(!empty($egitim['icerik']))
                <div class="mp-card" style="margin-top:1rem">
                    {!! \App\Services\HtmlSanitizer::clean($egitim['icerik'] ?? '') !!}
                </div>
            @endif
        </div>

        <div class="mp-card" style="position:sticky;top:1.5rem">
            <h3 style="margin-top:0;color:var(--mp-navy)">Başvuru formu</h3>
            <p style="font-size:.85rem;color:var(--muted)">Başvuru <strong>beklemede</strong> olarak düşer. Ücret hekim tarafından kendi kanalından alınır.</p>

            <div id="egitimFormMsg" class="text-muted" style="font-size:.85rem;margin-bottom:.75rem;display:none"></div>

            @if(!empty($egitim['basvuru_acik']))
            <form id="egitimBasvuruForm" class="stack-form">
                <input type="hidden" name="egitim_id" value="{{ $egitim['id'] }}">
                <div class="hidden" aria-hidden="true" style="position:absolute;left:-9999px">
                    <input type="text" name="website_url" value="" tabindex="-1" autocomplete="off">
                </div>
                <div class="grid-2" style="gap:.75rem">
                    <div>
                        <label>Ad *</label>
                        <input type="text" name="ad" required>
                    </div>
                    <div>
                        <label>Soyad *</label>
                        <input type="text" name="soyad" required>
                    </div>
                </div>
                <div>
                    <label>Telefon *</label>
                    <input type="tel" name="telefon" required>
                </div>
                <div>
                    <label>E-posta</label>
                    <input type="email" name="e_posta">
                </div>

                @foreach(($egitim['form_alanlari'] ?? []) as $alan)
                    <div>
                        <label>{{ $alan['etiket'] ?? '' }} @if(!empty($alan['zorunlu_mu']))*@endif</label>
                        @if(($alan['tip'] ?? '') === 'textarea')
                            <textarea name="alan[{{ $alan['id'] }}]" @if(!empty($alan['zorunlu_mu'])) required @endif rows="2" placeholder="{{ $alan['placeholder'] ?? '' }}"></textarea>
                        @elseif(($alan['tip'] ?? '') === 'select')
                            <select name="alan[{{ $alan['id'] }}]" @if(!empty($alan['zorunlu_mu'])) required @endif>
                                <option value="">Seçin</option>
                                @foreach(($alan['secenekler'] ?? []) as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        @elseif(($alan['tip'] ?? '') === 'checkbox')
                            <label style="display:flex;gap:.5rem;align-items:center;font-weight:500">
                                <input type="checkbox" name="alan[{{ $alan['id'] }}]" value="1" @if(!empty($alan['zorunlu_mu'])) required @endif>
                                {{ $alan['placeholder'] ?: ($alan['etiket'] ?? '') }}
                            </label>
                        @else
                            <input
                                type="{{ ($alan['tip'] ?? 'text') === 'phone' ? 'tel' : (($alan['tip'] ?? '') === 'number' ? 'number' : (($alan['tip'] ?? '') === 'date' ? 'date' : (($alan['tip'] ?? '') === 'email' ? 'email' : 'text'))) }}"
                                name="alan[{{ $alan['id'] }}]"
                                @if(!empty($alan['zorunlu_mu'])) required @endif
                                placeholder="{{ $alan['placeholder'] ?? '' }}">
                        @endif
                    </div>
                @endforeach

                @if(!empty($egitim['odeme_notu']))
                    <p class="chip" style="display:block;white-space:normal;line-height:1.4">{{ $egitim['odeme_notu'] }}</p>
                @endif

                <label style="display:flex;gap:.5rem;align-items:flex-start;font-size:.85rem">
                    <input type="checkbox" name="kvkk_onay" value="1" required style="margin-top:.2rem">
                    <span>Kişisel verilerimin eğitim başvurusu için işlenmesini kabul ediyorum.</span>
                </label>

                <button type="submit" class="mp-btn mp-btn-primary" style="width:100%">Başvur</button>
            </form>
            @else
                <p style="color:var(--muted)">Bu eğitime şu an başvuru alınmıyor.</p>
            @endif
        </div>
    </div>
</section>

@push('scripts')
<script>
(function(){
    const form = document.getElementById('egitimBasvuruForm');
    const msg = document.getElementById('egitimFormMsg');
    if (!form) return;
    form.addEventListener('submit', async function(e){
        e.preventDefault();
        msg.style.display = 'block';
        msg.textContent = 'Gönderiliyor…';
        msg.style.color = '';
        const fd = new FormData(form);
        const payload = {
            egitim_id: parseInt(fd.get('egitim_id'), 10),
            ad: fd.get('ad'),
            soyad: fd.get('soyad'),
            telefon: fd.get('telefon'),
            e_posta: fd.get('e_posta') || null,
            kvkk_onay: fd.get('kvkk_onay') ? 1 : 0,
            website_url: fd.get('website_url') || '',
            alan: {}
        };
        for (const [k, v] of fd.entries()) {
            const m = k.match(/^alan\[(\d+)\]$/);
            if (m) payload.alan[m[1]] = v;
        }
        try {
            const res = await fetch(@json(route('frontend.booking.educations.apply')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(payload)
            });
            const json = await res.json().catch(() => ({}));
            if (res.ok && (json.success || json.message)) {
                msg.style.color = '#047857';
                msg.textContent = json.message || 'Başvurunuz alındı.';
                form.reset();
            } else {
                msg.style.color = '#b91c1c';
                msg.textContent = json.message || 'Başvuru gönderilemedi.';
            }
        } catch (err) {
            msg.style.color = '#b91c1c';
            msg.textContent = 'Bağlantı hatası. Lütfen tekrar deneyin.';
        }
    });
})();
</script>
<style>
@media (max-width: 900px) {
    .mp-page .mp-container[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
}
.stack-form label { display:block; font-size:.75rem; font-weight:700; margin-bottom:.25rem; text-transform:uppercase; letter-spacing:.04em; color:#757575; }
.stack-form input, .stack-form textarea, .stack-form select {
    width:100%; padding:.65rem .75rem; border:1px solid #e6e6e6; border-radius:6px; margin-bottom:.75rem; font-size:.9rem; font-family:inherit;
}
.stack-form .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
</style>
@endpush
@endsection
