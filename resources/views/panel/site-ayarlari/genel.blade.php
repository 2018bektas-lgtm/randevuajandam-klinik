@extends('panel.layouts.app')
@section('baslik', 'Site Ayarları · Genel')
@section('sayfa_baslik', 'Site Ayarları · Genel')

@section('icerik')
@include('panel.site-ayarlari._shell')

<form method="POST" action="{{ route('panel.site-ayarlari.genel.kaydet') }}" enctype="multipart/form-data" class="sa-wrap">
    @csrf
    <div class="sa-layout">
        {{-- Sol: metin + logo/favicon --}}
        <div class="space-y-4">
            <div class="sa-card">
                <div class="sa-card-head">
                    <div>
                        <h3>Vitrin kimliği</h3>
                        <p class="sa-hint">Başlık, slogan ve footer — ana platform hekim verisinden bağımsız override’lar.</p>
                    </div>
                    <span class="sa-badge">Genel</span>
                </div>
                <div class="sa-card-body space-y-0">
                    <div class="sa-field">
                        <label class="sa-label">Site başlık eki</label>
                        <input type="text" name="site_baslik_ek" value="{{ $ayarlar['site_baslik_ek'] }}"
                               placeholder="| Resmi Web Sitesi" class="sa-input">
                        <p class="sa-help">Tarayıcı sekmesinde hekim adının yanına eklenir.</p>
                    </div>
                    <div class="sa-field">
                        <label class="sa-label">Slogan override</label>
                        <input type="text" name="slogan_override" value="{{ $ayarlar['slogan_override'] }}"
                               placeholder="Boş bırakırsanız API’den otomatik üretilir" class="sa-input">
                    </div>
                    <div class="sa-field">
                        <label class="sa-label">Footer metni</label>
                        <textarea name="footer_metin" rows="3" class="sa-textarea"
                                  placeholder="© 2026 Klinik adı · Tüm hakları saklıdır">{{ $ayarlar['footer_metin'] }}</textarea>
                    </div>
                    <div class="sa-field">
                        <label class="sa-label">Vitrin rozeti</label>
                        <input type="text" name="vitrin_badge" value="{{ $ayarlar['vitrin_badge'] }}"
                               placeholder="Örn. Kadıköy · İstanbul" class="sa-input">
                    </div>
                </div>
            </div>

            <div class="sa-card">
                <div class="sa-card-head">
                    <div>
                        <h3>Logo & Favicon</h3>
                        <p class="sa-hint">Header logosu ve tarayıcı sekmesi ikonu. PNG / SVG / WebP önerilir.</p>
                    </div>
                    <span class="sa-badge">Medya</span>
                </div>
                <div class="sa-card-body">
                    <div class="sa-grid-2">
                        {{-- Logo --}}
                        <div class="sa-upload">
                            <label class="sa-label">Site logosu</label>
                            <div class="sa-upload-preview" id="logoPreviewBox">
                                @if(!empty($ayarlar['logo_url']))
                                    <img src="{{ $ayarlar['logo_url'] }}" alt="Logo" id="logoPreviewImg" class="sa-upload-img sa-upload-img-logo">
                                @else
                                    <div class="sa-upload-ph" id="logoPreviewPh">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Logo yok</span>
                                    </div>
                                    <img src="" alt="" id="logoPreviewImg" class="sa-upload-img sa-upload-img-logo hidden">
                                @endif
                            </div>
                            <input type="file" name="logo" id="logoInput" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif"
                                   class="sa-file" onchange="saPreviewFile(this, 'logoPreviewImg', 'logoPreviewPh')">
                            <p class="sa-help">Max 4 MB · yatay logo (ör. 320×80) en iyi sonucu verir.</p>
                            @if(!empty($ayarlar['logo_url']))
                                <label class="inline-flex items-center gap-2 mt-2 text-xs font-semibold text-red-600 cursor-pointer">
                                    <input type="checkbox" name="logo_sil" value="1" class="rounded border-slate-300 text-red-600">
                                    Mevcut logoyu sil
                                </label>
                            @endif
                        </div>

                        {{-- Favicon --}}
                        <div class="sa-upload">
                            <label class="sa-label">Favicon</label>
                            <div class="sa-upload-preview sa-upload-preview-sm" id="faviconPreviewBox">
                                @if(!empty($ayarlar['favicon_url']))
                                    <img src="{{ $ayarlar['favicon_url'] }}" alt="Favicon" id="faviconPreviewImg" class="sa-upload-img sa-upload-img-fav">
                                @else
                                    <div class="sa-upload-ph" id="faviconPreviewPh">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span>Favicon yok</span>
                                    </div>
                                    <img src="" alt="" id="faviconPreviewImg" class="sa-upload-img sa-upload-img-fav hidden">
                                @endif
                            </div>
                            <input type="file" name="favicon" id="faviconInput" accept="image/png,image/jpeg,image/webp,image/x-icon,image/vnd.microsoft.icon,.ico,image/svg+xml,image/gif"
                                   class="sa-file" onchange="saPreviewFile(this, 'faviconPreviewImg', 'faviconPreviewPh')">
                            <p class="sa-help">Max 1 MB · kare 32×32 veya 64×64 PNG/ICO önerilir.</p>
                            @if(!empty($ayarlar['favicon_url']))
                                <label class="inline-flex items-center gap-2 mt-2 text-xs font-semibold text-red-600 cursor-pointer">
                                    <input type="checkbox" name="favicon_sil" value="1" class="rounded border-slate-300 text-red-600">
                                    Mevcut favicon’u sil
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sağ: tema + görünürlük --}}
        <div class="space-y-4">
            <div class="sa-card">
                <div class="sa-card-head">
                    <div>
                        <h3>Tema rengi</h3>
                        <p class="sa-hint">Vitrin vurgu rengi (butonlar, linkler).</p>
                    </div>
                </div>
                <div class="sa-card-body">
                    <div class="sa-color-wrap">
                        <input type="color" id="tema_renk_picker" value="{{ $ayarlar['tema_renk'] ?: '#0d9488' }}"
                               oninput="document.getElementById('tema_renk_text').value=this.value; document.getElementById('tema_renk_hidden').value=this.value">
                        <input type="text" id="tema_renk_text" value="{{ $ayarlar['tema_renk'] ?: '#0d9488' }}"
                               maxlength="7" pattern="^#[0-9A-Fa-f]{6}$"
                               oninput="if(/^#[0-9A-Fa-f]{6}$/.test(this.value)){document.getElementById('tema_renk_picker').value=this.value;document.getElementById('tema_renk_hidden').value=this.value}">
                        <input type="hidden" name="tema_renk" id="tema_renk_hidden" value="{{ $ayarlar['tema_renk'] ?: '#0d9488' }}">
                    </div>
                    <div class="mt-4 flex gap-2">
                        @foreach(['#0d9488','#C96A2B','#2563EB','#7C3AED','#DC2626','#059669'] as $c)
                            <button type="button" title="{{ $c }}"
                                    onclick="document.getElementById('tema_renk_picker').value='{{ $c }}';document.getElementById('tema_renk_text').value='{{ $c }}';document.getElementById('tema_renk_hidden').value='{{ $c }}'"
                                    class="w-8 h-8 rounded-lg border border-black/5 shadow-sm hover:scale-110 transition"
                                    style="background:{{ $c }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="sa-card">
                <div class="sa-card-head">
                    <div>
                        <h3>Görünür öğeler</h3>
                        <p class="sa-hint">Header ve floating butonlar.</p>
                    </div>
                </div>
                <div class="sa-card-body space-y-2.5">
                    <label class="sa-toggle-card {{ $ayarlar['whatsapp_goster'] ? 'is-on' : '' }}">
                        <span class="sa-toggle-icon">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.116 1.523 5.847L.057 23.45a.5.5 0 00.61.61l5.67-1.45A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22a9.94 9.94 0 01-5.09-1.4l-.364-.216-3.69.943.96-3.603-.237-.37A9.94 9.94 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                        </span>
                        <span class="flex-1 min-w-0">
                            <strong>WhatsApp butonu</strong>
                            <span class="desc">Sağ altta yüzen iletişim butonu</span>
                        </span>
                        <span class="sa-switch">
                            <input type="checkbox" name="whatsapp_goster" value="1" @checked($ayarlar['whatsapp_goster'])>
                            <span></span>
                        </span>
                    </label>
                    <label class="sa-toggle-card {{ $ayarlar['hekim_girisi_goster'] ? 'is-on' : '' }}">
                        <span class="sa-toggle-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <span class="flex-1 min-w-0">
                            <strong>Hekim girişi</strong>
                            <span class="desc">Header’da paneli açan link</span>
                        </span>
                        <span class="sa-switch">
                            <input type="checkbox" name="hekim_girisi_goster" value="1" @checked($ayarlar['hekim_girisi_goster'])>
                            <span></span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="sa-actions">
        <p class="sa-hint m-0">Değişiklikler anında public siteye yansır (cache ~1 dk).</p>
        <button type="submit" class="sa-btn sa-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Kaydet
        </button>
    </div>
</form>
@endsection

@push('styles')
<style>
    .sa-upload-preview {
        display: flex; align-items: center; justify-content: center;
        min-height: 7.5rem; padding: 1rem;
        border: 1.5px dashed #E5E7EB; border-radius: 1rem;
        background: linear-gradient(180deg, #FAFBFC, #fff);
        margin-bottom: .65rem;
    }
    .sa-upload-preview-sm { min-height: 6rem; }
    .sa-upload-ph {
        display: flex; flex-direction: column; align-items: center; gap: .4rem;
        color: #9CA3AF; font-size: .72rem; font-weight: 600;
    }
    .sa-upload-img { max-width: 100%; object-fit: contain; }
    .sa-upload-img-logo { max-height: 72px; }
    .sa-upload-img-fav { width: 48px; height: 48px; border-radius: .55rem; }
    .sa-file {
        width: 100%; font-size: .75rem; color: #4B5563;
        padding: .55rem .7rem; border: 1px solid #E5E7EB; border-radius: .75rem;
        background: #fff;
    }
    .sa-file::file-selector-button {
        margin-right: .75rem; padding: .35rem .7rem;
        border: 0; border-radius: .5rem;
        background: #FFF7ED; color: #C96A2B; font-weight: 700; font-size: .7rem;
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
window.saPreviewFile = function (input, imgId, phId) {
    const file = input.files && input.files[0];
    const img = document.getElementById(imgId);
    const ph = document.getElementById(phId);
    if (!file || !img) return;
    const url = URL.createObjectURL(file);
    img.src = url;
    img.classList.remove('hidden');
    if (ph) ph.classList.add('hidden');
};
document.addEventListener('DOMContentLoaded', () => window.saBindToggleCards?.());
</script>
@endpush
