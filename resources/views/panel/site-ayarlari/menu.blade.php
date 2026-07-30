@extends('panel.layouts.app')
@section('baslik', 'Site Ayarları · Menü')
@section('sayfa_baslik', 'Site Ayarları · Menü')

@section('icerik')
@include('panel.site-ayarlari._shell')

@php
    $pageGroups = $pageGroups ?? ['system' => $pageOptions ?? [], 'pages' => []];
    $parent = $parent ?? null;
    $childCounts = $childCounts ?? collect();
    $isSubView = $parent !== null;
@endphp

<div class="sa-wrap">
    <div class="sa-layout sa-layout-wide">
        <div class="sa-card">
            <div class="sa-card-head !items-center">
                <div class="min-w-0">
                    @if($isSubView)
                        <a href="{{ route('panel.site-ayarlari.menu') }}"
                           class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 hover:text-brand-600 mb-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                            Ana menü listesi
                        </a>
                        <h3 class="!mb-0.5">Alt menüler · {{ $parent->label }}</h3>
                        <p class="sa-hint">
                            Bu öğeler sitede “{{ $parent->label }}” altında açılır menü olarak görünür.
                        </p>
                    @else
                        <h3>Ana menü (site header)</h3>
                        <p class="sa-hint">
                            Tablodaki satırlar sitede yan yana görünür.
                            <strong>Alt menü (N)</strong> tıklayarak o menünün alt öğelerini ekleyin / düzenleyin.
                        </p>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="sa-badge">{{ $items->count() }} satır</span>
                    <form method="POST" action="{{ route('panel.site-ayarlari.menu.ekle') }}">
                        @csrf
                        @if($isSubView)
                            <input type="hidden" name="parent_id" value="{{ $parent->id }}">
                            <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm">+ Alt menü ekle</button>
                        @else
                            <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm">+ Ana menü ekle</button>
                        @endif
                    </form>
                </div>
            </div>

            <div class="sa-card-body !pt-3">
                @if($items->isEmpty())
                    <div class="sa-empty">
                        @if($isSubView)
                            <strong>Henüz alt menü yok</strong>
                            “+ Alt menü ekle” ile “{{ $parent->label }}” altına öğe ekleyin.
                        @else
                            <strong>Henüz menü öğesi yok</strong>
                            “+ Ana menü ekle” ile başlayın.
                        @endif
                    </div>
                @else
                    <form method="POST" action="{{ route('panel.site-ayarlari.menu.kaydet') }}" id="menuForm">
                        @csrf
                        <input type="hidden" name="context_parent_id" value="{{ $isSubView ? $parent->id : 0 }}">

                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="px-2 py-2.5 w-10 text-center" title="Sürükle">⋮⋮</th>
                                        <th class="px-2 py-2.5 w-10">#</th>
                                        <th class="px-3 py-2.5 min-w-[140px]">Menü etiketi</th>
                                        <th class="px-3 py-2.5 min-w-[160px]">Bağlantı tipi</th>
                                        <th class="px-3 py-2.5 min-w-[200px]">Gideceği yer</th>
                                        <th class="px-3 py-2.5 w-20 text-center">Aktif</th>
                                        @unless($isSubView)
                                            <th class="px-3 py-2.5 w-32">Alt menü</th>
                                        @endunless
                                        <th class="px-3 py-2.5 w-16 text-right">Sil</th>
                                    </tr>
                                </thead>
                                <tbody id="menuSortable" class="divide-y divide-slate-100 bg-white">
                                    @foreach($items as $item)
                                        @php
                                            $hasUrl = filled($item->url);
                                            $currentRoute = $item->route ?: 'frontend.anasayfa';
                                            $idx = $loop->index;
                                            $subCount = (int) ($childCounts[$item->id] ?? 0);
                                            $linkLabel = $hasUrl
                                                ? \Illuminate\Support\Str::limit($item->url, 36)
                                                : ($pageOptions[$currentRoute] ?? $currentRoute);
                                        @endphp
                                        <tr class="menu-tr {{ $item->aktif ? '' : 'opacity-60' }} hover:bg-slate-50/80"
                                            data-id="{{ $item->id }}">
                                            <td class="px-2 py-2 text-center align-middle">
                                                <button type="button" class="sa-drag inline-flex text-slate-400 hover:text-brand-600" title="Sürükle sırala" aria-label="Sürükle">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                                </button>
                                                <input type="hidden" name="id[]" value="{{ $item->id }}">
                                            </td>
                                            <td class="px-2 py-2 align-middle">
                                                <span class="sa-order !m-0">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                            </td>
                                            <td class="px-3 py-2 align-middle">
                                                <input type="text" name="label[]" value="{{ $item->label }}"
                                                       class="sa-input !py-2 !text-xs !rounded-lg" placeholder="Sitede görünen ad">
                                            </td>
                                            <td class="px-3 py-2 align-middle">
                                                <select name="link_type[]" class="sa-input sa-select menu-link-type !py-2 !text-xs !rounded-lg" data-row="{{ $idx }}">
                                                    <option value="route" @selected(! $hasUrl)>Site sayfası</option>
                                                    <option value="url" @selected($hasUrl)>Harici URL</option>
                                                </select>
                                            </td>
                                            <td class="px-3 py-2 align-middle">
                                                <div class="menu-route-wrap" data-row="{{ $idx }}" style="{{ $hasUrl ? 'display:none' : '' }}">
                                                    <select name="route[]" class="sa-input sa-select !py-2 !text-xs !rounded-lg">
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
                                                <div class="menu-url-wrap" data-row="{{ $idx }}" style="{{ $hasUrl ? '' : 'display:none' }}">
                                                    <input type="text" name="url[]" value="{{ $item->url }}"
                                                           class="sa-input !py-2 !text-xs !rounded-lg font-mono"
                                                           placeholder="https://...">
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-center align-middle">
                                                <label class="sa-switch !mx-auto" title="Menüde göster">
                                                    <input type="checkbox" name="aktif[{{ $idx }}]" value="1" class="toggle-aktif"
                                                           data-id="{{ $item->id }}" data-type="menu" @checked($item->aktif)>
                                                    <span></span>
                                                </label>
                                            </td>
                                            @unless($isSubView)
                                                <td class="px-3 py-2 align-middle">
                                                    <a href="{{ route('panel.site-ayarlari.menu', ['ust' => $item->id]) }}"
                                                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-brand-200 bg-brand-50 text-brand-700 text-[11px] font-bold hover:bg-brand-100 transition-colors whitespace-nowrap">
                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                                        Alt menü ({{ $subCount }})
                                                    </a>
                                                </td>
                                            @endunless
                                            <td class="px-3 py-2 text-right align-middle">
                                                <button type="submit" form="menu-del-{{ $item->id }}"
                                                        class="text-[11px] font-bold text-red-600 hover:underline"
                                                        onclick="return confirm(@json($isSubView ? 'Bu alt menü silinsin mi?' : 'Bu ana menü ve alt menüleri silinsin mi?'));">
                                                    Sil
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="sa-actions !mt-5">
                            <p class="sa-hint m-0">
                                @if($isSubView)
                                    Üst menü: <strong>{{ $parent->label }}</strong>
                                @else
                                    Özel sayfa için önce
                                    <a href="{{ route('panel.site-ayarlari.sayfalar') }}" class="font-semibold text-brand-600 underline">Sayfalar</a>
                                    oluşturun.
                                @endif
                            </p>
                            <button type="submit" class="sa-btn sa-btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                {{ $isSubView ? 'Alt menüleri kaydet' : 'Menüyü kaydet' }}
                            </button>
                        </div>
                    </form>

                    @foreach($items as $item)
                        <form id="menu-del-{{ $item->id }}" method="POST" action="{{ route('panel.site-ayarlari.menu.sil', $item->id) }}" class="hidden">
                            @csrf
                        </form>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    #menuSortable .menu-tr.sortable-ghost { opacity: .45; background: #FFF7ED; }
    #menuSortable .menu-tr.sortable-chosen { background: #fff; box-shadow: 0 8px 24px rgba(201,106,43,.12); }
    .sa-order { width: 1.75rem; height: 1.75rem; border-radius: .5rem; display: inline-flex; align-items: center; justify-content: center;
        font-size: .65rem; font-weight: 800; font-family: ui-monospace, monospace;
        background: #FFF7ED; color: #C96A2B; border: 1px solid rgba(231,181,138,.55); }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function(){
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const tbody = document.getElementById('menuSortable');
    if (tbody && window.Sortable) {
        Sortable.create(tbody, {
            handle: '.sa-drag',
            animation: 180,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            draggable: 'tr.menu-tr',
            onEnd: async function () {
                const ids = [...tbody.querySelectorAll('tr.menu-tr[data-id]')].map(n => parseInt(n.dataset.id, 10));
                tbody.querySelectorAll('tr.menu-tr').forEach((row, i) => {
                    const badge = row.querySelector('.sa-order');
                    if (badge) badge.textContent = String(i + 1).padStart(2, '0');
                });
                try {
                    const res = await fetch(@json(route('panel.site-ayarlari.reorder')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ type: 'menu', ids })
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    if (window.saToast) window.saToast('Sıralama kaydedildi', 'ok');
                } catch (e) {
                    if (window.saToast) window.saToast('Sıralama kaydedilemedi', 'err');
                }
            }
        });
    }
    window.saInitToggles?.(@json(route('panel.site-ayarlari.toggle')), csrf);

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
