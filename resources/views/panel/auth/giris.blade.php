<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hekim Girişi · Yönetim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    {{-- Tailwind: Vite ile derlenir (eskiden Play CDN idi — uretimde kullanilmamali) --}}
    @vite('resources/css/panel.css')
</head>
<body class="min-h-screen bg-[#F5F5F4] flex items-center justify-center p-4">
@php
    $apiOk = $apiLive ?? false;
    $apiConfigured = $apiConfigured ?? false;
@endphp
<div class="w-full max-w-md">
    <div class="text-center mb-6">
        <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-[#C96A2B] text-white font-display text-2xl font-bold shadow-lg shadow-[#C96A2B]/25">H</div>
        <h1 class="mt-4 font-display text-2xl font-bold text-slate-900">Klinik Hekim Girişi</h1>
        <p class="mt-2 text-sm text-slate-500">
            Bu kliniğe bağlı hekimler platform e-posta/şifresiyle girer.
            Randevu, hasta, içerik ve finans menüleri sizin hesabınıza aittir.
        </p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
        @if(session('hata'))
            <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm leading-relaxed">{{ session('hata') }}</div>
        @endif
        @if(session('basari'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-sm">{{ session('basari') }}</div>
        @endif
        @if(session('uyari'))
            <div class="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm">{{ session('uyari') }}</div>
        @endif

        <div class="mb-4 flex flex-wrap gap-2 text-[10px] font-bold uppercase tracking-wider">
            @if($apiOk)
                <span class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">API canlı</span>
            @elseif($apiConfigured)
                <span class="px-2 py-1 rounded-full bg-red-50 text-red-700 border border-red-100">API anahtarı geçersiz</span>
            @else
                <span class="px-2 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200">API yok</span>
            @endif
            <span class="px-2 py-1 rounded-full bg-slate-50 text-slate-600 border border-slate-100">Panel erişimi açık</span>
        </div>

        @if(!$apiOk)
            <div class="mb-4 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 text-sm leading-relaxed">
                <strong class="block mb-1">Platform bağlantısı yok</strong>
                @if(!empty($apiError))
                    <span class="block text-xs text-amber-800 mb-2">{{ $apiError }}</span>
                @endif
                <p class="text-[12px] text-amber-900 m-0">
                    Yerel yönetici hesabıyla panele girip <strong>API Entegrasyonu</strong> sayfasından
                    ana sunucu Key/Secret bilgisini girebilirsiniz. Ardından hekim hesabıyla yeniden giriş yapın.
                </p>
                @if(!empty($showLocalHints))
                    <div class="text-xs bg-white/80 border border-amber-100 rounded-lg p-2.5 font-mono space-y-0.5 mt-2">
                        <div class="text-[10px] uppercase tracking-wide text-amber-700 font-sans font-bold mb-1">Yalnızca local geliştirme</div>
                        <div>E-posta: <strong>{{ $localAdminEmail ?? 'admin@site.local' }}</strong></div>
                        <div>Şifre: <strong>LOCAL_ADMIN_PASSWORD</strong> (.env)</div>
                    </div>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('panel.giris.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">E-posta</label>
                <input type="email" name="e_posta" value="{{ old('e_posta') }}" required
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/15"
                       placeholder="hekim@ornek.com" autocomplete="username">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Şifre</label>
                <input type="password" name="sifre" required
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/15"
                       placeholder="••••••••" autocomplete="current-password">
            </div>
            <button type="submit" class="w-full py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-sm font-display transition">
                Giriş Yap
            </button>
        </form>

        <p class="mt-5 text-center text-xs text-slate-400">
            API anahtarları yalnızca ana sunucuda üretilir; bu sitede üretim yapılmaz.
        </p>
        <p class="mt-2 text-center">
            <a href="{{ route('frontend.anasayfa') }}" class="text-xs text-[#C96A2B] font-semibold">← Public siteye dön</a>
        </p>
    </div>
</div>
</body>
</html>
