@extends('panel.layouts.app')
@section('baslik', 'Site Ayarları · Menü')
@section('sayfa_baslik', 'Site Ayarları · Menü')

@section('icerik')
@include('panel.site-ayarlari._shell')

@php
    $pageOptions = $pageOptions ?? [];
@endphp

<div class="sa-wrap">
    <div class="sa-layout sa-layout-wide">
        <div class="sa-card">
            <div class="sa-card-head">
                <div>
                    <h3>Üst menü (sidebar / header)</h3>
                    <p class="sa-hint">
                        Sürükleyerek sırayı değiştirin. Her satırda <strong>sistem sayfası</strong> dropdown’dan seçilir
                        veya harici URL girilir.
                    </p>
                </div>
                <span class="sa-badge">{{ $items->count() }} öğe</span>
            </div>
            <div class="sa-card-body">
                <div class="sa-callout mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                    <div>
                        Sıra ve aktif/pasif anında kaydedilir. Etiket, sayfa seçimi ve harici URL için
                        <strong>Menüyü kaydet</strong> kullanın. Sistemdeki tüm sayfalar listede yoksa sayfa açılınca otomatik eklenir.
                    </div>
                </div>

                <form method="POST" action="{{ route('panel.site-ayarlari.menu.kaydet') }}" id="menuForm">
                    @csrf
                    <div id="menuSortable" class="sa-list">
                        @foreach($items as $item)
                            @php
                                $hasUrl = filled($item->url);
                                $currentRoute = $item->route ?: 'frontend.anasayfa';
                            @endphp
                            <div class="sa-row {{ $item->aktif ? '' : 'is-off' }}" data-id="{{ $item->id }}">
                                <button type="button" class="sa-drag" title="Sürükle sırala" aria-label="Sürükle">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                </button>
                                <span class="sa-order">{{ str_pad((string)$loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <input type="hidden" name="id[]" value="{{ $item->id }}">
                                <div class="flex-1 min-w-0 space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-[10px] font-mono text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded-md">{{ $item->key }}</span>
                                    </div>
                                    <input type="text" name="label[]" value="{{ $item->label }}"
                                           class="sa-input !py-2.5" placeholder="Menü etiketi (sidebar’da görünen ad)">

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Bağlantı tipi</label>
                                            <select name="link_type[]" class="sa-input sa-select menu-link-type" data-row="{{ $loop->index }}">
                                                <option value="route" @selected(! $hasUrl)>Sistem sayfası</option>
                                                <option value="url" @selected($hasUrl)>Harici URL</option>
                                            </select>
                                        </div>
                                        <div class="menu-route-wrap" data-row="{{ $loop->index }}" style="{{ $hasUrl ? 'display:none' : '' }}">
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Sistem menüsü</label>
                                            <select name="route[]" class="sa-input sa-select">
                                                @foreach($pageOptions as $route => $pageLabel)
                                                    <option value="{{ $route }}" @selected($currentRoute === $route)>{{ $pageLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="menu-url-wrap sm:col-span-2" data-row="{{ $loop->index }}" style="{{ $hasUrl ? '' : 'display:none' }}">
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Harici URL</label>
                                            <input type="text" name="url[]" value="{{ $item->url }}"
                                                   class="sa-input !py-2 text-xs" placeholder="https://... veya /ozel-sayfa">
                                        </div>
                                    </div>
                                </div>
                                <label class="sa-switch mt-2" title="Menüde göster">
                                    <input type="checkbox" name="aktif[{{ $loop->index }}]" value="1" class="toggle-aktif"
                                           data-id="{{ $item->id }}" data-type="menu" @checked($item->aktif)>
                                    <span></span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="sa-actions !mt-5">
                        <p class="sa-hint m-0">Sistem sayfaları: {{ implode(', ', array_values($pageOptions)) }}</p>
                        <button type="submit" class="sa-btn sa-btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Menüyü kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function(){
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    window.saInitSortable(document.getElementById('menuSortable'), 'menu', @json(route('panel.site-ayarlari.reorder')), csrf);
    window.saInitToggles(@json(route('panel.site-ayarlari.toggle')), csrf);

    document.querySelectorAll('.menu-link-type').forEach((sel) => {
        sel.addEventListener('change', () => {
            const row = sel.getAttribute('data-row');
            const isUrl = sel.value === 'url';
            const routeWrap = document.querySelector('.menu-route-wrap[data-row="' + row + '"]');
            const urlWrap = document.querySelector('.menu-url-wrap[data-row="' + row + '"]');
            if (routeWrap) routeWrap.style.display = isUrl ? 'none' : '';
            if (urlWrap) urlWrap.style.display = isUrl ? '' : 'none';
        });
    });
})();
</script>
@endpush
