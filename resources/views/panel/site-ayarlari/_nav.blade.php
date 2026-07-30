@php
    $tabs = [
        'genel' => [
            'route' => 'panel.site-ayarlari.genel',
            'label' => 'Genel',
            'desc' => 'Vitrin & logo',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>',
        ],
        'temalar' => [
            'route' => 'panel.site-ayarlari.temalar',
            'label' => 'Temalar',
            'desc' => 'Hazır tasarımlar',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/></svg>',
        ],
        'menu' => [
            'route' => 'panel.site-ayarlari.menu',
            'label' => 'Menü',
            'desc' => 'Sürükle sırala',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10"/></svg>',
        ],
        'slider' => [
            'route' => 'panel.site-ayarlari.slider',
            'label' => 'Slider',
            'desc' => 'Hero slaytlar',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
        ],
        'anasayfa' => [
            'route' => 'panel.site-ayarlari.anasayfa',
            'label' => 'Ana Sayfa',
            'desc' => 'Bölüm sırası',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>',
        ],
        'seo' => [
            'route' => 'panel.site-ayarlari.seo',
            'label' => 'SEO',
            'desc' => 'Meta & etiketler',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>',
        ],
        'iletisim' => [
            'route' => 'panel.site-ayarlari.iletisim',
            'label' => 'İletişim',
            'desc' => 'Sayfa blokları',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        ],
        'sayfalar' => [
            'route' => 'panel.site-ayarlari.sayfalar',
            'label' => 'Sayfalar',
            'desc' => 'KVKK, özel sayfa',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        ],
    ];
@endphp
<div class="sa-wrap mb-5">
    <div class="sa-card">
        <div class="sa-card-head !items-center">
            <div class="flex items-start gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center shadow-md shadow-brand-500/20 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-[15px] font-bold font-display text-ink tracking-tight m-0">Site Ayarları</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                        Vitrin tasarımı, menü, slider ve SEO — yerel veritabanında saklanır.
                    </p>
                </div>
            </div>
            <a href="{{ route('frontend.anasayfa') }}" target="_blank"
               class="sa-btn sa-btn-ghost sa-btn-sm shrink-0">
                Public site
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
        <div class="p-3 sm:p-3.5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-8 gap-2">
            @foreach($tabs as $key => $tab)
                @php $active = ($group ?? '') === $key; @endphp
                <a href="{{ route($tab['route']) }}"
                   class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all
                          {{ $active
                            ? 'bg-brand-500 text-white border-brand-500 shadow-sm shadow-brand-500/20'
                            : 'bg-[#FAFBFC] text-slate-600 border-[#E8EAED] hover:bg-white hover:border-brand-500/40 hover:text-ink' }}">
                    <span class="shrink-0 opacity-90">{!! $tab['icon'] !!}</span>
                    <span class="min-w-0">
                        <span class="block text-xs font-bold font-display leading-tight">{{ $tab['label'] }}</span>
                        <span class="block text-[10px] mt-0.5 truncate {{ $active ? 'text-white/75' : 'text-slate-400' }}">{{ $tab['desc'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</div>
