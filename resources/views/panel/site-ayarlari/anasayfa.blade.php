@extends('panel.layouts.app')
@section('baslik', 'Site Ayarları · Ana Sayfa')
@section('sayfa_baslik', 'Site Ayarları · Ana Sayfa')

@section('icerik')
@include('panel.site-ayarlari._shell')

<div class="sa-wrap">
    <div class="sa-card">
        <div class="sa-card-head">
            <div>
                <h3>Ana sayfa bölümleri</h3>
                <p class="sa-hint">Blokları sürükleyerek public sitedeki sırayı değiştirin — <strong>anında kaydedilir</strong>. Başlık ve alt metin opsiyoneldir.</p>
            </div>
            <span class="sa-badge">{{ $sections->count() }} bölüm</span>
        </div>
        <div class="sa-card-body">
            <div class="sa-callout mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm10 0a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/></svg>
                <div>Pasif bölümler sitede gizlenir. Slider’ı kapatmak için bu listedeki “Hero / Slider”ı kapatabilir veya Slider sekmesinden modu değiştirebilirsiniz.</div>
            </div>

            <form method="POST" action="{{ route('panel.site-ayarlari.anasayfa.kaydet') }}">
                @csrf
                <div id="sectionSortable" class="sa-list">
                    @foreach($sections as $sec)
                        <div class="sa-row {{ $sec->aktif ? '' : 'is-off' }}" data-id="{{ $sec->id }}">
                            <button type="button" class="sa-drag" title="Sürükle sırala" aria-label="Sürükle">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                            </button>
                            <span class="sa-order">{{ str_pad((string)$loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <input type="hidden" name="id[]" value="{{ $sec->id }}">
                            <div class="flex-1 min-w-0 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-bold text-ink font-display">{{ $sec->label }}</span>
                                    <span class="text-[10px] font-mono text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded-md">{{ $sec->key }}</span>
                                </div>
                                <div class="sa-grid-2 !gap-2">
                                    <input type="text" name="baslik[]" value="{{ $sec->baslik }}" placeholder="Bölüm başlığı (opsiyonel)" class="sa-input !py-2">
                                    <input type="text" name="alt_metin[]" value="{{ $sec->alt_metin }}" placeholder="Alt metin (opsiyonel)" class="sa-input !py-2">
                                </div>
                            </div>
                            <label class="sa-switch mt-2" title="Göster / gizle">
                                <input type="checkbox" name="aktif[{{ $loop->index }}]" value="1" class="toggle-aktif"
                                       data-id="{{ $sec->id }}" data-type="anasayfa" @checked($sec->aktif)>
                                <span></span>
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="sa-actions !mt-5">
                    <p class="sa-hint m-0">Sıra + görünürlük AJAX · metinler için kaydet.</p>
                    <button type="submit" class="sa-btn sa-btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Metinleri kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function(){
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    window.saInitSortable(document.getElementById('sectionSortable'), 'anasayfa', @json(route('panel.site-ayarlari.reorder')), csrf);
    window.saInitToggles(@json(route('panel.site-ayarlari.toggle')), csrf);
})();
</script>
@endpush
