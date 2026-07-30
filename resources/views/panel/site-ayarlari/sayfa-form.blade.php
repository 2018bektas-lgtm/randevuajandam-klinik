@extends('panel.layouts.app')
@php $edit = $page !== null; @endphp
@section('baslik', $edit ? 'Sayfa dÃ¼zenle' : 'Yeni sayfa')
@section('sayfa_baslik', $edit ? 'Sayfa dÃ¼zenle' : 'Yeni sayfa')

@section('icerik')
@include('panel.site-ayarlari._shell')

<div class="sa-wrap mb-4">
    <a href="{{ route('panel.site-ayarlari.sayfalar') }}" class="inline-flex items-center gap-1 text-xs text-[#6B7280] hover:text-[#C96A2B] font-semibold transition-colors font-display">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"></path>
        </svg>
        Sayfa listesine dÃ¶n
    </a>
</div>

<div class="bg-white rounded-2xl border border-[#E5E7EB] p-6 sm:p-8 shadow-sm">
    <form method="POST"
          action="{{ $edit ? route('panel.site-ayarlari.sayfalar.guncelle', $page->id) : route('panel.site-ayarlari.sayfalar.kaydet') }}"
          class="space-y-8"
          id="sayfaForm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2 space-y-6">
                <div class="space-y-1.5">
                    <label for="baslik" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Sayfa baÅŸlÄ±ÄŸÄ±</label>
                    <input type="text" name="baslik" id="baslik" required
                           value="{{ old('baslik', $page->baslik ?? '') }}"
                           placeholder="Ã–rn: KVKK AydÄ±nlatma Metni"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                </div>

                <div class="space-y-1.5">
                    <label for="slug" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">URL (slug)</label>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] text-slate-400 font-mono shrink-0">/sayfa/</span>
                        <input type="text" name="slug" id="slug"
                               value="{{ old('slug', $page->slug ?? '') }}"
                               placeholder="kvkk-aydinlatma (boÅŸ = baÅŸlÄ±ktan)"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs font-mono transition-all">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="icerik" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Sayfa iÃ§eriÄŸi</label>
                    <textarea name="icerik" id="icerik" rows="14"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs">{{ old('icerik', $page->icerik ?? '') }}</textarea>
                </div>
            </div>

            <div class="space-y-6">
                <div class="space-y-1.5 flex items-center justify-between p-4.5 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="max-w-[150px]">
                        <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">YayÄ±nda</label>
                        <span class="text-[9px] text-[#6B7280]">Public sitede gÃ¶rÃ¼nsÃ¼n mÃ¼?</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="aktif" id="aktif" value="1" class="sr-only peer"
                               @checked(old('aktif', $page->aktif ?? true))>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                    </label>
                </div>

                <div class="space-y-1.5 flex items-center justify-between p-4.5 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="max-w-[150px]">
                        <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Footerâ€™da gÃ¶ster</label>
                        <span class="text-[9px] text-[#6B7280]">Site altÄ±nda link</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="footer_goster" id="footer_goster" value="1" class="sr-only peer"
                               @checked(old('footer_goster', $page->footer_goster ?? false))>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                    </label>
                </div>

                <div class="space-y-1.5">
                    <label for="sira" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Footer sÄ±rasÄ±</label>
                    <input type="number" name="sira" id="sira" min="0" max="9999"
                           value="{{ old('sira', $page->sira ?? 0) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-xs">
                </div>

                <div class="bg-slate-50/50 rounded-2xl border border-[#E5E7EB] p-5 space-y-4">
                    <div class="flex items-center gap-2 border-b border-[#E5E7EB] pb-3">
                        <svg class="w-4 h-4 shrink-0 text-[#C96A2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.774 4.774zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-xs font-bold font-display text-[#111827] uppercase tracking-wider">SEO AyarlarÄ±</span>
                    </div>

                    <div class="space-y-1.5">
                        <label for="meta_baslik" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Meta BaÅŸlÄ±k (Title)</label>
                        <input type="text" name="meta_baslik" id="meta_baslik" maxlength="255"
                               value="{{ old('meta_baslik', $page->meta_baslik ?? '') }}"
                               placeholder="Arama motoru baÅŸlÄ±ÄŸÄ± (boÅŸ = sayfa baÅŸlÄ±ÄŸÄ±)"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>

                    <div class="space-y-1.5">
                        <label for="meta_aciklama" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Meta AÃ§Ä±klama (Description)</label>
                        <textarea name="meta_aciklama" id="meta_aciklama" rows="3" maxlength="500"
                                  placeholder="Arama sonucu Ã¶zeti (â‰ˆ160 karakter)"
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all resize-none">{{ old('meta_aciklama', $page->meta_aciklama ?? '') }}</textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Anahtar Kelimeler</label>
                        <input type="hidden" name="meta_anahtar_kelimeler" id="meta_anahtar_kelimeler"
                               value="{{ old('meta_anahtar_kelimeler', $page->meta_anahtar_kelimeler ?? '') }}">
                        <div id="tagContainer"
                             class="w-full px-3 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus-within:border-[#C96A2B] focus-within:ring-1 focus-within:ring-[#C96A2B] text-xs transition-all flex flex-wrap gap-2 items-center min-h-[42px] cursor-text">
                            <input type="text" id="tagInput" placeholder="Kelime ekleyin..."
                                   class="flex-grow bg-transparent border-0 focus:border-0 focus:ring-0 focus:outline-none outline-none text-xs py-0.5 placeholder-gray-400 min-w-[120px]">
                        </div>
                        <span class="text-[9px] text-gray-400">Enter veya virgÃ¼l ile ekleyin.</span>
                    </div>
                </div>

                <p class="text-[10px] text-slate-500 leading-relaxed">
                    MenÃ¼ye eklemek iÃ§in kaydettikten sonra
                    <a href="{{ route('panel.site-ayarlari.menu') }}" class="font-bold text-[#C96A2B] underline">MenÃ¼</a>
                    sayfasÄ±ndan <strong>Sayfa: â€¦</strong> seÃ§in.
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-3.5 pt-4 border-t border-[#E5E7EB]">
            <a href="{{ route('panel.site-ayarlari.sayfalar') }}"
               class="px-6 py-3 rounded-xl border border-[#E5E7EB] text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors font-display">
                Ä°ptal
            </a>
            <button type="submit"
                    class="px-8 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all shadow-sm hover:shadow-md cursor-pointer font-display">
                {{ $edit ? 'GÃ¼ncelle' : 'Kaydet' }}
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.config.versionCheck = false;
        CKEDITOR.replace('icerik', {
            language: 'tr',
            height: 380,
            removeButtons: 'About',
            uiColor: '#FFFFFF',
            allowedContent: true
        });
    }

    const form = document.getElementById('sayfaForm');
    if (form) {
        form.addEventListener('submit', function () {
            if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.icerik) {
                CKEDITOR.instances.icerik.updateElement();
            }
        });
    }

    const hiddenInput = document.getElementById('meta_anahtar_kelimeler');
    const tagContainer = document.getElementById('tagContainer');
    const tagInput = document.getElementById('tagInput');
    if (!hiddenInput || !tagContainer || !tagInput) return;

    let tags = [];
    if (hiddenInput.value.trim() !== '') {
        tags = hiddenInput.value.split(',').map(t => t.trim()).filter(Boolean);
        renderTags();
    }

    function renderTags() {
        tagContainer.querySelectorAll('.tag-badge').forEach(b => b.remove());
        tags.forEach((tag, index) => {
            const badge = document.createElement('span');
            badge.className = 'tag-badge inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/35 rounded-full text-[10px] font-bold font-display select-none';
            badge.innerHTML = '<span>' + tag + '</span><button type="button" class="tag-remove text-[#C96A2B] hover:text-red-600 font-bold text-xs" data-index="' + index + '">&times;</button>';
            tagContainer.insertBefore(badge, tagInput);
        });
        hiddenInput.value = tags.join(',');
    }

    function addTag() {
        const val = tagInput.value.trim().replace(/,/g, '');
        if (val !== '' && !tags.includes(val)) {
            tags.push(val);
            renderTags();
        }
        tagInput.value = '';
    }

    tagInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addTag();
        }
    });
    tagInput.addEventListener('blur', addTag);
    tagContainer.addEventListener('click', function (e) {
        if (e.target.classList.contains('tag-remove')) {
            tags.splice(parseInt(e.target.getAttribute('data-index'), 10), 1);
            renderTags();
            tagInput.focus();
        } else if (e.target === tagContainer) {
            tagInput.focus();
        }
    });
});
</script>
@endsection
