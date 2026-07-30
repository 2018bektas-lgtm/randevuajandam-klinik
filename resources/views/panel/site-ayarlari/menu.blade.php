@extends('panel.layouts.app')
@section('baslik', 'Site Ayarları · Menü')
@section('sayfa_baslik', 'Site Ayarları · Menü')

@section('icerik')
@include('panel.site-ayarlari._shell')

@php
    $pageGroups = $pageGroups ?? ['system' => $pageOptions ?? [], 'pages' => []];
    $rootItems = $rootItems ?? collect();
@endphp

<div class="sa-wrap">
    <div class="sa-layout sa-layout-wide">
        <div class="sa-card">
            <div class="sa-card-head !items-center">
                <div>
                    <h3>Üst menü (site header)</h3>
                    <p class="sa-hint">
                        <strong>Ana menü</strong> satırları sitede yan yana görünür.
                        Bir öğeyi başka bir ana menünün <strong>alt menüsü</strong> yapabilirsiniz (açılır liste).
                        Özel sayfalar (KVKK vb.) “Özel sayfalar” grubundan seçilir.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="sa-badge">{{ $items->count() }} öğe</span>
                    <form method="POST" action="{{ route('panel.site-ayarlari.menu.ekle') }}">
                        @csrf
                        <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm">+ Ana menü ekle</button>
                    </form>
                </div>
            </div>
            <div class="sa-card-body">
                <div class="sa-callout mb-4">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <strong>Hiyerarşi:</strong> “Konum” alanında <em>Ana menü</em> veya bir üst öğe seçin.
                        Alt menüler header’da açılır menü olarak çıkar. Sürükleyerek genel sırayı değiştirin;
                        etiket / konum / bağlantı için <strong>Menüyü kaydet</strong>’e basın.
                    </div>
                </div>

                @if($items->isEmpty())
                    <div class="sa-empty">
                        <strong>Henüz menü öğesi yok</strong>
                        “+ Ana menü ekle” ile başlayın veya sayfayı yenileyin (varsayılanlar otomatik oluşur).
                    </div>
                @else
                <form method="POST" action="{{ route('panel.site-ayarlari.menu.kaydet') }}" id="menuForm">
                    @csrf
                    <div id="menuSortable" class="sa-list">
                        @foreach($items as $item)
                            @php
                                $hasUrl = filled($item->url);
                                $currentRoute = $item->route ?: 'frontend.anasayfa';
                                $isChild = filled($item->parent_id);
                                $idx = $loop->index;
                            @endphp
                            <div class="sa-row {{ $item->aktif ? '' : 'is-off' }} {{ $isChild ? 'menu-row-child' : 'menu-row-root' }}"
                                 data-id="{{ $item->id }}">
                                <button type="button" class="sa-drag" title="Sürükle sırala" aria-label="Sürükle">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                </button>
                                <span class="sa-order">{{ str_pad((string)($loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>
                                <input type="hidden" name="id[]" value="{{ $item->id }}">

                                <div class="flex-1 min-w-0 space-y-2.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if($isChild)
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-600 bg-brand-50 border border-brand-100 px-1.5 py-0.5 rounded-md">Alt menü</span>
                                        @else
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded-md">Ana menü</span>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Menü etiketi</label>
                                            <input type="text" name="label[]" value="{{ $item->label }}"
                                                   class="sa-input !py-2.5" placeholder="Sitede görünen ad">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Konum (hiyerarşi)</label>
                                            <select name="parent_id[]" class="sa-input sa-select menu-parent-select">
                                                <option value="0" @selected(! $isChild)>— Ana menü (üst seviye) —</option>
                                                @foreach($rootItems as $root)
                                                    @if((int) $root->id === (int) $item->id)
                                                        @continue
                                                    @endif
                                                    <option value="{{ $root->id }}" @selected((int) $item->parent_id === (int) $root->id)>
                                                        Alt menü → {{ $root->label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Bağlantı tipi</label>
                                            <select name="link_type[]" class="sa-input sa-select menu-link-type" data-row="{{ $idx }}">
                                                <option value="route" @selected(! $hasUrl)>Site sayfası</option>
                                                <option value="url" @selected($hasUrl)>Harici URL</option>
                                            </select>
                                        </div>
                                        <div class="menu-route-wrap" data-row="{{ $idx }}" style="{{ $hasUrl ? 'display:none' : '' }}">
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Gideceği sayfa</label>
                                            <select name="route[]" class="sa-input sa-select">
                                                <optgroup label="Sistem sayfaları">
                                                    @foreach($pageGroups['system'] ?? [] as $route => $pageLabel)
                                                        <option value="{{ $route }}" @selected($currentRoute === $route)>{{ $pageLabel }}</option>
                                                    @endforeach
                                                </optgroup>
                                                @if(! empty($pageGroups['pages']))
                                                    <optgroup label="Özel sayfalar">
                                                        @foreach($pageGroups['pages'] as $route => $pageLabel)
                                                            <option value="{{ $route }}" @selected($currentRoute === $route)>{{ $pageLabel }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="menu-url-wrap sm:col-span-2" data-row="{{ $idx }}" style="{{ $hasUrl ? '' : 'display:none' }}">
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Harici URL</label>
                                            <input type="text" name="url[]" value="{{ $item->url }}"
                                                   class="sa-input !py-2 text-xs" placeholder="https://... veya /ozel-yol">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col items-center gap-2 shrink-0 mt-1">
                                    <label class="sa-switch" title="Menüde göster">
                                        <input type="checkbox" name="aktif[{{ $idx }}]" value="1" class="toggle-aktif"
                                               data-id="{{ $item->id }}" data-type="menu" @checked($item->aktif)>
                                        <span></span>
                                    </label>
                                    @if(! $isChild)
                                        <button type="submit" form="menu-add-child-{{ $item->id }}"
                                                class="text-[10px] font-bold text-brand-600 hover:underline whitespace-nowrap"
                                                title="Alt menü ekle">+ Alt</button>
                                    @endif
                                    <button type="submit" form="menu-del-{{ $item->id }}"
                                            class="text-[10px] font-bold text-red-600 hover:underline"
                                            onclick="return confirm('Bu menü öğesi silinsin mi?');">Sil</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="sa-actions !mt-5">
                        <p class="sa-hint m-0">
                            Özel sayfa yoksa önce
                            <a href="{{ route('panel.site-ayarlari.sayfalar') }}" class="font-semibold text-brand-600 underline">Sayfalar</a>
                            bölümünden oluşturun.
                        </p>
                        <button type="submit" class="sa-btn sa-btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Menüyü kaydet
                        </button>
                    </div>
                </form>

                {{-- Nested form yasağı: sil / alt ekle formları ana formun dışında --}}
                @foreach($items as $item)
                    <form id="menu-del-{{ $item->id }}" method="POST" action="{{ route('panel.site-ayarlari.menu.sil', $item->id) }}" class="hidden">
                        @csrf
                    </form>
                    @if(! $item->parent_id)
                        <form id="menu-add-child-{{ $item->id }}" method="POST" action="{{ route('panel.site-ayarlari.menu.ekle') }}" class="hidden">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $item->id }}">
                        </form>
                    @endif
                @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .menu-row-child {
        margin-left: 1.25rem;
        border-left: 3px solid rgba(201, 106, 43, 0.35);
        background: linear-gradient(90deg, #FFFBF7 0%, #fff 40%);
    }
    @media (min-width: 640px) {
        .menu-row-child { margin-left: 2rem; }
    }
</style>
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
