@extends('panel.layouts.app')
@section('baslik', 'Site Ayarları · Footer')
@section('sayfa_baslik', 'Site Ayarları · Footer')

@section('icerik')
@include('panel.site-ayarlari._shell')

@php
    $pageGroups = $pageGroups ?? ['system' => $pageOptions ?? [], 'pages' => []];
@endphp

<div class="sa-wrap">
    <div class="sa-layout sa-layout-wide">
        <div class="sa-card">
            <div class="sa-card-head !items-center">
                <div class="min-w-0">
                    <h3>Footer linkleri</h3>
                    <p class="sa-hint">
                        Sitede footer “Keşfet / Hızlı linkler” sütununda görünen bağlantılar.
                        Header menüsünden <strong>bağımsızdır</strong>. Sürükleyerek sıralayın.
                        Yasal sayfalar (KVKK vb.) için ayrıca
                        <a href="{{ route('panel.site-ayarlari.sayfalar') }}" class="font-semibold text-brand-600 underline">Sayfalar → Footer’da göster</a>
                        kullanın. Footer metni:
                        <a href="{{ route('panel.site-ayarlari.genel') }}" class="font-semibold text-brand-600 underline">Genel</a>.
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="sa-badge">{{ $items->count() }} satır</span>
                    <form method="POST" action="{{ route('panel.site-ayarlari.footer.ekle') }}">
                        @csrf
                        <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm">+ Link ekle</button>
                    </form>
                </div>
            </div>

            <div class="sa-card-body !pt-3">
                @if($items->isEmpty())
                    <div class="sa-empty">
                        <strong>Henüz footer linki yok</strong>
                        “+ Link ekle” ile başlayın.
                    </div>
                @else
                    <form method="POST" action="{{ route('panel.site-ayarlari.footer.kaydet') }}" id="footerForm">
                        @csrf
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="px-2 py-2.5 w-10 text-center">⋮⋮</th>
                                        <th class="px-2 py-2.5 w-10">#</th>
                                        <th class="px-3 py-2.5 min-w-[140px]">Etiket</th>
                                        <th class="px-3 py-2.5 min-w-[140px]">Bağlantı tipi</th>
                                        <th class="px-3 py-2.5 min-w-[200px]">Gideceği yer</th>
                                        <th class="px-3 py-2.5 w-20 text-center">Aktif</th>
                                        <th class="px-3 py-2.5 w-16 text-right">Sil</th>
                                    </tr>
                                </thead>
                                <tbody id="footerSortable" class="divide-y divide-slate-100 bg-white">
                                    @foreach($items as $item)
                                        @php
                                            $hasUrl = filled($item->url);
                                            $currentRoute = $item->route ?: 'frontend.anasayfa';
                                            $idx = $loop->index;
                                        @endphp
                                        <tr class="footer-tr {{ $item->aktif ? '' : 'opacity-60' }} hover:bg-slate-50/80" data-id="{{ $item->id }}">
                                            <td class="px-2 py-2 text-center align-middle">
                                                <button type="button" class="sa-drag inline-flex text-slate-400 hover:text-brand-600" title="Sürükle" aria-label="Sürükle">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                                </button>
                                                <input type="hidden" name="id[]" value="{{ $item->id }}">
                                            </td>
                                            <td class="px-2 py-2 align-middle">
                                                <span class="sa-order !m-0">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                            </td>
                                            <td class="px-3 py-2 align-middle">
                                                <input type="text" name="label[]" value="{{ $item->label }}"
                                                       class="sa-input !py-2 !text-xs !rounded-lg" placeholder="Footer’da görünen ad">
                                            </td>
                                            <td class="px-3 py-2 align-middle">
                                                <select name="link_type[]" class="sa-input sa-select footer-link-type !py-2 !text-xs !rounded-lg" data-row="{{ $idx }}">
                                                    <option value="route" @selected(! $hasUrl)>Site sayfası</option>
                                                    <option value="url" @selected($hasUrl)>Harici URL</option>
                                                </select>
                                            </td>
                                            <td class="px-3 py-2 align-middle">
                                                <div class="footer-route-wrap" data-row="{{ $idx }}" style="{{ $hasUrl ? 'display:none' : '' }}">
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
                                                <div class="footer-url-wrap" data-row="{{ $idx }}" style="{{ $hasUrl ? '' : 'display:none' }}">
                                                    <input type="text" name="url[]" value="{{ $item->url }}"
                                                           class="sa-input !py-2 !text-xs !rounded-lg font-mono" placeholder="https://...">
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-center align-middle">
                                                <label class="sa-switch !mx-auto" title="Footer’da göster">
                                                    <input type="checkbox" name="aktif[{{ $idx }}]" value="1" class="toggle-aktif"
                                                           data-id="{{ $item->id }}" data-type="footer" @checked($item->aktif)>
                                                    <span></span>
                                                </label>
                                            </td>
                                            <td class="px-3 py-2 text-right align-middle">
                                                <button type="submit" form="footer-del-{{ $item->id }}"
                                                        class="text-[11px] font-bold text-red-600 hover:underline"
                                                        onclick="return confirm('Bu footer linki silinsin mi?');">Sil</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="sa-actions !mt-5">
                            <p class="sa-hint m-0">Değişiklikler kaydedilmeden sitede görünmez (sıra ve aktif anında kaydolur).</p>
                            <button type="submit" class="sa-btn sa-btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Footer’ı kaydet
                            </button>
                        </div>
                    </form>

                    @foreach($items as $item)
                        <form id="footer-del-{{ $item->id }}" method="POST" action="{{ route('panel.site-ayarlari.footer.sil', $item->id) }}" class="hidden">
                            @csrf
                        </form>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    #footerSortable .footer-tr.sortable-ghost { opacity: .45; background: #FFF7ED; }
    #footerSortable .footer-tr.sortable-chosen { background: #fff; box-shadow: 0 8px 24px rgba(201,106,43,.12); }
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
    const tbody = document.getElementById('footerSortable');
    if (tbody && window.Sortable) {
        Sortable.create(tbody, {
            handle: '.sa-drag',
            animation: 180,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            draggable: 'tr.footer-tr',
            onEnd: async function () {
                const ids = [...tbody.querySelectorAll('tr.footer-tr[data-id]')].map(n => parseInt(n.dataset.id, 10));
                tbody.querySelectorAll('tr.footer-tr').forEach((row, i) => {
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
                        body: JSON.stringify({ type: 'footer', ids })
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

    document.querySelectorAll('.footer-link-type').forEach((sel) => {
        sel.addEventListener('change', () => {
            const row = sel.getAttribute('data-row');
            const isUrl = sel.value === 'url';
            const routeWrap = document.querySelector('.footer-route-wrap[data-row="' + row + '"]');
            const urlWrap = document.querySelector('.footer-url-wrap[data-row="' + row + '"]');
            if (routeWrap) routeWrap.style.display = isUrl ? 'none' : '';
            if (urlWrap) urlWrap.style.display = isUrl ? '' : 'none';
        });
    });
})();
</script>
@endpush
