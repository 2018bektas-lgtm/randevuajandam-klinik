@extends('panel.layouts.app')

@section('baslik', 'İki Adımlı Doğrulama')
@section('sayfa_baslik', 'İki Adımlı Doğrulama (2FA)')

@section('icerik')
<div class="max-w-2xl mx-auto space-y-6">
    @if(!empty($error))
        <div class="p-4 rounded-2xl bg-red-50 border border-red-100 text-sm text-red-700">{{ $error }}</div>
    @endif
    @if(session('basari'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-800 font-medium">
            {{ session('basari') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-100 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    @if($recoveryCodes = session('two_factor_recovery_codes'))
        <div class="p-6 rounded-3xl bg-amber-50 border border-amber-200">
            <h3 class="text-sm font-bold font-display text-amber-900 uppercase tracking-wider">Yedek kodlar (bir kez gösterilir)</h3>
            <p class="text-xs text-amber-800 mt-2 leading-relaxed">Bu kodları güvenli bir yere kaydedin. Authenticator’a erişemezseniz bunlarla giriş yapabilirsiniz. Her kod tek kullanımlıktır.</p>
            <ul class="mt-4 grid grid-cols-2 gap-2 font-mono text-sm text-amber-950">
                @foreach($recoveryCodes as $code)
                    <li class="px-3 py-2 bg-white/80 rounded-lg border border-amber-100 text-center">{{ $code }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="p-8 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h3 class="text-lg font-bold font-display text-[#111827]">Authenticator (TOTP)</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed max-w-md">
                    Google Authenticator, Authy veya 1Password ile 6 haneli kod. SMS maliyeti yok.
                    Platform hesabınızla senkron çalışır (ana site + hekim paneli + mobil).
                </p>
            </div>
            @if(!empty($enabled))
                <span class="inline-flex px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-extrabold uppercase tracking-wide border border-emerald-100">Aktif</span>
            @else
                <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-extrabold uppercase tracking-wide border border-slate-200">Kapalı</span>
            @endif
        </div>

        @if(empty($enabled) && !empty($setup))
            <div class="mt-8 grid md:grid-cols-2 gap-8 items-start">
                <div class="space-y-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 font-display">1. QR kodu tarayın</p>
                    @if(!empty($setup['qr_image_url']))
                        <div class="inline-block p-3 bg-white border border-slate-200 rounded-2xl">
                            <img src="{{ $setup['qr_image_url'] }}" alt="2FA QR" width="220" height="220" class="block">
                        </div>
                    @endif
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 font-display mt-4">Manuel anahtar</p>
                    <code class="block text-xs bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 break-all font-mono">{{ $setup['secret'] ?? '' }}</code>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 font-display mb-3">2. Uygulamadaki kodu girin</p>
                    <form method="POST" action="{{ route('panel.two-factor.confirm') }}" class="space-y-4">
                        @csrf
                        <input type="text" name="code" required inputmode="numeric" autocomplete="one-time-code" placeholder="000000"
                               class="w-full px-4 py-3 rounded-xl border border-[#E5E7EB] text-center text-lg tracking-[0.25em] font-semibold focus:border-[#C96A2B] focus:outline-none focus:ring-1 focus:ring-[#C96A2B]">
                        <button type="submit" class="w-full py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider font-display">
                            2FA’yı Etkinleştir
                        </button>
                    </form>
                </div>
            </div>
        @endif

        @if(!empty($enabled))
            <div class="mt-8 space-y-8">
                <form method="POST" action="{{ route('panel.two-factor.recovery') }}" class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                    @csrf
                    <h4 class="text-xs font-bold font-display text-slate-800 uppercase tracking-wider">Yedek kodları yenile</h4>
                    <p class="text-[11px] text-slate-500">Authenticator kodu ile yeni yedek kodlar üretir; eskiler geçersiz olur.</p>
                    <input type="text" name="code" required inputmode="numeric" autocomplete="one-time-code" placeholder="Authenticator kodu"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">Yenile</button>
                </form>

                <form method="POST" action="{{ route('panel.two-factor.disable') }}" class="p-4 rounded-2xl bg-red-50/50 border border-red-100 space-y-3">
                    @csrf
                    <h4 class="text-xs font-bold font-display text-red-800 uppercase tracking-wider">2FA’yı kapat</h4>
                    <input type="password" name="sifre" required placeholder="Mevcut şifre"
                           class="w-full px-3 py-2.5 rounded-xl border border-red-100 text-sm">
                    <input type="text" name="code" required placeholder="Authenticator veya yedek kod"
                           class="w-full px-3 py-2.5 rounded-xl border border-red-100 text-sm">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-wider">Kapat</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
