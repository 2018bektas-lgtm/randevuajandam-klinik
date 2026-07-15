<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('baslik', 'Hekim Paneli') · Klinik Sitesi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50:'#FFF7ED', 100:'#FFEDD5', 500:'#C96A2B', 600:'#B55A20', 700:'#9A4A18' },
                        ink: '#111827'
                    },
                    fontFamily: {
                        sans: ['Inter','system-ui','sans-serif'],
                        display: ['Outfit','Inter','sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        html, body { height: 100%; }
        body { font-family: Inter, system-ui, sans-serif; background: #F5F5F4; }
        .font-display { font-family: Outfit, Inter, sans-serif; }
        .nav-active { background: #FFF7ED; color: #C96A2B; font-weight: 600; border-left: 4px solid #C96A2B; }
        .nav-idle { color: #6B7280; }
        .nav-idle:hover { color: #111827; background: #FAFAFA; }
        .nav-child-active { background: #FFF7ED; color: #C96A2B; font-weight: 600; }
        .nav-child-idle { color: #6B7280; }
        .nav-child-idle:hover { color: #111827; background: #F9FAFB; }
        .nav-group-btn { color: #374151; }
        .nav-group-btn:hover { background: #FAFAFA; color: #111827; }
        .nav-group-btn.is-open .nav-chevron { transform: rotate(180deg); }
        .nav-group-btn.has-active { color: #C96A2B; }
        .nav-group-panel { display: none; }
        .nav-group-panel.is-open { display: block; }
        /* Sidebar scrollbar */
        .side-scroll { scrollbar-width: thin; scrollbar-color: #E5E7EB transparent; }
        .side-scroll::-webkit-scrollbar { width: 6px; }
        .side-scroll::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 8px; }
        .side-scroll::-webkit-scrollbar-track { background: transparent; }
        /* Main content scrollbar */
        .main-scroll { scrollbar-width: thin; scrollbar-color: #E5E7EB transparent; }
        .main-scroll::-webkit-scrollbar { width: 8px; }
        .main-scroll::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 8px; }
    </style>
    @stack('styles')
    @include('panel.partials.sidebar-ysb-theme')
</head>
<body class="text-[#4B5563] antialiased h-screen overflow-hidden">
@php
    $user = session('doctor_api_user', []);
    try {
        $apiConfigured = $apiConfigured ?? app(\App\Services\ApiConfigService::class)->isConfigured();
    } catch (\Throwable) {
        $apiConfigured = $apiConfigured ?? false;
    }
    $apiToken = $apiToken ?? (bool) session('doctor_api_token');
@endphp

<div class="h-screen flex flex-col md:flex-row overflow-hidden">
    {{-- Mobile top bar --}}
    <header class="md:hidden shrink-0 z-30 h-14 bg-white border-b border-[#E5E7EB] flex items-center justify-between px-4">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-brand-500 text-white font-display font-bold flex items-center justify-center text-sm">H</div>
            <span class="font-display font-bold text-ink text-sm">Klinik Hekim Paneli</span>
        </div>
        <button type="button" id="menuToggle" class="p-2 rounded-lg border border-slate-200 text-ink" aria-label="Menü">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </header>

    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-30 hidden md:hidden"></div>

    {{-- Sidebar: sabit yükseklik, menü kendi içinde kayar --}}
    <aside id="sidebar" class="ysb fixed inset-y-0 left-0 z-40 w-[18rem] h-screen max-h-screen flex flex-col
                  transform -translate-x-full md:translate-x-0 md:static md:shrink-0
                  transition-transform duration-300 overflow-hidden">
        <div class="ysb-brand shrink-0">
            <div class="ysb-brand-row">
                <div class="ysb-brand-mark">
                    <span style="color:#c96a2b;font-family:Outfit,Inter,sans-serif;font-weight:800;font-size:1rem">H</span>
                </div>
                <div class="min-w-0">
                    <div class="ysb-brand-title">Klinik Hekim</div>
                    <div class="ysb-brand-sub">Klinik sitesi</div>
                </div>
            </div>
            <div style="margin-top:.75rem;padding:.65rem .75rem;border-radius:.75rem;background:#fff7ed;border:1px solid #fed7aa">
                <div style="font-size:.8rem;font-weight:700;color:#1c1917;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $user['unvan'] ?? '' }} {{ $user['ad_soyad'] ?? 'Hekim' }}</div>
                <div style="font-size:.68rem;color:#78716c;margin-top:.15rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $user['e_posta'] ?? '' }}</div>
            </div>
        </div>

        @include('panel.partials.sidebar-nav-panel')

        <div class="ysb-footer shrink-0">
            <div class="ysb-footer-row">
                <div class="ysb-avatar">{{ mb_strtoupper(mb_substr($user['ad_soyad'] ?? 'H', 0, 2)) }}</div>
                <div class="min-w-0">
                    <div class="ysb-user-name">{{ $user['ad_soyad'] ?? 'Hekim' }}</div>
                    <div class="ysb-user-role">Panel</div>
                </div>
                <div class="ysb-footer-actions">
                    <form method="POST" action="{{ route('panel.cikis') }}">
                        @csrf
                        <button type="submit" class="ysb-icon-btn" title="Cikis">
                            <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    {{-- Sağ içerik: sadece burası sayfa kaydırır --}}
    <div class="flex-1 min-w-0 min-h-0 flex flex-col overflow-hidden h-full">
        <header class="hidden md:flex shrink-0 bg-white border-b border-[#E5E7EB] px-6 lg:px-8 py-4 items-center justify-between gap-4 z-20">
            <div>
                <h1 class="font-display font-bold text-ink text-lg">@yield('sayfa_baslik', 'Panel')</h1>
                <p class="text-xs text-slate-400 mt-0.5">
                    @if($apiConfigured && $apiToken)
                        Platform ile senkron (API oturumu açık)
                    @elseif($apiConfigured)
                        API anahtarı var · platform oturumu yok
                    @else
                        Yerel panel · API entegrasyonu yok
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($apiConfigured && $apiToken)
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        API bağlı
                    </span>
                @elseif($apiConfigured)
                    <a href="{{ route('panel.giris') }}" class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-sky-700 bg-sky-50 border border-sky-100 px-2.5 py-1 rounded-full hover:bg-sky-100">
                        Platform girişi
                    </a>
                @else
                    <a href="{{ route('panel.api-entegrasyon') }}" class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full hover:bg-amber-100">
                        API bağla
                    </a>
                @endif
            </div>
        </header>

        <main class="flex-1 min-h-0 overflow-y-auto overscroll-contain main-scroll p-4 sm:p-6 lg:p-8">
            <div class="md:hidden mb-4">
                <h1 class="font-display font-bold text-ink text-lg">@yield('sayfa_baslik', 'Panel')</h1>
            </div>

            @if(session('basari'))
                <div class="mb-4 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-sm">{{ session('basari') }}</div>
            @endif
            @if(session('uyari'))
                <div class="mb-4 p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-sm">{{ session('uyari') }}</div>
            @endif
            @if(session('hata'))
                <div class="mb-4 p-3.5 rounded-2xl bg-red-50 border border-red-100 text-red-700 text-sm">{{ session('hata') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3.5 rounded-2xl bg-red-50 border border-red-100 text-red-700 text-sm">
                    <ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            @yield('icerik')
        </main>
    </div>
</div>

<script>
    (function () {
        const toggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        function close() {
            sidebar?.classList.add('-translate-x-full');
            overlay?.classList.add('hidden');
        }
        function open() {
            sidebar?.classList.remove('-translate-x-full');
            overlay?.classList.remove('hidden');
        }
        toggle?.addEventListener('click', () => {
            if (sidebar?.classList.contains('-translate-x-full')) open(); else close();
        });
        overlay?.addEventListener('click', close);

        if (!isOpen && panel) {
                    panel.classList.add('is-open');
                    btn.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });
    })();
</script>
{{-- Ana hekim paneli ile aynı onay modalı --}}
<div id="onayModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-2xl max-w-sm w-full p-6">
        <p id="onayModalMesaj" class="text-sm text-[#111827] font-medium font-display leading-relaxed"></p>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" id="onayModalIptal" class="px-4 py-2 rounded-xl border border-[#E5E7EB] text-xs font-bold text-[#6B7280] hover:bg-slate-50">Vazgeç</button>
            <button type="button" id="onayModalOnay" class="px-4 py-2 rounded-xl bg-[#C96A2B] text-white text-xs font-bold hover:bg-[#B55A20]">Onayla</button>
        </div>
    </div>
</div>
<script>
    window.onayModalAc = function (event, formOrEl, mesaj) {
        if (event) event.preventDefault();
        const modal = document.getElementById('onayModal');
        const msg = document.getElementById('onayModalMesaj');
        const ok = document.getElementById('onayModalOnay');
        const iptal = document.getElementById('onayModalIptal');
        if (!modal) return true;
        msg.textContent = mesaj || 'Emin misiniz?';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        const form = formOrEl && formOrEl.tagName === 'FORM' ? formOrEl : (formOrEl && formOrEl.form ? formOrEl.form : null);
        const cleanup = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); ok.onclick = null; };
        iptal.onclick = cleanup;
        ok.onclick = function () {
            cleanup();
            if (form) form.submit();
        };
        return false;
    };
</script>
@stack('scripts')
</body>
</html>
