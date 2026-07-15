@extends('panel.layouts.app')
@section('baslik', 'Site Ayarları · Slider')
@section('sayfa_baslik', 'Site Ayarları · Slider')

@section('icerik')
@include('panel.site-ayarlari._shell')

@php
    $pageOptions = $pageOptions ?? [];
    $slidesJson = $slides->map(function ($s) {
        $stats = is_array($s->istatistikler ?? null) ? $s->istatistikler : [];

        return [
            'id' => $s->id,
            'baslik' => $s->baslik,
            'baslik_vurgulu' => $s->baslik_vurgulu ?? '',
            'alt' => $s->alt,
            'etiket' => $s->etiket,
            'badge' => $s->badge,
            'image' => $s->image,
            'image_url' => $s->image_url ?? $s->image,
            'cta' => $s->cta,
            'cta_url' => $s->cta_url,
            'cta2' => $s->cta2,
            'cta2_url' => $s->cta2_url,
            'cta_link_type' => $s->cta_link_type ?? 'route',
            'cta_route' => $s->cta_route ?? 'frontend.randevu',
            'cta2_link_type' => $s->cta2_link_type ?? 'route',
            'cta2_route' => $s->cta2_route ?? 'frontend.iletisim',
            'float_1_baslik' => $s->float_1_baslik ?? '',
            'float_1_aciklama' => $s->float_1_aciklama ?? '',
            'float_2_baslik' => $s->float_2_baslik ?? '',
            'float_2_aciklama' => $s->float_2_aciklama ?? '',
            'istatistikler' => $stats,
            'aktif' => (bool) $s->aktif,
        ];
    })->values();
@endphp

<div class="sa-wrap sp" id="sliderApp" data-slides='@json($slidesJson)'>
    <div class="sa-card">
        <div class="sa-card-head">
            <div>
                <h3>Hero slaytları</h3>
                <p class="sa-hint">Sürükleyerek sıralayın. Düzenle ile tüm alanları yönetin (görsel, butonlar, float kart, istatistik).</p>
            </div>
            <div class="sp-head-actions">
                <span class="sa-badge">{{ $slides->count() }} slayt</span>
                <button type="button" class="sa-btn sa-btn-primary" onclick="openSlideModal()">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Slayt ekle
                </button>
            </div>
        </div>

        <div class="sa-card-body" style="padding-top:.85rem">
            <div id="sliderSortable" class="sp-list">
                @forelse($slides as $i => $slide)
                    <div class="sp-row {{ $slide->aktif ? '' : 'is-off' }}" data-id="{{ $slide->id }}">
                        <button type="button" class="sp-drag sa-drag" title="Sürükle">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="7" r="1.4"/><circle cx="15" cy="7" r="1.4"/><circle cx="9" cy="12" r="1.4"/><circle cx="15" cy="12" r="1.4"/><circle cx="9" cy="17" r="1.4"/><circle cx="15" cy="17" r="1.4"/></svg>
                        </button>
                        <span class="sp-no sa-order">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>

                        <div class="sp-thumb">
                            @if($slide->image_url)
                                <img src="{{ $slide->image_url }}" alt="">
                            @else
                                <div class="sp-thumb-empty">IMG</div>
                            @endif
                        </div>

                        <div class="sp-info">
                            <div class="sp-title-row">
                                <strong>{{ $slide->baslik ?: 'Başlıksız' }}</strong>
                                @if($slide->baslik_vurgulu)
                                    <span class="sp-accent">{{ $slide->baslik_vurgulu }}</span>
                                @endif
                            </div>
                            <div class="sp-sub">
                                @if($slide->etiket)<span>{{ $slide->etiket }}</span>@endif
                                @if($slide->badge)<span>{{ $slide->badge }}</span>@endif
                                @if($slide->cta)<span class="sp-cta">{{ $slide->cta }}</span>@endif
                            </div>
                            @if($slide->alt)
                                <p class="sp-desc">{{ \Illuminate\Support\Str::limit($slide->alt, 90) }}</p>
                            @endif
                        </div>

                        <div class="sp-actions">
                            <label class="sa-switch" title="Yayın">
                                <input type="checkbox" class="toggle-aktif" data-id="{{ $slide->id }}" data-type="slider" @checked($slide->aktif)>
                                <span></span>
                            </label>
                            <button type="button" class="sa-btn sa-btn-ghost sa-btn-sm" onclick='openSlideModal(@json($slidesJson[$i] ?? $slide))'>Düzenle</button>
                            <form method="POST" action="{{ route('panel.site-ayarlari.slider.destroy', $slide->id) }}" onsubmit="return confirm('Bu slayt silinsin mi?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm">Sil</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="sp-empty">
                        <div class="sp-empty-icon">
                            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h4>Henüz slayt yok</h4>
                        <p>Hero alanında gösterilecek ilk slaytı ekleyin.</p>
                        <button type="button" class="sa-btn sa-btn-primary" onclick="openSlideModal()">Slayt ekle</button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div id="slideModal" class="sp-modal" onclick="if(event.target===this)closeSlideModal()">
    <div class="sp-modal-box" role="dialog" aria-modal="true">
        <div class="sp-modal-head">
            <div>
                <p class="sa-hint" style="margin:0">Hero slider</p>
                <h3 id="slideModalTitle">Yeni slayt</h3>
            </div>
            <button type="button" class="sp-close" onclick="closeSlideModal()" aria-label="Kapat">×</button>
        </div>

        <form method="POST" id="slideForm" enctype="multipart/form-data">
            @csrf
            <div id="slideMethodField"></div>

            <div class="sp-modal-body">
                <div class="sp-form-grid">
                    {{-- Sol sütun --}}
                    <div class="sp-col">
                        <section class="sp-section">
                            <h4>İçerik</h4>
                            <div class="sa-field">
                                <label class="sa-label">Başlık *</label>
                                <input name="baslik" id="f_baslik" required class="sa-input" placeholder="Ana başlık">
                            </div>
                            <div class="sa-field">
                                <label class="sa-label">Vurgulu başlık</label>
                                <input name="baslik_vurgulu" id="f_baslik_vurgulu" class="sa-input" placeholder="Altın renkte 2. satır">
                            </div>
                            <div class="sa-grid-2">
                                <div class="sa-field">
                                    <label class="sa-label">Etiket</label>
                                    <input name="etiket" id="f_etiket" class="sa-input" placeholder="Hekim">
                                </div>
                                <div class="sa-field">
                                    <label class="sa-label">Rozet</label>
                                    <input name="badge" id="f_badge" class="sa-input" placeholder="İstanbul">
                                </div>
                            </div>
                            <div class="sa-field" style="margin-bottom:0">
                                <label class="sa-label">Açıklama</label>
                                <textarea name="alt" id="f_alt" rows="3" class="sa-textarea" placeholder="Kısa tanıtım metni"></textarea>
                            </div>
                        </section>

                        <section class="sp-section">
                            <h4>Butonlar</h4>
                            <div class="sp-btn-block">
                                <p class="sp-btn-label">Birincil</p>
                                <div class="sa-field">
                                    <input name="cta" id="f_cta" class="sa-input" placeholder="Randevu Al" value="Randevu Al">
                                </div>
                                <div class="sa-grid-2">
                                    <div class="sa-field">
                                        <label class="sa-label">Link türü</label>
                                        <select name="cta_link_type" id="f_cta_link_type" class="sa-input" onchange="syncLinkUi('cta')">
                                            <option value="route">Site içi sayfa</option>
                                            <option value="url">Harici URL</option>
                                            <option value="none">Gösterme</option>
                                        </select>
                                    </div>
                                    <div class="sa-field" id="cta_route_wrap">
                                        <label class="sa-label">Sayfa</label>
                                        <select name="cta_route" id="f_cta_route" class="sa-input">
                                            @foreach($pageOptions as $route => $label)
                                                <option value="{{ $route }}" @selected($route === 'frontend.randevu')>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="sa-field hidden" id="cta_url_wrap" style="margin-bottom:0">
                                    <label class="sa-label">URL</label>
                                    <input name="cta_custom_url" id="f_cta_custom_url" class="sa-input" placeholder="https://...">
                                </div>
                            </div>

                            <div class="sp-btn-block" style="margin-top:.75rem">
                                <p class="sp-btn-label">İkincil</p>
                                <div class="sa-field">
                                    <input name="cta2" id="f_cta2" class="sa-input" placeholder="İletişim" value="İletişim">
                                </div>
                                <div class="sa-grid-2">
                                    <div class="sa-field">
                                        <label class="sa-label">Link türü</label>
                                        <select name="cta2_link_type" id="f_cta2_link_type" class="sa-input" onchange="syncLinkUi('cta2')">
                                            <option value="route">Site içi sayfa</option>
                                            <option value="url">Harici URL</option>
                                            <option value="none">Gösterme</option>
                                        </select>
                                    </div>
                                    <div class="sa-field" id="cta2_route_wrap">
                                        <label class="sa-label">Sayfa</label>
                                        <select name="cta2_route" id="f_cta2_route" class="sa-input">
                                            @foreach($pageOptions as $route => $label)
                                                <option value="{{ $route }}" @selected($route === 'frontend.iletisim')>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="sa-field hidden" id="cta2_url_wrap" style="margin-bottom:0">
                                    <label class="sa-label">URL</label>
                                    <input name="cta2_custom_url" id="f_cta2_custom_url" class="sa-input" placeholder="https://...">
                                </div>
                            </div>
                        </section>
                    </div>

                    {{-- Sağ sütun --}}
                    <div class="sp-col">
                        <section class="sp-section">
                            <h4>Görsel</h4>
                            <label class="sp-upload" for="f_image_file">
                                <div id="slideImgPh" class="sp-upload-ph">
                                    <strong>Dosya seçin</strong>
                                    <span>JPG, PNG, WebP · max 5 MB</span>
                                </div>
                                <img id="slideImgPreview" src="" alt="" class="hidden">
                                <input type="file" name="image_file" id="f_image_file" accept="image/*" class="sr-only" onchange="previewSlideImage(this)">
                            </label>
                            <div class="sa-field" style="margin-top:.75rem;margin-bottom:.5rem">
                                <label class="sa-label">veya görsel URL</label>
                                <input type="text" name="image_url" id="f_image_url" class="sa-input" placeholder="https://..."
                                       oninput="if(this.value.startsWith('http')) setImagePreview(this.value)">
                            </div>
                            <label class="sp-check-danger hidden" id="imageSilWrap">
                                <input type="checkbox" name="image_sil" value="1" id="f_image_sil"> Mevcut görseli sil
                            </label>
                        </section>

                        <section class="sp-section">
                            <h4>Float kartlar</h4>
                            <div class="sa-grid-2">
                                <div class="sa-field">
                                    <label class="sa-label">Kart 1 başlık</label>
                                    <input name="float_1_baslik" id="f_float_1_baslik" class="sa-input" placeholder="Uzmanlık">
                                </div>
                                <div class="sa-field">
                                    <label class="sa-label">Kart 1 alt</label>
                                    <input name="float_1_aciklama" id="f_float_1_aciklama" class="sa-input" placeholder="Deneyim">
                                </div>
                            </div>
                            <div class="sa-grid-2" style="margin-bottom:0">
                                <div class="sa-field" style="margin-bottom:0">
                                    <label class="sa-label">Kart 2 başlık</label>
                                    <input name="float_2_baslik" id="f_float_2_baslik" class="sa-input" placeholder="Randevu">
                                </div>
                                <div class="sa-field" style="margin-bottom:0">
                                    <label class="sa-label">Kart 2 alt</label>
                                    <input name="float_2_aciklama" id="f_float_2_aciklama" class="sa-input" placeholder="Online">
                                </div>
                            </div>
                        </section>

                        <section class="sp-section">
                            <h4>İstatistikler</h4>
                            @for($si = 0; $si < 3; $si++)
                                <div class="sp-stat-row">
                                    <input name="stat_sayi[]" id="f_stat_sayi_{{ $si }}" class="sa-input" placeholder="Sayı">
                                    <input name="stat_suffix[]" id="f_stat_suffix_{{ $si }}" class="sa-input" placeholder="+">
                                    <input name="stat_etiket[]" id="f_stat_etiket_{{ $si }}" class="sa-input" placeholder="Etiket">
                                </div>
                            @endfor
                            <p class="sa-help" style="margin-top:.35rem">Boş bırakılırsa sitede API istatistikleri kullanılır.</p>
                        </section>

                        <section class="sp-section sp-section-publish">
                            <label class="sp-publish">
                                <div>
                                    <strong>Yayında</strong>
                                    <span>Pasif slayt sitede görünmez</span>
                                </div>
                                <span class="sa-switch">
                                    <input type="checkbox" name="aktif" id="f_aktif" value="1" checked>
                                    <span></span>
                                </span>
                            </label>
                        </section>
                    </div>
                </div>
            </div>

            <div class="sp-modal-foot">
                <button type="button" class="sa-btn sa-btn-ghost" onclick="closeSlideModal()">Vazgeç</button>
                <button type="submit" class="sa-btn sa-btn-primary" id="slideSubmitBtn">Kaydet</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.sp-head-actions { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
.sp-list { display:flex; flex-direction:column; gap:.65rem; }
.sp-row {
    display:flex; align-items:center; gap:.85rem;
    padding:.9rem 1rem; border:1px solid #E8EAED; border-radius:1rem;
    background:#fff; transition: border-color .15s, box-shadow .15s, opacity .15s;
}
.sp-row:hover { border-color:#E7B58A88; box-shadow:0 6px 18px rgba(16,24,40,.05); }
.sp-row.is-off { opacity:.55; background:#FAFAFA; }
.sp-row.sortable-ghost { opacity:.4; border-style:dashed; border-color:#C96A2B; }
.sp-row.sortable-chosen { box-shadow:0 10px 28px rgba(201,106,43,.12); border-color:#C96A2B; }
.sp-drag {
    border:0; background:#F3F4F6; color:#9CA3AF; width:2rem; height:2rem;
    border-radius:.55rem; display:grid; place-items:center; cursor:grab; flex-shrink:0;
}
.sp-drag:hover { color:#C96A2B; background:#FFF7ED; }
.sp-no {
    width:1.75rem; height:1.75rem; border-radius:.5rem; flex-shrink:0;
    display:grid; place-items:center; font-size:.68rem; font-weight:800;
    font-family:ui-monospace,monospace; background:#FFF7ED; color:#C96A2B; border:1px solid #E7B58A55;
}
.sp-thumb {
    width:4.5rem; height:3.2rem; border-radius:.65rem; overflow:hidden;
    background:#F3F4F6; border:1px solid #E5E7EB; flex-shrink:0;
}
.sp-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.sp-thumb-empty {
    width:100%; height:100%; display:grid; place-items:center;
    font-size:.65rem; font-weight:700; color:#9CA3AF;
}
.sp-info { flex:1; min-width:0; }
.sp-title-row { display:flex; flex-wrap:wrap; align-items:baseline; gap:.4rem .65rem; }
.sp-title-row strong { font-size:.9rem; color:#111827; font-weight:700; }
.sp-accent { font-size:.78rem; font-weight:700; color:#C96A2B; }
.sp-sub { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.3rem; }
.sp-sub span {
    font-size:.68rem; font-weight:600; color:#6B7280;
    background:#F3F4F6; padding:.18rem .5rem; border-radius:999px;
}
.sp-sub .sp-cta { background:#FFF7ED; color:#C96A2B; }
.sp-desc { margin:.35rem 0 0; font-size:.75rem; color:#9CA3AF; line-height:1.4; }
.sp-actions {
    display:flex; align-items:center; gap:.45rem; flex-shrink:0; margin-left:auto;
}
.sp-empty {
    text-align:center; padding:2.75rem 1.25rem;
    border:1.5px dashed #E5E7EB; border-radius:1rem; background:#FAFBFC;
}
.sp-empty-icon {
    width:3.5rem; height:3.5rem; margin:0 auto .85rem; border-radius:1rem;
    display:grid; place-items:center; background:#F3F4F6; color:#9CA3AF;
}
.sp-empty h4 { margin:0 0 .35rem; font-size:.95rem; color:#111827; font-family:Outfit,Inter,sans-serif; }
.sp-empty p { margin:0 0 1rem; font-size:.8rem; color:#6B7280; }

/* Modal */
.sp-modal {
    position:fixed; inset:0; z-index:80; display:none; align-items:center; justify-content:center;
    padding:1rem; background:rgba(15,18,25,.48); backdrop-filter:blur(4px);
}
.sp-modal.is-open { display:flex; }
.sp-modal-box {
    width:min(920px, 100%); max-height:92vh; display:flex; flex-direction:column;
    background:#fff; border-radius:1.15rem; border:1px solid #E8EAED;
    box-shadow:0 24px 64px rgba(0,0,0,.18); overflow:hidden;
}
.sp-modal-head {
    display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;
    padding:1.1rem 1.25rem; border-bottom:1px solid #F0F1F3; background:#FAFBFC;
}
.sp-modal-head h3 {
    margin:.1rem 0 0; font-size:1.05rem; font-weight:700;
    font-family:Outfit,Inter,sans-serif; color:#111827;
}
.sp-close {
    width:2.1rem; height:2.1rem; border-radius:.6rem; border:1px solid #E5E7EB;
    background:#fff; font-size:1.25rem; line-height:1; color:#6B7280; cursor:pointer;
}
.sp-close:hover { background:#F9FAFB; color:#111; }
.sp-modal-body { flex:1; overflow-y:auto; padding:1.15rem 1.25rem; }
.sp-modal-foot {
    display:flex; justify-content:flex-end; gap:.55rem;
    padding:.9rem 1.25rem; border-top:1px solid #F0F1F3; background:#FAFBFC;
}
.sp-form-grid { display:grid; gap:1rem; }
@media (min-width:800px) {
    .sp-form-grid { grid-template-columns: 1.15fr .95fr; align-items:start; }
}
.sp-section {
    border:1px solid #E8EAED; border-radius:1rem; padding:1rem 1.05rem; margin-bottom:.85rem; background:#fff;
}
.sp-section h4 {
    margin:0 0 .85rem; font-size:.78rem; font-weight:800;
    font-family:Outfit,Inter,sans-serif; color:#111827; letter-spacing:.01em;
}
.sp-btn-block {
    padding:.8rem; border-radius:.85rem; background:#F9FAFB; border:1px solid #F0F1F3;
}
.sp-btn-label { margin:0 0 .55rem; font-size:.72rem; font-weight:800; color:#374151; }
.sp-upload {
    display:block; border:1.5px dashed #E5E7EB; border-radius:1rem; overflow:hidden;
    background:#FAFBFC; cursor:pointer; min-height:9.5rem;
}
.sp-upload:hover { border-color:#E7B58A; background:#FFFCFA; }
.sp-upload-ph {
    min-height:9.5rem; display:flex; flex-direction:column; align-items:center; justify-content:center;
    gap:.3rem; text-align:center; color:#6B7280;
}
.sp-upload-ph strong { color:#111827; font-size:.85rem; }
.sp-upload-ph span { font-size:.7rem; color:#9CA3AF; }
.sp-upload img { width:100%; height:9.5rem; object-fit:cover; display:block; }
.sp-check-danger {
    display:flex; align-items:center; gap:.4rem; margin-top:.55rem;
    font-size:.75rem; font-weight:700; color:#B91C1C; cursor:pointer;
}
.sp-stat-row {
    display:grid; grid-template-columns:1fr .6fr 1.3fr; gap:.45rem; margin-bottom:.45rem;
}
.sp-publish {
    display:flex; align-items:center; justify-content:space-between; gap:1rem; cursor:pointer;
}
.sp-publish strong { display:block; font-size:.85rem; color:#111827; }
.sp-publish span { display:block; font-size:.7rem; color:#9CA3AF; margin-top:.15rem; }
.sp-section-publish { background:linear-gradient(135deg,#FFFBF7,#fff); border-color:#E7B58A55; }
.sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); border:0; }
.hidden { display:none !important; }

@media (max-width:720px) {
    .sp-row { flex-wrap:wrap; }
    .sp-actions { width:100%; justify-content:flex-end; margin-left:0; padding-top:.35rem; }
    .sp-stat-row { grid-template-columns:1fr 1fr; }
    .sp-stat-row input:last-child { grid-column:1 / -1; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const SLIDE_STORE = @json(route('panel.site-ayarlari.slider.store'));
const SLIDE_BASE = @json(url('/yonetim/site-ayarlari/slider'));

function syncLinkUi(prefix) {
    const type = document.getElementById('f_' + prefix + '_link_type')?.value || 'route';
    document.getElementById(prefix + '_route_wrap')?.classList.toggle('hidden', type !== 'route');
    document.getElementById(prefix + '_url_wrap')?.classList.toggle('hidden', type !== 'url');
}

function previewSlideImage(input) {
    const file = input.files && input.files[0];
    if (file) setImagePreview(URL.createObjectURL(file));
}

function setImagePreview(url) {
    const img = document.getElementById('slideImgPreview');
    const ph = document.getElementById('slideImgPh');
    if (!img) return;
    if (url) {
        img.src = url;
        img.classList.remove('hidden');
        ph?.classList.add('hidden');
    } else {
        img.src = '';
        img.classList.add('hidden');
        ph?.classList.remove('hidden');
    }
}

function closeSlideModal() {
    document.getElementById('slideModal')?.classList.remove('is-open');
    document.body.style.overflow = '';
}

function clearStats() {
    for (let i = 0; i < 3; i++) {
        const a = document.getElementById('f_stat_sayi_' + i);
        const b = document.getElementById('f_stat_suffix_' + i);
        const c = document.getElementById('f_stat_etiket_' + i);
        if (a) a.value = '';
        if (b) b.value = '';
        if (c) c.value = '';
    }
}

window.openSlideModal = function (slide) {
    const form = document.getElementById('slideForm');
    const methodBox = document.getElementById('slideMethodField');
    form.reset();
    methodBox.innerHTML = '';
    document.getElementById('f_image_sil').checked = false;
    clearStats();

    if (slide && slide.id) {
        document.getElementById('slideModalTitle').textContent = 'Slaytı düzenle';
        form.action = SLIDE_BASE + '/' + slide.id;
        methodBox.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        f_baslik.value = slide.baslik || '';
        f_baslik_vurgulu.value = slide.baslik_vurgulu || '';
        f_etiket.value = slide.etiket || '';
        f_badge.value = slide.badge || '';
        f_alt.value = slide.alt || '';
        f_cta.value = slide.cta || '';
        f_cta2.value = slide.cta2 || '';
        f_float_1_baslik.value = slide.float_1_baslik || '';
        f_float_1_aciklama.value = slide.float_1_aciklama || '';
        f_float_2_baslik.value = slide.float_2_baslik || '';
        f_float_2_aciklama.value = slide.float_2_aciklama || '';
        f_aktif.checked = !!slide.aktif;
        const imgUrl = slide.image_url || slide.image || '';
        f_image_url.value = (imgUrl && String(imgUrl).startsWith('http')) ? imgUrl : '';
        setImagePreview(imgUrl || '');
        imageSilWrap.classList.toggle('hidden', !imgUrl);
        f_cta_link_type.value = slide.cta_link_type || 'route';
        f_cta_route.value = slide.cta_route || 'frontend.randevu';
        f_cta_custom_url.value = slide.cta_link_type === 'url' ? (slide.cta_url || '') : '';
        f_cta2_link_type.value = slide.cta2_link_type || 'route';
        f_cta2_route.value = slide.cta2_route || 'frontend.iletisim';
        f_cta2_custom_url.value = slide.cta2_link_type === 'url' ? (slide.cta2_url || '') : '';
        const stats = Array.isArray(slide.istatistikler) ? slide.istatistikler : [];
        stats.slice(0, 3).forEach((st, i) => {
            const a = document.getElementById('f_stat_sayi_' + i);
            const b = document.getElementById('f_stat_suffix_' + i);
            const c = document.getElementById('f_stat_etiket_' + i);
            if (a) a.value = st.sayi ?? st.deger ?? '';
            if (b) b.value = st.suffix || '';
            if (c) c.value = st.etiket || '';
        });
        slideSubmitBtn.textContent = 'Güncelle';
    } else {
        document.getElementById('slideModalTitle').textContent = 'Yeni slayt';
        form.action = SLIDE_STORE;
        f_baslik_vurgulu.value = '';
        f_float_1_baslik.value = '';
        f_float_1_aciklama.value = '';
        f_float_2_baslik.value = '';
        f_float_2_aciklama.value = '';
        f_cta.value = 'Randevu Al';
        f_cta2.value = 'İletişim';
        f_cta_link_type.value = 'route';
        f_cta_route.value = 'frontend.randevu';
        f_cta2_link_type.value = 'route';
        f_cta2_route.value = 'frontend.iletisim';
        f_aktif.checked = true;
        setImagePreview('');
        imageSilWrap.classList.add('hidden');
        slideSubmitBtn.textContent = 'Oluştur';
    }

    syncLinkUi('cta');
    syncLinkUi('cta2');
    document.getElementById('slideModal').classList.add('is-open');
    document.body.style.overflow = 'hidden';
};

document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const el = document.getElementById('sliderSortable');
    if (el && window.Sortable && el.querySelector('[data-id]')) {
        Sortable.create(el, {
            handle: '.sa-drag',
            animation: 180,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: async function () {
                window.saReorderNumbers?.(el);
                const ids = [...el.querySelectorAll('[data-id]')].map(n => parseInt(n.dataset.id, 10));
                try {
                    const res = await fetch(@json(route('panel.site-ayarlari.reorder')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ type: 'slider', ids })
                    });
                    if (!res.ok) throw new Error();
                    window.saToast?.('Sıralama kaydedildi', 'ok');
                } catch (e) {
                    window.saToast?.('Sıralama kaydedilemedi', 'err');
                }
            }
        });
    }

    document.querySelectorAll('.toggle-aktif').forEach(cb => {
        cb.addEventListener('change', function () {
            this.closest('.sp-row')?.classList.toggle('is-off', !this.checked);
        });
    });
    window.saInitToggles?.(@json(route('panel.site-ayarlari.toggle')), csrf);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeSlideModal();
    });
});
</script>
@endpush
